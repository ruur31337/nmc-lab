<?php
/**
 * config.php — NMC Main Website Configuration
 *
 * Site-wide constants and settings.
 * Do NOT store database credentials here — main-web is a
 * presentation layer only. DB access is handled by the
 * admission and academy portals via their own configs.
 *
 * @author   rdelacruz
 * @created  2025-01-08
 */

define('SITE_NAME',    'Northern Metro College');
define('SITE_URL',     'http://www.nmc.local');
define('SITE_EMAIL',   'info@nmc.edu.ph');
define('HR_EMAIL',     'hr@nmc.edu.ph');
define('UPLOAD_DIR',   __DIR__ . '/uploads/');
define('UPLOAD_MAX',   10 * 1024 * 1024);  // 10MB

// Mail config — uses local sendmail relay
// SMTP settings managed separately in /etc/msmtprc
define('MAIL_FROM',    'no-reply@nmc.edu.ph');
define('MAIL_FROM_NAME', 'NMC Website');
