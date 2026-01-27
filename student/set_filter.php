<?php
require_once '../config/session.php';
check_login();

if ($_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$allowed = ['Pending', 'In Progress', 'Resolved', 'ALL'];

$status = isset($_GET['status']) ? trim($_GET['status']) : 'ALL';
if (!in_array($status, $allowed, true)) {
    $status = 'ALL';
}

if ($status === 'ALL') {
    unset($_SESSION['student_complaints_filter']);
} else {
    $_SESSION['student_complaints_filter'] = $status;
}

header("Location: my_complaints.php");
exit();