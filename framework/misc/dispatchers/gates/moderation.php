<?php
/*
    Moderation Gate - Nature Watch Content Moderation

    File name: moderation.php
    Description: Handles sighting approval, rejection, and user management

    Actions:
    - get_pending: Get pending sightings queue
    - get_all_sightings: Get all sightings with filters
    - approve: Approve a sighting
    - reject: Reject a sighting with reason
    - flag: Flag a sighting for review
    - get_users: Get all users
    - set_role: Change user role
    - ban_user: Ban a user
    - get_stats: Get dashboard statistics
*/

// Check for direct access
if (!defined('micro_mvc'))
    exit();

// Role constants
define('ROLE_BANNED', 0);
define('ROLE_REGISTERED', 1);
define('ROLE_TRUSTED', 2);
define('ROLE_MODERATOR', 5);
define('ROLE_ADMIN', 10);

// Check action mode
if (empty($_POST['action']) && empty($_GET['action'])) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    return;
}

$action = $_POST['action'] ?? $_GET['action'];

// Check authentication for all moderation actions
$auth = UTIL::Get_Session_Variable('auth');
if (empty($auth) || empty($auth['login'])) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    return;
}

$current_user = $auth['user'];
$user_role = intval($current_user['role']);

// Check if user has moderation privileges
if ($user_role < ROLE_MODERATOR) {
    echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
    return;
}

$db_conn = DB::Use_Connection();
if (empty($db_conn)) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    return;
}

