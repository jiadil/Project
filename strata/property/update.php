<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Update Property Info</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
    <a class="navbar-brand" href="/strata/property/property.php">Back to all properties</a>
</nav>

<body class="d-flex flex-column vh-100">

    <?php
    include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
    $conn = OpenCon();
    $id = $_GET['propertyID'];
    $name = $_GET['propertyName'];
    $location = $_GET['location'];
    $company = $_GET['companyID'];

    $sql = "SELECT companyID FROM StrataManagementCompany";
    $result = $conn->query($sql);
    
    // Get current owner information
    $ownerQuery = "SELECT o.ownerID, o.name FROM Owner o 
                 JOIN HasOwnershipOf h ON o.ownerID = h.ownerID 
                 WHERE h.propertyID = $id";
    $ownerResult = $conn->query($ownerQuery);
    $currentOwner = $ownerResult->fetch_assoc();
    
    // Get all owners for dropdown
    $allOwners = $conn->query("SELECT ownerID, name FROM Owner");
    
    // Check property type
    $isCommercial = $conn->query("SELECT * FROM Commercial WHERE propertyID = $id");
    $isResidential = $conn->query("SELECT * FROM Residential WHERE propertyID = $id");
    
    $propertyType = "commercial"; // Default
    $commercialData = null;
    $residentialData = null;
    
    if ($isCommercial->num_rows > 0) {
        $propertyType = "commercial";
        $commercialData = $isCommercial->fetch_assoc();
    } elseif ($isResidential->num_rows > 0) {
        $propertyType = "residential";
        $residentialData = $isResidential->fetch_assoc();
    }
    ?>

    <div data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-offset="0" class="scrollspy-example container" tabindex="0">
        <div class="row mx-auto mt-5 mb-5">
            <form action="process-update.php?propertyID=<?php echo $id ?>" method="POST">
                <div class="container-fluid">
                    <div class="mb-3 col-md-6 offset-md-3">
                        <legend class=" text-center mt-5">Edit Property #<?php echo $id ?></legend>
                        <div class="mb-3">
                            <label for="newname" class="form-label">New Property Name</label>
                            <input type="text" class="form-control" id="newname" name="newname" value="<?php echo $name ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="newlocation" class="form-label">New Location</label>
                            <input type="text" class="form-control" id="newlocation" name="newlocation" value="<?php echo $location ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="newcompany" class="form-label">New Company ID</label>
                            <select class="form-select" name="newcompany" id="newcompany">
                                <option selected><?php echo $company ?></option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    unset($companyid);
                                    $companyid = $row['companyID'];
                                ?>
                                    <option value="<?php echo $companyid ?>"><?php echo $companyid ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Owner Assignment Section -->
                        <div class="mb-3">
                            <label for="newowner" class="form-label">Owner</label>
                            <select class="form-select" name="newowner" id="newowner">
                                <option value="">-- Select Owner --</option>
                                <?php
                                while ($owner = $allOwners->fetch_assoc()) {
                                    $selected = ($currentOwner && $owner['ownerID'] == $currentOwner['ownerID']) ? "selected" : "";
                                ?>
                                    <option value="<?php echo $owner['ownerID']; ?>" <?php echo $selected; ?>>
                                        <?php echo $owner['ownerID'] . " - " . $owner['name']; ?>
                                    </option>
                                <?php
                                } ?>
                            </select>
                        </div>
                        
                        <!-- Property Type Section -->
                        <div class="mb-3">
                            <label class="form-label">Property Type</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="propertyType" id="commercial" value="commercial" <?php echo ($propertyType == "commercial") ? "checked" : ""; ?> onclick="togglePropertyFields()">
                                <label class="form-check-label" for="commercial">
                                    Commercial
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="propertyType" id="residential" value="residential" <?php echo ($propertyType == "residential") ? "checked" : ""; ?> onclick="togglePropertyFields()">
                                <label class="form-check-label" for="residential">
                                    Residential
                                </label>
                            </div>
                        </div>
                        
                        <!-- Commercial Fields -->
                        <div id="commercialFields" style="display: <?php echo ($propertyType == "commercial") ? "block" : "none"; ?>">
                            <div class="mb-3">
                                <label for="storeName" class="form-label">Commercial Store Name</label>
                                <input type="text" class="form-control" id="storeName" name="storeName" 
                                       value="<?php echo ($commercialData) ? $commercialData['commercialStoreName'] : ''; ?>" 
                                       placeholder="Enter store name">
                            </div>
                            <div class="mb-3">
                                <label for="permissionNum" class="form-label">Commercial Permission Number</label>
                                <input type="text" class="form-control" id="permissionNum" name="permissionNum" 
                                       value="<?php echo ($commercialData) ? $commercialData['commercialPermissionNum'] : ''; ?>" 
                                       placeholder="Enter permission number">
                            </div>
                        </div>
                        
                        <!-- Residential Fields -->
                        <div id="residentialFields" style="display: <?php echo ($propertyType == "residential") ? "block" : "none"; ?>">
                            <div class="mb-3">
                                <label for="buildingSize" class="form-label">Restricted Building Size</label>
                                <input type="number" class="form-control" id="buildingSize" name="buildingSize" 
                                       value="<?php echo ($residentialData) ? $residentialData['restrictedBuildingSize'] : ''; ?>" 
                                       placeholder="Enter building size">
                            </div>
                            <div class="mb-3">
                                <label for="yardArea" class="form-label">Yard Area</label>
                                <input type="number" class="form-control" id="yardArea" name="yardArea" 
                                       value="<?php echo ($residentialData) ? $residentialData['yardArea'] : ''; ?>" 
                                       placeholder="Enter yard area">
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary mt-3">Update</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

<script>
function togglePropertyFields() {
    if (document.getElementById('commercial').checked) {
        document.getElementById('commercialFields').style.display = 'block';
        document.getElementById('residentialFields').style.display = 'none';
    } else {
        document.getElementById('commercialFields').style.display = 'none';
        document.getElementById('residentialFields').style.display = 'block';
    }
}
</script>

</body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . "/strata/display/footer.php");
?>

</html>