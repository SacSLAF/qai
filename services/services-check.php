<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../template/head.php';
require_once "../includes/config.php";

// Section mapping
$sections = [
    'Aeronautical Engineering' => 1,
    'Air Operations' => 2, 
    'Construction Engineering' => 3,
    'Electronic Engineering' => 4, 
    'General Engineering' => 5, 
    'Ground Operations' => 6, 
    'Productivity Management' => 7,
    'Training' => 8 
];

// Get selected parameters from URL
$selected_section = isset($_GET['section']) ? $_GET['section'] : 'Aeronautical Engineering';
$service_category_id = isset($_GET['service_category']) ? intval($_GET['service_category']) : 1;
$qa_category_id = isset($_GET['qa_category']) ? intval($_GET['qa_category']) : null;
$sub_category_id = isset($_GET['sub_category']) ? intval($_GET['sub_category']) : null;
$branch_id = $sections[$selected_section] ?? 1;
$camp_id = 1; // Default campus ID

// Check if we need to show sub-category dropdown
$show_sub_category = ($qa_category_id == 2 && $selected_section == 'Aeronautical Engineering');

// Sample sub-categories for Aeronautical Engineering Audit Plans
$camp_id = [
    1 => 'RMA',
    2 => 'AFHQSJP',
    3 => 'CBO',
    4 => 'AHP',
    5 => 'WLA',
    6 => 'HGR',
    7 => 'KTK',
    8 => 'KAT',
    9 => 'HIN',
    10 => 'VNA',
    11 => 'Camp 11',
    12 => 'Camp 12',
    13 => 'Camp 13',
    14 => 'Camp 14',
    15 => 'Camp 15',
    16 => 'Camp 16',
    17 => 'Camp 17',
    18 => 'Camp 18',
    19 => 'Camp 19',
    20 => 'Camp 20',
    21 => 'Camp 21',
    22 => 'Camp 22',
    23 => 'Camp 23',
    24 => 'Camp 24',
];

$elec_cat = [
    1 => 'Electronic Section in Academy / Base / Station',
    2 => 'Fight Squadrons',
    3 => 'Air Defence Radar Squadron',
    4 => 'No.01 E & TE Wing SLAF Base KAT',
    5 => 'No.02 E & TE Wing SLAF Base RMA',
    6 => 'No.01 IT Wing SLAF Base RMA',
    7 => 'No.02 IT Wing SLAF Base EKALA',
    8 => 'RMW SLAF Base KAT',
];

$electronic_sections =[
    1 => 'SLAF Academy CBY',
    2 => 'SLAF Base ANU',
    3 => 'SLAF Base HIN',
    4 => 'SLAF Base VNA',
    5 => 'SLAF Stn CHO',
    6 => 'SLAF Stn WLA',
    7 => 'SLAF Stn KGL',
    8 => 'SLAF Stn KTK',
    9 => 'SLAF Stn PLV',
    10 => 'SLAF Stn PLY',
    11 => 'SLAF Stn IRM',
    12 => 'SLAF Stn BCL',
    13 => 'SLAF Stn PGl',
    14 => 'SLAF Stn DIA',
    15 => 'SLAF PTS AMP',
    16 => 'SLAF PTS MIR',
];

// Sample sub-categories for Aeronautical Engineering Audit Plans
$sub_categories = [
    1 => '4 SQN',
    2 => '8 SQN',
    3 => '61 FLIGHT',
    4 => 'HELITOURS',
    5 => 'Category 5',
    6 => 'Category 6',
    7 => 'Category 7',
    8 => 'Category 8',
    9 => 'Category 9',
    10 => 'Category 10',
    11 => 'Category 11',
    12 => 'Category 12',
    13 => 'Category 13',
    14 => 'Category 14',
    15 => 'Category 15'
];

// Fetch service categories
$service_categories = [];
$service_result = $db->query("SELECT id, name FROM service_categories WHERE id = 1");
while ($row = $service_result->fetch_assoc()) {
    $service_categories[$row['id']] = $row['name'];
}

// Fetch QA categories
$qa_categories = [];
$qa_result = $db->query("SELECT id, name FROM qa_categories ORDER BY id");
while ($row = $qa_result->fetch_assoc()) {
    $qa_categories[$row['id']] = $row['name'];
}

// Build query for documents
$query = "SELECT d.title, d.description, d.uploaded_at, d.file_path, 
                 b.name as branch_name, qa.name as qa_category_name
          FROM service_documents d
          LEFT JOIN branches b ON d.branch_id = b.id
          LEFT JOIN qa_categories qa ON d.qa_category_id = qa.id
          WHERE d.service_category_id = ? AND d.is_active = 1";

$params = [$service_category_id];
$param_types = "i";

