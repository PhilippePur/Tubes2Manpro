<?php
require_once 'testsql.php';
session_start();

// --- Bagian Penanganan Penghapusan Komentar (Soft Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idKomen'])) {
    $idKomen = $_POST['idKomen'];

    if (!filter_var($idKomen, FILTER_VALIDATE_INT)) {
        die("ID Komentar tidak valid atau kosong.");
    }

    $sqlUpdate = "UPDATE Komen SET isActive = 0 WHERE idKomen = ?";
    $paramsUpdate = [$idKomen];

    $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);

    if ($stmtUpdate === false) {
        die("Error saat mengubah status komentar: " . print_r(sqlsrv_errors(), true));
    } else {
        // Tetap berada di halaman dengan query string yang sama
        header("Location: KomenFilter.php" . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
        exit();
    }
}

//  Bagian Filter dan Pengambilan Data Komentar 
$keyword = $_GET['keyword'] ?? '';
$date = $_GET['date'] ?? '';
$channelID = $_GET['idChannel'] ?? '';
$videoId = $_GET['id'] ?? ''; // ID video dari dashboard.php

if (!empty($videoId) && filter_var($videoId, FILTER_VALIDATE_INT)) {
    $sqlVideo = "SELECT title AS videoTitle, thumbnail, uploaded_at FROM Videos WHERE idVideo = ?";
    $stmtVideo = sqlsrv_query($conn, $sqlVideo, [$videoId]);

    if ($stmtVideo && $videoRow = sqlsrv_fetch_array($stmtVideo, SQLSRV_FETCH_ASSOC)) {
        $videoTitle = $videoRow['videoTitle'];
        $videoThumb = $videoRow['thumbnail'];
        $videoDate = $videoRow['uploaded_at']->format('F d, Y');
        $totalComments = getComments($videoId);
    } else {
        // Video tidak ditemukan
        $videoTitle = 'Judul tidak ditemukan';
        $videoThumb = 'Assets/default-thumb.jpg';
        $videoDate = '';
        $totalComments = 0;
    }
}

$sql = "
SELECT
    K.idKomen,
    K.komen,
    K.tanggal,
    U.Username AS userUsername,
    U.fotoProfil AS userFotoProfil
FROM Komen K
JOIN Users U ON K.idUser = U.idUser
WHERE K.isActive = 1
";

$params = [];

if (!empty($keyword)) {
    $sql .= " AND K.komen LIKE ?";
    $params[] = "%$keyword%";
}

if (!empty($date)) {
    $sql .= " AND CONVERT(DATE, K.tanggal) = ?";
    $params[] = $date;
}

if (!empty($videoId)) {
    $sql .= " AND K.idVideo = ?";
    $params[] = $videoId;
}

$sql .= " ORDER BY K.tanggal DESC";

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Error saat mengambil komentar: " . print_r(sqlsrv_errors(), true));
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

$offset = $totalComments * 250;

    ?>

<!DOCTYPE html>
<html>

