<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../template/head.php';
require_once "../includes/config.php";

// Section mapping (update branch_id values as per your DB)
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

// Get selected section from URL, default AE
$selected = isset($_GET['section']) ? $_GET['section'] : 'Aeronautical Engineering';
$branch_id = $sections[$selected] ?? 1;

// Fetch feedback documents for selected section
$stmt = $db->prepare("SELECT d.id, d.title, d.description, d.uploaded_at, d.file_path 
    FROM training_documents d
    WHERE d.training_category_id = 4 AND d.is_active = 1 AND d.branch_id = ?
    ORDER BY d.uploaded_at DESC");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Documents -Outside Training</title>
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../template/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container my-5 pt-5">
        <div class="page-header mb-4">
            <h3 class="colour-defult">Outside Training<i class="fa fa-tasks"></i>
                <div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </h3>
        </div>
        
        <div class="row">

            <!-- Right Side Content -->
        <div class="row">
            
            <div class="col-lg-12">
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
                        <?php if (count($documents) > 0): ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['title']) ?></td>
                                    <td><?= htmlspecialchars($doc['description']) ?></td>
                                    <td><?= date('M j, Y', strtotime($doc['uploaded_at'])) ?></td>
                                    <td>
                                        <a href="../view_document.php?file=<?= urlencode($doc['file_path']) ?>" 
                                           class="btn btn-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No documents found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <hr>
    </main>
    
    <?php
    include '../template/foot.php';
    ?>
</body>
</html>