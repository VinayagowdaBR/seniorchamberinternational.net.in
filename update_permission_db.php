<?php
$conn = new mysqli('localhost', 'root', '', 'ztakzvxv_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First, let's see how 'Offline Payment' is stored to copy its structure
$result = $conn->query("SELECT * FROM permission WHERE codename LIKE '%payment%' OR name LIKE '%payment%'");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Found existing payment permission: " . json_encode($row) . "\n";
    }
}

// Now insert the new one if it doesn't exist
$codename = 'offline_contribution_make';
$name = 'Offline Contribution';
$description = 'Contribution Payment';

$check = $conn->query("SELECT * FROM permission WHERE codename = '$codename'");
if ($check->num_rows == 0) {
    // Insert
    $sql = "INSERT INTO permission (name, codename, parent_status, description) VALUES ('$name', '$codename', 'parent', '$description')";
    if ($conn->query($sql) === TRUE) {
        echo "New permission created successfully.\n";
    } else {
        echo "Error: " . $sql . "\n" . $conn->error . "\n";
    }
} else {
    echo "Permission '$codename' already exists.\n";
}

$conn->close();
?>
