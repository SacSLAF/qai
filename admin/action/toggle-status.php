<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['current_status'])) {
    try {
        $id = intval($_POST['id']);
        $current_status = intval($_POST['current_status']);
        $service_category_id = isset($_POST['service_category_id']) ? intval($_POST['service_category_id']) : null;
        
        // Begin transaction
        $db->begin_transaction();

        // Toggle the status (1 becomes 0, 0 becomes 1)
        $new_status = $current_status == 1 ? 0 : 1;
        
        // Update service_documents table
        $update_stmt = $db->prepare("UPDATE service_documents SET is_active = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_status, $id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Service document update failed: " . $update_stmt->error);
        }
        $update_stmt->close();

        // Update related tables based on service category - FIXED
        switch ($service_category_id) {
            case 2: // Aircraft Competency
                // Option 1: Using file pattern matching
                $file_pattern = 'doc_' . $id;
                $related_stmt = $db->prepare("UPDATE aircraft_competency SET is_active = ? WHERE file_path LIKE ?");
                $like_pattern = "%" . $file_pattern . "%";
                $related_stmt->bind_param("is", $new_status, $like_pattern);
                break;
                
            case 3: // Latitude & Extensions
                $file_pattern = 'doc_' . $id;
                $related_stmt = $db->prepare("UPDATE latitude_extension SET is_active = ? WHERE file_path LIKE ?");
                $like_pattern = "%" . $file_pattern . "%";
                $related_stmt->bind_param("is", $new_status, $like_pattern);
                break;
                
            case 5: // Vehicle Emission Test
                $file_pattern = 'doc_' . $id;
                $related_stmt = $db->prepare("UPDATE vehicle_emission_test SET is_active = ? WHERE file_path LIKE ?");
                $like_pattern = "%" . $file_pattern . "%";
                $related_stmt->bind_param("is", $new_status, $like_pattern);
                break;
                
            default:
                $related_stmt = null;
                break;
        }

        // Update related table if applicable
        if ($related_stmt) {
            if (!$related_stmt->execute()) {
                throw new Exception("Related table update failed: " . $related_stmt->error);
            }
            $related_stmt->close();
        }

        // Commit transaction
        $db->commit();

        $action = $new_status == 1 ? 'enabled' : 'disabled';
        $_SESSION['success'] = "Document $action successfully!";
        header("Location: ../services-docs.php?success=1");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        error_log("Exception in toggle-status.php: " . $e->getMessage());
        $_SESSION['error'] = "Error updating document status: " . $e->getMessage();
        header("Location: ../services-docs.php?error=1");
        exit();
    }
} else {
    // Invalid request
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../services-docs.php?error=2");
    exit();
}
?>