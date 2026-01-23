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

$sql = "SELECT id, subject, complaint_text, status, admin_remark, created_at, updated_at
        FROM complaints
        WHERE student_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$page_title = "My Complaints | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">My Complaints</h2>

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
  </table>

  <p class="footer-note" style="margin-top:12px;">
    “Updated” indicates that an admin has changed the complaint status or added a remark after submission.
  </p>
</div>

<?php include '../includes/portal_footer.php'; ?>
