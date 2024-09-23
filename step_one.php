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

// Hapus pengeluaran jika ada request untuk menghapus
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM pengeluaran WHERE id = ? AND project_id = ?");
    $stmt->execute([$delete_id, $project_id]);

    // Redirect kembali untuk menghindari resubmission
    header("Location: step_one.php?project_id=$project_id");
    exit;
}

// Update status persiapan dan project
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_persiapan'])) {
        // Update status persiapan
        foreach ($_POST['status'] as $persiapan_id => $status) {
            $stmt = $pdo->prepare("UPDATE persiapan SET status = :status WHERE id = :id AND project_id = :project_id AND row_status = '1'");
            $stmt->execute(['status' => $status, 'id' => $persiapan_id, 'project_id' => $project_id]);

            // Catat riwayat perubahan status persiapan
            $edited = "Mengubah status persiapan ID $persiapan_id menjadi $status";
            $stmt = $pdo->prepare("INSERT INTO riwayat (project_id, edited) VALUES (:project_id, :edited)");
            $stmt->execute(['project_id' => $project_id, 'edited' => $edited]);
        }

        // Update project catatan_persiapan dan foto_persiapan
        $catatan_persiapan = $_POST['catatan_persiapan'];

        $stmt = $pdo->prepare("UPDATE project SET catatan_persiapan = :catatan_persiapan WHERE id = :project_id");
        $stmt->execute(['catatan_persiapan' => $catatan_persiapan, 'project_id' => $project_id]);

        // Catat riwayat perubahan catatan persiapan
        $edited = "Mengubah catatan persiapan proyek";
        $stmt = $pdo->prepare("INSERT INTO riwayat (project_id, edited) VALUES (:project_id, :edited)");
        $stmt->execute(['project_id' => $project_id, 'edited' => $edited]);

        // Handle multiple file uploads for foto_persiapan
        if (isset($_FILES['foto_persiapan'])) {
            $total_files = count($_FILES['foto_persiapan']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['foto_persiapan']['error'][$i] == UPLOAD_ERR_OK) {
                    $upload_dir = 'dokumen/';
                    $uploaded_file = $upload_dir . basename($_FILES['foto_persiapan']['name'][$i]);
                    if (move_uploaded_file($_FILES['foto_persiapan']['tmp_name'][$i], $uploaded_file)) {
                        // Insert each photo path into the 'dokumentasi' table with status 1
                        $stmt = $pdo->prepare("INSERT INTO dokumentasi (foto, project_id, status) VALUES (:foto, :project_id, 1)");
                        $stmt->execute(['foto' => $uploaded_file, 'project_id' => $project_id]);

                        // Catat riwayat upload foto
                        $edited = "Menambahkan foto persiapan " . basename($_FILES['foto_persiapan']['name'][$i]);
                        $stmt = $pdo->prepare("INSERT INTO riwayat (project_id, edited) VALUES (:project_id, :edited)");
                        $stmt->execute(['project_id' => $project_id, 'edited' => $edited]);
                    }
                }
            }
        }

        // Redirect setelah submit ke step_two.php
        header("Location: step_two.php?project_id=$project_id");
        exit;
    }

    if (isset($_POST['submit_pengeluaran'])) {
        // Tambahkan pengeluaran baru
        $nama_barang = $_POST['nama_barang'];
        $qty = $_POST['qty'];
        $harga = $_POST['harga'];

        if (!empty($nama_barang) && !empty($qty) && !empty($harga)) {
            for ($i = 0; $i < count($nama_barang); $i++) {
                $stmt = $pdo->prepare("INSERT INTO pengeluaran (project_id, nama_barang, qty, harga) VALUES (?, ?, ?, ?)");
                $stmt->execute([$project_id, $nama_barang[$i], $qty[$i], $harga[$i]]);
            }
        }
        header("Location: step_one.php?project_id=$project_id");
        exit;
    }
}

// Ambil data persiapan berdasarkan project_id
$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id AND row_status = '1'");
$stmt->execute(['project_id' => $project_id]);
$persiapan_list = $stmt->fetchAll();

