<?php
// Database Setup Script for Event Manager
// Run this file once to create the database and tables

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'event_manager';

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate database
    $pdo->exec("DROP DATABASE IF EXISTS $database");
    $pdo->exec("CREATE DATABASE $database");
    echo "✓ Database '$database' created successfully<br>";
    
    // Connect to the new database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents('sql/database.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Database tables created successfully<br>";
    echo "✓ Sample data inserted<br>";
    echo "<br><strong>Setup completed successfully!</strong><br>";
    echo "<br>Default admin credentials:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<br><a href='index.php'>Go to Event Manager</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>