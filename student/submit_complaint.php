<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$success = '';
$error = '';

$student_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($student_id <= 0) {
    die("Session error: student not authenticated properly.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $complaint_text = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($subject === '' || $complaint_text === '') {
        $error = 'All fields are required.';
    } else {
        $sql = "INSERT INTO complaints (student_id, subject, complaint_text)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("iss", $student_id, $subject, $complaint_text);

        if ($stmt->execute()) {
            $success = "Complaint submitted successfully.";
        } else {
            $error = "Execution failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<?php
$page_title = "Submit Complaint | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">Submit a Complaint</h2>

  <?php if ($success): ?>
    <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label for="subject">Subject</label>
      <input id="subject" type="text" name="subject" required placeholder="Enter complaint subject">
    </div>

    <div class="form-group">
      <label for="message">Complaint</label>
      <textarea id="message" name="message" required placeholder="Describe your complaint clearly"></textarea>
    </div>

    <button class="btn" type="submit">Submit Complaint</button>
  </form>

  <p class="footer-note" style="margin-top:12px;">
    Your complaint will be reviewed by the admin and you can track updates in “My Complaints”.
  </p>
</div>

<?php include '../includes/portal_footer.php'; ?>
