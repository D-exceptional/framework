<?php

namespace App\Models;

class User extends Model
{
    protected string $table = 'users';

    // =========================================
    // CREATE USER
    // =========================================
    public function createAccount(
        string $avatar, 
        string $fullname, 
        string $email, 
        string $contact, 
        string $country, 
        string $bio, 
        string $password, 
        string $role, 
        string $status
    ): bool {

        return $this->query()
            ->insert([
                'avatar'        => $avatar,
                'fullname'      => $fullname,
                'email'         => $email,
                'contact'       => $contact,
                'country'       => $country,
                'bio'           => $bio,
                'user_password' => $password,
                'user_role'     => $role,
                'user_status'   => $status
            ]);
    }

    // =========================================
    // FIND USER BY EMAIL
    // =========================================
    public function findByEmail(
        string $email
    ): ?array {

        return $this->query()
            ->where('email', '=', $email)
            ->first();
    }

    // =========================================
    // FIND USER BY ID
    // =========================================   
    public function findById(
        int $userId
    ): ?array {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->first();
    }

    // =========================================
    // UPDATE PASSWORD
    // =========================================
    public function updatePassword(
        string $email, 
        string $password
    ): bool {

        return $this->query()
            ->where('email', '=', $email)
            ->update(['user_password' => $password]);
    }

    // =========================================
    // UPDATE PROFILE
    // =========================================
    public function updateProfile(
        string $profile, 
        int $userId
    ): bool {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->update(['avatar' => $profile]);
    }

    // =========================================
    // UPDATE DETAILS
    // =========================================
    public function updateDetails(
        string $bio, 
        int $userId
    ): bool {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->update(['bio' => $bio]);
    }

    // =========================================
    // UPDATE SOCIALS
    // =========================================
    public function updateSocials(
        string $facebook, 
        string $instagram, 
        string $tiktok, 
        string $twitter, 
        int $userId
    ): bool {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->update([
                'facebook' => $facebook,
                'instagram' => $instagram,
                'tiktok' => $tiktok,
                'twitter' => $twitter
            ]);
    }

    // =========================================
    // UPDATE ROLE
    // =========================================
    public function updateRole(
        string $email, 
        string $role
    ): bool {

        return $this->query()
            ->where('email', '=', $email)
            ->update(['user_role' => $role]);
    }

    // =========================================
    // GET ALL USERS BY ROLE
    // =========================================
    public function allByRole(
        string $role
    ): ?array {

        return $this->query()
            ->where('user_role', '=', $role)
            ->orderBy('fullname', 'ASC')
            ->get();
    }

    // =========================================
    // COUNT UNREAD NOTIFICATIONS BY TYPE
    // =========================================
    public function getTopLeaders(): ?array
    {

        return $this->query()
            ->where('rank_type', '=', 'Top')
            ->get();
    }

    // =========================================
    // GET LEADER RANK
    // =========================================
    public function getLeaderRank(
        int $userId
    ): ?string {

        $result = $this->executeQuery(
            "SELECT rank_type FROM leader_rank WHERE user_id = ?",
            [$userId]
        );

        return $result ? $result['rank_type'] : null;
    }

    // =========================================
    // GET PROFILE BY USER ID
    // =========================================
    public function getProfile(
        int $userId
    ): ?string {

        $result = $this->query()
            ->select(['avatar'])
            ->where('user_id', '=', $userId)
            ->first();

        return $result ? $result['avatar'] : null;
    }

    // =========================================
    // GET USER DETAILS BY ROLE
    // =========================================
    public function getByRole(
        ?string $role = null, 
        int $page = 1, 
        int $limit = 20
    ): ?array {

        $users = $this->query()
            ->when(
                $role &&
                in_array($role, [
                    'Admin', 
                    'User', 
                    'Leader', 
                    'Dermatologist', 
                    'Doctor', 
                    'Lawyer', 
                    'Freelancer', 
                    'Therapist', 
                    'Vendor', 
                    'Worker'
                ]),

                fn($query) =>
                    $query->where('user_role', '=', $role)
            )
            ->orderBy('fullname', 'ASC')
            ->paginate($page, $limit)
            ->get();

        $total = $this->query()
            ->where('user_role', '=', $role)
            ->count();

        return $this->format($users, $total, $page, $limit);
    }

    // =========================================
    // UPDATE USER STATUS
    // =========================================
    public function updateStatus(
        string $status, 
        int $userId
    ): bool {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->update(['user_status' => $status]);
    }

    // =========================================
    // GET USER SOCIALS
    // =========================================
    public function getSocials(
        int $userId
    ): ?array {

        return $this->query()
            ->select([
                'facebook',
                'instagram',
                'tiktok',
                'twitter'
            ])
            ->where('user_id', '=', $userId)
            ->first();
    }

    // =========================================
    // COUNT ALL ROLES
    // =========================================
    public function countAllRoles(): ?array
    {
        // Define all possible roles
        $roles = [
            'Admin', 
            'User', 
            'Leader', 
            'Dermatologist', 
            'Doctor', 
            'Lawyer', 
            'Freelancer', 
            'Therapist', 
            'Vendor', 
            'Worker'
        ];

        // Query counts from DB
        $rows = $this->queryAll(
            "SELECT user_role, COUNT(*) AS total 
            FROM {$this->table}
            GROUP BY user_role",
            []
        );

        // Initialize all roles with zero
        $counts = array_fill_keys($roles, 0);

        // Overwrite with actual counts from DB
        foreach ($rows as $row) {
            $counts[$row['user_role']] = (int) $row['total'];
        }

        return $counts;
    }

    // =========================================
    // DELETE USER
    // =========================================
    public function deleteUser(
        int $userId
    ): bool {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->delete();
    }

    // =========================================
    // TRACK REFERRAL
    // =========================================
    public function trackReferral(
        int $facilitatorId, 
        int $userId, 
        string $condition
    ): bool {
        // Format current month and year
        $month = date('F'); // e.g., "July"
        $year  = date('Y');  // e.g., "2025"

        if ($condition === 'all') {

            return $this->executeQuery(
                "INSERT INTO membership_referral_track (facilitator_id, user_id, track_month, track_year) VALUES (?, ?, ?, ?)",
                [$facilitatorId, $userId, $month, $year]
            );
        } else {
            if (!in_array((int)$facilitatorId, [1, 2, 3])) {

                return $this->executeQuery(
                    "INSERT INTO membership_referral_track (facilitator_id, user_id, track_month, track_year) VALUES (?, ?, ?, ?)",
                    [$facilitatorId, $userId, $month, $year]
                );
            }
        }
    }

    // =========================================
    // ACTIVATE ACCOUNT
    // =========================================
    public function activateAccount(
        int $userId
    ): bool {

        return $this->query()
            ->where('user_id', '=', $userId)
            ->update(['user_status' => 'Active']);
    }

    // =========================================
    // RESULT PAGINATION HELPER
    // =========================================
    private function format(
        array $data, 
        int $total, 
        int $page, 
        int $limit
    ): ?array {
        return [
            'users'       => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $limit,
            'total_pages' => ceil($total / $limit),
        ];
    }
}
