<?php
require_once 'testsql.php';
session_start(); // session

if (!isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit;
}

// Ambil data session 
$uid = $_SESSION['uid'];
$uname = $_SESSION['uname'];
$fotoProfil = $_SESSION['fotoProfil'];

//ambil data idVIdeo dari homepage.php
$videoId = $_GET['id'] ?? null;
if (!$videoId) {
    die("ID video tidak ditemukan.");
}

//ambil data video dan channel dari database 
$sql = "SELECT 
            V.title,
            V.uploaded_at,
            V.description,
            V.path,
            C.namaChannel,
            C.fotoProfil,
            C.idChannel
        FROM Videos V
        JOIN Channel C ON V.idChannel = C.idChannel
        WHERE V.idVideo = ?";
$stmt = sqlsrv_query($conn, $sql, [$videoId]);

if ($stmt === false) {
    echo "Query gagal: <br>";
    die(print_r(sqlsrv_errors(), true));
}

$video = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$video) {
    die("Video tidak ditemukan.");
}
// Hitung jumlah like dan dislike
$sqlLikes = "SELECT 
    SUM(CASE WHEN likeDislike = 1 THEN 1 ELSE 0 END) AS likes,
    SUM(CASE WHEN likeDislike = 2 THEN 1 ELSE 0 END) AS dislikes
FROM Tonton WHERE idVideo = ?";
$stmtLikes = sqlsrv_query($conn, $sqlLikes, [$videoId]);

$dataLikes = sqlsrv_fetch_array($stmtLikes, SQLSRV_FETCH_ASSOC);
$jumlahLike = $dataLikes['likes'] ?? 0;
$jumlahDislike = $dataLikes['dislikes'] ?? 0;

// Simulasi id user yang sedang login
$userId = $uid;

// Simulasi lama menonton default
$lamaMenonton = 0;

// 0 = tidak memilih , 1 = like, 2 = dislike
$likeDislike = 0;

$sqlCheck = "SELECT 1 FROM Tonton WHERE idVideo = ? AND idUser = ?";
$stmtCheck = sqlsrv_query($conn, $sqlCheck, [$videoId, $userId]);

if ($stmtCheck === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (!sqlsrv_fetch_array($stmtCheck)) {
    // Belum ada → INSERT
    $sqlInsertView = "INSERT INTO Tonton (idVideo, idUser, lamaMenonton, likeDislike) VALUES (?, ?, ?, ?)";
    $paramsInsert = [$videoId, $uid, $lamaMenonton, $likeDislike];
    $resultInsert = sqlsrv_query($conn, $sqlInsertView, $paramsInsert);

    if ($resultInsert === false) {
        die(print_r(sqlsrv_errors(), true));
    }
} else {
    // Sudah ada → bisa update lamaMenonton jika perlu
    $sqlUpdateView = "UPDATE Tonton SET lamaMenonton = lamaMenonton + ? WHERE idVideo = ? AND idUser = ?";
    $resultUpdate = sqlsrv_query($conn, $sqlUpdateView, [$lamaMenonton, $videoId, $userId]);

    if ($resultUpdate === false) {
        die(print_r(sqlsrv_errors(), true));
    }
}



// Ambil jumlah view berdasarkan jumlahTonton
$sqlViews = "SELECT ISNULL(SUM(jumlahTonton), 0) AS jumlahView FROM Tonton WHERE idVideo = ?";
$stmtViews = sqlsrv_query($conn, $sqlViews, [$videoId]);

$viewData = sqlsrv_fetch_array($stmtViews, SQLSRV_FETCH_ASSOC);
$video['jumlahView'] = $viewData['jumlahView'] ?? 0;

// ambil like dislike
$sqlUserStatus = "SELECT likeDislike FROM Tonton WHERE idVideo = ? AND idUser = ?";
$stmt = sqlsrv_query($conn, $sqlUserStatus, [$videoId, $userId]);
$statusData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$userStatus = $statusData['likeDislike'] ?? 0;

// ambil jumlah subscribe bedasarkan tabel subscribe
$sqlJumlahSubs = "SELECT COUNT(idUser) as count FROM Subscribe WHERE idChannel= ? AND isActive = 1";
$stmt = sqlsrv_query($conn, $sqlJumlahSubs, [$video['idChannel']]);
$jumlahSubsData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$jumlahSubs = $jumlahSubsData['count'] ?? 0;

// Cek apakah user sudah subscribe ke channel ini
$sql = "SELECT COUNT(*) as count FROM Subscribe WHERE idUser = ? AND idChannel = ? AND isActive = 1";
$stmt = sqlsrv_query($conn, $sql, [$userId, $video['idChannel']]);
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$sudahSubscribe = $row['count'] > 0;

//Komentar
$sql = "SELECT 
            K.komen, 
            K.tanggal, 
            U.username,
            U.fotoProfil
        FROM Komen K
        JOIN Users U ON K.idUser = U.idUser
        WHERE K.idVideo = ? AND K.isActive = 1
        ORDER BY K.tanggal DESC";

$params = array($videoId);
$stmt = sqlsrv_prepare($conn, $sql, $params);

if (!$stmt) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_execute($stmt) === false) {
    die(print_r(sqlsrv_errors(), true));
}

