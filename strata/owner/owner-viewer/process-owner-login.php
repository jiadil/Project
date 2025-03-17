<?php
// Ensure no output is sent before session starts
ob_start();
session_start();

// Include database connection
include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");

// Ensure OpenCon() exists
if (!function_exists('OpenCon')) {
    die("<p style='color: red;'>❌ ERROR: Database connection function `OpenCon()` is missing. Check `connect.php`.</p>");
}

// Validate form inputs
if (!isset($_POST['ownerID']) || !ctype_digit($_POST['ownerID']) || empty($_POST['passkey'])) {
    $_SESSION['owner_login_error'] = "Both Owner ID and Passkey are required.";
    header("Location: owner-login.php");
    exit();
}

// Sanitize and connect to DB
$ownerID = (int) $_POST['ownerID'];
$passkey = $_POST['passkey'];
$conn = OpenCon();

// Check if owner exists
$checkOwner = $conn->prepare("SELECT ownerID, name FROM Owner WHERE ownerID = ?");
$checkOwner->bind_param("i", $ownerID);
$checkOwner->execute();
$result = $checkOwner->get_result();

if ($result->num_rows === 0) {
    $_SESSION['owner_login_error'] = "Owner ID not found.";
    header("Location: owner-login.php");
    CloseCon($conn);
    exit();
}

// Check Passkey (this should be hashed in production!)
if ($passkey === "000") {
    $ownerData = $result->fetch_assoc();

    // Set session variables
    $_SESSION['owner_logged_in'] = true;
    $_SESSION['owner_id'] = $ownerID;
    $_SESSION['owner_name'] = $ownerData['name'];

    // Redirect to owner portal
    header("Location: owner-viewer.php");
    exit();
} else {
    $_SESSION['owner_login_error'] = "Invalid passkey.";
    header("Location: owner-login.php");
    exit();
}

// Close connection
CloseCon($conn);
ob_end_flush();
?>
