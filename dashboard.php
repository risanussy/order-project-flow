<?php
session_start();
require 'admin/config_project.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Ambil daftar proyek yang sedang berlangsung
$stmt = $pdo->prepare("SELECT * FROM project WHERE code = 'Berlangsung' AND user_id ='$userId'");
$stmt->execute();
$projects = $stmt->fetchAll();

$stmt_selesai = $pdo->prepare("SELECT * FROM project WHERE code = 'Selesai' AND user_id ='$userId'");
$stmt_selesai->execute();
$projects_selesai = $stmt_selesai->fetchAll();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Project Flow</title>
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

        <div class="mt-5">
            <span class="badge text-bg-primary">Project Berlangsung</span>
            <?php if ($projects) { ?>
            <table class="table table-bordered mt-4">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama Project</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $index => $project): ?>
                    <tr>
                        <th scope="row"><?= $index + 1 ?></th>
                        <td><?= htmlspecialchars($project['nama_project']) ?></td>
                        <td><?= htmlspecialchars($project['deskripsi']) ?></td>
                        <td>
                        <a href="step_one.php?project_id=<?= $project['id'] ?>" class="btn btn-primary">Mulai</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php } else {?>
            <p class="mt-4 text-muted"><i>Tidak ada proyek yang berlangsung.</i></p>
            <?php }?>
        </div>
        <hr class="my-3">
        <div>
            <span class="badge text-bg-success">Project Selesai</span>
            <?php if ($projects_selesai) { ?>
            <table class="table table-bordered mt-4">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama Project</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects_selesai as $index => $project): ?>
                    <tr>
                        <th scope="row"><?= $index + 1 ?></th>
                        <td><?= htmlspecialchars($project['nama_project']) ?></td>
                        <td><?= htmlspecialchars($project['deskripsi']) ?></td>
                        <td>
                        <a href="step_one.php?project_id=<?= $project['id'] ?>" class="btn btn-primary">Mulai</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php } else {?>
            <p class="mt-4 text-muted"><i>Tidak ada proyek yang selesai.</i></p>
            <?php }?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
