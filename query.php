<?php
include 'connect.php';

// Open database connection
$conn = OpenCon();

// Query to fetch employee details
$sql = "SELECT ssn, fname, lname, salary, dno FROM employee";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th class='borderclass'>SSN</th>
                <th class='borderclass'>First Name</th>
                <th class='borderclass'>Last Name</th>
                <th class='borderclass'>Salary</th>
                <th class='borderclass'>Department Number</th>
            </tr>";
    
    // Loop through results and display each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td class='borderclass'>" . $row["ssn"] . "</td>
                <td class='borderclass'>" . $row["fname"] . "</td>
                <td class='borderclass'>" . $row["lname"] . "</td>
                <td class='borderclass'>" . $row["salary"] . "</td>
                <td class='borderclass'>" . $row["dno"] . "</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No results found.";
}

// Close database connection
CloseCon($conn);
?>
