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

// Handle form serah terima submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_serah_terima'])) {
    // Update status project menjadi "Selesai"
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

    // Redirect setelah submit
    header("Location: dashboard.php");
    exit;
}

// Handle form pengeluaran submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pengeluaran'])) {
    $nama_barang = $_POST['nama_barang'];
    $qty = $_POST['qty'];
    $harga = $_POST['harga'];

    if (!empty($nama_barang) && !empty($qty) && !empty($harga)) {
        for ($i = 0; $i < count($nama_barang); $i++) {
            $stmt = $pdo->prepare("INSERT INTO pengeluaran (project_id, nama_barang, qty, harga) VALUES (?, ?, ?, ?)");
            $stmt->execute([$project_id, $nama_barang[$i], $qty[$i], $harga[$i]]);
        }
    }
    header("Location: step_four.php?project_id=$project_id");
    exit;
}

// Ambil data persiapan dan project berdasarkan project_id
$stmt = $pdo->prepare("SELECT * FROM persiapan WHERE project_id = :project_id AND row_status = '2'");
$stmt->execute(['project_id' => $project_id]);
$persiapan_list = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM project WHERE id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$project = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE project_id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$pengeluaran_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        <!-- Navigation -->
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

        <!-- Form Serah Terima -->
        <div class="w-100 card shadow px-2 py-5">
            <h4>Serah Terima Pendataan Barang</h4>
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
                                            <input type="file" name="foto_persiapan[]" class="form-control">
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
                <button type="submit" name="submit_serah_terima" class="btn btn-lg btn-primary">Kirim Serah Terima</button>
            </form>
        </div>

        <!-- Form Pengeluaran -->
        <div class="w-100 card shadow px-2 py-5 mt-5">
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
                        <?php if ($pengeluaran_list): ?>
                            <?php foreach ($pengeluaran_list as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                                    <td><?= htmlspecialchars($item['qty']); ?></td>
                                    <td><?= 'Rp ' . number_format($item['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="step_four.php?project_id=<?= $project_id; ?>&delete_id=<?= $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
