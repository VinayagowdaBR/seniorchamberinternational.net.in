<?php
// Bypass the direct script access check
define('BASEPATH', '1');

// Direct database connection to check permissions
$config = include('application/config/database.php');
$host = $config['default']['hostname'];
$username = $config['default']['username'];
$password = $config['default']['password'];
$database = $config['default']['database'];

$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "Connected successfully\n";

// Check if permission table exists
$table_result = $mysqli->query("SHOW TABLES LIKE 'permission'");
if ($table_result->num_rows == 0) {
    die("Permission table does not exist\n");
}

// Check all permissions in the database
$result = $mysqli->query("SELECT * FROM permission");
if ($result) {
    echo "All permissions in the database:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['permission_id'] . " | Name: " . $row['name'] . " | Codename: " . $row['codename'] . " | Parent: " . $row['parent_status'] . "\n";
    }
} else {
    echo "Error fetching permissions: " . $mysqli->error . "\n";
}

echo "\n";

// Check if award permissions exist
$award_result = $mysqli->query("SELECT * FROM permission WHERE codename LIKE '%award%'");
if ($award_result) {
    echo "Award-related permissions in the database:\n";
    if ($award_result->num_rows > 0) {
        while ($row = $award_result->fetch_assoc()) {
            echo "ID: " . $row['permission_id'] . " | Name: " . $row['name'] . " | Codename: " . $row['codename'] . " | Parent: " . $row['parent_status'] . "\n";
        }
    } else {
        echo "No award-related permissions found\n";
    }
} else {
    echo "Error checking award permissions: " . $mysqli->error . "\n";
}

// Check the president role
$president_result = $mysqli->query("SELECT * FROM role WHERE name = 'presedent' OR name LIKE '%president%'");
if ($president_result) {
    echo "\nPresident-related roles:\n";
    while ($row = $president_result->fetch_assoc()) {
        echo "Role ID: " . $row['role_id'] . " | Name: " . $row['name'] . " | Permissions: " . $row['permission'] . "\n";
    }
} else {
    echo "Error checking president roles: " . $mysqli->error . "\n";
}

$mysqli->close();
?>