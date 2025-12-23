<?php
$conn = new mysqli('localhost', 'root', '', 'ztakzvxv_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = 'offline_contribution_make';
$codename = 'offline_contribution_make';
$parent_id = 0; // Assuming top level for now, or need to find parent

// Check if exists
$check = $conn->query("SELECT * FROM permission WHERE codename = '$codename'");
if ($check->num_rows == 0) {
     // We need to know the schema better to insert correctly. 
     // The previous output was garbled. Let's try to get columns first.
     $res = $conn->query("DESCRIBE permission");
     while($row = $res->fetch_assoc()) {
         echo $row['Field'] . "\n";
     }
} else {
    echo "Permission '$codename' already exists.\n";
}

$conn->close();
?>
