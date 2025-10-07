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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // var_dump($_POST);
    // var_dump($_FILES);
    // exit();
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $service_category_id = intval($_POST['service_category_id']);
    $uploaded_by = $_SESSION['admin_id'];
    $is_active = 1;
    
    // Handle branch_id - only set if it exists and is not empty
    $branch_id = isset($_POST['branch_id']) && !empty($_POST['branch_id']) 
        ? intval($_POST['branch_id']) 
        : null;
    
    // Handle QA category - only set if it exists and is not empty
    $qa_category_id = isset($_POST['qa_category_id']) && !empty($_POST['qa_category_id']) 
        ? intval($_POST['qa_category_id']) 
        : null;

    // Validate required fields
    if (empty($title) || empty($service_category_id)) {
        $_SESSION['error'] = "Title and Service Category are required fields.";
        header("Location: ../services-docs.php?error=1");
        exit();
    }

    // File upload validation
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== 0) {
        $error_code = $_FILES['document']['error'] ?? 'unknown';
        $_SESSION['error'] = "No file uploaded or upload error occurred. Error code: " . $error_code;
        header("Location: ../services-docs.php?error=2");
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
        header("Location: ../services-docs.php?error=3");
        exit();
    }

    // Validate file size (5MB max)
    if ($file_size > 5 * 1024 * 1024) {
        $_SESSION['error'] = "File size exceeds the 5MB limit.";
        header("Location: ../services-docs.php?error=4");
        exit();
    }

    // Create uploads directory if it doesn't exist
    $target_dir = "uploads/services/";
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            $_SESSION['error'] = "Failed to create upload directory.";
            header("Location: ../services-docs.php?error=5");
        }
    }

    // Step 1: Upload temporary unique file
    $temp_name = uniqid('temp_') . '.' . $file_ext;
    $temp_path = $target_dir . $temp_name;
    
    if (!move_uploaded_file($file_tmp, $temp_path)) {
        $_SESSION['error'] = "File upload failed.";
        header("Location: ../services-docs.php?error=6");
        exit();
    }

    try {
        // Begin transaction
        $db->begin_transaction();

        // Step 2: Insert into service_documents table
        $stmt = $db->prepare("INSERT INTO service_documents (title, description, service_category_id, qa_category_id, file_path, uploaded_by, is_active, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }
        
        $stmt->bind_param("ssiisiii", $title, $description, $service_category_id, $qa_category_id, $temp_path, $uploaded_by, $is_active, $branch_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . $stmt->error);
        }
        
        $last_id = $stmt->insert_id;
        $stmt->close();

        // Step 3: Generate the final file name
        $new_name = 'doc_' . $last_id . '.' . $file_ext;
        $new_path = $target_dir . $new_name;

        // Step 4: Insert into specific tables based on service category
        if ($service_category_id == 2) { // Aircraft Competency
            if (isset($_POST['svc_no']) && isset($_POST['rank']) && isset($_POST['name']) && 
                isset($_POST['aircraft_type']) && isset($_POST['last_competency']) && isset($_POST['branch_id']) && 
                isset($_POST['renewal_date']) && isset($_POST['currency']) && isset($_POST['squadron'])) {
                
                $stmt = $db->prepare("INSERT INTO aircraft_competency (title, description, svc_no, `rank`, name, aircraft_type, last_level_of_competency, renewal_date, currency, squadron, uploaded_by, file_path, is_active, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)");
                
                if (!$stmt) {
                    throw new Exception("Aircraft competency preparation failed: " . $db->error);
                }
                
                // FIXED: Use the final file path (new_path) instead of temporary path
                $stmt->bind_param("ssssssssssisii", $title, $description, $_POST['svc_no'], $_POST['rank'], $_POST['name'], 
                                 $_POST['aircraft_type'], $_POST['last_competency'], $_POST['renewal_date'], 
                                 $_POST['currency'], $_POST['squadron'], $uploaded_by, $new_path, $is_active , $_POST['branch_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Aircraft competency insert failed: " . $stmt->error);
                }
                
                $stmt->close();
            }
        } 
        elseif ($service_category_id == 3) { // Latitudes & Extensions
            if (isset($_POST['latitude_description']) && isset($_POST['related_aircraft']) && isset($_POST['latitude_period'])) {
                
                $stmt = $db->prepare("INSERT INTO latitude_extension (title, description, latitude_description, related_aircraft, latitude_period, uploaded_by, file_path, is_active, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if (!$stmt) {
                    throw new Exception("Latitude extension preparation failed: " . $db->error);
                }
                
                // FIXED: Use the final file path (new_path) instead of temporary path
                $stmt->bind_param("sssssisii", $title, $description, $_POST['latitude_description'], 
                                 $_POST['related_aircraft'], $_POST['latitude_period'], 
                                 $uploaded_by, $new_path, $is_active, $branch_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Latitude extension insert failed: " . $stmt->error);
                }
                
                $stmt->close();
            }
        } 
        elseif ($service_category_id == 5) { // Vehicle Emission Test
            if (isset($_POST['vehicle_no']) && isset($_POST['test_date']) && isset($_POST['state'])) {
                
                $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';
                
                $stmt = $db->prepare("INSERT INTO vehicle_emission_test (title, description, vehicle_no, test_performed_date, state, remarks, uploaded_by, file_path, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if (!$stmt) {
                    throw new Exception("Vehicle emission test preparation failed: " . $db->error);
                }
                
                // FIXED: Use the final file path (new_path) instead of temporary path
                $stmt->bind_param("ssssssisi", $title, $description, $_POST['vehicle_no'], 
                                 $_POST['test_date'], $_POST['state'], $remarks, 
                                 $uploaded_by, $new_path, $is_active);
                
                if (!$stmt->execute()) {
                    throw new Exception("Vehicle emission test insert failed: " . $stmt->error);
                }
                
                $stmt->close();
            }
        }

        // Step 5: Rename file using ID
        if (!rename($temp_path, $new_path)) {
            throw new Exception("File rename failed from: $temp_path to: $new_path");
        }

        // Step 6: Update DB with new file path in service_documents table
        $update_stmt = $db->prepare("UPDATE service_documents SET file_path = ? WHERE id = ?");
        if (!$update_stmt) {
            throw new Exception("Update prepare failed: " . $db->error);
        }
        
        $update_stmt->bind_param("si", $new_path, $last_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Update execute failed: " . $update_stmt->error);
        }
        
        $update_stmt->close();

        // Commit transaction
        $db->commit();

        $_SESSION['success'] = "Document uploaded successfully!";
        header("Location: ../services-docs.php?success=1");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        
        // Delete temporary file if it exists
        if (file_exists($temp_path)) {
            unlink($temp_path);
        }
        // die("Upload error: " . $e->getMessage());
        error_log("Upload error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during upload: " . $e->getMessage();
        header("Location: ../services-docs.php?error=7");
        exit();
    }
} else {
    header("Location: ../services-docs.php");
    exit();
}