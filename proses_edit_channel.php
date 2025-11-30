<!-- proses edit channel -->
<?php
session_start();
require_once 'testsql.php';

if (!isset($_SESSION['uid']) || !isset($_POST['update_channel'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['uid'];
$channelID = $_POST['idChannel'] ?? null;

$fotoProfil = null;
$namaChannel = $_POST['username'];
$deskripsi = $_POST['deskripsi'];

if (!$channelID) {
    echo "<script>alert('ID Channel tidak ditemukan.'); window.location.href='homePage.php';</script>";
    exit;
}

// Upload foto jika ada
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $fileName = basename($_FILES['foto']['name']);
    $targetDir = "Profile/";
    $targetFile = $targetDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
        $fotoProfil = $targetFile;
    }
}

// === Update ke tabel Channel ===
$sql = "UPDATE Channel SET namaChannel = ?, deskripsi = ?" . ($fotoProfil ? ", fotoProfil = ?" : "") . " WHERE idChannel = ?";
$params = $fotoProfil ? [$namaChannel, $deskripsi, $fotoProfil, $channelID] : [$namaChannel, $deskripsi, $channelID];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Gagal update channel: " . print_r(sqlsrv_errors(), true));
}

// === Jika upload foto berhasil, update juga ke Users ===
if ($fotoProfil) {
    $sqlUser = "UPDATE Users SET fotoProfil = ? WHERE Id = ?";
    $stmtUser = sqlsrv_query($conn, $sqlUser, [$fotoProfil, $uid]);

    if ($stmtUser) {
        $_SESSION['fotoProfil'] = $fotoProfil;
    }
}

// === Redirect dengan pesan sukses ===
echo "<script>window.location.href='homePage.php';</script>";
exit;
?>
