<?php

namespace App\Models;

class Blog extends Model
{
    protected string $table = 'blogs';

    // =========================================
    // CREATE BLOG
    // =========================================
    public function createBlog(
        string $banner,
        string $title,
        string $category,
        string $article,
        int $userId
    ): bool {

        return $this->query()
            ->insert([
                'banner'   => $banner,
                'title'    => $title,
                'category' => $category,
                'article'  => $article,
                'user_id'  => $userId
            ]);
    }

    // =========================================
    // UPDATE BLOG DETAILS
    // =========================================
    public function updateDetails(
        string $title,
        string $article,
        int $blogId
    ): bool {

        return $this->query()
            ->where('blog_id', '=', $blogId)
            ->update([
                'title'   => $title,
                'article' => $article
            ]);
    }

    // =========================================
    // UPDATE BLOG BANNER
    // =========================================
    public function updateBanner(
        string $banner,
        int $blogId
    ): bool {

        return $this->query()
            ->where('blog_id', '=', $blogId)
            ->update(['banner' => $banner]);
    }

    // =========================================
    // UPDATE BLOG STATUS
    // =========================================
    public function updateStatus(
        string $status,
        int $blogId
    ): bool {

        return $this->query()
            ->where('blog_id', '=', $blogId)
            ->update(['blog_status' => $status]);
    }

    // =========================================
    // COUNTER COLUMN MAP
    // =========================================
    private function getColumn(
        string $type
    ): string {
        return [
            'read'  => 'read_count',
            'like'  => 'like_count',
            'share' => 'share_count',
        ][$type];
    }

    // =========================================
    // UPDATE BLOG COUNTER
    // =========================================
    public function updateCounter(
        int $blogId,
        string $type
    ): bool {

        $column = $this->getColumn($type);

        return $this->query()
            ->where('blog_id', '=', $blogId)
            ->increment($column);
    }

    // =========================================
    // GET BLOG STATS
    // =========================================
    public function getStats(
        int $blogId
    ): ?array {

        return $this->query()
            ->select([
                'read_count',
                'like_count',
                'share_count'
            ])
            ->where('blog_id', '=', $blogId)
            ->first();
    }

    // =========================================
    // DELETE BLOG
    // =========================================
    public function deleteBlog(
        int $blogId
    ): bool {

        return $this->query()
            ->where('blog_id', '=', $blogId)
            ->delete();
    }

    // =========================================
    // FIND ONE BLOG
    // =========================================
    public function findOne(
        int $blogId
    ): ?array {

        return $this->query()
            ->where('blog_id', '=', $blogId)
            ->first();
    }

    // =========================================
    // FIND BLOG BANNER
    // =========================================
    public function findBanner(
        int $blogId
    ): ?array {

        $result = $this->query()
            ->select(['banner'])
            ->where('blog_id', '=', $blogId)
            ->first();

        return $result
            ? $result['banner']
            : null;

    }

    // =========================================
    // FIND BLOG USER
    // =========================================
    public function findUserByBlogId(
        int $blogId
    ): ?int {

        $result = $this->query()
            ->select(['user_id'])
            ->where('blog_id', '=', $blogId)
            ->first();

        return $result
            ? (int) $result['user_id']
            : null;
    }

    // =========================================
    // FIND BLOGS BY STATUS
    // =========================================
    public function findByStatus(
        ?string $status = null,
        int $page = 1,
        int $limit = 20
    ): array {

        return $this->query()
            ->when(
                $status &&
                in_array($status, ['Pending', 'Active', 'Deactivated']),

                fn($query) =>
                    $query->where('blog_status', '=', $status)
            )
            ->orderBy('blog_id', 'DESC')
            ->paginate($page, $limit)
            ->get();
    }

    // =========================================
    // FIND BLOGS BY USER
    // =========================================
    public function findByUser(
        int $userId,
        int $page = 1,
        int $limit = 20
    ): array {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->orderBy('blog_id', 'DESC')
            ->paginate($page, $limit)
            ->get();
    }

    // =========================================
    // COUNT BLOGS
    // =========================================
    public function countBlogs(
        ?int $userId = null,
        ?string $status = null
    ): int {

        return $this->query()
            ->when(
                $userId &&
                !is_null($userId),

                fn($query) =>
                    $query->where('user_id', '=', $userId)
            )
            ->when(
                $status && 
                !is_null($status),

                fn($query) =>
                    $query->where('blog_status', '=', $status)
            )
            ->count();
    }

    // =========================================
    // GET BLOG DASHBOARD STATS
    // =========================================
    public function getBlogStats(
        int $userId
    ): array {

        return [
            'active' => $this->countBlogs(
                null,
                'Active'
            ),

            'pending' => $this->countBlogs(
                null,
                'Pending'
            ),

            'personal' => $this->countBlogs(
                $userId,
                'Active'
            )
        ];
    }
}