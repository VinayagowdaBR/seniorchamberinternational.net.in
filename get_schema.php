<?php
$conn = new mysqli('localhost', 'root', '', 'ztakzvxv_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = ['offline_payment_invoices', 'offline_payment_members'];
$output = "";

foreach ($tables as $table) {
    $result = $conn->query("SHOW CREATE TABLE $table");
    if ($result) {
        $row = $result->fetch_assoc();
        $output .= $row['Create Table'] . ";\n\n";
    } else {
        $output .= "Error fetching schema for $table: " . $conn->error . "\n";
    }
}
file_put_contents('schema.txt', $output);
$conn->close();
?>
