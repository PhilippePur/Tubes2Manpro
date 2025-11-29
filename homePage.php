<?php
require_once 'testsql.php'; // Koneksi SQL Server
//mengambil user session

session_start();
if (!isset($_SESSION['uid'])) {
  header("Location: index.php");
  exit;
}

// Ambil data dari session
$userId = $_SESSION['uid'];
$username = $_SESSION['uname'];
$fotoProfil = $_SESSION['fotoProfil'] ?: 'Assets/NoProfile.jpg';// path foto profil
$isAdmin = false;

if (isset($_SESSION['uid'])) {

  $sql = "
        SELECT A.idChannel
        FROM Admin A
        WHERE A.idUser = ?
    ";
  $stmt = sqlsrv_query($conn, $sql, [$userId]);

  if ($stmt && sqlsrv_has_rows($stmt)) {
    $isAdmin = true; // User sudah jadi admin
  }
}

$subs = [];
if (isset($_SESSION['uid'])) {
  $userId = $_SESSION['uid'];

  $query = "
        SELECT C.fotoProfil, C.namaChannel 
        FROM Users U 
        INNER JOIN Subscribe S ON U.idUser = S.idUser 
        INNER JOIN Channel C ON C.idChannel = S.idChannel 
        WHERE U.idUser = ? 
        ORDER BY tanggalSubscribe ASC
    ";
  $stmt = sqlsrv_query($conn, $query, [$userId]);
  while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $subs[] = $row;
  }
}



$sqlCount = "SELECT COUNT(idVideo) as Count FROM Videos WHERE isActive = 1";
$stmtCount = sqlsrv_query($conn, $sqlCount);
$rowCount = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
$totalVideos = $rowCount['Count'] ?? 0;


$sql = "SELECT 
            V.idVideo,
            V.title,
            V.thumbnail,
            V.uploaded_at,
            C.namaChannel,
            C.fotoProfil,
            ISNULL(SUM(T.jumlahTonton), 0) AS jumlahView
        FROM Videos V
        JOIN Channel C ON V.idChannel = C.idChannel
        LEFT JOIN Tonton T ON V.idVideo = T.idVideo
        WHERE V.isActive = 1
        GROUP BY 
            V.idVideo, V.title, V.thumbnail, V.uploaded_at, 
            C.namaChannel, C.fotoProfil
        ORDER BY V.idVideo ASC";

$result = sqlsrv_query($conn, $sql);
$rowsNeeded = ceil($totalVideos / 3);
$height = max(982, 982 + ($rowsNeeded - 3) * 280);

$videos = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
  $videos[] = $row;
}

$left = [320, 734, 1148];
$top = [183, 453, 723]; // hanya patokan awal

foreach ($videos as $i => $video):
  $x = $left[$i % 3] + 8;
  $y = 183 + floor($i / 3) * 280; // Hitung top dinamis

  $color = ['#EB4A4A', '#20EFAE', '#3C36FE', '#FF4BB7', '#2E412B', '#E1E63E', '#308120', '#37B8EF', '#AD45EA'];
  $bgColor = $color[$i % count($color)];
  ?>


  <?php if ($video): ?>
    <!-- Thumbnail -->
    <a href="PageView.php?id=<?= $video['idVideo'] ?>"
      style="text-decoration: none; position: absolute; left: <?= $x ?>px; top: <?= $y ?>px; z-index: 10;">
      <div class="Konten<?= $i + 1 ?>"
        style="width: 316px; height: 179px; background: <?= $color ?>; border-radius: 20px; overflow: hidden; cursor: pointer; position: relative;">
        <img src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="Thumbnail"
          style="width: 100%; height: 100%; object-fit: cover; display: block;">
      </div>

      <!-- Tambahan info video -->
      <div style="padding: 8px; position: absolute; top : 170; width: 100%; color: black;">
        <!-- Judul -->
        <div style="font-weight: bold; font-size: 20px"><?= htmlspecialchars(string: $video['title']) ?></div>
        <div style="font-size: 11px;">
          <?= ($video['jumlahView'] ?? 0) ?> Views •
          <?= $video['uploaded_at'] ? date_format($video['uploaded_at'], "d M Y") : 'Tanggal tidak tersedia' ?>
          <!-- FotoProfil -->
          <div style="font-size: 15 px; display: flex; align-items: center;">
            <img src="<?= htmlspecialchars(string: $video['fotoProfil']) ?>" alt="Channel"
              style="width: 30px; height: 30px; border-radius: 50%; margin-right: 6px;">
            <!-- Nama Channel -->
            <?= htmlspecialchars(string: $video['namaChannel']) ?>
          </div>
          <!-- Jumlah Views & Tanggal -->

        </div>
      </div>
    </a>

  <?php else: ?>
    <div class="Konten<?= $i + 1 ?>"
      style="width: 316px; height: 179px; left: <?= $x ?>px; top: <?= $y ?>px; position: absolute; background: <?= $color ?>; border-radius: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
      Tidak Ada Video
    </div>
  <?php endif; ?>
