<?php
session_start();
require_once 'testsql.php'; // koneksi ke SQL server

$uid = $_SESSION['uid'];
$sql = "SELECT 
            C.fotoProfil, 
            A.idChannel 
        FROM Users U
        INNER JOIN [Admin] A ON A.idUser = U.idUser
        INNER JOIN Channel C ON C.idChannel = A.idChannel
        WHERE U.idUser = ?";


$params = [$uid];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt && sqlsrv_has_rows($stmt)) {
    $channelInfo = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    // akses seperti: $channelInfo['namaChannel'], $channelInfo['Email'], dst
} else {
    echo "Channel tidak ditemukan atau tidak ada admin terkait.";
}

$fotoProfil = $channelInfo['fotoProfil'];
$idChannel = $channelInfo['idChannel'];


// Tangani proses upload jika ada request POST dan file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $uploadDir = "uploads/";
    $filename = basename($_FILES["file"]["name"]);
    $targetFile = $uploadDir . $filename;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        $thumbnailPath = null;

        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbDir = 'thumbnails/';
            if (!file_exists($thumbDir)) {
                mkdir($thumbDir, 0777, true);
            }

            $thumbName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
            $thumbTarget = $thumbDir . $thumbName;

            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbTarget)) {
                $thumbnailPath = $thumbTarget;
            }
        }

        $videoTitle = $_POST['video_title'] ?? $filename;
        $videoDesc = $_POST['video_description'] ?? '';

        $sql = "INSERT INTO Videos (title, description, path, thumbnail, idChannel) VALUES (?, ?, ?, ?, ?)";
        $params = [$videoTitle, $videoDesc, $targetFile, $thumbnailPath, $idChannel];


        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            echo "<script>alert('Upload video berhasil & data disimpan ke database!'); window.location.href='homepage.php';</script>";
            exit;
        } else {
            echo "<script>alert('Upload video berhasil, TAPI GAGAL simpan ke database!'); window.location.href='upload.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Gagal mengunggah file. Mohon coba lagi.'); window.location.href='upload.php';</script>";
        exit;
    }
}
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Upload Konten</title>
</head>

