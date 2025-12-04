<?php
require 'testsql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? '';

    if (!$id || !in_array($action, ['accept', 'reject', 'cancel'])) {
        http_response_code(400);
        echo "Permintaan tidak valid.";
        exit;
    }
 

    
    // Tentukan status berdasarkan aksi
    switch ($action) {
        case 'accept':
            $newStatus = 1;
            break;
        case 'reject':
            $newStatus = 3;
            break;
        case 'cancel':
            $newStatus = 2;
            break;
    }

    $sql = "UPDATE [Admin] SET IsActive = ? WHERE idUser = ?";
    $params = [$newStatus, $id];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo "Berhasil";
    } else {
        http_response_code(500);
        echo "Gagal mengubah status.";
    }
}

?>