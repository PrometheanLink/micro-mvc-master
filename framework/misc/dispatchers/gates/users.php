<?php
/*
    Users Gate - Nature Watch User Management

    File name: users.php
    Description: Handles user registration, authentication, and profile management

    Actions:
    - register: Create new account with email verification
    - login: Authenticate user
    - logout: End session
    - verify_email: Verify email with token
    - resend_verification: Resend verification email
    - check_auth: Check if user is logged in
    - get_profile: Get current user profile
    - forgot_password: Request password reset
    - reset_password: Reset password with token
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
if (empty($_POST['action'])) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    return;
}

$db_conn = DB::Use_Connection();
if (empty($db_conn)) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    return;
}

$action = $_POST['action'];

switch ($action) {
    case 'register':
        handle_register($db_conn);
        break;
    case 'login':
        handle_login($db_conn);
        break;
    case 'logout':
        handle_logout();
        break;
    case 'verify_email':
        handle_verify_email($db_conn);
        break;
    case 'resend_verification':
        handle_resend_verification($db_conn);
        break;
    case 'check_auth':
        handle_check_auth();
        break;
    case 'get_profile':
        handle_get_profile($db_conn);
        break;
    case 'forgot_password':
        handle_forgot_password($db_conn);
        break;
    case 'reset_password':
        handle_reset_password($db_conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

/**
 * Register a new user
 */
function handle_register($db_conn) {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($email) || empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
        return;
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        echo json_encode(['success' => false, 'error' => 'Username must be 3-50 characters']);
        return;
    }

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        echo json_encode(['success' => false, 'error' => 'Username can only contain letters, numbers, underscores, and hyphens']);
        return;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
        return;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        return;
    }

    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = '" . mysqli_real_escape_string($db_conn, $email) . "'";
    $result = DB::Exec_SQL_Command($check_email);
    if (!empty($result)) {
        echo json_encode(['success' => false, 'error' => 'Email already registered']);
        return;
    }

    // Check if username already exists
    $check_username = "SELECT id FROM users WHERE username = '" . mysqli_real_escape_string($db_conn, $username) . "'";
    $result = DB::Exec_SQL_Command($check_username);
    if (!empty($result)) {
        echo json_encode(['success' => false, 'error' => 'Username already taken']);
        return;
    }

    // Generate verification token
    $verification_token = bin2hex(random_bytes(32));
    $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Hash password with bcrypt
    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Insert user
    $insert_query = "INSERT INTO users (email, username, password_hash, role, status, email_verified, verification_token, verification_expires, created_at)
                     VALUES (
                         '" . mysqli_real_escape_string($db_conn, $email) . "',
                         '" . mysqli_real_escape_string($db_conn, $username) . "',
                         '" . mysqli_real_escape_string($db_conn, $password_hash) . "',
                         " . ROLE_REGISTERED . ",
                         'pending',
                         0,
                         '" . mysqli_real_escape_string($db_conn, $verification_token) . "',
                         '" . $verification_expires . "',
                         NOW()
                     )";

    $result = DB::Exec_SQL_Command($insert_query);

    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'Registration failed. Please try again.']);
        return;
    }

    // Get the new user ID
    $user_id = mysqli_insert_id($db_conn);

    // Send verification email (placeholder - implement actual email sending)
    $verification_link = get_base_url() . '/en/verify-email?token=' . $verification_token;
    $email_sent = send_verification_email($email, $username, $verification_link);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please check your email to verify your account.',
        'user_id' => $user_id,
        'verification_required' => true
    ]);
}

/**
 * Login user
 */
function handle_login($db_conn) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        return;
    }

    // Get user by email
    $query = "SELECT * FROM users WHERE email = '" . mysqli_real_escape_string($db_conn, $email) . "'";
    $result = DB::Exec_SQL_Command($query);

    if (empty($result)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        return;
    }

    $user = $result[0];

    // Check password
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        return;
    }

    // Check if banned
    if ($user['role'] == ROLE_BANNED || $user['status'] === 'banned') {
        echo json_encode(['success' => false, 'error' => 'This account has been banned']);
        return;
    }

    // Check if email verified
    if (!$user['email_verified']) {
        echo json_encode([
            'success' => false,
            'error' => 'Please verify your email before logging in',
            'verification_required' => true,
            'email' => $user['email']
        ]);
        return;
    }

    // Check if account is suspended
    if ($user['status'] === 'suspended') {
        echo json_encode(['success' => false, 'error' => 'This account has been suspended']);
        return;
    }

    // Update last login
    $update_query = "UPDATE users SET last_login = NOW() WHERE id = " . intval($user['id']);
    DB::Exec_SQL_Command($update_query);

    // Regenerate session ID for security
    session_regenerate_id(true);

    // Set session
    UTIL::Set_Session_Variable('auth', [
        'login' => 1,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'role' => $user['role'],
            'approved_count' => $user['approved_count']
        ],
        'ip' => $_SERVER['REMOTE_ADDR'],
        'agent' => $_SERVER['HTTP_USER_AGENT'],
        'last_activity' => time()
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'role_name' => get_role_name($user['role'])
        ]
    ]);
}

/**
 * Logout user
 */
