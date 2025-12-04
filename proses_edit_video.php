<?php
require_once 'testsql.php';
session_start();

$idVideo = $_POST['id'];
$title = $_POST['title'];
$description = $_POST['description'];
$isActive = isset($_POST['arsipkan']) ? 0 : 1;

$thumbnailPath = null;
$videoPath = null;

// Handle file upload thumbnail
if ($_FILES['thumbnail']['size'] > 0) {
    $thumbnailPath = 'thumbnails/' . basename($_FILES['thumbnail']['name']);
    move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath);
}

// Handle file upload video
if ($_FILES['video_path']['size'] > 0) {
    $videoPath = 'uploads/' . basename($_FILES['video_path']['name']);
    move_uploaded_file($_FILES['video_path']['tmp_name'], $videoPath);
}

$sql = "UPDATE Videos SET title = ?, description = ?, isActive = ?";
$params = [$title, $description, $isActive];

if ($thumbnailPath) {
    $sql .= ", thumbnail = ?";
    $params[] = $thumbnailPath;
}

if ($videoPath) {
    $sql .= ", path = ?";
    $params[] = $videoPath;
}

$sql .= " WHERE idVideo = ?";
$params[] = $idVideo;

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo "<script>alert('Gagal mengupdate konten'); window.history.back();</script>";
} else {
    echo "<script>alert('Konten berhasil diperbarui'); window.location.href='dashboard.php';</script>";
}
?>


<!-- ini adalah komen -->