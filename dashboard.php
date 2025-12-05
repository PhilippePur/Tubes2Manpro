<?php
session_start();
require_once 'testsql.php'; // Pastikan path ke file koneksi benar

$uid = $_SESSION['uid'];
$sort = $_GET['sort'] ?? 'tanggal'; // default: tanggal terbaru
$orderBy = "V.uploaded_at DESC";    // default urutan
 

switch ($sort) {
    case 'views':
        $orderBy = "(SELECT SUM(jumlahTonton) FROM Tonton T WHERE T.idVideo = V.idVideo) DESC";
        break;
    case 'watchtime':
        $orderBy = "(SELECT SUM(lamaMenonton) FROM Tonton T WHERE T.idVideo = V.idVideo) DESC";
        break;
    case 'likes':
        $orderBy = "(SELECT COUNT(*) FROM Tonton T WHERE T.idVideo = V.idVideo AND likeDislike = 1) DESC";
        break;
    case 'comments':
        $orderBy = "(SELECT COUNT(*) FROM Komen K WHERE K.idVideo = V.idVideo) DESC";
        break;
    case 'tanggal':
    default:
        $orderBy = "V.uploaded_at DESC";
        break;
}

$RoleName = 'Viewer';
$sqlRole = "SELECT R.RoleName FROM [Admin] A INNER JOIN Roles R ON A.idRole = R.idRole WHERE idUser = ?";
$paramsRole = [$uid];
$stmtRole = sqlsrv_query($conn, $sqlRole, $paramsRole);

if ($stmtRole && ($rowRole = sqlsrv_fetch_array($stmtRole, SQLSRV_FETCH_ASSOC))) {
    $RoleName = $rowRole['RoleName'];
} else {
    die("Gagal mengambil RoleName: " . print_r(sqlsrv_errors(), true));
}



$sql = "
SELECT TOP 10 V.idVideo, V.title, V.thumbnail, V.uploaded_at
FROM Videos V
INNER JOIN Channel C ON V.idChannel = C.idChannel
INNER JOIN Admin A ON C.idChannel = A.idCHannel
INNER JOIN Users U ON A.idUser = U.idUser
WHERE U.idUser = ? AND V.isActive = 1
ORDER BY $orderBy";

$params = [$uid];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$currentSort = $_GET['sort'] ?? 'tanggal';

$isActiveV = ($currentSort === 'views') ? 'font-weight: bold; text-decoration: underline;' : 'text-decoration: none;';
$isActiveW = ($currentSort === 'watchtime') ? 'font-weight: bold; text-decoration: underline;' : 'text-decoration: none;';
$isActiveL = ($currentSort === 'likes') ? 'font-weight: bold; text-decoration: underline;' : 'text-decoration: none;';
$isActiveC = ($currentSort === 'comments') ? 'font-weight: bold; text-decoration: underline;' : 'text-decoration: none;';
$isActiveT = ($currentSort === 'tanggal') ? 'font-weight: bold; text-decoration: underline;' : 'text-decoration: none;';


$totalVideos = 0;
$totalViews = 0;
$totalWatchTime = 0;
$totalLikes = 0;
$totalComments = 0;

$videos = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $videoId = $row['idVideo'];
        $title = $row['title'];
        $thumbnail = $row['thumbnail'];
        $uploadedAt = $row['uploaded_at']->format('Y-m-d');
        // Format tanggal upload
        if ($row['uploaded_at'] instanceof DateTime) {
            $row['uploaded_at_formatted'] = $row['uploaded_at']->format('Y-m-d H:i');
        } else {
            $row['uploaded_at_formatted'] = 'N/A';
        }
        $videos[] = $row;
        $totalVideos++;

        $totalViews += getViews($row['idVideo']);
        $totalWatchTime += getWatchTime($row['idVideo']);
        $totalLikes += getLikes($row['idVideo']);
        $totalComments += getComments($row['idVideo']);
    }
} else {
    echo "Error saat mengambil data video: " . print_r(sqlsrv_errors(), true);
}

$offset = ($totalVideos >= 5 && $totalVideos <= 10) ? 300 + 300 * ($totalVideos - 5) : 300;

