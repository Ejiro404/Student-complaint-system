<?php
require_once '../config/session.php';
check_login();

if ($_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<h2>Student Dashboard</h2>
<p>Welcome, <?php echo $_SESSION['name']; ?></p>

<a href="submit_complaint.php">Submit Complaint</a><br><br>
<a href="../auth/logout.php">Logout</a>
