<?php
require_once '../includes/config.php';

// header('Content-Type: application/json');

try {
    // Query to get all formations
    $sql = "SELECT id, formation_name, type FROM slaf_formations ORDER BY formation_name";
    $result = $db->query($sql);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $db->error);
    }
    
    $formations = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $formations[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formations
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load formations'
    ]);
}

$db->close();
?>