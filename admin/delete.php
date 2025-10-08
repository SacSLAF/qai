<?php
// delete.php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and has permission
// Add your authentication logic here

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log deletion attempts
error_log("Delete request received: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = intval($_POST['id']);
        
        // First, get the file path to delete the physical file
        $stmt = $db->prepare("SELECT file_path FROM productivity_documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $document = $result->fetch_assoc();
            $file_path = '../' . $document['file_path']; // Adjust path as needed
            
            // Delete the database record
            $delete_stmt = $db->prepare("DELETE FROM productivity_documents WHERE id = ?");
            $delete_stmt->bind_param("i", $id);
            
            if ($delete_stmt->execute()) {
                // Delete the physical file if it exists
                if (file_exists($file_path) && is_writable($file_path)) {
                    if (unlink($file_path)) {
                        error_log("File deleted successfully: " . $file_path);
                    } else {
                        error_log("Failed to delete file: " . $file_path);
                    }
                }
                
                // Redirect with success message
                header("Location: productivity-docs.php?success=2");
                exit();
            } else {
                error_log("Database deletion failed: " . $db->error);
                // Redirect with error message
                header("Location: productivity-docs.php?error=1");
                exit();
            }
        } else {
            // Document not found
            error_log("Document not found with ID: " . $id);
            header("Location: productivity-docs.php?error=2");
            exit();
        }
    } catch (Exception $e) {
        error_log("Exception in delete.php: " . $e->getMessage());
        header("Location: productivity-docs.php?error=3");
        exit();
    }
} else {
    // Invalid request
    error_log("Invalid delete request method or missing ID");
    header("Location: productivity-docs.php?error=4");
    exit();
}
?>