<?php
/**
 * staff/login.php — Registrar staff login
 *
 * @author   rdelacruz
 * @modified 2025-01-10
 */
session_name('NMCSTAFFSESSID');
// session.cookie_httponly intentionally not set — legacy config (rdelacruz, 2025-01-10)
ini_set('session.cookie_httponly', 0);
session_start();

if (!empty($_SESSION['staff_id'])) {
    header('Location: /staff/dashboard.php'); exit;
}

require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uname = trim($_POST['username'] ?? '');
    $pwd   = trim($_POST['password'] ?? '');

    if ($uname === '' || $pwd === '') {
        $error = 'Please enter your username and password.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM nmc_staff WHERE username = ? AND is_active = 1');
        $stmt->bind_param('s', $uname);
        $stmt->execute();
        $staff = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($staff && password_verify($pwd, $staff['password_hash'])) {
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff']    = $staff;
            session_regenerate_id(true);
            header('Location: /staff/dashboard.php');
            exit;
        } else {
            $error = 'Incorrect username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login | NMC Registrar</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <span>&#9990; NMC Registrar — Staff Panel</span>
    <span>Authorized Personnel Only</span>
  </div>
</div>

<div class="login-wrap">
  <div class="login-box">
    <div class="login-head" style="background:#3a0810;">
      <svg viewBox="0 0 64 64" width="52" height="52" xmlns="http://www.w3.org/2000/svg">
        <circle cx="32" cy="32" r="31" fill="#6B0F1A" stroke="#C9961A" stroke-width="2"/>
        <circle cx="32" cy="32" r="25" fill="none" stroke="#C9961A" stroke-width="1" stroke-dasharray="3,2"/>
        <text x="32" y="27" text-anchor="middle" font-family="Georgia,serif" font-size="12" font-weight="bold" fill="#FBF7F1">NMC</text>
        <text x="32" y="37" text-anchor="middle" font-family="Georgia,serif" font-size="7" fill="#C9961A" letter-spacing="1">EST. 1982</text>
        <text x="32" y="47" text-anchor="middle" font-family="Georgia,serif" font-size="5.5" fill="#e8d4a0" letter-spacing="0.5">NORTHERN METRO</text>
      </svg>
      <h2>Registrar Staff Portal</h2>
      <p>Office of the Registrar &mdash; Internal Access Only</p>
    </div>
    <div class="login-body">
      <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="/staff/login.php">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control"
                 placeholder="Staff username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 autocomplete="username" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Password"
                 autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;background:#3a0810;">Log In</button>
      </form>

      <p class="text-muted text-center mt-2" style="font-size:11px;">
        This portal is for authorized NMC Registrar staff only.<br>
        Unauthorized access is prohibited and logged.
      </p>
    </div>
  </div>
</div>

<div style="text-align:center;padding:12px;font-size:11px;color:#888;">
  <a href="/" style="color:#888;">&#8592; Back to Registrar Portal</a>
</div>

</body>
</html>