sqlsrv_close($conn);

function getViews($videoId)
{
    require 'testsql.php'; // Koneksi ke database
    $sql = "SELECT SUM(jumlahTonton) AS total_views FROM Tonton WHERE idVideo = ? ";
    $stmt = sqlsrv_query($conn, $sql, [$videoId]);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_close($conn);
    return $row['total_views'] ?? 0;
}
function getWatchTime($videoId)
{
    require 'testsql.php';
    $sql = "SELECT SUM(lamaMenonton) AS total_watchtime FROM Tonton WHERE idVideo = ?";
    $stmt = sqlsrv_query($conn, $sql, [$videoId]);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_close($conn);
    // diasumsikan watch time dalam menit, dikonversi ke jam (jika perlu)
    return isset($row['total_watchtime']) ? $row['total_watchtime'] / 3600 : 0;
}
function getLikes($videoId)
{
    require 'testsql.php';
    $sql = "SELECT COUNT(*) AS total_likes FROM Tonton WHERE idVideo = ? AND likeDislike = 1";
    $stmt = sqlsrv_query($conn, $sql, [$videoId]);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_close($conn);
    return $row['total_likes'] ?? 0;
}
function getComments($videoId)
{
    require 'testsql.php';
    $sql = "SELECT COUNT(*) AS total_comments FROM Komen WHERE idVideo = ?";
    $stmt = sqlsrv_query($conn, $sql, [$videoId]);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_close($conn);

    return $row['total_comments'] ?? 0;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Konten</title>
</head>

<body>
    <div data-layer="VideoFilter" class="VideoFilter"
        style="width: 1512px; height: <?= 1800 + $offset ?>px; position: relative; background: white; overflow: hidden">
        <a href="homePage.php" data-layer="Youtube-Logo" class="YoutubeLogo"
            style="width: 204px; height: 45px; left: 44px; top: 53px; position: absolute; overflow: hidden">

            <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 0px; top: 0px; position: absolute">
                <svg width="65" height="45" viewBox="0 0 65 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M63.4048 7.02729C62.6586 4.25977 60.4674 2.0849 57.6794 1.34407C52.6313 4.02331e-07 32.3793 0 32.3793 0C32.3793 0 12.1276 4.02331e-07 7.07932 1.34407C4.29132 2.0849 2.10035 4.25977 1.35403 7.02729C4.05312e-07 12.0384 0 22.5 0 22.5C0 22.5 4.05312e-07 32.9616 1.35403 37.9728C2.10035 40.7403 4.29132 42.9151 7.07932 43.6558C12.1276 45 32.3793 45 32.3793 45C32.3793 45 52.6313 45 57.6794 43.6558C60.4674 42.9151 62.6586 40.7403 63.4048 37.9728C64.7589 32.9616 64.7589 22.5 64.7589 22.5C64.7589 22.5 64.7535 12.0384 63.4048 7.02729Z"
                        fill="#FF0000" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 25.90px; top: 12.86px; position: absolute">
                <svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.897461 20.1422L17.7216 10.5009L0.897461 0.859497V20.1422Z" fill="white" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 71.16px; top: 3.19px; position: absolute">
                <svg width="22" height="39" viewBox="0 0 22 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M7.43189 26.2581L0.160645 0.191528H6.50436L9.05255 12.0077C9.70286 14.9181 10.1773 17.3999 10.4864 19.453H10.673C10.8863 17.982 11.3661 15.5161 12.1071 12.0501L14.7457 0.191528H21.0895L13.7276 26.2581V38.7622H7.42667V26.2581H7.43189Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 90.45px; top: 13.03px; position: absolute">
                <svg width="19" height="30" viewBox="0 0 19 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M3.99796 27.9358C2.71866 27.0786 1.807 25.745 1.26323 23.9353C0.724896 22.1257 0.453125 19.7233 0.453125 16.7176V12.6271C0.453125 9.59495 0.762296 7.15561 1.38064 5.31941C1.99899 3.48323 2.96391 2.13915 4.27518 1.29778C5.58667 0.456418 7.30843 0.0330811 9.44091 0.0330811C11.5412 0.0330811 13.2203 0.461706 14.489 1.31896C15.7524 2.17618 16.68 3.52027 17.2663 5.34058C17.8527 7.16619 18.146 9.59495 18.146 12.6271V16.7176C18.146 19.7233 17.8582 22.1362 17.2879 23.9565C16.7174 25.7821 15.7898 27.1157 14.5103 27.957C13.231 28.7985 11.4931 29.2217 9.30219 29.2217C7.04187 29.2269 5.2775 28.7931 3.99796 27.9358ZM11.1733 23.5227C11.5251 22.602 11.7064 21.1044 11.7064 19.0195V10.2407C11.7064 8.21923 11.5306 6.73756 11.1733 5.80624C10.8161 4.86964 10.1925 4.40398 9.29675 4.40398C8.43338 4.40398 7.82025 4.86964 7.46846 5.80624C7.11123 6.74287 6.93533 8.21923 6.93533 10.2407V19.0195C6.93533 21.1044 7.1058 22.6071 7.44716 23.5227C7.78829 24.4434 8.40119 24.9037 9.29675 24.9037C10.1925 24.9037 10.8161 24.4434 11.1733 23.5227Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 111.46px; top: 13.58px; position: absolute">
                <svg width="18" height="30" viewBox="0 0 18 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.7812 28.7676H12.781L12.2266 25.3175H12.0881C10.7285 27.921 8.69214 29.2226 5.9735 29.2226C4.09171 29.2226 2.70043 28.6088 1.80487 27.3866C0.909313 26.1588 0.461426 24.2433 0.461426 21.6399V0.584389H6.8532V21.2693C6.8532 22.5288 6.99169 23.423 7.26891 23.9576C7.54612 24.492 8.00988 24.7617 8.66019 24.7617C9.21461 24.7617 9.74773 24.5925 10.2595 24.2539C10.7714 23.9151 11.1444 23.4864 11.3949 22.968V0.579102H17.7812V28.7676Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 127.20px; top: 3.20px; position: absolute">
                <svg width="20" height="39" viewBox="0 0 20 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.1441 5.29706H12.8004V38.7665H6.54733V5.29706H0.203613V0.195923H19.1441V5.29706Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 144.24px; top: 13.58px; position: absolute">
                <svg width="18" height="30" viewBox="0 0 18 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.5605 28.7676H12.5603L12.0059 25.3175H11.8674C10.508 27.921 8.47166 29.2226 5.75279 29.2226C3.87101 29.2226 2.47974 28.6088 1.58418 27.3866C0.688618 26.1588 0.240723 24.2433 0.240723 21.6399V0.584389H6.63249V21.2693C6.63249 22.5288 6.77099 23.423 7.0482 23.9576C7.32541 24.492 7.78917 24.7617 8.4397 24.7617C8.9939 24.7617 9.52702 24.5925 10.0388 24.2539C10.5506 23.9151 10.9237 23.4864 11.1742 22.968V0.579102H17.5605V28.7676Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 165.42px; top: 1.77px; position: absolute">
                <svg width="19" height="42" viewBox="0 0 19 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.7136 17.0871C17.3244 15.3091 16.7006 14.0232 15.8372 13.2242C14.9736 12.4252 13.7847 12.0283 12.2708 12.0283C11.0981 12.0283 9.99986 12.3564 8.98167 13.0178C7.96348 13.6793 7.17446 14.5418 6.62003 15.616H6.57221V0.7677H0.415039V40.7618H5.69252L6.34282 38.0949H6.48155C6.97727 39.0473 7.71823 39.7934 8.70446 40.3491C9.69069 40.8995 10.7889 41.1747 11.9936 41.1747C14.1526 41.1747 15.7465 40.1851 16.7647 38.2112C17.7829 36.2321 18.2945 33.1471 18.2945 28.9457V24.4848C18.2945 21.3362 18.0973 18.8651 17.7136 17.0871ZM11.8549 28.5857C11.8549 30.6388 11.7697 32.2476 11.599 33.4117C11.4285 34.5759 11.1459 35.4068 10.7408 35.8935C10.341 36.3856 9.79723 36.629 9.12017 36.629C8.59249 36.629 8.10742 36.5073 7.65953 36.2587C7.21186 36.0152 6.84943 35.6449 6.57221 35.158V19.1614C6.78528 18.3941 7.1586 17.7697 7.68628 17.2776C8.20875 16.7855 8.78448 16.542 9.39739 16.542C10.0479 16.542 10.5489 16.796 10.9009 17.2988C11.2579 17.8067 11.5031 18.6534 11.6418 19.8493C11.7803 21.0452 11.8497 22.7438 11.8497 24.9504V28.5857H11.8549Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 186.11px; top: 13.04px; position: absolute">
                <svg width="18" height="30" viewBox="0 0 18 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.36187 18.2102C6.36187 20.0199 6.41514 21.3746 6.5219 22.2796C6.62844 23.1843 6.85237 23.8406 7.19351 24.2587C7.53464 24.6713 8.05711 24.8776 8.76612 24.8776C9.72039 24.8776 10.3813 24.5073 10.7331 23.7718C11.0904 23.0362 11.2823 21.8086 11.3143 20.0941L16.8264 20.4168C16.8583 20.6602 16.8742 20.9989 16.8742 21.4275C16.8742 24.031 16.1545 25.9783 14.7207 27.2642C13.2868 28.5501 11.2556 29.1956 8.63284 29.1956C5.48241 29.1956 3.27536 28.2166 2.01192 26.2535C0.743266 24.2904 0.114258 21.2583 0.114258 17.1518V12.2306C0.114258 8.00268 0.770017 4.91237 2.08128 2.96506C3.39278 1.01773 5.63699 0.0440674 8.81939 0.0440674C11.0103 0.0440674 12.6949 0.440945 13.8677 1.23999C15.0405 2.03901 15.8667 3.27725 16.3465 4.96529C16.8264 6.65331 17.0662 8.98163 17.0662 11.9554V16.7815H6.36187V18.2102ZM7.1722 4.92826C6.84693 5.32511 6.63388 5.97599 6.5219 6.88085C6.41514 7.78573 6.36187 9.15625 6.36187 10.9978V13.0192H11.0371V10.9978C11.0371 9.188 10.9732 7.81748 10.8506 6.88085C10.7279 5.94424 10.504 5.28807 10.1787 4.90179C9.85367 4.5208 9.35251 4.32501 8.67545 4.32501C7.99319 4.33029 7.49203 4.53138 7.1722 4.92826Z"
                        fill="black" />
                </svg>
            </div>
        </a>
        <div data-svg-wrapper data-layer="Vector 2" class="Vector2"
            style="left: 1232.97px; top: 327.43px; position: absolute">
            <svg width="5" height="3" viewBox="0 0 5 3" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.96631 1.42566H3.20105" stroke="white" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>


        <div data-layer="Line 5" class="Line5"
            style="width: <?= 1038 + $offset ?>px; height: 0px; left: 190px; top: 199px; position: absolute; transform: rotate(90deg); transform-origin: top left; outline: 1px black solid; outline-offset: -0.50px">
        </div>

        <div data-layer="Total" class="Total"
            style="width: 402px; left: 584px; top: <?= 1180 + $offset ?>px; position: absolute; color: black; font-size: 24px; font-family: Roboto Slab; font-weight: 700;">
            Total
        </div>

        <div data-layer="TotalViews" class="TotalViews"
            style="width: 402px; left: 1035px; top: <?= 1180 + $offset ?>px; position: absolute; color: black; font-size: 24px; font-family: Roboto Slab; font-weight: 700;">
            <?= $totalViews ?>
        </div>
        <div data-layer="TotalLike" class="TotalLike"
            style="width: 402px; left: 1273px; top: <?= 1180 + $offset ?>px; position: absolute; color: black; font-size: 24px; font-family: Roboto Slab; font-weight: 700;">
            <?= $totalLikes ?>
        </div>
        <div data-layer="TotalComments" class="TotalComments"
            style="width: 402px; left: 1380px; top: <?= 1180 + $offset ?>px; position: absolute; color: black; font-size: 24px; font-family: Roboto Slab; font-weight: 700;">
            <?= number_format($totalComments) ?>
        </div>
        <div data-layer="TotalWatchTime" class="TotalWatchTime"
            style="width: 402px; left: 1160px; top: <?= 1180 + $offset ?>px; position: absolute; color: black; font-size: 24px; font-family: Roboto Slab; font-weight: 700;">
            <?= number_format($totalWatchTime, 1) ?>
        </div>


        <div data-layer="Line 6" class="Line6"
            style="width: 1316px; height: 0px; left: 190px; top: 297px; position: absolute; outline: 1px black solid; outline-offset: -0.50px">
        </div>
        <!-- Line 7: Top bertambah -->
        <div data-layer="Line 7" class="Line7"
            style="width: 1316px; height: 0px; left: 191px; top: <?= 1156 + $offset ?>px; position: absolute; outline: 1px black solid; outline-offset: -0.50px">
        </div>
        <div data-layer="Line 8" class="Line8"
            style="width: 1316px; height: 0px; left: 190px; top: 200px; position: absolute; outline: 1px black solid; outline-offset: -0.50px">
        </div>
        <!-- Line 9: Top bertambah -->
        <div data-layer="Line 9" class="Line9"
            style="width: 1316px; height: 0px; left: 191px; top: <?= 1237 + $offset ?>px; position: absolute; outline: 1px black solid; outline-offset: -0.50px">
        </div>

        <a href="?sort=tanggal"
            style="width: 126px; height: 28px; left: 580px; top: 237px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 20px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;<?= htmlspecialchars($isActiveT) ?>">
            Tanggal</a>

        <a href="?sort=views"
            style="position: absolute; top: 237px; left: 980px; width: 126px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-family: Roboto; font-weight: 400; text-decoration: none; color: black; <?= htmlspecialchars($isActiveV) ?>">
            Views
        </a>

        <a href="?sort=likes"
            style="width: 126px; height: 28px; left: 1273px; top: 235px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 20px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;<?= htmlspecialchars($isActiveL) ?>">
            Like</a>

        <a href="?sort=comments"
            style="width: 126px; height: 28px; left: 1361px; top: 233px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 20px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;<?= htmlspecialchars($isActiveC) ?>">
            Comments</a>
        <a href="?sort=watchtime"
            style="width: 126px; left: 1116px; top: 241px; position: absolute; text-align: center; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 20px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;<?= htmlspecialchars($isActiveW) ?>">
            Watch
            Time<br />(Hours)</a>
        <div data-layer="Last 28 days" class="Last28Days"
            style="width: 126px; height: 28px; left: 230px; top: 235px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 20px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Top <?= ($totalVideos) ?></div>
        <?php
        $top = 340; // Posisi awal top
        $increment = 208; // Jarak antar elemen
        
        foreach ($videos as $video) {
            // Hitung jumlah views, watch time, like, dan komentar
            // Misalnya, Anda memiliki fungsi untuk mendapatkan data tersebut
            $views = getViews($video['idVideo']);
            $watchTime = getWatchTime($video['idVideo']);
            $likes = getLikes($video['idVideo']);
            $comments = getComments($video['idVideo']);
            ?>
            <!-- Thumbnail -->
            <div class="Rectangle27"
                style="width: 320px; height: 165px; left: 231px; top: <?= $top ?>px; position: absolute; background: #E179CF; border-radius: 20px">
                <img src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="Thumbnail"
                    style="width: 100%; height: 100%; border-radius: 20px;">
            </div>

            <!-- Judul -->
            <div class="Judul"
                style="width: 402px; left: 584px; top: <?= $top + 49 ?>px; position: absolute; color: black; font-size: 24px; font-family: Roboto Slab; font-weight: 700;">
                <?= htmlspecialchars($video['title']) ?>
            </div>

            <!-- Tanggal Upload -->
            <div class="TanggalUpload"
                style="width: 200px; left: 584px; top: <?= $top + 80 ?>px; position: absolute; color: black; font-size: 16px; font-family: Roboto; ">
                <?= htmlspecialchars($video['uploaded_at_formatted']) ?>
            </div>

            <!-- Views -->
            <div class="ViewsInVideo"
                style="width: 126px; height: 28px; left: 1041px; top: <?= $top + 69 ?>px; position: absolute; color: black; font-size: 20px; font-family: Roboto;">
                <?= number_format($views) ?>
            </div>

            <!-- Watch Time -->
            <div class="WatchHour"
                style="width: 126px; height: 28px; left: 1159px; top: <?= $top + 69 ?>px; position: absolute; color: black; font-size: 20px; font-family: Roboto;">
                <?= number_format($watchTime, 1) ?>
            </div>

            <!-- Likes -->
            <div class="Like"
                style="width: 126px; height: 28px; left: 1275px; top: <?= $top + 69 ?>px; position: absolute; color: black; font-size: 20px; font-family: Roboto; ">
                <?= number_format($likes) ?>
            </div>

            <?php if ($RoleName == "Owner" || $RoleName == "Manager" || $RoleName == 'Admin'): ?>
                <!-- Komentar -->
                <a href="KomenFilter.php?id=<?= htmlspecialchars($video['idVideo']) ?>" class="Komentar"
                    style="width: 126px; height: 28px; left: 1384px; top: <?= $top + 69 ?>px; position: absolute; color: black; font-size: 20px; font-family: Roboto; ">
                    <?= number_format($comments) ?>
                </a>
            <?php else: ?>
                <div class="Komentar"
                    style="width: 126px; height: 28px; left: 1384px; top: <?= $top + 69 ?>px; position: absolute; color: black; font-size: 20px; font-family: Roboto; ">
                    <?= number_format($comments) ?>
                </div>

            <?php endif; ?>

            <?php if ($RoleName == "Owner" || $RoleName == "Manager" || $RoleName == 'Editor' || $RoleName == 'Subtitle Editor'): ?>
                <!-- Tombol Edit -->
                <div class='Edit03' style='left: 1457px; top: <?= $top + 57 ?>px; position: absolute'>
                    <a href='EditKonten.php?id=<?= htmlspecialchars(string: $video['idVideo']) ?>'>
                        <svg width='43' height='43' viewBox='0 0 43 43' fill='none' xmlns='http://www.w3.org/2000/svg'>
                            <path fill-rule='evenodd' clip-rule='evenodd'
                                d='M24.866 6.6419C26.965 4.54283 30.3683 4.54283 32.4674 6.6419L36.3581 10.5326C38.4572 12.6317 38.4572 16.035 36.3581 18.134L17.3919 37.1002C17.0559 37.4362 16.6002 37.625 16.125 37.625H7.16667C6.17716 37.625 5.375 36.8228 5.375 35.8333V26.875C5.375 26.3998 5.56376 25.9441 5.89977 25.6081L24.866 6.6419ZM29.9336 9.1757C29.2339 8.47601 28.0995 8.47601 27.3998 9.1757L25.8255 10.75L32.25 17.1745L33.8243 15.6002C34.524 14.9005 34.524 13.7661 33.8243 13.0664L29.9336 9.1757ZM29.7162 19.7083L23.2917 13.2838L8.95833 27.6171V34.0417H15.3829L29.7162 19.7083Z'
                                fill='#858080' />
                        </svg>
                    </a>
                </div>
            <?php endif; ?>

            <?php
            $top += $increment;
        }
        ?>


        <div data-layer="Rectangle 21" class="Rectangle21"
            style="width: 145px; height: 52px; left: 1322px; top: <?= 1318 + $offset ?>px; position: absolute; background: #795757; border-radius: 23px">
        </div>
        <!-- Add Video Button: Top bertambah -->
        <a href="upload.php"
            style="width: 146px; height: 15px; left: 1321px; top: <?= 1336 + $offset ?>px; position: absolute; text-align: center; justify-content: center; display: flex; flex-direction: column; color: #FFF3F3; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Add Video</a>
    </div>
</body>

</html>