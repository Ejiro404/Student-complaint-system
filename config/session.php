<?php
session_start();

// Prevent session hijacking
session_regenerate_id(true);

// Redirect if not logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /student-complaint-system/auth/login.php");
        exit();
    }
}
