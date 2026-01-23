<?php
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    }
    if ($_SESSION['role'] === 'student') {
        header("Location: student/dashboard.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LASUED Complaint System</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .home-hero{
      max-width: 960px;
      margin: 24px auto;
      padding: 0 16px;
    }
    .home-card{
      background:#fff;
      border:1px solid #e6e8ef;
      border-radius:10px;
      box-shadow:0 8px 24px rgba(0,0,0,0.06);
      overflow:hidden;
    }
    .home-head{
      padding:18px;
      border-bottom:1px solid #eef0f6;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
    }
    .home-title{
      font-size:22px;
      font-weight:900;
      margin:0;
    }
    .home-sub{
      margin:6px 0 0;
      color:#555;
    }
    .home-body{
      padding:18px;
      display:grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap:16px;
    }
    @media (max-width: 820px){
      .home-body{ grid-template-columns: 1fr; }
    }
    .home-actions{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-top:12px;
    }
    .btn-secondary{
      display:inline-block;
      padding:12px 18px;
      border-radius:10px;
      border:1px solid #d9dce7;
      background:#f7f8fc;
      font-weight:800;
      color:#1a73e8;
      text-decoration:none;
    }
    .btn-secondary:hover{ filter: brightness(0.97); text-decoration:none; }
    .mini{
      background:#f7f8fc;
      border:1px solid #e6e8ef;
      border-radius:10px;
      padding:14px;
    }
    .mini h3{ margin:0 0 8px; }
    .mini ul{ margin:0; padding-left:18px; color:#444; }
  </style>
</head>
<body style="background:#eef1f5;">

<div class="home-hero">
  <div class="home-card">

    <div class="home-head">
      <div>
        <h1 class="home-title">LASUED Online Student Complaint System</h1>
        <p class="home-sub">Submit complaints, track progress, and receive official feedback from the administration.</p>
      </div>
      <div>
        <a class="btn-secondary" href="auth/login.php">Login</a>
      </div>
    </div>

    <div class="home-body">
      <div>
        <div class="mini">
          <h3>How it works</h3>
          <ul>
            <li>Students submit complaints with a subject and detailed message.</li>
            <li>Admin reviews complaints and updates status (Pending, In Progress, Resolved).</li>
            <li>Students track status and view admin remarks with timestamps.</li>
          </ul>
        </div>

        <div class="home-actions">
          <a class="btn" href="auth/login.php">Proceed to Login</a>
          <a class="btn-secondary" href="auth/login.php">Student / Admin Access</a>
        </div>

        <p class="footer-note" style="margin-top:12px;">
          This system is designed for Lagos State University of Education (Otto/Ijanikin) to improve transparency and resolution of student complaints.
        </p>
      </div>

      <div class="mini">
        <h3>Core Features</h3>
        <ul>
          <li>Secure login (Student/Admin)</li>
          <li>Complaint submission</li>
          <li>Status tracking and admin remarks</li>
          <li>Updated indicator and timestamps</li>
        </ul>
      </div>
    </div>

  </div>
</div>

</body>
</html>
