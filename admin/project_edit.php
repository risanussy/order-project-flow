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

// Ambil data persiapan, pekerjaan, serah terima, dan finishing berdasarkan row_status dari project
$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id AND row_status = 1");
$stmt->execute(['project_id' => $project_id]);
$persiapan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id AND row_status = 2");
$stmt->execute(['project_id' => $project_id]);
$serah_terima_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE project_id = :project_id AND row_status = 1");
$stmt->execute(['project_id' => $project_id]);
$pekerjaan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE project_id = :project_id AND row_status = 2");
$stmt->execute(['project_id' => $project_id]);
$finishing_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses form ketika disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_project = $_POST['nama_project'];
    $deskripsi = $_POST['deskripsi'];
    $user_id = $_POST['user_id'];

    // Update data project
    $stmt = $pdo->prepare("UPDATE project SET nama_project = :nama_project, deskripsi = :deskripsi, user_id = :user_id WHERE id = :project_id");
    $stmt->execute([
        'nama_project' => $nama_project,
        'deskripsi' => $deskripsi,
        'user_id' => $user_id,
        'project_id' => $project_id
    ]);

    // Hapus persiapan yang dipilih
    if (isset($_POST['delete_persiapan'])) {
        foreach ($_POST['delete_persiapan'] as $persiapan_id) {
            $stmt = $pdo->prepare("DELETE FROM persiapan WHERE id = :persiapan_id AND project_id = :project_id");
            $stmt->execute(['persiapan_id' => $persiapan_id, 'project_id' => $project_id]);
        }
    }

    // Hapus serah terima yang dipilih
    if (isset($_POST['delete_serah_terima'])) {
        foreach ($_POST['delete_serah_terima'] as $serah_terima_id) {
            $stmt = $pdo->prepare("DELETE FROM persiapan WHERE id = :serah_terima_id AND project_id = :project_id");
            $stmt->execute(['serah_terima_id' => $serah_terima_id, 'project_id' => $project_id]);
        }
    }

    // Hapus pekerjaan yang dipilih
    if (isset($_POST['delete_pekerjaan'])) {
        foreach ($_POST['delete_pekerjaan'] as $pekerjaan_id) {
            $stmt = $pdo->prepare("DELETE FROM pekerjaan WHERE id = :pekerjaan_id AND project_id = :project_id");
            $stmt->execute(['pekerjaan_id' => $pekerjaan_id, 'project_id' => $project_id]);
        }
    }

    // Hapus finishing yang dipilih
    if (isset($_POST['delete_finishing'])) {
        foreach ($_POST['delete_finishing'] as $finishing_id) {
            $stmt = $pdo->prepare("DELETE FROM pekerjaan WHERE id = :finishing_id AND project_id = :project_id");
            $stmt->execute(['finishing_id' => $finishing_id, 'project_id' => $project_id]);
        }
    }

    // Tambahkan atau update persiapan
    foreach ($_POST['nama_barang_persiapan'] as $index => $nama_barang) {
        $persiapan_id = $_POST['persiapan_id'][$index]; // ID persiapan
        if (empty($persiapan_id)) {
            // Insert persiapan baru
            $stmt = $pdo->prepare("INSERT INTO persiapan (nama_barang, status, row_status, project_id) VALUES (:nama_barang, 'Belum Siap', 1, :project_id)");
            $stmt->execute(['nama_barang' => $nama_barang, 'project_id' => $project_id]);
        } else {
            // Update persiapan yang ada
            $stmt = $pdo->prepare("UPDATE persiapan SET nama_barang = :nama_barang WHERE id = :id AND project_id = :project_id");
            $stmt->execute(['nama_barang' => $nama_barang, 'id' => $persiapan_id, 'project_id' => $project_id]);
        }
    }

    // Tambahkan atau update serah terima
    foreach ($_POST['nama_barang_serah_terima'] as $index => $nama_barang) {
        $serah_terima_id = $_POST['serah_terima_id'][$index]; // ID serah terima
        if (empty($serah_terima_id)) {
            // Insert serah terima baru
            $stmt = $pdo->prepare("INSERT INTO persiapan (nama_barang, status, row_status, project_id) VALUES (:nama_barang, 'Belum Siap', 2, :project_id)");
            $stmt->execute(['nama_barang' => $nama_barang, 'project_id' => $project_id]);
        } else {
            // Update serah terima yang ada
            $stmt = $pdo->prepare("UPDATE persiapan SET nama_barang = :nama_barang WHERE id = :id AND project_id = :project_id");
            $stmt->execute(['nama_barang' => $nama_barang, 'id' => $serah_terima_id, 'project_id' => $project_id]);
        }
    }

    // Tambahkan atau update pekerjaan
    foreach ($_POST['nama_pekerjaan'] as $index => $nama_pekerjaan) {
        $pekerjaan_id = $_POST['pekerjaan_id'][$index]; // ID pekerjaan
        $jumlah_total = $_POST['jumlah_total'][$index];
        $sudah_dikerjakan = $_POST['sudah_dikerjakan'][$index];
        if (empty($pekerjaan_id)) {
            // Insert pekerjaan baru
            $stmt = $pdo->prepare("INSERT INTO pekerjaan (nama_pekerjaan, jumlah_total, sudah_dikerjakan, status, row_status, project_id) VALUES (:nama_pekerjaan, :jumlah_total, :sudah_dikerjakan, 'Belum Dikerjakan', 1, :project_id)");
            $stmt->execute([
                'nama_pekerjaan' => $nama_pekerjaan,
                'jumlah_total' => $jumlah_total,
                'sudah_dikerjakan' => $sudah_dikerjakan,
                'project_id' => $project_id
            ]);
        } else {
            // Update pekerjaan yang ada
            $stmt = $pdo->prepare("UPDATE pekerjaan SET nama_pekerjaan = :nama_pekerjaan, jumlah_total = :jumlah_total, sudah_dikerjakan = :sudah_dikerjakan WHERE id = :id AND project_id = :project_id");
            $stmt->execute([
                'nama_pekerjaan' => $nama_pekerjaan,
                'jumlah_total' => $jumlah_total,
                'sudah_dikerjakan' => $sudah_dikerjakan,
                'id' => $pekerjaan_id,
                'project_id' => $project_id
            ]);
        }
    }

    // Tambahkan atau update finishing
    foreach ($_POST['nama_finishing'] as $index => $nama_finishing) {
        $finishing_id = $_POST['finishing_id'][$index]; // ID finishing
        $jumlah_total_finishing = $_POST['jumlah_total_finishing'][$index];
        $sudah_dikerjakan_finishing = $_POST['sudah_dikerjakan_finishing'][$index];
        if (empty($finishing_id)) {
            // Insert finishing baru
            $stmt = $pdo->prepare("INSERT INTO pekerjaan (nama_pekerjaan, jumlah_total, sudah_dikerjakan, status, row_status, project_id) VALUES (:nama_pekerjaan, :jumlah_total, :sudah_dikerjakan, 'Belum Dikerjakan', 2, :project_id)");
            $stmt->execute([
                'nama_pekerjaan' => $nama_finishing,
                'jumlah_total' => $jumlah_total_finishing,
                'sudah_dikerjakan' => $sudah_dikerjakan_finishing,
                'project_id' => $project_id
            ]);
        } else {
            // Update finishing yang ada
            $stmt = $pdo->prepare("UPDATE pekerjaan SET nama_pekerjaan = :nama_pekerjaan, jumlah_total = :jumlah_total, sudah_dikerjakan = :sudah_dikerjakan WHERE id = :id AND project_id = :project_id AND row_status = 2");
            $stmt->execute([
                'nama_pekerjaan' => $nama_finishing,
                'jumlah_total' => $jumlah_total_finishing,
                'sudah_dikerjakan' => $sudah_dikerjakan_finishing,
                'id' => $finishing_id,
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
            <a class="navbar-brand text-white fw-bold" href="#">Admin Project</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h3 class="mb-4">Edit Project</h3>

        <form method="POST" id="main-form">
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

            <div class="row">
                <!-- Edit Persiapan -->
                <div class="col-md-6">
                    <h4>Persiapan</h4>
                    <div id="persiapan-section">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Barang Persiapan</th>
                                        <th>Hapus dari Database</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="persiapan-table-body">
                                    <?php foreach ($persiapan_list as $index => $persiapan): ?>
                                        <tr id="persiapan-row-<?= $index ?>">
                                            <td>
                                                <input type="text" name="nama_barang_persiapan[]" class="form-control" value="<?= htmlspecialchars($persiapan['nama_barang']) ?>" required>
                                                <input type="hidden" name="persiapan_id[]" value="<?= $persiapan['id'] ?>">
                                            </td>
                                            <td><input type="checkbox" name="delete_persiapan[]" value="<?= $persiapan['id'] ?>"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('persiapan-row-<?= $index ?>')">Hapus</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addPersiapan()">Tambah Persiapan</button>
                    </div>
                </div>

                <!-- Edit Serah Terima -->
                <div class="col-md-6">
                    <h4>Serah Terima</h4>
                    <div id="serah-terima-section">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Barang Serah Terima</th>
                                        <th>Hapus dari Database</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="serah-terima-table-body">
                                    <?php foreach ($serah_terima_list as $index => $serah_terima): ?>
                                        <tr id="serah-terima-row-<?= $index ?>">
                                            <td>
                                                <input type="text" name="nama_barang_serah_terima[]" class="form-control" value="<?= htmlspecialchars($serah_terima['nama_barang']) ?>" required>
                                                <input type="hidden" name="serah_terima_id[]" value="<?= $serah_terima['id'] ?>">
                                            </td>
                                            <td><input type="checkbox" name="delete_serah_terima[]" value="<?= $serah_terima['id'] ?>"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('serah-terima-row-<?= $index ?>')">Hapus</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addSerahTerima()">Tambah Serah Terima</button>
                    </div>
                </div>

                <!-- Edit Pekerjaan -->
                <div class="col-md-6">
                    <h4>Pekerjaan</h4>
                    <div id="pekerjaan-section">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Pekerjaan</th>
                                        <th>Jumlah Total</th>
                                        <th>Sudah Dikerjakan</th>
                                        <th>Hapus dari Database</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pekerjaan-table-body">
                                    <?php foreach ($pekerjaan_list as $index => $pekerjaan): ?>
                                        <tr id="pekerjaan-row-<?= $index ?>">
                                            <td><input type="text" name="nama_pekerjaan[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?>" required></td>
                                            <td><input type="number" name="jumlah_total[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['jumlah_total']) ?>" required></td>
                                            <td>
                                                <input type="number" name="sudah_dikerjakan[]" class="form-control" value="<?= htmlspecialchars($pekerjaan['sudah_dikerjakan']) ?>" required>
                                                <input type="hidden" name="pekerjaan_id[]" value="<?= $pekerjaan['id'] ?>">
                                            </td>
                                            <td><input type="checkbox" name="delete_pekerjaan[]" value="<?= $pekerjaan['id'] ?>"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('pekerjaan-row-<?= $index ?>')">Hapus</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addPekerjaan()">Tambah Pekerjaan</button>
                    </div>
                </div>

                <!-- Finishing Pekerjaan -->
                <div class="col-md-6">
                    <h4>Finishing Pekerjaan</h4>
                    <div id="finishing-section">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Finishing</th>
                                        <th>Jumlah Total</th>
                                        <th>Sudah Dikerjakan</th>
                                        <th>Hapus dari Database</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="finishing-table-body">
                                    <?php foreach ($finishing_list as $index => $finishing): ?>
                                        <tr id="finishing-row-<?= $index ?>">
                                            <td><input type="text" name="nama_finishing[]" class="form-control" value="<?= htmlspecialchars($finishing['nama_pekerjaan']) ?>" required></td>
                                            <td><input type="number" name="jumlah_total_finishing[]" class="form-control" value="<?= htmlspecialchars($finishing['jumlah_total']) ?>" required></td>
                                            <td>
                                                <input type="number" name="sudah_dikerjakan_finishing[]" class="form-control" value="<?= htmlspecialchars($finishing['sudah_dikerjakan']) ?>" required>
                                                <input type="hidden" name="finishing_id[]" value="<?= $finishing['id'] ?>">
                                            </td>
                                            <td><input type="checkbox" name="delete_finishing[]" value="<?= $finishing['id'] ?>"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('finishing-row-<?= $index ?>')">Hapus</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary mb-4" onclick="addFinishing()">Tambah Finishing</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Submit Semua</button>
        </form>
    </div>
    <br><br><br>                     
    <script>
        let persiapanIndex = <?= count($persiapan_list) ?>;
        let serahTerimaIndex = <?= count($serah_terima_list) ?>;
        let pekerjaanIndex = <?= count($pekerjaan_list) ?>;
        let finishingIndex = <?= count($finishing_list) ?>;

        function addPersiapan() {
            const persiapanTableBody = document.getElementById('persiapan-table-body');
            const newRow = 
                `<tr id="persiapan-row-${persiapanIndex}">
                    <td><input type="text" name="nama_barang_persiapan[]" class="form-control" required></td>
                    <td><input type="checkbox" name="delete_persiapan[]" value=""></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('persiapan-row-${persiapanIndex}')">Hapus</button></td>
                </tr>`;
            persiapanTableBody.insertAdjacentHTML('beforeend', newRow);
            persiapanIndex++;
        }

        function addSerahTerima() {
            const serahTerimaTableBody = document.getElementById('serah-terima-table-body');
            const newRow = 
                `<tr id="serah-terima-row-${serahTerimaIndex}">
                    <td><input type="text" name="nama_barang_serah_terima[]" class="form-control" required></td>
                    <td><input type="checkbox" name="delete_serah_terima[]" value=""></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('serah-terima-row-${serahTerimaIndex}')">Hapus</button></td>
                </tr>`;
            serahTerimaTableBody.insertAdjacentHTML('beforeend', newRow);
            serahTerimaIndex++;
        }

        function addPekerjaan() {
            const pekerjaanTableBody = document.getElementById('pekerjaan-table-body');
            const newRow = 
                `<tr id="pekerjaan-row-${pekerjaanIndex}">
                    <td><input type="text" name="nama_pekerjaan[]" class="form-control" required></td>
                    <td><input type="number" name="jumlah_total[]" class="form-control" required></td>
                    <td><input type="number" name="sudah_dikerjakan[]" class="form-control" required></td>
                    <td><input type="checkbox" name="delete_pekerjaan[]" value=""></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('pekerjaan-row-${pekerjaanIndex}')">Hapus</button></td>
                </tr>`;
            pekerjaanTableBody.insertAdjacentHTML('beforeend', newRow);
            pekerjaanIndex++;
        }

        function addFinishing() {
            const finishingTableBody = document.getElementById('finishing-table-body');
            const newRow = 
                `<tr id="finishing-row-${finishingIndex}">
                    <td><input type="text" name="nama_finishing[]" class="form-control" required></td>
                    <td><input type="number" name="jumlah_total_finishing[]" class="form-control" required></td>
                    <td><input type="number" name="sudah_dikerjakan_finishing[]" class="form-control" required></td>
                    <td><input type="checkbox" name="delete_finishing[]" value=""></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('finishing-row-${finishingIndex}')">Hapus</button></td>
                </tr>`;
            finishingTableBody.insertAdjacentHTML('beforeend', newRow);
            finishingIndex++;
        }

        function removeRow(rowId) {
            document.getElementById(rowId).style.display = 'none';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
