<?php
session_start();
require_once 'config.php';

// ========== Only Accept POST Requests ==========
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ========== CSRF Token Validation ==========
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Sesi tidak valid. Silakan coba lagi.';
    header('Location: index.php');
    exit;
}

// Regenerate CSRF token after use (single-use token)
unset($_SESSION['csrf_token']);

// ========== Get & Validate Input ==========
$username_input = $_POST['username'] ?? '';
$password_input = $_POST['password'] ?? '';

// Validate username
$usernameValidation = validateUsername($username_input);
if (!$usernameValidation['valid']) {
    $_SESSION['error'] = $usernameValidation['error'];
    header('Location: index.php');
    exit;
}
$username = $usernameValidation['username'];

// Validate password
$passwordValidation = validatePassword($password_input);
if (!$passwordValidation['valid']) {
    $_SESSION['error'] = $passwordValidation['error'];
    header('Location: index.php');
    exit;
}
$password = $passwordValidation['password'];

// ========== Brute Force Protection ==========
if (!checkBruteForce($username)) {
    $_SESSION['error'] = 'Terlalu banyak percobaan login gagal. Silakan tunggu 15 menit.';
    header('Location: index.php');
    exit;
}

// ========== Check if User Exists ==========
try {
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        // ========== LOGIN FLOW ==========
        if (password_verify($password, $user['password'])) {
            // Password benar — login berhasil
            clearFailedAttempts($username);

            // Regenerate session ID untuk prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_time'] = time();
            $_SESSION['regenerated'] = true;

            header('Location: dashboard.php');
            exit;
        } else {
            // Password salah
            recordFailedAttempt($username);
            $_SESSION['error'] = 'Password salah. Silakan coba lagi.';
            header('Location: index.php');
            exit;
        }
    } else {
        // ========== REGISTER FLOW ==========
        // Username belum terdaftar — buat akun baru
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute([
            'username' => $username,
            'password' => $hashedPassword
        ]);

        $_SESSION['success'] = 'Akun berhasil dibuat! Silakan login dengan username dan password Anda.';
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Database Error in proses.php: " . $e->getMessage());
    $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
    header('Location: index.php');
    exit;
}
?>
