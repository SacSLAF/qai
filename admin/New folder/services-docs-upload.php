<!DOCTYPE html>
<html lang="en">

<?php
include "template/head.php";
require_once '../includes/config.php';

$service_categories = $db->query("SELECT id, name FROM service_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$qa_categories = $db->query("SELECT id, name FROM qa_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
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
        <div class="container-fluid">
            <div class="row">
                <form method="post" enctype="multipart/form-data" action="action/services-doc-process.php">

                    <!-- Service Category Dropdown -->
                    <div class="form-group">
                        <label for="service_category_id" class="required-label">Services Category</label>
                        <select name="service_category_id" class="form-control input-default" id="service_category_id" required onchange="toggleDynamicFields()">
                            <option value="">Select a category</option>
                            <option value="1">Quality Assurance Audits</option>
                            <option value="2">Aircraft Competency</option>
                            <option value="3">Latitudes & Extensions</option>
                            <option value="4">Modifications R&D Projects</option>
                            <option value="5">Vehicle Emission Test</option>
                        </select>
                    </div>
                    
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
                    
                    <input type="hidden" id="main" name="main" value="services">

                    <!-- QA Category Dropdown (Initially Hidden) -->
                    <div class="form-group" id="qa_category_container" style="display: none;">
                        <label for="qa_category_id" class="required-label">QA Category</label>
                        <select name="qa_category_id" class="form-control input-default" id="qa_category_id">
                            <option value="">Select a QA category</option>
                            <option value="1">Audit Checklists</option>
                            <option value="2">Audit Reports</option>
                            <option value="3">Audit Plans</option>
                        </select>
                    </div>
                    
                    <!-- Branch Dropdown -->
                    <div class="form-group" id="branch_container">
                        <label for="branch_id">Branch</label>
                        <select name="branch_id" class="form-control input-default" id="branch_id">
                            <option value="">Select a branch</option>
                            <option value="1">Aeronautical Engineering</option>
                            <option value="2">Air Operations</option>
                            <option value="3">Construction Engineering</option>
                            <option value="4">Electronic Engineering</option>
                            <option value="5">General Engineering</option>
                            <option value="6">Ground Operations</option>
                            <option value="7">Productivity Management</option>
                            <option value="8">Training</option>
                        </select>
                    </div>

                    <!-- Dynamic Fields Section -->
                    <div id="dynamic_fields">
                        <!-- Fields will be inserted here based on category selection -->
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
        include "template/footer.php";
        ?>



    </div>


    <?php
    include "template/foot.php";
    ?>

    <!-- Bootstrap & jQuery -->
    <script src="../node_modules/jquery/jquery.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleDynamicFields() {
            const serviceCategory = document.getElementById('service_category_id');
            const qaCategoryContainer = document.getElementById('qa_category_container');
            const qaCategorySelect = document.getElementById('qa_category_id');
            const branchContainer = document.getElementById('branch_container');
            const branchSelect = document.getElementById('branch_id');
            const dynamicFields = document.getElementById('dynamic_fields');
            
            // Show QA category dropdown only if "Quality Assurance Audits" is selected (ID = 1)
            if (serviceCategory.value == '1') {
                qaCategoryContainer.style.display = 'block';
                qaCategorySelect.setAttribute('required', 'required');
            } else {
                qaCategoryContainer.style.display = 'none';
                qaCategorySelect.removeAttribute('required');
                qaCategorySelect.value = ''; // Clear selection
            }
            
            // Show branch dropdown only for specific categories
            if (serviceCategory.value == '1' || serviceCategory.value == '3') {
                branchContainer.style.display = 'block';
                branchSelect.setAttribute('required', 'required');
            } else {
                branchContainer.style.display = 'none';
                branchSelect.removeAttribute('required');
                branchSelect.value = ''; // Clear selection
            }
            
            // Clear previous dynamic fields
            dynamicFields.innerHTML = '';
            
            // Add fields based on selected category
            if (serviceCategory.value == '2') { // Aircraft Competency
                dynamicFields.innerHTML = `
                    <div class="dynamic-section">
                        <h5 class="section-title"><i class="fas fa-plane me-2"></i>Aircraft Competency Details</h5>
                        <div class="form-group">
                            <label for="svc_no" class="required-label">SVC No</label>
                            <input type="text" class="form-control input-default" id="svc_no" name="svc_no" required>
                        </div>
                        <div class="form-group">
                            <label for="rank" class="required-label">Rank</label>
                            <input type="text" class="form-control input-default" id="rank" name="rank" required>
                        </div>
                        <div class="form-group">
                            <label for="name" class="required-label">Name</label>
                            <input type="text" class="form-control input-default" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="aircraft_type" class="required-label">Aircraft Type</label>
                            <input type="text" class="form-control input-default" id="aircraft_type" name="aircraft_type" required>
                        </div>
                        <div class="form-group">
                            <label for="last_competency" class="required-label">Last Level of Competency</label>
                            <input type="text" class="form-control input-default" id="last_competency" name="last_competency" required>
                        </div>
                        <div class="form-group">
                            <label for="renewal_date" class="required-label">Renewal Date</label>
                            <input type="date" class="form-control input-default" id="renewal_date" name="renewal_date" required>
                        </div>
                        <div class="form-group">
                            <label for="currency" class="required-label">Currency</label>
                            <input type="text" class="form-control input-default" id="currency" name="currency" required>
                        </div>
                        <div class="form-group">
                            <label for="squadron" class="required-label">Squadron</label>
                            <input type="text" class="form-control input-default" id="squadron" name="squadron" required>
                        </div>
                    </div>
                `;
            } else if (serviceCategory.value == '3') { // Latitudes & Extensions
                dynamicFields.innerHTML = `
                    <div class="dynamic-section">
                        <h5 class="section-title"><i class="fas fa-expand-alt me-2"></i>Latitude & Extension Details</h5>
                        <div class="form-group">
                            <label for="latitude_description" class="required-label">Latitude Description</label>
                            <textarea class="form-control input-rounded" id="latitude_description" name="latitude_description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="related_aircraft" class="required-label">Related Aircraft</label>
                            <input type="text" class="form-control input-default" id="related_aircraft" name="related_aircraft" required>
                        </div>
                        <div class="form-group">
                            <label for="latitude_period" class="required-label">Latitude Period</label>
                            <input type="text" class="form-control input-default" id="latitude_period" name="latitude_period" placeholder="e.g., 6 months" required>
                        </div>
                    </div>
                `;
            } else if (serviceCategory.value == '5') { // Vehicle Emission Test
                dynamicFields.innerHTML = `
                    <div class="dynamic-section">
                        <h5 class="section-title"><i class="fas fa-car me-2"></i>Vehicle Emission Test Details</h5>
                        <div class="form-group">
                            <label for="vehicle_no" class="required-label">Vehicle Number</label>
                            <input type="text" class="form-control input-default" id="vehicle_no" name="vehicle_no" required>
                        </div>
                        <div class="form-group">
                            <label for="test_date" class="required-label">Test Performed Date</label>
                            <input type="date" class="form-control input-default" id="test_date" name="test_date" required>
                        </div>
                        <div class="form-group">
                            <label for="state" class="required-label">State</label>
                            <select class="form-control input-default" id="state" name="state" required>
                                <option value="">Select State</option>
                                <option value="Pass">Pass</option>
                                <option value="Fail">Fail</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <textarea class="form-control input-rounded" id="remarks" name="remarks" rows="3"></textarea>
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