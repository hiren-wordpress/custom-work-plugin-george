<?php
// Ensure no output has been sent before
ob_start();

// Use Composer's autoloader
require 'vendor/autoload.php';

use Shuchkin\SimpleXLSXGen;

// Sample data to write to Excel
$data = [
    ['Name', 'Age', 'City'],  // Headers
    ['John Doe', 25, 'New York'],
    ['Jane Smith', 30, 'Los Angeles'],
    ['Bob Johnson', 35, 'Chicago']
];

// Clear any previous output
ob_clean();

// Generate Excel file
$xlsx = SimpleXLSXGen::fromArray($data);

// Set filename
$file_name = 'users_' . date('Y-m-d') . '.xlsx';

// Send headers before file content
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Cache-Control: max-age=0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output the Excel file directly to the browser
$xlsx->downloadAs($file_name);

// End the script
exit();
?>