<head>
    <title>Filter Komen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .KomenFilter {
            width: 1512px;
            min-height: <?= 1000 + $offset ?>px  ;
            /* Gunakan min-height agar bisa memanjang */
            position: relative;
            background: white;
            overflow: hidden;
            padding-bottom: 50px;
            /* Ruang di bawah untuk konten dinamis */
        }

        .YoutubeLogo {
            width: 381px;
            height: 84px;
            left: 36px;
            top: 77px;
            position: absolute;
            overflow: hidden
        }

        .Line5 {
            width: 931px;
            height: 0px;
            left: 397px;
            top: 229px;
            position: absolute;
            transform: rotate(90deg);
            transform-origin: top left;
            outline: 1px black solid;
            outline-offset: -0.50px
        }

        .Line6 {
            width: 1512px;
            height: 0px;
            left: 0px;
            top: 229px;
            position: absolute;
            outline: 1px black solid;
            outline-offset: -0.50px
        }

        .Rectangle27 {
            width: 320px;
            height: 165px;
            left: 36px;
            top: 274px;
            position: absolute;
            background: #E179CF;
            border-radius: 20px
        }

        /* Style untuk konten statis lainnya di sidebar kiri */
        .LoremIpsumDeskripsi {
            width: 126px;
            height: 28px;
            left: 47px;
            top: 530px;
            position: absolute;
            justify-content: center;
            display: flex;
            flex-direction: column;
            color: black;
            font-size: 20px;
            font-family: Roboto;
            font-weight: 400;
            line-height: 16px;
            letter-spacing: 0.40px;
            word-wrap: break-word
        }

        .jumlahKomen {
            width: 402px;
            left: 47px;
            top: 530px;
            position: absolute;
            justify-content: center;
            display: flex;
            flex-direction: column;
            color: black;
            font-size: 20px;
            font-family: Roboto Slab;
            font-weight: 700;
            line-height: 16px;
            letter-spacing: 0.40px;
            word-wrap: break-word
        }


        .Konten1IniCeritanyaJudul {
            width: 402px;
            left: 47px;
            top: 469px;
            position: absolute;
            justify-content: center;
            display: flex;
            flex-direction: column;
            color: black;
            font-size: 20px;
            font-family: Roboto Slab;
            font-weight: 700;
            line-height: 16px;
            letter-spacing: 0.40px;
            word-wrap: break-word
        }

        .May242025 {
            width: 126px;
            height: 28px;
            left: 47px;
            top: 492px;
            position: absolute;
            justify-content: center;
            display: flex;
            flex-direction: column;
            color: black;
            font-size: 20px;
            font-family: Roboto;
            font-weight: 400;
            line-height: 16px;
            letter-spacing: 0.40px;
            word-wrap: break-word
        }

        .Mainprofile {
            width: 120px;
            height: 120px;
            left: 1308px;
            top: 58px;
            position: absolute;
            border-radius: 200px
        }

        /* Gaya dasar untuk card komentar dinamis */
        .comment-card {
            width: 988px;
            height: 155px;
            background: #D9D9D9;
            border-radius: 35px;
            position: absolute;
            box-sizing: border-box;
            padding: 20px;
            left: 463px;
            /* Posisi X komentar */
            /* top akan diatur dinamis oleh PHP */
        }

        .comment-card .User-icon {
            position: absolute;
            left: 22px;
            top: 19px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .comment-card .Comment-User {
            position: absolute;
            left: 93px;
            top: 29px;
            color: black;
            font-size: 20px;
            font-family: Roboto;
            font-weight: 400;
            line-height: 16px;
            letter-spacing: 0.40px;
            word-wrap: break-word;
        }

        .comment-card .Comment-Date {
            position: absolute;
            left: 93px;
            top: 50px;
            color: black;
            font-size: 20px;
            font-family: Roboto;
            font-weight: 400;
            line-height: 16px;
            letter-spacing: 0.40px;
            word-wrap: break-word;
        }

        .comment-card .Comment-Text {
            position: absolute;
            width: 943px;
            height: 66px;
            left: 22px;
            top: 82px;
            color: black;
            font-size: 16px;
            font-family: Roboto;
            font-weight: 400;
            line-height: 1.4;
            letter-spacing: 0.40px;
            word-wrap: break-word;
            overflow: hidden;
            /* Hide teks panjang */
            text-overflow: ellipsis;
        }

        .comment-card .Remove-button-container {
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .Remove-button-container button {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div data-layer="Komen Filter" class="KomenFilter">
        <a href="homepage.php" data-layer="Youtube-Logo" class="YoutubeLogo">
            <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 0px; top: 0px; position: absolute">
                <svg width="121" height="84" viewBox="0 0 121 84" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M118.418 13.1176C117.024 7.95157 112.932 3.89182 107.725 2.50894C98.2967 7.51018e-07 60.4732 0 60.4732 0C60.4732 0 22.6501 7.51018e-07 13.2217 2.50894C8.01467 3.89182 3.9227 7.95157 2.52885 13.1176C7.56979e-07 22.4718 0 42 0 42C0 42 7.56979e-07 61.5283 2.52885 70.8826C3.9227 76.0486 8.01467 80.1083 13.2217 81.4909C22.6501 84 60.4732 84 60.4732 84C60.4732 84 98.2967 84 107.725 81.4909C112.932 80.1083 117.024 76.0486 118.418 70.8826C120.947 61.5283 120.947 42 120.947 42C120.947 42 120.937 22.4718 118.418 13.1176Z"
                        fill="#FF0000" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 48.37px; top: 24px; position: absolute">
                <svg width="32" height="36" viewBox="0 0 32 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.367188 35.9987L31.7887 18.0017L0.367188 0.00439453V35.9987Z" fill="white" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 132.90px; top: 5.96px; position: absolute">
                <svg width="40" height="73" viewBox="0 0 40 73" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M14.4834 49.6151L0.90332 0.95752H12.7511L17.5103 23.0144C18.7248 28.4471 19.6108 33.0798 20.1883 36.9123H20.5367C20.935 34.1663 21.8312 29.5633 23.2151 23.0935L28.1431 0.95752H39.991L26.2415 49.6151V72.9562H14.4737V49.6151H14.4834Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 168.93px; top: 24.33px; position: absolute">
                <svg width="34" height="55" viewBox="0 0 34 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M7.55508 52.4135C5.16579 50.8133 3.46314 48.324 2.44756 44.9459C1.44214 41.5678 0.93457 37.0835 0.93457 31.4727V23.8371C0.93457 18.1772 1.51199 13.6238 2.66685 10.1962C3.8217 6.76865 5.62383 4.2597 8.07281 2.68915C10.5222 1.1186 13.7379 0.328369 17.7206 0.328369C21.6432 0.328369 24.7792 1.12847 27.1486 2.72867C29.5083 4.32883 31.2406 6.83778 32.3357 10.2357C33.4309 13.6435 33.9787 18.1772 33.9787 23.8371V31.4727C33.9787 37.0835 33.4411 41.5876 32.376 44.9854C31.3104 48.3933 29.5781 50.8826 27.1884 52.453C24.7991 54.0238 21.5534 54.8138 17.4615 54.8138C13.24 54.8235 9.9448 54.0137 7.55508 52.4135ZM20.9561 44.1756C21.6131 42.457 21.9518 39.6615 21.9518 35.7697V19.3826C21.9518 15.6092 21.6233 12.8434 20.9561 11.1049C20.2889 9.3566 19.1244 8.48737 17.4513 8.48737C15.8389 8.48737 14.6938 9.3566 14.0367 11.1049C13.3696 12.8533 13.0411 15.6092 13.0411 19.3826V35.7697C13.0411 39.6615 13.3594 42.4666 13.997 44.1756C14.6341 45.8943 15.7788 46.7536 17.4513 46.7536C19.1244 46.7536 20.2889 45.8943 20.9561 44.1756Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 208.17px; top: 25.35px; position: absolute">
                <svg width="33" height="54" viewBox="0 0 33 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M32.5182 52.9663H23.1795L22.144 46.526H21.8854C19.3462 51.3858 15.543 53.8155 10.4655 53.8155C6.95099 53.8155 4.35257 52.6698 2.67998 50.3883C1.00739 48.0964 0.170898 44.5209 0.170898 39.6611V0.357526H12.1085V38.9693C12.1085 41.3205 12.3671 42.9896 12.8849 43.9875C13.4026 44.985 14.2687 45.4886 15.4833 45.4886C16.5188 45.4886 17.5144 45.1727 18.4703 44.5406C19.4262 43.9081 20.123 43.108 20.5908 42.1403V0.347656H32.5182V52.9663Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 237.57px; top: 5.97px; position: absolute">
                <svg width="36" height="73" viewBox="0 0 36 73" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M35.9464 10.4878H24.0986V72.9642H12.4201V10.4878H0.572266V0.965698H35.9464V10.4878Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 269.39px; top: 25.35px; position: absolute">
                <svg width="33" height="54" viewBox="0 0 33 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M32.7385 52.9663H23.3997L22.3643 46.526H22.1056C19.5668 51.3858 15.7636 53.8155 10.6857 53.8155C7.17121 53.8155 4.57281 52.6698 2.90022 50.3883C1.22762 48.0964 0.391113 44.5209 0.391113 39.6611V0.357526H12.3287V38.9693C12.3287 41.3205 12.5874 42.9896 13.1051 43.9875C13.6228 44.985 14.489 45.4886 15.7039 45.4886C16.739 45.4886 17.7347 45.1727 18.6905 44.5406C19.6464 43.9081 20.3432 43.108 20.811 42.1403V0.347656H32.7385V52.9663Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 308.94px; top: 3.30px; position: absolute">
                <svg width="35" height="76" viewBox="0 0 35 76" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M33.2445 30.7625C32.5177 27.4436 31.3526 25.0433 29.7401 23.5518C28.1272 22.0603 25.9069 21.3194 23.0794 21.3194C20.8891 21.3194 18.8381 21.9318 16.9364 23.1666C15.0348 24.4013 13.5612 26.0113 12.5257 28.0165H12.4364V0.299683H0.937012V74.9553H10.7935L12.008 69.9771H12.2671C13.1929 71.7549 14.5768 73.1477 16.4187 74.1851C18.2606 75.2124 20.3117 75.726 22.5617 75.726C26.594 75.726 29.5708 73.8789 31.4724 70.1942C33.3741 66.4999 34.3295 60.7413 34.3295 52.8986V44.5717C34.3295 38.6943 33.9612 34.0814 33.2445 30.7625ZM22.3026 52.2266C22.3026 56.0591 22.1434 59.0621 21.8247 61.2352C21.5063 63.4083 20.9784 64.9593 20.2219 65.8678C19.4752 66.7863 18.4596 67.2408 17.1951 67.2408C16.2096 67.2408 15.3037 67.0136 14.4672 66.5495C13.6311 66.095 12.9542 65.4037 12.4364 64.4948V34.6346C12.8344 33.2023 13.5316 32.0368 14.5171 31.1181C15.4929 30.1995 16.5682 29.7451 17.7129 29.7451C18.9278 29.7451 19.8634 30.2192 20.5208 31.1577C21.1876 32.1059 21.6456 33.6863 21.9047 35.9187C22.1633 38.151 22.2929 41.3217 22.2929 45.4407V52.2266H22.3026Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 347.60px; top: 24.35px; position: absolute">
                <svg width="33" height="55" viewBox="0 0 33 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12.2645 34.259C12.2645 37.6371 12.364 40.1659 12.5634 41.8551C12.7624 43.5439 13.1806 44.7691 13.8177 45.5494C14.4549 46.3197 15.4306 46.7049 16.7548 46.7049C18.5371 46.7049 19.7715 46.0135 20.4285 44.6406C21.0957 43.2676 21.4543 40.9761 21.5139 37.7757L31.8086 38.3779C31.8683 38.8324 31.8979 39.4645 31.8979 40.2646C31.8979 45.1244 30.5538 48.7595 27.8758 51.1598C25.1978 53.5601 21.4043 54.7651 16.5059 54.7651C10.622 54.7651 6.50002 52.9377 4.14036 49.2732C1.77096 45.6087 0.596191 39.9487 0.596191 32.2833V23.0971C0.596191 15.2049 1.82092 9.43638 4.2699 5.8014C6.71931 2.16639 10.9107 0.348877 16.8543 0.348877C20.9462 0.348877 24.0925 1.08971 26.2828 2.58126C28.4731 4.07276 30.0162 6.38415 30.9124 9.53516C31.8086 12.6861 32.2565 17.0323 32.2565 22.5834V31.592H12.2645V34.259ZM13.7779 9.46603C13.1705 10.2068 12.7725 11.4218 12.5634 13.1109C12.364 14.8 12.2645 17.3583 12.2645 20.7959V24.5692H20.9962V20.7959C20.9962 17.4176 20.8768 14.8592 20.6478 13.1109C20.4188 11.3625 20.0005 10.1377 19.393 9.41663C18.786 8.70545 17.85 8.33996 16.5855 8.33996C15.3113 8.34983 14.3753 8.72519 13.7779 9.46603Z"
                        fill="black" />
                </svg>
            </div>
        </a>
        <div data-svg-wrapper data-layer="Vector 2" class="Vector2"
            style="left: 1221.97px; top: 177.43px; position: absolute">
            <svg width="5" height="3" viewBox="0 0 5 3" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.96631 1.42566H3.20105" stroke="white" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
        <div data-layer="Line 5" class="Line5"></div>
        <div data-layer="Line 6" class="Line6"></div>
        <div data-layer="Rectangle 27" class="Rectangle27"> <img src="<?= htmlspecialchars(string: $videoThumb) ?>"
                alt="Thumbnail" style="width: 100%; height: 100%; border-radius: 20px;"></div>
        <div data-layer="Judul" class="Konten1IniCeritanyaJudul"><?= htmlspecialchars($videoTitle) ?>
        </div>
        <div data-svg-wrapper data-layer="Rectangle 31" class="Rectangle31"
            style="left: 508px; top: 336px; position: absolute">
            <svg width="1" height="1" viewBox="0 0 1 1" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="1" height="1" fill="#D9D9D9" />
            </svg>
        </div>

        <div data-layer="TanggalUpload" class="May242025"><?= htmlspecialchars($videoDate) ?></div>

        <div data-layer="jumlahKomen" class="jumlahKomen"> Total Komen : <?= htmlspecialchars($totalComments) ?></div>
        <?php
        $currentTopPosition = 284;
        $verticalSpacing = 155 + 17;

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $idKomen = $row['idKomen'];
            $komenText = $row['komen'];
            $commentDate = $row['tanggal']->format('H:i  F d, Y');
            $userName = $row['userUsername'] ?? 'Unknown User';
            $userProfilePic = $row['userFotoProfil'] ?? 'Assets/NoProfile.jpg'; 
            ?>
            <div class="comment-card" style="top: <?php echo $currentTopPosition; ?>px;">

                <img class="User-icon" src="<?php echo htmlspecialchars($userProfilePic); ?>" alt="User Profile">

                <div class="Comment-User">
                    <?php echo htmlspecialchars($userName); ?>
                </div>

                <div class="Comment-Date">
                    <?php echo htmlspecialchars($commentDate); ?>
                </div>

                <div class="Comment-Text">
                    <?php echo htmlspecialchars($komenText); ?>
                </div>

                <div class="Remove-button-container">
                    <form action="KomenFilter.php" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                        <input type="hidden" name="idKomen" value="<?php echo htmlspecialchars($idKomen); ?>">
                        <button type="submit">
                            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="22" cy="22" r="16.5" stroke="#33363F" stroke-width="2" />
                                <path d="M13.75 22H30.25" stroke="#33363F" stroke-width="2" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <?php
            $currentTopPosition += $verticalSpacing;
        }
        ?>

    </div>
</body>

</html>