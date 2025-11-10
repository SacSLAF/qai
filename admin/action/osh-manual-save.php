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
    header('Location: ../osh-manual-form.php');
    exit();
}

// Collect fields
$sno = isset($_POST['sno']) ? trim($_POST['sno']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$manual_no = isset($_POST['manual_no']) ? trim($_POST['manual_no']) : '';
$rev_status = isset($_POST['rev_status']) ? trim($_POST['rev_status']) : '';
$section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 5;
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 3;
$created_by = $_SESSION['admin_id'];

// Validate required fields
$required_fields = [
    'Serial Number' => $sno,
    'Description' => $description,
    'Manual Number' => $manual_no,
    'Revision Status' => $rev_status
];

$missing_fields = [];
foreach ($required_fields as $field_name => $value) {
    if (empty($value)) {
        $missing_fields[] = $field_name;
    }
}

if (!empty($missing_fields)) {
    $_SESSION['error'] = "All required fields must be filled. Missing: " . implode(', ', $missing_fields);
    header('Location: ../osh-manual-form.php?error=1');
    exit();
}

// File upload handling
$file_uploaded = false;
$file_extension = '';
$temp_path = '';
$new_path = '';
$final_file_path = '';

// Check if we're in edit mode
$is_edit_mode = isset($_POST['id']) && !empty($_POST['id']);
$has_new_file = isset($_FILES['manual_file']) && $_FILES['manual_file']['error'] === UPLOAD_ERR_OK;
$has_existing_file = isset($_POST['existing_file']) && !empty($_POST['existing_file']);

// File validation for new records
if (!$is_edit_mode && !$has_new_file) {
    $_SESSION['error'] = "Manual file is required for new records";
    header('Location: ../osh-manual-form.php?error=2');
    exit();
}

// For edit mode, if no new file is provided but existing file exists, that's fine
if ($is_edit_mode && !$has_new_file && !$has_existing_file) {
    $_SESSION['error'] = "Manual file is required";
    header('Location: ../osh-manual-form.php?error=2');
    exit();
}

if ($has_new_file) {
    $file = $_FILES['manual_file'];
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['pdf', 'doc', 'docx'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error'] = "Only PDF, DOC, DOCX files are allowed. Your file: .$file_extension";
        header('Location: ../osh-manual-form.php?error=4');
        exit();
    }
    
    // Validate file size (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        $_SESSION['error'] = "File size exceeds the 10MB limit. Your file: " . round($file['size'] / 1024 / 1024, 2) . "MB";
        header('Location: ../osh-manual-form.php?error=5');
        exit();
    }

    // Create upload directory
    $upload_dir = 'uploads/osh_manuals/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            $_SESSION['error'] = "Failed to create upload directory: $upload_dir";
            header('Location: ../osh-manual-form.php?error=3');
            exit();
        }
    }

    // Upload temporary file
    $temp_name = 'temp_' . time() . '_' . uniqid() . '.' . $file_extension;
    $temp_path = $upload_dir . $temp_name;
    
    if (!move_uploaded_file($file['tmp_name'], $temp_path)) {
        $_SESSION['error'] = "File upload failed";
        header('Location: ../osh-manual-form.php?error=6');
        exit();
    }
    
    $file_uploaded = true;
}

try {
    // Begin transaction
    $db->begin_transaction();

    if ($is_edit_mode) {
        // UPDATE EXISTING RECORD
        $id = (int)$_POST['id'];
        $existing_file_path = $_POST['existing_file'] ?? '';
        
        if ($file_uploaded) {
            // Use temporary path for now, will rename later
            $final_file_path = 'uploads/osh_manuals/' . basename($temp_path);
        } else {
            // Keep existing file
            $final_file_path = $existing_file_path;
        }
        
        $sql = "UPDATE osh_manual SET 
                sno = ?, description = ?, manual_no = ?, rev_status = ?, 
                section_id = ?, category_id = ?, file_path = ?, created_by = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'ssssiisii',
            $sno, $description, $manual_no, $rev_status,
            $section_id, $category_id, $final_file_path, $created_by, $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database update failed: " . $stmt->error);
        }
        
        $last_id = $id;
        $stmt->close();

    } else {
        // INSERT NEW RECORD
        if ($file_uploaded) {
            $final_file_path = 'uploads/osh_manuals/' . basename($temp_path);
        } else {
            throw new Exception("No file provided for new record");
        }
        
        $sql = "INSERT INTO osh_manual (
            sno, description, manual_no, rev_status, section_id, category_id, file_path, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'ssssiisi',
            $sno, $description, $manual_no, $rev_status,
            $section_id, $category_id, $final_file_path, $created_by
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . $stmt->error);
        }
        
        $last_id = $stmt->insert_id;
        $stmt->close();
    }

    // FILE RENAMING LOGIC
    if ($file_uploaded) {
        $new_name = 'osh_' . $last_id . '.' . $file_extension;
        $new_path = $upload_dir . $new_name;
        $new_file_path = 'uploads/osh_manuals/' . $new_name;

        // Rename file using ID
        if (!rename($temp_path, $new_path)) {
            throw new Exception("File rename failed");
        }

        // Update DB with new file path
        $update_stmt = $db->prepare("UPDATE osh_manual SET file_path = ? WHERE id = ?");
        if (!$update_stmt) {
            throw new Exception("Update prepare failed: " . $db->error);
        }
        
        $update_stmt->bind_param("si", $new_file_path, $last_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Update execute failed: " . $update_stmt->error);
        }
        
        $update_stmt->close();
        
        // Clean up: if this was an edit and we have a new file, delete the old file
        if ($is_edit_mode && !empty($_POST['existing_file']) && file_exists('../../' . $_POST['existing_file'])) {
            unlink('../../' . $_POST['existing_file']);
        }
    }

    // Commit transaction
    $db->commit();

    $_SESSION['success'] = "OSH manual " . ($is_edit_mode ? 'updated' : 'saved') . " successfully!";
    header('Location: ../osh-manual.php?success=1');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    // Delete temporary file if it exists
    if (!empty($temp_path) && file_exists($temp_path)) {
        unlink($temp_path);
    }
    
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header('Location: ../osh-manual-form.php?error=7');
    exit();
}