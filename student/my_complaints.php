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

if ($filter_status !== '' && in_array($filter_status, $allowed_status, true)) {
    // FILTERED QUERY (2 placeholders)
    $sql = "SELECT id, subject, complaint_text, status, admin_remark, created_at, updated_at
            FROM complaints
            WHERE student_id = ? AND status = ?
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // 2 params: int + string
    $stmt->bind_param("is", $student_id, $filter_status);

} else {
    // UNFILTERED QUERY (1 placeholder)
    $filter_status = '';
    $sql = "SELECT id, subject, complaint_text, status, admin_remark, created_at, updated_at
            FROM complaints
            WHERE student_id = ?
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // 1 param: int
    $stmt->bind_param("i", $student_id);
}

$stmt->execute();
$result = $stmt->get_result();

$page_title = "My Complaints | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">My Complaints</h2>

  <?php if (!empty($filter_status)): ?>
    <div style="margin: 8px 0 12px;">
      <span class="badge">Showing: <?php echo htmlspecialchars($filter_status); ?></span>
      <a href="set_filter.php?status=ALL" style="margin-left:10px;">View All</a>
    </div>
  <?php endif; ?>

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

  <p class="footer-note" style="margin-top:12px;">
    “Updated” indicates that an admin has changed the complaint status or added a remark after submission.
  </p>
</div>

<?php include '../includes/portal_footer.php'; ?>