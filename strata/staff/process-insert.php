<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Insert Staff</title>
</head>
<body>
    <div class="container-fluid">
    <div class="mb-3 mt-5 col-md-6 offset-md-3">
    
    <?php
    include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
    $id = $_POST['id']; 
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $staffRole = isset($_POST['staffRole']) ? $_POST['staffRole'] : 'noRole';
    
    $conn = OpenCon();
    $success = true;
    $errorMessage = "";
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert basic staff record first
        $staffSql = "INSERT INTO `Staff` (`sinNum`, `name`, `phoneNum`) VALUES ('$id', '$name', '$phone')";
        
        if (!$conn->query($staffSql)) {
            throw new Exception("Error inserting staff: " . $conn->error);
        }
        
        // If staff role is accountant, insert into Accountant table
        if ($staffRole == 'accountant') {
            $cpaLicenseNum = $_POST['cpaLicenseNum'];
            $expirationDate = $_POST['accExpirationDate'];
            
            $accountantSql = "INSERT INTO `Accountant` (`sinNum`, `CPALicenseNum`, `expirationDate`) VALUES ('$id', '$cpaLicenseNum', '$expirationDate')";
            
            if (!$conn->query($accountantSql)) {
                throw new Exception("Error inserting accountant: " . $conn->error);
            }
        }
        
        // If staff role is contractor, insert into Contractor table
        if ($staffRole == 'contractor') {
            $contractorLicenseNum = $_POST['contractorLicenseNum'];
            $expirationDate = $_POST['conExpirationDate'];
            
            $contractorSql = "INSERT INTO `Contractor` (`sinNum`, `contractorLicenseNum`, `expirationDate`) VALUES ('$id', '$contractorLicenseNum', '$expirationDate')";
            
            if (!$conn->query($contractorSql)) {
                throw new Exception("Error inserting contractor: " . $conn->error);
            }
        }
        
        // If everything went well, commit the transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        $conn->rollback();
        $success = false;
        $errorMessage = $e->getMessage();
    }
    
    if ($success) {
    ?>
        <div class="alert alert-success text-center" role="alert">
            <h4 class="alert-heading">Success!</h4>
            <p>Successfully inserted a new staff member<?php echo ($staffRole != 'noRole') ? " as a $staffRole" : ""; ?>!</p>
            <hr>
            <a href="/strata/staff/staff.php" class="btn btn-primary">Back to All Staff</a>
        </div>
    <?php
    } else {
    ?>
        <div class="alert alert-danger text-center" role="alert">
            <h4 class="alert-heading">Error!</h4>
            <p><?php echo $errorMessage; ?></p>
            <hr>
            <a href="/strata/staff/staff.php" class="btn btn-primary">Go Back to All Staff</a>
        </div>
    <?php
    }
    
    CloseCon($conn);
    ?>
    </div>
    </div>
    
</body>
</html>