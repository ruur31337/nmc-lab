<?php
/**
 * includes/auth.php — Session guards
 *
 * require_student() — ensures student session active, else redirect to login
 * require_staff()   — ensures staff session active, else redirect to staff login
 */

function require_student() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('NMCREGSESSID');
        session_start();
    }
    if (empty($_SESSION['student_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function require_staff() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('NMCSTAFFSESSID');
        session_start();
    }
    if (empty($_SESSION['staff_id'])) {
        header('Location: /staff/login.php');
        exit;
    }
}

function current_student() {
    return $_SESSION['student'] ?? null;
}

function current_staff() {
    return $_SESSION['staff'] ?? null;
}
