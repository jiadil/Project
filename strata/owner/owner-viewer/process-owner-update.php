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
    <title>Update Owner Information</title>
</head>
<body>
    <div class="container-fluid">
        <div class="mb-3 mt-5 col-md-6 offset-md-3">

            <?php
            // Check if owner is logged in
            if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
                header("Location: owner-login.php");
                exit();
            }

            // Get owner ID from session and verify it matches form data
            $sessionOwnerID = $_SESSION['owner_id'];
            $formOwnerID = $_POST['ownerID'];

            if ($sessionOwnerID != $formOwnerID) {
                // Security check failed
                echo '<div class="alert alert-danger text-center" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <p>You can only update your own information.</p>
                        <hr>
                        <a href="owner-viewer.php" class="btn btn-primary">Go Back to Owner Portal</a>
                      </div>';
                exit();
            }

            include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
            $ownerID = $_POST['ownerID'];
            $newName = $_POST['newname'];
            $newPhone = $_POST['newphone'];
            $newEmail = $_POST['newemail'];
            $conn = OpenCon();

            // Validate phone number
            if (!preg_match('/^\d{10}$/', $newPhone)) {
                echo '<div class="alert alert-danger text-center" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <p>Phone number must be 10 digits.</p>
                        <hr>
                        <a href="owner-viewer.php" class="btn btn-primary">Go Back to Owner Portal</a>
                      </div>';
                exit();
            }

            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("UPDATE Owner SET name = ?, phoneNum = ?, emailAddress = ? WHERE ownerID = ?");
            $stmt->bind_param("sssi", $newName, $newPhone, $newEmail, $ownerID);
            
            if ($stmt->execute()) {
                // Update session info
                $_SESSION['owner_name'] = $newName;
            ?>
                <div class="alert alert-success text-center" role="alert">
                    <h4 class="alert-heading">Success!</h4>
                    <p>Your information has been updated successfully!</p>
                    <hr>
                    <a href="owner-viewer.php" class="btn btn-primary">Back to Owner Portal</a>
                </div>
            <?php
            } else {
            ?>
                <div class="alert alert-danger text-center" role="alert">
                    <h4 class="alert-heading">Error!</h4>
                    <p><?php echo "Error updating record: " . $conn->error ?></p>
                    <hr>
                    <a href="owner-viewer.php" class="btn btn-primary">Go Back to Owner Portal</a>
                </div>
            <?php
            }
            
            $stmt->close();
            CloseCon($conn);
            ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>