$listKomentar = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $listKomentar[] = $row;
}

$karakterPerBaris = 185;
$tinggiPerBaris = 24;
$spasiAntarKomentar = 30;
$topAwal = 179;
$currentTop = $topAwal;
date_default_timezone_set('Asia/Jakarta');


// ================== Komentar Waktu berlalu ==================
function waktu_berlalu($tanggal)
{
    if ($tanggal instanceof DateTime) {
        $waktuKomentar = $tanggal;
    } else {
        $waktuKomentar = new DateTime($tanggal);
    }

    $sekarang = new DateTime();
    $selisih = $sekarang->diff($waktuKomentar);

    if ($selisih->y > 0) {
        return $selisih->y . ' tahun yang lalu';
    } elseif ($selisih->m > 0) {
        return $selisih->m . ' bulan yang lalu';
    } elseif ($selisih->d > 0) {
        return $selisih->d . ' hari yang lalu';
    } elseif ($selisih->h > 0) {
        return $selisih->h . ' jam yang lalu';
    } elseif ($selisih->i > 0) {
        return $selisih->i . ' menit yang lalu';
    } else {
        return 'baru saja';
    }
}


?>




<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>PageView</title>

    <style>
        .komentar-teks {
            width: 1275px;
            min-height: 60px;
            position: absolute;
            left: 136px;
            color: black;
            font-size: 16px;
            font-family: Roboto;
            font-weight: 400;
            line-height: 24px;
            letter-spacing: 0.40px;
            justify-content: center;
            word-wrap: break-word;
            white-space: normal;
            /* ini bagian penting */
        }
    </style>

</head>

