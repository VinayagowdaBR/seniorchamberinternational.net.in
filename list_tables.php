<?php
$conn = new mysqli('localhost', 'root', '', 'ztakzvxv_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        echo $row[0] . "\n";
    }
} else {
    echo "Error showing tables: " . $conn->error . "\n";
}
$conn->close();
?>
