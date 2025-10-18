<?php
require_once 'config/db.php';

$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['email'] = 'admin@eventmanager.com';
$_SESSION['full_name'] = 'System Administrator';
$_SESSION['role'] = 'admin';

header('Location: dashboard.php');
exit();
?>