<?php

namespace App\Services;

use App\Http\Response;
use App\Contracts\SessionInterface;
use App\Support\TextManager;
use App\Queue\Queue;
use App\Jobs\SimpleMailJob;
use App\Jobs\PushNotificationJob;
use App\Jobs\CloudinaryJob;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Mail;
use App\Models\Push;
use App\Database\Database;

class UserService
{
    protected SessionInterface $session;
    protected Response $response;
    protected TextManager $textProcessor;
    protected Queue $queueManager;
    protected User $userModel;
    protected Wallet $walletModel;
    protected Notification $notificationModel;
    protected Mail $mailModel;
    protected Push $pushModel;
    private Database $dbConnection;
    private string $condition;
    private string $baseUrl;

    public function __construct(
        SessionInterface $session,
        Response $response,
        TextManager $textProcessor, 
        Queue $queueManager,
        User $userModel, 
        Wallet $walletModel, 
        Notification $notificationModel, 
        Mail $mailModel, 
        Push $pushModel,
        Database $dbConnection
    )
    {
        $this->session           = $session;
        $this->response          = $response;
        $this->textProcessor     = $textProcessor;
        $this->queueManager      = $queueManager;
        $this->userModel         = $userModel;
        $this->walletModel       = $walletModel;
        $this->notificationModel = $notificationModel;
        $this->mailModel         = $mailModel;
        $this->pushModel         = $pushModel;
        $this->dbConnection      = $dbConnection;
        $this->condition         = 'leader';
        $this->baseUrl           = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ? 'http://localhost/projects/showcase/jobspot' : '';
    }

