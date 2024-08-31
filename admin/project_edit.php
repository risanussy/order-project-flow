<?php
session_start();
require 'config_project.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// Ambil project_id dari GET parameter
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

// Redirect ke dashboard jika tidak ada project_id
if (!$project_id) {
    header('Location: dashboard.php');
    exit;
}

// Ambil data project dari database berdasarkan project_id
$stmt = $pdo->prepare("SELECT * FROM project WHERE id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika project tidak ditemukan, redirect ke dashboard
if (!$project) {
    header('Location: dashboard.php');
    exit;
}

// Ambil daftar user dari database yang hanya memiliki role 'user'
$stmt = $pdo->prepare("SELECT id, name FROM user WHERE role = :role");
$stmt->execute(['role' => 'user']);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data persiapan dan pekerjaan terkait dengan project
$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$persiapan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE project_id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$pekerjaan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses form ketika disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update project
    $nama_project = $_POST['nama_project'];
    $deskripsi = $_POST['deskripsi'];
    $code = $_POST['code'];
    $user_id = $_POST['user_id']; // Ambil user_id dari form

    // Update data project
    $stmt = $pdo->prepare("UPDATE project SET nama_project = :nama_project, deskripsi = :deskripsi, code = :code, user_id = :user_id WHERE id = :project_id");
    $stmt->execute([
        'nama_project' => $nama_project,
        'deskripsi' => $deskripsi,
        'code' => $code,
        'user_id' => $user_id,
        'project_id' => $project_id
    ]);

    // Update persiapan
    foreach ($_POST['nama_barang'] as $index => $nama_barang) {
        $status = $_POST['status'][$index];
        $persiapan_id = $_POST['persiapan_id'][$index]; // ID persiapan

        if (empty($persiapan_id)) {
            // Insert persiapan baru
            $stmt = $pdo->prepare("INSERT INTO persiapan (nama_barang, status, project_id) VALUES (:nama_barang, :status, :project_id)");
            $stmt->execute(['nama_barang' => $nama_barang, 'status' => $status, 'project_id' => $project_id]);
        } else {
            // Update persiapan yang ada
            $stmt = $pdo->prepare("UPDATE persiapan SET nama_barang = :nama_barang, status = :status WHERE id = :id AND project_id = :project_id");
            $stmt->execute(['nama_barang' => $nama_barang, 'status' => $status, 'id' => $persiapan_id, 'project_id' => $project_id]);
        }
    }

    // Update pekerjaan
    foreach ($_POST['nama_pekerjaan'] as $index => $nama_pekerjaan) {
        $jumlah_total = $_POST['jumlah_total'][$index];
        $sudah_dikerjakan = $_POST['sudah_dikerjakan'][$index];
        $status_pekerjaan = $_POST['status_pekerjaan'][$index];
        $row_status = $_POST['row_status'][$index]; // Ambil row_status dari form
        $pekerjaan_id = $_POST['pekerjaan_id'][$index]; // ID pekerjaan

        if (empty($pekerjaan_id)) {
            // Insert pekerjaan baru
            $stmt = $pdo->prepare("INSERT INTO pekerjaan (nama_pekerjaan, jumlah_total, sudah_dikerjakan, status, row_status, project_id) VALUES (:nama_pekerjaan, :jumlah_total, :sudah_dikerjakan, :status, :row_status, :project_id)");
            $stmt->execute([
                'nama_pekerjaan' => $nama_pekerjaan,
                'jumlah_total' => $jumlah_total,
                'sudah_dikerjakan' => $sudah_dikerjakan,
                'status' => $status_pekerjaan,
                'row_status' => $row_status,
                'project_id' => $project_id
            ]);
        } else {
            // Update pekerjaan yang ada
            $stmt = $pdo->prepare("UPDATE pekerjaan SET nama_pekerjaan = :nama_pekerjaan, jumlah_total = :jumlah_total, sudah_dikerjakan = :sudah_dikerjakan, status = :status, row_status = :row_status WHERE id = :id AND project_id = :project_id");
            $stmt->execute([
                'nama_pekerjaan' => $nama_pekerjaan,
                'jumlah_total' => $jumlah_total,
                'sudah_dikerjakan' => $sudah_dikerjakan,
                'status' => $status_pekerjaan,
                'row_status' => $row_status,
                'id' => $pekerjaan_id,
                'project_id' => $project_id
            ]);
        }
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
    <title>Edit Project | Admin Project Flow</title>
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
        <h3 class="mb-4">Edit Project</h3>

        <!-- Formulir besar untuk semua -->
        <form method="POST" id="main-form">
            <!-- Edit Project -->
            <h4>Edit Project</h4>
            <div class="mb-3">
                <label for="nama_project" class="form-label">Nama Project</label>
                <input type="text" name="nama_project" class="form-control" id="nama_project" value="<?= htmlspecialchars($project['nama_project']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" id="deskripsi" required><?= htmlspecialchars($project['deskripsi']) ?></textarea>
            </div>

            <!-- Dropdown User -->
            <div class="mb-3">
                <label for="user_id" class="form-label">Mandat kepada User</label>
                <select name="user_id" class="form-select" id="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= $user['id'] == $project['user_id'] ? 'selected' : '' ?>><?= $user['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="code" value="<?= htmlspecialchars($project['code']) ?>" id="code">

            <div class="row">
                <div class="col-md-4">
                    <!-- Edit Persiapan -->
                    <h4>Edit Persiapan</h4>
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
                                <tbody id="persiapan-table-body">
                                    <?php foreach ($persiapan_list as $index => $persiapan): ?>
                                        <tr id="persiapan-row-<?= $index ?>">
                                            <td>
                                                <input type="text" name="nama_barang[]" class="form-control" value="<?= htmlspecialchars($persiapan['nama_barang']) ?>" required>
                                                <input type="hidden" name="status[]" value="<?= htmlspecialchars($persiapan['status']) ?>">
                                                <input type="hidden" name="persiapan_id[]" value="<?= $persiapan['id'] ?>">
                                            </td>
                                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('persiapan-row-<?= $index ?>')">Hapus</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addPersiapan()">Add More Persiapan</button>                    
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Edit Pekerjaan -->
                    <h4>Edit Pekerjaan</h4>
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
                                <tbody id="pekerjaan-table-body">
                                    <?php foreach ($pekerjaan_list as $index => $pekerjaan): ?>
                                        <?php if ($pekerjaan['row_status'] == 1): ?>
                                            <tr id="pekerjaan-row-<?= $index ?>">
                                                <td><input type="text" name="nama_pekerjaan[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?>" required></td>
                                                <td><input type="number" name="jumlah_total[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['jumlah_total']) ?>" required></td>
                                                <td>
                                                    <input type="number" name="sudah_dikerjakan[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['sudah_dikerjakan']) ?>" required>
                                                    <input type="hidden" name="status_pekerjaan[]" value="<?= htmlspecialchars($pekerjaan['status']) ?>">
                                                    <input type="hidden" name="row_status[]" value="1">
                                                    <input type="hidden" name="pekerjaan_id[]" value="<?= $pekerjaan['id'] ?>">
                                                </td>
                                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('pekerjaan-row-<?= $index ?>')">Hapus</button></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
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
                                <tbody id="finishing-table-body">
                                    <?php foreach ($pekerjaan_list as $index => $pekerjaan): ?>
                                        <?php if ($pekerjaan['row_status'] == 2): ?>
                                            <tr id="finishing-row-<?= $index ?>">
                                                <td><input type="text" name="nama_pekerjaan[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?>" required></td>
                                                <td><input type="number" name="jumlah_total[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['jumlah_total']) ?>" required></td>
                                                <td>
                                                    <input type="number" name="sudah_dikerjakan[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['sudah_dikerjakan']) ?>" required>
                                                    <input type="hidden" name="status_pekerjaan[]" value="<?= htmlspecialchars($pekerjaan['status']) ?>">
                                                    <input type="hidden" name="row_status[]" value="2">
                                                    <input type="hidden" name="pekerjaan_id[]" value="<?= $pekerjaan['id'] ?>">
                                                </td>
                                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('finishing-row-<?= $index ?>')">Hapus</button></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
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
        let persiapanIndex = <?= count($persiapan_list) ?>;
        let pekerjaanIndex = <?= count($pekerjaan_list) ?>;

        function addPersiapan() {
            const persiapanTableBody = document.getElementById('persiapan-table-body');
            const newRow = 
                `<tr id="persiapan-row-${persiapanIndex}">
                    <td>
                        <input type="text" name="nama_barang[]" class="form-control" required>
                        <input type="hidden" name="status[]" value="Belum Siap">
                        <input type="hidden" name="persiapan_id[]" value="">
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('persiapan-row-${persiapanIndex}')">Hapus</button></td>
                </tr>`;
            persiapanTableBody.insertAdjacentHTML('beforeend', newRow);
            persiapanIndex++;
        }

        function addPekerjaan() {
            const pekerjaanTableBody = document.getElementById('pekerjaan-table-body');
            const newRow = 
                `<tr id="pekerjaan-row-${pekerjaanIndex}">
                    <td><input type="text" name="nama_pekerjaan[]" class="form-control" required></td>
                    <td><input type="number" name="jumlah_total[]" class="form-control" required></td>
                    <td>
                        <input type="number" name="sudah_dikerjakan[]" class="form-control" required>
                        <input type="hidden" name="status_pekerjaan[]" value="Belum Dikerjakan">
                        <input type="hidden" name="row_status[]" value="1">
                        <input type="hidden" name="pekerjaan_id[]" value="">
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('pekerjaan-row-${pekerjaanIndex}')">Hapus</button></td>
                </tr>`;
            pekerjaanTableBody.insertAdjacentHTML('beforeend', newRow);
            pekerjaanIndex++;
        }

        function addFinishing() {
            const finishingTableBody = document.getElementById('finishing-table-body');
            const newRow = 
                `<tr id="finishing-row-${pekerjaanIndex}">
                    <td><input type="text" name="nama_pekerjaan[]" class="form-control" required></td>
                    <td><input type="number" name="jumlah_total[]" class="form-control" required></td>
                    <td>
                        <input type="number" name="sudah_dikerjakan[]" class="form-control" required>
                        <input type="hidden" name="status_pekerjaan[]" value="Belum Dikerjakan">
                        <input type="hidden" name="row_status[]" value="2">
                        <input type="hidden" name="pekerjaan_id[]" value="">
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('finishing-row-${pekerjaanIndex}')">Hapus</button></td>
                </tr>`;
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
