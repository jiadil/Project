<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Owner</title>
</head>

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

<body class="d-flex flex-column">

    <?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    session_start();
    include("../connect.php"); // Ensure this file exists and is accessible

    $conn = OpenCon();
    $result = $conn->query("SELECT ownerID, phoneNum, name, emailAddress FROM Owner");
    $council = $conn->query("SELECT Holds.meetingID, Holds.duration, Holds.outcome, CouncilMeeting.cdate, CouncilMeeting.location
        FROM Holds
        INNER JOIN CouncilMeeting ON Holds.meetingID = CouncilMeeting.meetingID
        ORDER BY meetingID");
    ?>

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
                            // output data of each row
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <tr>
                                    <th scope="row"><?php echo $row["ownerID"] ?></td>
                                    <td><?php echo $row["name"] ?></td>
                                    <td><?php echo $row["phoneNum"] ?></td>
                                    <td><?php echo $row["emailAddress"] ?></td>

                                    <td><a href="update.php?ownerID=<?php echo $row["ownerID"] ?>&name=<?php echo $row["name"] ?>&phoneNum=<?php echo $row["phoneNum"] ?>&emailAddress=<?php echo $row["emailAddress"] ?>" class="btn btn-primary">edit</a>
                                        <a href="delete.php?ownerID=<?php echo $row["ownerID"] ?>" class="btn btn-danger">delete</a>
                                        <a href="detail.php?ownerID=<?php echo $row["ownerID"] ?>" class="btn btn-success">detail</a>
                                    </td>
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


            <h4 id="scrollspyHeading2">Insert an new Owner</h4>
            <div class="container-fluid mt-4 mb-5">
                <div class="col-md-6 offset-md-3">
                    <form id="form" action="process-insert.php" method="POST">
                        <div class="mb-3">
                            <label for="id" class="form-label">OwnerID</label>
                            <input type="text" class="form-control" id="id" name="id" placeholder="Enter OwnerID in the format of 1,2,3,..." required>
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

            <h4 id="scrollspyHeading3">Council Meeting</h4>
            <div class="container-fluid mt-4 mb-5">
                <table class="table table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Meeting ID</th>
                            <th scope="col">Meeting Date</th>
                            <th scope="col">Location</th>
                            <th scope="col">Duration</th>
                            <th scope="col">Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($council->num_rows > 0) {
                            // output data of each row
                            while ($row = $council->fetch_assoc()) {
                        ?>
                                <tr>
                                    <th scope="row"><?php echo $row["meetingID"] ?></td>
                                    <td><?php echo $row["cdate"] ?></td>
                                    <td><?php echo $row["location"] ?></td>
                                    <td><?php echo $row["duration"] ?></td>
                                    <td><?php echo $row["outcome"] ?></td>
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
    </div>

    <!-- javascripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js" integrity="sha384-oesi62hOLfzrys4LxRF63OJCXdXDipiYWBnvTl9Y9/TRlw5xlKIEHpNyvvDShgf/" crossorigin="anonymous"></script>

</body>

<?php
include("../display/footer.php");
?>

</html>