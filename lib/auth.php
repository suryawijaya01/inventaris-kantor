<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($username, $password) {
        $username = $this->db->real_escape_string($username);
        
        $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['departemen'] = $user['departemen'];
                $_SESSION['logged_in'] = true;
                
                return true;
            }
        }
        
        return false;
    }
    
    public function register($username, $password, $role = 'pegawai', $departemen = null) {
        $username = $this->db->real_escape_string($username);
        $departemen = $departemen ? $this->db->real_escape_string($departemen) : null;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if username already exists
        $check_query = "SELECT id FROM users WHERE username = '$username'";
        $check_result = $this->db->query($check_query);
        
        if ($check_result->num_rows > 0) {
            return false;
        }
        
        $query = "INSERT INTO users (username, password, role, departemen) 
                  VALUES ('$username', '$hashed_password', '$role', " . 
                  ($departemen ? "'$departemen'" : "NULL") . ")";
        
        return $this->db->query($query);
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $_ENV['BASE_PATH'] . '/login.php');
            exit();
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . $_ENV['BASE_PATH'] . '/admin/index.php');
            exit();
        }
    }
    
    public function getUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'departemen' => $_SESSION['departemen']
            ];
        }
        return null;
    }
    
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
?>