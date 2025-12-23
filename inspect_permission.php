<?php
$conn = new mysqli('localhost', 'root', '', 'ztakzvxv_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Show structure
$result = $conn->query("SHOW CREATE TABLE permission");
if ($result) {
    $row = $result->fetch_assoc();
    echo $row['Create Table'] . ";\n\n";
}

// Show first few rows to understand data format
$result = $conn->query("SELECT * FROM permission LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
}
$conn->close();
?>
