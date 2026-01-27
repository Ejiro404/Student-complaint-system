<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($student_id <= 0) {
    die("Session error: student not authenticated properly.");
}

$filter_status = isset($_SESSION['student_complaints_filter']) ? $_SESSION['student_complaints_filter'] : '';
$allowed_status = ['Pending', 'In Progress', 'Resolved'];

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $per_page;

// Count total rows (for pagination)
if ($filter_status !== '' && in_array($filter_status, $allowed_status, true)) {
    $countSql = "SELECT COUNT(*) AS total FROM complaints WHERE student_id = ? AND status = ?";
    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) { die("Prepare failed: " . $conn->error); }
    $countStmt->bind_param("is", $student_id, $filter_status);
} else {
    $filter_status = '';
    $countSql = "SELECT COUNT(*) AS total FROM complaints WHERE student_id = ?";
    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) { die("Prepare failed: " . $conn->error); }
    $countStmt->bind_param("i", $student_id);
}
$countStmt->execute();
$countRes = $countStmt->get_result();
$total_rows = (int)($countRes->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$total_pages = (int)ceil($total_rows / $per_page);
if ($total_pages < 1) { $total_pages = 1; }
if ($page > $total_pages) { $page = $total_pages; $offset = ($page - 1) * $per_page; }

// Fetch page rows
if ($filter_status !== '' && in_array($filter_status, $allowed_status, true)) {
    $sql = "SELECT id, subject, complaint_text, status, admin_remark, created_at, updated_at
            FROM complaints
            WHERE student_id = ? AND status = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Prepare failed: " . $conn->error); }
    $stmt->bind_param("isii", $student_id, $filter_status, $per_page, $offset);
} else {
    $sql = "SELECT id, subject, complaint_text, status, admin_remark, created_at, updated_at
            FROM complaints
            WHERE student_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Prepare failed: " . $conn->error); }
    $stmt->bind_param("iii", $student_id, $per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$page_title = "My Complaints | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">My Complaints</h2>

  <!-- Filter Bar -->
  <div class="filter-bar">
    <a class="filter-pill <?php echo empty($filter_status) ? 'active' : ''; ?>"
       href="set_filter.php?status=ALL">All</a>

    <a class="filter-pill <?php echo ($filter_status === 'Pending') ? 'active' : ''; ?>"
       href="set_filter.php?status=Pending">Pending</a>

    <a class="filter-pill <?php echo ($filter_status === 'In Progress') ? 'active' : ''; ?>"
       href="set_filter.php?status=In%20Progress">In Progress</a>

    <a class="filter-pill <?php echo ($filter_status === 'Resolved') ? 'active' : ''; ?>"
       href="set_filter.php?status=Resolved">Resolved</a>
  </div>

  <table>
    <tr>
      <th>Subject</th>
      <th>Complaint</th>
      <th>Status</th>
      <th>Admin Remark</th>
      <th>Update</th>
      <th>Date Submitted</th>
      <th>Last Updated</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['subject']); ?></td>
          <td><?php echo htmlspecialchars($row['complaint_text']); ?></td>

          <td class="status-<?php echo str_replace(' ', '-', $row['status']); ?>">
            <?php echo htmlspecialchars($row['status']); ?>
          </td>

          <td><?php echo htmlspecialchars($row['admin_remark'] ?? ''); ?></td>

          <td>
            <?php echo ($row['updated_at'] !== $row['created_at']) ? '<span class="badge">Updated</span>' : ''; ?>
          </td>

          <td><?php echo htmlspecialchars($row['created_at']); ?></td>
          <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="7" style="text-align:center; padding:16px;">
          No complaints found<?php echo $filter_status ? " for \"".htmlspecialchars($filter_status)."\"" : ""; ?>.
        </td>
      </tr>
    <?php endif; ?>
  </table>

  <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php
        $prev = $page - 1;
        $next = $page + 1;
      ?>

      <a class="page-link <?php echo ($page <= 1) ? 'disabled' : ''; ?>"
         href="?page=<?php echo $prev; ?>">Prev</a>

      <?php
        // show up to 5 page numbers around current page
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        for ($p = $start; $p <= $end; $p++):
      ?>
        <a class="page-link <?php echo ($p === $page) ? 'active' : ''; ?>"
           href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
      <?php endfor; ?>

      <a class="page-link <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"
         href="?page=<?php echo $next; ?>">Next</a>
    </div>
  <?php endif; ?>

  <p class="footer-note" style="margin-top:12px;">
    “Updated” indicates that an admin has changed the complaint status or added a remark after submission.
  </p>
</div>

<?php include '../includes/portal_footer.php'; ?>
