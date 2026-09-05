<?php
/**
 * includes/db.php — Database connection
 *
 * Returns a mysqli connection. Used by all pages.
 *
 * @author  rdelacruz (IT dept)
 */

$db_host = getenv('DB_HOST') ?: 'registrar-db';
$db_user = getenv('DB_USER') ?: 'reguser';
$db_pass = getenv('DB_PASS') ?: 'regpass2025';
$db_name = getenv('DB_NAME') ?: 'nmc_registrar';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    error_log('Registrar DB connect failed: ' . $conn->connect_error);
    http_response_code(503);
    die('<p style="font-family:sans-serif;padding:40px;color:#c00">Database unavailable. Please contact the IT Helpdesk at <a href="mailto:ithelpdesk@nmc.edu.ph">ithelpdesk@nmc.edu.ph</a> or loc. 305.</p>');
}

$conn->set_charset('utf8mb4');
