<?php
require_once 'testsql.php';
session_start();

$videoId = $_POST['video_id'] ?? null;
$watchingTime = $_POST['watching_time'] ?? null;
$userId = $_SESSION['uid'];;
if (!$videoId || !is_numeric($watchingTime)) {
    exit('Data tidak lengkap atau salah');
}

$sql = "SELECT * FROM Tonton WHERE idVideo = ? AND idUser = ?";
$stmt = sqlsrv_query($conn, $sql, params: [$videoId, $userId]);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if ($row === false) {
    // insert
    $insertSql = "INSERT INTO Tonton (idVideo, idUser, lamaMenonton) VALUES (?, ?, ?)";
    $res = sqlsrv_query($conn, $insertSql, [$videoId, $userId, $watchingTime]);
    if ($res === false)
        die(print_r(sqlsrv_errors(), true));
} else {
    // Update
    $updateSql = "UPDATE Tonton 
              SET jumlahTonton = jumlahTonton + 1, 
                  lamaMenonton = lamaMenonton + ? 
              WHERE idVideo = ? AND idUser = ?";
    $res = sqlsrv_query($conn, $updateSql, [$watchingTime, $videoId, $userId]);
    if ($res === false)
        die(print_r(sqlsrv_errors(), true));
}

?>