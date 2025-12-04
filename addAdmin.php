<?php
require_once 'testsql.php';
session_start();

$userId = $_SESSION['uid'];
$username = $_SESSION['uname'];
$fotoProfil = $_SESSION['fotoProfil'] ?: 'Assets/NoProfile.jpg';// path foto profil
$generatedLink = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['email']) || empty($_POST['role'])) {
        echo "<script>alert('Email dan Role wajib diisi!'); window.location.href='addAdmin.php';</script>";
        exit;
    }


    

    $email = $_POST['email'];
    $roleID = $_POST['role'];
    $userid = $_SESSION['uid'];

    // Ambil ChannelID milik user yang sedang login
    $channelSql = "SELECT idChannel FROM Admin WHERE idUser = ?";
    $channelStmt = sqlsrv_query($conn, $channelSql, [$userid]);

    if ($channelStmt === false || !sqlsrv_has_rows($channelStmt)) {
        echo "<script>alert('Kamu belum memiliki channel!'); window.location.href='makeChannel.php';</script>";
        exit;
    }

    $channelData = sqlsrv_fetch_array($channelStmt, SQLSRV_FETCH_ASSOC);
    $channelID = $channelData['idChannel'];

    // Cek apakah email valid dan ambil user ID
    $checkSql = "
        SELECT U.idUser 
        FROM Users U
        WHERE U.Email = ?
    ";
    $checkStmt = sqlsrv_query($conn, $checkSql, [$email]);

    if ($checkStmt === false) {
        echo "<script>alert('Terjadi kesalahan saat mengecek data.'); window.location.href='addAdmin.php';</script>";
        exit;
    }

    $userData = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if (!$userData) {
        echo "<script>alert('Email tidak ditemukan di database!'); window.location.href='addAdmin.php';</script>";
        exit;
    }

    $newUserId = $userData['idUser'];

    // Cek apakah user tersebut sudah admin di channel ini
    $checkAdminSql = "SELECT * FROM Admin WHERE idUser = ?";
    $stmtAdminCheck = sqlsrv_query($conn, $checkAdminSql, [$newUserId, $channelID]);

    if (sqlsrv_has_rows($stmtAdminCheck)) {
        echo "<script>alert('User ini sudah menjadi admin di channel kamu!'); window.location.href='addAdmin.php';</script>";
        exit;
    }

    // Insert ke Admin
    $sqlInsert = "
        INSERT INTO Admin (idUser, idChannel, idRole, IsActive, CreatedAt)
        VALUES (?, ?, ?, 2, GETDATE())
    ";
    $stmtInsert = sqlsrv_query($conn, $sqlInsert, [$newUserId, $channelID, $roleID]);

    if ($stmtInsert === false) {
        echo "<script>alert('Gagal menambahkan admin!'); window.location.href='addAdmin.php';</script>";
    } else {
        echo "<script>alert('Admin berhasil ditambahkan!'); window.location.href='updateChannel.php';</script>";
    }
}


?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Tambah Admin</title>
</head>

