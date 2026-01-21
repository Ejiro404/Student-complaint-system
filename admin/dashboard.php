<?php
require_once '../config/session.php';
check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<a href="view_complaints.php">View Complaints</a><br><br>

<h2>Admin Dashboard</h2>
<p>Welcome, <?php echo $_SESSION['name']; ?></p>

<a href="../auth/logout.php">Logout</a>
