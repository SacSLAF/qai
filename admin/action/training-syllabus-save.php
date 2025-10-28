<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header('Location: ../training-syllabus-form.php');
    exit();
}

// Debug: Log POST data
error_log("Training Syllabus Save - POST Data: " . print_r($_POST, true));
error_log("Training Syllabus Save - FILES Data: " . print_r($_FILES, true));

// Collect fields
$syllabus_no = trim($_POST['syllabus_no'] ?? '');
$formation_id = (int)($_POST['formation_id'] ?? 0);
$type_id = (int)($_POST['type_id'] ?? 0);
$trade = trim($_POST['trade'] ?? '');
$syllabus_type = trim($_POST['syllabus_type'] ?? '');
$description = trim($_POST['description'] ?? '');
$issue = trim($_POST['issue'] ?? '');
$revision = trim($_POST['revision'] ?? '');
$revision_date = $_POST['revision_date'] ?? null;
$ac_categories_id = (int)($_POST['ac_categories_id'] ?? 0);

// Validate required fields
$required_fields = [
    'syllabus_no' => $syllabus_no,
    'formation_id' => $formation_id,
    'type_id' => $type_id,
    'description' => $description,
    'issue' => $issue,
    'ac_categories_id' => $ac_categories_id
];

foreach ($required_fields as $field => $value) {
    if (empty($value)) {
        $_SESSION['error'] = "All required fields must be filled. Missing: $field";
        header('Location: ../training-syllabus-form.php?error=1');
        exit();
    }
}

// File upload handling
$file_uploaded = false;
$file_extension = '';
$temp_path = '';
$new_path = '';
$final_file_path = '';

// Check if we're in edit mode and no new file is provided
$is_edit_mode = isset($_POST['id']) && !empty($_POST['id']);
$has_new_file = isset($_FILES['syllabus_file']) && $_FILES['syllabus_file']['error'] === UPLOAD_ERR_OK;

if (!$is_edit_mode && !$has_new_file) {
    $_SESSION['error'] = "Syllabus file is required for new records";
    header('Location: ../training-syllabus-form.php?error=2');
    exit();
}

if ($has_new_file) {
    $file = $_FILES['syllabus_file'];
    error_log("File upload details - Name: " . $file['name'] . ", Size: " . $file['size'] . ", Error: " . $file['error']);
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error'] = "Only PDF, DOC, DOCX, XLS, XLSX files are allowed. Your file: .$file_extension";
        header('Location: ../training-syllabus-form.php?error=4');
        exit();
    }
    
    // Validate file size (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        $_SESSION['error'] = "File size exceeds the 10MB limit. Your file: " . round($file['size'] / 1024 / 1024, 2) . "MB";
        header('Location: ../training-syllabus-form.php?error=5');
        exit();
    }

    // Create upload directory
    $upload_dir = 'uploads/training_syllabus/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            $_SESSION['error'] = "Failed to create upload directory: $upload_dir";
            header('Location: ../training-syllabus-form.php?error=3');
            exit();
        }
    }

    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        $_SESSION['error'] = "Upload directory is not writable: $upload_dir";
        header('Location: ../training-syllabus-form.php?error=3');
        exit();
    }

    // Upload temporary file
    $temp_name = 'temp_' . time() . '_' . uniqid() . '.' . $file_extension;
    $temp_path = $upload_dir . $temp_name;
    
    error_log("Attempting to move uploaded file to: $temp_path");
    
    if (!move_uploaded_file($file['tmp_name'], $temp_path)) {
        $upload_error = "File upload failed. ";
        $upload_error .= "Temp path: " . $file['tmp_name'] . ", ";
        $upload_error .= "Target path: $temp_path, ";
        $upload_error .= "Upload error code: " . $file['error'];
        
        $_SESSION['error'] = $upload_error;
        header('Location: ../training-syllabus-form.php?error=6');
        exit();
    }
    
    // Verify the file was actually moved
    if (!file_exists($temp_path)) {
        $_SESSION['error'] = "File was not successfully uploaded to: $temp_path";
        header('Location: ../training-syllabus-form.php?error=6');
        exit();
    }
    
    $file_uploaded = true;
    error_log("File successfully uploaded to temporary location: $temp_path");
}

// Prepare revision date for database
$revision_date_db = (!empty($revision_date) && $revision_date != '0000-00-00') ? $revision_date : null;

