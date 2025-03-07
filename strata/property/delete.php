<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Delete an Property</title>
</head>

<body>
    <div class="container-fluid">
        <div class="mb-3 mt-5 col-md-6 offset-md-3">

            <?php
            include '../connect.php';
            $id = $_GET['propertyID'];
            $conn = OpenCon();
            
            // Start transaction
            $conn->begin_transaction();
            $error = false;
            
            // Check for owner records and delete them first
            $sqlOwner = "DELETE FROM HasOwnershipOf WHERE propertyID='$id'";
            if ($conn->query($sqlOwner) !== TRUE) {
                // It's okay if there are no ownership records to delete
                if ($conn->errno !== 1451) { // Foreign key constraint error
                    $error = true;
                }
            }
            
            // Delete the property
            $sql = "DELETE FROM Property_AssignTo WHERE propertyID='$id'";
            if ($conn->query($sql) !== TRUE) {
                $error = true;
            }
            
            if (!$error) {
                $conn->commit();
            ?>
                <div class="alert alert-success text-center" role="alert">
                    <h4 class="alert-heading">Success!</h4>
                    <p>Successfully delete the property!
                        <hr>
                        <a href="/strata/property/property.php" class="btn btn-primary">Back to All Properties</a>
                </div>
            <?php
            } else {
                $conn->rollback();
            ?>
                <div class="alert alert-danger text-center" role="alert">
                    <h4 class="alert-heading">Error!</h4>
                    <p><?php echo "Error updating record: " . $conn->error ?>
                        <hr>
                        <a href="/strata/property/property.php" class="btn btn-primary">Go Back to All Properties</a>
                </div>
            <?php
            }
            
            CloseCon($conn);
            ?>
        </div>
    </div>

</body>

</html>