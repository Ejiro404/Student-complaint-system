<?php
$page_title = "LASUED Online Student Complaint System";
$base_path = "."; // because index.php is in root
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/style.css">
</head>
<body>

<div class="landing-wrap">
  <div class="landing-container">

    <div class="landing-card">


      <!-- Hero -->
      <div class="landing-hero">

        <div class="landing-logo">
          <img src="<?php echo $base_path; ?>/assets/images/lasued-logo.png" alt="LASUED Logo">
        </div>

        <h1 class="landing-title">LASUED Online Student Complaint System</h1>
        <p class="landing-subtitle">
          Submit complaints, track progress, and receive official feedback from the university administration.
        </p>

        <a class="landing-cta" href="<?php echo $base_path; ?>/auth/login.php">Proceed to Login</a>
      </div>

      <!-- Features -->
      <div class="landing-features">
        <div class="feature-card">
          <p class="feature-title">Secure Access</p>
          <p class="feature-text">Student and Admin login with role-based access.</p>
        </div>

        <div class="feature-card">
          <p class="feature-title">Complaint Submission</p>
          <p class="feature-text">Submit a complaint with a subject and detailed message.</p>
        </div>

        <div class="feature-card">
          <p class="feature-title">Status Tracking</p>
          <p class="feature-text">Track Pending, In Progress, and Resolved updates easily.</p>
        </div>

        <div class="feature-card">
          <p class="feature-title">Admin Feedback</p>
          <p class="feature-text">View admin remarks and timestamps for transparency.</p>
        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>
