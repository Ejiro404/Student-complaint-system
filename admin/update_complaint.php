<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_complaints.php");
    exit();
}

$complaint_id = (int) $_GET['id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = isset($_POST['status']) ? $_POST['status'] : 'Pending';
    $admin_remark = isset($_POST['admin_remark']) ? trim($_POST['admin_remark']) : '';

    $updateSql = "UPDATE complaints
                  SET status = ?, admin_remark = ?
                  WHERE id = ?";

    $stmt = $conn->prepare($updateSql);
    if (!$stmt) {
        die("Prepare failed (UPDATE): " . $conn->error);
    }

    $stmt->bind_param("ssi", $status, $admin_remark, $complaint_id);

    if ($stmt->execute()) {
        $success = "Complaint updated successfully.";
    } else {
        $error = "Update failed: " . $stmt->error;
    }
    $stmt->close();
}

$selectSql = "SELECT subject, complaint_text, status, admin_remark
              FROM complaints
              WHERE id = ?";

$stmt = $conn->prepare($selectSql);
if (!$stmt) {
    die("Prepare failed (SELECT): " . $conn->error);
}
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Complaint not found.");
}

$complaint = $result->fetch_assoc();
$stmt->close();

$page_title = "Update Complaint | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">Update Complaint</h2>

  <?php if ($success): ?>
    <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <p><strong>Subject:</strong> <?php echo htmlspecialchars($complaint['subject']); ?></p>
  <p><strong>Message:</strong> <?php echo htmlspecialchars($complaint['complaint_text']); ?></p>

  <form method="POST" style="margin-top:14px;">
    <div class="form-group">
      <label>Status</label>
      <select name="status" required>
        <option value="Pending" <?php if ($complaint['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
        <option value="In Progress" <?php if ($complaint['status'] === 'In Progress') echo 'selected'; ?>>In Progress</option>
        <option value="Resolved" <?php if ($complaint['status'] === 'Resolved') echo 'selected'; ?>>Resolved</option>
      </select>
    </div>

    <div class="form-group">
      <label>Admin Remark</label>
      <textarea name="admin_remark" placeholder="Enter remark for the student"><?php echo htmlspecialchars($complaint['admin_remark'] ?? ''); ?></textarea>
    </div>

    <button class="btn" type="submit">Save Update</button>
    <a href="view_complaints.php" style="margin-left:10px;">Back</a>
  </form>
</div>
<?php if (!empty($success)): ?>
<script>
  // Redirect back to Admin Dashboard after 2.2 seconds
  setTimeout(() => {
    window.location.href = 'dashboard.php';
  }, 2200);
</script>
<?php endif; ?>

<?php include '../includes/portal_footer.php'; ?>
