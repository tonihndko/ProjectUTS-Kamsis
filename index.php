<?php
require_once 'config.php';
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle query parameter messages
$message_type = '';
$message_text = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logout_success':
            $message_type = 'success';
            $message_text = 'Anda telah berhasil logout.';
            break;
        case 'session_expired':
            $message_type = 'error';
            $message_text = 'Sesi Anda telah berakhir. Silakan login kembali.';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login dan Registrasi - Sistem Keamanan Web Kamsis">
    <title>Login / Registrasi - Web Kamsis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated background orbs */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 8s ease-in-out infinite;
            z-index: 0;
        }
        body::before {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            top: -100px;
            right: -100px;
        }
        body::after {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            bottom: -80px;
            left: -80px;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        .container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 44px 40px;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.35);
        }
        .logo h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .logo p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            margin-top: 6px;
            font-weight: 400;
        }

        /* Alert messages */
        .alert {
            padding: 14px 16px;
            margin-bottom: 22px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: alertIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes alertIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-success {
            background: rgba(52, 211, 153, 0.15);
            color: #6ee7b7;
            border: 1px solid rgba(52, 211, 153, 0.25);
        }
        .alert-error {
            background: rgba(248, 113, 113, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(248, 113, 113, 0.25);
        }
        .alert-icon {
            font-size: 16px;
            flex-shrink: 0;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            letter-spacing: 0.2px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            opacity: 0.4;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 13px 14px 13px 42px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: #ffffff;
            transition: all 0.3s ease;
            outline: none;
        }
        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: rgba(102, 126, 234, 0.6);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        }
        input:focus ~ .input-icon,
        input:not(:placeholder-shown) ~ .input-icon {
            opacity: 0.7;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 4px;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .btn-submit:hover::before {
            left: 100%;
        }
        .btn-submit:active {
            transform: translateY(0);
        }

        /* Info section */
        .info {
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .info-title {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 14px;
        }
        .info-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .info-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.45);
            font-size: 12.5px;
            line-height: 1.5;
        }
        .info-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            flex-shrink: 0;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <div class="logo-icon">&#x1F512;</div>
            <h1>Web Kamsis</h1>
            <p>Login atau daftar untuk melanjutkan</p>
        </div>

        <?php if ($message_type === 'success' || isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <span class="alert-icon">&#x2714;</span>
                <span><?php 
                    if (isset($_SESSION['success'])) {
                        echo sanitizeOutput($_SESSION['success']);
                        unset($_SESSION['success']);
                    } else {
                        echo sanitizeOutput($message_text);
                    }
                ?></span>
            </div>
        <?php endif; ?>

        <?php if ($message_type === 'error' || isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <span class="alert-icon">&#x2716;</span>
                <span><?php 
                    if (isset($_SESSION['error'])) {
                        echo sanitizeOutput($_SESSION['error']);
                        unset($_SESSION['error']);
                    } else {
                        echo sanitizeOutput($message_text);
                    }
                ?></span>
            </div>
        <?php endif; ?>

        <form action="proses.php" method="POST" novalidate id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        placeholder="Masukkan username"
                        maxlength="50"
                    >
                    <span class="input-icon">&#x1F464;</span>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        maxlength="255"
                    >
                    <span class="input-icon">&#x1F511;</span>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">Login / Daftar</button>
        </form>

        <div class="info">
            <div class="info-title">&#x1F4A1; Catatan</div>
            <ul class="info-list">
                <li><span class="info-dot"></span>Jika username belum terdaftar, akun dibuat otomatis</li>
                <li><span class="info-dot"></span>Username: alfanumerik, underscore, hyphen (1-50 karakter)</li>
                <li><span class="info-dot"></span>Password: minimal 6 karakter</li>
                <li><span class="info-dot"></span>Dilindungi dari SQL Injection, XSS &amp; CSRF</li>
            </ul>
        </div>
    </div>
</body>
</html>