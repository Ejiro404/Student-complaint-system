<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

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

$page_title = "View Complaints | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">All Student Complaints</h2>

  <table>
    <tr>
      <th>Student</th>
      <th>Subject</th>
      <th>Message</th>
      <th>Status</th>
      <th>Date</th>
      <th>Action</th>
    </tr>

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
  </table>

  <p class="footer-note" style="margin-top:12px;">
    Click “Update” to change complaint status and add an admin remark.
  </p>
</div>

<?php include '../includes/portal_footer.php'; ?>
