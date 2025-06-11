<?php
include 'session.php';
include 'connect.php';

if ($_SESSION['role'] !== 'dosen') {
    echo "Akses ditolak.";
    exit();
}

if (isset($_GET['id']) && isset($_GET['table'])) {
    $id = intval($_GET['id']);
    $table = $_GET['table'];
    if (in_array($table, ['matkul', 'tugas'])) {
        $conn->query("DELETE FROM $table WHERE id = $id");
    }
}

header("Location: dashboard_dosen.php");
exit();
