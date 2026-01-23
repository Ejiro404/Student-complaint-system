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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | LASUED Complaint System</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="portal-login-wrap">

  <!-- Logo Section -->
  <div class="portal-login-header">
    <img
      src="../assets/images/lsued-logo.png"
      alt="Lagos State University of Education"
      class="portal-logo-img"
    >
    <p class="portal-subtitle">Online Student Complaint System</p>
  </div>

  <!-- Login Card -->
  <div class="portal-card">
    <div class="portal-card-head">
      <span>Please Sign In</span>
    </div>

    <div class="portal-card-body">

      <?php if (!empty($error)): ?>
        <div class="portal-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="portal-input">
          <div class="left-icon">✉</div>
          <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="portal-input">
          <div class="left-icon">🔑</div>
          <input type="password" name="password" placeholder="Password" required>
        </div>

        <button class="portal-btn" type="submit">Login</button>
      </form>

    </div>
  </div>

</div>

</body>
</html>
