
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../template/head.php';
require_once "../includes/config.php";

// Section mapping (update branch_id values as per your DB)
$sections = [
    'AE' => 6,
    'AO' => 7,
    'AA' => 8,
    'GE' => 9,
    'EE' => 10,
    'BE' => 11,
    'CE' => 12
];

// Get selected section from URL, default AE
$selected = isset($_GET['section']) ? $_GET['section'] : 'AE';
$branch_id = $sections[$selected] ?? 6;

// Fetch feedback documents for selected section
$stmt = $db->prepare("SELECT d.title, d.description, d.uploaded_at, d.file_path 
    FROM documents d
    WHERE d.category_id = 9 AND d.is_active = 1 AND d.branch_id = ?
    ORDER BY d.uploaded_at DESC");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<body>
<?php include '../template/header.php'; ?>

<main class="container my-5 pt-5">
        <div class="page-header mb-4">
            <h3 class="colour-defult">Services <i class="fa fa-tasks"></i>
                <div class="float-end">
                    <a href="index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <!-- Nav Tabs -->
                <ul class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist" aria-orientation="vertical">
                    <li class="nav-item">

                        <a class="nav-link active" id="org-tab" data-bs-toggle="pill" href="#org" role="tab">R & D project Reports</a>
      </li>
                    
                </ul>
            </div>
 <div class="col-lg-10">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Title</th>
                            <th>description</th>
                            <!-- <th>Branch</th> -->
                            <!-- <th>Category</th> -->
                            <!-- <th>Uploaded By</th> -->
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
                                    <!-- <td><?= htmlspecialchars($row['branch_name']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['category']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['uploaded_by']) ?></td> -->
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
                                <td colspan="5">No documents found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <hr>
</main>
<?php
//include 'template/footer.php';
include '../template/foot.php';
?>
</body>
</html>