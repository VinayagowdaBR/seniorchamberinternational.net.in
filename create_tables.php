<?php
$conn = new mysqli('localhost', 'root', '', 'ztakzvxv_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql1 = "CREATE TABLE IF NOT EXISTS `offline_contribution_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `plan_name` varchar(255) DEFAULT NULL,
  `total_members` int(11) NOT NULL DEFAULT 0,
  `base_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(100) DEFAULT 'offline',
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'completed',
  `payment_date` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `admin_id` (`admin_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$sql2 = "CREATE TABLE IF NOT EXISTS `offline_contribution_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `member_profile_id` varchar(50) DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `member_email` varchar(255) DEFAULT NULL,
  `area_name` varchar(255) DEFAULT NULL,
  `legion_name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql1) === TRUE) {
    echo "Table offline_contribution_invoices created successfully\n";
} else {
    echo "Error creating table offline_contribution_invoices: " . $conn->error . "\n";
}

if ($conn->query($sql2) === TRUE) {
    echo "Table offline_contribution_members created successfully\n";
} else {
    echo "Error creating table offline_contribution_members: " . $conn->error . "\n";
}

$conn->close();
?>
