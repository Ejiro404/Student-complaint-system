<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get complaint counts by status (single query)
$countSql = "
    SELECT status, COUNT(*) AS total
    FROM complaints
    GROUP BY status
";
$countResult = $conn->query($countSql);

$counts = [
    'Pending' => 0,
    'In Progress' => 0,
    'Resolved' => 0
];

if ($countResult) {
    while ($row = $countResult->fetch_assoc()) {
        $status = $row['status'];
        if (isset($counts[$status])) {
            $counts[$status] = (int)$row['total'];
        }
    }
}

$totalComplaints = array_sum($counts);

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
    <h3 style="margin-bottom:6px;">Total Complaints</h3>
    <div class="stat-number"><?php echo (int)$totalComplaints; ?></div>
    <p class="stat-note">All complaints submitted by students.</p>
    <a class="btn" href="set_filter.php?status=ALL">View Complaints</a>

  </div>

  <div class="tile">
    <h3 style="margin-bottom:6px;">Pending</h3>
    <div class="stat-number status-Pending"><?php echo (int)$counts['Pending']; ?></div>
    <p class="stat-note">Complaints not yet attended to.</p>
    <a class="btn" href="set_filter.php?status=Pending">Manage Pending</a>

  </div>

  <div class="tile">
    <h3 style="margin-bottom:6px;">In Progress</h3>
    <div class="stat-number status-In-Progress"><?php echo (int)$counts['In Progress']; ?></div>
    <p class="stat-note">Complaints currently being handled.</p>
    <a class="btn" href="set_filter.php?status=In%20Progress">Manage In Progress</a>

  </div>

  <div class="tile">
    <h3 style="margin-bottom:6px;">Resolved</h3>
    <div class="stat-number status-Resolved"><?php echo (int)$counts['Resolved']; ?></div>
    <p class="stat-note">Complaints completed and closed.</p>
    <a class="btn" href="set_filter.php?status=Resolved">View Resolved</a>

  </div>
</div>

<p class="footer-note" style="margin-top:14px;">
  Tip: Updates made to complaint status will reflect immediately in these summary counts.
</p>

<?php include '../includes/portal_footer.php'; ?>
