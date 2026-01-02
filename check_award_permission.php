<?php
// Script to check and fix award permission for president role
// Direct database connection (update these values if needed)
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ztakzvxv_db';

$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h2>Award Permission Diagnostic Tool</h2>";

// Step 1: Check if 'award' permission exists
echo "<h3>Step 1: Checking 'award' permission...</h3>";
$award_permission = $mysqli->query("SELECT * FROM permission WHERE codename = 'award'");
if ($award_permission && $award_permission->num_rows > 0) {
    $award_row = $award_permission->fetch_assoc();
    $award_permission_id = $award_row['permission_id'];
    echo "✓ Found 'award' permission (ID: {$award_permission_id})<br>";
} else {
    echo "✗ 'award' permission NOT FOUND in database!<br>";
    echo "Please create the permission with codename 'award' first.<br>";
    $mysqli->close();
    exit;
}

// Step 2: Find president role
echo "<h3>Step 2: Finding president role...</h3>";
$president_query = "SELECT * FROM role WHERE name LIKE '%president%' OR name LIKE '%presedent%'";
$president_result = $mysqli->query($president_query);

if ($president_result && $president_result->num_rows > 0) {
    while ($role = $president_result->fetch_assoc()) {
        echo "Found role: {$role['name']} (ID: {$role['role_id']})<br>";
        
        // Step 3: Check current permissions
        echo "<h3>Step 3: Checking current permissions for role '{$role['name']}'...</h3>";
        $permissions = json_decode($role['permission'], true);
        
        if (!is_array($permissions)) {
            $permissions = [];
        }
        
        echo "Current permissions: " . implode(', ', $permissions) . "<br>";
        
        // Step 4: Check if award permission is present
        if (in_array($award_permission_id, $permissions)) {
            echo "✓ Award permission is already present!<br>";
        } else {
            echo "✗ Award permission is MISSING!<br>";
            
            // Step 5: Add award permission
            echo "<h3>Step 4: Adding award permission...</h3>";
            $permissions[] = $award_permission_id;
            $permissions = array_unique($permissions); // Remove duplicates
            $new_permissions_json = json_encode($permissions);
            
            $update_query = "UPDATE role SET permission = ? WHERE role_id = ?";
            $stmt = $mysqli->prepare($update_query);
            $stmt->bind_param("si", $new_permissions_json, $role['role_id']);
            
            if ($stmt->execute()) {
                echo "✓ Successfully added 'award' permission to role '{$role['name']}'!<br>";
                echo "Updated permissions: " . implode(', ', $permissions) . "<br>";
            } else {
                echo "✗ Failed to update role: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }
    }
} else {
    echo "✗ No president role found!<br>";
    echo "Please check the role name in the database.<br>";
}

// Step 6: Verify all award-related permissions
echo "<h3>Step 5: Checking all award-related permissions...</h3>";
$award_permissions = ['award', 'award_add', 'award_approve', 'award_report'];
foreach ($award_permissions as $perm_codename) {
    $perm_result = $mysqli->query("SELECT permission_id FROM permission WHERE codename = '$perm_codename'");
    if ($perm_result && $perm_result->num_rows > 0) {
        $perm_row = $perm_result->fetch_assoc();
        echo "✓ Found permission: $perm_codename (ID: {$perm_row['permission_id']})<br>";
    } else {
        echo "✗ Missing permission: $perm_codename<br>";
    }
}

echo "<br><h3>Done!</h3>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>If permissions were updated, please logout and login again as president</li>";
echo "<li>Clear browser cache if needed</li>";
echo "<li>The award menu should now appear in the sidebar</li>";
echo "</ol>";

$mysqli->close();
?>

