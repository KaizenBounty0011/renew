<?php
require_once __DIR__ . '/../config.php';
$error = '';

if (isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/admin-dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $security_key = trim($_POST['security_key'] ?? '');

    if (!$username || !$password || !$security_key) {
        $error = 'All fields are required.';
    } else {
        // Verify security key
        $storedKey = getSetting('admin_security_key');
        if (!$storedKey || !hash_equals($storedKey, $security_key)) {
            $error = 'Invalid security key.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 'active'");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header('Location: ' . SITE_URL . '/admin/admin-dashboard.php');
                exit;
            } else {
                $error = 'Invalid credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Renew Empire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0f23, #1a1a2e, #0a0a1a);
            padding: 20px;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 45px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.8rem;
            font-weight: 900;
        }
        .login-logo .renew { color: #e8491d; }
        .login-logo .empire { color: #1a1a2e; }
        .login-subtitle {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-subtitle span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f2f5;
            color: #555;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .login-subtitle span i { color: #e8491d; }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #333;
        }
        .form-group label i { color: #999; font-size: 0.8rem; }
        .form-group input {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #e8491d;
            box-shadow: 0 0 0 3px rgba(232,73,29,0.1);
        }
        .security-field input {
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            font-weight: 600;
        }
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: #ccc;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e9ecef;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #e8491d, #f58634);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(232,73,29,0.4);
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            border: 1px solid #f5c6cb;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #999;
            font-size: 0.82rem;
            text-decoration: none;
        }
        .back-link a:hover { color: #e8491d; }
        .security-note {
            text-align: center;
            margin-top: 15px;
            font-size: 0.72rem;
            color: #bbb;
        }
        .security-note i { margin-right: 4px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <span class="renew">RENEW</span><span class="empire">EMPIRE</span>
        </div>
        <div class="login-subtitle">
            <span><i class="fas fa-shield-alt"></i> Secure Admin Access</span>
        </div>

        <?php if ($error): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username or Email</label>
                <input type="text" name="username" placeholder="Enter username or email" required autofocus>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>

            <div class="divider">Security Verification</div>

            <div class="form-group security-field">
                <label><i class="fas fa-key"></i> Security Key</label>
                <input type="password" name="security_key" placeholder="Enter security key" required>
            </div>

            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Sign In</button>
        </form>

        <div class="back-link">
            <a href="<?= SITE_URL ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
        </div>
        <div class="security-note">
            <i class="fas fa-lock"></i> This area is restricted to authorized personnel only.
        </div>
    </div>
</body>
</html>
