<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if owner is logged in
if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
    header("Location: owner-login.php");
    exit();
}

// Get owner ID from session and verify it matches URL parameter
$sessionOwnerID = $_SESSION['owner_id'];
$urlOwnerID = $_GET['ownerID'];

if ($sessionOwnerID != $urlOwnerID) {
    // Security check failed
    echo '<div class="container mt-5"><div class="alert alert-danger">You can only update your own information.</div></div>';
    exit();
}

$name = $_GET['name'];
$phoneNum = $_GET['phoneNum'];
$emailAddress = $_GET['emailAddress'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Update Owner Information</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
    <a class="navbar-brand" href="owner-viewer.php">Back to Owner Portal</a>
</nav>

<body class="d-flex flex-column">

    

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Update My Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="process-owner-update.php" method="POST">
                            <input type="hidden" name="ownerID" value="<?php echo $urlOwnerID; ?>">
                            
                            <div class="mb-3">
                                <label for="newname" class="form-label">Name</label>
                                <input type="text" class="form-control" id="newname" name="newname" value="<?php echo $name; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="newphone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="newphone" name="newphone" value="<?php echo $phoneNum; ?>" required>
                                <div class="form-text">Phone number should be 10 digits</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="newemail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="newemail" name="newemail" value="<?php echo $emailAddress; ?>" required>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="owner-viewer.php" class="btn btn-outline-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Information</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- javascripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . "/strata/display/footer.php");
?>

</html>
