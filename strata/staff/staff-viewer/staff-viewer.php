<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Staff Portal</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link" href="#scrollspyHeading1">My Information</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#scrollspyHeading2">Properties</a>
        </li>
        <?php
        session_start();
        if ($_SESSION['staff_role'] == 'accountant') {
            echo '<li class="nav-item">
                <a class="nav-link" href="#scrollspyHeading3">Financial Statements</a>
            </li>';
        } elseif ($_SESSION['staff_role'] == 'contractor') {
            echo '<li class="nav-item">
                <a class="nav-link" href="#scrollspyHeading3">Repair Events</a>
            </li>';
        }
        ?>
        <li class="nav-item ms-auto">
            <a class="btn btn-outline-secondary" href="/strata/logout.php">Logout</a>
        </li>
    </ul>
</nav>

<body class="d-flex flex-column">

    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Check if staff is logged in
    if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
        header("Location: staff-login.php");
        exit();
    }

    // Get staff ID from session
    $sinNum = $_SESSION['staff_id'];
    $staffRole = $_SESSION['staff_role'];

    include("../../connect.php");
    $conn = OpenCon();

    // Get staff information
    $staffInfo = $conn->query("SELECT sinNum, phoneNum, name FROM Staff WHERE sinNum = $sinNum");
    $staffData = $staffInfo->fetch_assoc();

    // Get role-specific information
    $roleInfo = null;
    if ($staffRole == 'accountant') {
        $roleQuery = $conn->query("SELECT CPALicenseNum, expirationDate FROM Accountant WHERE sinNum = $sinNum");
        $roleInfo = $roleQuery->fetch_assoc();
    } elseif ($staffRole == 'contractor') {
        $roleQuery = $conn->query("SELECT contractorLicenseNum, expirationDate FROM Contractor WHERE sinNum = $sinNum");
        $roleInfo = $roleQuery->fetch_assoc();
    }

    // Get all properties
    $properties = $conn->query("SELECT propertyID, propertyName, location FROM Property_AssignTo ORDER BY propertyID");

    ?>

    <div data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-offset="0" class="scrollspy-example container" tabindex="0">
        <div class="row mx-auto mt-5 mb-5">
            <h4 id="scrollspyHeading1">My Information</h4>
            <div class="mt-4 mb-5">
                <?php if ($staffData): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>SIN Number:</strong> <?php echo $staffData["sinNum"]; ?></p>
                                <p><strong>Name:</strong> <?php echo $staffData["name"]; ?></p>
                                <p><strong>Phone Number:</strong> <?php echo $staffData["phoneNum"]; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Role:</strong> <?php echo ucfirst($staffRole); ?></p>
                                <?php if ($roleInfo && $staffRole == 'accountant'): ?>
                                    <p><strong>CPA License Number:</strong> <?php echo $roleInfo["CPALicenseNum"]; ?></p>
                                    <p><strong>License Expiration Date:</strong> <?php echo $roleInfo["expirationDate"]; ?></p>
                                <?php elseif ($roleInfo && $staffRole == 'contractor'): ?>
                                    <p><strong>Contractor License Number:</strong> <?php echo $roleInfo["contractorLicenseNum"]; ?></p>
                                    <p><strong>License Expiration Date:</strong> <?php echo $roleInfo["expirationDate"]; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="update-staff-info.php?sinNum=<?php echo $staffData["sinNum"]; ?>&name=<?php echo $staffData["name"]; ?>&phoneNum=<?php echo $staffData["phoneNum"]; ?>" class="btn btn-primary">Update My Information</a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    Your information could not be found. Please contact the administrator.
                </div>
                <?php endif; ?>
            </div>

            <h4 id="scrollspyHeading2">Properties</h4>
            <div class="mt-4 mb-5">
                <?php if ($properties->num_rows > 0): ?>
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Property ID</th>
                            <th scope="col">Property Name</th>
                            <th scope="col">Location</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $properties->fetch_assoc()): ?>
                        <tr>
                            <th scope="row"><?php echo $row["propertyID"]; ?></th>
                            <td><?php echo $row["propertyName"]; ?></td>
                            <td><?php echo $row["location"]; ?></td>
                            <td>
                                <a href="property-detail.php?propertyID=<?php echo $row["propertyID"]; ?>" class="btn btn-primary btn-sm">View Details</a>
                                <?php if ($staffRole == 'accountant'): ?>
                                    <a href="prepare-statement.php?propertyID=<?php echo $row["propertyID"]; ?>" class="btn btn-success btn-sm">Prepare Statement</a>
                                <?php elseif ($staffRole == 'contractor'): ?>
                                    <a href="arrange-repair.php?propertyID=<?php echo $row["propertyID"]; ?>" class="btn btn-success btn-sm">Arrange Repair</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-info">
                    No properties found in the system.
                </div>
                <?php endif; ?>
            </div>

                            <?php if ($staffRole == 'accountant'): ?>
                <h4 id="scrollspyHeading3">My Financial Statements</h4>
                <div class="container-fluid mt-4 mb-5">
                    <?php
                    // Get financial statements prepared by this accountant only - updated for statementID
                    $statements = $conn->query("SELECT p.statementID, f.propertyID, f.cdate, p.pstatus, p.summary, pa.propertyName 
                                               FROM prepared p
                                               JOIN FinancialStatements_Has f ON p.statementID = f.statementID
                                               JOIN Property_AssignTo pa ON f.propertyID = pa.propertyID
                                               WHERE p.sinNum = $sinNum
                                               ORDER BY f.cdate DESC");
                    
                    if ($statements->num_rows > 0):
                    ?>
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Property Name</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Summary ($)</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $statements->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row["propertyName"]; ?></td>
                                <td><?php echo $row["cdate"]; ?></td>
                                <td><?php echo $row["pstatus"]; ?></td>
                                <td><?php echo $row["summary"]; ?></td>
                                <td>
                                    <a href="view-statement.php?propertyID=<?php echo $row["propertyID"]; ?>&statementID=<?php echo $row["statementID"]; ?>" class="btn btn-primary btn-sm">View</a>
                                    <?php if ($row["pstatus"] != 'completed'): ?>
                                    <a href="update-statement.php?propertyID=<?php echo $row["propertyID"]; ?>&statementID=<?php echo $row["statementID"]; ?>" class="btn btn-warning btn-sm">Update</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">
                        You haven't prepared any financial statements yet.
                    </div>
                    <?php endif; ?>
                </div>
                            <?php elseif ($staffRole == 'contractor'): ?>
                <h4 id="scrollspyHeading3">My Repair Events</h4>
                <div class="container-fluid mt-4 mb-5">
                    <?php
                    // Get repair events arranged by this contractor only
                    $repairs = $conn->query("SELECT a.eventNum, a.propertyID, a.astatus, a.budget, r.eventName, p.propertyName
                                             FROM Arrange a
                                             JOIN RepairEvent_Undergoes r ON a.eventNum = r.eventNum AND a.propertyID = r.propertyID
                                             JOIN Property_AssignTo p ON a.propertyID = p.propertyID
                                             WHERE a.sinNum = $sinNum
                                             ORDER BY a.eventNum DESC");
                    
                    if ($repairs->num_rows > 0):
                    ?>
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Event #</th>
                                <th scope="col">Property Name</th>
                                <th scope="col">Event Name</th>
                                <th scope="col">Status</th>
                                <th scope="col">Budget ($)</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $repairs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row["eventNum"]; ?></td>
                                <td><?php echo $row["propertyName"]; ?></td>
                                <td><?php echo $row["eventName"]; ?></td>
                                <td><?php echo $row["astatus"]; ?></td>
                                <td><?php echo $row["budget"]; ?></td>
                                <td>
                                    <a href="view-repair.php?eventNum=<?php echo $row["eventNum"]; ?>&propertyID=<?php echo $row["propertyID"]; ?>" class="btn btn-primary btn-sm">View</a>
                                    <?php 
                                    // Check if the event is already completed by ANY contractor
                                    $eventNum = $row["eventNum"];
                                    $propertyID = $row["propertyID"];
                                    $checkAnyCompleted = $conn->prepare("SELECT * FROM Arrange WHERE eventNum = ? AND propertyID = ? AND astatus = 'completed'");
                                    $checkAnyCompleted->bind_param("ii", $eventNum, $propertyID);
                                    $checkAnyCompleted->execute();
                                    $anyCompletedResult = $checkAnyCompleted->get_result();
                                    
                                    if ($anyCompletedResult->num_rows == 0 && $row["astatus"] != 'completed'): 
                                    ?>
                                    <a href="update-repair.php?eventNum=<?php echo $row["eventNum"]; ?>&propertyID=<?php echo $row["propertyID"]; ?>" class="btn btn-warning btn-sm">Update</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">
                        You haven't arranged any repair events yet.
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- javascripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>

<?php
include("../../display/footer.php");
CloseCon($conn);
?>

</html>