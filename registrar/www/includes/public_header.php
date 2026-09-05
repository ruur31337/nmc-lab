<?php
/**
 * includes/public_header.php — Public portal header (no auth required)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title ?? 'NMC Registrar Portal') ?></title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <span>&#9990; Office of the Registrar &mdash; Northern Metro College</span>
    <span>Mon&ndash;Fri &nbsp;8:00 AM&ndash;5:00 PM &nbsp;|&nbsp; loc. 110</span>
  </div>
</div>

<nav class="navbar">
  <div class="nav-inner">
    <a href="/" class="nav-brand">
      <svg viewBox="0 0 48 48" width="28" height="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="24" cy="24" r="23" fill="#6B0F1A" stroke="#C9961A" stroke-width="1.5"/>
        <text x="24" y="21" text-anchor="middle" font-family="Georgia,serif" font-size="9" font-weight="bold" fill="#FBF7F1">NMC</text>
        <text x="24" y="29" text-anchor="middle" font-family="Georgia,serif" font-size="5" fill="#C9961A" letter-spacing="1">EST. 1982</text>
      </svg>
      Registrar Portal
    </a>
    <div class="nav-links">
      <a href="/" class="<?= ($active_nav ?? '') === 'home' ? 'active' : '' ?>">Home</a>
      <a href="/apply.php" class="<?= ($active_nav ?? '') === 'apply' ? 'active' : '' ?>">New Request</a>
      <a href="/how-to.php" class="<?= ($active_nav ?? '') === 'howto' ? 'active' : '' ?>">How to Request</a>
      <a href="/requirements.php" class="<?= ($active_nav ?? '') === 'requirements' ? 'active' : '' ?>">Requirements</a>
      <a href="/contact.php" class="<?= ($active_nav ?? '') === 'contact' ? 'active' : '' ?>">Contact</a>
      <a href="/staff/login.php" style="border:1px solid #C9961A;border-radius:3px;padding:3px 10px;margin-left:8px;color:#C9961A;">Staff Login</a>
    </div>
  </div>
</nav>
