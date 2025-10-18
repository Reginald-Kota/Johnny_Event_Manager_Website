<?php
class Event {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO events (title, description, event_date, event_time, venue, max_participants, category, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'], $data['description'], $data['event_date'], 
            $data['event_time'], $data['venue'], $data['max_participants'],
            $data['category'], $data['created_by']
        ]);
    }
    
    public function getAll() {
        $sql = "SELECT e.*, u.full_name as organizer, COUNT(r.id) as registrations
                FROM events e 
                LEFT JOIN users u ON e.created_by = u.id
                LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'registered'
                GROUP BY e.id ORDER BY e.event_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $sql = "SELECT e.*, u.full_name as organizer 
                FROM events e 
                LEFT JOIN users u ON e.created_by = u.id 
                WHERE e.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, venue = ?, max_participants = ?, category = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'], $data['description'], $data['event_date'],
            $data['event_time'], $data['venue'], $data['max_participants'],
            $data['category'], $id
        ]);
    }
    
    public function register($userId, $eventId) {
        $sql = "INSERT INTO registrations (user_id, event_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $eventId]);
    }
    
    public function getRegistrations($eventId) {
        $sql = "SELECT u.*, r.registration_date, a.attendance_date
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN attendance a ON r.user_id = a.user_id AND r.event_id = a.event_id
                WHERE r.event_id = ? AND r.status = 'registered'
                ORDER BY r.registration_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }
}
?>