<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../template/head.php';
require_once "../includes/config.php";

// Fetch all productivity documents for category 4 (Awards)
$stmt = $db->prepare("SELECT d.id, d.title, d.description, d.uploaded_at, d.file_path 
    FROM productivity_documents d
    WHERE d.productivity_category_id = 3 AND d.is_active = 1
    ORDER BY d.uploaded_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCC Documents - QCC</title>
    <link rel="stylesheet" href="../assets/css/audit_card.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .breadcrumb {
            margin-top: 20px;
            background-color: #e2edfbff;
            padding: 8px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .breadcrumb a {
            color: #0d6efd;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include '../template/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container my-5 pt-5">

            <!-- Breadcrumb Navigation -->
                
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="productivity.php" >Productivity</a></li>
                <li class="breadcrumb-item active" aria-current="page">Quality Control Circle</li>
            </ol>
        </nav>
        <div class="page-header mb-4">
            <h3 class="colour-defult">Quality Control Circle<i class="fa fa-tasks"></i>
               <!-- <div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>-->
            </h3>
        </div>

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
    include '../template/footer.php';
    include '../template/foot.php';
    ?>
</body>
</html>