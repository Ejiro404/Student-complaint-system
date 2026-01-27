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

// Default values (so form can safely echo them)
$subject = '';
$complaint_text = '';

$redirect_after_success = false;
$redirect_url = "my_complaints.php";

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
            $redirect_after_success = true;

            // clear form values after successful submission
            $subject = '';
            $complaint_text = '';
        } else {
            $error = "Execution failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

$page_title = "Submit Complaint | LASUED Complaint System";
include '../includes/portal_header.php';
?>

<div class="tile">
  <h2 style="margin:0 0 10px;">Submit a Complaint</h2>

  <?php if (!empty($success)): ?>
    <div class="alert-success" id="successAlert">
      <span class="alert-icon">✅</span>
      <div style="flex:1;">
        <div><?php echo htmlspecialchars($success); ?></div>
        <div style="font-weight:600; opacity:0.9; margin-top:2px;">
          You can track progress in <strong>My Complaints</strong>.
        </div>

        <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
          <span style="font-weight:600; opacity:0.85; align-self:center;">
            Redirecting shortly...
          </span>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error">
      <span class="alert-icon">⚠️</span>
      <div><?php echo htmlspecialchars($error); ?></div>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label for="subject">Subject</label>
      <input id="subject" type="text" name="subject"
             value="<?php echo htmlspecialchars($subject); ?>"
             required placeholder="Enter complaint subject">
    </div>

    <div class="form-group">
      <label for="message">Complaint</label>
      <textarea id="message" name="message"
                required placeholder="Describe your complaint clearly"><?php echo htmlspecialchars($complaint_text); ?></textarea>
    </div>

    <button class="btn" type="submit">Submit Complaint</button>
  </form>

  <p class="footer-note" style="margin-top:12px;">
    Your complaint will be reviewed by the admin and you can track updates in “My Complaints”.
  </p>
</div>

<?php if (!empty($success) && $redirect_after_success): ?>
<script>
  (function () {
    const alertBox = document.getElementById('successAlert');
    const redirectUrl = <?php echo json_encode($redirect_url); ?>;

    // Auto-fade after 3 seconds
    setTimeout(() => {
      if (!alertBox) return;
      alertBox.style.transition = "opacity 400ms ease";
      alertBox.style.opacity = "0";
    }, 3000);

    // Redirect after 3.2 seconds (so user briefly sees success message)
    setTimeout(() => {
      window.location.href = redirectUrl;
    }, 3200);
  })();
</script>
<?php endif; ?>

<?php include '../includes/portal_footer.php'; ?>
