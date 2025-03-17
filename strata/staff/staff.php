<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Staff</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
    <a class="navbar-brand" href="/strata/display/dashboard.php">Dashboard</a>
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link" href="#scrollspyHeading1">All Staff</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#scrollspyHeading2">Insert</a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Dropdown</a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#scrollspyHeading3">Sort</a></li>
            </ul>
        </li>
    </ul>
</nav>

<body>
    <?php
    include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
    $conn = OpenCon();
    $sql = "SELECT sinNum, phoneNum, name FROM Staff";
    $result = $conn->query($sql);
    ?>

    <div data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-offset="0" class="scrollspy-example container" tabindex="0">
        <div class="row mx-auto mt-5 mb-5">
            <h4 id="scrollspyHeading1">All Staff</h4>
            <div class="mt-4 mb-5">
                <table class="table table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">SSN</th>
                            <th scope="col">Name</th>
                            <th scope="col">Phone Number</th>
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
                                    <th scope="row"><?php echo $row["sinNum"] ?></td>
                                    <td><?php echo $row["name"] ?></td>
                                    <td><?php echo $row["phoneNum"] ?></td>

                                    <td><a href="update.php?sinNum=<?php echo $row["sinNum"] ?>&name=<?php echo $row["name"] ?>&phoneNum=<?php echo $row["phoneNum"] ?>" class="btn btn-primary">edit</a>
                                        <a href="delete.php?sinNum=<?php echo $row["sinNum"] ?>" class="btn btn-danger">delete</a>
                                        <a href="detail.php?sinNum=<?php echo $row["sinNum"] ?>" class="btn btn-success">details</a>
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

            <h4 id="scrollspyHeading2">Insert a new Staff</h4>
            <div class="container-fluid mb-5 mt-4">
                <div class="col-md-6 offset-md-3">
                    <form action="process-insert.php" method="POST">
                        <div class="mb-3">
                            <label for="id" class="form-label">SSN Number</label>
                            <input type="text" class="form-control" id="id" name="id" placeholder="Enter SSN Number" required>
                            <div class="form-text">SSN Number should be 9 digits and can't be changed once set up!</div>
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
                        
                        <!-- Staff Role Selection -->
                        <div class="mb-3">
                            <label class="form-label">Staff Role</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="staffRole" id="noRole" value="noRole" checked onclick="toggleRoleFields()">
                                <label class="form-check-label" for="noRole">
                                    Basic Staff (No Special Role)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="staffRole" id="accountant" value="accountant" onclick="toggleRoleFields()">
                                <label class="form-check-label" for="accountant">
                                    Accountant
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="staffRole" id="contractor" value="contractor" onclick="toggleRoleFields()">
                                <label class="form-check-label" for="contractor">
                                    Contractor
                                </label>
                            </div>
                        </div>
                        
                        <!-- Accountant Fields (default hidden) -->
                        <div id="accountantFields" style="display: none;">
                            <div class="mb-3">
                                <label for="cpaLicenseNum" class="form-label">CPA License Number</label>
                                <input type="text" class="form-control" id="cpaLicenseNum" name="cpaLicenseNum" placeholder="Enter CPA License Number (e.g., 87654321)">
                            </div>
                            <div class="mb-3">
                                <label for="accExpirationDate" class="form-label">Expiration Date</label>
                                <input type="text" class="form-control" id="accExpirationDate" name="accExpirationDate" placeholder="MM/DD/YYYY">
                            </div>
                        </div>
                        
                        <!-- Contractor Fields (default hidden) -->
                        <div id="contractorFields" style="display: none;">
                            <div class="mb-3">
                                <label for="contractorLicenseNum" class="form-label">Contractor License Number</label>
                                <input type="text" class="form-control" id="contractorLicenseNum" name="contractorLicenseNum" placeholder="Enter Contractor License Number (e.g., 102938475)">
                            </div>
                            <div class="mb-3">
                                <label for="conExpirationDate" class="form-label">Expiration Date</label>
                                <input type="text" class="form-control" id="conExpirationDate" name="conExpirationDate" placeholder="MM/DD/YYYY">
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary mt-3">Insert</button>
                        </div>
                    </form>
                </div>
            </div>


            <h4 id="scrollspyHeading3">Sort Staff by Role</h4>
            <div class="mb-5 mt-4 text-center">
                <a href="/strata/staff/accountant.php" class="btn btn-primary mt-3">Accountant</a>
                <a href="/strata/staff/contractor.php" class="btn btn-primary mt-3">Contractor</a>
            </div>


        </div>
    </div>



    <!-- javascripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js" integrity="sha384-oesi62hOLfzrys4LxRF63OJCXdXDipiYWBnvTl9Y9/TRlw5xlKIEHpNyvvDShgf/" crossorigin="anonymous"></script>
    <script>
    function toggleRoleFields() {
        if (document.getElementById('accountant').checked) {
            document.getElementById('accountantFields').style.display = 'block';
            document.getElementById('contractorFields').style.display = 'none';
        } else if (document.getElementById('contractor').checked) {
            document.getElementById('accountantFields').style.display = 'none';
            document.getElementById('contractorFields').style.display = 'block';
        } else {
            document.getElementById('accountantFields').style.display = 'none';
            document.getElementById('contractorFields').style.display = 'none';
        }
    }
    </script>

</body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . "/strata/display/footer.php");
?>

</html>