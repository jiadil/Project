<?php
session_start();

// Get form data
$sinNum = $_POST['sinNum'];
$passkey = $_POST['passkey'];

// Validate inputs
if (empty($sinNum) || empty($passkey)) {
    $_SESSION['staff_login_error'] = "Both SSN Number and Password are required.";
    header("Location: staff-login.php");
    exit();
}

// For security, sanitize the SIN number
$sinNum = filter_var($sinNum, FILTER_SANITIZE_NUMBER_INT);

// Connect to database
include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
$conn = OpenCon();

// Check if staff exists
$checkStaff = $conn->prepare("SELECT sinNum, name FROM Staff WHERE sinNum = ?");
$checkStaff->bind_param("i", $sinNum);
$checkStaff->execute();
$result = $checkStaff->get_result();

if ($result->num_rows === 0) {
    // Staff not found
    $_SESSION['staff_login_error'] = "SSN Number not found.";
    header("Location: staff-login.php");
    CloseCon($conn);
    exit();
}

// For this demo, we'll use a simple passkey check (in a real app, you'd use hashed passwords)
if ($passkey === "000") {
    // Authentication successful
    $staffData = $result->fetch_assoc();
    
    // Check if staff is an accountant
    $checkAccountant = $conn->prepare("SELECT sinNum FROM Accountant WHERE sinNum = ?");
    $checkAccountant->bind_param("i", $sinNum);
    $checkAccountant->execute();
    $accountantResult = $checkAccountant->get_result();
    
    // Check if staff is a contractor
    $checkContractor = $conn->prepare("SELECT sinNum FROM Contractor WHERE sinNum = ?");
    $checkContractor->bind_param("i", $sinNum);
    $checkContractor->execute();
    $contractorResult = $checkContractor->get_result();
    
    // Determine role
    $role = "general";
    if ($accountantResult->num_rows > 0) {
        $role = "accountant";
    } elseif ($contractorResult->num_rows > 0) {
        $role = "contractor";
    }
    
    // Set session variables
    $_SESSION['staff_logged_in'] = true;
    $_SESSION['staff_id'] = $sinNum;
    $_SESSION['staff_name'] = $staffData['name'];
    $_SESSION['staff_role'] = $role;
    
    // Redirect to staff viewer page
    header("Location: staff-viewer.php");
} else {
    // Invalid passkey
    $_SESSION['staff_login_error'] = "Invalid password.";
    header("Location: staff-login.php");
}

CloseCon($conn);
exit();
?>