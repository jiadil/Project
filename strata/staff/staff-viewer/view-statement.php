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
    <title>View Financial Statement</title>
</head>
<body>
    <?php
    
    // Check if staff is logged in and is an accountant
    if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true || $_SESSION['staff_role'] !== 'accountant') {
        header("Location: staff-login.php");
        exit();
    }
    
    // Get the parameters from the URL
    $propertyID = isset($_GET['propertyID']) ? $_GET['propertyID'] : 0;
    $cdate = isset($_GET['cdate']) ? $_GET['cdate'] : '';
    $statementID = isset($_GET['statementID']) ? $_GET['statementID'] : 0;
    $sinNum = $_SESSION['staff_id'];
    
    include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
    $conn = OpenCon();
    
    // Get property information
    $propertyInfo = $conn->prepare("SELECT * FROM Property_AssignTo WHERE propertyID = ?");
    $propertyInfo->bind_param("i", $propertyID);
    $propertyInfo->execute();
    $propertyResult = $propertyInfo->get_result();
    $propertyData = $propertyResult->fetch_assoc();
    
    // Check if property exists
    if (!$propertyData) {
        echo '<div class="container mt-5">
                <div class="alert alert-danger" role="alert">
                    Property not found.
                </div>
                <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
              </div>';
        exit();
    }
    
    // Get the financial statement by either statementID or by propertyID and cdate
    if ($statementID > 0) {
        $statementInfo = $conn->prepare("SELECT f.*, p.pstatus, p.summary, p.sinNum as prepared_by 
                                        FROM FinancialStatements_Has f
                                        JOIN prepared p ON f.statementID = p.statementID
                                        WHERE f.statementID = ?");
        $statementInfo->bind_param("i", $statementID);
    } else {
        $statementInfo = $conn->prepare("SELECT f.*, p.pstatus, p.summary, p.sinNum as prepared_by 
                                        FROM FinancialStatements_Has f
                                        JOIN prepared p ON f.statementID = p.statementID
                                        WHERE f.propertyID = ? AND f.cdate = ?");
        $statementInfo->bind_param("is", $propertyID, $cdate);
    }
    
    $statementInfo->execute();
    $statementResult = $statementInfo->get_result();
    $statementData = $statementResult->fetch_assoc();
    
    // Check if statement exists
    if (!$statementData) {
        echo '<div class="container mt-5">
                <div class="alert alert-danger" role="alert">
                    Financial statement not found.
                </div>
                <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-primary">Back to Property Details</a>
              </div>';
        exit();
    }
    
    // Get accountant information
    $accountantInfo = $conn->prepare("SELECT name FROM Staff WHERE sinNum = ?");
    $accountantInfo->bind_param("i", $statementData['prepared_by']);
    $accountantInfo->execute();
    $accountantResult = $accountantInfo->get_result();
    $accountantData = $accountantResult->fetch_assoc();
    ?>
    
    <div class="container mt-5">
        <h2>Financial Statement Details</h2>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><?php echo $propertyData['propertyName']; ?> - <?php echo $statementData['cdate']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Property ID:</strong> <?php echo $propertyID; ?></p>
                        <p><strong>Property Name:</strong> <?php echo $propertyData['propertyName']; ?></p>
                        <p><strong>Location:</strong> <?php echo $propertyData['location']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Statement ID:</strong> <?php echo $statementData['statementID']; ?></p>
                        <p><strong>Statement Date:</strong> <?php echo $statementData['cdate']; ?></p>
                        <p><strong>Status:</strong> <span class="badge <?php echo $statementData['pstatus'] == 'completed' ? 'bg-success' : 'bg-warning'; ?>"><?php echo $statementData['pstatus']; ?></span></p>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h5>Financial Data</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Cash:</strong> $<?php echo $statementData['cash']; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Debt:</strong> $<?php echo $statementData['debt']; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Balance:</strong> $<?php echo $statementData['cash'] - $statementData['debt']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-success mt-3">
                    <h5>Accountant Summary</h5>
                    <p><strong>Summary Amount:</strong> $<?php echo $statementData['summary']; ?></p>
                    <p><strong>Status:</strong> <?php echo $statementData['pstatus']; ?></p>
                    <p><strong>Prepared by:</strong> <?php echo $accountantData['name']; ?> (SIN: <?php echo $statementData['prepared_by']; ?>)</p>
                </div>
                
                <div class="text-center mt-4">
                    <?php if ($statementData['prepared_by'] == $sinNum && $statementData['pstatus'] != 'completed'): ?>
                    <a href="update-statement.php?propertyID=<?php echo $propertyID; ?>&statementID=<?php echo $statementData['statementID']; ?>" class="btn btn-warning me-2">Update Statement</a>
                    <?php endif; ?>
                    <a href="staff-viewer.php" class="btn btn-primary">Back to Portal</a>
                    <a href="property-detail.php?propertyID=<?php echo $propertyID; ?>" class="btn btn-outline-secondary ms-2">Back to Property</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
<?php CloseCon($conn); ?>