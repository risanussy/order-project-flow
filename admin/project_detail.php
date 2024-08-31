<?php
session_start();
require 'config_project.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Ambil project_id dari GET variable
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

if (!$project_id) {
    header('Location: project_berlangsung.php');
    exit;
}

$no_index_1 = 1;
$no_index_2 = 1;

// Ambil data project, persiapan, dan pekerjaan berdasarkan project_id
$stmt = $pdo->prepare("SELECT * FROM project WHERE id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$project = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$persiapan_list = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE project_id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$pekerjaan_list = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Project | Admin Project Flow</title>
    <link rel="icon" href="https://static.vecteezy.com/system/resources/previews/000/350/490/non_2x/tools-vector-icon.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-primary">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="#">Admin <span style="opacity: .45;">Project</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white" href="project_berlangsung.php">Kembali ke Daftar Proyek</a>
                </li>
            </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h3>Detail Project: <?= htmlspecialchars($project['nama_project']) ?></h3>
        <p><strong>Deskripsi:</strong> <?= htmlspecialchars($project['deskripsi']) ?></p>
        <p><strong>Catatan Persiapan:</strong> <?= $project['catatan_persiapan']; ?></p>
        <img src="../<?= $project['foto_persiapan']; ?>" alt="<?= $project['foto_persiapan']; ?>" width="300px">
        <p><strong>Catatan Pekerjaan:</strong> <?= $project['catatan_pekerjaan']; ?></p>
        <img src="../<?= $project['foto_pekerjaan']; ?>" alt="<?= $project['foto_pekerjaan']; ?>" width="300px">
        <p><strong>Catatan Finishing:</strong> <?= $project['catatan_finish']; ?></p>
        <img src="../<?= $project['foto_finish']; ?>" alt="<?= $project['foto_finish']; ?>" width="300px">

        <h4 class="mt-4">Persiapan</h4>
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Di Update</th>
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($persiapan_list as $index => $persiapan): ?>
                <?php if($persiapan['row_status'] == 1) { ?>
                <tr>
                    <th scope="row"><?= $no_index_1; ?></th>
                    <td><?= htmlspecialchars($persiapan['updated_at']) ?></td>
                    <td><?= htmlspecialchars($persiapan['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($persiapan['status']) ?></td>
                </tr>
                <?php
                    $no_index_1++;
                ?>
                <?php } ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h4 class="mt-4">Pekerjaan</h4>
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Di Update</th>
                    <th scope="col">Nama Pekerjaan</th>
                    <th scope="col">Jumlah Total</th>
                    <th scope="col">Sudah Dikerjakan</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pekerjaan_list as $index => $pekerjaan): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= htmlspecialchars($pekerjaan['updated_at']) ?></td>
                    <td><?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?></td>
                    <td><?= htmlspecialchars($pekerjaan['jumlah_total']) ?></td>
                    <td><?= htmlspecialchars($pekerjaan['sudah_dikerjakan']) ?></td>
                    <td><?= htmlspecialchars($pekerjaan['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4 class="mt-4">Selesai</h4>
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Di Update</th>
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($persiapan_list as $index => $persiapan): ?>
                <?php if($persiapan['row_status'] == 2) { ?>
                <tr>
                    <th scope="row"><?= $no_index_2; ?></th>
                    <td><?= htmlspecialchars($persiapan['updated_at']) ?></td>
                    <td><?= htmlspecialchars($persiapan['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($persiapan['status']) ?></td>
                </tr>
                <?php
                    $no_index_2++;
                ?>
                <?php } ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>  
    <br>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
