<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!empty($_SESSION['admin_id'])) redirect('/admin/dashboard.php');

$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $user = trim($_POST['username']??'');
    $pass = $_POST['password']??'';
    if($user && $pass){
        $stmt = db()->prepare("SELECT * FROM users WHERE username=?");
        $stmt->execute([$user]);
        $u = $stmt->fetch();
        if($u && $u['password']===hashPw($pass)){
            $_SESSION['admin_id'] = $u['id'];
            $_SESSION['admin_username'] = $u['username'];
            $_SESSION['admin_role'] = $u['role'];
            $_SESSION['admin_permissions'] = $u['permissions'] ?? '';
            redirect('/admin/dashboard.php');
        } else { $error = 'Invalid username or password.'; }
    } else { $error = 'Please enter username and password.'; }
}
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — Ukloole</title>
<link rel="icon" href="/assets/images/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
body{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#312e81 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Inter',sans-serif}
.login-card{background:white;border-radius:24px;padding:2.5rem;width:100%;max-width:420px;box-shadow:0 25px 60px rgba(0,0,0,.4)}
.login-logo{text-align:center;margin-bottom:1.5rem}
.login-logo img{height:52px}
.login-logo h2{font-family:Poppins,sans-serif;font-weight:700;font-size:1.5rem;color:#0f172a;margin:8px 0 4px}
.login-logo p{color:#64748b;font-size:.9rem}
.form-control:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15)}
.btn-login{background:linear-gradient(135deg,#667eea,#764ba2);border:none;color:white;padding:12px;font-weight:700;font-size:1rem;border-radius:12px;width:100%;transition:all .3s}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(102,126,234,.4)}
</style>
</head><body>
<div class="login-card">
    <div class="login-logo">
        <img src="/assets/images/logo.png" alt="Ukloole">
        <h2>Admin Access</h2>
        <p>Sign in to manage the Ukloole platform</p>
    </div>
    <?php if($error): ?><div class="alert alert-danger py-2 mb-3 rounded-3"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label fw-600">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control" placeholder="admin" autofocus required>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-600">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" class="btn-login">Sign In</button>
    </form>
    <div class="text-center mt-3"><a href="/" style="color:#64748b;font-size:.9rem;text-decoration:none"><i class="bi bi-arrow-left me-1"></i>Back to Site</a></div>
</div>
</body></html>
