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
            ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $filter_status);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $filter_status = '';
    $sql = "SELECT
                c.id AS complaint_id,
                c.subject,
                c.complaint_text,
                c.status,
                c.created_at,
                u.full_name
            FROM complaints c
            LEFT JOIN users u ON c.student_id = u.id
            ORDER BY c.created_at DESC";

    $result = $conn->query($sql);
    if (!$result) {
        die("SQL Error: " . $conn->error);
    }
}

$page_title = "View Complaints | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">All Student Complaints</h2>

  <?php if ($filter_status !== ''): ?>
    <div style="margin-bottom:12px;">
      <span class="badge">Showing: <?php echo htmlspecialchars($filter_status); ?></span>
      <a href="set_filter.php?status=ALL" style="margin-left:10px;">View All</a>
    </div>
  <?php endif; ?>

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
          No complaints found<?php echo $filter_status ? " for \"$filter_status\"" : ""; ?>.
        </td>
      </tr>
    <?php endif; ?>
  </table>

  <p class="footer-note" style="margin-top:12px;">
    Click “Update” to change complaint status and add an admin remark.
  </p>
</div>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
include '../includes/portal_footer.php';
?>
