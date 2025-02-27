<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Try to include the connection file with error handling
try {
    // Check if the file exists
    if (!file_exists('connect.php')) {
        echo "Error: connect.php file not found<br>";
        echo "Current directory: " . __DIR__ . "<br>";
        echo "Files in current directory:<br>";
        echo "<pre>";
        print_r(scandir(__DIR__));
        echo "</pre>";
        exit;
    }
    
    // Try to include the file
    include 'connect.php';
    
    // Try to establish connection
    try {
        $conn = OpenCon();
        echo "Connected Successfully";
		echo "index.php";


		// // Query to fetch employee details
		// $sql = "SELECT ssn, fname, lname, salary, dno FROM employee";
		// $result = $conn->query($sql);

		// if ($result->num_rows > 0) {
		// 	echo "<table border='1'>
		// 			<tr>
		// 				<th class='borderclass'>SSN</th>
		// 				<th class='borderclass'>First Name</th>
		// 				<th class='borderclass'>Last Name</th>
		// 				<th class='borderclass'>Salary</th>
		// 				<th class='borderclass'>Department Number</th>
		// 			</tr>";
			
		// 	// Loop through results and display each row
		// 	while($row = $result->fetch_assoc()) {
		// 		echo "<tr>
		// 				<td class='borderclass'>" . $row["ssn"] . "</td>
		// 				<td class='borderclass'>" . $row["fname"] . "</td>
		// 				<td class='borderclass'>" . $row["lname"] . "</td>
		// 				<td class='borderclass'>" . $row["salary"] . "</td>
		// 				<td class='borderclass'>" . $row["dno"] . "</td>
		// 			</tr>";
		// 	}

		// 	echo "</table>";
		// } else {
		// 	echo "No results found.";
		// }



        CloseCon($conn);
    } catch (Exception $e) {
        echo "Database connection error: " . $e->getMessage();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>