    // =========================================
    // REGISTER USER
    // =========================================
    public function register(
        string $avatar,
        string $firstname,
        string $lastname,
        string $email,
        string $contact,
        string $country,
        string $password,
        string $membership,
        string $state,
        string $lga,
        string $receipt,
        int $amount,
        string $narration,
        int $id,
        int $facilitator,
        string $currency,
        string $code
    ): array {

        $acceptedRoles = ['Dermatologist', 'Doctor', 'Freelancer', 'Lawyer', 'Therapist', 'Vendor'];
        if (!in_array($membership, $acceptedRoles)) {
            return $this->response->fail('Role not supported', 409);
        }

        $hasRegistered = $this->userModel->findByEmail($email);
        if ($hasRegistered === true) {
            return $this->response->fail('Email already registered', 409);
        }

        $firstname     = $this->textProcessor->formatUserName($firstname);
        $lastname      = $this->textProcessor->formatUserName($lastname);
        $fullName      = $firstname . ' ' . $lastname;
        $contact       = $code . ltrim($contact, '0');
        $password      = password_hash($password, PASSWORD_BCRYPT ?? PASSWORD_ARGON2ID);
        $defaultStatus = 'Pending';
        $defaultBio    = 'N/A';

        $this->dbConnection->beginTransaction();
        try {

            $userId = $this->userModel->createAccount($avatar, $fullName, $email, $contact, $country, $defaultBio, $state, $password, $membership, $defaultStatus);

            if (!$userId) {
                throw new \Exception("User creation failed");
            }

            $identifier = $this->getIdentifier($facilitator);
            $reference  = $this->walletModel->createPayment($amount, $facilitator, $id, $userId, $identifier, $narration, $receipt);
            $this->walletModel->createWallet(0, $userId);
            $this->walletModel->createDetails(0, 'None', 'None', 'None', $currency, $userId);
            $this->userModel->trackReferral($id, $userId, $this->condition);

            $this->dbConnection->commit();
        } catch (\Throwable $e) {

            $this->dbConnection->rollBack();
            return $this->response->fail('Registration failed: ' . $e->getMessage(), 500);
        }

        $message = $this->buildWelcomeMessage($fullName);

        $mail = [
            'subject' => 'Registration Under Review',
            'message' => $message
        ];

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $email, 
                $mail['message']
            ],
            'emails'
        );

        $adminMessage =  "
            Hello Admin, 
            <br> A new {$membership}, <b>{$fullName}</b>, just registered on the platform!
            <br> Kindly review and take necessary actions. 
        ";

        $adminPushMessage = $this->textProcessor->formatPushMessage($adminMessage);

        $admins = $this->userModel->allByRole('Admin');

        foreach ($admins as $admin) {

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    'New Registration',
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
                    'New Registration',
                    $adminPushMessage,
                    ['url' => "{$this->baseUrl}/admin", 'type' => 'registration']
                ],
                'push'
            );
            
            $notification = $this->notificationModel->create($adminMessage, 'New Registration', $admin['user_id']);
            if ($notification === false) {
                $this->logError("Failed to create notification for admin: {$admin['email']}");
            }
        }

        $data = [
            'user' => [
                'name'  => $fullName, 
                'email' => $email, 
                'phone' => $contact
            ],
            'reference' => $narration === 'Auto' ? $reference : null
        ];

        return $this->response->success('Registration successful', 201, $data);
    }

    // =========================================
    // LOGIN USER
    // =========================================
    public function login(
        string $email,
        string $password
    ): array {

        $user = $this->userModel->findByEmail($email);
        if ($user === false) {
            return $this->response->fail('User not found', 404);
        }

        if (in_array(strtolower($user['user_role']), ['leader'])) {
            return $this->response->fail('Access denied', 403);
        }
        
        if (in_array(strtolower($user['user_status']), ['deactivated', 'pending'])) {
            return $this->response->fail('Cannot login at this time', 403);
        }

        if (!password_verify($password, $user['user_password']) || $email !== $user['email']) {
            return $this->response->fail('Invalid credentials', 401);
        }

        $data = [
            'id'    => $user['user_id'],
            'name'  => $user['fullname'],
            'email' => $user['email'],
            'role'  => strtolower($user['user_role'])
        ];

        // Basic session login (can be switched to JWT or other token-based auth)
        $this->session->basicLogin($data);

        $dadhboardPath = $this->getPath($data['role']);

        return $this->response->success('Login successful', ['dashboard' => $dadhboardPath, 'user' => $data]);
    }

    // =========================================
    // GET OTP
    // =========================================
    public function sendOtp(
        string $email
    ): array {

        $user = $this->userModel->findByEmail($email);
        if ($user === false) {
            return $this->response->fail('User not found', 404);
        }

        $otp = rand(100000, 999999);

        $data = [
            'code'       => $otp,
            'email'      => $email,
            'expires_at' => time() + 300
        ];

        $this->session->store('otp', $data);

        $mail = [
            'subject' => 'Password Reset OTP',
            'message' => "Hi, <br> Your password reset OTP is <b>$otp</b> and it expires in 5 minutes",
        ];

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $email,
                $mail['message']
            ],
            'otp'
        );

        return $this->response->success('OTP sent to your email');
    }

    // =========================================
    // RESET PASSWORD
    // =========================================
    public function reset(
        int $otp,
        string $password
    ): array {

        $sessionOtp = $this->session->retrieve('otp');

        if (!$sessionOtp || time() > $sessionOtp['expires_at']) {
            return $this->response->fail('OTP expired or not set', 400);
        }

        if ($payload['otp'] != $sessionOtp['code']) {
            return $this->response->fail('Invalid OTP', 401);
        }

        $email          = $sessionOtp['email'];
        $hashedPassword = password_hash($payload['password'], PASSWORD_BCRYPT);

        $success = $this->userModel->updatePassword($email, $hashedPassword);
        if ($success === false) {
            return $this->response->fail('Failed to reset password', 500);
        } 

        $this->session->terminate('otp');

        return $this->response->success('Password reset successful');
    }

    // =========================================
    // UPDATE BIO
    // =========================================
    public function update(
        string $bio, 
        int $userId
    ): array {

        $updated = $this->userModel->updateDetails($bio, $userId);
        if ($updated === false) {
            return $this->response->fail('Update failed', 500);
        } 

        return $this->response->success('Details updated successfully');
    }

    // =========================================
    // UPDATE SOCIAL HANDLES
    // =========================================
    public function social(
        string $facebook,
        string $instagram,
        string $tiktok,
        string $twitter, 
        int $userId
    ): array {

        $updated = $this->userModel->updateSocials($facebook, $instagram, $tiktok, $twitter, $userId);
        if ($updated === false) {
            return $this->response->fail('Update failed', 500);
        } 
        
        return $this->response->success('Socials updated successfully');
    }

    // =========================================
    // UPDATE PROFILE PICTURE
    // =========================================
    public function profile(
        string $avatar, 
        int $userId
    ): array {

        $profile = $this->userModel->getProfile($userId);
        if ($profile === null) {
            return $this->response->fail('Profile not found', 404);
        }

        $updated = $this->userModel->updateProfile($avatar, $userId);
        if ($updated === false) {
            return $this->response->fail('Failed to update profile', 500);
        }

        if ($profile !== 'None') {

            // Cloudinary job dispatch
            $this->queueManager->dispatch(
                CloudinaryJob::class,
                [
                    $profile
                ],
                'cloudinary'
            );
        }

        return $this->response->success('Profile updated successfully');
    }

    // =========================================
    // UPDATE PASSWORD
    // =========================================
    public function password(
        string $currentPassword, 
        string $newPassword,
        int $userId
    ): array {

        $user = $this->userModel->findById($userId);
        if ($user === false) {
            return $this->response->fail('User not found', 404);
        }

        $email      = $user['email'];
        $dbPassword = $user['user_password'];

        if (password_hash($newPassword, PASSWORD_BCRYPT) === $dbPassword) {
            return $this->response->fail('New password cannot be the same as the old password', 400);
        }

        if (password_verify($currentPassword, $dbPassword)) {

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $success = $this->userModel->updatePassword($email, $hashedPassword);
            if ($success === false) {
                return $this->response->fail('Failed to change password', 500);
            } 

            return $this->response->success('Password changed successfully');
        } else {
           return $this->response->fail('Failed to verify password', 500);
        }
    }

    // =========================================
    // UPDATE STATUS
    // =========================================
    public function status(
        string $status,
        int $id
    ): array {

        $status = $this->userModel->updateStatus($status, $id);
        if ($status === false) {
            return $this->response->fail('Failed to update status', 500);
        }

        $userDetails = $this->userModel->findById($id);
        $userName    = $userDetails['fullname'];
        $userEmail   = $userDetails['email'];

        $statusMessages = [
            'Active' => "
                Hi <b>{$userName}</b>, 
                <br> Great news! 🎉 Your account has been <b>activated</b>. 
                <br> You can now log into your account and pick up from where you left off. 
                <br> Take care to adhere to the regulations in order to prevent sanctions of this nature.
                <br> We're excited to have you back!
            ",

            'Deactivated' => "
                Hi <b>{$userName}</b>, 
                <br> Your account has been <b>deactivated</b>. 
                <br> This may be due to policy violations, inactivity, or other issues. 
                <br> Please contact support at <b>support@jobspot.com</b> or visit <b><a href='{$this->baseUrl}/contact'>Appeal Page</a></b> 
                    to resolve this and restore your account. 
                <br> We value your partnership and hope to have you back soon.
            ",
        ];

        $message = $statusMessages[$status] ?? "
            Hi <b>{$userName}</b>, 
            <br> There has been an update to your account status. 
            <br> Please check account for more details.
        ";

        $mail = [
            'subject' => 'Account Status Updated',
            'message' => $message
        ];

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $userEmail,
                $mail['message']
            ],
            'emails'
        );

        return $this->response->success('Account status updated successfully');
    }

    // =========================================
    // COUNT USERS
    // =========================================
    public function count(): array
    { 
        $counts = $this->userModel->countAllRoles();
        /*if (count($counts) === 0) {
            return $this->response->fail('Counts failed', 500);
        }*/
        
        return $this->response->success('Counts fetched successfully', ['counts' => $counts]);
    }

    // =========================================
    // LOGOUT USER
    // =========================================
    public function logout(
        ?string $role
    ): array {

        if (!isset($role) || is_null($role)) {
            return $this->response->fail('User role not found', 400);
        }

        $loginPath = $this->baseUrl . (strtolower($role) === 'admin' ? '/admin' : '/login');

        $this->session->terminate('user');
        $this->session->destroy();

        return $this->response->success('Logout successful', ['dashboard' => $loginPath]);
    }

    // =========================================
    // SEND CONTACT MESSAGE
    // =========================================
    public function contact(
        string $name,
        string $email,
        string $contact,
        string $country,
        string $subject,
        string $message,
        string $code
    ): array {

        $contact = $code . ltrim($contact, '0');

        $builtMessage = "
            A message was sent by <b> " . trim($name) . "</b> from  <b> " . trim($country) . "</b>
            <br>
            You can reach out to them via their mobile: <b>" . trim($contact) . "</b> or email address: <b>" . trim($email) . "</b>
        ";

        $admins = $this->userModel->allByRole('Admin');

        foreach ($admins as $admin) {

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    $subject,
                    $admin['email'],
                    $builtMessage
                ],
                'emails'
            );
            
            $notification = $this->notificationModel->create($message, 'New Message', $admin['user_id']);
            if ($notification === false) {
                $this->logError("Failed to create notification for admin: {$admin['email']}");
            }

            $mailed = $this->mailModel->createMail('Text', $subject, $name, $admin['email'], $message, 'None', 'None');
            if ($mailed === false) {
                $this->logError("Failed to create mail for admin: {$admin['email']}");
            }
        }

        return $this->response->success('Message sent successfully');
    }

    // =========================================
    // SUBSCRIBE TO PUSH NOTIFICATION
    // =========================================
    public function subscribe(
        string $token, 
        string $deviceId,
        int $userId, 
        string $userType
    ): array {

        $subscribed = $this->pushModel->saveToken($token, $deviceId, $userId, $userType);
        if ($subscribed) {
            return $this->response->success('Sync successful');
        } else {
            return $this->response->fail('Sync failed');
        }
    }

    // =========================================
    // UNSUBSCRIBE TO PUSH NOTIFICATION
    // =========================================
    public function unsubscribe(
        string $token, 
        string $deviceId
    ): array {

        $deleted = $this->pushModel->deactivateToken($token, $deviceId);
        if ($deleted === false) {
            return $this->response->fail('Failed to disable notifications', 500);
        }
        return $this->response->success('Notifications disabled successfully');
    }

    // =========================================
    // GET USERS BY ROLE
    // =========================================
    public function fetch(
        string $role, 
        int $page
    ): array { 

        $users = $this->userModel->getByRole($role, $page);
        /*if (count($users['users']) === 0) {
            return $this->response->fail('Failed to fetch users', 500);
        }*/
        
        return $this->response->success('Users fetched successfully', $users);
    }

    // =========================================
    // DELETE USER
    // =========================================
    public function delete(
        int $id
    ): array {

        $deleted = $this->userModel->deleteUser($id);
        if ($deleted === false) {
            return $this->response->fail('Failed to delete user', 500);
        } 
        
        return $this->response->success('User deleted successfully');
    }

    // =========================================
    // SWITCH USER ROLE
    // =========================================
    public function switch(
        string $email, 
        string $role
    ): array {

        $allowedEmail = 'chukwuebukaokeke09@gmail.com';

        if ($email !== $allowedEmail) {
            return $this->response->fail('Email invalid', 401);
        }

        $user = $this->userModel->findByEmail($email);
        if ($user === false) {
            return $this->response->fail('User not found', 404);
        }

        $updated = $this->userModel->updateRole($email, $role);
        if ($updated === false) {
            return $this->response->fail('Failed to update role', 500);
        } 

        $loginPath = $this->getPath($role);

        return $this->response->success('Role switched successfully', ['path' => $loginPath]);
    }

    // =========================================
    // GET IDENTIFIER
    // =========================================
    private function getIdentifier(
        string $channel
    ): string {

        $maps = [
            'Admin'  => 'ADM',
            'User'   => 'USR',
            'Leader' => 'LDR'
        ];

        return $maps[$channel];
    }

    // =========================================
    // BUILD WELCOME MESSAGE (HELPER)
    // =========================================
    private function buildWelcomeMessage(
        string $fullName
    ): string {
        
        return "
            Hi <b>{$fullName}</b>, 
            <br> Your registration is currently <b>undergoing review</b>. 
            <br> Our team is reviewing your details. Once approved, you'll be able to login and use our services. 
            <br> We'll notify you as soon as the status changes.
            <br> Thank you for your patience.
        ";
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

    // =========================================
    // GET DASHBOARD PATH
    // =========================================
    private function getPath(
        string $role
    ): string {

        $paths = [

            'admin'  => '/admin/dashboard',

            'worker' => '/worker',
        ];

        return $this->baseUrl . (
            $paths[strtolower($role)] ?? '/dashboard'
        );
    }
}
