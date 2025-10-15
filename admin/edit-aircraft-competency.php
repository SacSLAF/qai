<?php
require_once '../includes/config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: aircraft-competency.php');
    exit();
}

$id = intval($_GET['id']);

$res = $db->query("SELECT * FROM aircraft_competency WHERE record_id = $id OR id = $id LIMIT 1");
if (!$res || $res->num_rows === 0) {
    header('Location: aircraft-competency.php?error=notfound');
    exit();
}

$record = $res->fetch_assoc();

// Load lookups
$branches = [];
$b_res = $db->query("SELECT id, name FROM ac_categories ORDER BY name");
if ($b_res) $branches = $b_res->fetch_all(MYSQLI_ASSOC);

$formations = [];
$f_res = $db->query("SELECT formation_id, formation_name FROM formation ORDER BY formation_name");
if ($f_res) $formations = $f_res->fetch_all(MYSQLI_ASSOC);

$types = [];
$t_res = $db->query("SELECT type_id, type_name FROM type ORDER BY type_name");
if ($t_res) $types = $t_res->fetch_all(MYSQLI_ASSOC);

include 'template/head.php';
?>
<!doctype html>
<html>
<body>
<?php include 'template/preloader.php'; include 'template/nav.php'; include 'template/header.php'; include 'template/desnav.php'; ?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Edit Aircraft Competency</h4>
                        <a href="aircraft-competency.php" class="btn btn-sm btn-outline-secondary">Back</a>
                    </div>
                    <div class="card-body">
                        <form action="action/aircraft-competency-update.php" method="post">
                            <input type="hidden" name="record_id" value="<?= htmlspecialchars($record['record_id'] ?? $record['id']) ?>">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>SVC Number</label>
                                    <input type="text" name="svc_no" class="form-control" value="<?= htmlspecialchars($record['svc_no']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Rank</label>
                                    <input type="text" name="rank" class="form-control" value="<?= htmlspecialchars($record['rank']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($record['name']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Branch</label>
                                    <select name="branch_id" class="form-select">
                                        <option value="">Select</option>
                                        <?php foreach ($branches as $b): ?>
                                            <option value="<?= $b['id'] ?>" <?= (isset($record['branch']) && $record['branch'] === $b['name']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <a href="aircraft-competency.php" class="btn btn-secondary">Cancel</a>
                                <button class="btn btn-primary" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'template/footer.php'; ?>
</body>
</html>
