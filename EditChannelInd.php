<?php
require_once 'testsql.php';
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit;
}

$isAdmin = false;
$uid = $_SESSION['uid'];



$sql = "SELECT 
            C.namaChannel, 
            C.deskripsi, 
            C.fotoProfil, 
            C.channelType,
            U.Email,
            A.idChannel,
            R.RoleName
        FROM Users U
        INNER JOIN [Admin] A ON A.idUser = U.idUser
        INNER JOIN Channel C ON C.idChannel = A.idChannel
        INNER JOIN Roles R ON R.idRole = A.idRole
        WHERE U.idUser = ? AND (isActive = 1 OR isActive = 2) ";

$params = [$uid];
$stmt = sqlsrv_query($conn, $sql, $params);
$canEdit = true;
$isGroup = true;
if ($stmt && sqlsrv_has_rows($stmt)) {
    $channelInfo = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $usernameLama = $channelInfo['namaChannel'];
    $emailLama = $channelInfo['Email'];
    $fotoLama = $channelInfo['fotoProfil'];
    $deskripsiLama = $channelInfo['deskripsi'] ?? '';
    $channelID = $channelInfo['idChannel'];
    if ($channelInfo['channelType'] == 0) {
        $isGroup = false;
    }
} else {
    // echo "Channel tidak ditemukan atau tidak ada admin terkait.";
    $usernameLama = "Tidak Ditemukan";
    $emailLama = "Tidak Ditemukan";
    $fotoLama = "Assets/NoProfile.jpg";
    $deskripsiLama = "Channel tidak ditemukan, mohon buat channel atau masuk ke dalam channel grup sebagai admin";
    $channelID = 0;
}

if (isset($_SESSION['uid'])) {

    $sql = "SELECT *
    FROM Admin A
    WHERE A.idUser = ? AND 
    (idRole = (SELECT idRole FROM Roles WHERE RoleName = 'Owner') OR 
    idRole = (SELECT idRole FROM Roles WHERE RoleName = 'Manager') OR 
    idRole = (SELECT idRole FROM Roles WHERE RoleName = 'Editor')) 
    ";
    $stmt = sqlsrv_query($conn, $sql, [$uid]);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        $isAdmin = true; // User sudah jadi admin
    } else {
        $deskripsiLama = "Anda Tidak Diperkenankan Mengedit Channel Ini !";
        $canEdit = false;
    }

}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Make Channel</title>
</head>


