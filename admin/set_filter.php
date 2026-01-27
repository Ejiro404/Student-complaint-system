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

// Always go to complaints page after choosing manage filter
header("Location: view_complaints.php");
exit();