// Add QA category filter if selected
if (!empty($qa_category_id)) {
    $query .= " AND d.qa_category_id = ?";
    $params[] = $qa_category_id;
    $param_types .= "i";
}

// Add branch filter
$query .= " AND d.branch_id = ?";
$params[] = $branch_id;
$param_types .= "i";

// Add sub-category filter if selected and applicable
if ($show_sub_category && !empty($sub_category_id)) {
    // Assuming you have a sub_category_id field in your database
    // If not, you'll need to modify your database structure
    $query .= " AND d.sub_category_id = ?";
    $params[] = $sub_category_id;
    $param_types .= "i";
}

$query .= " ORDER BY d.uploaded_at DESC";

// Prepare and execute query
$stmt = $db->prepare($query);
if ($stmt) {
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Error preparing statement: " . $db->error);
}
?>

<body>
<?php include '../template/header.php'; ?>

<main class="container my-5 pt-5">
    <div class="page-header mb-4">
        <h3 class="colour-defult">Quality Assurance Audits<i class="fa fa-tasks"></i>
            <div class="float-end">
                <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
        </h3>
    </div>

    <div class="row">
        <!-- Left Side Filters -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <form method="get">
                        <!-- QA Category
                        <div class="mb-3">
                            <label for="qa_category" class="form-label">QA Category</label>
                            <select class="form-select" id="qa_category" name="qa_category" onchange="this.form.submit()">
                                <option value="">All QA Categories</option>
                                <?php foreach ($qa_categories as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $qa_category_id == $id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div> -->

                        <!-- Branch Selection -->
                        <div class="mb-3">
                            <label for="section" class="form-label">Branch</label>
                            <select class="form-select" id="section" name="section" onchange="this.form.submit()">
                                <?php foreach ($sections as $name => $id): ?>
                                    <option value="<?= htmlspecialchars($name) ?>" <?= $selected_section == $name ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Camp Selection -->
                        <div class="mb-3">
                            <label for="section" class="form-label">Electronic Engineering</label>
                            <select class="form-select" id="section" name="section" onchange="this.form.submit()">
                                <?php foreach ($elec_cat as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $$elec_cat == $id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Camp Selection -->
                        <div class="mb-3">
                            <label for="section" class="form-label">Electronic Sections</label>
                            <select class="form-select" id="section" name="section" onchange="this.form.submit()">
                                <?php foreach ($electronic_sections as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $camp_id == $id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        


                        <!-- Sub Category (Only show for Aeronautical Engineering + Audit Plans) -->
                        <?php if ($show_sub_category): ?>
                        <div class="mb-3">
                            <label for="sub_category" class="form-label">Plan Type</label>
                            <select class="form-select" id="sub_category" name="sub_category" onchange="this.form.submit()">
                                <option value="">All Plan Types</option>
                                <?php foreach ($sub_categories as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $sub_category_id == $id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side Content -->
        <div class="col-lg-9 col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?= htmlspecialchars($service_categories[$service_category_id] ?? 'Documents') ?>
                        <?php if ($qa_category_id): ?> - <?= htmlspecialchars($qa_categories[$qa_category_id] ?? '') ?><?php endif; ?>
                        <?php if ($show_sub_category && $sub_category_id): ?> - <?= htmlspecialchars($sub_categories[$sub_category_id] ?? '') ?><?php endif; ?>
                    </h5>
                    <?php if ($result->num_rows > 0): ?>
                        <span class="badge bg-primary"><?= $result->num_rows ?> document(s) found</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th class="text-center">Title</th>
                                        <th class="text-center">Description</th>
                                        <th class="text-center">QA Category</th>
                                        <th class="text-center">Branch</th>
                                        <?php if ($show_sub_category): ?>
                                        <th class="text-center">Plan Type</th>
                                        <?php endif; ?>
                                        <th class="text-center">Uploaded Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-start"><?= htmlspecialchars($row['title']) ?></td>
                                            <td class="text-start"><?= htmlspecialchars($row['description']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['qa_category_name']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['branch_name']) ?></td>
                                            <?php if ($show_sub_category): ?>
                                            <td class="text-center">
                                                <?php 
                                                // You would need to store and retrieve sub-category information
                                                // This is a placeholder - you'll need to modify your database
                                                if (isset($row['sub_category_id']) && isset($sub_categories[$row['sub_category_id']])) {
                                                    echo htmlspecialchars($sub_categories[$row['sub_category_id']]);
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                            <?php endif; ?>
                                            <td class="text-center"><?= date('M j, Y', strtotime($row['uploaded_at'])) ?></td>
                                            <td class="text-center">
                                                <a href="../view_document.php?file=<?= urlencode($row['file_path']) ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa fa-file-excel-o fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No documents found</h5>
                            <p class="text-muted">Try adjusting your filters or check back later</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include '../template/foot.php';
?>
</body>
</html>