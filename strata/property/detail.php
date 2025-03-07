<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Detail</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
    <a class="navbar-brand" href="/strata/display/dashboard.php">Dashboard</a>
    <?php
    // Check if the user is logged in as an owner
    session_start();
    if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
        // Only show "Back to all properties" if NOT logged in as an owner
        echo '<a class="navbar-brand" href="/strata/property/property.php">Back to all properties</a>';
    } else {
        // For owners, show back to owner portal instead
        echo '<a class="navbar-brand" href="/strata/owner/owner-viewer/owner-viewer.php">Back to Owner Portal</a>';
    }
    ?>
</nav>

<body class="d-flex flex-column">

    <?php
    include '../connect.php';
    $id = $_GET['propertyID'];
    $conn = OpenCon();
    $result = $conn->query("SELECT propertyID, propertyName, location, companyID 
        FROM Property_AssignTo
        WHERE Property_AssignTo.propertyID = '$id'");
    $company = $conn->query("SELECT StrataManagementCompany.companyID, StrataManagementCompany.name, StrataManagementCompany.address
        FROM StrataManagementCompany
        INNER JOIN Property_AssignTo ON StrataManagementCompany.companyID = Property_AssignTo.companyID
        WHERE Property_AssignTo.propertyID = '$id'
        ORDER BY companyID");
    $sortC = $conn->query("SELECT Commercial.propertyID, Commercial.commercialStoreName, Commercial.commercialPermissionNum
        FROM Commercial
        JOIN Property_AssignTo ON Commercial.propertyID = Property_AssignTo.propertyID
        WHERE Property_AssignTo.propertyID = '$id'");
    $sortR = $conn->query("SELECT Residential.propertyID, Residential.restrictedBuildingSize, Residential.yardArea
        FROM Residential
        JOIN Property_AssignTo ON Residential.propertyID = Property_AssignTo.propertyID
        WHERE Property_AssignTo.propertyID = '$id'");
    $statF = $conn->query("SELECT f.cdate, p.summary, p.pstatus, f.cash, f.debt
        FROM prepared p
        JOIN FinancialStatements_Has f 
        ON p.statementID = f.statementID
        WHERE f.propertyID = $id");
    $statE = $conn->query("SELECT repairevent_undergoes.eventNum, repairevent_undergoes.eventName, arrange.budget, repairevent_undergoes.cost, arrange.astatus
        FROM `repairevent_undergoes`
        JOIN `arrange`
        ON repairevent_undergoes.eventNum = arrange.eventNum AND repairevent_undergoes.propertyID = arrange.propertyID
        WHERE arrange.propertyID = $id
        GROUP BY arrange.propertyID");
    
    // Add owner query
    $ownerQuery = $conn->query("SELECT o.ownerID, o.name, o.phoneNum, o.emailAddress, h.startDate 
        FROM Owner o 
        JOIN HasOwnershipOf h ON o.ownerID = h.ownerID 
        WHERE h.propertyID = '$id'");
    ?>

    <div class="container">
        <div class="row mt-md-5">
            <div class="col-md-3">
                <?php
                if ($result->num_rows > 0) {
                    // output data of each row
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row["propertyName"] ?></h5>
                                <p class="card-text">Property ID: <?php echo $row["propertyID"] ?></p>
                            </div>
                            <ul class="list-group list-group-flush">
                                <?php
                                if ($sortC->num_rows > 0) {
                                ?>
                                    <li class="list-group-item">Commercial</li>
                                <?php
                                } else {
                                ?>
                                    <li class="list-group-item">Residential</li>
                                <?php
                                }
                                ?>
                                <li class="list-group-item"><?php echo $row["location"] ?></li>
                            </ul>
                        </div>
                <?php
                    }
                } else
                    echo "0 results";
                //CloseCon($conn);
                ?>
            </div>

            <div class="col-md-9 mb-5">
                <!-- Owner Section - New Card -->
                <div class="card mb-5">
                    <div class="card-header">
                        Owner Information
                    </div>
                    <div class="card-body">
                        <?php if ($ownerQuery->num_rows > 0) { ?>
                            <table class="table table-hover text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Owner ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Phone Number</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Ownership Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($owner = $ownerQuery->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo $owner["ownerID"] ?></td>
                                            <td><?php echo $owner["name"] ?></td>
                                            <td><?php echo $owner["phoneNum"] ?></td>
                                            <td><?php echo $owner["emailAddress"] ?></td>
                                            <td><?php echo $owner["startDate"] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div class="alert alert-info" role="alert">
                                No owner assigned to this property.
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-header">
                        Company
                    </div>
                    <div class="card-body">
                        <table class="table table-hover text-center">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Property ID</th>
                                    <th scope="col">Property Name</th>
                                    <th scope="col">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($company->num_rows > 0) {
                                    // output data of each row
                                    while ($row = $company->fetch_assoc()) {
                                ?>
                                        <tr>
                                            <th scope="row"><?php echo $row["companyID"] ?></td>
                                            <td><?php echo $row["name"] ?></td>
                                            <td><?php echo $row["address"] ?></td>
                                        </tr>
                                <?php
                                    }
                                } else
                                    echo "0 results";
                                //CloseCon($conn);
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-header">
                        Category
                    </div>
                    <div class="card-body">
                        <?php
                        if ($sortC->num_rows > 0) {
                        ?>
                            <table class="table table-hover text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Commercial Store Name</th>
                                        <th scope="col">Commercial Permission Number</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $sortC->fetch_assoc()) {
                                    ?>
                                        <tr>
                                            <td><?php echo $row["commercialStoreName"] ?></td>
                                            <td><?php echo $row["commercialPermissionNum"] ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        <?php
                        } else {
                        ?>
                            <table class="table table-hover text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Restricted Building Size</th>
                                        <th scope="col">Yard Area</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $sortR->fetch_assoc()) {
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $row["restrictedBuildingSize"] ?></td>
                                            <td><?php echo $row["yardArea"] ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        <?php
                        }
                        ?>

                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-header">
                        Financial Statement
                    </div>
                    <div class="card-body">
                        <table class="table table-hover text-center">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Cash</th>
                                    <th scope="col">Debt</th>
                                    <th scope="col">Summary</th>
                                    <th scope="col">Process Date</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($statF->num_rows > 0) {
                                    // output data of each row
                                    while ($row = $statF->fetch_assoc()) {
                                ?>
                                        <tr>
                                            <td><?php echo $row["cash"] ?></td>
                                            <td><?php echo $row["debt"] ?></td>
                                            <td><?php echo $row["summary"] ?></td>
                                            <td><?php echo $row["cdate"] ?></td>
                                            <td><?php echo $row["pstatus"] ?></td>
                                        </tr>

                                <?php
                                    }
                                } else
                                    echo "0 results";
                                //CloseCon($conn);
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-header">
                        Repair Event
                    </div>
                    <div class="card-body">
                        <table class="table table-hover text-center">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Event Number</th>
                                    <th scope="col">Event Name</th>
                                    <th scope="col">Budget</th>
                                    <th scope="col">Cost</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($statE->num_rows > 0) {
                                    // output data of each row
                                    while ($row = $statE->fetch_assoc()) {
                                ?>
                                        <tr>
                                            <td><?php echo $row["eventNum"] ?></td>
                                            <td><?php echo $row["eventName"] ?></td>
                                            <td><?php echo $row["budget"] ?></td>
                                            <td><?php echo $row["cost"] ?></td>
                                            <td><?php echo $row["astatus"] ?></td>
                                        </tr>

                                <?php
                                    }
                                } else
                                    echo "0 results";
                                CloseCon($conn);
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>




            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js" integrity="sha384-oesi62hOLfzrys4LxRF63OJCXdXDipiYWBnvTl9Y9/TRlw5xlKIEHpNyvvDShgf/" crossorigin="anonymous"></script>

</body>

<?php
include("../display/footer.php");
?>

</html>