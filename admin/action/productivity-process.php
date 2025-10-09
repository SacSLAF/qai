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
    $productivity_category_id = intval($_POST['category_id']); 
    $uploaded_by = $_SESSION['admin_id'];
    $is_active = 1;
    
    // Handle conditional fields - only set if they exist
    $osh_category_id = isset($_POST['osh_category_id']) && !empty($_POST['osh_category_id']) 
        ? intval($_POST['osh_category_id']) 
        : null;
        
    $environment_category_id = isset($_POST['environment_category_id']) && !empty($_POST['environment_category_id']) 
        ? intval($_POST['environment_category_id']) 
        : null;
        
    $branch_id = isset($_POST['branch_id']) && !empty($_POST['branch_id']) 
        ? intval($_POST['branch_id']) 
        : null;

    $qcc_category_id = isset($_POST['qcc_category_id']) && !empty($_POST['qcc_category_id']) 
        ? $db->real_escape_string($_POST['qcc_category_id']) 
        : null;

    // Check if it's QCC Active Registration (no file upload)
    if ($productivity_category_id == 3 && $qcc_category_id == 'registrations') {
        // Validate QCC registration fields
        $qcc_name = isset($_POST['qcc_name']) ? trim($_POST['qcc_name']) : '';
        $slaf_establishment_id = isset($_POST['slaf_establishment_id']) ? intval($_POST['slaf_establishment_id']) : 0;
        $location = isset($_POST['location']) ? trim($_POST['location']) : '';
        $team_members = isset($_POST['team_members']) ? trim($_POST['team_members']) : '';

        // Validate required fields for QCC registration
        if (empty($qcc_name) || empty($slaf_establishment_id) || empty($location) || empty($team_members)) {
            $_SESSION['error'] = "All QCC registration fields are required.";
            header("Location: ../productivity-docs.php?error=1");
            exit();
        }

        try {
            // Insert into active_qcc_registrations table
            $stmt = $db->prepare("INSERT INTO active_qcc_registrations 
                                (title, description, qcc_name, slaf_establishment_id, location, team_members, category_id, qcc_category_id, main_category, created_by, is_active) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'productivity', ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("sssisssisi", $title, $description, $qcc_name, $slaf_establishment_id, $location, $team_members, $productivity_category_id, $qcc_category_id, $uploaded_by, $is_active);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "QCC registration added successfully!";
                    header("Location: ../productivity-docs.php?success=1");
                    exit();
                } else {
                    error_log("QCC registration execute failed: " . $stmt->error);
                    $_SESSION['error'] = "Failed to add QCC registration. Please try again.";
                }
                $stmt->close();
            } else {
                error_log("QCC registration prepare failed: " . $db->error);
                $_SESSION['error'] = "Database preparation failed for QCC registration.";
            }
        } catch (Exception $e) {
            error_log("QCC registration error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while processing QCC registration.";
        }

        // Redirect with error if we get here
        header("Location: ../productivity-docs.php?error=1");
        exit();

    } else {
        // Handle regular document upload (existing logic)
        if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
            $allowed_types = ['pdf', 'docx', 'xlsx'];
            $file_name = $_FILES['document']['name'];
            $file_tmp = $_FILES['document']['tmp_name'];
            $file_size = $_FILES['document']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Validate file type
            if (!in_array($file_ext, $allowed_types)) {
                $_SESSION['error'] = "Invalid file type. Allowed types: PDF, DOCX, XLSX.";
                header("Location: ../productivity-docs.php?error=1");
                exit();
            }

            // Create uploads directory if it doesn't exist
            $target_dir = "uploads/productivity/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Step 1: Upload temporary unique file
            $temp_name = uniqid('temp_') . '.' . $file_ext;
            $temp_path = $target_dir . $temp_name;
            
            if (move_uploaded_file($file_tmp, $temp_path)) {
                // Step 2: Insert into DB with temporary path
                $stmt = $db->prepare("INSERT INTO productivity_documents (title, description, productivity_category_id, osh_category_id, environment_category_id, file_path, uploaded_by, is_active, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt) {
                    $stmt->bind_param("ssiiisiii", $title, $description, $productivity_category_id, $osh_category_id, $environment_category_id, $temp_path, $uploaded_by, $is_active, $branch_id);
                    
                    if ($stmt->execute()) {
                        $last_id = $stmt->insert_id;

                        // Step 3: Rename file using ID
                        $new_name = 'doc_' . $last_id . '.' . $file_ext;
                        $new_path = $target_dir . $new_name;

                        if (rename($temp_path, $new_path)) {
                            // Step 4: Update DB with new file path
                            $update_stmt = $db->prepare("UPDATE productivity_documents SET file_path = ? WHERE id = ?");
                            if ($update_stmt) {
                                $update_stmt->bind_param("si", $new_path, $last_id);
                                $update_stmt->execute();
                                $update_stmt->close();
                                
                                $_SESSION['success'] = "Document uploaded successfully!";
                                header("Location: ../productivity-docs.php?success=1");
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
            // For QCC Registration Form (reg-form), file is required
            if ($productivity_category_id == 3 && $qcc_category_id == 'reg-form') {
                $error_code = $_FILES['document']['error'] ?? 'unknown';
                error_log("File upload error for QCC Registration Form: " . $error_code);
                $_SESSION['error'] = "Document file is required for QCC Registration Form upload.";
            } else {
                $error_code = $_FILES['document']['error'] ?? 'unknown';
                error_log("File upload error: " . $error_code);
                $_SESSION['error'] = "No file uploaded or upload error occurred. Error code: " . $error_code;
            }
        }

        // Redirect with error if we get here
        header("Location: ../productivity-docs.php?error=1");
        exit();
    }
} else {
    header("Location: ../productivity-docs.php");
    exit();
}