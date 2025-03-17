<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure session starts before any output is sent
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure connect.php is loaded
include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");

// Ensure OpenCon function is available
if (!function_exists('OpenCon')) {
    die("<p style='color: red;'>❌ ERROR: OpenCon() function not found. Check connect.php</p>");
}

// Connect to the database
$conn = OpenCon();

// Fetch data from the database
$result = $conn->query("SELECT ownerID, phoneNum, name, emailAddress FROM Owner");
if (!$result) {
    die("<p style='color: red;'>❌ ERROR: Failed to fetch Owners: " . $conn->error . "</p>");
}

$council = $conn->query("SELECT Holds.meetingID, Holds.duration, Holds.outcome, CouncilMeeting.cdate, CouncilMeeting.location
        FROM Holds
        INNER JOIN CouncilMeeting ON Holds.meetingID = CouncilMeeting.meetingID
        ORDER BY meetingID");
if (!$council) {
    die("<p style='color: red;'>❌ ERROR: Failed to fetch Council Meetings: " . $conn->error . "</p>");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Owner</title>
</head>

<body class="d-flex flex-column">
    <nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
        <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
        <a class="navbar-brand" href="/strata/display/dashboard.php">Dashboard</a>
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link" href="#scrollspyHeading1">All owners</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#scrollspyHeading2">Insert</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#scrollspyHeading3">Council meeting</a>
            </li>
        </ul>
    </nav>

    <div data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-offset="0" class="scrollspy-example container" tabindex="0">
        <div class="row mx-auto mt-5 mb-5">
            <h4 id="scrollspyHeading1">All Owners</h4>
            <div class="mt-4 mb-5">
                <table class="table table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">OwnerID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Phone Number</th>
                            <th scope="col">Email Address</th>
                            <th scope="col"> </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <tr>
                                    <th scope="row"><?php echo htmlspecialchars($row["ownerID"]); ?></th>
                                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["phoneNum"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["emailAddress"]); ?></td>
                                    <td>
                                        <a href="update.php?ownerID=<?php echo urlencode($row["ownerID"]); ?>&name=<?php echo urlencode($row["name"]); ?>&phoneNum=<?php echo urlencode($row["phoneNum"]); ?>&emailAddress=<?php echo urlencode($row["emailAddress"]); ?>" class="btn btn-primary">Edit</a>
                                        <a href="delete.php?ownerID=<?php echo urlencode($row["ownerID"]); ?>" class="btn btn-danger">Delete</a>
                                        <a href="detail.php?ownerID=<?php echo urlencode($row["ownerID"]); ?>" class="btn btn-success">Detail</a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='5'>0 results</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <h4 id="scrollspyHeading2">Insert a New Owner</h4>
            <div class="container-fluid mt-4 mb-5">
                <div class="col-md-6 offset-md-3">
                    <form id="form" action="process-insert.php" method="POST">
                        <div class="mb-3">
                            <label for="id" class="form-label">OwnerID</label>
                            <input type="text" class="form-control" id="id" name="id" placeholder="Enter OwnerID (1,2,3...)" required>
                            <div class="form-text">OwnerID can't be changed once set up!</div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number" required>
                            <div class="form-text">Phone number should be 10 digits</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary mt-3">Insert</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <?php
    // Ensure footer.php is loaded properly
    $footerPath = $_SERVER['DOCUMENT_ROOT'] . "/strata/display/footer.php";
    if (!file_exists($footerPath)) {
        die("<p style='color: red;'>❌ ERROR: footer.php not found at $footerPath</p>");
    }
    include($footerPath);
    ?>
</body>

</html>
