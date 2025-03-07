<?php
session_start();
include("../../connect.php");

// Get form data
$ownerID = $_POST['ownerID'];
$passkey = $_POST['passkey'];

// Validate inputs
if (empty($ownerID) || empty($passkey)) {
    $_SESSION['owner_login_error'] = "Both Owner ID and Passkey are required.";
    header("Location: owner-login.php");
    exit();
}

// For security, sanitize the owner ID (assuming it's a number)
$ownerID = filter_var($ownerID, FILTER_SANITIZE_NUMBER_INT);

// Connect to database
$conn = OpenCon();

// Check if owner exists
$checkOwner = $conn->prepare("SELECT ownerID, name FROM Owner WHERE ownerID = ?");
$checkOwner->bind_param("i", $ownerID);
$checkOwner->execute();
$result = $checkOwner->get_result();

if ($result->num_rows === 0) {
    // Owner not found
    $_SESSION['owner_login_error'] = "Owner ID not found.";
    header("Location: owner-login.php");
    CloseCon($conn);
    exit();
}

// For this demo, we'll use a simple passkey check (in a real app, you'd use hashed passwords)
if ($passkey === "000") {
    // Authentication successful
    $ownerData = $result->fetch_assoc();
    
    // Set session variables
    $_SESSION['owner_logged_in'] = true;
    $_SESSION['owner_id'] = $ownerID;
    $_SESSION['owner_name'] = $ownerData['name'];
    
    // Redirect to owner viewer page
    header("Location: owner-viewer.php");
} else {
    // Invalid passkey
    $_SESSION['owner_login_error'] = "Invalid passkey.";
    header("Location: owner-login.php");
}

CloseCon($conn);
exit();
?>