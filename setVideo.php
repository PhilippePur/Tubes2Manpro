<?php
session_start();
if (isset($_GET['id'])) {
    $_SESSION['current_video_id'] = $_GET['id'];
    header("Location: PageView.php");
    exit();
} else {
    echo "Video ID tidak ditemukan.";
}

// setVideo.php
