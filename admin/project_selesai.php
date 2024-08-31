<?php
session_start();
require 'config_project.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Ambil daftar proyek yang sedang berlangsung
$stmt = $pdo->prepare("SELECT * FROM project WHERE code = 'Selesai'");
$stmt->execute();
$projects = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Selesai | Admin Project Flow</title>
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
                    <a class="nav-link text-white" href="user.php">User</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Project
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="project.php">Tambah Project</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="project_berlangsung.php">Project Berlangsung</a></li>
                        <li><a class="dropdown-item" href="project_selesai.php">Project Selesai</a></li>
                    </ul>
                </li>
            </ul>
            <div>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h3>Daftar Project yang Sudah Selesai</h3>
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
                        <a href="project_edit.php?project_id=<?= $project['id'] ?>" class="btn btn-warning">Edit Project</a>
                        <a href="project_detail.php?project_id=<?= $project['id'] ?>" class="btn btn-primary">View Details</a>
                        <a href="project_delete.php?project_id=<?= $project['id'] ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus proyek ini?')">Hapus Project</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
