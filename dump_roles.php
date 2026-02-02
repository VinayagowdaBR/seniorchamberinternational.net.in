<?php
$conn = new mysqli("localhost", "root", "", "ztakzvxv_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT role_id, name FROM role";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["role_id"]. " - Name: " . $row["name"]. "\n";
    }
} else {
    echo "0 results";
}
$conn->close();
?>
