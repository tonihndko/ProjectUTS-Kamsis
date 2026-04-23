<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'Username dan password wajib diisi.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');

        if ($stmt) {
            $stmt->bind_param('ss', $username, $passwordHash);

            if ($stmt->execute()) {
                $message = 'Data berhasil disimpan.';
            } else {
                $message = 'Gagal menyimpan data: ' . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = 'Query tidak valid: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Username dan Password</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f3f7ff, #e8f8f2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 22px;
            color: #1f2937;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            background: #0ea5e9;
            color: #ffffff;
        }

        button:hover {
            background: #0284c7;
        }

        .message {
            margin-bottom: 14px;
            padding: 10px;
            border-radius: 8px;
            background: #ecfeff;
            color: #0f766e;
            border: 1px solid #a5f3fc;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Form Input User</h1>

        <?php if ($message !== ''): ?>
            <div class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" maxlength="50" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="6" required>

            <button type="submit">Simpan</button>
        </form>
    </div>
</body>
</html>
