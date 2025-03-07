<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Update Property Info</title>
</head>

<body>
    <div class="container-fluid">
        <div class="mb-3 mt-5 col-md-6 offset-md-3">

            <?php
            include '../connect.php';
            $id = $_GET['propertyID'];
            $newName = $_POST['newname'];
            $newLocation = $_POST['newlocation'];
            $newCompany = $_POST['newcompany'];
            $conn = OpenCon();
            
            // New variables for owner and property type
            $newOwner = isset($_POST['newowner']) ? $_POST['newowner'] : null;
            $propertyType = isset($_POST['propertyType']) ? $_POST['propertyType'] : null;
            
            $conn->begin_transaction();
            $error = false;

            // 1. Update basic property information (original code)
            $sql = "update Property_AssignTo set propertyName = '$newName', location = '$newLocation', companyID = '$newCompany' where propertyID = $id";

            if ($conn->query($sql) !== TRUE) {
                $error = true;
            }
            
            // 2. Handle owner update if an owner is selected
            if (!$error && $newOwner != "") {
                // Check if property already has an owner
                $checkOwner = $conn->query("SELECT ownerID FROM HasOwnershipOf WHERE propertyID = $id");
                
                if ($checkOwner->num_rows > 0) {
                    // Update existing ownership
                    $updateOwner = "UPDATE HasOwnershipOf SET ownerID = '$newOwner' WHERE propertyID = $id";
                    if ($conn->query($updateOwner) !== TRUE) {
                        $error = true;
                    }
                } else {
                    // Create new ownership record
                    $currentDate = date("m/d/Y");
                    $registerOwner = 1; // Default value
                    $insertOwner = "INSERT INTO HasOwnershipOf (ownerID, propertyID, startDate, registerOwner) 
                                   VALUES ('$newOwner', '$id', '$currentDate', '$registerOwner')";
                    if ($conn->query($insertOwner) !== TRUE) {
                        $error = true;
                    }
                }
            }
            
            // 3. Handle property type update
            if (!$error && $propertyType) {
                // Check current property type
                $isCommercial = $conn->query("SELECT propertyID FROM Commercial WHERE propertyID = $id");
                $isResidential = $conn->query("SELECT propertyID FROM Residential WHERE propertyID = $id");
                
                if ($propertyType === 'commercial') {
                    $storeName = $_POST['storeName'];
                    $permissionNum = $_POST['permissionNum'];
                    
                    // If it was previously residential, delete that record
                    if ($isResidential->num_rows > 0) {
                        if ($conn->query("DELETE FROM Residential WHERE propertyID = $id") !== TRUE) {
                            $error = true;
                        }
                    }
                    
                    // Update or insert commercial data
                    if ($isCommercial->num_rows > 0) {
                        $updateCommercial = "UPDATE Commercial SET commercialStoreName = '$storeName', 
                                           commercialPermissionNum = '$permissionNum' WHERE propertyID = $id";
                        if ($conn->query($updateCommercial) !== TRUE) {
                            $error = true;
                        }
                    } else {
                        $insertCommercial = "INSERT INTO Commercial (propertyID, commercialStoreName, commercialPermissionNum) 
                                          VALUES ('$id', '$storeName', '$permissionNum')";
                        if ($conn->query($insertCommercial) !== TRUE) {
                            $error = true;
                        }
                    }
                } 
                else if ($propertyType === 'residential') {
                    $buildingSize = $_POST['buildingSize'];
                    $yardArea = $_POST['yardArea'];
                    
                    // If it was previously commercial, delete that record
                    if ($isCommercial->num_rows > 0) {
                        if ($conn->query("DELETE FROM Commercial WHERE propertyID = $id") !== TRUE) {
                            $error = true;
                        }
                    }
                    
                    // Update or insert residential data
                    if ($isResidential->num_rows > 0) {
                        $updateResidential = "UPDATE Residential SET restrictedBuildingSize = '$buildingSize', 
                                           yardArea = '$yardArea' WHERE propertyID = $id";
                        if ($conn->query($updateResidential) !== TRUE) {
                            $error = true;
                        }
                    } else {
                        $insertResidential = "INSERT INTO Residential (propertyID, restrictedBuildingSize, yardArea) 
                                          VALUES ('$id', '$buildingSize', '$yardArea')";
                        if ($conn->query($insertResidential) !== TRUE) {
                            $error = true;
                        }
                    }
                }
            }

            if (!$error) {
                $conn->commit();
            ?>
                <div class="alert alert-success text-center" role="alert">
                    <h4 class="alert-heading">Success!</h4>
                    <p>Property Info Updated successfully!
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