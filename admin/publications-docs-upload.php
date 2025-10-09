<!DOCTYPE html>
<html lang="en">

<?php
include "template/head.php";
require_once '../includes/config.php';

$publication_categories = $db->query("SELECT id, name FROM publication_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$maintenance_categories = $db->query("SELECT id, name FROM maintenance_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$branches = $db->query("SELECT id, name FROM branches ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<body>

    <?php
    include "template/preloader.php";
    ?>



    <div id="main-wrapper">


        <?php
        include "template/nav.php";
        // include "template/chatbox.php";
        include "template/header.php";
        ?>


        <?php
        include "template/desnav.php";
        ?>


        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
        <div class="row">
            <form method="post" enctype="multipart/form-data" action="action/publications-process.php">
                <!-- Title -->
                <div class="form-group">
                    <label for="title" class="required-label">Title</label>
                    <input type="text" class="form-control input-default" id="title" name="title" required value="">
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control input-rounded" id="description" name="description" rows="3"></textarea>
                </div>
                
                <input type="hidden" id="main" name="main" value="publication">
                
                <!-- Category Dropdown -->
                <div class="form-group">
                    <label for="publication_category_id" class="required-label">Publication Category</label>
                    <select name="publication_category_id" class="form-control input-default" id="publication_category_id" required onchange="toggleDynamicFields()">
                        <option value="" selected disabled>Select a category</option>
                        <option value="1">Online Subscription</option>
                        <option value="2">Airworthiness Directives & Bulletins</option>
                        <option value="3">QAI Safety Newsletters</option>
                        <option value="4">Maintenance Programme</option>
                        <option value="5">Technical Library</option>
                    </select>
                </div>
                
                <!-- Dynamic Fields Section -->
                <div id="dynamic_fields">
                    <!-- Fields will be inserted here based on category selection -->
                </div>
                
                <!-- Maintenance Category Dropdown (Initially Hidden) -->
                <div class="form-group" id="maintenance_category_container" style="display: none;">
                    <label for="maintenance_category_id" class="required-label">Maintenance Category</label>
                    <select name="maintenance_category_id" class="form-control input-default" id="maintenance_category_id">
                        <option value="">Select a maintenance category</option>
                        <option value="1">Servicing Schedule</option>
                        <option value="2">Worksheets</option>
                    </select>
                </div>
                
                <!-- Branch Dropdown (Initially Hidden) -->
                <div class="form-group" id="branch_container" style="display: none;">
                    <label for="branch_id" class="required-label">Branch</label>
                    <select name="branch_id" class="form-control input-default" id="branch_id">
                        <option value="">Select a branch</option>
                        <option value="1">Aeronautical Engineering</option>
                        <option value="4">Electronic Engineering</option>
                        <option value="5">General Engineering</option>>
                    </select>
                </div>
                
                <!-- Document File Upload -->
                <div class="form-group">
                    <label for="document" class="required-label">Document File</label>
                    <input type="file" class="form-control input-default" id="document" name="document" accept=".pdf,.docx,.xlsx" required>
                    <small class="text-muted">
                        Max file size: 5MB. Allowed types: PDF, DOCX, XLSX
                    </small>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


    <?php
    include "template/foot.php";
    ?>

    <!-- Bootstrap & jQuery -->
    <script src="../node_modules/jquery/jquery.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleDynamicFields() {
            const publicationCategory = document.getElementById('publication_category_id');
            const maintenanceContainer = document.getElementById('maintenance_category_container');
            const branchContainer = document.getElementById('branch_container');
            const maintenanceSelect = document.getElementById('maintenance_category_id');
            const branchSelect = document.getElementById('branch_id');
            const dynamicFields = document.getElementById('dynamic_fields');
            
            // Show maintenance category and branch dropdowns only if "Maintenance Programme" is selected (ID = 4)
            if (publicationCategory.value == '4') {
                maintenanceContainer.style.display = 'block';
                branchContainer.style.display = 'block';
                maintenanceSelect.setAttribute('required', 'required');
                branchSelect.setAttribute('required', 'required');
            } else {
                maintenanceContainer.style.display = 'none';
                branchContainer.style.display = 'none';
                maintenanceSelect.removeAttribute('required');
                branchSelect.removeAttribute('required');
                maintenanceSelect.value = ''; // Clear selection
                branchSelect.value = ''; // Clear selection
            }
            
            // Clear previous dynamic fields
            dynamicFields.innerHTML = '';
            
            // Add fields based on selected category
            if (publicationCategory.value == '1') { // Online Subscription
                dynamicFields.innerHTML = `
                    <div class="dynamic-section">
                        <h5 class="section-title"><i class="fas fa-globe me-2"></i>Online Subscription Details</h5>
                        <div class="form-group">
                            <label for="related_aircraft" class="required-label">Related Aircraft</label>
                            <input type="text" class="form-control input-default" id="related_aircraft" name="related_aircraft" required>
                        </div>
                        <div class="form-group">
                            <label for="subscription_period" class="required-label">Subscription Period</label>
                            <input type="text" class="form-control input-default" id="subscription_period" name="subscription_period" placeholder="e.g., 1 year, 6 months" required>
                        </div>
                    </div>
                `;
            } else if (publicationCategory.value == '2') { // Airworthiness Directives & Bulletins
                dynamicFields.innerHTML = `
                    <div class="dynamic-section">
                        <h5 class="section-title"><i class="fas fa-file-alt me-2"></i>Airworthiness Directives & Bulletins Details</h5>
                        <div class="form-group">
                            <label for="aircraft_type" class="required-label">Aircraft Type</label>
                            <input type="text" class="form-control input-default" id="aircraft_type" name="aircraft_type" required>
                        </div>
                        <div class="form-group">
                            <label for="ad_description" class="required-label">Description of the AD Bulletins</label>
                            <textarea class="form-control input-rounded" id="ad_description" name="ad_description" rows="3" required></textarea>
                        </div>
                    </div>
                `;
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleDynamicFields();
        });
    </script>

</body>

</html>