function handle_logout() {
    $auth = UTIL::Get_Session_Variable('auth');

    if (empty($auth)) {
        echo json_encode(['success' => true, 'message' => 'Already logged out']);
        return;
    }

    session_regenerate_id(true);
    UTIL::Set_Session_Variable('auth', null);

    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

/**
 * Verify email with token
 */
function handle_verify_email($db_conn) {
    $token = trim($_POST['token'] ?? '');

    if (empty($token)) {
        echo json_encode(['success' => false, 'error' => 'Verification token is required']);
        return;
    }

    // Find user with this token
    $query = "SELECT * FROM users
              WHERE verification_token = '" . mysqli_real_escape_string($db_conn, $token) . "'
              AND verification_expires > NOW()";
    $result = DB::Exec_SQL_Command($query);

    if (empty($result)) {
        echo json_encode(['success' => false, 'error' => 'Invalid or expired verification token']);
        return;
    }

    $user = $result[0];

    // Update user as verified
    $update_query = "UPDATE users
                     SET email_verified = 1,
                         status = 'active',
                         verification_token = NULL,
                         verification_expires = NULL
                     WHERE id = " . intval($user['id']);
    DB::Exec_SQL_Command($update_query);

    echo json_encode([
        'success' => true,
        'message' => 'Email verified successfully! You can now log in.'
    ]);
}

/**
 * Resend verification email
 */
function handle_resend_verification($db_conn) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        return;
    }

    // Find user
    $query = "SELECT * FROM users WHERE email = '" . mysqli_real_escape_string($db_conn, $email) . "'";
    $result = DB::Exec_SQL_Command($query);

    if (empty($result)) {
        // Don't reveal if email exists
        echo json_encode(['success' => true, 'message' => 'If this email is registered, a verification link will be sent']);
        return;
    }

    $user = $result[0];

    if ($user['email_verified']) {
        echo json_encode(['success' => false, 'error' => 'Email is already verified']);
        return;
    }

    // Generate new token
    $verification_token = bin2hex(random_bytes(32));
    $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $update_query = "UPDATE users
                     SET verification_token = '" . mysqli_real_escape_string($db_conn, $verification_token) . "',
                         verification_expires = '" . $verification_expires . "'
                     WHERE id = " . intval($user['id']);
    DB::Exec_SQL_Command($update_query);

    // Send email
    $verification_link = get_base_url() . '/en/verify-email?token=' . $verification_token;
    send_verification_email($user['email'], $user['username'], $verification_link);

    echo json_encode(['success' => true, 'message' => 'Verification email sent']);
}

/**
 * Check if user is authenticated
 */
function handle_check_auth() {
    $auth = UTIL::Get_Session_Variable('auth');

    if (empty($auth) || empty($auth['login'])) {
        echo json_encode(['authenticated' => false]);
        return;
    }

    echo json_encode([
        'authenticated' => true,
        'user' => $auth['user']
    ]);
}

/**
 * Get user profile
 */
function handle_get_profile($db_conn) {
    $auth = UTIL::Get_Session_Variable('auth');

    if (empty($auth) || empty($auth['login'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        return;
    }

    $user_id = $auth['user']['id'];

    $query = "SELECT id, email, username, role, status, approved_count, created_at, last_login
              FROM users WHERE id = " . intval($user_id);
    $result = DB::Exec_SQL_Command($query);

    if (empty($result)) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
    }

    $user = $result[0];
    $user['role_name'] = get_role_name($user['role']);

    echo json_encode(['success' => true, 'user' => $user]);
}

/**
 * Request password reset
 */
function handle_forgot_password($db_conn) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        return;
    }

    // Always return success to prevent email enumeration
    $query = "SELECT * FROM users WHERE email = '" . mysqli_real_escape_string($db_conn, $email) . "'";
    $result = DB::Exec_SQL_Command($query);

    if (!empty($result)) {
        $user = $result[0];

        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update_query = "UPDATE users
                         SET reset_token = '" . mysqli_real_escape_string($db_conn, $reset_token) . "',
                             reset_expires = '" . $reset_expires . "'
                         WHERE id = " . intval($user['id']);
        DB::Exec_SQL_Command($update_query);

        // Send email
        $reset_link = get_base_url() . '/en/reset-password?token=' . $reset_token;
        send_reset_email($user['email'], $user['username'], $reset_link);
    }

    echo json_encode(['success' => true, 'message' => 'If this email is registered, a password reset link will be sent']);
}

/**
 * Reset password with token
 */
function handle_reset_password($db_conn) {
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Token and password are required']);
        return;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
        return;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        return;
    }

    // Find user with this token
    $query = "SELECT * FROM users
              WHERE reset_token = '" . mysqli_real_escape_string($db_conn, $token) . "'
              AND reset_expires > NOW()";
    $result = DB::Exec_SQL_Command($query);

    if (empty($result)) {
        echo json_encode(['success' => false, 'error' => 'Invalid or expired reset token']);
        return;
    }

    $user = $result[0];

    // Update password
    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $update_query = "UPDATE users
                     SET password_hash = '" . mysqli_real_escape_string($db_conn, $password_hash) . "',
                         reset_token = NULL,
                         reset_expires = NULL
                     WHERE id = " . intval($user['id']);
    DB::Exec_SQL_Command($update_query);

    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
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

/**
 * Helper: Get base URL
 */
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

/**
 * Helper: Send verification email
 * TODO: Implement actual email sending with SMTP
 */
function send_verification_email($email, $username, $link) {
    // For now, log the verification link (implement real email later)
    error_log("VERIFICATION EMAIL to $email: $link");

    // Placeholder for actual email implementation
    // mail($email, 'Verify your Nature Watch account', "Hello $username,\n\nClick here to verify: $link");

    return true;
}

/**
 * Helper: Send password reset email
 * TODO: Implement actual email sending with SMTP
 */
function send_reset_email($email, $username, $link) {
    // For now, log the reset link (implement real email later)
    error_log("PASSWORD RESET EMAIL to $email: $link");

    return true;
}
?>
