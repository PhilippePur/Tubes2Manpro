<?php
session_start();
require 'testsql.php'; // pastikan koneksi

$userId = $_SESSION['uid'] ?? null;

if (!$userId) {
    echo "<p>Silakan login terlebih dahulu.</p>";
    exit;
}


// Ambil undangan yang statusnya pending (misalnya IsActive = 2)
$sql = "
SELECT A.idUser, C.namaChannel, C.fotoProfil, R.RoleName
FROM [Admin] A
JOIN Channel C ON A.idChannel = C.idChannel
JOIN Roles R ON A.idRole = R.idRole
WHERE A.idUser = ? AND A.IsActive = 2 AND C.channelType = 1
";
$params = [$userId];
$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt || !sqlsrv_has_rows($stmt)) {
    echo "<p>Tidak ada undangan saat ini.</p>";
    exit;
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $idAdmin = $row['idUser'];
    $channel = htmlspecialchars($row['namaChannel']);
    $foto = $row['fotoProfil'] ?? 'Assets/NoProfile.jpg';
    $role = htmlspecialchars($row['RoleName']);

    echo "
    <div style='margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;'>
        <img src='$foto' width='30' height='30' style='border-radius: 50%; vertical-align: middle;'> 
        <strong>$channel</strong> - $role<br>
        <button onclick=\"respondToInvite($idAdmin, 'accept')\">Terima</button>
        <button onclick=\"respondToInvite($idAdmin, 'reject')\">Tolak</button>
        <button onclick=\"respondToInvite($idAdmin, 'cancel')\">Batalkan</button>
    </div>";

}
?>