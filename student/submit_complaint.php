<?php
require_once '../config/session.php';
require_once '../config/db.php';
check_login();

if ($_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $student_id = $_SESSION['user_id'];

    if (empty($subject) || empty($message)) {
        $error = "All fields are required.";
    } else {
        $sql = "INSERT INTO complaints (student_id, subject, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $student_id, $subject, $message);

        if ($stmt->execute()) {
            $success = "Complaint submitted successfully.";
        } else {
            $error = "Something went wrong. Try again.";
        }
    }
}
?>

<h2>Submit Complaint</h2>

<?php if ($success): ?><p style="color:green;"><?php echo $success; ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>

<form method="POST">
    <input type="text" name="subject" placeholder="Complaint Subject" required><br><br>
    <textarea name="message" placeholder="Describe your complaint" required></textarea><br><br>
    <button type="submit">Submit</button>
</form>

<a href="dashboard.php">Back to Dashboard</a>
