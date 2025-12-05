<?php
require_once 'testsql.php';
session_start();

$idChannel = $_POST['idChannel'] ?? null;
$userId = $_SESSION['uid'];; // Simulasi login user

if (!$idChannel) {
    echo json_encode(['success' => false, 'message' => 'ID channel tidak valid']);
    exit;
}

// Cek apakah sudah ada data subscribe
$sqlCheck = "SELECT isActive FROM Subscribe WHERE idUser = ? AND idChannel = ?";
$stmt = sqlsrv_query($conn, $sqlCheck, [$userId, $idChannel]);

if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['isActive']) {
        // Sudah subscribe → lakukan unsubscribe (soft delete)
        $sqlUpdate = "UPDATE Subscribe SET isActive = 0 WHERE idUser = ? AND idChannel = ?";
        sqlsrv_query($conn, $sqlUpdate, [$userId, $idChannel]);
        echo json_encode(['success' => true, 'message' => 'Unsubscribed']);
    } else {
        // Sudah pernah subscribe tapi tidak aktif → aktifkan kembali
        $sqlUpdate = "UPDATE Subscribe SET isActive = 1, tanggalSubscribe = GETDATE() WHERE idUser = ? AND idChannel = ?";
        sqlsrv_query($conn, $sqlUpdate, [$userId, $idChannel]);
        echo json_encode(['success' => true, 'message' => 'Subscribed again']);
    }
} else {
    // Belum pernah subscribe → insert baru
    $sqlInsert = "INSERT INTO Subscribe (idUser, idChannel, isActive) VALUES (?, ?, 1)";
    sqlsrv_query($conn, $sqlInsert, [$userId, $idChannel]);
    echo json_encode(['success' => true, 'message' => 'Subscribed']);
}
