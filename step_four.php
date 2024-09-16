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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update code menjadi "Selesai" di tabel project
    $stmt = $pdo->prepare("UPDATE project SET code = 'Selesai' WHERE id = :project_id");
    $stmt->execute(['project_id' => $project_id]);

    // Update project catatan_persiapan
    $catatan_persiapan = $_POST['catatan_persiapan'];

    // Handle multiple file uploads for foto_persiapan
    if (isset($_FILES['foto_persiapan'])) {
        $total_files = count($_FILES['foto_persiapan']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['foto_persiapan']['error'][$i] == UPLOAD_ERR_OK) {
                $upload_dir = 'dokumen/';
                $uploaded_file = $upload_dir . basename($_FILES['foto_persiapan']['name'][$i]);
                if (move_uploaded_file($_FILES['foto_persiapan']['tmp_name'][$i], $uploaded_file)) {
                    // Insert each photo path into the 'dokumentasi' table
                    $stmt = $pdo->prepare("INSERT INTO dokumentasi (foto, project_id) VALUES (:foto, :project_id)");
                    $stmt->execute(['foto' => $uploaded_file, 'project_id' => $project_id]);

                    // Catat riwayat upload foto
                    $edited = "Menambahkan foto persiapan: " . basename($_FILES['foto_persiapan']['name'][$i]);
                    $stmt = $pdo->prepare("INSERT INTO riwayat (project_id, edited) VALUES (:project_id, :edited)");
                    $stmt->execute(['project_id' => $project_id, 'edited' => $edited]);
                }
            }
        }
    }

    // Update project catatan dan foto persiapan
    $stmt = $pdo->prepare("UPDATE project SET catatan_persiapan = :catatan_persiapan WHERE id = :project_id");
    $stmt->execute([
        'catatan_persiapan' => $catatan_persiapan,
        'project_id' => $project_id
    ]);

    // Update persiapan data for row_status = 2
    foreach ($_POST['status'] as $persiapan_id => $status) {
        $stmt = $pdo->prepare("UPDATE persiapan SET status = :status WHERE id = :id AND project_id = :project_id AND row_status = 2");
        $stmt->execute([
            'status' => $status,
            'id' => $persiapan_id,
            'project_id' => $project_id
        ]);

        // Catat riwayat perubahan status persiapan
        $edited = "Mengubah status persiapan ID $persiapan_id menjadi $status";
        $stmt = $pdo->prepare("INSERT INTO riwayat (project_id, edited) VALUES (:project_id, :edited)");
        $stmt->execute(['project_id' => $project_id, 'edited' => $edited]);
    }

    // Redirect to a success page or show a success message
    header("Location: dashboard.php");
    exit;
}

// Ambil data persiapan dan project berdasarkan project_id
$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id AND row_status = '2'");
$stmt->execute(['project_id' => $project_id]);
$persiapan_list = $stmt->fetchAll();

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
                <a class="nav-link" href="step_one.php?project_id=<?= $_GET['project_id']; ?>">Step 1</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="step_two.php?project_id=<?= $_GET['project_id']; ?>">Step 2</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="step_three.php?project_id=<?= $_GET['project_id']; ?>">Step 3</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active text-primary" aria-current="page" href="#">Step 4</a>
            </li>
        </ul>
        <br>
        <div class="w-100 card shadow px-2 py-5">
        <h4>Serah Terima Pendataan Barang.</h4>
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
                        <td colspan="3">
                            <label>Foto Persiapan</label>
                            <div id="foto-container">
                                <div class="input-group mb-3">
                                    <input type="file" name="foto_persiapan[]" class="form-control" required>
                                    <button class="btn btn-danger remove-foto" type="button">Hapus</button>
                                </div>
                            </div>
                            <button type="button" id="add-foto" class="btn btn-primary">Tambah Foto</button>
                            <br><br>
                            <label>Catatan Persiapan</label>
                            <textarea class="form-control" name="catatan_persiapan" id="catatan_persiapan" rows="3"><?= htmlspecialchars($project['catatan_persiapan']) ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button class="btn btn-lg btn-primary">Kirim</button>
        </form>
        </div>
    </div>

    <script>
        // Tambah input foto dinamis
        document.getElementById('add-foto').addEventListener('click', function () {
            const container = document.getElementById('foto-container');
            const newInputGroup = document.createElement('div');
            newInputGroup.classList.add('input-group', 'mb-3');
            newInputGroup.innerHTML = `
                <input type="file" name="foto_persiapan[]" class="form-control" required>
                <button class="btn btn-danger remove-foto" type="button">Hapus</button>
            `;
            container.appendChild(newInputGroup);
        });

        // Fungsi untuk menghapus input foto
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-foto')) {
                e.target.closest('.input-group').remove();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
