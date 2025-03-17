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
    <title>Update Staff Information</title>
</head>
<body>
    <?php
    
    // Check if staff is logged in
    if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
        header("Location: staff-login.php");
        exit();
    }
    
    // Check if the SIN in URL matches the logged-in staff's SIN
    if ($_GET['sinNum'] != $_SESSION['staff_id']) {
        echo '<div class="container mt-5">
                <div class="alert alert-danger" role="alert">
                    You can only update your own information.
                </div>
                <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
              </div>';
        exit();
    }
    
    $sinNum = $_GET['sinNum'];
    $name = $_GET['name'];
    $phoneNum = $_GET['phoneNum'];
    ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Update Your Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="process-staff-update.php" method="POST">
                            <input type="hidden" name="sinNum" value="<?php echo $sinNum; ?>">
                            
                            <div class="mb-3">
                                <label for="newname" class="form-label">Name</label>
                                <input type="text" class="form-control" id="newname" name="newname" value="<?php echo $name; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="newphone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="newphone" name="newphone" value="<?php echo $phoneNum; ?>" pattern="\d{10}" title="Phone number must be 10 digits" required>
                                <div class="form-text">Phone number must be 10 digits</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Information</button>
                                <a href="staff-viewer.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>