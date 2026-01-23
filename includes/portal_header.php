<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($page_title)) {
    $page_title = "LASUED Complaint System";
}

$base = (isset($base_path) && $base_path) ? $base_path : ".."; // default for /student and /admin pages
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <div class="topbar-title">LASUED Complaint-System</div>

    <?php if (isset($_SESSION['role'])): ?>
      <div class="topbar-links">

        <?php if ($_SESSION['role'] === 'student'): ?>
          <a href="<?php echo $base; ?>/student/dashboard.php">Dashboard</a>
          <a href="<?php echo $base; ?>/student/submit_complaint.php">Submit Complaint</a>
          <a href="<?php echo $base; ?>/student/my_complaints.php">My Complaints</a>

        <?php elseif ($_SESSION['role'] === 'admin'): ?>
          <a href="<?php echo $base; ?>/admin/dashboard.php">Dashboard</a>
          <a href="<?php echo $base; ?>/admin/view_complaints.php">View Complaints</a>

        <?php endif; ?>

        <a href="<?php echo $base; ?>/auth/logout.php">Logout</a>
      </div>
    <?php else: ?>
      <div class="topbar-links">
        <a href="<?php echo $base; ?>/auth/login.php">Login</a>
      </div>
    <?php endif; ?>

  </div>
</div>

<div class="page-wrap">
