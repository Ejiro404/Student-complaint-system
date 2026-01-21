<?php
require_once '../config/session.php';
require_once '../config/db.php';
check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$sql = "SELECT complaints.*, users.full_name 
        FROM complaints 
        JOIN users ON complaints.student_id = users.id
        ORDER BY complaints.created_at DESC";

$result = $conn->query($sql);
?>

<h2>All Complaints</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>Student</th>
        <th>Subject</th>
        <th>Message</th>
        <th>Status</th>
        <th>Date</th>
    </tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['full_name']; ?></td>
    <td><?php echo $row['subject']; ?></td>
    <td><?php echo $row['message']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['created_at']; ?></td>
</tr>
<?php endwhile; ?>

</table>

<a href="dashboard.php">Back</a>
