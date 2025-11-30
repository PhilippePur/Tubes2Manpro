<?php
session_start();
require_once 'testsql.php';

if (!isset($_POST['login'])) {
  header("Location: index.php");
  exit;
}

$user = trim($_POST['username']);
$pass = $_POST['password'];

$sql = "SELECT idUser, Username, Email, Pass, fotoProfil FROM Users 
         WHERE Username = ? OR Email = ?";
$params = [$user, $user];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
  if ($pass === $row['Pass']) {
    // Simpan data penting ke session
    $_SESSION['uid'] = $row['idUser'];
    $_SESSION['uname'] = $row['Username'];
    $_SESSION['fotoProfil'] = $row['fotoProfil'];

    header("Location: homePage.php");
    exit;
  }
}

// Jika gagal login
echo "<script>alert('Login gagal! username/email atau password salah'); 
      window.location.href='index.php';</script>";

exit;