// Ambil data pengeluaran
$stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE project_id = ?");
$stmt->execute([$project_id]);
$pengeluaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Project Flow - Persiapan dan Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </head>
  <body>
    <div class="container py-3">
        <h1 class="text-primary">Project Flow</h1>
        <p>Detail persiapan dan pengeluaran proyek.</p>
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>

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

        <!-- Bagian Persiapan -->
        <div class="card shadow px-2 py-5">
            <h4>Sebelum Berangkat - Persiapan</h4>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-primary">
                            <tr>
                                <th>No.</th>
                                <th>Nama Barang</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($persiapan_list as $index => $persiapan): ?>
                            <tr>
                                <th><?= $index + 1 ?></th>
                                <td><?= htmlspecialchars($persiapan['nama_barang']) ?></td>
                                <td>
                                    <select class="form-select" name="status[<?= $persiapan['id'] ?>]">
                                        <option value="Siap" <?= $persiapan['status'] == 'Siap' ? 'selected' : '' ?>>Siap</option>
                                        <option value="Belum Siap" <?= $persiapan['status'] == 'Belum Siap' ? 'selected' : '' ?>>Belum Siap</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Bagian Upload Foto Persiapan -->
                <div>
                    <label>Upload Foto Persiapan</label>
                    <div id="foto-container">
                        <div class="input-group mb-3">
                            <input type="file" name="foto_persiapan[]" class="form-control">
                            <button class="btn btn-danger remove-foto" type="button">Hapus</button>
                        </div>
                    </div>
                    <button type="button" id="add-foto" class="btn btn-primary">Tambah Foto</button><br><br>

                    <!-- Catatan Persiapan -->
                    <label>Catatan Persiapan</label>
                    <textarea class="form-control" name="catatan_persiapan" id="catatan_persiapan" rows="3"><?= $project['catatan_persiapan'] ?></textarea>
                </div>

                <!-- Tombol Lanjutkan -->
                <button type="submit" name="submit_persiapan" class="btn btn-lg btn-warning mt-3">Lanjutkan ke Step 2</button>
            </form>
        </div>

        <!-- Bagian Pengeluaran -->
        <div class="card shadow px-2 py-5 mt-5">
            <h4>Pengeluaran</h4>
            <form action="" method="POST">
                <div class="table-responsive">
                    <table class="table" id="pengeluaranTable">
                        <thead class="table-primary">
                            <tr>
                                <th>No.</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><input type="text" name="nama_barang[]" class="form-control" required></td>
                                <td><input type="number" name="qty[]" class="form-control" required></td>
                                <td><input type="number" step="0.01" name="harga[]" class="form-control" required></td>
                                <td><button type="button" class="btn btn-danger removeRow">Hapus</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" id="addRow" class="btn btn-primary">Tambah Baris</button><br><br>

                <button type="submit" name="submit_pengeluaran" class="btn btn-lg btn-success">Catat Pengeluaran</button>
            </form>

            <!-- Daftar Pengeluaran -->
            <h4 class="mt-5">Daftar Pengeluaran</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-primary">
                        <tr>
                            <th>No.</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pengeluaran): ?>
                            <?php foreach ($pengeluaran as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                                    <td><?= htmlspecialchars($item['qty']); ?></td>
                                    <td><?= 'Rp ' . number_format($item['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="step_one.php?project_id=<?= $project_id; ?>&delete_id=<?= $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">Belum ada pengeluaran.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk Dinamis Row dan Foto -->
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

        // Tambah baris pengeluaran dinamis
        $(document).ready(function(){
            var count = 1;
            $('#addRow').click(function(){
                count++;
                var newRow = '<tr><td>' + count + '</td>' +
                    '<td><input type="text" name="nama_barang[]" class="form-control" required></td>' +
                    '<td><input type="number" name="qty[]" class="form-control" required></td>' +
                    '<td><input type="number" step="0.01" name="harga[]" class="form-control" required></td>' +
                    '<td><button type="button" class="btn btn-danger removeRow">Hapus</button></td></tr>';
                $('#pengeluaranTable tbody').append(newRow);
            });

            // Hapus baris pengeluaran
            $(document).on('click', '.removeRow', function(){
                $(this).closest('tr').remove();
            });
        });
    </script>
  </body>
</html>