<body>
    <div data-layer="Profile & Update Channel" class="ProfileUpdateChannel"
        style="width: 1512px; height: 1109px; position: relative; background: white; overflow: hidden">
        <div data-layer="Rectangle 8" class="Rectangle8"
            style="width: 1118px; height: 950px; left: 197px; top: 132px; position: absolute; background: #D9D9D9; border-radius: 101px">
        </div>

        <img id="previewFoto" data-layer="MainProfile" class="Mainprofile"
            style="width: 140px; height: 140px; left: 300px; top: 216px; position: absolute; border-radius: 200px"
            src="<?= htmlspecialchars($fotoLama) ?>">

        <a href="logout.php" style="width: 104px; height: 43px; left: 318px; top: 159px; position: absolute; background: #eb4034; border-radius: 12px; 
           text-align: center; justify-content: center; display: flex; align-items: center; text-decoration: none;">
            <span style="color: #FFF3F3; font-size: 14px; font-family: Roboto; font-weight: 400; 
         line-height: 16px; letter-spacing: 0.40px;">
                LogOut
            </span>
        </a>


        <?php if ($isAdmin): ?>
            <a href="upload.php" style="display: inline-block; width: 104px; height: 43px; left: 1109px; top: 159px; position: absolute; 
          background: #795757; border-radius: 12px; text-align: center; line-height: 43px; color: white; 
          text-decoration: none; font-family: Inter; font-size: 14px;">
                Upload
            </a>

            <?php if ($isGroup): ?>
                <a href="updateChannel.php" style="display: inline-block; width: 104px; height: 43px; left: 959px; top: 159px; position: absolute; 
          background: #795757; border-radius: 12px; text-align: center; line-height: 43px; color: white; 
          text-decoration: none; font-family: Inter; font-size: 14px;">
                    Manage Admin
                </a>
            <?php endif; ?>
        <?php endif; ?>



        <a href="homepage.php" data-layer="Youtube-Logo" class="YoutubeLogo"
            style="width: 204px; height: 45px; left: 25 3px; top: 42px; position: absolute; overflow: hidden">
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



        <div data-layer="Rectangle 10" class="Rectangle10"
            style="width: 999px; height: 482px; left: 257px; top: 475px; position: absolute; opacity: 0.36; background: #472323; border-radius: 26px">
        </div>

        <div data-layer="Deskripsi" class="Deskripsi"
            style="width: 212px; height: 28px; left: 281px; top: 500px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 24px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Deskripsi</div>

        <div data-layer="ChannelName" class="ChannelName"
            style="width: 212px; height: 28px; left: 481px; top: 255px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 24px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Channel Name </div>

        <div data-layer="Email" class="Email"
            style="width: 212px; height: 28px; left: 481px; top: 320px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 24px; font-family: Roboto; font-weight: 700; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
            Email </div>
        <?php if ($canEdit): ?>
            <form method="POST" action="proses_edit_channel.php" enctype="multipart/form-data">
                <!-- Tombol pilih file -->
                <label for="foto" style="width: 104px; height: 43px; left: 318px; top: 370px; position: absolute; background: #795757; 
    border-radius: 12px; text-align: center; justify-content: center; display: flex; align-items: center; 
    text-decoration: none; cursor: pointer;">
                    <span style="color: #FFF3F3; font-size: 14px; font-family: Roboto; font-weight: 400; 
     line-height: 16px; letter-spacing: 0.40px;">Choose Photo</span>
                    <input type="file" id="foto" name="foto" accept="image/*" style="display: none;"
                        onchange="previewFoto(event)">
                </label>
                <input type="hidden" name="idChannel" value="<?= htmlspecialchars($channelID) ?>">

                <!-- Field username -->
                <input type="text" name="username" value="<?= htmlspecialchars($usernameLama) ?>" style="width: 537px; height: 60px; left: 656px; top: 237px; position: absolute; opacity: 0.9; 
        background: #472323; border-radius: 26px; color: white; font-size: 18px; padding-left: 20px; border: none;" />

                <!-- Field email readonly -->
                <input type="email" name="email" value="<?= htmlspecialchars($emailLama) ?>" readonly style="width: 537px; height: 60px; left: 656px; top: 307px; position: absolute; opacity: 0.9; 
        background: #472323; border-radius: 26px; color: white; font-size: 18px; padding-left: 20px; border: none;" />

                <!-- Tombol submit -->
                <button type="submit" name="update_channel" style="display: inline-block; width: 104px; height: 43px; left: 1109px; top: 970px; position: absolute; 
          background: #795757; border-radius: 12px; text-align: center; line-height: 43px; color: white; 
          text-decoration: none; font-family: Inter; font-size: 14px;">
                    Save
                </button>
                <textarea name="deskripsi"
                    style="width: 960px; height: 370px; left: 257px; top: 547px; position: absolute; opacity: 0.9; 
    background:rgb(119, 94, 94); border-radius: 26px; color: white; font-size: 18px; padding: 20px; border: none; resize: none;">
                                                <?= htmlspecialchars($deskripsiLama) ?>
                                                </textarea>
            </form>
        <?php else: ?>
            <input type="text" name="username" value="<?= htmlspecialchars($usernameLama) ?>" readonly style="width: 537px; height: 60px; left: 656px; top: 237px; position: absolute; opacity: 0.9; 
        background: #472323; border-radius: 26px; color: white; font-size: 18px; padding-left: 20px; border: none;" />

            <input type="email" name="email" value="<?= htmlspecialchars($emailLama) ?>" readonly style="width: 537px; height: 60px; left: 656px; top: 307px; position: absolute; opacity: 0.9; 
        background: #472323; border-radius: 26px; color: white; font-size: 18px; padding-left: 20px; border: none;" />
            <textarea name="deskripsi" readonly
                style="width: 960px; height: 370px; left: 257px; top: 547px; position: absolute; opacity: 0.9; 
    background:rgb(119, 94, 94); border-radius:     26px; color: white; font-size: 18px; padding: 20px; border: none; resize: none;">
                                                <?= htmlspecialchars($deskripsiLama) ?>
                                                </textarea>
        <?php endif; ?>


        
    </div>
    <script>
        function previewFoto(event) {
            const file = event.target.files[0];
            if (file) {
                const preview = document.getElementById('previewFoto');
                preview.src = URL.createObjectURL(file);
            }
        }
    </script>
</body>

</html>