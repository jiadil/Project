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
    <title>Update Repair Status</title>
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
    
    // Get the repair event - verify this contractor arranged it
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
                    Repair event not found or you do not have permission to edit it.
                </div>
                <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
              </div>';
        exit();
    }
    
    // Check if repair is already completed
    if ($repairData['astatus'] == 'completed') {
        echo '<div class="container mt-5">
                <div class="alert alert-warning" role="alert">
                    This repair event is already marked as completed and cannot be edited.
                </div>
                <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
              </div>';
        exit();
    }
    ?>
    
    <div class="container mt-5">
        <h2>Update Repair Status</h2>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><?php echo $propertyData['propertyName']; ?> - Event #<?php echo $repairData['eventNum']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Property ID:</strong> <?php echo $propertyID; ?></p>
                        <p><strong>Property Name:</strong> <?php echo $propertyData['propertyName']; ?></p>
                        <p><strong>Event Name:</strong> <?php echo $repairData['eventName']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Event Number:</strong> <?php echo $repairData['eventNum']; ?></p>
                        <p><strong>Estimated Cost:</strong> $<?php echo $repairData['cost']; ?></p>
                        <p><strong>Current Status:</strong> <span class="badge bg-warning"><?php echo $repairData['astatus']; ?></span></p>
                    </div>
                </div>
                
                <form action="process-repair.php" method="POST">
                    <input type="hidden" name="propertyID" value="<?php echo $propertyID; ?>">
                    <input type="hidden" name="sinNum" value="<?php echo $sinNum; ?>">
                    <input type="hidden" name="eventNum" value="<?php echo $eventNum; ?>">
                    <input type="hidden" name="isNewEvent" value="0">
                    
                    <div class="mb-3">
                        <label for="budget" class="form-label">Budget ($)</label>
                        <input type="number" class="form-control" id="budget" name="budget" value="<?php echo $repairData['budget']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="astatus" class="form-label">Status</label>
                        <select class="form-select" id="astatus" name="astatus" required>
                            <option value="in progress" <?php echo ($repairData['astatus'] == 'in progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Update Repair Status</button>
                        <a href="view-repair.php?propertyID=<?php echo $propertyID; ?>&eventNum=<?php echo $eventNum; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
<?php CloseCon($conn); ?>