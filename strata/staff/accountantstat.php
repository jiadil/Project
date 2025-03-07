<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Accountant Details and Statistics</title>
</head>

<nav id="navbar-example2" class="navbar navbar-light bg-light px-3 sticky-top">
    <a class="navbar-brand" href="/strata/check-connection.php">Home</a>
    <a class="navbar-brand" href="/strata/staff/staff.php">Back to all staff</a>
    <a class="navbar-brand" href="/strata/staff/accountant.php">Back to all accountants</a>
</nav>

<body>
    <?php
    include '../connect.php';
    $id = $_GET['sinNum'];
    $conn = OpenCon();
    
    // Updated query to properly join using statementID from prepared table
    $sort = $conn->query("SELECT p.sinNum, f.propertyID, f.cdate, p.summary, f.cash, f.debt
            FROM prepared p
            JOIN FinancialStatements_Has f ON p.statementID = f.statementID
            WHERE p.sinNum = $id");
    
    // Updated query for the summary stats
    $stat = $conn->query("SELECT p.sinNum, f.propertyID, SUM(p.summary) as total_summary, 
            SUM(f.cash) as total_cash, SUM(f.debt) as total_debt
            FROM prepared p
            JOIN FinancialStatements_Has f ON p.statementID = f.statementID
            WHERE p.sinNum = $id
            GROUP BY f.propertyID");
    ?>


    <div data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-offset="0" class="scrollspy-example container" tabindex="0">
        <div class="row mx-auto mt-5 mb-5">

            <div class="mt-4 mb-5 text-center">
                <legend>Processed Statements</legend>
                <table class="table table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">PropertyID</th>
                            <th scope="col">Process Date</th>
                            <th scope="col">Cash</th>
                            <th scope="col">Debt</th>
                            <th scope="col">Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($sort->num_rows > 0) {
                            // output data of each row
                            while ($row = $sort->fetch_assoc()) {
                        ?>
                                <tr>
                                    <th scope="row"><?php echo $row["propertyID"] ?></td>
                                    <td><?php echo $row["cdate"] ?></td>
                                    <td><?php echo $row["cash"] ?></td>
                                    <td><?php echo $row["debt"] ?></td>
                                    <td><?php echo $row["summary"] ?></td>
                                </tr>

                        <?php
                            }
                        } else
                            echo "0 results";
                        //CloseCon($conn);
                        ?>
                    </tbody>
                </table>
            </div>


            <div class="mt-4 mb-5 text-center">
                <legend>Summary for Each Property</legend>
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Property</th>
                            <th scope="col">Total Cash</th>
                            <th scope="col">Total Debt</th>
                            <th scope="col">Total Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($stat->num_rows > 0) {
                            // output data of each row
                            while ($row = $stat->fetch_assoc()) {
                        ?>
                                <tr>
                                    <th scope="row"><?php echo $row["propertyID"] ?></td>
                                    <td><?php echo $row["total_cash"] ?></td>
                                    <td><?php echo $row["total_debt"] ?></td>
                                    <td><?php echo $row["total_summary"] ?></td>
                                </tr>

                        <?php
                            }
                        } else {
                            echo "0 results";
                        }
                        CloseCon($conn);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

<?php
include("../display/footer.php");
?>

</html>