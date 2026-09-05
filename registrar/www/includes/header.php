<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title ?? 'Student Portal') ?> | NMC Registrar</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <span>&#9990; Office of the Registrar &nbsp;|&nbsp; Building A, Room 105 &nbsp;|&nbsp; loc. 110</span>
    <span>Mon&ndash;Fri 8:00 AM &ndash; 5:00 PM</span>
  </div>
</div>

<div id="site-header">
  <div class="header-inner">
    <div class="site-logo">
      <svg viewBox="0 0 64 64" width="52" height="52" xmlns="http://www.w3.org/2000/svg">
        <circle cx="32" cy="32" r="31" fill="#6B0F1A" stroke="#C9961A" stroke-width="2"/>
        <circle cx="32" cy="32" r="25" fill="none" stroke="#C9961A" stroke-width="1" stroke-dasharray="3,2"/>
        <text x="32" y="27" text-anchor="middle" font-family="Georgia,serif" font-size="12" font-weight="bold" fill="#FBF7F1">NMC</text>
        <text x="32" y="37" text-anchor="middle" font-family="Georgia,serif" font-size="7" fill="#C9961A" letter-spacing="1">EST. 1982</text>
        <text x="32" y="47" text-anchor="middle" font-family="Georgia,serif" font-size="5.5" fill="#e8d4a0" letter-spacing="0.5">NORTHERN METRO</text>
      </svg>
      <div class="logo-text">
        <h1>Northern Metro College</h1>
        <p>Office of the Registrar &mdash; Student Portal</p>
      </div>
    </div>
    <?php if (!empty($_SESSION['student_id'])): ?>
    <div class="header-user">
      <span>&#128100; <?= htmlspecialchars(($_SESSION['student']['given_name'] ?? '') . ' ' . ($_SESSION['student']['surname'] ?? '')) ?></span>
      <span class="student-id-badge"><?= htmlspecialchars($_SESSION['student_id'] ?? '') ?></span>
    </div>
    <?php endif; ?>
  </div>
</div>

<nav id="main-nav">
  <ul>
    <li<?= ($active_nav ?? '') === 'dashboard' ? ' class="active"' : '' ?>><a href="/dashboard.php">Dashboard</a></li>
    <li<?= ($active_nav ?? '') === 'records'   ? ' class="active"' : '' ?>><a href="/records.php">Academic Records</a></li>
    <li<?= ($active_nav ?? '') === 'request'   ? ' class="active"' : '' ?>><a href="/request.php">Request Documents</a></li>
    <li<?= ($active_nav ?? '') === 'status'    ? ' class="active"' : '' ?>><a href="/status.php">Request Status</a></li>
    <li><a href="/logout.php">Log Out</a></li>
  </ul>
</nav>