<body>
    <div data-layer="Upload Konten" class="UploadKonten"
        style="width: 1512px; height: 1009px; position: relative; background: white; overflow: hidden">
        <div data-layer="Rectangle 8" class="Rectangle8"
            style="width: 1512px; height: 904px; left: 0px; top: 105px; position: absolute; background: #D9D9D9"></div>
        <div data-layer="Rectangle 9" class="Rectangle9"
            style="width: 537px; height: 77px; left: 196px; top: 160px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>
        <a href="homePage.php" data-layer="Youtube-Logo" class="YoutubeLogo"
            style="width: 204px; height: 45px; left: 23px; top: 33px; position: absolute; overflow: hidden">
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
                    <path d="M0.897461 20.142L17.7216 10.5008L0.897461 0.859375V20.142Z" fill="white" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 71.16px; top: 3.19px; position: absolute">
                <svg width="22" height="39" viewBox="0 0 22 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M7.43237 26.2582L0.161133 0.19165H6.50485L9.05304 12.0078C9.70335 14.9182 10.1778 17.4 10.4869 19.4532H10.6735C10.8868 17.9821 11.3666 15.5162 12.1076 12.0502L14.7462 0.19165H21.0899L13.728 26.2582V38.7624H7.42716V26.2582H7.43237Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 90.45px; top: 13.03px; position: absolute">
                <svg width="19" height="30" viewBox="0 0 19 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M3.99796 27.9359C2.71866 27.0787 1.807 25.7451 1.26323 23.9354C0.724896 22.1258 0.453125 19.7234 0.453125 16.7177V12.6272C0.453125 9.59507 0.762296 7.15574 1.38064 5.31953C1.99899 3.48335 2.96391 2.13927 4.27518 1.29791C5.58667 0.45654 7.30843 0.0332031 9.44091 0.0332031C11.5412 0.0332031 13.2203 0.461828 14.489 1.31908C15.7524 2.17631 16.68 3.52039 17.2663 5.34071C17.8527 7.16631 18.146 9.59507 18.146 12.6272V16.7177C18.146 19.7234 17.8582 22.1363 17.2879 23.9566C16.7174 25.7822 15.7898 27.1158 14.5103 27.9571C13.231 28.7986 11.4931 29.2218 9.30219 29.2218C7.04187 29.227 5.2775 28.7932 3.99796 27.9359ZM11.1733 23.5228C11.5251 22.6021 11.7064 21.1045 11.7064 19.0196V10.2408C11.7064 8.21936 11.5306 6.73769 11.1733 5.80637C10.8161 4.86976 10.1925 4.4041 9.29675 4.4041C8.43338 4.4041 7.82025 4.86976 7.46846 5.80637C7.11123 6.743 6.93533 8.21936 6.93533 10.2408V19.0196C6.93533 21.1045 7.1058 22.6073 7.44716 23.5228C7.78829 24.4435 8.40119 24.9038 9.29675 24.9038C10.1925 24.9038 10.8161 24.4435 11.1733 23.5228Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 111.46px; top: 13.58px; position: absolute">
                <svg width="18" height="30" viewBox="0 0 18 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.7817 28.7679H12.7815L12.227 25.3177H12.0886C10.729 27.9212 8.69263 29.2228 5.97399 29.2228C4.0922 29.2228 2.70092 28.609 1.80536 27.3868C0.909801 26.159 0.461914 24.2436 0.461914 21.6401V0.584633H6.85369V21.2695C6.85369 22.5291 6.99218 23.4232 7.26939 23.9578C7.54661 24.4922 8.01037 24.762 8.66067 24.762C9.2151 24.762 9.74822 24.5928 10.26 24.2542C10.7718 23.9153 11.1449 23.4867 11.3954 22.9683V0.579346H17.7817V28.7679Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 127.20px; top: 3.20px; position: absolute">
                <svg width="20" height="39" viewBox="0 0 20 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.1446 5.29718H12.8009V38.7666H6.54782V5.29718H0.204102V0.196045H19.1446V5.29718Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 144.24px; top: 13.58px; position: absolute">
                <svg width="18" height="30" viewBox="0 0 18 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.561 28.7679H12.5608L12.0063 25.3177H11.8678C10.5085 27.9212 8.47215 29.2228 5.75328 29.2228C3.8715 29.2228 2.48023 28.609 1.58467 27.3868C0.689106 26.159 0.241211 24.2436 0.241211 21.6401V0.584633H6.63297V21.2695C6.63297 22.5291 6.77147 23.4232 7.04869 23.9578C7.3259 24.4922 7.78966 24.762 8.44019 24.762C8.99439 24.762 9.52751 24.5928 10.0393 24.2542C10.5511 23.9153 10.9242 23.4867 11.1747 22.9683V0.579346H17.561V28.7679Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 165.42px; top: 1.77px; position: absolute">
                <svg width="19" height="42" viewBox="0 0 19 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.7136 17.0872C17.3244 15.3092 16.7006 14.0233 15.8372 13.2243C14.9736 12.4253 13.7847 12.0284 12.2708 12.0284C11.0981 12.0284 9.99986 12.3565 8.98167 13.0179C7.96348 13.6794 7.17446 14.5419 6.62003 15.6161H6.57221V0.767822H0.415039V40.7619H5.69252L6.34282 38.095H6.48155C6.97727 39.0474 7.71823 39.7935 8.70446 40.3493C9.69069 40.8996 10.7889 41.1748 11.9936 41.1748C14.1526 41.1748 15.7465 40.1852 16.7647 38.2113C17.7829 36.2322 18.2945 33.1472 18.2945 28.9458V24.485C18.2945 21.3364 18.0973 18.8652 17.7136 17.0872ZM11.8549 28.5858C11.8549 30.6389 11.7697 32.2477 11.599 33.4118C11.4285 34.576 11.1459 35.4069 10.7408 35.8936C10.341 36.3857 9.79723 36.6291 9.12017 36.6291C8.59249 36.6291 8.10742 36.5074 7.65953 36.2588C7.21186 36.0153 6.84943 35.645 6.57221 35.1581V19.1615C6.78528 18.3942 7.1586 17.7698 7.68628 17.2777C8.20875 16.7856 8.78448 16.5422 9.39739 16.5422C10.0479 16.5422 10.5489 16.7962 10.9009 17.2989C11.2579 17.8069 11.5031 18.6535 11.6418 19.8494C11.7803 21.0453 11.8497 22.7439 11.8497 24.9505V28.5858H11.8549Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 186.12px; top: 13.04px; position: absolute">
                <svg width="18" height="30" viewBox="0 0 18 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.36285 18.2103C6.36285 20.02 6.41612 21.3747 6.52288 22.2797C6.62941 23.1844 6.85335 23.8407 7.19448 24.2588C7.53562 24.6714 8.05809 24.8778 8.7671 24.8778C9.72137 24.8778 10.3823 24.5074 10.7341 23.7719C11.0913 23.0364 11.2833 21.8088 11.3153 20.0943L16.8274 20.4169C16.8593 20.6604 16.8752 20.999 16.8752 21.4276C16.8752 24.0311 16.1555 25.9785 14.7216 27.2643C13.2877 28.5502 11.2566 29.1957 8.63382 29.1957C5.48338 29.1957 3.27634 28.2168 2.0129 26.2536C0.744243 24.2905 0.115234 21.2584 0.115234 17.1519V12.2307C0.115234 8.0028 0.770993 4.91249 2.08226 2.96518C3.39375 1.01785 5.63796 0.0441895 8.82036 0.0441895C11.0113 0.0441895 12.6959 0.441067 13.8687 1.24011C15.0415 2.03913 15.8677 3.27737 16.3475 4.96541C16.8274 6.65343 17.0672 8.98175 17.0672 11.9556V16.7816H6.36285V18.2103ZM7.17318 4.92838C6.84791 5.32523 6.63485 5.97611 6.52288 6.88097C6.41612 7.78585 6.36285 9.15637 6.36285 10.998V13.0194H11.0381V10.998C11.0381 9.18812 10.9742 7.8176 10.8515 6.88097C10.7289 5.94436 10.505 5.2882 10.1797 4.90192C9.85464 4.52092 9.35348 4.32513 8.67643 4.32513C7.99416 4.33042 7.493 4.5315 7.17318 4.92838Z"
                        fill="black" />
                </svg>
            </div>
        </a>
        <div data-svg-wrapper data-layer="Vector 2" class="Vector2"
            style="left: 1401px; top: 149px; position: absolute">
            <svg width="3" height="2" viewBox="0 0 3 2" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1H2" stroke="white" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>

        <img data-layer="MainProfile" class="Mainprofile"
            style="width: 140px; height: 140px; left: 23px; top: 128px; position: absolute; border-radius: 200px"
            src="<?= htmlspecialchars($fotoProfil) ?>" alt="Foto Profil Utama">

        <div data-layer="Rectangle 10" class="Rectangle10"
            style="width: 1172px; height: 322px; left: 197px; top: 268px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>
        <div data-layer="Rectangle 11" class="Rectangle11"
            style="width: 1172px; height: 282px; left: 199px; top: 600px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>

        <div data-layer="Upload" class="Upload"
            style="width: 136.50px; height: 24.62px; left: 1218.43px; top: 927.28px; position: absolute; text-align: center; justify-content: center; display: flex; flex-direction: column; color: #FFF3F3; font-size: 32px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Upload</div>
        <div data-layer="Deskripsi" class="Deskripsi"
            style="width: 165px; height: 28px; left: 235px; top: 291.63px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 32px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Deskripsi</div>
        <div data-layer="Thumbnail" class="Thumbnail"
            style="width: 165px; height: 28px; left: 911px; top: 630px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 32px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Thumbnail</div>
        <div data-layer="Video" class="Video"
            style="width: 165px; height: 28px; left: 221px; top: 630px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 32px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Video</div>

        <div data-layer="Line 3" class="Line3"
            style="width: 1173px; height: 0px; left: 196px; top: 331.63px; position: absolute; outline: 1px white solid; outline-offset: -0.50px">
        </div>
        <div data-layer="Line 4" class="Line4"
            style="width: 279px; height: 0px; left: 894px; top: 616px; position: absolute; transform: rotate(90deg); transform-origin: top left; outline: 1px white solid; outline-offset: -0.50px">
        </div>
        <div data-layer="Rectangle 15" class="Rectangle15"
            style="width: 330px; height: 176px; left: 967px; top: 681px; position: absolute; background: #D9D9D9"></div>
        <?php
        require_once 'testsql.php'; // koneksi ke SQL Server
        
        // Cek apakah form disubmit
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
            $uploadDir = "uploads/";
            $filename = basename($_FILES["file"]["name"]);
            $targetFile = $uploadDir . $filename;

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
                $videoTitle = $filename;
                $sql = "INSERT INTO Videos (title, path) VALUES (?, ?)";
                $params = [$videoTitle, $targetFile];
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt) {
                    echo "<script>alert('Upload video berhasil & data disimpan ke database!'); window.location.href='homepage.php';</script>";
                } else {
                    echo "<script>alert('Upload video berhasil, TAPI GAGAL simpan ke database! Error: " . implode(" ", sqlsrv_errors()) . "'); window.location.href='upload.php';</script>";
                }
            } else {
                echo "<script>alert('Gagal mengunggah file. Mohon coba lagi.'); window.location.href='upload.php';</script>";
            }
        }
        ?>

        <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="file" name="file" id="fileInput" accept="video/*" style="display:none;" required>

            <!-- Video -->
            <div class="Rectangle16"
                style="width: 330px; height: 176px; left: 225px; top: 681px; position: absolute; background: #D9D9D9; cursor: pointer;"
                onclick="document.getElementById('fileInput').click();">
                <span
                    style="position: absolute; top: 90%; left: 50%; transform: translate(-50%, -50%); font-size: 20px; color: #555;">
                    Pilih File Video
                </span>
            </div>

            <!-- Rectangle 15: Pilih Gambar Thumbnail -->
            <div class="Rectangle15"
                style="width: 330px; height: 176px; left: 967px; top: 681px; position: absolute; background: #D9D9D9; cursor: pointer;"
                onclick="document.getElementById('thumbnailInput').click();">
                <span id="thumbnailText"
                    style="position: absolute; top: 90%; left: 50%; transform: translate(-50%, -50%); font-size: 20px; color: #555;">
                    Pilih Thumbnail
                </span>
                <img id="thumbnailPreview" style="width: 100%; height: 100%; display: none; object-fit: cover;" />
            </div>

            <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" style="display: none;"
                onchange="previewThumbnail(event)">

            <input type="text" name="video_title" placeholder="Isi Judul" style="
        width: 490px;
        height: 39px;
        left: 227px;
        top: 179px;
        position: absolute;
        color: black;
        font-size: 20px;               /* Ukuran font tidak terlalu besar */
        font-family: Roboto;
        font-weight: 500;
        line-height: 39px;             /* Sama dengan tinggi input agar teks vertikal tengah */
        letter-spacing: 0.40px;
        padding: 0 10px;               /* Padding kiri-kanan agar tidak mentok */
        border: 1px solid #ccc;
        box-sizing: border-box;
        text-align: left;              /* Teks rata kiri */
        vertical-align: middle;        /* Untuk berjaga-jaga */
        overflow: hidden;              /* Sembunyikan teks jika terlalu panjang */
        white-space: nowrap;           /* Hindari teks meluber ke bawah */
    ">


            <textarea name="video_description" placeholder="Isi Deskripsi untuk menjelaskan video"
                style="width: 1097px; height: 195px; left: 235px; top: 354.63px; position: absolute; color: black; font-size: 16px; font-family: Roboto; font-weight: 300; line-height: 16px; letter-spacing: 0.40px; padding: 10px; border: 1px solid #ccc; box-sizing: border-box;"></textarea>

            <button type="submit" class="Rectangle5"
                style="width: 166.18px; height: 68px; left: 1203px; top: 905px; position: absolute; background: #795757; border-radius: 23px; border: none; color: white; font-size: 24px; cursor: pointer;">
                Upload Video
            </button>
        </form>



        <div data-layer="add-square" class="AddSquare"
            style="width: 89px; height: 89px; left: 1087px; top: 729px; position: absolute; overflow: hidden">
            <div data-svg-wrapper data-layer="union-1" class="Union1"
                style="left: 7.42px; top: 7.42px; position: absolute">
                <svg width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.416992 18.9584C0.416992 8.71814 8.71838 0.416748 18.9587 0.416748H56.042C66.2823 0.416748 74.5837 8.71814 74.5837 18.9584V56.0417C74.5837 66.282 66.2823 74.5834 56.042 74.5834H18.9587C8.71838 74.5834 0.416992 66.282 0.416992 56.0417V18.9584ZM18.9587 7.83341C12.8145 7.83341 7.83366 12.8142 7.83366 18.9584V56.0417C7.83366 62.1859 12.8145 67.1667 18.9587 67.1667H56.042C62.1862 67.1667 67.167 62.1859 67.167 56.0417V18.9584C67.167 12.8142 62.1862 7.83341 56.042 7.83341H18.9587ZM37.5003 22.6667C39.5484 22.6667 41.2087 24.327 41.2087 26.3751V33.7917H48.6253C50.6734 33.7917 52.3337 35.452 52.3337 37.5001C52.3337 39.5481 50.6734 41.2084 48.6253 41.2084H41.2087V48.6251C41.2087 50.6731 39.5484 52.3334 37.5003 52.3334C35.4523 52.3334 33.792 50.6731 33.792 48.6251V41.2084H26.3753C24.3273 41.2084 22.667 39.5481 22.667 37.5001C22.667 35.452 24.3273 33.7917 26.3753 33.7917H33.792V26.3751C33.792 24.327 35.4523 22.6667 37.5003 22.6667Z"
                        fill="white" />
                </svg>
            </div>
        </div>
        <div data-layer="add-square" class="AddSquare"
            style="width: 89px; height: 89px; left: 345px; top: 725px; position: absolute; overflow: hidden">
            <div data-svg-wrapper data-layer="union-1" class="Union1"
                style="left: 7.42px; top: 7.42px; position: absolute">
                <svg width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.416992 18.9584C0.416992 8.71814 8.71838 0.416748 18.9587 0.416748H56.042C66.2823 0.416748 74.5837 8.71814 74.5837 18.9584V56.0417C74.5837 66.282 66.2823 74.5834 56.042 74.5834H18.9587C8.71838 74.5834 0.416992 66.282 0.416992 56.0417V18.9584ZM18.9587 7.83341C12.8145 7.83341 7.83366 12.8142 7.83366 18.9584V56.0417C7.83366 62.1859 12.8145 67.1667 18.9587 67.1667H56.042C62.1862 67.1667 67.167 62.1859 67.167 56.0417V18.9584C67.167 12.8142 62.1862 7.83341 56.042 7.83341H18.9587ZM37.5003 22.6667C39.5484 22.6667 41.2087 24.327 41.2087 26.3751V33.7917H48.6253C50.6734 33.7917 52.3337 35.452 52.3337 37.5001C52.3337 39.5481 50.6734 41.2084 48.6253 41.2084H41.2087V48.6251C41.2087 50.6731 39.5484 52.3334 37.5003 52.3334C35.4523 52.3334 33.792 50.6731 33.792 48.6251V41.2084H26.3753C24.3273 41.2084 22.667 39.5481 22.667 37.5001C22.667 35.452 24.3273 33.7917 26.3753 33.7917H33.792V26.3751C33.792 24.327 35.4523 22.6667 37.5003 22.6667Z"
                        fill="white" />
                </svg>
            </div>
        </div>

    </div>
    <script>
        function previewThumbnail(event) {
            const input = event.target;
            const preview = document.getElementById('thumbnailPreview');
            const text = document.getElementById('thumbnailText');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    text.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>

</html>