<?php endforeach; ?>


<!DOCTYPE html>
<html>

<head>
  <title>Dashboard</title>
</head>

<body>

  <div data-layer="HomePage No Login" class="HomepageNoLogin"
    style="width: 1512px; height: <?= $height ?>px; position: relative; background: white; overflow: hidden">

    <div data-layer="SideBorder" class="Sideborder"
      style="width: 251px; height: <?= $height ?>px; background-color: #7E7E7E;">
      <div data-layer="Subscription" class="Subscription"
        style="width: 126px; height: 28px; left: 12px; top: 418px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 20px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
        Subscription</div>
      <div style="position: absolute; left: 30px; top: 460px; width: 180px; max-height: 700px; overflow-y: auto;">
        <?php if (empty($subs)): ?>
        <?php else: ?>
          <?php foreach ($subs as $sub): ?>
            <div style="display: flex; align-items: center; margin-bottom: 12px;">
              <img src="<?= htmlspecialchars($sub['fotoProfil']) ?: 'Assets/NoProfile.jpg' ?>" alt="Foto Profil"
                style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">
              <span style="color: white; font-size: 14px; word-break: break-word;">
                <?= htmlspecialchars($sub['namaChannel']) ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>

    <div data-layer="Frame 2" class="Frame2"
      style="width: 1519px; height: 132px; padding: 10px; left: 186px; top: -10px; position: absolute; justify-content: flex-start; align-items: center; gap: 10px; display: inline-flex">
      <div data-svg-wrapper data-layer="SideBorder" class="Sideborder">
        <svg width="1316" height="131" viewBox="0 0 1316 131" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 131H1338V-19H0V131Z" fill="#7E7E7E" />
        </svg>
      </div>
    </div>
    <div data-layer="Youtube-Logo" class="YoutubeLogo"
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
          <path d="M0.407471 13.8566L12.3658 7.00064L0.407471 0.144531V13.8566Z" fill="white" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 50.58px; top: 2.27px; position: absolute">
        <svg width="16" height="28" viewBox="0 0 16 28" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M5.74836 18.8058L0.580078 0.269531H5.08909L6.90031 8.67216C7.36254 10.7418 7.69974 12.5066 7.9195 13.9666H8.05209C8.20369 12.9205 8.54477 11.167 9.07144 8.70227L10.9469 0.269531H15.456L10.2232 18.8058V27.6976H5.74466V18.8058H5.74836Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 64.29px; top: 9.27px; position: absolute">
        <svg width="13" height="22" viewBox="0 0 13 22" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M2.81234 20.1095C1.90303 19.4999 1.25504 18.5516 0.868534 17.2647C0.485895 15.9779 0.292725 14.2695 0.292725 12.1321V9.22329C0.292725 7.06713 0.512478 5.33249 0.951989 4.02675C1.3915 2.72102 2.07735 1.76523 3.00938 1.16692C3.94157 0.568618 5.16537 0.267578 6.6811 0.267578C8.17395 0.267578 9.36747 0.572378 10.2692 1.18198C11.1672 1.79156 11.8265 2.74735 12.2433 4.0418C12.6601 5.34001 12.8686 7.06713 12.8686 9.22329V12.1321C12.8686 14.2695 12.664 15.9854 12.2586 17.2798C11.8531 18.578 11.1938 19.5263 10.2843 20.1246C9.37504 20.723 8.1398 21.0239 6.5825 21.0239C4.9759 21.0276 3.72181 20.7191 2.81234 20.1095ZM7.91247 16.9713C8.16252 16.3166 8.2914 15.2516 8.2914 13.7691V7.52633C8.2914 6.08884 8.16638 5.03521 7.91247 4.37294C7.65856 3.70691 7.21534 3.37577 6.57863 3.37577C5.96496 3.37577 5.52916 3.70691 5.27911 4.37294C5.0252 5.03899 4.90018 6.08884 4.90018 7.52633V13.7691C4.90018 15.2516 5.02134 16.3203 5.26397 16.9713C5.50644 17.626 5.94208 17.9534 6.57863 17.9534C7.21534 17.9534 7.65856 17.626 7.91247 16.9713Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 79.23px; top: 9.66px; position: absolute">
        <svg width="13" height="22" viewBox="0 0 13 22" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M12.536 20.7014H8.98189L8.58781 18.248H8.48937C7.52303 20.0994 6.0756 21.025 4.14324 21.025C2.80569 21.025 1.81679 20.5885 1.18024 19.7194C0.543693 18.8462 0.225342 17.4842 0.225342 15.6328V0.66001H4.76851V15.3693C4.76851 16.265 4.86695 16.9008 5.06399 17.281C5.26103 17.661 5.59066 17.8528 6.05289 17.8528C6.44697 17.8528 6.8259 17.7325 7.18969 17.4917C7.55348 17.2507 7.81866 16.9459 7.99669 16.5773V0.65625H12.536V20.7014Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 90.41px; top: 2.27px; position: absolute">
        <svg width="14" height="28" viewBox="0 0 14 28" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M13.8772 3.89993H9.36814V27.7004H4.92356V3.89993H0.414551V0.272461H13.8772V3.89993Z" fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 102.52px; top: 9.66px; position: absolute">
        <svg width="13" height="22" viewBox="0 0 13 22" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M12.8348 20.7014H9.28072L8.88664 18.248H8.7882C7.82202 20.0994 6.37459 21.025 4.44206 21.025C3.10452 21.025 2.11563 20.5885 1.47908 19.7194C0.842527 18.8462 0.52417 17.4842 0.52417 15.6328V0.66001H5.06734V15.3693C5.06734 16.265 5.16578 16.9008 5.36282 17.281C5.55986 17.661 5.88949 17.8528 6.35188 17.8528C6.74579 17.8528 7.12472 17.7325 7.48851 17.4917C7.8523 17.2507 8.11749 16.9459 8.29552 16.5773V0.65625H12.8348V20.7014Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 117.57px; top: 1.26px; position: absolute">
        <svg width="14" height="29" viewBox="0 0 14 29" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M12.8702 11.8617C12.5936 10.5974 12.1502 9.68299 11.5365 9.11478C10.9227 8.54659 10.0777 8.26436 9.00162 8.26436C8.16803 8.26436 7.38744 8.49766 6.66373 8.96803C5.94002 9.4384 5.37919 10.0518 4.98512 10.8156H4.95113V0.256836H0.574707V28.6971H4.32586L4.78808 26.8006H4.88668C5.23903 27.4779 5.7657 28.0084 6.4667 28.4036C7.16769 28.795 7.94828 28.9907 8.80458 28.9907C10.3392 28.9907 11.4721 28.287 12.1958 26.8833C12.9195 25.476 13.2831 23.2822 13.2831 20.2945V17.1224C13.2831 14.8834 13.143 13.1261 12.8702 11.8617ZM8.70598 20.0385C8.70598 21.4985 8.6454 22.6425 8.52408 23.4704C8.40293 24.2982 8.20202 24.8891 7.91412 25.2352C7.62992 25.5851 7.24342 25.7582 6.76218 25.7582C6.38711 25.7582 6.04233 25.6716 5.72398 25.4948C5.40578 25.3217 5.14817 25.0584 4.95113 24.7121V13.3368C5.10257 12.7912 5.36792 12.3471 5.74299 11.9972C6.11435 11.6472 6.52357 11.4741 6.95922 11.4741C7.4216 11.4741 7.77766 11.6548 8.02786 12.0123C8.28161 12.3735 8.45593 12.9756 8.55453 13.826C8.65297 14.6764 8.70228 15.8843 8.70228 17.4534V20.0385H8.70598Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 132.29px; top: 9.28px; position: absolute">
        <svg width="13" height="21" viewBox="0 0 13 21" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M4.72806 13.1935C4.72806 14.4804 4.76592 15.4438 4.84181 16.0873C4.91753 16.7307 5.0767 17.1974 5.31917 17.4947C5.56164 17.7881 5.93301 17.9348 6.43696 17.9348C7.11524 17.9348 7.58504 17.6715 7.83508 17.1484C8.08899 16.6254 8.22545 15.7524 8.24817 14.5332L12.1661 14.7627C12.1888 14.9358 12.2001 15.1766 12.2001 15.4814C12.2001 17.3327 11.6885 18.7175 10.6693 19.6319C9.65016 20.5463 8.20645 21.0054 6.34223 21.0054C4.10295 21.0054 2.53421 20.3092 1.63618 18.9132C0.734443 17.5172 0.287354 15.3611 0.287354 12.4409V8.94137C0.287354 5.93485 0.753457 3.73729 1.68548 2.35254C2.61767 0.967775 4.21282 0.275391 6.47482 0.275391C8.03212 0.275391 9.2295 0.557614 10.0631 1.12582C10.8967 1.69401 11.4839 2.57454 11.825 3.77493C12.1661 4.9753 12.3365 6.63099 12.3365 8.7457V12.1775H4.72806V13.1935ZM5.30403 3.74859C5.07283 4.0308 4.9214 4.49365 4.84181 5.1371C4.76592 5.78057 4.72806 6.75517 4.72806 8.06473V9.50218H8.05113V8.06473C8.05113 6.77774 8.0057 5.80315 7.91854 5.1371C7.83138 4.47107 7.6722 4.00446 7.44101 3.72977C7.20997 3.45885 6.85376 3.31961 6.37252 3.31961C5.88757 3.32337 5.53136 3.46637 5.30403 3.74859Z"
            fill="black" />
        </svg>
      </div>
    </div>
    <div data-layer="Navigations/SearchBox" class="NavigationsSearchbox"
      style="width: 440px; height: 40px; left: 454px; top: 39px; position: absolute">
      <div data-layer="Navigations/SearchBox" class="NavigationsSearchbox"
        style="width: 575px; height: 40px; left: 0px; top: 0px; position: absolute; background: #121212; overflow: hidden; border-top-left-radius: 2px; border-bottom-left-radius: 2px">
        <div data-layer="border-bottom" class="BorderBottom"
          style="width: 575px; height: 1px; left: 0px; top: 39px; position: absolute; background: #303030"></div>
        <div data-svg-wrapper data-layer="border-left" class="BorderLeft"
          style="left: 0px; top: 0px; position: absolute">
          <svg width="1" height="40" viewBox="0 0 1 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="1" height="40" fill="#303030" />
          </svg>
        </div>
        <div data-layer="border-top" class="BorderTop"
          style="width: 575px; height: 1px; left: 0px; top: 0px; position: absolute; background: #303030"></div>
        <div data-layer="Navigations/SearchBox/Placeholder" class="NavigationsSearchboxPlaceholder"
          style="width: 54px; height: 31px; padding-left: 2px; padding-right: 2px; padding-top: 6px; padding-bottom: 6px; left: 6px; top: 4px; position: absolute; justify-content: flex-start; align-items: flex-start; gap: 10px; display: inline-flex">
          <div data-layer="Search" class="Search"
            style="color: #AAAAAA; font-size: 16px; font-family: Roboto; font-weight: 400; word-wrap: break-word">Search
          </div>
        </div>
      </div>
      <div data-layer="Navigations/SearchBox-Button" class="NavigationsSearchboxButton"
        style="width: 64px; height: 36px; padding-left: 7px; padding-right: 7px; padding-top: 2px; padding-bottom: 2px; left: 575px; top: 0px; position: absolute; background: #303030; overflow: hidden; border-top-right-radius: 2px; border-bottom-right-radius: 2px; outline: 1px #303030 solid; outline-offset: -1px; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 10px; display: inline-flex">
        <div data-layer="Navigations/SearchBox-Button/icon" class="NavigationsSearchboxButtonIcon"
          style="width: 50px; height: 36px; position: relative; background: #303030; overflow: hidden">
          <div data-svg-wrapper data-layer="search" class="Search" style="left: 13px; top: 6px; position: absolute">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M20.87 20.17L15.28 14.58C16.35 13.35 17 11.75 17 10C17 6.13 13.87 3 10 3C6.13 3 3 6.13 3 10C3 13.87 6.13 17 10 17C11.75 17 13.35 16.35 14.58 15.29L20.17 20.88L20.87 20.17ZM10 16C6.69 16 4 13.31 4 10C4 6.69 6.69 4 10 4C13.31 4 16 6.69 16 10C16 13.31 13.31 16 10 16Z"
                fill="white" />
            </svg>
          </div>
        </div>
      </div>
    </div>
    <?php
    ?>
    <div data-svg-wrapper data-layer="home" class="Home" style="left: 26px; top: 107px; position: absolute">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M20.0001 7.21667L31.6667 17.4167V33.3333H25.0001V23.3333H15.0001V33.3333H8.33341V17.4167L20.0001 7.21667ZM20.0001 5L6.66675 16.6667V35H16.6667V25H23.3334V35H33.3334V16.6667L20.0001 5Z"
          fill="white" />
      </svg>
    </div>
    <div data-svg-wrapper data-layer="subscriptions" class="Subscriptions"
      style="left: 26px; top: 173px; position: absolute">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M16.6666 30V20L24.9999 25L16.6666 30ZM28.3333 5H11.6666V6.66667H28.3333V5ZM33.3333 10H6.66659V11.6667H33.3333V10ZM36.6666 15H3.33325V35H36.6666V15ZM4.99992 16.6667H34.9999V33.3333H4.99992V16.6667Z"
          fill="white" />
      </svg>
    </div>
    <div data-svg-wrapper data-layer="history" class="History" style="left: 26px; top: 275px; position: absolute">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M24.9499 28.2502L16.6666 23.1168V11.6668H19.9999V21.2668L26.7166 25.4168L24.9499 28.2502ZM36.6666 20.0002C36.6666 29.1835 29.1833 36.6668 19.9999 36.6668C10.8166 36.6668 3.33327 29.1835 3.33327 20.0002H4.99993C4.99993 28.2668 11.7333 35.0002 19.9999 35.0002C28.2666 35.0002 34.9999 28.2668 34.9999 20.0002C34.9999 11.7335 28.2666 5.00016 19.9999 5.00016C14.6833 5.00016 9.8666 7.7335 7.13327 12.3002C6.94994 12.6002 6.7666 12.9168 6.6166 13.2335C6.59993 13.2668 6.58327 13.3002 6.5666 13.3335H13.3333V15.0002H3.2666V5.00016H4.93327V12.9002C4.99993 12.7502 5.04993 12.6168 5.1166 12.4835C5.29993 12.1168 5.49993 11.7835 5.69993 11.4335C8.69993 6.4335 14.1833 3.3335 19.9999 3.3335C29.1833 3.3335 36.6666 10.8168 36.6666 20.0002Z"
          fill="white" />
      </svg>
    </div>
    <div data-svg-wrapper data-layer="liked" class="Liked" style="left: 23px; top: 341px; position: absolute">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M31.2833 18.3332H24.2333L26.7667 10.0998C27.3 8.38317 25.9 6.6665 23.9667 6.6665C23 6.6665 22.0667 7.0665 21.4333 7.74984L11.6667 18.3332H5V34.9998H11.6667H13.3333H29.05C30.8167 34.9998 32.35 33.8832 32.7 32.3165L34.9333 22.3165C35.3833 20.2498 33.6333 18.3332 31.2833 18.3332ZM11.6667 33.3332H6.66667V19.9998H11.6667V33.3332ZM33.3 21.9498L31.0667 31.9498C30.9 32.7498 30.05 33.3332 29.05 33.3332H13.3333V18.9832L22.6667 8.88317C22.9833 8.53317 23.4667 8.33317 23.9667 8.33317C24.4 8.33317 24.8 8.5165 25.0167 8.83317C25.1333 8.99984 25.2667 9.2665 25.1667 9.6165L22.6333 17.8498L21.9667 19.9998H24.2167H31.2667C31.95 19.9998 32.6 20.2832 32.9833 20.7665C33.2 21.0165 33.4167 21.4332 33.3 21.9498Z"
          fill="white" />
      </svg>
    </div>
    <div data-layer="HomePage" class="Homepage"
      style="width: 126px; height: 28px; left: 85px; top: 113px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
      HomePage</div>
    <div data-layer="Subscription" class="Subscription"
      style="width: 126px; height: 28px; left: 85px; top: 179px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
      Subscription</div>

    <div data-layer="History" class="History"
      style="width: 126px; height: 28px; left: 82px; top: 281px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
      History</div>
    <div data-layer="Liked Video" class="LikedVideo"
      style="width: 126px; height: 28px; left: 76px; top: 348px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 16px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
      Liked Video</div>
    <div data-svg-wrapper data-layer="Vector 1" class="Vector1" style="left: 0.88px; top: 244.10px; position: absolute">
      <svg width="252" height="3" viewBox="0 0 252 3" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.876221 1.10156L250.293 1.10156" stroke="white" stroke-width="2" stroke-linecap="round" />
      </svg>
    </div>
    <div data-svg-wrapper data-layer="Menu" data-size="48" class="Menu"
      style="left: 28px; top: 39px; position: absolute">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M5 20H35M5 10H35M5 30H35" stroke="var(--Icon-Brand-On-Brand, #F5F5F5)" stroke-width="4"
          stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </div>
    <div data-svg-wrapper data-layer="Vector 3" class="Vector3" style="left: 0.71px; top: 399.43px; position: absolute">
      <svg width="255" height="3" viewBox="0 0 255 3" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.713867 1.43408H253.352" stroke="white" stroke-width="2" stroke-linecap="round" />
      </svg>
    </div>

    <!-- Lonceng Notifikasi -->
    <div style="position: absolute; top: 32px; right: 330px;height: 50px; width: 50px;">
      <button id="notifBtn"
        style="background: none; border: none; cursor: pointer;height: 50px; width: 50px; font-size: 34px;">
        🔔
      </button>
      <div id="notifPopup"
        style="display: none; position: absolute; right: 0; background: white; border: 1px solid #ccc; padding: 10px; width: 300px;height: 300px; z-index: 100;">
        <strong>Undangan Admin</strong>
        <div id="notifContent">
          <p>Memuat...</p>
        </div>
      </div>
    </div>

    <a href="EditChannelInd.php">
      <img data-layer="MainProfile" class="Mainprofile"
        style="width: 75; height: 75; left: 1415; top: 22; position: absolute; border-radius: 200px"
        src="<?= htmlspecialchars(string: $fotoProfil) ?>" alt="Foto Profil Utama">
    </a>

    <?php if (!$isAdmin): ?>
      <a href="makeChannel.php" style="display: inline-block; width: 104px; height: 43px; left: 1209px; top: 40px; position: absolute; 
        background: #795757; border-radius: 12px; text-align: center; line-height: 43px; color: white; 
        text-decoration: none; font-family: Inter; font-size: 14px;">
        Make Channel
      </a>
    <?php endif; ?>

    <div data-svg-wrapper data-layer="add-square" class="AddSquare" style="left: 1333px; top: 34px; position: absolute">
      <a href="dashboard.php" class="AddSquare" style="left: 0; top: 0; position: absolute; display: block;">
        <img src="Assets/add-square.png" alt="Add Square" width="51" height="51">
      </a>
    </div>
  </div>

  <script>
    document.getElementById('notifBtn').onclick = function () {
      const popup = document.getElementById('notifPopup');
      popup.style.display = (popup.style.display === 'none') ? 'block' : 'none';

      // AJAX fetch data dari invitation
      fetch('getInvitation.php')
        .then(response => response.text())
        .then(data => {
          document.getElementById('notifContent').innerHTML = data;
        });
    };

    function respondToInvite(inviteId, action) {
      fetch('respondInvite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${inviteId}&action=${action}`
      }).then(() => {
        // Refresh notifikasi
        document.getElementById('notifBtn').click();
        document.getElementById('notifBtn').click();
      });
    }
  </script>


</html>