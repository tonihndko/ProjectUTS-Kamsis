<?php
session_start();

// Log logout
error_log("User logout: " . ($_SESSION['username'] ?? 'Unknown') . " at " . date('Y-m-d H:i:s'));

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete PHPSESSID cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login
header('Location: index.php?message=logout_success');
exit;
?>