<body>
    <div data-layer="Add Admin" class="AddAdmin"
        style="width: 1512px; height: 1009px; position: relative; background: white; overflow: hidden">
        <a href="homepage.php" data-layer=" Youtube-Logo" class="YoutubeLogo"
            style="width: 258px; height: 57px; left: 56px; top: 53px; position: absolute; overflow: hidden">
            <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 0px; top: 0px; position: absolute">
                <svg width="82" height="57" viewBox="0 0 82 57" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M80.1884 8.90123C79.2447 5.39571 76.4735 2.64088 72.9475 1.70249C66.5631 5.0962e-07 40.9503 0 40.9503 0C40.9503 0 15.3379 5.0962e-07 8.95326 1.70249C5.42726 2.64088 2.65632 5.39571 1.71245 8.90123C5.126e-07 15.2487 0 28.5 0 28.5C0 28.5 5.126e-07 41.7514 1.71245 48.0989C2.65632 51.6044 5.42726 54.3592 8.95326 55.2974C15.3379 57 40.9503 57 40.9503 57C40.9503 57 66.5631 57 72.9475 55.2974C76.4735 54.3592 79.2447 51.6044 80.1884 48.0989C81.9009 41.7514 81.901 28.5 81.901 28.5C81.901 28.5 81.8941 15.2487 80.1884 8.90123Z"
                        fill="#FF0000" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 32.75px; top: 16.29px; position: absolute">
                <svg width="23" height="25" viewBox="0 0 23 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.752441 24.7133L22.03 12.501L0.752441 0.288574V24.7133Z" fill="white" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 90px; top: 4.04px; position: absolute">
                <svg width="27" height="49" viewBox="0 0 27 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.19354 33.0606L-0.00244141 0.0429688H8.0205L11.2432 15.0101C12.0657 18.6966 12.6656 21.8402 13.0567 24.4409H13.2926C13.5623 22.5775 14.1692 19.4541 15.1063 15.0638L18.4434 0.0429688H26.4664L17.1557 33.0606V48.8992H9.18695V33.0606H9.19354Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 114.40px; top: 16.51px; position: absolute">
                <svg width="23" height="38" viewBox="0 0 23 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M4.87991 35.8523C3.26196 34.7664 2.10899 33.0772 1.42127 30.785C0.740439 28.4927 0.396729 25.4498 0.396729 21.6425V16.4612C0.396729 12.6205 0.787738 9.53066 1.56976 7.20481C2.35179 4.87898 3.57213 3.17647 5.2305 2.11075C6.88915 1.04502 9.06667 0.508789 11.7636 0.508789C14.4199 0.508789 16.5435 1.05171 18.148 2.13756C19.7459 3.22338 20.9189 4.92589 21.6605 7.23163C22.4021 9.54406 22.7731 12.6205 22.7731 16.4612V21.6425C22.7731 25.4498 22.409 28.5061 21.6878 30.8118C20.9662 33.1242 19.7932 34.8134 18.1749 35.8791C16.557 36.945 14.3591 37.481 11.5882 37.481C8.72955 37.4876 6.49814 36.9381 4.87991 35.8523ZM13.9546 30.2623C14.3995 29.0961 14.6289 27.1991 14.6289 24.5583V13.4384C14.6289 10.8779 14.4064 9.00113 13.9546 7.82146C13.5028 6.63509 12.7142 6.04526 11.5813 6.04526C10.4894 6.04526 9.71397 6.63509 9.26906 7.82146C8.81728 9.00786 8.59482 10.8779 8.59482 13.4384V24.5583C8.59482 27.1991 8.8104 29.1026 9.24212 30.2623C9.67355 31.4285 10.4487 32.0116 11.5813 32.0116C12.7142 32.0116 13.5028 31.4285 13.9546 30.2623Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 140.97px; top: 17.20px; position: absolute">
                <svg width="23" height="37" viewBox="0 0 23 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22.8708 35.9057H16.5469L15.8457 31.5355H15.6706C13.9512 34.8332 11.3757 36.4819 7.93746 36.4819C5.55755 36.4819 3.79799 35.7045 2.66537 34.1563C1.53275 32.6011 0.966309 30.1749 0.966309 26.8772V0.206892H9.05002V26.4078C9.05002 28.0032 9.22517 29.1358 9.57577 29.8129C9.92636 30.4898 10.5129 30.8315 11.3353 30.8315C12.0365 30.8315 12.7108 30.6172 13.358 30.1883C14.0053 29.7591 14.4772 29.2162 14.794 28.5595V0.200195H22.8708V35.9057Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 160.88px; top: 4.05px; position: absolute">
                <svg width="25" height="49" viewBox="0 0 25 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24.8297 6.50977H16.8067V48.9044H8.89843V6.50977H0.875488V0.0483398H24.8297V6.50977Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 182.42px; top: 17.20px; position: absolute">
                <svg width="23" height="37" viewBox="0 0 23 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22.3269 35.9057H16.003L15.3018 31.5355H15.1266C13.4075 34.8332 10.8321 36.4819 7.39352 36.4819C5.01361 36.4819 3.25406 35.7045 2.12144 34.1563C0.988819 32.6011 0.422363 30.1749 0.422363 26.8772V0.206892H8.50607V26.4078C8.50607 28.0032 8.68123 29.1358 9.03182 29.8129C9.38242 30.4898 9.96894 30.8315 10.7917 30.8315C11.4926 30.8315 12.1668 30.6172 12.8141 30.1883C13.4614 29.7591 13.9332 29.2162 14.25 28.5595V0.200195H22.3269V35.9057Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 209.20px; top: 2.24px; position: absolute">
                <svg width="23" height="52" viewBox="0 0 23 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22.0792 20.9105C21.587 18.6584 20.7981 17.0296 19.7062 16.0175C18.614 15.0054 17.1104 14.5027 15.1958 14.5027C13.7125 14.5027 12.3236 14.9182 11.0359 15.7561C9.74823 16.5939 8.75034 17.6865 8.04915 19.0471H7.98868V0.239258H0.20166V50.8984H6.87612L7.69856 47.5203H7.87401C8.50095 48.7267 9.43805 49.6718 10.6853 50.3758C11.9326 51.0729 13.3215 51.4214 14.8452 51.4214C17.5757 51.4214 19.5915 50.168 20.8792 47.6677C22.1669 45.1608 22.8139 41.2532 22.8139 35.9314V30.281C22.8139 26.2927 22.5645 23.1626 22.0792 20.9105ZM14.6697 35.4754C14.6697 38.076 14.5619 40.1138 14.3461 41.5884C14.1305 43.0629 13.773 44.1155 13.2608 44.7319C12.7551 45.3552 12.0674 45.6636 11.2111 45.6636C10.5437 45.6636 9.93026 45.5094 9.36381 45.1945C8.79764 44.8861 8.33927 44.417 7.98868 43.8002V23.5379C8.25814 22.566 8.73029 21.7751 9.39765 21.1518C10.0584 20.5284 10.7865 20.2201 11.5617 20.2201C12.3844 20.2201 13.018 20.5418 13.4631 21.1786C13.9146 21.822 14.2248 22.8945 14.4003 24.4093C14.5754 25.9241 14.6631 28.0756 14.6631 30.8706V35.4754H14.6697Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 235.38px; top: 16.52px; position: absolute">
                <svg width="22" height="38" viewBox="0 0 22 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M8.28177 23.5329C8.28177 25.8252 8.34914 27.5411 8.48416 28.6874C8.6189 29.8334 8.90211 30.6647 9.33354 31.1943C9.76498 31.717 10.4257 31.9783 11.3224 31.9783C12.5293 31.9783 13.3652 31.5092 13.8101 30.5775C14.2619 29.6459 14.5047 28.0909 14.5451 25.9192L21.5163 26.3279C21.5567 26.6363 21.5768 27.0652 21.5768 27.6081C21.5768 30.9059 20.6666 33.3725 18.8532 35.0013C17.0397 36.6301 14.4709 37.4477 11.1539 37.4477C7.1695 37.4477 4.37824 36.2077 2.78036 33.7211C1.17588 31.2345 0.380371 27.3938 0.380371 22.1923V15.9587C0.380371 10.6034 1.20971 6.68898 2.86808 4.22239C4.52673 1.75577 7.365 0.522461 11.3898 0.522461C14.1607 0.522461 16.2912 1.02517 17.7745 2.03729C19.2577 3.04938 20.3026 4.61783 20.9094 6.75601C21.5163 8.89417 21.8196 11.8434 21.8196 15.6102V21.7232H8.28177V23.5329ZM9.3066 6.7091C8.89523 7.21178 8.62578 8.03623 8.48416 9.18239C8.34914 10.3286 8.28177 12.0646 8.28177 14.3972V16.9577H14.1946V14.3972C14.1946 12.1048 14.1137 10.3688 13.9586 9.18239C13.8035 7.99602 13.5203 7.16487 13.1089 6.67558C12.6979 6.19299 12.064 5.94498 11.2078 5.94498C10.3449 5.95168 9.71108 6.20639 9.3066 6.7091Z"
                        fill="black" />
                </svg>
            </div>
        </a>
        <div data-layer="Rectangle 51" class="Rectangle51"
            style="width: 829px; height: 778px; left: 103px; top: 181px; position: absolute; background: #D9D9D9; border-radius: 64px">
        </div>
        <div data-layer="Rectangle 53" class="Rectangle53"
            style="width: 829px; height: 746px; left: 103px; top: 187px; position: absolute; background: #D9D9D9; border-radius: 64px">
        </div>
        <div data-layer="Add Admin" class="AddAdmin"
            style="left: 170px; top: 254px; position: absolute; color: black; font-size: 48px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Add Admin</div>
        <div data-layer="Email" class="Email"
            style="left: 174px; top: 364px; position: absolute; color: black; font-size: 32px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Email</div>
        <div data-layer="Roles" class="Roles"
            style="left: 174px; top: 491px; position: absolute; color: black; font-size: 32px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Roles</div>
        <!-- Teks judul -->
        <div
            style="position: absolute; left: 176px; top: 796px; color: black; font-size: 32px; font-family: Inter; font-weight: 400;">
            Copy This Link for Invite
        </div>

        <!-- Input berisi link -->
        <input type="text" id="generatedLink" readonly
            value="<?= isset($generatedLink) ? htmlspecialchars($generatedLink) : '' ?>"
            style="position: absolute; left: 176px; top: 840px; width: 680px; padding: 10px; font-size: 16px; border-radius: 8px; border: 1px solid #ccc;">


        <form action="addAdmin.php" method="post">
            <!-- Input Email -->
            <input type="email" name="email"
                style="width: 693px; height: 63px; left: 171px; top: 408px; position: absolute; background: white; border-radius: 12px; padding-left: 12px; font-size: 16px;">

            <!-- Dropdown Role -->
            <select name="role" required style="width: 159px; height: 48px; position: absolute; top: 500px; left: 171px;
               padding: 12px; background: #4A4A4A; color: white; border-radius: 5px; border: none; margin-top: 27px;
               font-family: Inter; font-size: 14px;">
                <option value="">Pilih Role</option>
                <option value="2">Manager</option>
                <option value="3">Editor</option>
                <option value="4">Admin</option>
                <option value="5">Subtitle Editor</option>
                <option value="6">Viewer</option>
            </select>

            <!-- Tombol Submit -->
            <button type="submit" name="addAdmin" style="width: 333px; height: 63px; position: absolute; top: 600px; left: 350px;
               background: white; border-radius: 12px; cursor: pointer; font-size: 16px;">
                Tambah Admin
            </button>
        </form>


        <img data-layer="YTMASCOTT" class="YTMASCOTT"
            style="width: 510.92px; height: 369px; left: 944px; top: 312px; position: absolute"
            src="Assets/Youtube-Mascott.png">
        <!-- <div data-layer="Rectangle 54" class="Rectangle54"
            style="width: 693px; height: 63px; left: 171px; top: 847px; position: absolute; background: white; border-radius: 12px">
        </div> -->

        <div data-svg-wrapper data-layer="Vector" class="Vector">
            <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 7L0 1.0636L1.075 0L6 4.89753L10.925 0.024735L12 1.08834L6 7Z" fill="white" />
            </svg>
        </div>
    </div>
    </div>
</body>

</html>