<?php
session_start();
require 'config_project.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];

    // Hapus proyek dari database
    $stmt = $pdo->prepare("DELETE FROM project WHERE id = :project_id");
    $stmt->execute(['project_id' => $project_id]);

    // Redirect kembali ke halaman daftar proyek
    header('Location: project_berlangsung.php');
    exit;
}
?>
