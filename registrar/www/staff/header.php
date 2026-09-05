<?php
/**
 * staff/header.php — Shared header for staff portal
 *
 * Expects $page_title and $active_nav to be set before include.
 *
 * @author   rdelacruz
 * @modified 2025-01-10
 */
if (session_name() !== 'NMCSTAFFSESSID') {
    // Ensure correct session is active
    session_name('NMCSTAFFSESSID');
}
$_staff = $_SESSION['staff'] ?? [];
$_staff_name = $_staff['full_name'] ?? 'Staff';
$_staff_role = ucfirst($_staff['role'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title ?? 'Staff Portal') ?> | NMC Registrar</title>
<link rel="stylesheet" href="/css/style.css">
<style>
/* Staff portal overrides */
.topbar { background: #3a0810; }
.navbar  { background: #2a060c; border-bottom: 2px solid #6B0F1A; }
.navbar a { color: #e8c97a; }
.navbar a:hover, .navbar a.active { color: #fff; background: #6B0F1A; }
.role-badge {
  display:inline-block;
  background:#C9961A;
  color:#2a060c;
  font-size:10px;
  font-weight:700;
  padding:1px 6px;
  border-radius:3px;
  letter-spacing:.5px;
  text-transform:uppercase;
  vertical-align:middle;
  margin-left:4px;
}
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <span>&#9876; NMC Registrar &mdash; Staff Portal</span>
    <span>
      <?= htmlspecialchars($_staff_name) ?>
      <span class="role-badge"><?= htmlspecialchars($_staff_role) ?></span>
      &nbsp;|&nbsp;
      <a href="/staff/logout.php" style="color:#e8c97a;">Log out</a>
    </span>
  </div>
</div>

<nav class="navbar">
  <div class="nav-inner">
    <a href="/staff/dashboard.php" class="nav-brand">
      <svg viewBox="0 0 48 48" width="28" height="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="24" cy="24" r="23" fill="#6B0F1A" stroke="#C9961A" stroke-width="1.5"/>
        <text x="24" y="21" text-anchor="middle" font-family="Georgia,serif" font-size="9" font-weight="bold" fill="#FBF7F1">NMC</text>
        <text x="24" y="29" text-anchor="middle" font-family="Georgia,serif" font-size="5" fill="#C9961A" letter-spacing="1">EST. 1982</text>
      </svg>
      Staff Portal
    </a>
    <div class="nav-links">
      <a href="/staff/dashboard.php"       class="<?= ($active_nav ?? '') === 'dashboard'      ? 'active' : '' ?>">Dashboard</a>
      <a href="/staff/requests.php"        class="<?= ($active_nav ?? '') === 'requests'       ? 'active' : '' ?>">Requests</a>
      <a href="/staff/search.php"          class="<?= ($active_nav ?? '') === 'search'         ? 'active' : '' ?>">Student Search</a>
      <a href="/staff/announcements.php"   class="<?= ($active_nav ?? '') === 'announcements'  ? 'active' : '' ?>">Announcements</a>
    </div>
  </div>
</nav>
