<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$filter_status = isset($_SESSION['complaints_filter']) ? $_SESSION['complaints_filter'] : '';
$allowed_status = ['Pending', 'In Progress', 'Resolved'];

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $per_page;

// Count total rows (for pagination)
if ($filter_status !== '' && in_array($filter_status, $allowed_status, true)) {
    $countSql = "SELECT COUNT(*) AS total FROM complaints WHERE status = ?";
    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) { die("Prepare failed: " . $conn->error); }
    $countStmt->bind_param("s", $filter_status);
} else {
    $filter_status = '';
    $countSql = "SELECT COUNT(*) AS total FROM complaints";
    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) { die("Prepare failed: " . $conn->error); }
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
    $sql = "SELECT
                c.id AS complaint_id,
                c.subject,
                c.complaint_text,
                c.status,
                c.created_at,
                u.full_name
            FROM complaints c
            LEFT JOIN users u ON c.student_id = u.id
            WHERE c.status = ?
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Prepare failed: " . $conn->error); }
    $stmt->bind_param("sii", $filter_status, $per_page, $offset);
} else {
    $sql = "SELECT
                c.id AS complaint_id,
                c.subject,
                c.complaint_text,
                c.status,
                c.created_at,
                u.full_name
            FROM complaints c
            LEFT JOIN users u ON c.student_id = u.id
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Prepare failed: " . $conn->error); }
    $stmt->bind_param("ii", $per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$page_title = "View Complaints | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">All Student Complaints</h2>

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
      <th>Student</th>
      <th>Subject</th>
      <th>Message</th>
      <th>Status</th>
      <th>Date</th>
      <th>Action</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></td>
          <td><?php echo htmlspecialchars($row['subject']); ?></td>
          <td><?php echo htmlspecialchars($row['complaint_text']); ?></td>
          <td class="status-<?php echo str_replace(' ', '-', $row['status']); ?>">
            <?php echo htmlspecialchars($row['status']); ?>
          </td>
          <td><?php echo htmlspecialchars($row['created_at']); ?></td>
          <td>
            <a class="btn" style="padding:8px 10px; font-size:14px;"
               href="update_complaint.php?id=<?php echo (int)$row['complaint_id']; ?>">
              Update
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" style="text-align:center; padding:16px;">
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
    Click “Update” to change complaint status and add an admin remark.
  </p>
</div>

<?php include '../includes/portal_footer.php'; ?>
