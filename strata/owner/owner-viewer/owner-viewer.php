<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if owner is logged in
if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
    header("Location: owner-login.php");
    exit();
}

// Get owner ID from session
$ownerID = $_SESSION['owner_id'];

include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
$conn = OpenCon();

// Get owner information
$ownerInfo = $conn->query("SELECT ownerID, phoneNum, name, emailAddress FROM Owner WHERE ownerID = $ownerID");
$ownerData = $ownerInfo->fetch_assoc();

// Get owner's properties
$properties = $conn->query("SELECT p.propertyID, p.propertyName, p.location, h.startDate 
                            FROM Property_AssignTo p 
                            JOIN HasOwnershipOf h ON p.propertyID = h.propertyID 
                            WHERE h.ownerID = $ownerID");

// Get council meetings for this owner
$council = $conn->query("SELECT Holds.meetingID, Holds.duration, Holds.outcome, CouncilMeeting.cdate, CouncilMeeting.location
                        FROM Holds
                        INNER JOIN CouncilMeeting ON Holds.meetingID = CouncilMeeting.meetingID
                        WHERE Holds.ownerID = $ownerID
                        ORDER BY meetingID");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Owner Portal</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/logout.php">Home</a>
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link" href="#scrollspyHeading1">My Information</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#scrollspyHeading2">My Properties</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#scrollspyHeading3">Council Meetings</a>
        </li>
        <li class="nav-item ms-auto">
            <a class="btn btn-outline-secondary" href="/strata/logout.php">Logout</a>
        </li>
    </ul>
</nav>

<body class="d-flex flex-column">

    

    <div data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-offset="0" class="scrollspy-example container" tabindex="0">
        <div class="row mx-auto mt-5 mb-5">
            <h4 id="scrollspyHeading1">My Information</h4>
            <div class="mt-4 mb-5">
                <?php if ($ownerData): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Owner ID:</strong> <?php echo $ownerData["ownerID"]; ?></p>
                                <p><strong>Name:</strong> <?php echo $ownerData["name"]; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone Number:</strong> <?php echo $ownerData["phoneNum"]; ?></p>
                                <p><strong>Email:</strong> <?php echo $ownerData["emailAddress"]; ?></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="update-owner-info.php?ownerID=<?php echo $ownerData["ownerID"]; ?>&name=<?php echo $ownerData["name"]; ?>&phoneNum=<?php echo $ownerData["phoneNum"]; ?>&emailAddress=<?php echo $ownerData["emailAddress"]; ?>" class="btn btn-primary">Update My Information</a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    Your information could not be found. Please contact the administrator.
                </div>
                <?php endif; ?>
            </div>

            <h4 id="scrollspyHeading2">My Properties</h4>
            <div class="mt-4 mb-5">
                <?php if ($properties->num_rows > 0): ?>
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Property ID</th>
                            <th scope="col">Property Name</th>
                            <th scope="col">Location</th>
                            <th scope="col">Ownership Since</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $properties->fetch_assoc()): ?>
                        <tr>
                            <th scope="row"><?php echo $row["propertyID"]; ?></th>
                            <td><?php echo $row["propertyName"]; ?></td>
                            <td><?php echo $row["location"]; ?></td>
                            <td><?php echo $row["startDate"]; ?></td>
                            <td>
                                <a href="/strata/property/detail.php?propertyID=<?php echo $row["propertyID"]; ?>" class="btn btn-primary btn-sm">View Details</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-info">
                    You don't own any properties yet.
                </div>
                <?php endif; ?>
            </div>

            <h4 id="scrollspyHeading3">Council Meetings</h4>
            <div class="container-fluid mt-4 mb-5">
                <?php if ($council->num_rows > 0): ?>
                <table class="table table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Meeting ID</th>
                            <th scope="col">Meeting Date</th>
                            <th scope="col">Location</th>
                            <th scope="col">Duration (minutes)</th>
                            <th scope="col">Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $council->fetch_assoc()): ?>
                        <tr>
                            <th scope="row"><?php echo $row["meetingID"]; ?></th>
                            <td><?php echo $row["cdate"]; ?></td>
                            <td><?php echo $row["location"]; ?></td>
                            <td><?php echo $row["duration"]; ?></td>
                            <td><?php echo $row["outcome"]; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-info">
                    You don't have any upcoming or past council meetings.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- javascripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . "/strata/display/footer.php");
CloseCon($conn);
?>

</html>