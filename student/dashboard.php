<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($student_id <= 0) {
    die("Session error: student not authenticated properly.");
}

// Count complaints by status for this student
$countSql = "
    SELECT status, COUNT(*) AS total
    FROM complaints
    WHERE student_id = ?
    GROUP BY status
";

$stmt = $conn->prepare($countSql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();

$counts = [
    'Pending' => 0,
    'In Progress' => 0,
    'Resolved' => 0
];

while ($row = $res->fetch_assoc()) {
    $status = $row['status'];
    if (isset($counts[$status])) {
        $counts[$status] = (int)$row['total'];
    }
}

$stmt->close();

$totalComplaints = array_sum($counts);

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

<!-- Summary Cards -->
<div class="grid-2">
  <div class="tile">
    <h3 style="margin-bottom:6px;">Total Complaints</h3>
    <div class="stat-number"><?php echo (int)$totalComplaints; ?></div>
    <p class="stat-note">All complaints you have submitted.</p>
    <a class="btn" href="my_complaints.php">View My Complaints</a>
  </div>

  <div class="tile">
    <h3 style="margin-bottom:6px;">Pending</h3>
    <div class="stat-number status-Pending"><?php echo (int)$counts['Pending']; ?></div>
    <p class="stat-note">Complaints awaiting review.</p>
    <a class="btn" href="set_filter.php?status=Pending">Track Pending</a>
  </div>

  <div class="tile">
    <h3 style="margin-bottom:6px;">In Progress</h3>
    <div class="stat-number status-In-Progress"><?php echo (int)$counts['In Progress']; ?></div>
    <p class="stat-note">Complaints being handled.</p>
    <a class="btn" href="set_filter.php?status=In%20Progress">Track In Progress</a>
  </div>

  <div class="tile">
    <h3 style="margin-bottom:6px;">Resolved</h3>
    <div class="stat-number status-Resolved"><?php echo (int)$counts['Resolved']; ?></div>
    <p class="stat-note">Complaints completed and closed.</p>
    <a class="btn" href="set_filter.php?status=Resolved">View Resolved</a>
  </div>
</div>

<div style="height:14px;"></div>

<!-- Action Cards -->
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

<p class="footer-note" style="margin-top:14px;">
  Tip: If your complaint status changes, you will see an “Updated” indicator on your complaints list.
</p>

<?php include '../includes/portal_footer.php'; ?>
