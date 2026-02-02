<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($page_title)) {
    $page_title = "LASUED Complaint System";
}

$base = (isset($base_path) && $base_path) ? $base_path : ".."; // default for /student and /admin pages
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">

    <div class="topbar-left topbar-brand">
      <?php
        $logoPathDisk = __DIR__ . '/../assets/images/lasued-logo.png';
        $logoPathWeb  = $base . '/assets/images/lasued-logo.png';
      ?>
      <?php if (file_exists($logoPathDisk)): ?>
        <img src="<?php echo $logoPathWeb; ?>" class="topbar-logo" alt="LASUED Logo">
      <?php endif; ?>

      <div class="topbar-title">LASUED Complaint-System</div>
    </div>

    <!-- Desktop links -->
    <nav class="topbar-links desktop-nav" aria-label="Primary navigation">
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <a href="<?php echo $base; ?>/student/dashboard.php">Dashboard</a>
        <a href="<?php echo $base; ?>/student/submit_complaint.php">Submit Complaint</a>
        <a href="<?php echo $base; ?>/student/my_complaints.php">My Complaints</a>
      <?php else: ?>
        <a href="<?php echo $base; ?>/admin/dashboard.php">Dashboard</a>
        <a href="<?php echo $base; ?>/admin/view_complaints.php">View Complaints</a>
      <?php endif; ?>
      <a href="<?php echo $base; ?>/auth/logout.php">Logout</a>
    </nav>

    <!-- Mobile hamburger (shows ONLY on mobile via .nav-mobile-only in CSS) -->
    <button
      id="navToggle"
      class="nav-toggle nav-mobile-only"
      type="button"
      aria-label="Toggle menu"
      aria-expanded="false"
      aria-controls="mobileNav"
    >
      ☰
    </button>

  </div>

  <!-- Mobile dropdown menu -->
  <div id="mobileNav" class="mobile-nav" aria-label="Mobile navigation">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
      <a href="<?php echo $base; ?>/student/dashboard.php">Dashboard</a>
      <a href="<?php echo $base; ?>/student/submit_complaint.php">Submit Complaint</a>
      <a href="<?php echo $base; ?>/student/my_complaints.php">My Complaints</a>
    <?php else: ?>
      <a href="<?php echo $base; ?>/admin/dashboard.php">Dashboard</a>
      <a href="<?php echo $base; ?>/admin/view_complaints.php">View Complaints</a>
    <?php endif; ?>
    <a href="<?php echo $base; ?>/auth/logout.php">Logout</a>
  </div>
</div>

<script>
  (function(){
    const toggleBtn = document.getElementById('navToggle');
    const nav = document.getElementById('mobileNav');

    function closeNav(){
      nav.classList.remove('open');
      toggleBtn.setAttribute('aria-expanded', 'false');
    }

    function toggleNav(){
      const isOpen = nav.classList.toggle('open');
      toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    // Toggle on hamburger click
    toggleBtn.addEventListener('click', toggleNav);

    // Close menu when any link is clicked (mobile UX)
    nav.addEventListener('click', function(e){
      if (e.target && e.target.tagName === 'A') closeNav();
    });

    // Close menu if resized to desktop width
    window.addEventListener('resize', function(){
      if (window.innerWidth > 700) closeNav();
    });
  })();
</script>
