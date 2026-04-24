<?php
// ========== Security Headers ==========
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com");

// ========== Database Configuration ==========
$host = 'localhost';
$dbname = 'web_kamsis';
$username = 'root';
$password = '';

// ========== Input Validation Constants ==========
define('MAX_USERNAME_LENGTH', 50);
define('MAX_PASSWORD_LENGTH', 255);
define('MIN_PASSWORD_LENGTH', 6);
define('MAX_FAILED_ATTEMPTS', 5);
define('ATTEMPT_TIMEOUT', 900); // 15 minutes

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false // Prevent SQL injection
    ]);
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Koneksi database gagal. Silakan hubungi administrator.");
}

// ========== Security Functions ==========

/**
 * Validasi dan sanitasi username
 */
function validateUsername($username) {
    $username = trim($username);
    
    // Check length
    if (strlen($username) === 0 || strlen($username) > MAX_USERNAME_LENGTH) {
        return ['valid' => false, 'error' => 'Username harus 1-' . MAX_USERNAME_LENGTH . ' karakter'];
    }
    
    // Allow alphanumeric, underscore, and hyphen only
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        return ['valid' => false, 'error' => 'Username hanya boleh alfanumerik, underscore, dan hyphen'];
    }
    
    return ['valid' => true, 'username' => $username];
}

/**
 * Validasi password
 */
function validatePassword($password) {
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        return ['valid' => false, 'error' => 'Password minimal ' . MIN_PASSWORD_LENGTH . ' karakter'];
    }
    
    if (strlen($password) > MAX_PASSWORD_LENGTH) {
        return ['valid' => false, 'error' => 'Password terlalu panjang'];
    }
    
    return ['valid' => true, 'password' => $password];
}

/**
 * Sanitize output untuk prevent XSS
 */
function sanitizeOutput($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Check brute force attempts
 */
function checkBruteForce($username) {
    $attempt_file = sys_get_temp_dir() . '/login_attempts_' . md5($username) . '.txt';
    
    if (file_exists($attempt_file)) {
        $data = json_decode(file_get_contents($attempt_file), true);
        $now = time();
        
        // Reset jika timeout sudah lewat
        if ($now - $data['timestamp'] > ATTEMPT_TIMEOUT) {
            unlink($attempt_file);
            return true;
        }
        
        // Check apakah sudah exceed max attempts
        if ($data['attempts'] >= MAX_FAILED_ATTEMPTS) {
            return false;
        }
    }
    
    return true;
}

/**
 * Record failed login attempt
 */
function recordFailedAttempt($username) {
    $attempt_file = sys_get_temp_dir() . '/login_attempts_' . md5($username) . '.txt';
    
    if (file_exists($attempt_file)) {
        $data = json_decode(file_get_contents($attempt_file), true);
        $data['attempts']++;
    } else {
        $data = ['attempts' => 1, 'timestamp' => time()];
    }
    
    file_put_contents($attempt_file, json_encode($data));
}

/**
 * Clear failed login attempts
 */
function clearFailedAttempts($username) {
    $attempt_file = sys_get_temp_dir() . '/login_attempts_' . md5($username) . '.txt';
    if (file_exists($attempt_file)) {
        unlink($attempt_file);
    }
}
?>