<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Staff Details</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
    <a class="navbar-brand" href="/strata/display/dashboard.php">Dashboard</a>
    <a class="navbar-brand" href="/strata/staff/staff.php">Back to all staff</a>
</nav>

<body>
    <?php
    include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
    $sinNum = $_GET['sinNum'];
    $conn = OpenCon();
    
    // Get staff information
    $staffInfo = $conn->prepare("SELECT s.*, 
                                 a.CPALicenseNum, a.expirationDate as accExpirationDate,
                                 c.contractorLicenseNum, c.expirationDate as conExpirationDate
                            FROM Staff s
                            LEFT JOIN Accountant a ON s.sinNum = a.sinNum
                            LEFT JOIN Contractor c ON s.sinNum = c.sinNum
                            WHERE s.sinNum = ?");
    $staffInfo->bind_param("i", $sinNum);
    $staffInfo->execute();
    $staffResult = $staffInfo->get_result();
    $staffData = $staffResult->fetch_assoc();
    
    // Check if staff exists
    if (!$staffData) {
        echo '<div class="container mt-5">
                <div class="alert alert-danger" role="alert">
                    Staff not found.
                </div>
                <a href="staff.php" class="btn btn-primary">Back to Staff List</a>
              </div>';
        exit();
    }
    
    // Determine staff role
    $isAccountant = isset($staffData['CPALicenseNum']);
    $isContractor = isset($staffData['contractorLicenseNum']);
    
    // Get financial statements prepared by this staff (if accountant)
    $financialStatements = null;
    if ($isAccountant) {
        $financialStmt = $conn->prepare("SELECT p.statementID, p.pstatus, p.summary, 
                                       f.propertyID, f.cdate, f.cash, f.debt,
                                       pr.propertyName
                                FROM prepared p
                                JOIN FinancialStatements_Has f ON p.statementID = f.statementID
                                JOIN Property_AssignTo pr ON f.propertyID = pr.propertyID
                                WHERE p.sinNum = ?
                                ORDER BY f.cdate DESC");
        $financialStmt->bind_param("i", $sinNum);
        $financialStmt->execute();
        $financialStatements = $financialStmt->get_result();
    }
    
    // Get repair events arranged by this staff (if contractor)
    $repairEvents = null;
    if ($isContractor) {
        $repairStmt = $conn->prepare("SELECT a.propertyID, a.eventNum, a.astatus, a.budget,
                                   r.eventName, r.cost,
                                   p.propertyName
                                FROM Arrange a
                                JOIN RepairEvent_Undergoes r ON a.eventNum = r.eventNum AND a.propertyID = r.propertyID
                                JOIN Property_AssignTo p ON a.propertyID = p.propertyID
                                WHERE a.sinNum = ?
                                ORDER BY a.propertyID");
        $repairStmt->bind_param("i", $sinNum);
        $repairStmt->execute();
        $repairEvents = $repairStmt->get_result();
    }
    
    // Get manager information for this staff (if any)
    $managerInfo = $conn->prepare("SELECT m.*, sm.name as managerName, sm.companyID
                                FROM Manage m
                                JOIN StrataManager_Work sm ON m.licenseNum = sm.licenseNum
                                WHERE m.sinNum = ?");
    $managerInfo->bind_param("i", $sinNum);
    $managerInfo->execute();
    $managerResult = $managerInfo->get_result();
    ?>
    
    <div class="container mt-5">
        <h2>Staff Details</h2>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><?php echo $staffData['name']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Staff ID (SSN):</strong> <?php echo $staffData['sinNum']; ?></p>
                        <p><strong>Phone Number:</strong> <?php echo $staffData['phoneNum']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <?php if ($isAccountant): ?>
                            <div class="alert alert-info">
                                <h5>Accountant</h5>
                                <p><strong>CPA License Number:</strong> <?php echo $staffData['CPALicenseNum']; ?></p>
                                <p><strong>License Expiration:</strong> <?php echo $staffData['accExpirationDate']; ?></p>
                            </div>
                        <?php elseif ($isContractor): ?>
                            <div class="alert alert-info">
                                <h5>Contractor</h5>
                                <p><strong>Contractor License Number:</strong> <?php echo $staffData['contractorLicenseNum']; ?></p>
                                <p><strong>License Expiration:</strong> <?php echo $staffData['conExpirationDate']; ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary">
                                <h5>Basic Staff</h5>
                                <p>No special role assigned</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($managerResult->num_rows > 0): ?>
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h4>Management Information</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Manager</th>
                            <th>Company ID</th>
                            <th>Training Status</th>
                            <th>Evaluation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($managerData = $managerResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $managerData['managerName']; ?></td>
                                <td><?php echo $managerData['companyID']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($managerData['trainingStatus'] == 'done' ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo $managerData['trainingStatus']; ?>
                                    </span>
                                </td>
                                <td><?php echo $managerData['evaluation']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($isAccountant && $financialStatements && $financialStatements->num_rows > 0): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4>Financial Statements Prepared</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Cash</th>
                            <th>Debt</th>
                            <th>Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($statementData = $financialStatements->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $statementData['propertyID'] . ' - ' . $statementData['propertyName']; ?></td>
                                <td><?php echo $statementData['cdate']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($statementData['pstatus'] == 'completed' ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo $statementData['pstatus']; ?>
                                    </span>
                                </td>
                                <td>$<?php echo $statementData['cash']; ?></td>
                                <td>$<?php echo $statementData['debt']; ?></td>
                                <td>$<?php echo $statementData['summary']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif ($isAccountant): ?>
            <div class="alert alert-warning">
                No financial statements have been prepared by this accountant.
            </div>
        <?php endif; ?>
        
        <?php if ($isContractor && $repairEvents && $repairEvents->num_rows > 0): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h4>Repair Events Arranged</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Event #</th>
                            <th>Event Name</th>
                            <th>Status</th>
                            <th>Budget</th>
                            <th>Cost</th>
                            <th>Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($eventData = $repairEvents->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $eventData['propertyID'] . ' - ' . $eventData['propertyName']; ?></td>
                                <td><?php echo $eventData['eventNum']; ?></td>
                                <td><?php echo $eventData['eventName']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($eventData['astatus'] == 'completed' ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo $eventData['astatus']; ?>
                                    </span>
                                </td>
                                <td>$<?php echo $eventData['budget']; ?></td>
                                <td>$<?php echo $eventData['cost']; ?></td>
                                <td>$<?php echo $eventData['budget'] - $eventData['cost']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif ($isContractor): ?>
            <div class="alert alert-warning">
                No repair events have been arranged by this contractor.
            </div>
        <?php endif; ?>
        
        <!-- Aggregate Statistics -->
        <?php if ($isAccountant): 
            // Get aggregate statistics for accountant
            $statSql = $conn->prepare("SELECT COUNT(*) as total_statements, 
                                 SUM(p.summary) as total_summary,
                                 COUNT(CASE WHEN p.pstatus = 'completed' THEN 1 END) as completed_statements,
                                 SUM(f.cash) as total_cash,
                                 SUM(f.debt) as total_debt
                          FROM prepared p
                          JOIN FinancialStatements_Has f ON p.statementID = f.statementID
                          WHERE p.sinNum = ?");
            $statSql->bind_param("i", $sinNum);
            $statSql->execute();
            $statResult = $statSql->get_result();
            $stats = $statResult->fetch_assoc();
            
            if ($stats && $stats['total_statements'] > 0):
        ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4>Accountant Statistics</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $stats['total_statements']; ?></h5>
                                <p class="card-text">Total Statements</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $stats['completed_statements']; ?></h5>
                                <p class="card-text">Completed Statements</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo $stats['total_cash']; ?></h5>
                                <p class="card-text">Total Cash Managed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo $stats['total_summary']; ?></h5>
                                <p class="card-text">Total Summary Value</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; endif; ?>
        
        <?php if ($isContractor): 
            // Get aggregate statistics for contractor
            $statSql = $conn->prepare("SELECT COUNT(*) as total_events, 
                                 SUM(r.cost) as total_cost,
                                 SUM(a.budget) as total_budget,
                                 COUNT(CASE WHEN a.astatus = 'completed' THEN 1 END) as completed_events
                          FROM Arrange a
                          JOIN RepairEvent_Undergoes r ON a.eventNum = r.eventNum AND a.propertyID = r.propertyID
                          WHERE a.sinNum = ?");
            $statSql->bind_param("i", $sinNum);
            $statSql->execute();
            $statResult = $statSql->get_result();
            $stats = $statResult->fetch_assoc();
            
            if ($stats && $stats['total_events'] > 0):
        ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4>Contractor Statistics</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $stats['total_events']; ?></h5>
                                <p class="card-text">Total Events</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $stats['completed_events']; ?></h5>
                                <p class="card-text">Completed Events</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo $stats['total_budget']; ?></h5>
                                <p class="card-text">Total Budget</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo $stats['total_cost']; ?></h5>
                                <p class="card-text">Total Cost</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; endif; ?>
        
        <div class="text-center mb-5">
            <a href="staff.php" class="btn btn-primary">Back to Staff List</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
<?php CloseCon($conn); ?>