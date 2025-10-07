<?php
require_once '../../includes/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $publication_category_id = intval($_POST['publication_category_id']);
    $uploaded_by = $_SESSION['admin_id'];
    $is_active = 1;
    
    // Handle maintenance category and branch - only set if they exist
    $maintenance_category_id = isset($_POST['maintenance_category_id']) && !empty($_POST['maintenance_category_id']) 
        ? intval($_POST['maintenance_category_id']) 
        : null;
        
    $branch_id = isset($_POST['branch_id']) && !empty($_POST['branch_id']) 
        ? intval($_POST['branch_id']) 
        : null;

    // Validate required fields
    if (empty($title) || empty($publication_category_id)) {
        $_SESSION['error'] = "Title and Publication Category are required fields.";
        header("Location: ../publications-docs.php?error=1");
        exit();
    }

    // File upload validation
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== 0) {
        $error_code = $_FILES['document']['error'] ?? 'unknown';
        $_SESSION['error'] = "No file uploaded or upload error occurred. Error code: " . $error_code;
        header("Location: ../publications-docs.php?error=1");
        exit();
    }

    $allowed_types = ['pdf', 'docx', 'xlsx'];
    $file_name = $_FILES['document']['name'];
    $file_tmp = $_FILES['document']['tmp_name'];
    $file_size = $_FILES['document']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validate file type
    if (!in_array($file_ext, $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Allowed types: PDF, DOCX, XLSX.";
        header("Location: ../publications-docs.php?error=1");
        exit();
    }

    // Validate file size (5MB max)
    if ($file_size > 5 * 1024 * 1024) {
        $_SESSION['error'] = "File size exceeds the 5MB limit.";
        header("Location: ../publications-docs.php?error=1");
        exit();
    }

    // Create uploads directory if it doesn't exist
    $target_dir = "uploads/publication/";
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            $_SESSION['error'] = "Failed to create upload directory.";
            header("Location: ../publications-docs.php?error=1");
            exit();
        }
    }

    // Step 1: Upload temporary unique file
    $temp_name = uniqid('temp_') . '.' . $file_ext;
    $temp_path = $target_dir . $temp_name;
    
    if (!move_uploaded_file($file_tmp, $temp_path)) {
        $_SESSION['error'] = "File upload failed.";
        header("Location: ../publications-docs.php?error=1");
        exit();
    }

    try {
        // Begin transaction
        $db->begin_transaction();

        // Step 2: Generate the final file name first
        // We need to get an ID first to use in the filename
        $stmt = $db->prepare("INSERT INTO publication_documents (title, description, publication_category_id, maintenance_category_id, file_path, uploaded_by, is_active, branch_id) VALUES (?, ?, ?, ?, '', ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }
        
        // Use empty string for file_path initially
        $stmt->bind_param("ssiiiii", $title, $description, $publication_category_id, $maintenance_category_id, $uploaded_by, $is_active, $branch_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . $stmt->error);
        }
        
        $last_id = $stmt->insert_id;
        $stmt->close();

        // Step 3: Generate the final file name and path
        $new_name = 'doc_' . $last_id . '.' . $file_ext;
        $new_path = $target_dir . $new_name;

        // Step 4: Rename file using ID
        if (!rename($temp_path, $new_path)) {
            throw new Exception("File rename failed from: $temp_path to: $new_path");
        }

        // Step 5: Update DB with new file path in publication_documents table
        $update_stmt = $db->prepare("UPDATE publication_documents SET file_path = ? WHERE id = ?");
        if (!$update_stmt) {
            throw new Exception("Update prepare failed: " . $db->error);
        }
        
        $update_stmt->bind_param("si", $new_path, $last_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Update execute failed: " . $update_stmt->error);
        }
        
        $update_stmt->close();

        // Step 6: Insert into specific tables based on publication category
        if ($publication_category_id == 1) { // Online Subscription
            if (isset($_POST['related_aircraft']) && isset($_POST['subscription_period'])) {
                
                $stmt = $db->prepare("INSERT INTO online_subscription (title, description, related_aircraft, subscription_period, uploaded_by, file_path, is_active, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                if (!$stmt) {
                    throw new Exception("Online subscription preparation failed: " . $db->error);
                }
                
                $stmt->bind_param("ssssisii", $title, $description, $_POST['related_aircraft'], 
                                 $_POST['subscription_period'], $uploaded_by, $new_path, $is_active, $branch_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Online subscription insert failed: " . $stmt->error);
                }
                
                $stmt->close();
            }
        } 
        elseif ($publication_category_id == 2) { // Airworthiness Directives & Bulletins
            if (isset($_POST['aircraft_type'])) {
                
                // FIXED: Use the correct table name and parameter order
                $stmt = $db->prepare("INSERT INTO ad_bulletins (title, description, aircraft_type, uploaded_by, file_path, is_active, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                // Add this after the ad_bulletins insert to debug
error_log("AD Bulletins Insert - Title: " . $title);
error_log("AD Bulletins Insert - Description: " . $ad_description);
error_log("AD Bulletins Insert - Aircraft Type: " . $_POST['aircraft_type']);
error_log("AD Bulletins Insert - File Path: " . $new_path);
error_log("AD Bulletins Insert - Branch ID: " . $branch_id);

// Check if the file actually exists at the path
if (!file_exists($new_path)) {
    error_log("ERROR: File does not exist at path: " . $new_path);
}
                if (!$stmt) {
                    throw new Exception("AD bulletins preparation failed: " . $db->error);
                }
                
                // Use description from form or fallback to main description
                $ad_description = isset($_POST['ad_description']) ? $_POST['ad_description'] : $description;
                $stmt->bind_param("ssssisi", $title, $ad_description, $_POST['aircraft_type'], 
                                 $uploaded_by, $new_path, $is_active, $branch_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("AD bulletins insert failed: " . $stmt->error);
                }
                
                $stmt->close();
            }
        }

        // Commit transaction
        $db->commit();

        $_SESSION['success'] = "Document uploaded successfully!";
        header("Location: ../publications-docs.php?success=1");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        
        // Delete temporary file if it exists
        if (file_exists($temp_path)) {
            unlink($temp_path);
        }
        
        error_log("Upload error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during upload: " . $e->getMessage();
        header("Location: ../publications-docs.php?error=1");
        exit();
    }
} else {
    header("Location: ../publications-docs.php");
    exit();
}