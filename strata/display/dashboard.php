<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: /strata/check-connection.php");
    exit();
}

// Get the user's role
$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Dashboard - Strata Management System</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Strata Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($role === 'owner'): ?>
                        <li class="nav-item">
                            <div class="text-center">
                                <a class="nav-link" href="logout.php">Home</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo htmlspecialchars($username); ?></span>
                        </li>
                        <li class="nav-item">
                            <div class="text-center">
                                <a class="nav-link" href="logout.php">Logout</a>
                            </div>
                            
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    
    <main class="container-fluid mt-5 mb-5">
        <div class="px-3 text-center">
            <h1><?php echo ucfirst($role); ?> Dashboard</h1>
            <p class="lead">Manage your Strata resources</p>
        </div>
    
        <div class="row mx-auto">
            <?php if ($role === 'owner'): ?>
                <!-- Owner Card -->
                <div class="col-md-4 col-sm-6 mb-3 mx-auto">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Owner Portal</h5>
                            <p class="card-text">Access your owner account, view your properties and council meetings</p>
                            <a href="/strata/owner/owner-viewer/owner-login.php" class="btn btn-primary">Access Portal</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'manager'): ?>
                <!-- Property Card -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Property</h5>
                            <p class="card-text">Create, modify, or delete a Property</p>
                            <a href="/strata/property/property.php" class="btn btn-primary">Go</a>
                        </div>
                    </div>           
                </div>

                 <!-- Owner Card -->
                <div class="col-md-4 col-sm-6 mb-3 mx-auto">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Owner</h5>
                            <p class="card-text">Create, modify, or delete an Owner account and its relevant Council Meetings</p>
                            <a href="/strata/owner/owner.php" class="btn btn-primary">Go</a>
                        </div>
                    </div>
                </div>
                
                
                
                

                <!-- Staff Card -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Staff</h5>
                            <p class="card-text">Create, modify, or delete a Staff and its relevant events</p>
                            <a href="/strata/staff/staff.php" class="btn btn-primary">Go</a>
                        </div>
                    </div>           
                </div>

                <!-- Strata Manager Card -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Strata Manager</h5>
                            <p class="card-text">Create, modify, or delete a Strata Manager</p>
                            <a href="/strata/manager/manager.php" class="btn btn-primary">Go</a>
                        </div>
                    </div>           
                </div>
            <?php endif; ?>

            <?php if ($role === 'staff'): ?>
                <!-- Staff Card -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Staff Portal</h5>
                            <p class="card-text">View and edit your staff portal and arrange property events or statements</p>
                            <a href="/strata/staff/staff-viewer/staff-viewer.php" class="btn btn-primary">Go</a>
                        </div>
                    </div>           
                </div>
            <?php endif; ?>

            <?php if ($role === 'companyowner'): ?>
                <!-- Company Owner Card -->
                <div class="col-md-4 col-sm-6 mb-3 mx-auto">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Company Owner</h5>
                            <p class="card-text">Create, modify, or delete a Company Owner</p>
                            <a href="/strata/comowner/comowner.php" class="btn btn-primary">Go</a>
                        </div>
                    </div>           
                </div>

                <!-- Strata Management Company Card -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Strata Management Company</h5>
                            <p class="card-text">Create, modify, or delete a Strata Management Company</p>
                            <a href="/strata/company/company.php" class="btn btn-primary">Go</a>
                        </div>
                    </div>           
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <?php if ($role === 'owner'): ?>
                <a href="/strata/logout.php" class="btn btn-outline-secondary">Go Back</a>
            <?php else: ?>
                <a href="/strata/logout.php" class="btn btn-outline-secondary">Logout</a>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>


