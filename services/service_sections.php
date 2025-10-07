<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../template/head.php';
require_once "../includes/config.php";

// Get parameters from URL
$service_type = isset($_GET['type']) ? $_GET['type'] : 'qa_audits';
$service_category = isset($_GET['category']) ? $_GET['category'] : 'checklist';
$section = isset($_GET['section']) ? $_GET['section'] : 'AE';

// Service type to category mapping
$service_structure = [
    'qa_audits' => [
        'name' => 'Quality Assurance Audits',
        'categories' => [
            'checklist' => ['name' => 'Check List'],
            'auditreport' => ['name' => 'Audit Report'],
            'feedback' => ['name' => 'Feedback Report']
        ]
    ],
    'competency' => [
        'name' => 'Aircraft Competency',
        'categories' => [
            'checklist' => ['name' => 'Check List'],
            'auditreport' => ['name' => 'Audit Report'],
            'feedback' => ['name' => 'Feedback Report']
        ]
    ],
    'latitude' => [
        'name' => 'Latitudes and Extensions',
        'categories' => [
            'latitude' => ['name' => 'Latitude']
        ]
    ],
    'rdproject' => [
        'name' => 'Modifications / R & D Projects',
        'categories' => [
            'rdproject' => ['name' => 'R&D Project Proposal']
        ]
    ],
    'vehicle_emission' => [
        'name' => 'Vehicle Emission Test',
        'categories' => [
            'emission' => ['name' => 'Emission Test']
        ]
    ]
];

// Section mapping
$sections = [
    'AE' => ['name' => 'AE', 'id' => 6],
    'AO' => ['name' => 'AO', 'id' => 7],
    'AA' => ['name' => 'AA', 'id' => 8],
    'GE' => ['name' => 'GE', 'id' => 9],
    'EE' => ['name' => 'EE', 'id' => 10],
    'BE' => ['name' => 'BE', 'id' => 11],
    'CE' => ['name' => 'CE', 'id' => 12]
];

// Service type to category ID mapping
$category_ids = [
    'qa_audits' => [
        'checklist' => 16,
        'auditreport' => 17,
        'feedback' => 18
    ],
    'competency' => [
        'checklist' => 19,
        'auditreport' => 20,
        'feedback' => 21
    ],
    'latitude' => [
        'latitude' => 22
    ],
    'rdproject' => [
        'rdproject' => 23
    ],
    'vehicle_emission' => [
        'emission' => 24
    ]
];

// Redirect if invalid parameters
if (!array_key_exists($service_type, $service_structure) || 
    !array_key_exists($service_category, $service_structure[$service_type]['categories']) ||
    !array_key_exists($section, $sections)) {
    header("Location: services.php");
    exit();
}

// Get category ID and branch ID
$category_id = $category_ids[$service_type][$service_category];
$branch_id = $sections[$section]['id'];

// Fetch documents
$stmt = $db->prepare("SELECT d.title, d.description, d.uploaded_at, d.file_path 
    FROM documents d
    WHERE d.category_id = ? AND d.is_active = 1 AND d.branch_id = ?
    ORDER BY d.uploaded_at DESC");
$stmt->bind_param("ii", $category_id, $branch_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - <?php echo $service_structure[$service_type]['name']; ?> - <?php echo $service_structure[$service_type]['categories'][$service_category]['name']; ?> - <?php echo $section; ?></title>
    <link rel="stylesheet" href="../assets/css/audit_card.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .breadcrumb {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }
        .document-table {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .table thead th {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../template/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container my-5 pt-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="services.php">Services</a></li>
                <li class="breadcrumb-item"><a href="service_categories.php?type=<?php echo $service_type; ?>"><?php echo $service_structure[$service_type]['name']; ?></a></li>
                <li class="breadcrumb-item"><a href="service_sections.php?type=<?php echo $service_type; ?>&category=<?php echo $service_category; ?>"><?php echo $service_structure[$service_type]['categories'][$service_category]['name']; ?></a></li>
                <li class="breadcrumb-item active"><?php echo $section; ?></li>
            </ol>
        </nav>
        
        <div class="page-header mb-4">
            <h3 class="text-primary">
                <i class="fa fa-file me-2"></i> 
                <?php echo $service_structure[$service_type]['name']; ?> - 
                <?php echo $service_structure[$service_type]['categories'][$service_category]['name']; ?> - 
                <?php echo $section; ?>
                <div class="float-end">
                    <a href="service_sections.php?type=<?php echo $service_type; ?>&category=<?php echo $service_category; ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Sections
                    </a>
                </div>
            </h3>
        </div>
        
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover document-table">
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
                                         class="btn btn-primary btn-sm">
                                         <i class="fa fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No documents found for this section.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php
    include '../template/footer.php';
    include '../template/foot.php';
    ?>
</body>
</html>