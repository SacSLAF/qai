<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../template/head.php';
require_once "../includes/config.php";

// Service type to category mapping
$serviceCategories = [
    'checklist' => 16,    // Update with your actual category IDs
    'auditreport' => 17,
    'feedback' => 18,
    'rdproject' => 19,
    'latitude' => 20
];

// Section mapping
$sections = [
    'AE' => 6,
    'AO' => 7,
    'AA' => 8,
    'GE' => 9,
    'EE' => 10,
    'BE' => 11,
    'CE' => 12
];

// Get selected service and section from URL
$serviceType = isset($_GET['service']) ? $_GET['service'] : 'checklist';
$selectedSection = isset($_GET['section']) ? $_GET['section'] : 'AE';

$category_id = $serviceCategories[$serviceType] ?? 16;
$branch_id = $sections[$selectedSection] ?? 6;

// Service names for display
$serviceNames = [
    'checklist' => 'Check List',
    'auditreport' => 'Audit Report',
    'feedback' => 'Feedback Report',
    'rdproject' => 'R&D Project Proposal',
    'latitude' => 'Latitude'
];

// Fetch documents for selected service and section
$stmt = $db->prepare("SELECT d.title, d.description, d.uploaded_at, d.file_path 
    FROM documents d
    WHERE d.category_id = ? AND d.is_active = 1 AND d.branch_id = ?
    ORDER BY d.uploaded_at DESC");
$stmt->bind_param("ii", $category_id, $branch_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<body>
<?php include '../template/header.php'; ?>

<main class="container my-5 pt-5">
    <div class="page-header mb-4">
        <h3 class="colour-defult">
            <?php echo $serviceNames[$serviceType] . ' - ' . $selectedSection; ?>
            <div class="float-end">
                <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                <a href="services.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Services</a>
            </div>
        </h3>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Uploaded Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                                <td>
                                    <a href="../view_document.php?file=<?= urlencode($row['file_path']) ?>" 
                                     class="btn btn-primary">
                                     View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No documents found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <hr>
</main>
<?php
include '../template/footer.php';
include '../template/foot.php';
?>
</body>
</html>