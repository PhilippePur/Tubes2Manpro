<?php
session_start();
require_once 'testsql.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $channelName = $_POST['channel_name'] ?? '';
    $channelType = $_POST['channel_type'] ?? '';
    $descChannel = $_POST['description'] ?? '';
    $fotoProfil = null;

    $userId = $_SESSION['uid']; // pastikan user sudah login

    if (empty($channelName) || $channelType === '') {
        echo "<script>alert('Nama Channel dan Tipe wajib diisi.'); window.location.href='makeChannel.php';</script>";
        exit;
    }

    // Upload foto jika ada
    if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['photoProfil']['name']);
        $tmpPath = $_FILES['photoProfil']['tmp_name'];
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpPath, $targetPath)) {
            $fotoProfil = $targetPath;
        }
    }


    // Konversi tipe
    $channelTypeValue = ($channelType === 'Individual') ? 0 : 1;

    // Simpan ke tabel channel
    $sqlChannel = "INSERT INTO Channel (namaChannel, deskripsi, fotoProfil, channelType)
               OUTPUT INSERTED.IdChannel
               VALUES (?, ?, ?, ?)";
    $paramsChannel = [$channelName, $descChannel, $fotoProfil, $channelTypeValue];
    $stmt = sqlsrv_query($conn, $sqlChannel, $paramsChannel);

    if ($stmt === false) {
        die("Insert channel gagal: " . print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $newChannelID = $row['IdChannel'];

    $RoleOwner = 1;
    $sqlOWner = "SELECT A.idRole FROM [Admin] A INNER JOIN Roles R ON A.idRole = R.idRole WHERE RoleName = 'Owner' ";
    $stmtOwner = sqlsrv_query($conn, $sqlOWner);

    if ($stmtOwner && ($rowOwner = sqlsrv_fetch_array($stmtOwner, SQLSRV_FETCH_ASSOC))) {
        $RoleOwner = $rowOwner['idRole'];
    } else {
        die("Gagal mengambil RoleID untuk 'Owner': " . print_r(sqlsrv_errors(), true));
    }
    // Insert ke Admin (sebagai Owner)
    $sqlAdmin = "INSERT INTO [Admin] (idUser, idChannel, idRole, IsActive, CreatedAt)
                 VALUES (?, ?, ?, 1, GETDATE())";
    $paramsAdmin = [$userId, $newChannelID, $RoleOwner];
    $stmtAdmin = sqlsrv_query($conn, $sqlAdmin, $paramsAdmin);

    if ($stmtAdmin === false) {
        die("Gagal insert ke Admin: " . print_r(sqlsrv_errors(), true));
    }

    // Jika channel type = individual, update foto profil user
    if ($channelTypeValue === 0 && $fotoProfil) {
        $sqlUpdateUser = "UPDATE Users SET fotoProfil = ? WHERE Id = ?";
        sqlsrv_query($conn, $sqlUpdateUser, [$fotoProfil, $userId]);

        // Update session foto profil agar langsung tampil
        $_SESSION['fotoProfil'] = $fotoProfil;
    }

    // Redirect 
    if ($channelTypeValue == 0) {
        echo "<script>window.location.href='homePage.php';</script>";
    } else {
        echo "<script>window.location.href='homePage.php';</script>";
    }
    exit;

}

?>


<!DOCTYPE html>
<html>

<head>
    <title>Make Channel</title>
    <script>
        function toggleCheckbox(selected) {
            const checkboxes = document.getElementsByName('channel_type');
            checkboxes.forEach(cb => {
                if (cb !== selected) cb.checked = false;
            });
        }
    </script>
</head>

<body>
    <div data-layer="Make Channel" class="MakeChannel"
        style="width: 1512px; height: 1309px; position: relative; background: white; overflow: hidden">
        <a href="homepage.php" data-layer="Youtube-Logo" class="YoutubeLogo"
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
                        d="M4.87966 35.8523C3.26172 34.7664 2.10874 33.0772 1.42103 30.785C0.740195 28.4927 0.396484 25.4498 0.396484 21.6425V16.4612C0.396484 12.6205 0.787494 9.53066 1.56952 7.20481C2.35155 4.87898 3.57189 3.17647 5.23026 2.11075C6.88891 1.04502 9.06643 0.508789 11.7634 0.508789C14.4196 0.508789 16.5433 1.05171 18.1477 2.13756C19.7456 3.22338 20.9187 4.92589 21.6603 7.23163C22.4019 9.54406 22.7728 12.6205 22.7728 16.4612V21.6425C22.7728 25.4498 22.4088 28.5061 21.6875 30.8118C20.966 33.1242 19.7929 34.8134 18.1747 35.8791C16.5567 36.945 14.3589 37.481 11.5879 37.481C8.72931 37.4876 6.4979 36.9381 4.87966 35.8523ZM13.9544 30.2623C14.3993 29.0961 14.6286 27.1991 14.6286 24.5583V13.4384C14.6286 10.8779 14.4062 9.00113 13.9544 7.82146C13.5026 6.63509 12.714 6.04526 11.5811 6.04526C10.4892 6.04526 9.71373 6.63509 9.26882 7.82146C8.81703 9.00786 8.59457 10.8779 8.59457 13.4384V24.5583C8.59457 27.1991 8.81016 29.1026 9.24188 30.2623C9.67331 31.4285 10.4485 32.0116 11.5811 32.0116C12.714 32.0116 13.5026 31.4285 13.9544 30.2623Z"
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
            style="width: 829px; height: 761px; left: 103px; top: 181px; position: absolute; background: #D9D9D9; border-radius: 64px">
        </div>
        <div data-layer="Rectangle 53" class="Rectangle53"
            style="width: 829px; height: 1061px; left: 103px; top: 181px; position: absolute; background: #D9D9D9; border-radius: 64px">
        </div>
        <div data-layer="Make Channel" class="MakeChannel"
            style="width: 323px; height: 53px; left: 170px; top: 210px; position: absolute; color: black; font-size: 46px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Make Channel</div>

        <div data-layer="Name" class="Name"
            style="width: 89px; height: 36px; left: 174px; top: 499px; position: absolute; color: black; font-size: 28px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Name</div>

        <div data-layer="Foto Profil" class="FotoProfile"
            style="width: 277px; height: 36px; left: 401px; top: 280px; position: absolute; color: black; font-size: 30px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Select Photo Profile </div>
        <img id="previewImage" src="Profile/NoProfile.jpg"
            style="width: 130px; height: 130px; left: 455px; top: 323px; position: absolute; border-radius: 200px; object-fit: cover;"
            alt="Preview Foto">

        <div data-layer="Type" class="Type"
            style="width: 75px; height: 36px; left: 176px; top: 620px; position: absolute; color: black; font-size: 28px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Type</div>

        <div data-layer="Description" class="Description"
            style="width: 1005px; height: 36px; left: 176px; top: 715px; position: absolute; color: black; font-size: 28px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Description</div>

        <img data-layer="YTMASCOTT" class="YTMASCOTT"
            style="width: 510.92px; height: 369px; left: 944px; top: 312px; position: absolute"
            src="Assets/Youtube-Mascott.png">

        <form action="makeChannel.php" method="POST" enctype="multipart/form-data">
            <!-- Input untuk Nama Channel -->
            <input type="text" name="channel_name" placeholder="Nama Channel"
                style="width: 693px; height: 58px; left: 171px; top: 539px; position: absolute; background: white; border-radius: 12px"
                required>

            <!-- Input untuk Descripion -->
            <textarea name="description" placeholder="Description" style="width: 693px; height: 308px; left: 171px; top: 755px; position: absolute; 
         background: white; border-radius: 12px; padding: 12px; resize: none; font-size: 16px;"></textarea>

            <!-- Group -->
            <label style="position: absolute; left: 171px; top: 666px; display: flex; align-items: center; gap: 12px;">
                <input type="radio" name="channel_type" value="Group" required
                    style="width: 20px; height: 20px; transform: scale(1.5); accent-color: #000;">
                <span style="font-size: 28px; font-family: Inter;">Group</span>
            </label>

            <!-- Individual -->
            <label style="position: absolute; left: 321px; top: 666px; display: flex; align-items: center; gap: 12px;">
                <input type="radio" name="channel_type" value="Individual" required
                    style="width: 20px; height: 20px; transform: scale(1.5); accent-color: #000;">
                <span style="font-size: 28px; font-family: Inter;">Individual</span>
            </label>

            <!-- Tombol Submit -->
            <button type="submit"
                style="width: 333px; height: 58px; left: 351px; top: 1121px; position: absolute; background: white; border-radius: 12px; font-size: 16px; cursor: pointer;">
                Make Channel
            </button>
            <!-- Select Photo -->
            <input type="file" id="photoInput" name="photoProfil" accept="image/*" style="display: none;"
                onchange="document.getElementById('labelFoto').innerText = this.files[0]?.name || 'Select Photo'">

            <button type="button" onclick="document.getElementById('photoInput').click();" style="width: 150px; height: 40px; left: 450px; top: 460px; position: absolute;
               background: #4CAF50; border-radius: 12px; border: none; color: white;
               font-size: 20px; font-weight: bold; cursor: pointer; display: flex;
               align-items: center; justify-content: center;">
                Select Photo

        </form>
    </div>
    <script>
        document.getElementById('photoInput').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewImage').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>