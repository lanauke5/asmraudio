<?php require __DIR__.'/../includes/bootstrap.php';
if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!$pdo) { $error = 'Database connection is unavailable.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') { $error = 'Enter a valid email and password.'; }
    else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1'); $stmt->execute([$email]); $user = $stmt->fetch();
        $locked = $user && $user['locked_until'] && strtotime($user['locked_until']) > time();
        if ($locked || !$user || !password_verify($password, $user['password_hash'])) {
            if ($user && !$locked) { $attempts = (int)$user['failed_attempts'] + 1; $until = $attempts >= 5 ? date('Y-m-d H:i:s', time()+900) : null; $pdo->prepare('UPDATE users SET failed_attempts=?,locked_until=? WHERE id=?')->execute([$attempts,$until,$user['id']]); }
            log_admin_activity('login_failed','Email attempt'); $error = 'Invalid credentials or temporarily locked account.';
        } else { session_regenerate_id(true); $_SESSION['admin_id']=(int)$user['id']; $_SESSION['admin_name']=$user['display_name']; log_admin_activity('login_success','Administrator signed in'); $pdo->prepare('UPDATE users SET failed_attempts=0,locked_until=NULL,last_login_at=NOW() WHERE id=?')->execute([$user['id']]); header('Location: dashboard.php'); exit; }
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administrator Login</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><main class="container"><section class="card" style="max-width:460px;margin:12vh auto"><h1>Administrator Login</h1><?php if($error):?><p role="alert"><?=e($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><p><label>Email<input type="email" name="email" required autocomplete="username"></label></p><p><label>Password<input type="password" name="password" required autocomplete="current-password"></label></p><button type="submit">Sign in</button></form></section></main></body></html>

