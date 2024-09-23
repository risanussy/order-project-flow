<?php
session_start();
require 'admin/config_project.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Ambil project_id dari GET
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

// Hapus pengeluaran jika ada request untuk menghapus
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM pengeluaran WHERE id = ? AND project_id = ?");
    $stmt->execute([$delete_id, $project_id]);

    // Redirect kembali untuk menghindari resubmission
    header("Location: step_four.php?project_id=$project_id");
    exit;
}

// Tambahkan pengeluaran baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = $_POST['nama_barang'];
    $qty = $_POST['qty'];
    $harga = $_POST['harga'];

    if (!empty($nama_barang) && !empty($qty) && !empty($harga)) {
        for ($i = 0; $i < count($nama_barang); $i++) {
            $stmt = $pdo->prepare("INSERT INTO pengeluaran (project_id, nama_barang, qty, harga) VALUES (?, ?, ?, ?)");
            $stmt->execute([$project_id, $nama_barang[$i], $qty[$i], $harga[$i]]);
        }
        header("Location: step_five.php?project_id=$project_id");
        exit;
    }
}

// Ambil data yang sudah tersimpan
$stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE project_id = ?");
$stmt->execute([$project_id]);
$pengeluaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Flow - Pengeluaran</title>
    <link rel="icon" href="https://static.vecteezy.com/system/resources/previews/000/350/490/non_2x/tools-vector-icon.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Step 5</a>
            </li>
        </ul>
        <br>
        <div class="w-100 card shadow px-2 py-5">
        <h4>Pengeluaran.</h4>

        <!-- Form Input Pengeluaran -->
        <form action="step_four.php?project_id=<?= $_GET['project_id']; ?>" method="POST">
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
            <button type="submit" class="btn btn-lg btn-success">Kirim</button>
        </form>

        <!-- Tampilkan Data Pengeluaran yang Sudah Diinput -->
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
                                <td><?= htmlspecialchars($item['harga']); ?></td>
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

    <!-- JavaScript untuk Dinamis Row -->
    <script>
        $(document).ready(function(){
            var count = 1;
            
            // Tambah baris baru
            $('#addRow').click(function(){
                count++;
                var newRow = '<tr><td>' + count + '</td>' +
                    '<td><input type="text" name="nama_barang[]" class="form-control" required></td>' +
                    '<td><input type="number" name="qty[]" class="form-control" required></td>' +
                    '<td><input type="number" step="0.01" name="harga[]" class="form-control" required></td>' +
                    '<td><button type="button" class="btn btn-danger removeRow">Hapus</button></td></tr>';
                $('#pengeluaranTable tbody').append(newRow);
            });

            // Hapus baris
            $(document).on('click', '.removeRow', function(){
                $(this).closest('tr').remove();
            });
        });
    </script>
</body>
</html>
