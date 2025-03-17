<?php
    $k = $_POST['id'];
    $k = trim($k);
    include($_SERVER['DOCUMENT_ROOT'] . "/strata/connect.php");
    $conn = OpenCon();
    
    if($k == "All"){
        $sql = "SELECT * FROM Owner";
        $result = $conn->query($sql);
    }
    else{
        $sql = "SELECT * FROM Owner where name='{$k}'";
        $result = $conn->query($sql);
    }

    if (!$result) {
        echo "<tr><td colspan='5'>Query error: " . $conn->error . "</td></tr>";
        exit;
    }

    if ($result->num_rows == 0) {
        echo "<tr><td colspan='5'>No results found</td></tr>";
        exit;
    }

    while($row = $result->fetch_assoc()) { 
?>
<tr>
    <th scope="row"><?php echo $row['ownerID']; ?></th>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['phoneNum']; ?></td>
    <td><?php echo $row['emailAddress']; ?></td>
</tr>
<?php
    }
?>