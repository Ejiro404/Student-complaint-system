<?php
require_once '../config/session.php';
check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$allowed = ['Pending', 'In Progress', 'Resolved', 'ALL'];

$status = isset($_GET['status']) ? trim($_GET['status']) : 'ALL';
if (!in_array($status, $allowed, true)) {
    $status = 'ALL';
}

if ($status === 'ALL') {
    unset($_SESSION['complaints_filter']);
} else {
    $_SESSION['complaints_filter'] = $status;
}

// Preserve search query if present
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$redirect = "view_complaints.php" . ($q !== '' ? "?q=" . urlencode($q) : "");

header("Location: " . $redirect);
exit();
