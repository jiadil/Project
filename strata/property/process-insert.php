<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Insert an Property</title>
</head>

<body>
    <div class="container-fluid">
        <div class="mb-3 mt-5 col-md-6 offset-md-3">

            <?php
            include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
            $id = $_POST['id'];
            $name = $_POST['name'];
            $location = $_POST['location'];
            $company = $_POST['company'];
            $conn = OpenCon();
            
            // New variables for owner and property type
            $ownerId = isset($_POST['owner']) ? $_POST['owner'] : null;
            $propertyType = isset($_POST['propertyType']) ? $_POST['propertyType'] : null;
            
            // Start transaction to ensure data consistency
            $conn->begin_transaction();
            $success = true;

            // 1. Insert into Property_AssignTo table (original code)
            $sql = "INSERT INTO `Property_AssignTo` 
            (`propertyID`, `propertyName`, `location`, `companyID`) 
            VALUES ('$id', '$name', '$location', '$company')";

            if ($conn->query($sql) !== TRUE) {
                $success = false;
            }
            
            // 2. If owner is selected, insert into HasOwnershipOf table
            if ($success && $ownerId) {
                $currentDate = date("m/d/Y");
                $registerOwner = 1; // Default value
                
                $sql2 = "INSERT INTO `HasOwnershipOf` 
                (`ownerID`, `propertyID`, `startDate`, `registerOwner`) 
                VALUES ('$ownerId', '$id', '$currentDate', '$registerOwner')";
                
                if ($conn->query($sql2) !== TRUE) {
                    $success = false;
                }
            }
            
            // 3. Insert into either Commercial or Residential table based on property type
            if ($success && $propertyType) {
                if ($propertyType === 'commercial') {
                    $storeName = $_POST['storeName'];
                    $permissionNum = $_POST['permissionNum'];
                    
                    $sql3 = "INSERT INTO `Commercial` 
                    (`propertyID`, `commercialStoreName`, `commercialPermissionNum`) 
                    VALUES ('$id', '$storeName', '$permissionNum')";
                    
                    if ($conn->query($sql3) !== TRUE) {
                        $success = false;
                    }
                } 
                else if ($propertyType === 'residential') {
                    $buildingSize = $_POST['buildingSize'];
                    $yardArea = $_POST['yardArea'];
                    
                    $sql3 = "INSERT INTO `Residential` 
                    (`propertyID`, `restrictedBuildingSize`, `yardArea`) 
                    VALUES ('$id', '$buildingSize', '$yardArea')";
                    
                    if ($conn->query($sql3) !== TRUE) {
                        $success = false;
                    }
                }
            }
            
            // Commit or rollback based on success
            if ($success) {
                $conn->commit();
            ?>
                <div class="alert alert-success text-center" role="alert">
                    <h4 class="alert-heading">Success!</h4>
                    <p>Successfully insert a property!
                        <hr>
                        <a href="/strata/property/property.php" class="btn btn-primary">Back to All Properties</a>
                </div>
            <?php
            } 
            else {
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