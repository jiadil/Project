<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Process Financial Statement</title>
</head>
<body>
    <div class="container mt-5">
        <?php
        session_start();
        
        // Check if staff is logged in and is an accountant
        if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true || $_SESSION['staff_role'] !== 'accountant') {
            header("Location: staff-login.php");
            exit();
        }
        
        // Process the form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            include("../../connect.php");
            $conn = OpenCon();
            
            // Get form data
            $propertyID = $_POST['propertyID'];
            $sinNum = $_POST['sinNum'];
            $cdate = $_POST['cdate'];
            $cash = $_POST['cash'];
            $debt = $_POST['debt'];
            $summary = $_POST['summary'];
            $pstatus = $_POST['pstatus'];
            $statementID = isset($_POST['statementID']) ? $_POST['statementID'] : null;
            
            // Sanitize date input - remove any extra spaces
            $cdate = trim($cdate);
            
            // Validate date format to prevent DECIMAL error
            if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $cdate)) {
                echo '<div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <p>Invalid date format. Date must be in MM/DD/YYYY format (e.g. 05/01/2022).</p>
                      </div>';
                echo '<div class="text-center mt-4">
                        <a href="prepare-statement.php?propertyID='.$propertyID.'" class="btn btn-primary">Try Again</a>
                        <a href="staff-viewer.php" class="btn btn-outline-secondary ms-2">Back to Portal</a>
                      </div>';
                exit();
            }
            
            $success = false;
            $error = '';
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // If statementID is provided, we're updating an existing statement
                if ($statementID) {
                    // Check if another accountant has already prepared this statement
                    $checkPrepared = $conn->prepare("SELECT * FROM prepared WHERE statementID = ? AND sinNum != ?");
                    $checkPrepared->bind_param("ii", $statementID, $sinNum);
                    $checkPrepared->execute();
                    $preparedResult = $checkPrepared->get_result();
                    
                    if ($preparedResult->num_rows > 0) {
                        throw new Exception("This statement has already been prepared by another accountant.");
                    }
                    
                    // Update existing statement
                    $updateStatement = $conn->prepare("UPDATE FinancialStatements_Has SET cash = ?, debt = ? WHERE statementID = ?");
                    $updateStatement->bind_param("iii", $cash, $debt, $statementID);
                    
                    if (!$updateStatement->execute()) {
                        throw new Exception("Error updating financial statement: " . $conn->error);
                    }
                    
                    // Check if this accountant has already prepared this statement
                    $checkOwnPrepared = $conn->prepare("SELECT * FROM prepared WHERE sinNum = ? AND statementID = ?");
                    $checkOwnPrepared->bind_param("ii", $sinNum, $statementID);
                    $checkOwnPrepared->execute();
                    $ownPreparedResult = $checkOwnPrepared->get_result();
                    
                    if ($ownPreparedResult->num_rows > 0) {
                        // Update existing prepared record
                        $updatePrepared = $conn->prepare("UPDATE prepared SET pstatus = ?, summary = ? WHERE sinNum = ? AND statementID = ?");
                        $updatePrepared->bind_param("siii", $pstatus, $summary, $sinNum, $statementID);
                        
                        if (!$updatePrepared->execute()) {
                            throw new Exception("Error updating prepared record: " . $conn->error);
                        }
                    } else {
                        // Insert new prepared record
                        $insertPrepared = $conn->prepare("INSERT INTO prepared (sinNum, statementID, pstatus, summary) VALUES (?, ?, ?, ?)");
                        $insertPrepared->bind_param("iisi", $sinNum, $statementID, $pstatus, $summary);
                        
                        if (!$insertPrepared->execute()) {
                            throw new Exception("Error inserting prepared record: " . $conn->error);
                        }
                    }
                } else {
                    // Check if statement for this date and property already exists
                    $checkStatement = $conn->prepare("SELECT statementID FROM FinancialStatements_Has WHERE propertyID = ? AND cdate = ?");
                    $checkStatement->bind_param("is", $propertyID, $cdate);
                    $checkStatement->execute();
                    $statementResult = $checkStatement->get_result();
                    
                    if ($statementResult->num_rows > 0) {
                        $statementData = $statementResult->fetch_assoc();
                        $statementID = $statementData['statementID'];
                        
                        // Check if another accountant has already prepared this statement
                        $checkPrepared = $conn->prepare("SELECT * FROM prepared WHERE statementID = ? AND sinNum != ?");
                        $checkPrepared->bind_param("ii", $statementID, $sinNum);
                        $checkPrepared->execute();
                        $preparedResult = $checkPrepared->get_result();
                        
                        if ($preparedResult->num_rows > 0) {
                            throw new Exception("This statement has already been prepared by another accountant.");
                        }
                        
                        // Update existing statement
                        $updateStatement = $conn->prepare("UPDATE FinancialStatements_Has SET cash = ?, debt = ? WHERE statementID = ?");
                        $updateStatement->bind_param("iii", $cash, $debt, $statementID);
                        
                        if (!$updateStatement->execute()) {
                            throw new Exception("Error updating financial statement: " . $conn->error);
                        }
                    } else {
                        // Insert new financial statement
                        $insertStatement = $conn->prepare("INSERT INTO FinancialStatements_Has (propertyID, cdate, cash, debt) VALUES (?, ?, ?, ?)");
                        $insertStatement->bind_param("isii", $propertyID, $cdate, $cash, $debt);
                        
                        if (!$insertStatement->execute()) {
                            throw new Exception("Error inserting financial statement: " . $conn->error);
                        }
                        
                        // Get the newly inserted statement ID
                        $statementID = $conn->insert_id;
                    }
                    
                    // Check if this accountant has already prepared this statement
                    $checkOwnPrepared = $conn->prepare("SELECT * FROM prepared WHERE sinNum = ? AND statementID = ?");
                    $checkOwnPrepared->bind_param("ii", $sinNum, $statementID);
                    $checkOwnPrepared->execute();
                    $ownPreparedResult = $checkOwnPrepared->get_result();
                    
                    if ($ownPreparedResult->num_rows > 0) {
                        // Update existing prepared record
                        $updatePrepared = $conn->prepare("UPDATE prepared SET pstatus = ?, summary = ? WHERE sinNum = ? AND statementID = ?");
                        $updatePrepared->bind_param("siii", $pstatus, $summary, $sinNum, $statementID);
                        
                        if (!$updatePrepared->execute()) {
                            throw new Exception("Error updating prepared record: " . $conn->error);
                        }
                    } else {
                        // Insert new prepared record
                        $insertPrepared = $conn->prepare("INSERT INTO prepared (sinNum, statementID, pstatus, summary) VALUES (?, ?, ?, ?)");
                        $insertPrepared->bind_param("iisi", $sinNum, $statementID, $pstatus, $summary);
                        
                        if (!$insertPrepared->execute()) {
                            throw new Exception("Error inserting prepared record: " . $conn->error);
                        }
                    }
                }
                
                // Commit transaction
                $conn->commit();
                $success = true;
            } catch (Exception $e) {
                // Roll back transaction on error
                $conn->rollback();
                $error = $e->getMessage();
            }
            
            if ($success) {
                echo '<div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">Success!</h4>
                        <p>Financial statement has been successfully ' . ($statementID ? 'updated' : 'created') . '.</p>
                      </div>';
                echo '<div class="text-center mt-4">
                        <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
                        <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-outline-secondary ms-2">View Property Details</a>
                      </div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <p>An error occurred while submitting the financial statement: '.$error.'</p>
                      </div>';
                echo '<div class="text-center mt-4">
                        <a href="prepare-statement.php?propertyID='.$propertyID.'" class="btn btn-primary">Try Again</a>
                        <a href="staff-viewer.php" class="btn btn-outline-secondary ms-2">Back to Portal</a>
                      </div>';
            }
            
            CloseCon($conn);
        } else {
            echo '<div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Invalid Request</h4>
                    <p>This page should only be accessed through form submission.</p>
                  </div>';
            echo '<div class="text-center mt-4">
                    <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
                  </div>';
        }
        ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>