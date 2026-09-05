<?php
/**
 * includes/header.php — Shared page header
 *
 * Outputs <head>, topbar, logo, and main navigation.
 *
 * Expected variables (set before require_once):
 *   $page  (string) — current page slug for .active nav highlight
 *   $title (string) — page <title> prefix
 */
if (!isset($page))  $page  = '';
if (!isset($title)) $title = 'Northern Metro College';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?> | Northern Metro College</title>
<?php if (!empty($meta_description)): ?>
<meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
<?php endif; ?>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <span>&#128205; Taguig City, Metro Manila &nbsp;|&nbsp; &#9742; (02) 8XXX-XXXX &nbsp;|&nbsp; &#9993; info@nmc.edu.ph</span>
    <span>
      <a href="http://admission.nmc.local">Admission Portal</a> &nbsp;|&nbsp;
      <a href="http://academy.nmc.local">Student Portal</a>
    </span>
  </div>
</div>

<div id="header">
  <div class="inner">
    <div class="site-logo">
      <svg viewBox="0 0 64 64" width="64" height="64" xmlns="http://www.w3.org/2000/svg">
        <circle cx="32" cy="32" r="31" fill="#6B0F1A" stroke="#C9961A" stroke-width="2"/>
        <circle cx="32" cy="32" r="25" fill="none" stroke="#C9961A" stroke-width="1" stroke-dasharray="3,2"/>
        <text x="32" y="27" text-anchor="middle" font-family="Georgia,serif" font-size="12" font-weight="bold" fill="#FBF7F1">NMC</text>
        <text x="32" y="37" text-anchor="middle" font-family="Georgia,serif" font-size="7" fill="#C9961A" letter-spacing="1">EST. 1982</text>
        <text x="32" y="47" text-anchor="middle" font-family="Georgia,serif" font-size="5.5" fill="#e8d4a0" letter-spacing="0.5">NORTHERN METRO</text>
      </svg>
      <div class="logo-text">
        <h1>Northern Metro College</h1>
        <p>Excellence in Education Since 1982</p>
      </div>
    </div>
  </div>
</div>

<nav id="main-nav">
  <ul>
    <li<?= $page==='home'     ? ' class="active"' : '' ?>><a href="/index.php">Home</a></li>
    <li<?= $page==='about'    ? ' class="active"' : '' ?>><a href="/about.php">About NMC</a></li>
    <li<?= $page==='programs' ? ' class="active"' : '' ?>><a href="/programs.php">Programs</a></li>
    <li><a href="http://admission.nmc.local">Admissions</a></li>
    <li<?= $page==='careers'  ? ' class="active"' : '' ?>><a href="/careers.php">Careers</a></li>
    <li<?= $page==='contact'  ? ' class="active"' : '' ?>><a href="/contact.php">Contact Us</a></li>
  </ul>
</nav>