switch ($action) {
    case 'get_pending':
        handle_get_pending($db_conn);
        break;
    case 'get_all_sightings':
        handle_get_all_sightings($db_conn);
        break;
    case 'approve':
        handle_approve($db_conn, $current_user);
        break;
    case 'reject':
        handle_reject($db_conn, $current_user);
        break;
    case 'flag':
        handle_flag($db_conn, $current_user);
        break;
    case 'get_users':
        handle_get_users($db_conn, $user_role);
        break;
    case 'set_role':
        handle_set_role($db_conn, $user_role);
        break;
    case 'ban_user':
        handle_ban_user($db_conn, $user_role);
        break;
    case 'get_stats':
        handle_get_stats($db_conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

/**
 * Get pending sightings for moderation queue
 */
function handle_get_pending($db_conn) {
    $page = intval($_POST['page'] ?? 1);
    $limit = intval($_POST['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    // Get pending sightings with user info
    $query = "SELECT s.*, u.username, u.email, u.role as user_role, u.approved_count
              FROM wildlife_sightings s
              LEFT JOIN users u ON s.user_id = u.id
              WHERE s.status = 'pending'
              ORDER BY s.created_at DESC
              LIMIT $limit OFFSET $offset";

    $sightings = DB::Exec_SQL_Command($query);

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM wildlife_sightings WHERE status = 'pending'";
    $count_result = DB::Exec_SQL_Command($count_query);
    $total = $count_result ? intval($count_result[0]['total']) : 0;

    echo json_encode([
        'success' => true,
        'sightings' => $sightings ?: [],
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

/**
 * Get all sightings with optional filters
 */
function handle_get_all_sightings($db_conn) {
    $page = intval($_POST['page'] ?? 1);
    $limit = intval($_POST['limit'] ?? 20);
    $offset = ($page - 1) * $limit;
    $status = $_POST['status'] ?? '';
    $search = $_POST['search'] ?? '';

    $where = "1=1";

    if ($status && in_array($status, ['pending', 'approved', 'rejected', 'flagged'])) {
        $where .= " AND s.status = '" . mysqli_real_escape_string($db_conn, $status) . "'";
    }

    if ($search) {
        $search = mysqli_real_escape_string($db_conn, $search);
        $where .= " AND (s.species LIKE '%$search%' OR s.notes LIKE '%$search%' OR s.observer_name LIKE '%$search%')";
    }

    $query = "SELECT s.*, u.username, u.email, u.role as user_role,
                     m.username as moderator_name
              FROM wildlife_sightings s
              LEFT JOIN users u ON s.user_id = u.id
              LEFT JOIN users m ON s.moderated_by = m.id
              WHERE $where
              ORDER BY s.created_at DESC
              LIMIT $limit OFFSET $offset";

    $sightings = DB::Exec_SQL_Command($query);

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM wildlife_sightings s WHERE $where";
    $count_result = DB::Exec_SQL_Command($count_query);
    $total = $count_result ? intval($count_result[0]['total']) : 0;

    echo json_encode([
        'success' => true,
        'sightings' => $sightings ?: [],
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

/**
 * Approve a sighting
 */
function handle_approve($db_conn, $current_user) {
    $sighting_id = intval($_POST['sighting_id'] ?? 0);

    if ($sighting_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid sighting ID']);
        return;
    }

    // Get sighting info
    $query = "SELECT * FROM wildlife_sightings WHERE id = $sighting_id";
    $result = DB::Exec_SQL_Command($query);

    if (empty($result)) {
        echo json_encode(['success' => false, 'error' => 'Sighting not found']);
        return;
    }

    $sighting = $result[0];

    // Update sighting status
    $update = "UPDATE wildlife_sightings
               SET status = 'approved',
                   moderated_by = " . intval($current_user['id']) . ",
                   moderated_at = NOW()
               WHERE id = $sighting_id";

    DB::Exec_SQL_Command($update);

    // If sighting has a user, increment their approved count
    if (!empty($sighting['user_id'])) {
        $user_update = "UPDATE users
                        SET approved_count = approved_count + 1
                        WHERE id = " . intval($sighting['user_id']);
        DB::Exec_SQL_Command($user_update);

        // Check if user should be auto-promoted to trusted
        $user_query = "SELECT approved_count, role FROM users WHERE id = " . intval($sighting['user_id']);
        $user_result = DB::Exec_SQL_Command($user_query);

        if (!empty($user_result)) {
            $user = $user_result[0];
            if ($user['approved_count'] >= 3 && $user['role'] < ROLE_TRUSTED) {
                $promote = "UPDATE users SET role = " . ROLE_TRUSTED . " WHERE id = " . intval($sighting['user_id']);
                DB::Exec_SQL_Command($promote);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Sighting approved'
    ]);
}

/**
 * Reject a sighting
 */
function handle_reject($db_conn, $current_user) {
    $sighting_id = intval($_POST['sighting_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    if ($sighting_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid sighting ID']);
        return;
    }

    $update = "UPDATE wildlife_sightings
               SET status = 'rejected',
                   moderated_by = " . intval($current_user['id']) . ",
                   moderated_at = NOW(),
                   rejection_reason = '" . mysqli_real_escape_string($db_conn, $reason) . "'
               WHERE id = $sighting_id";

    DB::Exec_SQL_Command($update);

    echo json_encode([
        'success' => true,
        'message' => 'Sighting rejected'
    ]);
}

/**
 * Flag a sighting for further review
 */
function handle_flag($db_conn, $current_user) {
    $sighting_id = intval($_POST['sighting_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    if ($sighting_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid sighting ID']);
        return;
    }

    $update = "UPDATE wildlife_sightings
               SET status = 'flagged',
                   moderated_by = " . intval($current_user['id']) . ",
                   moderated_at = NOW(),
                   rejection_reason = '" . mysqli_real_escape_string($db_conn, $reason) . "'
               WHERE id = $sighting_id";

    DB::Exec_SQL_Command($update);

    echo json_encode([
        'success' => true,
        'message' => 'Sighting flagged for review'
    ]);
}

/**
 * Get all users (admin only)
 */
function handle_get_users($db_conn, $user_role) {
    if ($user_role < ROLE_ADMIN) {
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        return;
    }

    $page = intval($_POST['page'] ?? 1);
    $limit = intval($_POST['limit'] ?? 20);
    $offset = ($page - 1) * $limit;
    $search = $_POST['search'] ?? '';
    $role_filter = $_POST['role'] ?? '';

    $where = "1=1";

    if ($search) {
        $search = mysqli_real_escape_string($db_conn, $search);
        $where .= " AND (username LIKE '%$search%' OR email LIKE '%$search%')";
    }

    if ($role_filter !== '' && is_numeric($role_filter)) {
        $where .= " AND role = " . intval($role_filter);
    }

    $query = "SELECT id, email, username, role, status, email_verified, approved_count, created_at, last_login
              FROM users
              WHERE $where
              ORDER BY created_at DESC
              LIMIT $limit OFFSET $offset";

    $users = DB::Exec_SQL_Command($query);

    // Add role names
    if ($users) {
        foreach ($users as &$user) {
            $user['role_name'] = get_role_name($user['role']);
        }
    }

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM users WHERE $where";
    $count_result = DB::Exec_SQL_Command($count_query);
    $total = $count_result ? intval($count_result[0]['total']) : 0;

    echo json_encode([
        'success' => true,
        'users' => $users ?: [],
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

/**
 * Change user role (admin only)
 */
function handle_set_role($db_conn, $user_role) {
    if ($user_role < ROLE_ADMIN) {
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        return;
    }

    $user_id = intval($_POST['user_id'] ?? 0);
    $new_role = intval($_POST['role'] ?? -1);

    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        return;
    }

    if ($new_role < 0 || $new_role > ROLE_ADMIN) {
        echo json_encode(['success' => false, 'error' => 'Invalid role']);
        return;
    }

    // Prevent demoting yourself
    $auth = UTIL::Get_Session_Variable('auth');
    if ($user_id == $auth['user']['id']) {
        echo json_encode(['success' => false, 'error' => 'Cannot change your own role']);
        return;
    }

    $update = "UPDATE users SET role = $new_role WHERE id = $user_id";
    DB::Exec_SQL_Command($update);

    echo json_encode([
        'success' => true,
        'message' => 'User role updated'
    ]);
}

/**
 * Ban a user (admin only)
 */
function handle_ban_user($db_conn, $user_role) {
    if ($user_role < ROLE_ADMIN) {
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        return;
    }

    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        return;
    }

    // Prevent banning yourself
    $auth = UTIL::Get_Session_Variable('auth');
    if ($user_id == $auth['user']['id']) {
        echo json_encode(['success' => false, 'error' => 'Cannot ban yourself']);
        return;
    }

    $update = "UPDATE users SET role = " . ROLE_BANNED . ", status = 'banned' WHERE id = $user_id";
    DB::Exec_SQL_Command($update);

    echo json_encode([
        'success' => true,
        'message' => 'User banned'
    ]);
}

/**
 * Get dashboard statistics
 */
function handle_get_stats($db_conn) {
    $stats = [];

    // Pending count
    $pending = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE status = 'pending'");
    $stats['pending'] = $pending ? intval($pending[0]['count']) : 0;

    // Today's sightings
    $today = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE DATE(created_at) = CURDATE()");
    $stats['today'] = $today ? intval($today[0]['count']) : 0;

    // Flagged count
    $flagged = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE status = 'flagged'");
    $stats['flagged'] = $flagged ? intval($flagged[0]['count']) : 0;

    // Total users
    $users = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $users ? intval($users[0]['count']) : 0;

    // Approved this week
    $week = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings WHERE status = 'approved' AND moderated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['approved_week'] = $week ? intval($week[0]['count']) : 0;

    // Total sightings
    $total = DB::Exec_SQL_Command("SELECT COUNT(*) as count FROM wildlife_sightings");
    $stats['total_sightings'] = $total ? intval($total[0]['count']) : 0;

    // Recent activity (last 10 moderation actions)
    $activity = DB::Exec_SQL_Command("SELECT s.id, s.species, s.status, s.moderated_at, u.username as moderator
                                       FROM wildlife_sightings s
                                       LEFT JOIN users u ON s.moderated_by = u.id
                                       WHERE s.moderated_at IS NOT NULL
                                       ORDER BY s.moderated_at DESC
                                       LIMIT 10");
    $stats['recent_activity'] = $activity ?: [];

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

/**
 * Helper: Get role name from role code
 */
function get_role_name($role) {
    switch ($role) {
        case ROLE_BANNED: return 'Banned';
        case ROLE_REGISTERED: return 'Registered';
        case ROLE_TRUSTED: return 'Trusted';
        case ROLE_MODERATOR: return 'Moderator';
        case ROLE_ADMIN: return 'Admin';
        default: return 'Unknown';
    }
}
?>
