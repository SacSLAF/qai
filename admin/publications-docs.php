<!DOCTYPE html>
<html lang="en">

<?php
require_once '../includes/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fixed SQL query - added d.id and fixed the JOIN condition for branch_id
$documents = $db->query("
    SELECT d.id, d.title, d.uploaded_at, d.file_path,
           c.name AS category_name, 
           b.name AS branch_name, 
           a.username AS uploaded_by_name
    FROM publication_documents d
    LEFT JOIN publication_categories c ON d.publication_category_id = c.id
    LEFT JOIN branches b ON d.branch_id = b.id
    LEFT JOIN admins a ON d.uploaded_by = a.id
    ORDER BY d.uploaded_at DESC
")->fetch_all(MYSQLI_ASSOC);

include "template/head.php";
?>

<body>

    <?php
    include "template/preloader.php";
    ?>

    <div id="main-wrapper">
        <?php
        include "template/nav.php";
        include "template/header.php";
        ?>

        <?php
        include "template/desnav.php";
        ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Publication Documents</h4>
                                <a href="publications-docs-upload.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Upload Document
                                </a>
                            </div>
                            
                            <?php if (isset($_GET['success'])): ?>
                                <?php if($_GET['success'] == 1): ?>
                                <div class="alert alert-primary mx-5" >
                                    Document uploaded successfully!
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger mx-5" >
                                    Error occurred while processing your request.
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="documentTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Branch</th>
                                                <th>Uploaded By</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($documents)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">No documents found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($documents as $doc): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($doc['title']) ?></td>
                                                        <td><?= htmlspecialchars($doc['category_name'] ?? 'N/A') ?></td>
                                                    ��ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 1, 20, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (4372, 3824, 2768, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 1, 34, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (4373, 1184, 876, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 1, 35, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (4374, 16962, 11528, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 1, 26, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (6195, 145241, 39699, 1, 'Death Certificate', '2021-09-29', '', 3, 0, 1, 40, '2021-10-12');
INSERT INTO `tbl_suspension_details` VALUES (6196, 4576, 3288, 1, 'Death Certificate', '2021-02-07', '', 3, 0, 1, 40, '2021-10-12');
INSERT INTO `tbl_suspension_details` VALUES (6197, 10255, 6959, 1, 'Death Certificate', '2021-05-26', '', 3, 0, 1, 40, '2021-10-12');
INSERT INTO `tbl_suspension_details` VALUES (6198, 22254, 15194, 1, 'Death Certificate', '2017-09-29', '', 3, 0, 1, 40, '2021-10-12');
INSERT INTO `tbl_suspension_details` VALUES (6199, 142697, 38053, 1, 'Death Certificate', '2021-07-29', '', 3, 0, 1, 40, '2021-10-12');
INSERT INTO `tbl_suspension_details` VALUES (4376, 3900, 2823, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 1, 20, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (4377, 10072, 6844, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 1, 33, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (4378, 251, 185, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 0, 35, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (4379, 11209, 7565, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කරන ලදී.', '2020-11-25', '', 3, 0, 1, 26, '2020-11-25');
INSERT INTO `tbl_suspension_details` VALUES (4380, 11208, 7565, 4, '2019.07.18 සිට 2019.08.28 දක්වා සිදුකල විගණනයේ දී නිරීක්‍ෂණය වු අයදුම්පත් වලට අදාලව ගිණුම්,183 වන කළමණාකරන මණ්ඩල  තීරණය බලයට ගෙන  අත්හිටු වීම් කර