try {
    // Begin transaction
    $db->begin_transaction();
    error_log("Database transaction started");

    if ($is_edit_mode) {
        // UPDATE EXISTING RECORD
        $id = (int)$_POST['id'];
        $existing_file_path = $_POST['existing_file'] ?? '';
        
        if ($file_uploaded) {
            // Use temporary path for now, will rename later
            $final_file_path = 'uploads/training_syllabus/' . basename($temp_path);
        } else {
            // Keep existing file
            $final_file_path = $existing_file_path;
        }
        
        $sql = "UPDATE training_syllabus SET 
                syllabus_no = ?, formation_id = ?, type_id = ?, trade = ?, syllabus_type = ?,
                description = ?, issue = ?, revision = ?, revision_date = ?, 
                ac_categories_id = ?, file_path = ?
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'siissssssisi',
            $syllabus_no, $formation_id, $type_id, $trade, $syllabus_type,
            $description, $issue, $revision, $revision_date_db,
            $ac_categories_id, $final_file_path, $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database update failed: " . $stmt->error);
        }
        
        $last_id = $id;
        $stmt->close();
        error_log("Existing record updated with ID: $last_id");

    } else {
        // INSERT NEW RECORD
        if ($file_uploaded) {
            $final_file_path = 'uploads/training_syllabus/' . basename($temp_path);
        } else {
            throw new Exception("No file provided for new record");
        }
        
        $sql = "INSERT INTO training_syllabus (
            syllabus_no, formation_id, type_id, trade, syllabus_type,
            description, issue, revision, revision_date, ac_categories_id, file_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'siissssssis',
            $syllabus_no, $formation_id, $type_id, $trade, $syllabus_type,
            $description, $issue, $revision, $revision_date_db,
            $ac_categories_id, $final_file_path
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . $stmt->error);
        }
        
        $last_id = $stmt->insert_id;
        $stmt->close();
        error_log("New record inserted with ID: $last_id");
    }

    // FILE RENAMING LOGIC
    if ($file_uploaded) {
        $new_name = 'syllabus_' . $last_id . '.' . $file_extension;
        $new_path = $upload_dir . $new_name;
        $new_file_path = 'uploads/training_syllabus/' . $new_name;

        error_log("Attempting to rename file from: $temp_path to: $new_path");
        
        // Rename file using ID
        if (!rename($temp_path, $new_path)) {
            $rename_error = "File rename failed. ";
            $rename_error .= "Source exists: " . (file_exists($temp_path) ? 'Yes' : 'No') . ", ";
            $rename_error .= "Target exists: " . (file_exists($new_path) ? 'Yes' : 'No') . ", ";
            $rename_error .= "Source: $temp_path, Target: $new_path";
            
            throw new Exception($rename_error);
        }

        // Verify the file was renamed
        if (!file_exists($new_path)) {
            throw new Exception("File was not successfully renamed to: $new_path");
        }

        // Update DB with new file path
        $update_stmt = $db->prepare("UPDATE training_syllabus SET file_path = ? WHERE id = ?");
        if (!$update_stmt) {
            throw new Exception("Update prepare failed: " . $db->error);
        }
        
        $update_stmt->bind_param("si", $new_file_path, $last_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Update execute failed: " . $update_stmt->error);
        }
        
        $update_stmt->close();
        error_log("File successfully renamed and database updated with new path: $new_file_path");
        
        // Clean up: if this was an edit and we have a new file, delete the old file
        if ($is_edit_mode && !empty($_POST['existing_file']) && file_exists('../../' . $_POST['existing_file'])) {
            $old_file = '../../' . $_POST['existing_file'];
            if (unlink($old_file)) {
                error_log("Old file deleted: $old_file");
            } else {
                error_log("Warning: Could not delete old file: $old_file");
            }
        }
    }

    // Commit transaction
    $db->commit();
    error_log("Transaction committed successfully");

    $_SESSION['success'] = "Training syllabus saved successfully!";
    header('Location: ../training-syllabus.php?success=1');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    error_log("Transaction rolled back due to error: " . $e->getMessage());
    
    // Delete temporary file if it exists
    if (!empty($temp_path) && file_exists($temp_path)) {
        if (unlink($temp_path)) {
            error_log("Temporary file deleted after error: $temp_path");
        } else {
            error_log("Warning: Could not delete temporary file: $temp_path");
        }
    }
    
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header('Location: ../training-syllabus-form.php?error=7');
    exit();
}
?>