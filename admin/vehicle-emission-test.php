<?php
// vehicle-emission-test.php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all vehicle emission test records
$records = [];
$res = $db->query("SELECT v.*, s.name as camp_name 
                   FROM vehicle_emission_test v 
                   LEFT JOIN slaf_establishments s ON v.camp_id = s.id 
                   ORDER BY v.fuel_type, v.test_date DESC, v.serial_no ASC");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching vehicle emission test records: ' . $db->error);
    $records = [];
}

include "template/head.php";
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <?php include "template/preloader.php"; ?>

    <div id="main-wrapper">
        <?php include "template/nav.php"; include "template/header.php"; ?>
        <?php include "template/desnav.php"; ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">VEHICLE EMISSION TEST RECORDS - 2024</h4>
                                <div>
                                    <a href="vehicle-emission-test-form.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus"></i> Add New Record
                                    </a>
                                </div>
                            </div>

                            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-primary mx-5">
                                    Record saved successfully!
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger mx-5">
                                    Error occurred while processing your request.
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <!-- Fuel Type Tabs -->
                                <ul class="nav nav-tabs" id="fuelTypeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="diesel-tab" data-bs-toggle="tab" data-bs-target="#diesel" type="button" role="tab">
                                            <i class="fas fa-oil-can me-1"></i> Diesel Vehicles
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="petrol-tab" data-bs-toggle="tab" data-bs-target="#petrol" type="button" role="tab">
                                            <i class="fas fa-gas-pump me-1"></i> Petrol Vehicles
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="fuelTypeTabsContent">
                                    <!-- Diesel Vehicles Tab -->
                                    <div class="tab-pane fade show active" id="diesel" role="tabpanel">
                                        <div class="table-responsive mt-3">
                                            <table id="dieselTable" class="display table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>S/No</th>
                                                        <th>Camp</th>
                                                        <th>Vehicle No</th>
                                                        <th>Vehicle Type</th>
                                                        <th>Model</th>
                                                        <th>Date</th>
                                                        <th>Test Values</th>
                                                        <th>Status</th>
                                                        <th>Next Due Date</th>
                                                        <th>Remarks</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $diesel_records = array_filter($records, function($r) {
                                                        return $r['fuel_type'] === 'Diesel';
                                                    });
                                                    ?>
                                                    <?php if (empty($diesel_records)): ?>
                                                        <tr>
                                                            <td colspan="11" class="text-center py-4">No diesel vehicle records found</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($diesel_records as $r): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($r['serial_no'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($r['camp_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($r['vehicle_no'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($r['vehicle_type'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($r['model'] ?? 'N/A') ?></td>
                                                                <td><?= $r['test_date'] ? htmlspecialchars($r['test_date']) : 'N/A' ?></td>
                                                                <td>
                                                                    <?php if ($r['first_test']): ?>
                                                                        <small>1st: <?= $r['first_test'] ?></small><br>
                                                                    <?php endif; ?>
                                                                    <?php if ($r['second_test']): ?>
                                                                        <small>2nd: <?= $r['second_test'] ?></small><br>
                                                                    <?php endif; ?>
                                                                    <?php if ($r['third_test']): ?>
                                                                        <small>3rd: <?= $r['third_test'] ?></small><br>
                                                                    <?php endif; ?>
                                                                    <?php if ($r['average']): ?>
                                                                        <small><strong>Avg: <?= $r['average'] ?></strong></small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-<?= 
                                                                        $r['status'] == 'Pass' ? 'success' : 
                                                                        ($r['status'] == 'Fail' ? 'danger' : 
                                                                        ($r['status'] == 'Not Suitable' ? 'warning' : 'secondary')) 
                                                                    ?>">
                                                                        <?= htmlspecialchars($r['status'] ?? 'N/A') ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= $r['next_due_date'] ? htmlspecialchars($r['next_due_date']) : 'N/A' ?></td>
                                                                <td><?= htmlspecialchars($r['remarks'] ?? '') ?></td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <a href="edit-vehicle-emission-test.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                            <i class="fas fa-edit"></i> Edit
                                                                        </a>

                                                                        <form action="action/vehicle-emission-test-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                                            <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                                                                <i class="fas fa-trash"></i> Delete
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Petrol Vehicles Tab -->
                                    <div class="tab-pane fade" id="petrol" role="tabpanel">
                                        <div class="table-responsive mt-3">
                                            <table id="petrolTable" class="display table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>S/No</th>
                                                        <th>Camp</th>
                                                        <th>Vehicle No</th>
                                                        <th>Vehicle Type</th>
                                                        <th>Model</th>
                                                        <th>Date</th>
                                                        <th>Test Values</th>
                                                        <th>Status</th>
                                                        <th>Next Due Date</th>
                                                        <th>Remarks</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $petrol_records = array_filter($records, function($r) {
                                                        return $r['fuel_type'] === 'Petrol';
                                                    });
                                                    ?>
                                                    <?php if (empty($petrol_records)): ?>
                                                        <tr>
                                                            <td colspan="11" class="text-center py-4">No petrol vehicle records found</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($petrol_records as $r): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($r['serial_no'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($r['camp_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($r['vehicle_no'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($r['vehicle_type'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($r['model'] ?? 'N/A') ?></td>
                                                                <td><?= $r['test_date'] ? htmlspecialchars($r['test_date']) : 'N/A' ?></td>
                                                                <td>
                                                                    <?php if ($r['rpm_2500_hc']): ?>
                                                                        <small>2500 RPM HC: <?= $r['rpm_2500_hc'] ?></small><br>
                                                                    <?php endif; ?>
                                                                    <?php if ($r['rpm_2500_co']): ?>
                                                                        <small>2500 RPM CO: <?= $r['rpm_2500_co'] ?></small><br>
                                                                    <?php endif; ?>
                                                                    <?php if ($r['idle_hc']): ?>
                                                                        <small>Idle HC: <?= $r['idle_hc'] ?></small><br>
                                                                    <?php endif; ?>
                                                                    <?php if ($r['idle_co']): ?>
                                                                        <small>Idle CO: <?= $r['idle_co'] ?></small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-<?= 
                                                                        $r['status'] == 'Pass' ? 'success' : 
                                                                        ($r['status'] == 'Fail' ? 'danger' : 
                                                                        ($r['status'] == 'Not Suitable' ? 'warning' : 'secondary')) 
                                                                    ?>">
                                                                        <?= htmlspecialchars($r['status'] ?? 'N/A') ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= $r['next_due_date'] ? htmlspecialchars($r['next_due_date']) : 'N/A' ?></td>
                                                                <td><?= htmlspecialchars($r['remarks'] ?? '') ?></td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <a href="edit-vehicle-emission-test.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                            <i class="fas fa-edit"></i> Edit
                                                                        </a>

                                                                        <form action="action/vehicle-emission-test-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                                            <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                                                                <i class="fas fa-trash"></i> Delete
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developed by <a href="#" target="_blank">Directorate of Information Technology. Sri Lanka Air Force.</a> 2025</p>
            </div>
        </div>
    </div>

    <!-- Required scripts -->
    <script src="assets/vendor/global/global.min.js"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/datatables/responsive/responsive.js"></script>
    <script src="assets/js/plugins-init/datatables.init.js"></script>
    <script src="assets/js/custom.min.js"></script>
    <script src="assets/js/deznav-init.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize both tables
            $('#dieselTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "order": [[0, "asc"]]
            });

            $('#petrolTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "order": [[0, "asc"]]
            });
        });
    </script>
</body>
</html>