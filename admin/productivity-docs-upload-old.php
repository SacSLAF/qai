<!DOCTYPE html>
<html lang="en">

<?php
include "template/head.php";
require_once '../includes/config.php';

$productivity_categories = $db->query("SELECT id, name FROM productivity_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$osh_categories = $db->query("SELECT id, name FROM osh_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$environment_categories = $db->query("SELECT id, name FROM environment_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
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
                    <?php
                    //include "section/mainbox.php";
                    ?>

                    <form method="post" enctype="multipart/form-data" action="action/productivity-process.php">
                        <!-- Title -->

                        <div class="form-group">
                            <label for="title">Title*</label>
                            <input type="text" class="form-control input-default" id="title" name="title" required
                                value="">
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control input-rounded" id="description" name="description" rows="3"></textarea>
                        </div>
                        <input type="hidden" id="main" name="main" value="productivity">
                        <!-- Category Dropdown -->
                        <div class="form-group">
                            <label for="category_id">Productivity Category*</label>
                            <select name="category_id" class="form-control input-default" id="category_id" required onchange="toggleSubCategories()">
                                <option value="" selected disabled>Select a category</option>
                                <?php foreach ($productivity_categories as $pro_cat): ?>
                                    <option value="<?= $pro_cat['id'] ?>" <?=
                                                                            (isset($_POST['category_id']) && $_POST['category_id'] == $pro_cat['id']) ? 'selected' : ''
                                                                            ?>>
                                        <?= htmlspecialchars($pro_cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- QCC Category Dropdown (Initially Hidden) -->
                        <div class="form-group" id="qcc_category_container" style="display: none;">
                            <label for="qcc_category_id">QCC Category*</label>
                            <select name="qcc_category_id" class="form-control input-default" id="qcc_category_id">
                                <option value="" selected disabled>Select an option</option>
                                <option value="reg-form">QCC Registration Form</option>
                                <option value="registrations">Active QCC Registrations</option>
                            </select>
                        </div>

                        <!-- OSH Category Dropdown (Initially Hidden) -->
                        <div class="form-group" id="osh_category_container" style="display: none;">
                            <label for="osh_category_id">OSH Category*</label>
                            <select name="osh_category_id" class="form-control input-default" id="osh_category_id">
                                <option value="">Select an OSH category</option>
                                <?php foreach ($osh_categories as $osh_cat): ?>
                                    <option value="<?= $osh_cat['id'] ?>">
                                        <?= htmlspecialchars($osh_cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Environment Category Dropdown (Initially Hidden) -->
                        <div class="form-group" id="environment_category_container" style="display: none;">
                            <label for="environment_category_id">Environment Category*</label>
                            <select name="environment_category_id" class="form-control input-default" id="environment_category_id">
                                <option value="">Select an Environment category</option>
                                <?php foreach ($environment_categories as $env_cat): ?>
                                    <option value="<?= $env_cat['id'] ?>">
                                        <?= htmlspecialchars($env_cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Branch Dropdown (Initially Hidden) -->
                        <div class="form-group" id="branch_container" style="display: none;">
                            <label for="branch_id">Branch</label>
                            <select name="branch_id" class="form-control input-default" id="branch_id">
                                <option value="">Select a branch</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= $branch['id'] ?>" <?=
                                                                            (isset($_POST['branch_id']) && $_POST['branch_id'] == $branch['id']) ? 'selected' : ''
                                                                            ?>>
                                        <?= htmlspecialchars($branch['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Document File Upload -->
                        <div class="form-group" id="document_container">
                            <label for="document">Document File*</label>
                            <input type="file" class="form-control input-default" id="document" name="document"
                                accept=".pdf,.docx,.xlsx">
                            <small class="text-muted">
                                Max file size: MB. Allowed types: PDF, DOCX, XLSX
                            </small>
                        </div>

                        <!-- Checkbox Row (Example Usage) -->
                        <div class="row">
                            <div class="col-xl-4 col-xxl-6 col-6">
                                <!-- <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="confirmUpload" required>
                                    <label class="form-check-label" for="confirmUpload">Confirm Upload</label>
                                </div> -->
                            </div>
                            <!-- You can add more checkboxes here if needed -->
                        </div>
                        <br>

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

    <script>
        function toggleSubCategories() {
            const productivityCategory = document.getElementById('category_id');
            const oshContainer = document.getElementById('osh_category_container');
            const qccContainer = document.getElementById('qcc_category_container');
            const environmentContainer = document.getElementById('environment_category_container');
            const branchContainer = document.getElementById('branch_container');
            const oshSelect = document.getElementById('osh_category_id');
            const qccSelect = document.getElementById('qcc_category_id');
            const docUpload = document.getElementById('document_container');
            const environmentSelect = document.getElementById('environment_category_id');
            const branchSelect = document.getElementById('branch_id');


            // Hide all containers first
            oshContainer.style.display = 'none';
            qccContainer.style.display = 'none';
            environmentContainer.style.display = 'none';
            branchContainer.style.display = 'none';
            oshSelect.removeAttribute('required');
            environmentSelect.removeAttribute('required');
            branchSelect.removeAttribute('required');
            oshSelect.value = '';
            environmentSelect.value = '';
            branchSelect.value = '';

            // Show appropriate containers based on selection
            if (productivityCategory.value == '1') { // Occupational Health & Safety
                oshContainer.style.display = 'block';
                branchContainer.style.display = 'block';
                oshSelect.setAttribute('required', 'required');
                branchSelect.setAttribute('required', 'required');
            } else if (productivityCategory.value == '2') { // Environment
                environmentContainer.style.display = 'block';
                branchContainer.style.display = 'block';
                environmentSelect.setAttribute('required', 'required');
                branchSelect.setAttribute('required', 'required');
            } else if (productivityCategory.value == '3') { // Quality Control Circle
                qccContainer.style.display = 'block';
                qccContainer.querySelector('select').setAttribute('required', 'required');
                if (qccSelect.value === 'reg-form') {
                    docUpload.setAttribute('required', 'required');
                } else if (qccSelect.value === 'registrations') {
                    docUpload.removeAttribute('required');
                }
            }

        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleSubCategories();
        });
    </script>

</body>

</html>