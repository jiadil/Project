<?php
session_start();

// Display error message if any
if (isset($_SESSION['owner_login_error'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['owner_login_error'] . '</div>';
    unset($_SESSION['owner_login_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Strata Management System</title>
</head>

<body>
<main class="container-fluid mt-5 mb-5">
    <div class="px-3 text-center">
        <h1>Strata Management System</h1>
        <p class="lead">Owner Portal</p>
    </div>
      
    <div class="row mx-auto justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title">Owner Login</h5>
                    
                    
                    
                    <form action="process-owner-login.php" method="POST">
                        <div class="mb-3 text-start">
                            <label for="ownerID" class="form-label">Owner ID</label>
                            <input type="text" class="form-control" id="ownerID" name="ownerID" pattern="\d+" title="Owner ID must contain only numbers" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="passkey" class="form-label">Passkey</label>
                            <input type="password" class="form-control" id="passkey" name="passkey" value="000" required>
                            <div class="form-text">For demo purposes, use "000" as the passkey</div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <a href="/strata/check-connection.php" class="btn btn-outline-secondary">Back to Home</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>