<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>View Repair Event</title>
</head>
<body>
    <?php

    
    // Check if staff is logged in and is a contractor
    if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true || $_SESSION['staff_role'] !== 'contractor') {
        header("Location: staff-login.php");
        exit();
    }
    
    // Get the parameters from the URL
    $propertyID = isset($_GET['propertyID']) ? $_GET['propertyID'] : 0;
    $eventNum = isset($_GET['eventNum']) ? $_GET['eventNum'] : 0;
    $sinNum = $_SESSION['staff_id'];
    
    include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
    $conn = OpenCon();
    
    // Get property information
    $propertyInfo = $conn->prepare("SELECT * FROM Property_AssignTo WHERE propertyID = ?");
    $propertyInfo->bind_param("i", $propertyID);
    $propertyInfo->execute();
    $propertyResult = $propertyInfo->get_result();
    $propertyData = $propertyResult->fetch_assoc();
    
    // Check if property exists
    if (!$propertyData) {
        echo '<div class="container mt-5">
                <div class="alert alert-danger" role="alert">
                    Property not found.
                </div>
                <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
              </div>';
        exit();
    }
    
    // Get the repair event
    $repairInfo = $conn->prepare("SELECT r.*, a.astatus, a.budget 
                                 FROM RepairEvent_Undergoes r
                                 JOIN Arrange a ON r.propertyID = a.propertyID AND r.eventNum = a.eventNum
                                 WHERE r.propertyID = ? AND r.eventNum = ? AND a.sinNum = ?");
    $repairInfo->bind_param("iii", $propertyID, $eventNum, $sinNum);
    $repairInfo->execute();
    $repairResult = $repairInfo->get_result();
    $repairData = $repairResult->fetch_assoc();
    
    // Check if repair event exists
    if (!$repairData) {
        echo '<div class="container mt-5">
                <div class="alert alert-danger" role="alert">
                    Repair event not found or you do not have permission to view it.
                </div>
                <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
              </div>';
        exit();
    }
    ?>
    
    <div class="container mt-5">
        <h2>Repair Event Details</h2>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><?php echo $propertyData['propertyName']; ?> - Event #<?php echo $repairData['eventNum']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Property ID:</strong> <?php echo $propertyID; ?></p>
                        <p><strong>Property Name:</strong> <?php echo $propertyData['propertyName']; ?></p>
                        <p><strong>Location:</strong> <?php echo $propertyData['location']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Event Number:</strong> <?php echo $repairData['eventNum']; ?></p>
                        <p><strong>Status:</strong> <span class="badge <?php echo $repairData['astatus'] == 'completed' ? 'bg-success' : 'bg-warning'; ?>"><?php echo $repairData['astatus']; ?></span></p>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h5>Repair Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Event Name:</strong> <?php echo $repairData['eventName']; ?></p>
                            <p><strong>Estimated Cost:</strong> $<?php echo $repairData['cost']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Budget Allocated:</strong> $<?php echo $repairData['budget']; ?></p>
                            <p><strong>Status:</strong> <?php echo $repairData['astatus']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <?php if ($repairData['astatus'] != 'completed'): ?>
                    <a href="update-repair.php?propertyID=<?php echo $propertyID; ?>&eventNum=<?php echo $eventNum; ?>" class="btn btn-warning me-2">Update Status</a>
                    <?php endif; ?>
                    <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
<?php CloseCon($conn); ?>