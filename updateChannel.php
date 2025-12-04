<!-- update channel -->
<?php
session_start();
require_once 'testsql.php'; // Koneksi SQL Server

// Ambil user ID dari session
$userId = $_SESSION['uid'] ?? null;

if ($userId === null) {
    die("User belum login.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggleActiveAdmin'])) {
    $toggleUserId = $_POST['toggleUserId'] ?? null;
    $currentStatus = $_POST['currentStatus'] ?? null;
    if ($toggleUserId !== null && $currentStatus !== null) {
        // Set IsActive explicitly based on currentStatus
        if ($currentStatus == 1) {
            $newStatus = 3; // deactivate
        } else if ($currentStatus == 3) {
            $newStatus = 1; // activate
        } else {
            $newStatus = 1; // default to active
        }
        $updateToggleSql = "UPDATE Admin SET IsActive = ? WHERE idUser = ? AND idChannel = (SELECT idChannel FROM Admin WHERE idUser = ?)";
        $params = [$newStatus, $toggleUserId, $userId];
        $stmt = sqlsrv_query($conn, $updateToggleSql, $params);
        if ($stmt === false) {
            die("Gagal mengubah status admin: " . print_r(sqlsrv_errors(), true));
        } else {
            // Redirect to avoid form resubmission
            header("Location: updateChannel.php");
            exit();
        }
    }
}

// Prepare list of admins for selection in deactivate form
$selectAdminSql = "SELECT U.Username, A.idUser
                   FROM Admin A
                   INNER JOIN Users U ON A.idUser = U.idUser
                   WHERE A.idChannel = (SELECT idChannel FROM Admin WHERE idChannel = ?)
                   AND A.IsActive IN (1, 2)";
$selectAdminParams = [$userId];
$selectAdminStmt = sqlsrv_query($conn, $selectAdminSql, $selectAdminParams);

$selectAdminList = [];
if ($selectAdminStmt !== false) {
    while ($row = sqlsrv_fetch_array($selectAdminStmt, SQLSRV_FETCH_ASSOC)) {
        $selectAdminList[] = $row;
    }
}

// Query untuk mendapatkan data user dan channel terkait
$sql = "SELECT U.fotoProfil AS userFotoProfil, C.namaChannel, C.fotoProfil AS channelFotoProfil, U.Email
        FROM Users U
        INNER JOIN Admin A ON U.idUser = A.idUser
        INNER JOIN Channel C ON A.idChannel = C.idChannel
        WHERE U.idUser = ?";
$params = [$userId];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Error saat mengambil data user dan channel: " . print_r(sqlsrv_errors(), true));
}

$userData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$userFotoProfil = $userData['userFotoProfil'] ?? 'Assets/NoProfile.jpg';
$channelFotoProfil = $userData['channelFotoProfil'] ?? 'Assets/NoProfile.jpg';
$namaChannel = $userData['namaChannel'] ?? 'Nama Channel';
$displayName = $userData['Email'] ?? 'email@example.com';

// Proses update nama channel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateChannel'])) {
    $channelId = $_POST['channel_id'] ?? '';
    $channelName = $_POST['channel_name'] ?? '';

    if (!empty($channelId) && !empty($channelName)) {
        $updateSql = "UPDATE Channel SET namaChannel = ? WHERE idChannel = ?";
        $params = [$channelName, $channelId];
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);

        if ($updateStmt === false) {
            $message = "Gagal update channel: " . print_r(sqlsrv_errors(), true);
        } else {
            $message = "Berhasil update channel!";
        }
    } else {
        $message = "ID Channel dan Nama Channel harus diisi.";
    }
}

// Ambil daftar admin untuk channel user
$adminListSql = "SELECT U.fotoProfil, U.Username, R.RoleName, A.IsActive, A.idUser
                 FROM Admin A
                 INNER JOIN Users U ON A.idUser = U.idUser
                 INNER JOIN Roles R ON A.idRole = R.idRole
                 WHERE A.idChannel = (SELECT idChannel FROM Admin WHERE idUser = ?)
                 AND A.IsActive IN (1, 3)";
$adminListParams = [$userId];
$adminListStmt = sqlsrv_query($conn, $adminListSql, $adminListParams);

if ($adminListStmt === false) {
    die("Error saat mengambil daftar admin: " . print_r(sqlsrv_errors(), true));
}

$adminList = [];
while ($row = sqlsrv_fetch_array($adminListStmt, SQLSRV_FETCH_ASSOC)) {
    $adminList[] = $row;
}

