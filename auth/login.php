<?php
require_once '../config/db.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Student Complaint System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST">
    <input type="email" name="email" required placeholder="Email"><br><br>
    <input type="password" name="password" required placeholder="Password"><br><br>
    <button type="submit">Login</button>
</form>

</body>
</html>
