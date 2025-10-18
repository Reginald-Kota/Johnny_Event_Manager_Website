<?php
require_once 'config/db.php';

// Destroy session and redirect
session_destroy();
redirect('index.php');
?>