// Query to get invited admins (IsActive = 2)
$invitedAdminSql = "SELECT U.fotoProfil, U.Username, R.RoleName, U.email
                    FROM Admin A
                    INNER JOIN Users U ON A.idUser = U.idUser
                    INNER JOIN Roles R ON A.idRole = R.idRole
                    WHERE A.idChannel = (SELECT idChannel FROM Admin WHERE idUser = ?)
                    AND A.IsActive = 2";
$invitedAdminParams = [$userId];
$invitedAdminStmt = sqlsrv_query($conn, $invitedAdminSql, $invitedAdminParams);

if ($invitedAdminStmt === false) {
    die("Error saat mengambil daftar admin yang diundang: " . print_r(sqlsrv_errors(), true));
}

$invitedAdminList = [];
while ($row = sqlsrv_fetch_array($invitedAdminStmt, SQLSRV_FETCH_ASSOC)) {
    $invitedAdminList[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Tambah Admin</title>
</head>

<body>
    <div data-layer="Update Channel" class="UpdateChannel"
        style="width: 1512px; height: 1509px; position: relative; background: white; overflow: hidden">
        <div data-layer="Rectangle 8" class="Rectangle8"
            style="width: 1118px; height: 1067px; left: 197px; top: 132px; position: absolute; background: #D9D9D9; border-radius: 101px">
        </div>
        <a href="homepage.php" data-layer="Youtube-Logo" class="YoutubeLogo"
            style="width: 204px; height: 45px; left: 43px; top: 42px; position: absolute; overflow: hidden">
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
                        d="M7.43213 26.2582L0.160889 0.19165H6.5046L9.0528 12.0078C9.7031 14.9182 10.1775 17.4 10.4867 19.4532H10.6732C10.8865 17.9821 11.3664 15.5162 12.1074 12.0502L14.746 0.19165H21.0897L13.7278 26.2582V38.7624H7.42692V26.2582H7.43213Z"
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
                        d="M17.7815 28.7679H12.7812L12.2268 25.3177H12.0883C10.7288 27.9212 8.69238 29.2228 5.97374 29.2228C4.09196 29.2228 2.70068 28.609 1.80512 27.3868C0.909557 26.159 0.46167 24.2436 0.46167 21.6401V0.584633H6.85345V21.2695C6.85345 22.5291 6.99194 23.4232 7.26915 23.9578C7.54636 24.4922 8.01012 24.762 8.66043 24.762C9.21486 24.762 9.74798 24.5928 10.2598 24.2542C10.7716 23.9153 11.1447 23.4867 11.3952 22.9683V0.579346H17.7815V28.7679Z"
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
                        d="M17.5608 28.7679H12.5605L12.0061 25.3177H11.8676C10.5083 27.9212 8.4719 29.2228 5.75304 29.2228C3.87125 29.2228 2.47998 28.609 1.58442 27.3868C0.688862 26.159 0.240967 24.2436 0.240967 21.6401V0.584633H6.63273V21.2695C6.63273 22.5291 6.77123 23.4232 7.04844 23.9578C7.32566 24.4922 7.78941 24.762 8.43995 24.762C8.99415 24.762 9.52726 24.5928 10.0391 24.2542C10.5509 23.9153 10.924 23.4867 11.1744 22.9683V0.579346H17.5608V28.7679Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 165.42px; top: 1.77px; position: absolute">
                <svg width="19" height="42" viewBox="0 0 19 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.7138 17.0872C17.3246 15.3092 16.7008 14.0233 15.8374 13.2243C14.9738 12.4253 13.785 12.0284 12.2711 12.0284C11.0983 12.0284 10.0001 12.3565 8.98192 13.0179C7.96373 13.6794 7.1747 14.5419 6.62027 15.6161H6.57246V0.767822H0.415283V40.7619H5.69277L6.34306 38.095H6.48179C6.97751 39.0474 7.71848 39.7935 8.7047 40.3493C9.69093 40.8996 10.7891 41.1748 11.9939 41.1748C14.1529 41.1748 15.7468 40.1852 16.765 38.2113C17.7832 36.2322 18.2948 33.1472 18.2948 28.9458V24.485C18.2948 21.3364 18.0975 18.8652 17.7138 17.0872ZM11.8551 28.5858C11.8551 30.6389 11.7699 32.2477 11.5992 33.4118C11.4288 34.576 11.1461 35.4069 10.7411 35.8936C10.3412 36.3857 9.79747 36.6291 9.12042 36.6291C8.59274 36.6291 8.10767 36.5074 7.65977 36.2588C7.21211 36.0153 6.84967 35.645 6.57246 35.1581V19.1615C6.78552 18.3942 7.15885 17.7698 7.68653 17.2777C8.20899 16.7856 8.78472 16.5422 9.39763 16.5422C10.0482 16.5422 10.5491 16.7962 10.9011 17.2989C11.2581 17.8069 11.5034 18.6535 11.6421 19.8494C11.7806 21.0453 11.8499 22.7439 11.8499 24.9505V28.5858H11.8551Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 186.11px; top: 13.04px; position: absolute">
                <svg width="18" height="30" viewBox="0 0 18 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.36236 18.2103C6.36236 20.02 6.41563 21.3747 6.52239 22.2797C6.62893 23.1844 6.85286 23.8407 7.19399 24.2588C7.53513 24.6714 8.0576 24.8778 8.76661 24.8778C9.72088 24.8778 10.3818 24.5074 10.7336 23.7719C11.0908 23.0364 11.2828 21.8088 11.3148 20.0943L16.8269 20.4169C16.8588 20.6604 16.8747 20.999 16.8747 21.4276C16.8747 24.0311 16.155 25.9785 14.7211 27.2643C13.2872 28.5502 11.2561 29.1957 8.63333 29.1957C5.48289 29.1957 3.27585 28.2168 2.01241 26.2536C0.743755 24.2905 0.114746 21.2584 0.114746 17.1519V12.2307C0.114746 8.0028 0.770505 4.91249 2.08177 2.96518C3.39326 1.01785 5.63748 0.0441895 8.81987 0.0441895C11.0108 0.0441895 12.6954 0.441067 13.8682 1.24011C15.041 2.03913 15.8672 3.27737 16.347 4.96541C16.8269 6.65343 17.0667 8.98175 17.0667 11.9556V16.7816H6.36236V18.2103ZM7.17269 4.92838C6.84742 5.32523 6.63437 5.97611 6.52239 6.88097C6.41563 7.78585 6.36236 9.15637 6.36236 10.998V13.0194H11.0376V10.998C11.0376 9.18812 10.9737 7.8176 10.851 6.88097C10.7284 5.94436 10.5045 5.2882 10.1792 4.90192C9.85416 4.52092 9.353 4.32513 8.67594 4.32513C7.99368 4.33042 7.49252 4.5315 7.17269 4.92838Z"
                        fill="black" />
                </svg>
            </div>
        </a>


        <img data-layer="MainProfile" class="Mainprofile"
            style="width: 140px; height: 140px; left: 301px; top: 187px; position: absolute; border-radius: 200px"
            src="<?php echo htmlspecialchars($channelFotoProfil); ?>">

        <div data-layer="Rectangle 9" class="Rectangle9"
            style="width: 582px; height: 77px; left: 457px; top: 218px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>
        <div data-layer="Rectangle 10" class="Rectangle10"
            style="width: 999px; height: 382px; left: 257px; top: 377px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>
        <div data-layer="Rectangle 12" class="Rectangle12"
            style="width: 999px; height: 310px; left: 257px; top: 449px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>
        <div data-layer="Rectangle 11" class="Rectangle11"
            style="width: 999px; height: 362px; left: 257px; top: 791px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>
        <div data-layer="Pending" class="Pending"
            style="width: 999px; height: 28px; left: 280px; top: 825px; position: absolute; color: black; font-family: Roboto; font-weight: 700; font-size: 36px; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;">
            Pending
        </div>
        <div data-layer="Rectangle 13" class="Rectangle13"
            style="width: 999px; height: 288px; left: 257px; top: 865px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>

        <div data-layer="Rectangle 5" class="Rectangle5"
            style="width: 99px; height: 43px; left: 1142px; top: 236px; position: absolute; background: #795757; border-radius: 11px">
        </div>
        <form method="post" action="logout.php" style="display:inline;">
            <button type="submit" data-layer="LogOut" class="Logout"
                style="width: 129px; height: 23px; left: 1127px; top: 246px; position: absolute; text-align: center; justify-content: center; display: flex; flex-direction: column; color: #FFF3F3; font-size: 14px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word; background:none; border:none; cursor:pointer;">
                LogOut
            </button>
        </form>
        <div data-layer="Admin List" class="AdminList"
            style="width: 978px; height: 28px; left: 280px; top: 410px; position: absolute; color: black; font-family: Roboto; font-weight: 700; font-size: 36px; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word;">
            Admin List
        </div>

        <a href="addAdmin.php" style="display: inline-block; width: 104px; height: 43px; left: 1130px; top: 390px; position: absolute; 
          background: #795757; border-radius: 12px; text-align: center; line-height: 43px; color: white; 
          text-decoration: none; font-family: Inter; font-size: 14px;">
            Add Admin
        </a>
        <div data-layer="Admin List Content" class="AdminListContent"
            style="width: 959px; height: 250px; left: 257px; top: 469px; position: absolute; justify-content: flex-start; display: flex; flex-direction: row; gap: 20px; color: black; font-size: 18px; font-family: Roboto; font-weight: 400; line-height: 20px; letter-spacing: 0.40px; word-wrap: break-word; overflow-x: auto; overflow-y: hidden; padding: 10px; border-radius: 10px; background: transparent; scroll-behavior: smooth;">
            <?php foreach ($adminList as $admin): ?>
                <div
                    style="display: flex; flex-direction: column; align-items: center; width: 120px; min-width: 120px; padding: 10px; border-radius: 10px; position: relative;">
                    <img src="<?php echo htmlspecialchars($admin['fotoProfil'] ?? 'Assets/NoProfile.jpg'); ?>" alt="Profile"
                        style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 12px;">
                    <div style="font-weight: 700; font-size: 24px; text-align: center; margin-bottom: 12px;">
                        <?php echo htmlspecialchars($admin['Username'] ?? 'Unknown'); ?></div>
                    <div style="font-size: 20px; color: black; text-align: center; margin-bottom: 8px;">
                        <?php echo htmlspecialchars($admin['RoleName'] ?? 'Role'); ?></div>
                    <?php $isActiveInt = (int) $admin['IsActive']; ?>
                    <div
                        style="font-size: 16px; font-weight: 600; margin-bottom: 8px; text-align: center; padding: 5px 10px; border-radius: 5px; color: white; <?php echo ($admin['IsActive'] == 1) ? 'background-color: green;' : 'background-color: red;'; ?>">
                        <?php
                        $status = 'Active'; // default
                        if (isset($admin['IsActive'])) {
                            echo "<!-- DEBUG IsActive: $isActiveInt -->"; // debug output
                            echo "<!-- DEBUG IsActive value: {$admin['IsActive']}, type: " . gettype($admin['IsActive']) . " -->";
                            if ($admin['IsActive'] == 1) {
                                $status = 'Active';
                            } else if ($admin['IsActive'] == 2) {
                                $status = 'Invited-Pending';
                            } else {
                                $status = 'Inactive';
                            }
                        }
                        echo $status;
                        ?>
                    </div>
                    <?php $isActiveInt = (int) $admin['IsActive']; ?>
                    <form method="post" style="position: absolute; top: 5px; right: 5px;">
                        <input type="hidden" name="toggleUserId"
                            value="<?php echo htmlspecialchars($admin['idUser'] ?? ''); ?>">
                        <input type="hidden" name="currentStatus"
                            value="<?php echo htmlspecialchars($admin['IsActive'] ?? ''); ?>">
                        <button type="submit" name="toggleActiveAdmin"
                            style="background-color: <?php echo ($admin['IsActive'] == 1) ? 'green' : 'red'; ?>; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer;"
                            title="Toggle Active Status">
                            <?php echo ($admin['IsActive'] == 1) ? 'Activate' : 'Deactivate'; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <div data-layer="Nama Channel" class="NamaChannel"
            style="width: 448px; height: 39px; left: 483px; top: 237px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 40px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            <?php echo htmlspecialchars($namaChannel); ?>
        </div>
        <div data-layer="Display Name" class="DisplayName"
            style="width: 293px; height: 23px; left: 483px; top: 315px; position: absolute; color: black; font-size: 32px; font-family: Roboto; font-weight: 400; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            <?php echo htmlspecialchars($displayName); ?>
        </div>

        <div data-layer="Invited Admin List Content" class="InvitedAdminListContent"
            style="width: 959px; height: 250px; left: 257px; top: 870px; position: absolute; justify-content: flex-start; display: flex; flex-direction: row; gap: 20px; color: black; font-size: 18px; font-family: Roboto; font-weight: 400; line-height: 20px; letter-spacing: 0.40px; word-wrap: break-word; overflow-x: auto; overflow-y: hidden; padding: 10px; border-radius: 10px; background: transparent; scroll-behavior: smooth;">
            <?php foreach ($invitedAdminList as $admin): ?>
                <div
                    style="display: flex; flex-direction: column; align-items: center; width: 120px; min-width: 120px; padding: 10px; border-radius: 10px;">
                    <img src="<?php echo htmlspecialchars($admin['fotoProfil'] ?? 'Assets/NoProfile.jpg'); ?>" alt="Profile"
                        style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 12px;">
                    <div style="font-weight: 700; font-size: 24px; text-align: center; margin-bottom: 12px;">
                        <?php echo htmlspecialchars($admin['Username'] ?? 'Unknown'); ?></div>
                    <div style="font-size: 20px; color: black; text-align: center; width: 110px;">
                        <?php echo htmlspecialchars($admin['email'] ?? 'Email'); ?></div><br>
                    <div style="font-size: 20px; color: black; text-align: center;">
                        <?php echo htmlspecialchars($admin['RoleName'] ?? 'Role'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>