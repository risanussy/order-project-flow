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
    $all_ready = true;

    // Loop melalui setiap pekerjaan dan update jumlah yang sudah dikerjakan serta status
    foreach ($_POST['sudah_dikerjakan'] as $pekerjaan_id => $sudah_dikerjakan) {
        // Ambil jumlah total untuk pekerjaan ini
        $stmt = $pdo->prepare("SELECT jumlah_total FROM pekerjaan WHERE id = :id AND project_id = :project_id AND row_status = 2");
        $stmt->execute(['id' => $pekerjaan_id, 'project_id' => $project_id]);
        $pekerjaan = $stmt->fetch();

        // Tentukan status berdasarkan sudah_dikerjakan vs jumlah_total
        $status = ($sudah_dikerjakan == $pekerjaan['jumlah_total']) ? 'Selesai' : 'Belum Dikerjakan';

        // Update pekerjaan
        $stmt = $pdo->prepare("UPDATE pekerjaan SET sudah_dikerjakan = :sudah_dikerjakan, status = :status WHERE id = :id AND project_id = :project_id AND row_status = 2");
        $stmt->execute([
            'sudah_dikerjakan' => $sudah_dikerjakan,
            'status' => $status,
            'id' => $pekerjaan_id,
            'project_id' => $project_id
        ]);

        // Cek apakah semua pekerjaan sudah selesai
        if ($status !== 'Selesai') {
            $all_ready = false;
        }
    }

    // Update project catatan_finish
    $catatan_finish = $_POST['catatan_finish'];
    $stmt = $pdo->prepare("UPDATE project SET catatan_finish = :catatan_finish WHERE id = :project_id");
    $stmt->execute(['catatan_finish' => $catatan_finish, 'project_id' => $project_id]);

    // Handle file upload for foto_finish
    if (isset($_FILES['foto_finish']) && $_FILES['foto_finish']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'dokumen/';
        $uploaded_file = $upload_dir . basename($_FILES['foto_finish']['name']);
        if (move_uploaded_file($_FILES['foto_finish']['tmp_name'], $uploaded_file)) {
            // Update project with file path
            $stmt = $pdo->prepare("UPDATE project SET foto_finish = :foto_finish WHERE id = :project_id");
            $stmt->execute(['foto_finish' => $uploaded_file, 'project_id' => $project_id]);
        }
    }

    // Jika semua pekerjaan selesai, pindah ke step berikutnya
    if ($all_ready) {
        header("Location: step_four.php?project_id=$project_id");
        exit;
    } else {
        $error_message = "Berhasil Update, Selesaikan semua pekerjaan untuk melanjutkan ke langkah berikutnya.";
    }
}

// Ambil data pekerjaan berdasarkan project_id dan row_status = 2
$stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE project_id = :project_id AND row_status = 2");
$stmt->execute(['project_id' => $project_id]);
$pekerjaan_list = $stmt->fetchAll();

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
                <a class="nav-link" aria-disabled="true" href="step_one.php?project_id=<?= $_GET['project_id']; ?>">Step 1</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" aria-disabled="true" href="step_two.php?project_id=<?= $_GET['project_id']; ?>">Step 2</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active text-primary" aria-current="page" href="#">Step 3</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Step 4</a>
            </li>
        </ul>
        <br>
        <div class="w-100 card shadow p-5">
        <h4>Mulai Pekerjaan Finishing.</h4>

        <!-- Display error message if any -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
        <div class="table-responsive">
            <table class="table">
                <thead class="table-primary">
                    <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Nama Pekerjaan</th>
                    <th scope="col">Jumlah Total</th>
                    <th scope="col">Sudah Dikerjakan</th>
                    <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pekerjaan_list as $index => $pekerjaan): ?>
                    <tr>
                        <th scope="row"><?= $index + 1 ?></th>
                        <td><?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?></td>
                        <td><?= htmlspecialchars($pekerjaan['jumlah_total']) ?> titik</td>
                        <td>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrement(<?= $pekerjaan['id'] ?>)">-</button>
                                <input type="number" name="sudah_dikerjakan[<?= $pekerjaan['id'] ?>]" id="sudah_dikerjakan_<?= $pekerjaan['id'] ?>" value="<?= htmlspecialchars($pekerjaan['sudah_dikerjakan']) ?>" class="form-control text-center" min="0" max="<?= $pekerjaan['jumlah_total'] ?>">
                                <button type="button" class="btn btn-outline-secondary" onclick="increment(<?= $pekerjaan['id'] ?>)">+</button>
                            </div>
                        </td>
                        <td>
                            <span id="status_<?= $pekerjaan['id'] ?>" class="badge <?= $pekerjaan['status'] == 'Selesai' ? 'bg-success' : 'bg-danger' ?>">
                                <?= $pekerjaan['status'] == 'Selesai' ? 'Selesai' : 'Belum Terpenuhi' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td class="text-end"></td>
                        <td>
                            <label>Foto Pekerjaan Finishing</label>
                            <input name="foto_finish" id="foto_finish" type="file" class="form-control mb-3">
                            <label>Catatan Finishing</label>
                            <textarea class="form-control" name="catatan_finish" id="catatan_finish" rows="3"><?= htmlspecialchars($project['catatan_finish']); ?></textarea>
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
        function increment(id) {
            var input = document.getElementById('sudah_dikerjakan_' + id);
            var currentValue = parseInt(input.value);
            var maxValue = parseInt(input.max);
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
                updateStatus(id, input.value, maxValue);
            }
        }

        function decrement(id) {
            var input = document.getElementById('sudah_dikerjakan_' + id);
            var currentValue = parseInt(input.value);
            if (currentValue > 0) {
                input.value = currentValue - 1;
                updateStatus(id, input.value, input.max);
            }
        }

        function updateStatus(id, currentValue, maxValue) {
            var statusBadge = document.getElementById('status_' + id);
            if (currentValue == maxValue) {
                statusBadge.textContent = 'Selesai';
                statusBadge.className = 'badge bg-success';
            } else {
                statusBadge.textContent = 'Belum Terpenuhi';
                statusBadge.className = 'badge bg-danger';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
