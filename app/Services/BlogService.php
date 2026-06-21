<?php

namespace App\Services;

use App\Http\Response;
use App\Support\TextManager;
use App\Queue\Queue;
use App\Jobs\SimpleMailJob;
use App\Jobs\PushNotificationJob;
use App\Jobs\CloudinaryJob;
use App\Models\Blog;
use App\Models\User;
use App\Models\Notification;
use Exception;

class BlogService
{
    protected Response $response;
    protected TextManager $textProcessor;
    protected Queue $queueManager;
    protected Blog $blogModel;
    protected User $userModel;
    protected Notification $notificationModel;
    private string $baseUrl;

    public function __construct(
        Response $response, 
        TextManager $textProcessor, 
        Queue $queueManager,
        Blog $blogModel, 
        User $userModel, 
        Notification $notificationModel
    )
    {
        $this->response          = $response;
        $this->textProcessor     = $textProcessor;
        $this->queueManager      = $queueManager;
        $this->blogModel         = $blogModel;
        $this->userModel         = $userModel;
        $this->notificationModel = $notificationModel;
        $this->baseUrl           = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ? 'http://localhost/projects/showcase/jobspot' : '';
    }

    // =========================================
    // CREATE BLOG
    // =========================================
    public function createBlog(
        string $banner, 
        string $title, 
        string $category, 
        string $article, 
        int $userId
    ): array {

        $title = $this->textProcessor->formatTitle($title);

        $created = $this->blogModel->createBlog($banner, $title, $category, $article, $userId);
        if ($created === false) {
            return $this->response->fail('Failed to create blog', 500);
        }

        $authorDetails = $this->userModel->findById($userId);
        $authorName    = $authorDetails['fullname'];
        $authorEmail   = $authorDetails['email'];
        $authorRole    = $authorDetails['user_role'];

        $mail = [
            'subject' => 'Blog Creation Successful',
            'message' => "Hi <b>{$authorName}</b>, 
                <br> Your blog is currently <b>pending approval</b>. 
                <br> Our team is reviewing your blog details. Once approved, it'll be displyed live on the site. 
                <br> We'll notify you as soon as the status changes.
                <br> Thank you for your patience.
            "
        ];

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $authorEmail,
                $mail['message']
            ],
            'emails'
        );

        $authorPushMessage = $this->textProcessor->formatPushMessage($mail['message']);

        // Push notification job dispatch
        $this->queueManager->dispatch(
            PushNotificationJob::class,
            [
                "Single $authorRole",
                $userId,
                $mail['subject'],
                $authorPushMessage,
                ['url' => "{$this->baseUrl}/blog", 'type' => 'blog']
            ],
            'push'
        );

        $adminMessage =  "
            Hello Admin, 
            <br> A new blog was created on the platform!
            <br> Kindly review and take necessary actions. 
        ";

        $adminPushMessage = $this->textProcessor->formatPushMessage($adminMessage);

        $admins = $this->userModel->allByRole('Admin');

        foreach ($admins as $admin) {

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    'New Blog',
                    $admin['email'],
                    $adminMessage
                ],
                'emails'
            );

            // Push notification job dispatch
            $this->queueManager->dispatch(
                PushNotificationJob::class,
                [
                    'Single Admin',
                    $admin['user_id'],
                    'New Blog',
                    $adminPushMessage,
                    ['url' => "{$this->baseUrl}/admin", 'type' => 'blog']
                ],
                'push'
            );
            
            $notification = $this->notificationModel->create($adminMessage, 'New Blog', $admin['user_id']);
            if ($notification === false) {
                $this->logError("Failed to create notification for admin: {$admin['email']}");
            }
        }

        return $this->response->success('Blog created successfully', [], 201);
    }

    // =========================================
    // UPDATE BLOG DETAILS
    // =========================================
    public function updateDetails(
        string $title, 
        string $article, 
        int $id
    ): array {

        $updated = $this->blogModel->updateDetails($title, $article, $id);
        if ($updated === false) {
            return $this->response->fail('Failed to update blog', 500);
        }
        
        return $this->response->success('Details updated successfully');
    }

    // =========================================
    // UPDATE BLOG BANNER
    // =========================================
    public function updateBanner(
        int $id, 
        string $url
    ): array {

        $banner = $this->blogModel->findBanner($id);
        if ($banner === false) {
            return $this->response->fail('Blog banner not found', 404);
        }

        $updated = $this->blogModel->updateBanner($url, $id);
        if ($updated === false) {
            return $this->response->fail('Failed to update blog banner', 500);
        }

        // Cloudinary job dispatch
        $this->queueManager->dispatch(
            CloudinaryJob::class,
            [
                $banner
            ],
            'cloudinary'
        );

        return $this->response->success('Banner updated successfully');
    }

    // =========================================
    // UPDATE BLOG STATUS
    // =========================================
    public function updateStatus(
        string $status, 
        int $id
    ): array {

        $updated = $this->blogModel->updateStatus($status, $id);
        if ($updated === false) {
            return $this->response->fail('Failed to update status', 500);
        }

        $authorId      = $this->blogModel->findUserByBlogId($id);
        $authorDetails = $this->userModel->findById($authorId);
        $authorName    = $authorDetails['fullname'];
        $authorEmail   = $authorDetails['email'];
        $authorRole    = $authorDetails['user_role'];

        // Build message based on status
        $statusMessages = [
            'Active' => "
                Hi <b>{$authorName}</b>, 
                <br> Great news! 🎉 Your latest blog is now <b>active</b>. 
                <br> People can now read, react, share and bookmark your blog.
                <br> Check it out via this link: <b><a href='{$this->baseUrl}/read?id={$id}'>Read Blog</a></b>.
                <br> We hope to see more of your blogs on our platform!
            ",

            'Pending' => "
                Hi <b>{$authorName}</b>, 
                <br> Your store has been <b>deactivated</b>. 
                <br> This may be due to policy violations, inactivity, or other issues. 
                <br> Please contact support at <b>support@jobspot.com</b> or visit <b><a href='{$this->baseUrl}/contact'>Appeal Page</a></b> to resolve this and restore your blog. 
                <br> We value your partnership and hope to have you back soon.
            ",
        ];

        // Fallback in case of unknown status
        $message = $statusMessages[$status] ?? "
            Hi <b>{$authorName}</b>, 
            <br> There has been an update to your blog status. 
            <br> Please check your dashboard for more details.
        ";

        $mail = [
            'subject' => 'Blog Status Updated',
            'message' => $message
        ];

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $authorEmail,
                $mail['message']
            ],
            'emails'
        );

        // Send push notification to author
        $authorPushMessage = $this->textProcessor->formatPushMessage($message);

        // Push notification job dispatch
        $this->queueManager->dispatch(
            PushNotificationJob::class,
            [
                "Single $authorRole",
                $authorId,
                'Blog Status Updated',
                $authorPushMessage,
                ['url' => "{$this->baseUrl}/blog", 'type' => 'blog']
            ],
            'push'
        );

        return $this->response->success('Status updated successfully');
    }

    // =========================================
    // UPDATE BLOG COUNTERS
    // =========================================
    public function updateCounter(
        int $blogId, 
        string $type
    ): array {

        $updated = $this->blogModel->updateCounter($blogId, $type);
        if ($updated === false) {
            return $this->response->fail('Failed to update counter', 500);
        }

        $stats = $this->blogModel->getStats($blogId);

        return $this->response->success('Counter updated successfully', ['stats' => $stats]);
    }

    // =========================================
    // DELETE BLOG
    // =========================================
    public function deleteBlog(
        int $id
    ): array {

        $deleted = $this->blogModel->deleteBlog($id);
        if ($deleted === false) {
            return $this->response->fail('Failed to delete blog', 505);
        }

        return $this->response->success('Blog deleted successfully');
    }

    // =========================================
    // GET SINGLE BLOG
    // =========================================
    public function findOne(
        int $id
    ): array { 

        $blog = $this->blogModel->findOne($id);
        if ($blog === false) {
            return $this->response->fail('Failed to fetch blog', 505);
        }
        
        return $this->response->success('Blog fetched successfully', ['blog' => $blog]);
    }

    // =========================================
    // GET BLOGS BY STATUS
    // =========================================
    public function findByStatus(
        string $status, 
        int $page
    ): array { 

        $blogs = $this->blogModel->findByStatus($status, $page);
        if ($blogs === false) {
            return $this->response->fail('Failed to fetch blogs', 505);
        }
       
        return $this->response->success('Blogs fetched successfully', ['blogs' => $blogs]);
    }

    // =========================================
    // GET BLOGS BY AUTHORS
    // =========================================
    public function findByUser(
        int $id, 
        int $page
    ): array { 
        
        $blogs = $this->blogModel->findByUser($id, $page);
        if ($blogs === false) {
            return $this->response->fail('Failed to fetch blogs', 505);
        }
        
        return $this->response->success('Blogs fetched successfully', ['blogs' => $blogs]);
    }

    // =========================================
    // LOG ERROR MESSAGES
    // =========================================
    private function logError(
        string $message
    ): void {
        
        $logFile   = dirname(__DIR__, 2) . '/storage/logs/php-error.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry     = "[{$timestamp}] {$message}\n";
        
        error_log($entry, 3, $logFile);
    }
}
