<?php
// Start session
session_start();

// Get the form data
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Simple authentication logic (in a real system, you'd check against a database)
$validCredentials = false;

// Check if username matches role and password is 000
if (($role === 'owner' && $username === 'owner' && $password === '000') ||
    ($role === 'manager' && $username === 'manager' && $password === '000') ||
    ($role === 'staff' && $username === 'staff' && $password === '000') ||
    ($role === 'companyowner' && $username === 'companyowner' && $password === '000')) {
    $validCredentials = true;
}

if ($validCredentials) {
    // Set session variables
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['logged_in'] = true;
    
    // Redirect to the role-specific dashboard
    header("Location: /strata/display/dashboard.php");
    exit();
} else {
    // Authentication failed
    $_SESSION['login_error'] = "Invalid username or password";
    header("Location: /strata/check-connection.php");
    exit();
}
?>