<body>
    <!-- <body style="transform: scale(0.25); transform-origin: top left;"> -->

    <div data-layer="PageView" class="Pageview"
        style="width: 1512px; height: 2002px; position: relative; background: white; overflow: hidden">
        <div data-layer="KolomDeskripsi" class="KolomDeskripsi"
            style="width: 1430px; height: 345px; left: 33px; top: 976px; position: absolute; background: #D9D9D9; border-radius: 42px">
        </div>
    </div>


    <?php ob_start(); ?> <!-- Start output buffering -->

    <?php foreach ($listKomentar as $komentar): ?>
        <?php
        $username = htmlspecialchars($komentar['username']);
        $waktu = waktu_berlalu($komentar['tanggal']);
        $isiKomentar = htmlspecialchars($komentar['komen']);

        $jumlahKarakter = strlen($isiKomentar);
        $jumlahBaris = ceil($jumlahKarakter / $karakterPerBaris);
        $tinggiKomentar = ($jumlahBaris * $tinggiPerBaris) + 30;

        $topUsername = $currentTop;
        $topWaktu = $topUsername + 1;
        $topIsiKomentar = $topUsername + 35;
        ?>

        <!-- Foto Profil -->
        <div data-layer="ProfileKomentator" class="ProfileKomentator"
            style="left: 87px; top: <?= $topUsername ?>px; position: absolute;">
            <img data-layer="ProfKomentator" class="ProfileKomentator-Image"
                src="<?= htmlspecialchars($komentar['fotoProfil']) ?>" alt="Foto <?= $username ?>"
                style="width: 40px; height: 40px; border-radius: 50%;">
        </div>


        <!-- Username -->
        <div style="width: 98px; height: 27px; left: 137px; top: <?= $topUsername ?>px; position: absolute; 
    justify-content: center; display: flex; flex-direction: column; color: black; font-size: 15px; 
    font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;">
            <?= $username ?>
        </div>

        <!-- Waktu -->
        <div style="width: 179px; height: 27px; left: 245px; top: <?= $topWaktu ?>px; position: absolute; 
    justify-content: center; display: flex; flex-direction: column; color: black; font-size: 12px; 
    font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;">
            <?= $waktu ?>
        </div>

        <!-- Isi Komentar -->
        <div style="width: 1257px; left: 136px; top: <?= $topIsiKomentar ?>px; position: absolute; 
    color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 24px; 
    letter-spacing: 0.40px; word-wrap: break-word;">
            <?= $isiKomentar ?>
        </div>

        <?php $currentTop += $tinggiKomentar + $spasiAntarKomentar; ?>
    <?php endforeach; ?>

    <?php $komentarHTML = ob_get_clean(); ?>

    <div id="kolomKomentar" class="KolomKomentar" style="
    position: absolute;
    top: 1350px;
    left: 40px;
    width: 1435px;
    max-width: 1440px;
    background: #D9D9D9;
    border-radius: 42px;
    padding: 24px;
    box-sizing: border-box;
    height: <?= $currentTop - $topAwal + 200 ?>px;"> <!-- 24 untuk padding bawah -->

        <?= $komentarHTML ?>
    </div>


    <div data-layer="Frame 2" class="Frame2"
        style="width: 1705px; height: 132px; padding: 10px; left: -32px; top: -9px; position: absolute; justify-content: flex-start; align-items: center; gap: 10px; display: inline-flex">
        <div data-svg-wrapper data-layer="SideBorder" class="Sideborder">
            <svg width="1535" height="144" viewBox="0 0 1512 144" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M-22 143.5H1524V-29.5H-22V143.5Z" fill="#7E7E7E" />
            </svg>
        </div>
        <div data-layer="Navigations/SearchBox" class="NavigationsSearchbox"
            style="width: 470px; height: 40px; position: relative"></div>
    </div>
    <a href="EditChannelInd.php">
        <img data-layer="MainProfile" class="Mainprofile"
            style="width: 75px; height: 75px; left: 1415px; top: 22px; position: absolute; border-radius: 200px" <img
            data-layer="MainProfile" class="Mainprofile"
            style="width: 140px; height: 140px; left: 23px; top: 128px; position: absolute; border-radius: 200px"
            src="<?= htmlspecialchars($fotoProfil) ?>" alt="Foto Profil Utama">
    </a>

    <div data-layer="Uploader" class="Uploader"
        style="width: 239.88px; height: 43.66px; left: 129px; top: 880px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 24px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        <?= htmlspecialchars($video['namaChannel']) ?>
    </div>
    <div data-layer="JumlahViewers" class="JumlahViewers"
        style="width: 239.88px; height: 43.66px; left: 65px; top: 1029px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        <?= number_format($video['jumlahView']) ?> views
    </div>
    <div data-layer="UploadedAt" class="UploadedAt"
        style="width: 336px; height: 44px; left: 65px; top: 994px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 24px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        Uploaded At <?= date_format($video['uploaded_at'], "d M Y") ?></div>
    <div data-layer="Komentar" class="Komentar"
        style="width: 336px; height: 44px; left: 65px; top: 1372px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 24px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        Komentar</div>

    <div data-layer="JumlahSubs" class="JumlahSubs"
        style="width: 239.88px; height: 43.66px; left: 129px; top: 910px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        <?= number_format($jumlahSubs) ?> subscribers
    </div>
    <!-- <div data-layer="Tambahkan Komentar" class="TambahkanKomentar"
        style="width: 239.88px; height: 43.66px; left: 137px; top: 1430px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        Tambahkan Komentar</div> -->
    <div data-layer="Deskripsi"
        style="width: 1325px; height: 345px; left: 68px; top: 1073px; position: absolute; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        <?= nl2br(htmlspecialchars($video['description'])) ?>
    </div>


    <div data-layer="Uploader" class="Uploader" style="left: 48px; top: 884px; position: absolute;">
        <img class="Uploader-Image" src="<?= htmlspecialchars($video['fotoProfil']) ?>" alt="Foto Channel"
            style="width: 59px; height: 59px; border-radius: 50%;">
    </div>
    <div data-layer="Konten" class="Konten"
        style="width: 676px; height: 25px; left: 48px; top: 841px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 32px; font-family: Roboto Slab; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        <?= htmlspecialchars($video['title']) ?>
    </div>
    <div data-layer="Navigations/SearchBox" class="NavigationsSearchbox"
        style="width: 440px; height: 40px; left: 440px; top: 49px; position: absolute">
        <div data-layer="Navigations/SearchBox" class="NavigationsSearchbox"
            style="width: 575px; height: 40px; left: 0px; top: 0px; position: absolute; background: #121212; overflow: hidden; border-top-left-radius: 2px; border-bottom-left-radius: 2px">
            <div data-layer="border-bottom" class="BorderBottom"
                style="width: 575px; height: 1px; left: 0px; top: 39px; position: absolute; background: #303030">
            </div>
            <div data-svg-wrapper data-layer="border-left" class="BorderLeft"
                style="left: 0px; top: 0px; position: absolute">
                <svg width="1" height="40" viewBox="0 0 1 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="1" height="40" fill="#303030" />
                </svg>
            </div>
            <div data-layer="border-top" class="BorderTop"
                style="width: 575px; height: 1px; left: 0px; top: 0px; position: absolute; background: #303030">
            </div>
            <div data-layer="Navigations/SearchBox/Placeholder" class="NavigationsSearchboxPlaceholder"
                style="width: 54px; height: 31px; padding-left: 2px; padding-right: 2px; padding-top: 6px; padding-bottom: 6px; left: 6px; top: 4px; position: absolute; justify-content: flex-start; align-items: flex-start; gap: 10px; display: inline-flex">
                <div data-layer="Search" class="Search"
                    style="color: #AAAAAA; font-size: 16px; font-family: Roboto; font-weight: 400; word-wrap: break-word">
                    Search</div>
            </div>
        </div>
        <div data-layer="Navigations/SearchBox-Button" class="NavigationsSearchboxButton"
            style="width: 64px; height: 36px; padding-left: 7px; padding-right: 7px; padding-top: 2px; padding-bottom: 2px; left: 575px; top: 0px; position: absolute; background: #303030; overflow: hidden; border-top-right-radius: 2px; border-bottom-right-radius: 2px; outline: 1px #303030 solid; outline-offset: -1px; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 10px; display: inline-flex">
            <div data-layer="Navigations/SearchBox-Button/icon" class="NavigationsSearchboxButtonIcon"
                style="width: 50px; height: 36px; position: relative; background: #303030; overflow: hidden">
                <div data-svg-wrapper data-layer="search" class="Search"
                    style="left: 13px; top: 6px; position: absolute">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M20.87 20.17L15.28 14.58C16.35 13.35 17 11.75 17 10C17 6.13 13.87 3 10 3C6.13 3 3 6.13 3 10C3 13.87 6.13 17 10 17C11.75 17 13.35 16.35 14.58 15.29L20.17 20.88L20.87 20.17ZM10 16C6.69 16 4 13.31 4 10C4 6.69 6.69 4 10 4C13.31 4 16 6.69 16 10C16 13.31 13.31 16 10 16Z"
                            fill="white" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Tampilkan video
        <iframe width="1536" height="710" style="left: -455px; top: 62px; position: absolute; border-radius: 20px;"
            src="https://www.youtube.com/embed/<?= htmlspecialchars(string: $video['path']) ?>?autoplay=1&controls=1" title="YouTube video player"
            frameborder="0" allow="autoplay; encrypted-media" allowfullscreen>
        </iframe> -->

        <video controls autoplay
            style="width: 1536px; height: 710px; left: -455px; top: 62px; position: absolute; border-radius: 20px; background: black; object-fit: cover;">
            <source src="<?= htmlspecialchars(string: $video['path']) ?>" type="video/mp4">
            Browser Anda tidak mendukung video.
        </video>


    </div>
    <a href="homePage.php" data-layer="Youtube-Logo" class="YoutubeLogo"
        style="width: 145px; height: 32px; left: 83px; top: 43px; position: absolute; overflow: hidden">
        <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 0px; top: 0px; position: absolute">
            <svg width="47" height="32" viewBox="0 0 47 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M45.0671 4.99718C44.5367 3.02917 42.9793 1.4826 40.9976 0.955786C37.4095 2.86102e-07 23.0147 0 23.0147 0C23.0147 0 8.62012 2.86102e-07 5.03187 0.955786C3.0502 1.4826 1.49289 3.02917 0.962423 4.99718C2.88089e-07 8.56067 0 16 0 16C0 16 2.88089e-07 23.4394 0.962423 27.0029C1.49289 28.9709 3.0502 30.5174 5.03187 31.0442C8.62012 32 23.0147 32 23.0147 32C23.0147 32 37.4095 32 40.9976 31.0442C42.9793 30.5174 44.5367 28.9709 45.0671 27.0029C46.0296 23.4394 46.0296 16 46.0296 16C46.0296 16 46.0257 8.56067 45.0671 4.99718Z"
                    fill="#FF0000" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 18.41px; top: 9.14px; position: absolute">
            <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0.407425 13.8566L12.3657 7.00064L0.407425 0.144531V13.8566Z" fill="white" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 50.58px; top: 2.27px; position: absolute">
            <svg width="16" height="28" viewBox="0 0 16 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M5.74832 18.8058L0.580032 0.269539H5.08905L6.90026 8.67216C7.36249 10.7418 7.69969 12.5066 7.91945 13.9666H8.05204C8.20365 12.9205 8.54472 11.167 9.0714 8.70228L10.9469 0.269539H15.4559L10.2232 18.8058V27.6976H5.74461V18.8058H5.74832Z"
                    fill="black" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 64.29px; top: 9.27px; position: absolute">
            <svg width="13" height="22" viewBox="0 0 13 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M2.81229 20.1099C1.90298 19.5003 1.25499 18.552 0.868488 17.2651C0.485849 15.9782 0.292679 14.2699 0.292679 12.1325V9.22369C0.292679 7.06753 0.512432 5.33289 0.951943 4.02714C1.39145 2.72142 2.0773 1.76562 3.00933 1.16732C3.94152 0.569015 5.16532 0.267975 6.68105 0.267975C8.17391 0.267975 9.36742 0.572775 10.2692 1.18237C11.1672 1.79196 11.8265 2.74775 12.2433 4.0422C12.66 5.34041 12.8685 7.06753 12.8685 9.22369V12.1325C12.8685 14.2699 12.6639 15.9858 12.2586 17.2802C11.853 18.5784 11.1938 19.5267 10.2843 20.125C9.37499 20.7234 8.13975 21.0243 6.58245 21.0243C4.97585 21.028 3.72177 20.7195 2.81229 20.1099ZM7.91243 16.9717C8.16247 16.317 8.29136 15.252 8.29136 13.7694V7.52673C8.29136 6.08924 8.16634 5.03561 7.91243 4.37333C7.65852 3.7073 7.2153 3.37617 6.57859 3.37617C5.96492 3.37617 5.52911 3.7073 5.27907 4.37333C5.02516 5.03938 4.90013 6.08924 4.90013 7.52673V13.7694C4.90013 15.252 5.02129 16.3206 5.26392 16.9717C5.5064 17.6264 5.94204 17.9538 6.57859 17.9538C7.2153 17.9538 7.65852 17.6264 7.91243 16.9717Z"
                    fill="black" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 79.23px; top: 9.66px; position: absolute">
            <svg width="13" height="22" viewBox="0 0 13 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12.5359 20.7014H8.98181L8.58773 18.248H8.48929C7.52295 20.0994 6.07553 21.025 4.14316 21.025C2.80562 21.025 1.81672 20.5885 1.18017 19.7194C0.543616 18.8462 0.225266 17.4842 0.225266 15.6328V0.66001H4.76844V15.3693C4.76844 16.265 4.86687 16.9008 5.06391 17.281C5.26095 17.661 5.59059 17.8528 6.05281 17.8528C6.44689 17.8528 6.82583 17.7325 7.18961 17.4917C7.5534 17.2507 7.81859 16.9459 7.99661 16.5773V0.65625H12.5359V20.7014Z"
                    fill="black" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 90.41px; top: 2.27px; position: absolute">
            <svg width="14" height="28" viewBox="0 0 14 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13.8772 3.90013H9.36818V27.7006H4.92361V3.90013H0.414597V0.272659H13.8772V3.90013Z"
                    fill="black" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector"
            style="left: 102.52px; top: 9.66px; position: absolute">
            <svg width="13" height="22" viewBox="0 0 13 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12.8348 20.7014H9.28073L8.88666 18.248H8.78821C7.82203 20.0994 6.37461 21.025 4.44208 21.025C3.10454 21.025 2.11564 20.5885 1.47909 19.7194C0.842542 18.8462 0.524185 17.4842 0.524185 15.6328V0.66001H5.06735V15.3693C5.06735 16.265 5.16579 16.9008 5.36283 17.281C5.55987 17.661 5.8895 17.8528 6.35189 17.8528C6.74581 17.8528 7.12474 17.7325 7.48853 17.4917C7.85232 17.2507 8.11751 16.9459 8.29553 16.5773V0.65625H12.8348V20.7014Z"
                    fill="black" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector"
            style="left: 117.57px; top: 1.26px; position: absolute">
            <svg width="14" height="29" viewBox="0 0 14 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12.8701 11.8619C12.5934 10.5976 12.1501 9.68319 11.5364 9.11498C10.9225 8.54679 10.0775 8.26456 9.00147 8.26456C8.16788 8.26456 7.38729 8.49786 6.66358 8.96823C5.93987 9.43859 5.37904 10.052 4.98496 10.8158H4.95098V0.257034H0.574554V28.6973H4.3257L4.78793 26.8008H4.88653C5.23888 27.4781 5.76555 28.0086 6.46654 28.4038C7.16754 28.7952 7.94813 28.9909 8.80443 28.9909C10.339 28.9909 11.4719 28.2872 12.1957 26.8835C12.9194 25.4762 13.283 23.2824 13.283 20.2947V17.1226C13.283 14.8836 13.1428 13.1263 12.8701 11.8619ZM8.70583 20.0387C8.70583 21.4987 8.64525 22.6427 8.52393 23.4706C8.40278 24.2984 8.20187 24.8893 7.91396 25.2354C7.62976 25.5853 7.24326 25.7584 6.76203 25.7584C6.38696 25.7584 6.04218 25.6718 5.72382 25.495C5.40563 25.3219 5.14801 25.0586 4.95098 24.7123V13.337C5.10242 12.7914 5.36777 12.3473 5.74284 11.9974C6.1142 11.6474 6.52342 11.4743 6.95906 11.4743C7.42145 11.4743 7.77751 11.655 8.02771 12.0125C8.28146 12.3737 8.45578 12.9758 8.55438 13.8262C8.65282 14.6766 8.70213 15.8845 8.70213 17.4536V20.0387H8.70583Z"
                    fill="black" />
            </svg>
        </div>
        <div data-svg-wrapper data-layer="Vector" class="Vector"
            style="left: 132.29px; top: 9.28px; position: absolute">
            <svg width="13" height="21" viewBox="0 0 13 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M4.72807 13.1939C4.72807 14.4808 4.76594 15.4442 4.84182 16.0877C4.91754 16.731 5.07671 17.1978 5.31919 17.495C5.56166 17.7885 5.93302 17.9352 6.43698 17.9352C7.11526 17.9352 7.58505 17.6718 7.8351 17.1488C8.08901 16.6258 8.22547 15.7528 8.24819 14.5336L12.1661 14.763C12.1888 14.9362 12.2001 15.177 12.2001 15.4818C12.2001 17.3331 11.6886 18.7179 10.6694 19.6323C9.65017 20.5467 8.20646 21.0058 6.34225 21.0058C4.10296 21.0058 2.53423 20.3096 1.6362 18.9136C0.734458 17.5176 0.287369 15.3614 0.287369 12.4413V8.94176C0.287369 5.93523 0.753472 3.73768 1.6855 2.35292C2.61769 0.968156 4.21284 0.275772 6.47484 0.275772C8.03214 0.275772 9.22952 0.557996 10.0631 1.1262C10.8967 1.6944 11.4839 2.57492 11.825 3.77531C12.1661 4.97568 12.3365 6.63137 12.3365 8.74608V12.1779H4.72807V13.1939ZM5.30404 3.74897C5.07285 4.03118 4.92141 4.49403 4.84182 5.13748C4.76594 5.78096 4.72807 6.75555 4.72807 8.06512V9.50256H8.05115V8.06512C8.05115 6.77812 8.00572 5.80353 7.91856 5.13748C7.8314 4.47145 7.67222 4.00484 7.44102 3.73016C7.20999 3.45923 6.85377 3.32 6.37253 3.32C5.88759 3.32376 5.53137 3.46675 5.30404 3.74897Z"
                    fill="black" />
            </svg>
        </div>
    </a>
    <div data-svg-wrapper data-layer="Menu" data-size="48" class="Menu"
        style="left: 28px; top: 39px; position: absolute">
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 20H35M5 10H35M5 30H35" stroke="var(--Icon-Brand-On-Brand, #F5F5F5)" stroke-width="4"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </div>

    <div id="subsBtn" onclick="toggleSubscribe(<?= $video['idChannel'] ?>)"
        style="width: 179px; height: 48px; left: 371px; top: 880px; position: absolute; 
     background: <?= $sudahSubscribe ? '#D9D9D9' : '#d11111' ?>; 
     border-radius: 139px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer;">
        <?= $sudahSubscribe ? 'SUBSCRIBED' : 'SUBSCRIBE' ?>
    </div>



    <div id="likeBtn" onclick="likeDislike(1)" data-layer="Like Button" class="Like Button"
        style="width: 115px; height: 48px; left: 1211px; top: 895px; position: absolute; background: #D9D9D9; border-radius: 139px">
        <div data-svg-wrapper data-layer="liked-fill" class="LikedFill" style="left: 10; top: 0; position: absolute">
            <svg width="52" height="42" viewBox="-10 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M5.25 19.25H10.5V36.75H5.25V19.25ZM32.8475 19.25H25.445L28.105 10.605C28.665 8.8025 27.195 7 25.165 7C24.15 7 23.17 7.42 22.505 8.1375L12.25 19.25V36.75H30.5025C32.3575 36.75 33.9675 35.5775 34.335 33.9325L36.68 23.4325C37.1525 21.2625 35.315 19.25 32.8475 19.25Z"
                    fill="white" />
            </svg>
        </div>

        <div id="likeCount" style="position: absolute; left: 65px; top: 3px;
                color: black; font-size: 16px; font-family: Roboto; font-weight: 400;
                line-height: 44px; letter-spacing: 0.40px;">
            <?= htmlspecialchars($jumlahLike) ?>
        </div>
    </div>

    <div id="dislikeBtn" onclick="likeDislike(2)" class="Tombol Dislike"
        style="width: 115px; height: 48px; left: 1348px; top: 895px; position: absolute; background: #D9D9D9; border-radius: 139px">
        <div data-svg-wrapper data-layer="liked-fill" class="LikedFill" style="left: 10; top: 7; position: absolute">
            <svg width="52" height="42" viewBox="-10 -5 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M5.25 22.75H10.5V5.25H5.25V22.75ZM32.8475 22.75H25.445L28.105 31.395C28.665 33.1975 27.195 35 25.165 35C24.15 35 23.17 34.58 22.505 33.8625L12.25 22.75V5.25H30.5025C32.3575 5.25 33.9675 6.4225 34.335 8.0675L36.68 18.5675C37.1525 20.7375 35.315 22.75 32.8475 22.75Z"
                    fill="white" />
            </svg>
        </div>
        <div id="dislikeCount" style="position: absolute; left: 65px; top: 15px; /* sesuaikan manual */
               color: black; font-size: 16px; font-family: Roboto; font-weight: 400;
               letter-spacing: 0.4px;">
            <?= htmlspecialchars($jumlahDislike) ?>
        </div>
    </div>

    <?php
    // Ambil path profil dari sesi login
    $fotoProfilUser = isset($_SESSION['user']['profil']) && !empty($_SESSION['user']['profil'])
        ? htmlspecialchars($_SESSION['user']['profil'])
        : 'Assets/NoProfile.jpg'; // default jika tidak ada
    ?>

    <div data-layer="MainProfile-Wrapper" class="MainProfile-Wrapper"
        style="left: 87px; top: 1428px; position: absolute;">
        <img data-layer="MainProfile" class="MainProfile-Image" src="<?= $fotoProfil ?>" alt="Foto Profil Anda"
            style="width: 40px; height: 40px; border-radius: 50%;">
    </div>



    <div data-layer="Line 1" class="Line1"
        style="width: 1256px; height: 0px; left: 137px; top: 1468px; position: absolute; outline: 1px black solid; outline-offset: -0.50px">
    </div>
    <!-- Form Tambah Komentar -->
    <form action="upload_komentar.php" method="POST"
        style="position: absolute; top: 1425px; left: 137px; width: 1256px; display: flex; flex-direction: column; gap: 10px;">

        <!-- Hidden input untuk ID video -->
        <input type="hidden" name="idVideo" value="<?= htmlspecialchars($videoId) ?>">

        <!-- Textarea Komentar -->
        <textarea name="komentar" placeholder="Tulis komentar Anda..." rows="4"
            style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; resize: vertical; font-family: Roboto; font-size: 14px;"></textarea>

        <!-- Tombol Submit -->
        <button type="submit"
            style="align-self: flex-end; padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 6px; font-size: 14px; cursor: pointer;">
            Tambahkan Komentar
        </button>
    </form>
    </div>
    <script>
        // ================== Onload pertama kali ==================
        window.onload = function () {
            const status = <?= $userStatus ?>;
            if (status == 1) {
                document.getElementById('likeBtn').style.background = '#007BFF';
            } else if (status == 2) {
                document.getElementById('dislikeBtn').style.background = '#007BFF';
            }
        }
        // ================== Timer Lama Menonton ==================
        let startTime = Date.now();

        window.addEventListener('beforeunload', function () {
            const watchingTime = Math.floor((Date.now() - startTime) / 1000); // detik

            navigator.sendBeacon('update_time.php', new URLSearchParams({
                video_id: <?= $videoId ?>,
                watching_time: watchingTime
            }));
        });

        // ================== Fungsi Like/Dislike ==================
        function likeDislike(action) {
            const videoId = <?= json_encode($videoId) ?>;

            fetch('like_dislike.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `video_id=${videoId}&action=${action}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('likeCount').innerText = data.likes;
                        document.getElementById('dislikeCount').innerText = data.dislikes;

                        // Reset warna tombol
                        document.getElementById('likeBtn').style.background = '#D9D9D9';
                        document.getElementById('dislikeBtn').style.background = '#D9D9D9';

                        // Update warna tombol aktif
                        if (data.user_status == 1) {
                            document.getElementById('likeBtn').style.background = '#007BFF';
                        } else if (data.user_status == 2) {
                            document.getElementById('dislikeBtn').style.background = '#007BFF';
                        }
                    }
                });
        }
        // ================== Toggle Subscribe ==================
        function toggleSubscribe(idChannel) {
            fetch('toggle_subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'idChannel=' + idChannel
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Refresh untuk update tampilan
                    } else {
                        alert("Gagal: " + data.message);
                    }
                });
        }

    </script>

</body>
