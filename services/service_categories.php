<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../template/head.php';
require_once "../includes/config.php";

// Get service type from URL
$service_type = isset($_GET['type']) ? $_GET['type'] : 'qa_audits';

// Service type to category mapping
$service_structure = [
    'qa_audits' => [
        'name' => 'Quality Assurance Audits',
        'categories' => [
            'checklist' => ['name' => 'Check List', 'icon' => 'list-check', 'color' => 'primary'],
            'auditreport' => ['name' => 'Audit Report', 'icon' => 'clipboard-data', 'color' => 'success'],
            'feedback' => ['name' => 'Feedback Report', 'icon' => 'chat-left-text', 'color' => 'info']
        ]
    ],
    'competency' => [
        'name' => 'Aircraft Competency',
        'categories' => [
            'checklist' => ['name' => 'Check List', 'icon' => 'list-check', 'color' => 'primary'],
            'auditreport' => ['name' => 'Audit Report', 'icon' => 'clipboard-data', 'color' => 'success'],
            'feedback' => ['name' => 'Feedback Report', 'icon' => 'chat-left-text', 'color' => 'info']
        ]
    ],
    'latitude' => [
        'name' => 'Latitudes and Extensions',
        'categories' => [
            'latitude' => ['name' => 'Latitude', 'icon' => 'clock', 'color' => 'warning']
        ]
    ],
    'rdproject' => [
        'name' => 'Modifications / R & D Projects',
        'categories' => [
            'rdproject' => ['name' => 'R&D Project Proposal', 'icon' => 'tools', 'color' => 'danger']
        ]
    ],
    'vehicle_emission' => [
        'name' => 'Vehicle Emission Test',
        'categories' => [
            'emission' => ['name' => 'Emission Test', 'icon' => 'truck', 'color' => 'secondary']
        ]
    ]
];

// Redirect if invalid service type
if (!array_key_exists($service_type, $service_structure)) {
    header("Location: services.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $service_structure[$service_type]['name']; ?> - Categories</title>
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
        .category-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .category-icon {
            font-size: 2rem;
            margin-bottom: 15px;
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
                <li class="breadcrumb-item active"><?php echo $service_structure[$service_type]['name']; ?></li>
            </ol>
        </nav>
        
        <div class="page-header mb-4">
            <h3 class="text-primary">
                <i class="fa fa-folder me-2"></i> <?php echo $service_structure[$service_type]['name']; ?> - Categories
                <div class="float-end">
                    <a href="services.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Services</a>
                </div>
            </h3>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-2"></i> Select a category to view available sections.
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($service_structure[$service_type]['categories'] as $key => $category): ?>
            <div class="col-md-4 mb-4">
                <a href="service_sections.php?type=<?php echo $service_type; ?>&category=<?php echo $key; ?>" class="text-decoration-none">
                    <div class="card category-card border-<?php echo $category['color']; ?>">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-<?php echo $category['icon']; ?> category-icon text-<?php echo $category['color']; ?>"></i>
                            <h4 class="card-title text-dark"><?php echo $category['name']; ?></h4>
                            <p class="text-muted">Click to view sections</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php
    include '../template/footer.php';
    include '../template/foot.php';
    ?>
</body>
</html>