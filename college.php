<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/Database.php';

class CollegeAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getCollegeInfo($collegeId = null, $collegeName = null) {
        try {
            if ($collegeId) {
                $sql = "SELECT * FROM colleges WHERE college_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$collegeId]);
            } elseif ($collegeName) {
                $sql = "SELECT * FROM colleges WHERE college_name LIKE ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(["%$collegeName%"]);
            } else {
                $sql = "SELECT * FROM colleges ORDER BY college_name";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            
            $result = $stmt->fetchAll();
            
            return [
                'status' => 'success',
                'data' => $result,
                'count' => count($result)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database error occurred'
            ];
        }
    }
    
    public function addCollege($data) {
        try {
            $sql = "INSERT INTO colleges (college_id, college_name, naac_grade, location, established_year) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['college_id'],
                $data['college_name'],
                $data['naac_grade'],
                $data['location'],
                $data['established_year']
            ]);
            
            if ($result) {
                return [
                    'status' => 'success',
                    'message' => 'College added successfully',
                    'id' => $this->db->lastInsertId()
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to add college'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'College ID already exists or invalid data'
            ];
        }
    }
}

$api = new CollegeAPI();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $collegeId = $_GET['college_id'] ?? null;
        $collegeName = $_GET['college_name'] ?? null;
        $response = $api->getCollegeInfo($collegeId, $collegeName);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $response = $api->addCollege($input);
        break;
        
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>