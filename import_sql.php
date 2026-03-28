<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

echo "<h2>Railway Database Importer</h2>";
echo "Attempting to connect to host: " . ($servername) . "...<br>";

$sqlFile = 'system (29).sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at " . $sqlFile);
}

$sql = file_get_contents($sqlFile);

// Remove comments and whitespace
$queries = explode(";\n", $sql);

$successCount = 0;
$errorCount = 0;

foreach ($queries as $query) {
    if (trim($query)) {
        try {
            if ($conn->query($query)) {
                $successCount++;
            } else {
                $errorCount++;
            }
        } catch (mysqli_sql_exception $e) {
            // Table already exists or other non-fatal error, keep going
            $errorCount++;
        }
    }
}

echo "<h3>Import Finished!</h3>";
echo "Successfully executed queries: $successCount<br>";
echo "Queries with warnings/errors: $errorCount (Note: Errors are normal if tables already exist)<br>";
echo "<br><a href='log2/log2login.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";

// Standard safety: Suggest deleting this file after use
echo "<p style='color: red; margin-top: 20px;'>Important: For security, please delete this 'import_sql.php' file from your project after the import is successful.</p>";
