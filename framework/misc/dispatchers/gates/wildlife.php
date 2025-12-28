<?php
    /*
        Wildlife Gate - CRUD operations for Nature Watch sightings

        File name: wildlife.php
        Description: AJAX gate for wildlife sighting management

        Coded by Claude AI for PHOENIX Nature Watch
        Copyright (C) 2025
        Open Software License (OSL 3.0)
    */

    // Check for direct access
    if (!defined('micro_mvc'))
        exit();

    header('Content-Type: application/json; charset=utf-8');

    // Role constants
    if (!defined('ROLE_BANNED')) define('ROLE_BANNED', 0);
    if (!defined('ROLE_REGISTERED')) define('ROLE_REGISTERED', 1);
    if (!defined('ROLE_TRUSTED')) define('ROLE_TRUSTED', 2);
    if (!defined('ROLE_MODERATOR')) define('ROLE_MODERATOR', 5);
    if (!defined('ROLE_ADMIN')) define('ROLE_ADMIN', 10);

    // Get database connection
    $db_conn = DB::Use_Connection();
    if (empty($db_conn)) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit();
    }

    // Get current user auth info
    $auth = UTIL::Get_Session_Variable('auth');
    $is_authenticated = !empty($auth) && !empty($auth['login']);
    $current_user = $is_authenticated ? $auth['user'] : null;
    $user_role = $current_user ? intval($current_user['role']) : 0;
    $is_moderator = $user_role >= ROLE_MODERATOR;

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action) {
        // ============================================
        // Get all sightings (with optional filters)
        // ============================================
        case 'get_sightings':
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 100;
            $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
            $category = isset($_POST['category']) ? mysqli_real_escape_string($db_conn, $_POST['category']) : '';
            $species = isset($_POST['species']) ? mysqli_real_escape_string($db_conn, $_POST['species']) : '';
            $observer_id = isset($_POST['observer_id']) ? mysqli_real_escape_string($db_conn, $_POST['observer_id']) : '';
            $bounds = isset($_POST['bounds']) ? json_decode($_POST['bounds'], true) : null;

            $where = [];

            // Non-moderators only see approved sightings (or their own pending ones)
            if (!$is_moderator) {
                if (!empty($observer_id)) {
                    // User can see their own pending sightings
                    $where[] = "(status = 'approved' OR (observer_id = '$observer_id'))";
                } else {
                    $where[] = "status = 'approved'";
                }
            }

            if (!empty($category) && $category !== 'all') {
                $where[] = "species_category = '$category'";
            }
            if (!empty($species)) {
                $where[] = "species LIKE '%$species%'";
            }
            if (!empty($observer_id) && $is_moderator) {
                // Only filter by observer_id for mods, regular users get it in the status filter above
                $where[] = "observer_id = '$observer_id'";
            }
            if ($bounds && isset($bounds['north'], $bounds['south'], $bounds['east'], $bounds['west'])) {
                $where[] = "latitude BETWEEN " . floatval($bounds['south']) . " AND " . floatval($bounds['north']);
                $where[] = "longitude BETWEEN " . floatval($bounds['west']) . " AND " . floatval($bounds['east']);
            }

            $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $query = "SELECT * FROM wildlife_sightings $where_clause ORDER BY sighting_date DESC LIMIT $offset, $limit";
            $result = DB::Exec_SQL_Command($query);

            echo json_encode([
                'success' => true,
                'data' => $result ?: []
            ]);
            break;

        // ============================================
        // Get single sighting by ID
        // ============================================
        case 'get_sighting':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid sighting ID']);
                break;
            }

            $query = "SELECT * FROM wildlife_sightings WHERE id = $id LIMIT 1";
            $result = DB::Exec_SQL_Command($query);

            if (!empty($result)) {
                echo json_encode(['success' => true, 'data' => $result[0]]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Sighting not found']);
            }
            break;

        // ============================================
        // Add new sighting
        // ============================================
        case 'add_sighting':
            // Required fields
            $species = isset($_POST['species']) ? mysqli_real_escape_string($db_conn, trim($_POST['species'])) : '';
            $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
            $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;

            if (empty($species) || $latitude == 0 || $longitude == 0) {
                echo json_encode(['success' => false, 'error' => 'Species and location are required']);
                break;
            }

            // Optional fields
            $category = isset($_POST['category']) ? mysqli_real_escape_string($db_conn, $_POST['category']) : 'other';
            $photo_path = isset($_POST['photo_path']) ? mysqli_real_escape_string($db_conn, $_POST['photo_path']) : '';
            $notes = isset($_POST['notes']) ? mysqli_real_escape_string($db_conn, $_POST['notes']) : '';
            $location_name = isset($_POST['location_name']) ? mysqli_real_escape_string($db_conn, $_POST['location_name']) : '';
            $observer_name = isset($_POST['observer_name']) ? mysqli_real_escape_string($db_conn, $_POST['observer_name']) : 'Anonymous';
            $observer_id = isset($_POST['observer_id']) ? mysqli_real_escape_string($db_conn, $_POST['observer_id']) : '';
            $sighting_date = isset($_POST['sighting_date']) ? mysqli_real_escape_string($db_conn, $_POST['sighting_date']) : date('Y-m-d H:i:s');

            // Determine status based on user trust level
            $status = 'pending'; // Default for anonymous users
            $user_id = null;

            if ($is_authenticated) {
                $user_id = intval($current_user['id']);

                // Check if user is verified (required to submit)
                $user_check = DB::Exec_SQL_Command("SELECT email_verified, approved_count, role FROM users WHERE id = $user_id");
                if (!empty($user_check)) {
                    $user_data = $user_check[0];

                    // Must be email verified to submit
                    if (!$user_data['email_verified']) {
                        echo json_encode(['success' => false, 'error' => 'Please verify your email before submitting sightings']);
                        break;
                    }

                    // Determine auto-approve status
                    if ($user_data['role'] >= ROLE_TRUSTED) {
                        // Trusted users and above auto-approve
                        $status = 'approved';
                    } else if ($user_data['approved_count'] >= 3) {
                        // Users with 3+ approved submissions auto-approve
                        $status = 'approved';
                        // Also promote them to trusted
                        DB::Exec_SQL_Command("UPDATE users SET role = " . ROLE_TRUSTED . " WHERE id = $user_id AND role < " . ROLE_TRUSTED);
                    }
                    // Otherwise stays 'pending'
                }
            }

            $query = "INSERT INTO wildlife_sightings
                (species, species_category, photo_path, notes, latitude, longitude, location_name, observer_name, observer_id, user_id, status, sighting_date)
                VALUES (
                    '$species',
                    '$category',
                    " . ($photo_path ? "'$photo_path'" : "NULL") . ",
                    " . ($notes ? "'$notes'" : "NULL") . ",
                    $latitude,
                    $longitude,
                    " . ($location_name ? "'$location_name'" : "NULL") . ",
                    '$observer_name',
                    " . ($observer_id ? "'$observer_id'" : "NULL") . ",
                    " . ($user_id ? "$user_id" : "NULL") . ",
                    '$status',
                    '$sighting_date'
                )";

            $result = DB::Exec_SQL_Command($query);

            if ($result !== false) {
                // Get the inserted ID
                $id_query = "SELECT LAST_INSERT_ID() as id";
                $id_result = DB::Exec_SQL_Command($id_query);
                $new_id = $id_result ? $id_result[0]['id'] : 0;

                $message = $status === 'approved'
                    ? 'Sighting recorded and published!'
                    : 'Sighting recorded! It will appear after moderation.';

                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'id' => $new_id,
                    'status' => $status
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save sighting']);
            }
            break;

        // ============================================
        // Delete sighting
        // ============================================
        case 'delete_sighting':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $observer_id = isset($_POST['observer_id']) ? mysqli_real_escape_string($db_conn, $_POST['observer_id']) : '';

            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid sighting ID']);
                break;
            }

            // Only allow deletion by the original observer
            $where = "id = $id";
            if (!empty($observer_id)) {
                $where .= " AND observer_id = '$observer_id'";
            }

            $query = "DELETE FROM wildlife_sightings WHERE $where LIMIT 1";
            $result = DB::Exec_SQL_Command($query);

            echo json_encode([
                'success' => $result !== false,
                'message' => $result !== false ? 'Sighting deleted' : 'Failed to delete'
            ]);
            break;

        // ============================================
        // Get species statistics
        // ============================================
        case 'get_stats':
            // Only count approved sightings for public stats
            $status_filter = $is_moderator ? "" : " WHERE status = 'approved'";
            $status_and = $is_moderator ? "" : " AND status = 'approved'";

            // Total count
            $total_query = "SELECT COUNT(*) as total FROM wildlife_sightings" . $status_filter;
            $total_result = DB::Exec_SQL_Command($total_query);
            $total = $total_result ? intval($total_result[0]['total']) : 0;

            // By category
            $category_query = "SELECT species_category, COUNT(*) as count FROM wildlife_sightings" . $status_filter . " GROUP BY species_category ORDER BY count DESC";
            $category_result = DB::Exec_SQL_Command($category_query);

            // Top species
            $species_query = "SELECT species, species_category, COUNT(*) as count FROM wildlife_sightings" . $status_filter . " GROUP BY species, species_category ORDER BY count DESC LIMIT 10";
            $species_result = DB::Exec_SQL_Command($species_query);

            // Recent activity (last 30 days by day)
            $activity_query = "SELECT DATE(sighting_date) as date, COUNT(*) as count FROM wildlife_sightings WHERE sighting_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $status_and . " GROUP BY DATE(sighting_date) ORDER BY date";
            $activity_result = DB::Exec_SQL_Command($activity_query);

            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'by_category' => $category_result ?: [],
                    'top_species' => $species_result ?: [],
                    'activity' => $activity_result ?: []
                ]
            ]);
            break;

        // ============================================
        // Get nearby sightings
        // ============================================
        case 'get_nearby':
            $lat = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
            $lng = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;
            $radius = isset($_POST['radius']) ? floatval($_POST['radius']) : 5; // km
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;

            if ($lat == 0 || $lng == 0) {
                echo json_encode(['success' => false, 'error' => 'Location required']);
                break;
            }

            // Haversine formula for distance calculation
            $query = "SELECT *,
                (6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude)))) AS distance
                FROM wildlife_sightings
                HAVING distance < $radius
                ORDER BY distance
                LIMIT $limit";

            $result = DB::Exec_SQL_Command($query);

            echo json_encode([
                'success' => true,
                'data' => $result ?: []
            ]);
            break;

        // ============================================
        // Get species list for autocomplete
        // ============================================
        case 'get_species_list':
            $query = "SELECT DISTINCT species, species_category FROM wildlife_sightings ORDER BY species";
            $result = DB::Exec_SQL_Command($query);

            echo json_encode([
                'success' => true,
                'data' => $result ?: []
            ]);
            break;

        // ============================================
        // Default: Invalid action
        // ============================================
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
?>
