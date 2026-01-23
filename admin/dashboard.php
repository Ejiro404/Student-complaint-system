<?php
require_once '../config/session.php';
check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Admin Dashboard | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 6px;">Admin Dashboard</h2>
  <p style="margin:0; color:#555;">
    Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>.
  </p>
</div>

<div style="height:14px;"></div>

<div class="grid-2">
  <div class="tile">
    <h3>View All Complaints</h3>
    <p>Access, review, and manage student complaints with status updates and remarks.</p>
    <a class="btn" href="view_complaints.php">Open Complaints</a>
  </div>

  <div class="tile">
    <h3>Quick Admin Actions</h3>
    <p>Update complaint status (Pending/In Progress/Resolved) and add official remarks.</p>
    <a class="btn" href="view_complaints.php">Manage Now</a>
  </div>
</div>

<p class="footer-note" style="margin-top:14px;">
  Tip: Updates made here will reflect immediately on the student tracking page.
</p>

<?php include '../includes/portal_footer.php'; ?>
