<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Update Financial Statement</title>
</head>
<body>
    <?php
    session_start();
    
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
    
    include("../../connect.php");
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
    
    // Get the financial statement either by statementID or by propertyID and cdate
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
    
    // Check if this accountant prepared the statement
    if ($statementData['prepared_by'] != $sinNum) {
        echo '<div class="container mt-5">
                <div class="alert alert-danger" role="alert">
                    You can only update financial statements that you have prepared yourself.
                </div>
                <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-primary">Back to Property Details</a>
              </div>';
        exit();
    }
    
    // Check if statement is already completed
    if ($statementData['pstatus'] == 'completed') {
        echo '<div class="container mt-5">
                <div class="alert alert-warning" role="alert">
                    This financial statement is already marked as completed and cannot be edited.
                </div>
                <a href="property-detail.php?propertyID='.$propertyID.'" class="btn btn-primary">Back to Property Details</a>
              </div>';
        exit();
    }
    ?>
    
    <div class="container mt-5">
        <h2>Update Financial Statement</h2>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><?php echo $propertyData['propertyName']; ?> - <?php echo $statementData['cdate']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Property ID:</strong> <?php echo $propertyID; ?></p>
                        <p><strong>Property Name:</strong> <?php echo $propertyData['propertyName']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Statement Date:</strong> <?php echo $statementData['cdate']; ?></p>
                        <p><strong>Current Status:</strong> <span class="badge bg-warning"><?php echo $statementData['pstatus']; ?></span></p>
                    </div>
                </div>
                
                <form action="process-statement.php" method="POST">
                    <input type="hidden" name="propertyID" value="<?php echo $propertyID; ?>">
                    <input type="hidden" name="sinNum" value="<?php echo $sinNum; ?>">
                    <input type="hidden" name="cdate" value="<?php echo $statementData['cdate']; ?>">
                    <input type="hidden" name="statementID" value="<?php echo $statementData['statementID']; ?>">
                    
                    <div class="mb-3">
                        <label for="cash" class="form-label">Cash Amount ($)</label>
                        <input type="number" class="form-control" id="cash" name="cash" value="<?php echo $statementData['cash']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="debt" class="form-label">Debt Amount ($)</label>
                        <input type="number" class="form-control" id="debt" name="debt" value="<?php echo $statementData['debt']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="summary" class="form-label">Summary Amount ($)</label>
                        <input type="number" class="form-control" id="summary" name="summary" value="<?php echo $statementData['summary']; ?>" required>
                        <div class="form-text">This is your professional assessment of the property's financial health</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pstatus" class="form-label">Status</label>
                        <select class="form-select" id="pstatus" name="pstatus" required>
                            <option value="in progress" <?php echo ($statementData['pstatus'] == 'in progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Update Financial Statement</button>
                        <a href="property-detail.php?propertyID=<?php echo $propertyID; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
<?php CloseCon($conn); ?>