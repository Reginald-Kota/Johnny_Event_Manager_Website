<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (username, email, password, full_name, phone, role, college_id, naac_grade) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['username'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT),
            $data['full_name'], $data['phone'], $data['role'], 
            $data['college_id'] ?? null, $data['naac_grade'] ?? null
        ]);
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, college_id = ?, naac_grade = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['full_name'], $data['email'], $data['phone'],
            $data['college_id'], $data['naac_grade'], $id
        ]);
    }
    
    public function getAll() {
        $sql = "SELECT u.*, COUNT(r.id) as total_registrations 
                FROM users u 
                LEFT JOIN registrations r ON u.id = r.user_id 
                GROUP BY u.id ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>