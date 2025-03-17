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
    <title>Property Details</title>
</head>
<body>
    <?php
    
    // Check if staff is logged in
    if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
        header("Location: staff-login.php");
        exit();
    }
    
    // Get the property ID from the URL
    $propertyID = isset($_GET['propertyID']) ? $_GET['propertyID'] : 0;
    $staffRole = $_SESSION['staff_role'];
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
    
    // Check if it's a commercial or residential property
    $isCommercial = $conn->prepare("SELECT * FROM Commercial WHERE propertyID = ?");
    $isCommercial->bind_param("i", $propertyID);
    $isCommercial->execute();
    $commercialResult = $isCommercial->get_result();
    $commercialData = $commercialResult->fetch_assoc();
    
    $isResidential = $conn->prepare("SELECT * FROM Residential WHERE propertyID = ?");
    $isResidential->bind_param("i", $propertyID);
    $isResidential->execute();
    $residentialResult = $isResidential->get_result();
    $residentialData = $residentialResult->fetch_assoc();
    
    // Get financial statements for this property
    $financialInfo = $conn->prepare("SELECT * FROM FinancialStatements_Has WHERE propertyID = ? ORDER BY cdate DESC");
    $financialInfo->bind_param("i", $propertyID);
    $financialInfo->execute();
    $financialResult = $financialInfo->get_result();
    
    // Get repair events for this property
    $repairInfo = $conn->prepare("SELECT * FROM RepairEvent_Undergoes WHERE propertyID = ? ORDER BY eventNum DESC");
    $repairInfo->bind_param("i", $propertyID);
    $repairInfo->execute();
    $repairResult = $repairInfo->get_result();
    
    // Get owner information for this property
    $ownerInfo = $conn->prepare("SELECT o.ownerID, o.name, o.phoneNum, o.emailAddress, h.startDate 
                                FROM Owner o 
                                JOIN HasOwnershipOf h ON o.ownerID = h.ownerID 
                                WHERE h.propertyID = ?");
    $ownerInfo->bind_param("i", $propertyID);
    $ownerInfo->execute();
    $ownerResult = $ownerInfo->get_result();
    ?>
    
    <div class="container mt-5">
        <h2>Property Details</h2>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><?php echo $propertyData['propertyName']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Property ID:</strong> <?php echo $propertyData['propertyID']; ?></p>
                        <p><strong>Location:</strong> <?php echo $propertyData['location']; ?></p>
                        <p><strong>Company ID:</strong> <?php echo $propertyData['companyID']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <?php if ($commercialData): ?>
                            <div class="alert alert-info">
                                <h5>Commercial Property</h5>
                                <p><strong>Store Name:</strong> <?php echo $commercialData['commercialStoreName']; ?></p>
                                <p><strong>Permission Number:</strong> <?php echo $commercialData['commercialPermissionNum']; ?></p>
                            </div>
                        <?php elseif ($residentialData): ?>
                            <div class="alert alert-info">
                                <h5>Residential Property</h5>
                                <p><strong>Building Size (sqft):</strong> <?php echo $residentialData['restrictedBuildingSize']; ?></p>
                                <p><strong>Yard Area (sqft):</strong> <?php echo $residentialData['yardArea']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>Owner Information</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($ownerResult->num_rows > 0): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Owner ID</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Since</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($ownerData = $ownerResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $ownerData['ownerID']; ?></td>
                                            <td><?php echo $ownerData['name']; ?></td>
                                            <td><?php echo $ownerData['phoneNum']; ?></td>
                                            <td><?php echo $ownerData['startDate']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                No owner information found for this property.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <?php if ($staffRole == 'accountant'): ?>
                    <!-- Show Repair Events for Accountants in the upper card -->
                    <div class="card-header bg-warning text-dark">
                        <h4>Repair Events</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($repairResult->num_rows > 0): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event #</th>
                                        <th>Event Name</th>
                                        <th>Cost</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Reset pointer for repair result
                                    $repairResult->data_seek(0);
                                    
                                    while ($repairData = $repairResult->fetch_assoc()): 
                                        $eventNum = $repairData['eventNum'];
                                        
                                        // Check status from Arrange table
                                        $checkStatus = $conn->prepare("SELECT astatus FROM Arrange WHERE eventNum = ? AND propertyID = ?");
                                        $checkStatus->bind_param("ii", $eventNum, $propertyID);
                                        $checkStatus->execute();
                                        $statusResult = $checkStatus->get_result();
                                        $statusData = $statusResult->fetch_assoc();
                                    ?>
                                        <tr>
                                            <td><?php echo $repairData['eventNum']; ?></td>
                                            <td><?php echo $repairData['eventName']; ?></td>
                                            <td>$<?php echo $repairData['cost']; ?></td>
                                            <td>
                                                <?php if ($statusData): ?>
                                                    <span class="badge <?php echo ($statusData['astatus'] == 'completed' ? 'bg-success' : 'bg-warning'); ?>">
                                                        <?php echo $statusData['astatus']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not arranged</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php 
                                        $checkStatus->close();
                                        endwhile; 
                                    ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                No repair events found for this property.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <!-- Show Financial Information for Contractors and other roles in the upper card -->
                    <div class="card-header bg-info text-white">
                        <h4>Financial Information</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($financialResult->num_rows > 0): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Cash</th>
                                        <th>Debt</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($financialData = $financialResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $financialData['cdate']; ?></td>
                                            <td>$<?php echo $financialData['cash']; ?></td>
                                            <td>$<?php echo $financialData['debt']; ?></td>
                                            <td>$<?php echo $financialData['cash'] - $financialData['debt']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                No financial statements found for this property.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if ($staffRole == 'contractor'): ?>
        <!-- Show Repair Events for Contractors -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h4>Repair Events</h4>
            </div>
            <div class="card-body">
                <?php if ($repairResult->num_rows > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Event #</th>
                                <th>Event Name</th>
                                <th>Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($repairData = $repairResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $repairData['eventNum']; ?></td>
                                    <td><?php echo $repairData['eventName']; ?></td>
                                    <td>$<?php echo $repairData['cost']; ?></td>
                                    <td>
                                        <?php
                                        // Check if this contractor is already managing this event
                                        $eventNum = $repairData['eventNum'];
                                        $checkArranging = $conn->prepare("SELECT * FROM Arrange WHERE sinNum = ? AND eventNum = ? AND propertyID = ?");
                                        $checkArranging->bind_param("iii", $sinNum, $eventNum, $propertyID);
                                        $checkArranging->execute();
                                        $arrangingResult = $checkArranging->get_result();
                                        
                                        if ($arrangingResult->num_rows > 0) {
                                            // This contractor is managing this event
                                            $arrangingData = $arrangingResult->fetch_assoc();
                                            if ($arrangingData['astatus'] == 'completed') {
                                                echo '<span class="badge bg-success">Completed</span>';
                                            } else {
                                                echo '<a href="update-repair.php?eventNum='.$eventNum.'&propertyID='.$propertyID.'" class="btn btn-sm btn-warning">Update</a>';
                                            }
                                        } else {
                                            // Check if anyone else is managing this event
                                            $checkOtherArranging = $conn->prepare("SELECT a.*, s.name FROM Arrange a 
                                                                                JOIN Staff s ON a.sinNum = s.sinNum
                                                                                WHERE a.propertyID = ? AND a.eventNum = ?");
                                            $checkOtherArranging->bind_param("ii", $propertyID, $eventNum);
                                            $checkOtherArranging->execute();
                                            $otherArrangingResult = $checkOtherArranging->get_result();
                                            
                                            if ($otherArrangingResult->num_rows > 0) {
                                                echo '<span class="badge bg-secondary">Managed by another contractor</span>';
                                            } else {
                                                echo '<a href="arrange-repair.php?eventNum='.$eventNum.'&propertyID='.$propertyID.'" class="btn btn-sm btn-primary">Arrange</a>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No repair events found for this property.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($staffRole == 'accountant'): ?>
        <!-- Show Financial Statements for Accountants -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4>Financial Statements</h4>
            </div>
            <div class="card-body">
                <?php if ($financialResult->num_rows > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Cash</th>
                                <th>Debt</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset pointer for financial result
                            $financialResult->data_seek(0);
                            
                            while ($financialData = $financialResult->fetch_assoc()): 
                                $statementID = $financialData['statementID'];
                                
                                // Check if this accountant prepared this statement
                                $stmt = $conn->prepare("SELECT * FROM prepared WHERE sinNum = ? AND statementID = ?");
                                $stmt->bind_param("ii", $sinNum, $statementID);
                                $stmt->execute();
                                $preparedResult = $stmt->get_result();
                                $preparedData = $preparedResult->fetch_assoc();
                            ?>
                                <tr>
                                    <td><?php echo $financialData['cdate']; ?></td>
                                    <td>$<?php echo $financialData['cash']; ?></td>
                                    <td>$<?php echo $financialData['debt']; ?></td>
                                    <td>$<?php echo $financialData['cash'] - $financialData['debt']; ?></td>
                                    <td>
                                <?php 
                                // Check if any accountant has prepared this statement
                                $checkAnyPrepared = $conn->prepare("SELECT * FROM prepared WHERE statementID = ?");
                                $checkAnyPrepared->bind_param("i", $statementID);
                                $checkAnyPrepared->execute();
                                $anyPreparedResult = $checkAnyPrepared->get_result();
                                $anyPreparedData = $anyPreparedResult->fetch_assoc();
                                
                                if ($anyPreparedData) {
                                    // Some accountant has prepared this statement
                                    if ($anyPreparedData['sinNum'] == $sinNum) {
                                        // This accountant prepared it
                                        echo '<span class="badge ' . ($anyPreparedData['pstatus'] == 'completed' ? 'bg-success' : 'bg-warning') . '">';
                                        echo $anyPreparedData['pstatus'] . '</span>';
                                    } else {
                                        // Another accountant prepared it
                                        echo '<span class="badge bg-secondary">Prepared by another accountant</span>';
                                    }
                                } else {
                                    // No one has prepared this statement
                                    echo '<span class="badge bg-secondary">Not managed</span>';
                                }
                                ?>
                                    </td>
                                    <td>
                                        <?php if ($anyPreparedData): ?>
                                            <!-- Check if this accountant prepared this statement -->
                                            <?php if ($anyPreparedData['sinNum'] == $sinNum): ?>
                                                <a href="view-statement.php?propertyID=<?php echo $propertyID; ?>&statementID=<?php echo $statementID; ?>" class="btn btn-sm btn-primary">View</a>
                                                <?php if ($anyPreparedData['pstatus'] != 'completed'): ?>
                                                    <a href="update-statement.php?propertyID=<?php echo $propertyID; ?>&statementID=<?php echo $statementID; ?>" class="btn btn-sm btn-warning">Update</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="prepare-statement.php?propertyID=<?php echo $propertyID; ?>&statementID=<?php echo $statementID; ?>" class="btn btn-sm btn-success">Prepare</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                $stmt->close();
                                endwhile; 
                            ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No financial statements found for this property.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="text-center mb-5">
            <?php if ($staffRole == 'accountant'): ?>
                <a href="prepare-statement.php?propertyID=<?php echo $propertyID; ?>" class="btn btn-success me-2">Prepare Financial Statement</a>
            <?php elseif ($staffRole == 'contractor'): ?>
                <a href="arrange-repair.php?propertyID=<?php echo $propertyID; ?>" class="btn btn-success me-2">Arrange New Repair Event</a>
            <?php endif; ?>
            <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
<?php CloseCon($conn); ?>