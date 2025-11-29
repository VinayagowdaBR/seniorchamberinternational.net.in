<?php
/**
 * PhonePe Contribution Payment System - Debug Test Script
 * 
 * This script helps verify that all components are working correctly
 * Run this from: http://localhost/senior-new/test_phonepe_contribution.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load CodeIgniter
define('BASEPATH', TRUE);
require_once 'application/config/config.php';
require_once 'application/config/database.php';

echo "<h1>PhonePe Contribution Payment System - Debug Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .test { margin: 15px 0; padding: 10px; border-left: 4px solid #ccc; }
    pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style>";

// Test 1: Check Sessions Directory
echo "<div class='test'>";
echo "<h2>Test 1: Sessions Directory</h2>";
$sessions_dir = APPPATH . 'sessions/';
if (file_exists($sessions_dir)) {
    if (is_writable($sessions_dir)) {
        echo "<span class='success'>✓ Sessions directory exists and is writable</span><br>";
        echo "Path: <code>$sessions_dir</code>";
    } else {
        echo "<span class='error'>✗ Sessions directory exists but is NOT writable</span><br>";
        echo "Path: <code>$sessions_dir</code><br>";
        echo "<strong>Fix:</strong> Run: <code>icacls \"$sessions_dir\" /grant Everyone:F</code>";
    }
} else {
    echo "<span class='error'>✗ Sessions directory does NOT exist</span><br>";
    echo "Expected path: <code>$sessions_dir</code><br>";
    echo "<strong>Fix:</strong> Create the directory manually or run the PowerShell command from the implementation plan.";
}
echo "</div>";

// Test 2: Check Database Connection
echo "<div class='test'>";
echo "<h2>Test 2: Database Connection</h2>";
try {
    $conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
    if ($conn->connect_error) {
        echo "<span class='error'>✗ Database connection failed: " . $conn->connect_error . "</span>";
    } else {
        echo "<span class='success'>✓ Database connection successful</span><br>";
        echo "Database: <code>" . $db['default']['database'] . "</code>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<span class='error'>✗ Database connection error: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Test 3: Check Required Tables
echo "<div class='test'>";
echo "<h2>Test 3: Required Database Tables</h2>";
$required_tables = [
    'contribution_bulk_payment_master',
    'contribution_bulk_payment_invoices',
    'package_payment',
    'plan',
    'member',
    'admin'
];

try {
    $conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<span class='success'>✓ Table '$table' exists</span><br>";
        } else {
            echo "<span class='error'>✗ Table '$table' is MISSING</span><br>";
        }
    }
    $conn->close();
} catch (Exception $e) {
    echo "<span class='error'>✗ Error checking tables: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Test 4: Check PhonePe Config
echo "<div class='test'>";
echo "<h2>Test 4: PhonePe Configuration</h2>";
$phonepe_config_file = APPPATH . 'config/phonepe_contribution.php';
if (file_exists($phonepe_config_file)) {
    echo "<span class='success'>✓ PhonePe config file exists</span><br>";
    include $phonepe_config_file;
    echo "<strong>Merchant ID:</strong> <code>" . ($config['phonepe_contribution_merchant_id'] ?? 'NOT SET') . "</code><br>";
    echo "<strong>Base URL:</strong> <code>" . ($config['phonepe_contribution_base_url'] ?? 'NOT SET') . "</code><br>";
    
    if (isset($config['phonepe_contribution_salt_key']) && !empty($config['phonepe_contribution_salt_key'])) {
        echo "<strong>Salt Key:</strong> <span class='success'>✓ Configured (hidden for security)</span><br>";
    } else {
        echo "<strong>Salt Key:</strong> <span class='error'>✗ NOT SET</span><br>";
    }
} else {
    echo "<span class='error'>✗ PhonePe config file NOT found</span><br>";
    echo "Expected: <code>$phonepe_config_file</code>";
}
echo "</div>";

// Test 5: Check Controllers
echo "<div class='test'>";
echo "<h2>Test 5: Controller Files</h2>";
$controllers = [
    'Phonepe_contribution.php' => APPPATH . 'controllers/Phonepe_contribution.php',
    'Admin.php' => APPPATH . 'controllers/Admin.php'
];

foreach ($controllers as $name => $path) {
    if (file_exists($path)) {
        echo "<span class='success'>✓ Controller '$name' exists</span><br>";
    } else {
        echo "<span class='error'>✗ Controller '$name' NOT found</span><br>";
    }
}
echo "</div>";

// Test 6: Check Library
echo "<div class='test'>";
echo "<h2>Test 6: PhonePe Library</h2>";
$library_path = APPPATH . 'libraries/Phonepe_contribution.php';
if (file_exists($library_path)) {
    echo "<span class='success'>✓ PhonePe library exists</span><br>";
    echo "Path: <code>$library_path</code>";
} else {
    echo "<span class='error'>✗ PhonePe library NOT found</span><br>";
    echo "Expected: <code>$library_path</code>";
}
echo "</div>";

// Test 7: Check Routes
echo "<div class='test'>";
echo "<h2>Test 7: Routes Configuration</h2>";
$routes_file = APPPATH . 'config/routes.php';
if (file_exists($routes_file)) {
    $routes_content = file_get_contents($routes_file);
    $phonepe_routes = [
        'phonepe_contribution/initiate_payment',
        'phonepe_contribution/payment_return',
        'phonepe_contribution/payment_callback'
    ];
    
    $all_found = true;
    foreach ($phonepe_routes as $route) {
        if (strpos($routes_content, $route) !== false) {
            echo "<span class='success'>✓ Route '$route' configured</span><br>";
        } else {
            echo "<span class='error'>✗ Route '$route' NOT configured</span><br>";
            $all_found = false;
        }
    }
    
    if (!$all_found) {
        echo "<br><strong>Fix:</strong> Add the missing routes to <code>routes.php</code> as shown in the implementation plan.";
    }
} else {
    echo "<span class='error'>✗ Routes file NOT found</span>";
}
echo "</div>";

// Test 8: Check Autoload
echo "<div class='test'>";
echo "<h2>Test 8: Autoload Configuration</h2>";
$autoload_file = APPPATH . 'config/autoload.php';
if (file_exists($autoload_file)) {
    include $autoload_file;
    if (in_array('session', $autoload['libraries'])) {
        echo "<span class='success'>✓ Session library is auto-loaded</span><br>";
    } else {
        echo "<span class='warning'>⚠ Session library is NOT auto-loaded</span><br>";
        echo "This might cause issues. Consider adding 'session' to autoload['libraries']";
    }
    
    if (in_array('database', $autoload['libraries'])) {
        echo "<span class='success'>✓ Database library is auto-loaded</span><br>";
    } else {
        echo "<span class='warning'>⚠ Database library is NOT auto-loaded</span><br>";
    }
} else {
    echo "<span class='error'>✗ Autoload file NOT found</span>";
}
echo "</div>";

// Test 9: Test PhonePe API Connectivity
echo "<div class='test'>";
echo "<h2>Test 9: PhonePe API Connectivity</h2>";
if (isset($config['phonepe_contribution_base_url'])) {
    $test_url = $config['phonepe_contribution_base_url'];
    echo "Testing connectivity to: <code>$test_url</code><br><br>";
    
    $ch = curl_init($test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<span class='error'>✗ Connection failed: $error</span><br>";
        echo "<strong>Possible causes:</strong> Firewall blocking, no internet connection, or incorrect URL";
    } else {
        echo "<span class='success'>✓ Connection successful (HTTP $http_code)</span><br>";
        echo "PhonePe sandbox is reachable.";
    }
} else {
    echo "<span class='error'>✗ PhonePe base URL not configured</span>";
}
echo "</div>";

// Summary
echo "<div class='test' style='border-left-color: #4CAF50; background: #f0f9f0;'>";
echo "<h2>Summary</h2>";
echo "<p>Review the test results above. All items marked with <span class='success'>✓</span> are working correctly.</p>";
echo "<p>Fix any items marked with <span class='error'>✗</span> before proceeding with payment testing.</p>";
echo "<p>Items marked with <span class='warning'>⚠</span> are warnings and may not be critical.</p>";
echo "<br>";
echo "<strong>Next Steps:</strong>";
echo "<ol>";
echo "<li>Fix any errors shown above</li>";
echo "<li>Access the admin panel: <a href='http://localhost/senior-new/admin/login'>Login</a></li>";
echo "<li>Navigate to Contribution Payment: <a href='http://localhost/senior-new/admin/contributionpayment'>Contribution Payment</a></li>";
echo "<li>Test the complete payment flow as described in the implementation plan</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Generated: " . date('Y-m-d H:i:s') . "</p>";
?>
