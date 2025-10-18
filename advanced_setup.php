<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'event_manager';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("DROP DATABASE IF EXISTS $database");
    $pdo->exec("CREATE DATABASE $database");
    
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('sql/database.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Advanced Event Manager Setup</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link href='css/style.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='row justify-content-center'>
                <div class='col-md-8'>
                    <div class='card glass-effect'>
                        <div class='card-header text-center'>
                            <h2><i class='fas fa-check-circle text-success'></i> Setup Complete!</h2>
                        </div>
                        <div class='card-body'>
                            <div class='alert alert-success'>
                                <h5>✓ Advanced Event Manager Successfully Installed</h5>
                                <ul class='mb-0'>
                                    <li>Database created with advanced schema</li>
                                    <li>Sample colleges and users added</li>
                                    <li>NAAC integration enabled</li>
                                    <li>API endpoints configured</li>
                                </ul>
                            </div>
                            
                            <h5>Default Login Credentials:</h5>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <div class='card'>
                                        <div class='card-body text-center'>
                                            <h6>Admin</h6>
                                            <p><strong>admin</strong><br>admin123</p>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-4'>
                                    <div class='card'>
                                        <div class='card-body text-center'>
                                            <h6>Faculty</h6>
                                            <p><strong>faculty1</strong><br>admin123</p>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-4'>
                                    <div class='card'>
                                        <div class='card-body text-center'>
                                            <h6>Student</h6>
                                            <p><strong>student1</strong><br>admin123</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class='text-center mt-4'>
                                <a href='index.php' class='btn btn-primary btn-lg me-3'>
                                    <i class='fas fa-home'></i> Go to Event Manager
                                </a>
                                <a href='api/college.php' class='btn btn-outline-primary btn-lg' target='_blank'>
                                    <i class='fas fa-api'></i> Test API
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>