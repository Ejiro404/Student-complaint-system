<?php
require_once '../config/session.php';
check_login();

if ($_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Student Dashboard | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 6px;">Student Dashboard</h2>
  <p style="margin:0; color:#555;">
    Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>.
  </p>
</div>

<div style="height:14px;"></div>

<div class="grid-2">
  <div class="tile">
    <h3>Submit a Complaint</h3>
    <p>Send a new complaint to the appropriate office for review and resolution.</p>
    <a class="btn" href="submit_complaint.php">Go to Submit</a>
  </div>

  <div class="tile">
    <h3>Track My Complaints</h3>
    <p>View status updates, admin remarks, and the last updated time.</p>
    <a class="btn" href="my_complaints.php">View Complaints</a>
  </div>
</div>

<p class="footer-note" style="margin-top:19px;">
  Tip: If your complaint status changes, you will see an “Updated” indicator on your complaints list.
</p>

<?php include '../includes/portal_footer.php'; ?>
