<?php
include '../includes/db.php';

echo "<h2>Creating Job Applications Table</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f8f9fa; font-weight: bold; }
    .status { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
    .pending { background: #fff3cd; color: #856404; }
    .reviewed { background: #cce5ff; color: #004085; }
    .accepted { background: #d4edda; color: #155724; }
    .rejected { background: #f8d7da; color: #721c24; }
</style>";

echo "<div class='container'>";

// Create job_applications table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS job_applications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    job_position VARCHAR(100) NOT NULL,
    job_department VARCHAR(50) NOT NULL,
    job_branch VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    home_address TEXT NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NOT NULL,
    civil_status ENUM('single', 'married', 'divorced', 'widowed', 'separated') NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    resume_path VARCHAR(255),
    valid_id_path VARCHAR(255),
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    INDEX idx_job_id (job_id),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_application_date (application_date)
)";

if ($conn->query($table_sql) === TRUE) {
    echo "<div class='success'>✅ Job Applications table created successfully!</div>";

    // Show table structure
    echo "<h3>Table Structure:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    $describe_sql = "DESCRIBE job_applications";
    $result = $conn->query($describe_sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    // Check for existing applications
    $count_sql = "SELECT COUNT(*) as total FROM job_applications";
    $count_result = $conn->query($count_sql);
    $count = $count_result->fetch_assoc()['total'];

    echo "<div class='info'>";
    echo "<strong>Current Status:</strong><br>";
    echo "• Table created with 18 fields<br>";
    echo "• Indexes added for performance<br>";
    echo "• Total applications in database: <strong>$count</strong><br>";
    echo "</div>";

    if ($count > 0) {
        echo "<h3>Recent Applications:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Position</th><th>Status</th><th>Applied Date</th></tr>";

        $recent_sql = "SELECT id, full_name, email, job_position, status, application_date FROM job_applications ORDER BY application_date DESC LIMIT 10";
        $recent_result = $conn->query($recent_sql);

        while($row = $recent_result->fetch_assoc()) {
            $status_class = strtolower($row['status']);
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['job_position']) . "</td>";
            echo "<td><span class='status $status_class'>" . ucfirst($row['status']) . "</span></td>";
            echo "<td>" . date('M d, Y H:i', strtotime($row['application_date'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} else {
    echo "<div class='error'>❌ Error creating table: " . $conn->error . "</div>";
}

$conn->close();

echo "<br><a href='hr1main.php?section=recruitment' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Back to HR Dashboard</a>";
echo "<br><br><a href='career.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-left: 10px;'>View Career Page</a>";

echo "</div>";
?>