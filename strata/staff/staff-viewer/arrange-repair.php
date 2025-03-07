<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Arrange Repair Event</title>
</head>
<body>
    <?php
    session_start();
    
    // Check if staff is logged in and is a contractor
    if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true || $_SESSION['staff_role'] !== 'contractor') {
        header("Location: staff-login.php");
        exit();
    }
    
    // Get the property ID from the URL
    $propertyID = isset($_GET['propertyID']) ? $_GET['propertyID'] : 0;
    $eventNum = isset($_GET['eventNum']) ? $_GET['eventNum'] : 0;
    $sinNum = $_SESSION['staff_id'];
    
    include("../../connect.php");
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
    
    $repairEventData = null;
    $isNewEvent = true;
    $arrangeData = null;
    
    // If eventNum is provided, get event details
    if ($eventNum > 0) {
        $eventInfo = $conn->prepare("SELECT * FROM RepairEvent_Undergoes WHERE propertyID = ? AND eventNum = ?");
        $eventInfo->bind_param("ii", $propertyID, $eventNum);
        $eventInfo->execute();
        $eventResult = $eventInfo->get_result();
        $repairEventData = $eventResult->fetch_assoc();
        
        if ($repairEventData) {
            $isNewEvent = false;
            
            // Check if this event is already completed by any contractor
            $checkCompleted = $conn->prepare("SELECT a.*, s.name FROM Arrange a 
                                           JOIN Staff s ON a.sinNum = s.sinNum
                                           WHERE a.propertyID = ? AND a.eventNum = ? AND a.astatus = 'completed'");
            $checkCompleted->bind_param("ii", $propertyID, $eventNum);
            $checkCompleted->execute();
            $completedResult = $checkCompleted->get_result();
            
            if ($completedResult->num_rows > 0) {
                $completedData = $completedResult->fetch_assoc();
                echo '<div class="container mt-5">
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">Event Already Completed</h4>
                            <p>This repair event has already been completed by contractor: ' . $completedData['name'] . '</p>
                            <p>Completed events cannot be modified or re-arranged.</p>
                        </div>
                        <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-primary">Back to Property Details</a>
                      </div>';
                exit();
            }
            
            // Check if this contractor is already arranging this event
            $arrangeInfo = $conn->prepare("SELECT * FROM Arrange WHERE sinNum = ? AND propertyID = ? AND eventNum = ?");
            $arrangeInfo->bind_param("iii", $sinNum, $propertyID, $eventNum);
            $arrangeInfo->execute();
            $arrangeResult = $arrangeInfo->get_result();
            $arrangeData = $arrangeResult->fetch_assoc();
            
            // If this contractor is not arranging it, check if someone else is
            if (!$arrangeData) {
                $otherArrangeInfo = $conn->prepare("SELECT a.*, s.name FROM Arrange a 
                                                 JOIN Staff s ON a.sinNum = s.sinNum
                                                 WHERE a.propertyID = ? AND a.eventNum = ?");
                $otherArrangeInfo->bind_param("ii", $propertyID, $eventNum);
                $otherArrangeInfo->execute();
                $otherArrangeResult = $otherArrangeInfo->get_result();
                
                if ($otherArrangeResult->num_rows > 0) {
                    // Someone else is already managing this event
                    $otherArrangeData = $otherArrangeResult->fetch_assoc();
                    echo '<div class="container mt-5">
                            <div class="alert alert-warning" role="alert">
                                <h4 class="alert-heading">Event Already Managed</h4>
                                <p>This repair event is already being managed by contractor: ' . $otherArrangeData['name'] . '</p>
                            </div>
                            <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-primary">Back to Property Details</a>
                          </div>';
                    exit();
                }
            }
        }
    }
    
    // If creating a new event, get the next event number
    if ($isNewEvent) {
        $maxEventNum = $conn->query("SELECT MAX(eventNum) as maxNum FROM RepairEvent_Undergoes");
        $maxData = $maxEventNum->fetch_assoc();
        $nextEventNum = $maxData['maxNum'] + 1;
    }
    ?>
    
    <div class="container mt-5">
        <h2><?php echo $isNewEvent ? 'Create New Repair Event' : 'Arrange Existing Repair Event'; ?></h2>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><?php echo $propertyData['propertyName']; ?></h4>
            </div>
            <div class="card-body">
                <p><strong>Property ID:</strong> <?php echo $propertyData['propertyID']; ?></p>
                <p><strong>Location:</strong> <?php echo $propertyData['location']; ?></p>
                
                <?php if (!$isNewEvent): ?>
                <div class="alert alert-info">
                    <h5>Repair Event #<?php echo $repairEventData['eventNum']; ?></h5>
                    <p><strong>Event Name:</strong> <?php echo $repairEventData['eventName']; ?></p>
                    <p><strong>Estimated Cost:</strong> $<?php echo $repairEventData['cost']; ?></p>
                </div>
                <?php endif; ?>
                
                <form action="process-repair.php" method="POST">
                    <input type="hidden" name="propertyID" value="<?php echo $propertyID; ?>">
                    <input type="hidden" name="sinNum" value="<?php echo $sinNum; ?>">
                    
                    <?php if ($isNewEvent): ?>
                        <input type="hidden" name="isNewEvent" value="1">
                        <input type="hidden" name="eventNum" value="<?php echo $nextEventNum; ?>">
                        
                        <div class="mb-3">
                            <label for="eventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="eventName" name="eventName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cost" class="form-label">Estimated Cost ($)</label>
                            <input type="number" class="form-control" id="cost" name="cost" required>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="isNewEvent" value="0">
                        <input type="hidden" name="eventNum" value="<?php echo $eventNum; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="budget" class="form-label">Budget ($)</label>
                        <input type="number" class="form-control" id="budget" name="budget" 
                               value="<?php echo $arrangeData ? $arrangeData['budget'] : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="astatus" class="form-label">Status</label>
                        <select class="form-select" id="astatus" name="astatus" required>
                            <option value="in progress" <?php echo ($arrangeData && $arrangeData['astatus'] == 'in progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo ($arrangeData && $arrangeData['astatus'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <?php echo $isNewEvent ? 'Create and Arrange Repair Event' : ($arrangeData ? 'Update Arrangement' : 'Arrange Repair Event'); ?>
                        </button>
                        <a href="property-detail.php?propertyID=<?php echo $propertyID; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
<?php CloseCon($conn); ?>