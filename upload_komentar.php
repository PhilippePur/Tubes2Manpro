<?php
session_start();
require 'testsql.php'; // koneksi ke database

$idVideo = isset($_POST['idVideo']) ? (int) $_POST['idVideo'] : 0;

if ($idVideo <= 0) {
    die("ID video tidak valid.");
}


if (isset($_SESSION['uid']) && isset($_SESSION['uname']) && isset($_SESSION['fotoProfil'])) {
    $userId = $_SESSION['uid'];
    $username = $_SESSION['uname'];
    $fotoProfil = $_SESSION['fotoProfil'];

    // Lanjutkan dengan logika upload komentar
} else {
    // Pengguna belum login, arahkan ke halaman login
    header("Location: index.php"); // lebih baik ke index
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVideo = isset($_POST['idVideo']) ? (int) $_POST['idVideo'] : 0;
    $idUser = $_SESSION['uid'];
    $komentar = trim($_POST['komentar']);

    $tsql = "INSERT INTO Komen (idVideo, idUser, komen, tanggal, isActive) VALUES (?, ?, ?, GETDATE(), 1)";
    $params = array($idVideo, $idUser, $komentar);

    $stmt = sqlsrv_prepare($conn, $tsql, $params);

    if ($stmt) {
        sqlsrv_execute($stmt);
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Redirect kembali ke halaman video
header("Location: PageView.php?id=" . urlencode($idVideo));
exit;
?>