<?php
require_once 'config.php';
$pageTitle = 'Admin Login';
$error = '';

if (isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/admin-dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please enter both username and password.';
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
            $error = 'Invalid username or password.';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e, #0a0a1a);
            padding: 20px;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8rem;
            font-weight: 900;
        }
        .login-logo .renew { color: #e8491d; }
        .login-logo .empire { color: #1a1a2e; }
        .login-logo p { font-size: 0.85rem; color: #777; font-weight: 400; margin-top: 5px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: #333; }
        .form-group input {
            width: 100%; padding: 14px 16px; border: 2px solid #e9ecef; border-radius: 8px;
            font-size: 0.95rem; font-family: 'Inter', sans-serif; transition: all 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #e8491d; box-shadow: 0 0 0 3px rgba(232,73,29,0.1); }
        .btn-login {
            width: 100%; padding: 14px; border: none; border-radius: 8px;
            background: linear-gradient(135deg, #e8491d, #f58634); color: #fff;
            font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(232,73,29,0.4); }
        .error { background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #f5c6cb; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #777; font-size: 0.85rem; text-decoration: none; }
        .back-link a:hover { color: #e8491d; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <span class="renew">RENEW</span><span class="empire">EMPIRE</span>
            <p>Admin Panel Login</p>
        </div>
        <?php if ($error): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="username" placeholder="Enter username or email" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-login">Sign In <i class="fas fa-arrow-right"></i></button>
        </form>
        <div class="back-link">
            <a href="<?= SITE_URL ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
        </div>
    </div>
</body>
</html>
