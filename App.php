<?php
session_start();

class App {
    public static function init() {
        self::setTimezone();
        self::setErrorReporting();
        self::autoload();
    }
    
    private static function setTimezone() {
        date_default_timezone_set('Asia/Kolkata');
    }
    
    private static function setErrorReporting() {
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
    }
    
    private static function autoload() {
        spl_autoload_register(function($class) {
            $paths = ['models/', 'controllers/', 'config/'];
            foreach ($paths as $path) {
                $file = __DIR__ . '/../' . $path . $class . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }
    
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    public static function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public static function getCurrentUser() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'name' => $_SESSION['full_name'] ?? null
        ];
    }
}

App::init();
?>