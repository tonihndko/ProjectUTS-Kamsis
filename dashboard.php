<?php
session_start();
require_once "config.php";

// ========== SESSION SECURITY ==========
// Check session timeout (30 minutes)
$session_timeout = 1800;
if (isset($_SESSION["login_time"]) && (time() - $_SESSION["login_time"]) > $session_timeout) {
    session_destroy();
    header("Location: index.php?message=session_expired");
    exit;
}

// Check apakah user sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

// Regenerate session ID untuk prevent session fixation
if (empty($_SESSION["regenerated"])) {
    session_regenerate_id(true);
    $_SESSION["regenerated"] = true;
}

$username = sanitizeOutput($_SESSION["username"] ?? "User");
$user_id = intval($_SESSION["user_id"]);
$login_time = date("d/m/Y H:i:s", $_SESSION["login_time"] ?? time());
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Dashboard Keamanan - Web Kamsis">
    <title>Dashboard - Web Kamsis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #1a2a3a 50%, #24243e 100%);
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background orbs */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 10s ease-in-out infinite;
            z-index: 0;
        }
        body::before {
            width: 450px;
            height: 450px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            top: -120px;
            right: -120px;
        }
        body::after {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            bottom: -100px;
            left: -100px;
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        .page-wrapper {
            max-width: 640px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header-title {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 14px rgba(17, 153, 142, 0.35);
        }
        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: rgba(248, 113, 113, 0.15);
            color: #fca5a5;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: 1px solid rgba(248, 113, 113, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .btn-logout:hover {
            background: rgba(248, 113, 113, 0.25);
            border-color: rgba(248, 113, 113, 0.4);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(248, 113, 113, 0.15);
        }

        /* Welcome Card */
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card:nth-child(2) { animation-delay: 0.1s; }
        .card:nth-child(3) { animation-delay: 0.2s; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome-card {
            text-align: center;
            padding: 40px 32px;
        }
        .welcome-avatar {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(17, 153, 142, 0.3);
        }
        .welcome-text h2 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .welcome-text p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 28px;
        }
        .stat-item {
            background: rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 18px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .stat-label {
            color: rgba(255, 255, 255, 0.4);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }
        .stat-value {
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }
        .stat-value.online {
            color: #6ee7b7;
        }

        /* Security features card */
        .section-title {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }
        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .feature-icon.green {
            background: rgba(52, 211, 153, 0.15);
        }
        .feature-name {
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 500;
        }
        .feature-desc {
            color: rgba(255, 255, 255, 0.4);
            font-size: 11.5px;
            margin-top: 2px;
        }
        .feature-badge {
            margin-left: auto;
            padding: 4px 10px;
            background: rgba(52, 211, 153, 0.12);
            color: #6ee7b7;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 24px;
            color: rgba(255, 255, 255, 0.25);
            font-size: 12px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            animation-delay: 0.3s;
            animation-fill-mode: both;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .header { flex-direction: column; gap: 16px; align-items: flex-start; }
            .card { padding: 24px; }
            .welcome-card { padding: 28px 20px; }
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
        <div class="header">
            <div class="header-title">
                <span class="header-icon">&#x1F6E1;</span>
                Dashboard
            </div>
            <a href="logout.php" class="btn-logout" id="btnLogout">
                &#x2190; Keluar
            </a>
        </div>

        <!-- Welcome Card -->
        <div class="card welcome-card">
            <div class="welcome-avatar">&#x1F44B;</div>
            <div class="welcome-text">
                <h2>Selamat Datang, <?php echo $username; ?>!</h2>
                <p>Anda berada di area yang aman</p>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-label">Waktu Login</div>
                    <div class="stat-value"><?php echo $login_time; ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Status</div>
                    <div class="stat-value online">&#x25CF; Online</div>
                </div>
            </div>
        </div>

        <!-- Security Features Card -->
        <div class="card">
            <div class="section-title">
                &#x1F512; Perlindungan Keamanan Aktif
            </div>

            <ul class="features-list">
                <li class="feature-item">
                    <div class="feature-icon green">&#x1F5C4;</div>
                    <div>
                        <div class="feature-name">SQL Injection</div>
                        <div class="feature-desc">Prepared Statements &amp; PDO</div>
                    </div>
                    <span class="feature-badge">Aktif</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon green">&#x1F6E1;</div>
                    <div>
                        <div class="feature-name">Cross-Site Scripting (XSS)</div>
                        <div class="feature-desc">htmlspecialchars &amp; CSP Headers</div>
                    </div>
                    <span class="feature-badge">Aktif</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon green">&#x1F510;</div>
                    <div>
                        <div class="feature-name">CSRF Protection</div>
                        <div class="feature-desc">Token-based Validation</div>
                    </div>
                    <span class="feature-badge">Aktif</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon green">&#x1F6AB;</div>
                    <div>
                        <div class="feature-name">Brute Force Protection</div>
                        <div class="feature-desc">Rate Limiting &amp; Delay</div>
                    </div>
                    <span class="feature-badge">Aktif</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon green">&#x1F504;</div>
                    <div>
                        <div class="feature-name">Session Hijacking</div>
                        <div class="feature-desc">Regeneration &amp; Timeout 30 menit</div>
                    </div>
                    <span class="feature-badge">Aktif</span>
                </li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            Project CLO2 &mdash; Web Kamsis &copy; <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
