<?php
require_once 'testsql.php';
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Content-Type: application/json");

$videoId = $_POST['video_id'] ?? null;
$action = $_POST['action'] ?? null; // 1 = like, 2 = dislike
$userId = $_SESSION['uid'];; // Ganti sesuai sistem login

if (!$videoId || !$action) {
    exit;
}

// Ambil status likeDislike saat ini
$cekSql = "SELECT likeDislike FROM Tonton WHERE idVideo = ? AND idUser = ?";
$stmt = sqlsrv_query($conn, $cekSql, [$videoId, $userId]);
$data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$userStatus = 0;

if ($data) {
    if ($data['likeDislike'] == $action) {
        // Undo (klik tombol yang sama)
        $update = "UPDATE Tonton SET likeDislike = 0 WHERE idVideo = ? AND idUser = ?";
        sqlsrv_query($conn, $update, [$videoId, $userId]);
        $userStatus = 0;
    } else {
        // Ubah pilihan
        $update = "UPDATE Tonton SET likeDislike = ? WHERE idVideo = ? AND idUser = ?";
        sqlsrv_query($conn, $update, [$action, $videoId, $userId]);
        $userStatus = $action;
    }
} else {
    // Belum pernah interaksi, insert baru
    $insert = "INSERT INTO Tonton (idVideo, idUser, lamaMenonton, jumlahTonton, likeDislike) VALUES (?, ?, 0, 0, ?)";
    sqlsrv_query($conn, $insert, [$videoId, $userId, $action]);
    $userStatus = $action;
}

// Hitung jumlah like & dislike
$countSql = "SELECT 
    SUM(CASE WHEN likeDislike = 1 THEN 1 ELSE 0 END) AS likes,
    SUM(CASE WHEN likeDislike = 2 THEN 1 ELSE 0 END) AS dislikes
    FROM Tonton WHERE idVideo = ?";
$stmt = sqlsrv_query($conn, $countSql, [$videoId]);
$countData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Kirim respons JSON ke bagian frontend
echo json_encode([
    'success' => true,
    'likes' => $countData['likes'] ?? 0,
    'dislikes' => $countData['dislikes'] ?? 0,
    'user_status' => $userStatus
]);
?>
