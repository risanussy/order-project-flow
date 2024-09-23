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

// Handle form pekerjaan submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pekerjaan'])) {
    $all_ready = true;

    // Loop melalui setiap pekerjaan dan update status serta sudah_dikerjakan
    foreach ($_POST['sudah_dikerjakan'] as $pekerjaan_id => $sudah_dikerjakan) {
        $stmt = $pdo->prepare("SELECT jumlah_total FROM pekerjaan WHERE id = :id AND project_id = :project_id AND row_status = 1");
        $stmt->execute(['id' => $pekerjaan_id, 'project_id' => $project_id]);
        $pekerjaan = $stmt->fetch();

        $status = ($sudah_dikerjakan == $pekerjaan['jumlah_total']) ? 'Selesai' : 'Belum Dikerjakan';

        $stmt = $pdo->prepare("UPDATE pekerjaan SET sudah_dikerjakan = :sudah_dikerjakan, status = :status WHERE id = :id AND project_id = :project_id AND row_status = 1");
        $stmt->execute([
            'sudah_dikerjakan' => $sudah_dikerjakan,
            'status' => $status,
            'id' => $pekerjaan_id,
            'project_id' => $project_id
        ]);

        if ($status !== 'Selesai') {
            $all_ready = false;
        }

        $edited = "Mengubah pekerjaan ID $pekerjaan_id menjadi status $status, sudah dikerjakan: $sudah_dikerjakan";
        $stmt = $pdo->prepare("INSERT INTO riwayat (project_id, edited) VALUES (:project_id, :edited)");
        $stmt->execute(['project_id' => $project_id, 'edited' => $edited]);
    }

    // Update project catatan_pekerjaan
    $catatan_pekerjaan = $_POST['catatan_pekerjaan'];
    $stmt = $pdo->prepare("UPDATE project SET catatan_pekerjaan = :catatan_pekerjaan WHERE id = :project_id");
    $stmt->execute(['catatan_pekerjaan' => $catatan_pekerjaan, 'project_id' => $project_id]);

    // Handle multiple file uploads for foto_pekerjaan
    if (isset($_FILES['foto_pekerjaan'])) {
        $total_files = count($_FILES['foto_pekerjaan']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['foto_pekerjaan']['error'][$i] == UPLOAD_ERR_OK) {
                $upload_dir = 'dokumen/';
                $uploaded_file = $upload_dir . basename($_FILES['foto_pekerjaan']['name'][$i]);
                if (move_uploaded_file($_FILES['foto_pekerjaan']['tmp_name'][$i], $uploaded_file)) {
                    $stmt = $pdo->prepare("INSERT INTO dokumentasi (foto, project_id, status) VALUES (:foto, :project_id, 2)");
                    $stmt->execute(['foto' => $uploaded_file, 'project_id' => $project_id]);

                    $edited = "Menambahkan foto pekerjaan dengan status 2: " . basename($_FILES['foto_pekerjaan']['name'][$i]);
                    $stmt = $pdo->prepare("INSERT INTO riwayat (project_id, edited) VALUES (:project_id, :edited)");
                    $stmt->execute(['project_id' => $project_id, 'edited' => $edited]);
                }
            }
        }
    }

    if ($all_ready) {
        header("Location: step_three.php?project_id=$project_id");
        exit;
    } else {
        $error_message = "Berhasil Update, Selesaikan semua pekerjaan untuk melanjutkan ke langkah berikutnya.";
    }
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
    header("Location: step_two.php?project_id=$project_id");
    exit;
}

// Ambil data pekerjaan dan pengeluaran
$stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE project_id = :project_id AND row_status = 1");
$stmt->execute(['project_id' => $project_id]);
$pekerjaan_list = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE project_id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$pengeluaran_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <ul class="nav mt-5 nav-tabs">
            <li class="nav-item">
                <a class="nav-link" aria-disabled="true" href="step_one.php?project_id=<?= $_GET['project_id']; ?>">Step 1</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active text-primary" aria-current="page" href="#">Step 2</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Step 3</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Step 4</a>
            </li>
        </ul>
        <br>
        
        <!-- Form Pekerjaan -->
        <div class="w-100 card shadow py-5 px-2">
            <h4>Mulai Pekerjaan.</h4>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-primary">
                            <tr>
                                <th>No.</th>
                                <th>Nama Pekerjaan</th>
                                <th>Jumlah Total</th>
                                <th>Sudah Dikerjakan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pekerjaan_list as $index => $pekerjaan): ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td><?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?></td>
                                <td><?= htmlspecialchars($pekerjaan['jumlah_total']) ?> titik</td>
                                <td>
                                    <div class="d-flex" style="width: 150px">
                                        <button type="button" class="btn btn-outline-secondary" onclick="decrement(<?= $pekerjaan['id'] ?>)">-</button>
                                        <input type="number" name="sudah_dikerjakan[<?= $pekerjaan['id'] ?>]" id="sudah_dikerjakan_<?= $pekerjaan['id'] ?>" value="<?= htmlspecialchars($pekerjaan['sudah_dikerjakan']) ?>" class="form-control text-center" min="0" max="<?= $pekerjaan['jumlah_total'] ?>">
                                        <button type="button" class="btn btn-outline-secondary" onclick="increment(<?= $pekerjaan['id'] ?>)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($pekerjaan['status'] == 'Selesai'): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Belum Terpenuhi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="5">
                                    <label>Foto Pekerjaan</label>
                                    <div id="foto-container">
                                        <div class="input-group mb-3">
                                            <input type="file" name="foto_pekerjaan[]" class="form-control">
                                            <button class="btn btn-danger remove-foto" type="button">Hapus</button>
                                        </div>
                                    </div>
                                    <button type="button" id="add-foto" class="btn btn-primary">Tambah Foto</button>
                                    <br><br>
                                    <label>Catatan Pekerjaan</label>
                                    <textarea class="form-control" name="catatan_pekerjaan" id="catatan_pekerjaan" rows="3"><?= $project['catatan_pekerjaan']; ?></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="submit_pekerjaan" class="btn btn-lg btn-primary">Kirim Pekerjaan</button>
            </form>
        </div>

        <!-- Form Pengeluaran -->
        <div class="w-100 card shadow py-5 px-2 mt-5">
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
                                        <a href="step_two.php?project_id=<?= $project_id; ?>&delete_id=<?= $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
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

    <script>
        function increment(id) {
            var input = document.getElementById('sudah_dikerjakan_' + id);
            var currentValue = parseInt(input.value);
            var maxValue = parseInt(input.max);
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
            }
        }

        function decrement(id) {
            var input = document.getElementById('sudah_dikerjakan_' + id);
            var currentValue = parseInt(input.value);
            if (currentValue > 0) {
                input.value = currentValue - 1;
            }
        }

        // Tambah input foto dinamis
        document.getElementById('add-foto').addEventListener('click', function () {
            const container = document.getElementById('foto-container');
            const newInputGroup = document.createElement('div');
            newInputGroup.classList.add('input-group', 'mb-3');
            newInputGroup.innerHTML = `
                <input type="file" name="foto_pekerjaan[]" class="form-control" required>
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
