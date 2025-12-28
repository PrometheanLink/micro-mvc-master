<?php
/**
 * Admin Nature Watch Model
 * Provides data for the Nature Watch admin/moderation panel
 */

// Role constants
define('ROLE_BANNED', 0);
define('ROLE_REGISTERED', 1);
define('ROLE_TRUSTED', 2);
define('ROLE_MODERATOR', 5);
define('ROLE_ADMIN', 10);

class AdminNatureWatch_Model
{
    public static function Get_Data()
    {
        // Check authentication
        $auth = UTIL::Get_Session_Variable('auth');
        $is_authenticated = !empty($auth) && !empty($auth['login']);
        $user = $is_authenticated ? $auth['user'] : null;

        // Check if user has moderation access
        $has_access = $is_authenticated && intval($user['role']) >= ROLE_MODERATOR;
        $is_admin = $is_authenticated && intval($user['role']) >= ROLE_ADMIN;

        return [
            'title' => 'Nature Watch Admin',
            'subtitle' => 'Moderation & User Management',
            'phoenix' => true,
            'is_authenticated' => $is_authenticated,
            'has_access' => $has_access,
            'is_admin' => $is_admin,
            'user' => $user,
            'stats' => $has_access ? self::Get_Stats() : null,
            'pending_sightings' => $has_access ? self::Get_Pending_Sightings(10) : [],
            'role_options' => self::Get_Role_Options()
        ];
    }

    /**
     * Get dashboard statistics
     */
    private static function Get_Stats()
    {
        $db_conn = DB::Use_Connection();
        if (empty($db_conn)) {
            return self::Get_Default_Stats();
        }

        $stats = [];

        // Pending count
        $pending = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE status = 'pending'");
        $stats['pending'] = $pending ? intval($pending[0]['count']) : 0;

        // Today's submissions
        $today = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE DATE(created_at) = CURDATE()");
        $stats['today'] = $today ? intval($today[0]['count']) : 0;

        // Flagged count
        $flagged = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE status = 'flagged'");
        $stats['flagged'] = $flagged ? intval($flagged[0]['count']) : 0;

        // Total users
        $users = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM users WHERE status != 'banned'");
        $stats['users'] = $users ? intval($users[0]['count']) : 0;

        // Total approved
        $approved = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE status = 'approved'");
        $stats['approved'] = $approved ? intval($approved[0]['count']) : 0;

        // Rejected this week
        $rejected = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE status = 'rejected' AND moderated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['rejected_week'] = $rejected ? intval($rejected[0]['count']) : 0;

        return $stats;
    }

    /**
     * Default stats when DB is unavailable
     */
    private static function Get_Default_Stats()
    {
        return [
            'pending' => 0,
            'today' => 0,
            'flagged' => 0,
            'users' => 0,
            'approved' => 0,
            'rejected_week' => 0
        ];
    }

    /**
     * Get pending sightings for the queue
     */
    private static function Get_Pending_Sightings($limit = 10)
    {
        $db_conn = DB::Use_Connection();
        if (empty($db_conn)) {
            return [];
        }

        $query = "SELECT s.*, u.username, u.email, u.role as user_role, u.approved_count
                  FROM wildlife_sightings s
                  LEFT JOIN users u ON s.user_id = u.id
                  WHERE s.status = 'pending'
                  ORDER BY s.created_at DESC
                  LIMIT " . intval($limit);

        $result = DB::Exec_SQL_Command($query);
        return $result ?: [];
    }

    /**
     * Get role options for dropdowns
     */
    private static function Get_Role_Options()
    {
        return [
            ROLE_BANNED => 'Banned',
            ROLE_REGISTERED => 'Registered',
            ROLE_TRUSTED => 'Trusted',
            ROLE_MODERATOR => 'Moderator',
            ROLE_ADMIN => 'Admin'
        ];
    }

    /**
     * Get role name from code
     */
    public static function Get_Role_Name($role)
    {
        $roles = self::Get_Role_Options();
        return $roles[$role] ?? 'Unknown';
    }

    /**
     * Get role badge color
     */
    public static function Get_Role_Color($role)
    {
        switch ($role) {
            case ROLE_BANNED: return '#ff4466';
            case ROLE_REGISTERED: return '#888888';
            case ROLE_TRUSTED: return '#00d4ff';
            case ROLE_MODERATOR: return '#ffaa00';
            case ROLE_ADMIN: return '#00ff88';
            default: return '#888888';
        }
    }
}
?>
