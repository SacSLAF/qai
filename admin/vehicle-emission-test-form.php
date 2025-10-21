<?php
// vehicle-emission-test-form.php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch SLAF establishments for camp dropdown
$camps = [];
$camps_result = $db->query("SELECT id, name, code FROM slaf_establishments WHERE is_active = 1 ORDER BY name");
if ($camps_result) {
    $camps = $camps_result->fetch_all(MYSQLI_ASSOC);
}

// Vehicle types including both diesel and petrol
$vehicle_types = [
    'Truck', 'D/Cab', 'Jeep', 'Van', 'S/Cab', 'Meal Run', 
    'W/ Bowser', 'Fire Vehicle', 'Coach', 'Fuel Bowser', 
    'Ambulance', 'Drum Truck', 'Gulley Bowser', 'Three Wheel', 'M/Cycle', 'Car'
];

// Status options
$status_options = [
    'Pass', 'Fail', 'Not Suitable', 'Serviceable Not Done'
];

// If editing, fetch existing record
$record = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM vehicle_emission_test WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();
}

include "template/head.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($record) ? 'Edit' : 'Add' ?> Vehicle Emission Test Record</title>
    <style>
        .test-section {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .test-section h6 {
            color: #495057;
            margin-bottom: 15px;
        }
        .fuel-type-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <?php include "template/preloader.php"; ?>
    <?php include "template/nav.php"; ?>
    <?php include "template/header.php"; ?>
    <?php include "template/desnav.php"; ?>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> Vehicle Emission Test Record</h4>
                            <a href="vehicle-emission-test.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>

                        <div class="card-body">
                            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong>Success!</strong> Record saved successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Error!</strong> <?= htmlspecialchars($_GET['error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form action="action/vehicle-emission-test-save.php" method="post" id="emissionForm">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                <?php endif; ?>

                                <div class="row">
                                    <!-- Basic Vehicle Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-car me-2"></i>Vehicle Details</h5>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label">S/No</label>
                                                    <input type="number" name="serial_no" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['serial_no']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Camp *</label>
                                                    <select name="camp_id" class="form-select" required>
                                                        <option value="">Select Camp</option>
                                                        <?php foreach ($camps as $camp): ?>
                                                            <option value="<?= $camp['id'] ?>" 
                                                                <?= isset($record) && $record['camp_id'] == $camp['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($camp['name']) ?> (<?= htmlspecialchars($camp['code']) ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Vehicle Number *</label>
                                                    <input type="text" name="vehicle_no" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['vehicle_no']) : '' ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label">Fuel Type *</label>
                                                    <select name="fuel_type" class="form-select" id="fuelType" required>
                                                        <option value="Diesel" <?= isset($record) && $record['fuel_type'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                                        <option value="Petrol" <?= isset($record) && $record['fuel_type'] == 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label">Vehicle Type *</label>
                                                    <select name="vehicle_type" class="form-select" required>
                                                        <option value="">Select Type</option>
                                                        <?php foreach ($vehicle_types as $type): ?>
                                                            <option value="<?= $type ?>" 
                                                                <?= isset($record) && $record['vehicle_type'] == $type ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($type) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Model</label>
                                                    <input type="text" name="model" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['model']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Date *</label>
                                                    <input type="date" name="test_date" class="form-control" 
                                                           value="<?= isset($record) ? $record['test_date'] : '' ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Diesel Test Results -->
                                    <div class="col-md-12 mb-4" id="dieselSection">
                                        <div class="test-section">
                                            <h6 class="text-primary">
                                                <i class="fas fa-oil-can me-2"></i>Diesel Vehicle Test Results
                                                <span class="badge bg-secondary fuel-type-badge ms-2">Diesel</span>
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">1st Test</label>
                                                        <input type="number" step="0.01" name="first_test" class="form-control test-input-diesel" 
                                                               value="<?= isset($record) ? $record['first_test'] : '' ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">2nd Test</label>
                                                        <input type="number" step="0.01" name="second_test" class="form-control test-input-diesel" 
                                                               value="<?= isset($record) ? $record['second_test'] : '' ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">3rd Test</label>
                                                        <input type="number" step="0.01" name="third_test" class="form-control test-input-diesel" 
                                                               value="<?= isset($record) ? $record['third_test'] : '' ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Average</label>
                                                        <input type="number" step="0.01" name="average" class="form-control" 
                                                               value="<?= isset($record) ? $record['average'] : '' ?>" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Petrol Test Results -->
                                    <div class="col-md-12 mb-4" id="petrolSection" style="display: none;">
                                        <div class="test-section">
                                            <h6 class="text-primary">
                                                <i class="fas fa-gas-pump me-2"></i>Petrol Vehicle Test Results
                                                <span class="badge bg-warning fuel-type-badge ms-2">Petrol</span>
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">2500 RPM HC</label>
                                                        <input type="number" step="0.01" name="rpm_2500_hc" class="form-control" 
                                                               value="<?= isset($record) ? $record['rpm_2500_hc'] : '' ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">2500 RPM CO</label>
                                                        <input type="number" step="0.01" name="rpm_2500_co" class="form-control" 
                                                               value="<?= isset($record) ? $record['rpm_2500_co'] : '' ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Idle HC</label>
                                                        <input type="number" step="0.01" name="idle_hc" class="form-control" 
                                                               value="<?= isset($record) ? $record['idle_hc'] : '' ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Idle CO</label>
                                                        <input type="number" step="0.01" name="idle_co" class="form-control" 
                                                               value="<?= isset($record) ? $record['idle_co'] : '' ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status and Additional Info -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Status & Additional Information</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Status *</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="">Select Status</option>
                                                        <?php foreach ($status_options as $status): ?>
                                                            <option value="<?= $status ?>" 
                                                                <?= isset($record) && $record['status'] == $status ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($status) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Next Due Date</label>
                                                    <input type="date" name="next_due_date" class="form-control" 
                                                           value="<?= isset($record) ? $record['next_due_date'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="3"><?= isset($record) ? htmlspecialchars($record['remarks']) : '' ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-md-12">
                                        <div class="text-end">
                                            <button type="reset" class="btn btn-secondary me-2">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Record
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
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

    <!-- Required scripts -->
    <script src="assets/vendor/global/global.min.js"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/js/custom.min.js"></script>
    <script src="assets/js/deznav-init.js"></script>

    <script>
        // Toggle between diesel and petrol test sections
        document.addEventListener('DOMContentLoaded', function() {
            const fuelTypeSelect = document.getElementById('fuelType');
            const dieselSection = document.getElementById('dieselSection');
            const petrolSection = document.getElementById('petrolSection');

            function toggleTestSections() {
                if (fuelTypeSelect.value === 'Diesel') {
                    dieselSection.style.display = 'block';
                    petrolSection.style.display = 'none';
                } else {
                    dieselSection.style.display = 'none';
                    petrolSection.style.display = 'block';
                }
            }

            // Initial toggle
            toggleTestSections();

            // Toggle on change
            fuelTypeSelect.addEventListener('change', toggleTestSections);

            // Calculate average for diesel tests
            const dieselTestInputs = document.querySelectorAll('.test-input-diesel');
            const averageInput = document.querySelector('input[name="average"]');

            function calculateAverage() {
                const values = Array.from(dieselTestInputs)
                    .map(input => parseFloat(input.value))
                    .filter(val => !isNaN(val) && val > 0);
                
                if (values.length > 0) {
                    const sum = values.reduce((a, b) => a + b, 0);
                    averageInput.value = (sum / values.length).toFixed(2);
                } else {
                    averageInput.value = '';
                }
            }

            dieselTestInputs.forEach(input => {
                input.addEventListener('input', calculateAverage);
            });
        });
    </script>
</body>
</html>