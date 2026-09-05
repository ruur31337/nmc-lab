<?php
/**
 * staff/update_request.php — Update status and staff notes on a document request.
 * POST handler only — redirects back to view_request.php after update.
 */
session_name('NMCSTAFFSESSID');
session_start();

require_once '../includes/auth.php';
require_staff();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /staff/requests.php');
    exit;
}

$id          = (int)($_POST['id'] ?? 0);
$new_status  = $_POST['status']      ?? '';
$staff_notes = trim($_POST['staff_notes'] ?? '');
$release_date = trim($_POST['release_date'] ?? '') ?: null;

$allowed_statuses = ['pending', 'processing', 'ready', 'released'];
if (!$id || !in_array($new_status, $allowed_statuses)) {
    header('Location: /staff/requests.php?err=bad_request');
    exit;
}

$stmt = $conn->prepare(
    'UPDATE document_requests
     SET status = ?, staff_notes = ?, release_date = ?, processed_by = ?
     WHERE id = ?'
);
$stmt->bind_param('sssii', $new_status, $staff_notes, $release_date, $_SESSION['staff_id'], $id);
$stmt->execute();
$stmt->close();

header("Location: /staff/view_request.php?id=$id&updated=1");
exit;
