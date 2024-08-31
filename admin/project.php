<?php
session_start();
require 'config_project.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// Ambil daftar user dari database yang hanya memiliki role 'user'
$stmt = $pdo->prepare("SELECT id, name FROM user WHERE role = :role");
$stmt->execute(['role' => 'user']);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses form ketika disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create project
    $nama_project = $_POST['nama_project'];
    $deskripsi = $_POST['deskripsi'];
    $code = $_POST['code'];
    $user_id = $_POST['user_id']; // Ambil user_id dari form

    $stmt = $pdo->prepare("INSERT INTO project (nama_project, deskripsi, code, user_id) VALUES (:nama_project, :deskripsi, :code, :user_id)");
    $stmt->execute([
        'nama_project' => $nama_project,
        'deskripsi' => $deskripsi,
        'code' => $code,
        'user_id' => $user_id
    ]);
    $project_id = $pdo->lastInsertId(); // Mendapatkan ID project yang baru dibuat

    // Create persiapan
    foreach ($_POST['nama_barang'] as $index => $nama_barang) {
        $status = $_POST['status'][$index];
        $stmt = $pdo->prepare("INSERT INTO persiapan (nama_barang, status, row_status, project_id) VALUES (:nama_barang, :status, '1', :project_id)");
        $stmt->execute(['nama_barang' => $nama_barang, 'status' => $status, 'project_id' => $project_id]);
        $stmt2 = $pdo->prepare("INSERT INTO persiapan (nama_barang, status, row_status, project_id) VALUES (:nama_barang, :status, '2', :project_id)");
        $stmt2->execute(['nama_barang' => $nama_barang, 'status' => $status, 'project_id' => $project_id]);
    }

    // Create pekerjaan
    foreach ($_POST['nama_pekerjaan'] as $index => $nama_pekerjaan) {
        $jumlah_total = $_POST['jumlah_total'][$index];
        $sudah_dikerjakan = $_POST['sudah_dikerjakan'][$index];
        $status_pekerjaan = $_POST['status_pekerjaan'][$index];
        $row_status = $_POST['row_status'][$index]; // Ambil row_status dari form

        $stmt = $pdo->prepare("INSERT INTO pekerjaan (nama_pekerjaan, jumlah_total, sudah_dikerjakan, status, project_id, row_status) VALUES (:nama_pekerjaan, :jumlah_total, :sudah_dikerjakan, :status, :project_id, :row_status)");
        $stmt->execute([
            'nama_pekerjaan' => $nama_pekerjaan,
            'jumlah_total' => $jumlah_total,
            'sudah_dikerjakan' => $sudah_dikerjakan,
            'status' => $status_pekerjaan,
            'project_id' => $project_id,
            'row_status' => $row_status
        ]);
    }

    // Redirect setelah submit
    header('Location: project.php');
    exit;
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project | Admin Project Flow</title>
    <link rel="icon" href="https://static.vecteezy.com/system/resources/previews/000/350/490/non_2x/tools-vector-icon.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h3 class="mb-4">Project Management</h3>

        <!-- Formulir besar untuk semua -->
        <form method="POST" id="main-form">
            <!-- Create Project -->
            <h4>Buat Project</h4>
            <div class="mb-3">
                <label for="nama_project" class="form-label">Nama Project</label>
                <input type="text" name="nama_project" class="form-control" id="nama_project" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" id="deskripsi" required></textarea>
            </div>

            <!-- Dropdown User -->
            <div class="mb-3">
                <label for="user_id" class="form-label">Mandat kepada User</label>
                <select name="user_id" class="form-select" id="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= $user['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="code" value="Berlangsung" id="code">

            <div class="row">
                <div class="col-md-4">
                    <!-- Create Persiapan -->
                    <h4>Buat Persiapan</h4>
                    <div id="persiapan-section">
                        <!-- Persiapan Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama<br>Barang</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="persiapan-table-body"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addPersiapan()">Add More Persiapan</button>                    
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Create Pekerjaan -->
                    <h4>Buat Pekerjaan</h4>
                    <div id="pekerjaan-section">
                        <!-- Pekerjaan Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama<br>Pekerjaan</th>
                                        <th>Jumlah<br>Total</th>
                                        <th>Sudah<br>Dikerjakan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pekerjaan-table-body"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addPekerjaan()">Add More Pekerjaan</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Finishing Pekerjaan -->
                    <h4>Finishing Pekerjaan</h4>
                    <div id="finishing-section">
                        <!-- Finishing Pekerjaan Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama<br>Pekerjaan</th>
                                        <th>Jumlah<br>Total</th>
                                        <th>Sudah<br>Dikerjakan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="finishing-table-body"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addFinishing()">Add More Finishing Pekerjaan</button>
                    </div>
                </div>
            </div>

            <br>
            <!-- Submit Semua -->
            <button type="submit" class="btn btn-success">Submit Semua</button>
            <br><br>
        </form>
    </div>

    <script>
        let persiapanIndex = 0;
        let pekerjaanIndex = 0;

        function addPersiapan() {
            const persiapanTableBody = document.getElementById('persiapan-table-body');
            const newRow = `
                <tr id="persiapan-row-${persiapanIndex}">
                    <td>
                    <input type="text" name="nama_barang[]" class="form-control" required>
                    <input type="hidden" name="status[]" value="Belum Siap">
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('persiapan-row-${persiapanIndex}')">Hapus</button></td>
                </tr>
            `;
            persiapanTableBody.insertAdjacentHTML('beforeend', newRow);
            persiapanIndex++;
        }

        function addPekerjaan() {
            const pekerjaanTableBody = document.getElementById('pekerjaan-table-body');
            const newRow = `
                <tr id="pekerjaan-row-${pekerjaanIndex}">
                    <td><input type="text" name="nama_pekerjaan[]" class="form-control" required></td>
                    <td><input type="number" name="jumlah_total[]" class="form-control" required></td>
                    <td>
                        <input type="number" name="sudah_dikerjakan[]" class="form-control" required>
                        <input type="hidden" name="status_pekerjaan[]" value="Belum Dikerjakan">
                        <input type="hidden" name="row_status[]" value="1"> <!-- Row status untuk pekerjaan biasa -->
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('pekerjaan-row-${pekerjaanIndex}')">Hapus</button></td>
                </tr>
            `;
            pekerjaanTableBody.insertAdjacentHTML('beforeend', newRow);
            pekerjaanIndex++;
        }

        function addFinishing() {
            const finishingTableBody = document.getElementById('finishing-table-body');
            const newRow = `
                <tr id="finishing-row-${pekerjaanIndex}">
                    <td><input type="text" name="nama_pekerjaan[]" class="form-control" required></td>
                    <td><input type="number" name="jumlah_total[]" class="form-control" required></td>
                    <td>
                        <input type="number" name="sudah_dikerjakan[]" class="form-control" required>
                        <input type="hidden" name="status_pekerjaan[]" value="Belum Dikerjakan">
                        <input type="hidden" name="row_status[]" value="2"> <!-- Row status untuk finishing -->
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('finishing-row-${pekerjaanIndex}')">Hapus</button></td>
                </tr>
            `;
            finishingTableBody.insertAdjacentHTML('beforeend', newRow);
            pekerjaanIndex++;
        }

        function removeRow(rowId) {
            document.getElementById(rowId).remove();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
