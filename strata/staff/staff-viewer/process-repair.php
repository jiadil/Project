<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Process Repair Event</title>
</head>
<body>
    <div class="container mt-5">
        <?php
        session_start();
        
        // Check if staff is logged in and is a contractor
        if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true || $_SESSION['staff_role'] !== 'contractor') {
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
            $eventNum = $_POST['eventNum'];
            $isNewEvent = $_POST['isNewEvent'];
            $budget = $_POST['budget'];
            $astatus = $_POST['astatus'];
            
            // Check if event is already completed by any contractor
            $checkCompleted = $conn->prepare("SELECT * FROM Arrange WHERE eventNum = ? AND propertyID = ? AND astatus = 'completed'");
            $checkCompleted->bind_param("ii", $eventNum, $propertyID);
            $checkCompleted->execute();
            $completedResult = $checkCompleted->get_result();
            
            if ($completedResult->num_rows > 0) {
                echo '<div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <p>This repair event has already been completed and cannot be modified.</p>
                      </div>';
                echo '<div class="text-center mt-4">
                        <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-primary">Back to Property Details</a>
                      </div>';
                CloseCon($conn);
                exit();
            }
            
            // Check if any other contractor is already arranging this event
            if ($isNewEvent == 0) { // Only for existing events
                $checkOtherArranging = $conn->prepare("SELECT * FROM Arrange WHERE eventNum = ? AND propertyID = ? AND sinNum != ?");
                $checkOtherArranging->bind_param("iii", $eventNum, $propertyID, $sinNum);
                $checkOtherArranging->execute();
                $otherArrangingResult = $checkOtherArranging->get_result();
                
                if ($otherArrangingResult->num_rows > 0) {
                    echo '<div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">Error!</h4>
                            <p>This repair event is already being managed by another contractor.</p>
                          </div>';
                    echo '<div class="text-center mt-4">
                            <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-primary">Back to Property Details</a>
                          </div>';
                    CloseCon($conn);
                    exit();
                }
            }
            
            $success = false;
            $error = '';
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // If this is a new event, create it first
                if ($isNewEvent == 1) {
                    $eventName = $_POST['eventName'];
                    $cost = $_POST['cost'];
                    
                    $insertEvent = $conn->prepare("INSERT INTO RepairEvent_Undergoes (propertyID, eventNum, eventName, cost) VALUES (?, ?, ?, ?)");
                    $insertEvent->bind_param("iisi", $propertyID, $eventNum, $eventName, $cost);
                    $insertEvent->execute();
                }
                
                // Check if contractor is already arranging this event
                $checkArrange = $conn->prepare("SELECT * FROM Arrange WHERE sinNum = ? AND propertyID = ? AND eventNum = ?");
                $checkArrange->bind_param("iii", $sinNum, $propertyID, $eventNum);
                $checkArrange->execute();
                $arrangeResult = $checkArrange->get_result();
                
                if ($arrangeResult->num_rows > 0) {
                    // Update existing arrangement
                    $updateArrange = $conn->prepare("UPDATE Arrange SET astatus = ?, budget = ? WHERE sinNum = ? AND propertyID = ? AND eventNum = ?");
                    $updateArrange->bind_param("siiii", $astatus, $budget, $sinNum, $propertyID, $eventNum);
                    $updateArrange->execute();
                } else {
                    // Insert new arrangement
                    $insertArrange = $conn->prepare("INSERT INTO Arrange (sinNum, eventNum, propertyID, astatus, budget) VALUES (?, ?, ?, ?, ?)");
                    $insertArrange->bind_param("iiisi", $sinNum, $eventNum, $propertyID, $astatus, $budget);
                    $insertArrange->execute();
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
                        <p>Repair event has been successfully ' . ($isNewEvent == 1 ? 'created and' : '') . ' arranged.</p>
                      </div>';
                echo '<div class="text-center mt-4">
                        <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
                        <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-outline-secondary ms-2">View Property Details</a>
                      </div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <p>An error occurred while processing the repair event: '.$error.'</p>
                      </div>';
                echo '<div class="text-center mt-4">
                        <a href="arrange-repair.php?propertyID='.$propertyID.'&eventNum='.$eventNum.'" class="btn btn-primary">Try Again</a>
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