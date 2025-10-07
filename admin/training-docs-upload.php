<!DOCTYPE html>
<html lang="en">

<?php
include "template/head.php";
require_once '../includes/config.php';

$training_categories = $db->query("SELECT id, name FROM training_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
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

                    <form method="post" enctype="multipart/form-data" action="action/training-process.php">
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
     <input type="hidden" id="main" name="main" value="services">
                        <!-- Category Dropdown -->
                        <div class="form-group">
                            <label for="category_id">Category*</label>
                            <select name="category_id" class="form-control input-default" id="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($training_categories as $t_cat): ?>
                                    <option value="<?= $t_cat['id'] ?>" <?=
                                                                        (isset($_POST['category_id']) && $_POST['category_id'] == $t_cat['id']) ? 'selected' : ''
                                                                        ?>>
                                        <?= htmlspecialchars($t_cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>
                                <!-- Category Dropdown -->
                        <div class="form-group">
                            <label for="branch_id">Branch</label>
                            <select name="branch_id" class="form-control input-default" id="branch_id" required>
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
                        <div class="form-group">
                            <label for="document">Document File*</label>
                            <input type="file" class="form-control input-default" id="document" name="document"
                                accept=".pdf,.docx,.xlsx" required>
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


</body>

</html>