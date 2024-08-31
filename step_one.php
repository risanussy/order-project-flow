<?php
session_start();
require 'admin/config_project.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Ambil project_id dari GET variable
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

// Redirect jika tidak ada project_id
if (!$project_id) {
    header('Location: dashboard.php');
    exit;
}

// Update status persiapan dan project
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Loop melalui setiap status persiapan
    foreach ($_POST['status'] as $persiapan_id => $status) {
        $stmt = $pdo->prepare("UPDATE persiapan SET status = :status WHERE id = :id AND project_id = :project_id AND row_status = '1'");
        $stmt->execute(['status' => $status, 'id' => $persiapan_id, 'project_id' => $project_id]);
    }

    // Update project catatan_persiapan dan foto_persiapan
    $catatan_persiapan = $_POST['catatan_persiapan'];

    $stmt = $pdo->prepare("UPDATE project SET catatan_persiapan = :catatan_persiapan WHERE id = :project_id");
    $stmt->execute(['catatan_persiapan' => $catatan_persiapan, 'project_id' => $project_id]);

    // Handle file upload for foto_persiapan
    if (isset($_FILES['foto_persiapan']) && $_FILES['foto_persiapan']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'dokumen/';
        $uploaded_file = $upload_dir . basename($_FILES['foto_persiapan']['name']);
        if (move_uploaded_file($_FILES['foto_persiapan']['tmp_name'], $uploaded_file)) {
            // Update project with file path
            $stmt = $pdo->prepare("UPDATE project SET foto_persiapan = :foto_persiapan WHERE id = :project_id");
            $stmt->execute(['foto_persiapan' => $uploaded_file, 'project_id' => $project_id]);
        }
    }

    header("Location: step_two.php?project_id=$project_id");
    exit;
}

// Ambil data persiapan berdasarkan project_id
$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id AND row_status = '1'");
$stmt->execute(['project_id' => $project_id]);
$persiapan_list = $stmt->fetchAll();

// Ambil data project
$stmt = $pdo->prepare("SELECT * FROM project WHERE id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$project = $stmt->fetch();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Flow</title>
    <link rel="icon" href="https://static.vecteezy.com/system/resources/previews/000/350/490/non_2x/tools-vector-icon.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <div class="container pt-3">
        
        <div class="w-100 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="text-primary">Project Flow</h1>
                <p>This is a project flow App.</p>
            </div>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
        <ul class="nav mt-5 nav-tabs">
            <li class="nav-item">
                <a class="nav-link active text-primary" aria-current="page" href="#">Step 1</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Step 2</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Step 3</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Step 4</a>
            </li>
        </ul>
        <br>
        <div class="w-100 card shadow p-5">
        <h4>Sebelum Berangkat.</h4>
        <form action="" method="POST" enctype="multipart/form-data">
        <div class="table-responsive">
            <table class="table">
                <thead class="table-primary">
                    <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($persiapan_list as $index => $persiapan): ?>
                    <tr>
                        <th scope="row"><?= $index + 1 ?></th>
                        <td><?= htmlspecialchars($persiapan['nama_barang']) ?></td>
                        <td class="col-5">
                            <select class="form-select" name="status[<?= $persiapan['id'] ?>]">
                                <option value="Siap" <?= $persiapan['status'] == 'Siap' ? 'selected' : '' ?>>Terpenuhi</option>
                                <option value="Belum Siap" <?= $persiapan['status'] == 'Belum Siap' ? 'selected' : '' ?>>Kosong</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td class="text-end"></td>
                        <td>
                            <label>Foto Persiapan</label>
                            <input name="foto_persiapan" id="foto_persiapan" type="file" class="form-control mb-3">
                            <label>Catatan Persiapan</label>
                            <textarea class="form-control" name="catatan_persiapan" id="catatan_persiapan" rows="3"><?= $project['catatan_persiapan'] ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button class="btn btn-lg btn-primary">Kirim</button>
        </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
