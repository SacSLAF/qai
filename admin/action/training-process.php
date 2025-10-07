<?php
require_once '../../includes/config.php';
session_start(); // Add session start

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $training_category_id = intval($_POST['category_id']); 
    $branch_id = intval($_POST['branch_id']);
    $uploaded_by = $_SESSION['admin_id'];
    $is_active = 1;

    // File upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
        $allowed_types = ['pdf', 'docx', 'xlsx'];
        $file_name = $_FILES['document']['name'];
        $file_tmp = $_FILES['document']['tmp_name'];
        $file_size = $_FILES['document']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Allowed types: PDF, DOCX, XLSX.";
            header("Location: ../training-docs.php?error=1");
            exit();
        }

        // Create uploads directory if it doesn't exist
        $target_dir = "uploads/training/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Step 1: Upload temporary unique file
        $temp_name = uniqid('temp_') . '.' . $file_ext;
        $temp_path = $target_dir . $temp_name;
        
        if (move_uploaded_file($file_tmp, $temp_path)) {
            // Step 2: Insert into DB with temporary path - CORRECTED column names
            $stmt = $db->prepare("INSERT INTO training_documents (title, description, training_category_id, file_path, uploaded_by, is_active, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("ssisiii", $title, $description, $training_category_id, $temp_path, $uploaded_by, $is_active, $branch_id);
                
                if ($stmt->execute()) {
                    $last_id = $stmt->insert_id;

                    // Step 3: Rename file using ID
                    $new_name = 'doc_' . $last_id . '.' . $file_ext;
                    $new_path = $target_dir . $new_name;

                    if (rename($temp_path, $new_path)) {
                        // Step 4: Update DB with new file path
                        $update_stmt = $db->prepare("UPDATE training_documents SET file_path = ? WHERE id = ?");
                        if ($update_stmt) {
                            $update_stmt->bind_param("si", $new_path, $last_id);
                            $update_stmt->execute();
                            $update_stmt->close();
                            
                            $_SESSION['success'] = "Document uploaded successfully!";
                            header("Location: ../training-docs.php?success=1");
                            exit();
                        } else {
                            error_log("Update prepare failed: " . $db->error);
                            $_SESSION['error'] = "Database error occurred.";
                        }
                    } else {
                        error_log("File rename failed from: $temp_path to: $new_path");
                        $_SESSION['error'] = "File rename failed.";
                    }
                } else {
                    error_log("Execute failed: " . $stmt->error);
                    $_SESSION['error'] = "Database insert failed.";
                    unlink($temp_path); // Delete uploaded file on failure
                }
                $stmt->close();
            } else {
                error_log("Prepare failed: " . $db->error);
                $_SESSION['error'] = "Database preparation failed.";
                unlink($temp_path);
            }
        } else {
            error_log("File move failed: " . $_FILES['document']['error']);
            $_SESSION['error'] = "File upload failed.";
        }
    } else {
        $error_code = $_FILES['document']['error'] ?? 'unknown';
        error_log("File upload error: " . $error_code);
        $_SESSION['error'] = "No file uploaded or upload error occurred. Error code: " . $error_code;
    }

    // Redirect with error if we get here
    header("Location: ../training-docs.php?error=1");
    exit();
} else {
    header("Location: ../training-docs.php");
    exit();
}