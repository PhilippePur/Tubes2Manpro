<?php
session_start();
require_once 'testsql.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $uploadDir = 'Profile/'; // folder tempat menyimpan file
    $fotoProfilPath = 'Profile/NoProfile.jpg'; // default path

    // Validasi password dulu
    if ($password !== $confirm_password) {
        echo "<script>alert('Password dan konfirmasi tidak sama!'); window.location.href='register.php';</script>";
        exit;
    }

    
    // Cek username/email sudah dipakai
    $sqlCheck = "SELECT username FROM Users WHERE username = ? OR email = ?";
    $paramsCheck = [$username, $email];
    $stmtCheck = sqlsrv_query($conn, $sqlCheck, $paramsCheck);

    if ($stmtCheck === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmtCheck)) {
        echo "<script>alert('Username atau email sudah digunakan!'); window.location.href='register.php';</script>";
        exit;
    }

    // *** Proses upload foto dulu ***
    if (isset($_FILES['fotoProfil']) && $_FILES['fotoProfil']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['fotoProfil']['tmp_name'];
        $fileName = basename($_FILES['fotoProfil']['name']);
        $filePath = $uploadDir . uniqid() . '_' . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($tmpName, $filePath)) {
            $fotoProfilPath = $filePath; // update path dengan file upload
        }
    }

    // Insert user dengan path foto yang sudah di-set
    $sqlInsert = "INSERT INTO Users (Username, Email, Pass, fotoProfil) VALUES (?, ?, ?, ?)";
    $paramsInsert = [$username, $email, $password, $fotoProfilPath];
    $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);

    if ($stmtInsert) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal registrasi!'); window.location.href='register.php';</script>";
    }

    exit;
}

?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f7f7f7;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            margin: auto;
        }

        input[type=text],
        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            padding: 12px;
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .back-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-login a {
            color: #007bff;
            text-decoration: none;
        }

        .back-login a:hover {}
    </style>
</head>

<body>
    <div data-layer="Register Page" class="RegisterPage"
        style="width: 1512px; height: 1200px; position: relative; background: white; overflow: hidden">
        <div data-layer="Youtube-Logo" class="YoutubeLogo"
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
                        d="M9.19354 33.0601L-0.00244141 0.0424805H8.0205L11.2432 15.0097C12.0657 18.6962 12.6656 21.8397 13.0567 24.4404H13.2926C13.5623 22.577 14.1692 19.4536 15.1063 15.0633L18.4434 0.0424805H26.4664L17.1557 33.0601V48.8987H9.18695V33.0601H9.19354Z"
                        fill="black" />
                </svg>
            </div>
            <div data-svg-wrapper data-layer="Vector" class="Vector"
                style="left: 114.40px; top: 16.51px; position: absolute">
                <svg width="23" height="38" viewBox="0 0 23 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M4.87966 35.852C3.26172 34.7662 2.10874 33.077 1.42103 30.7847C0.740195 28.4925 0.396484 25.4495 0.396484 21.6422V16.4609C0.396484 12.6202 0.787494 9.53042 1.56952 7.20456C2.35155 4.87874 3.57189 3.17623 5.23026 2.1105C6.88891 1.04477 9.06643 0.508545 11.7634 0.508545C14.4196 0.508545 16.5433 1.05147 18.1477 2.13732C19.7456 3.22314 20.9187 4.92565 21.6603 7.23138C22.4019 9.54381 22.7728 12.6202 22.7728 16.4609V21.6422C22.7728 25.4495 22.4088 28.5059 21.6875 30.8115C20.966 33.124 19.7929 34.8132 18.1747 35.8788C16.5567 36.9447 14.3589 37.4808 11.5879 37.4808C8.72931 37.4873 6.4979 36.9379 4.87966 35.852ZM13.9544 30.262C14.3993 29.0958 14.6286 27.1988 14.6286 24.558V13.4382C14.6286 10.8777 14.4062 9.00089 13.9544 7.82122C13.5026 6.63485 12.714 6.04501 11.5811 6.04501C10.4892 6.04501 9.71373 6.63485 9.26882 7.82122C8.81703 9.00761 8.59457 10.8777 8.59457 13.4382V24.558C8.59457 27.1988 8.81016 29.1024 9.24188 30.262C9.67331 31.4282 10.4485 32.0114 11.5811 32.0114C12.714 32.0114 13.5026 31.4282 13.9544 30.262Z"
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
                    <path d="M24.8297 6.50953H16.8067V48.9042H8.89843V6.50953H0.875488V0.0480957H24.8297V6.50953Z"
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
                        d="M22.0792 20.9102C21.587 18.6581 20.7981 17.0293 19.7062 16.0172C18.614 15.0051 17.1104 14.5024 15.1958 14.5024C13.7125 14.5024 12.3236 14.918 11.0359 15.7558C9.74823 16.5937 8.75034 17.6862 8.04915 19.0469H7.98868V0.239014H0.20166V50.8982H6.87612L7.69856 47.5201H7.87401C8.50095 48.7265 9.43805 49.6716 10.6853 50.3755C11.9326 51.0726 13.3215 51.4212 14.8452 51.4212C17.5757 51.4212 19.5915 50.1678 20.8792 47.6674C22.1669 45.1606 22.8139 41.253 22.8139 35.9311V30.2807C22.8139 26.2925 22.5645 23.1623 22.0792 20.9102ZM14.6697 35.4751C14.6697 38.0758 14.5619 40.1135 14.3461 41.5881C14.1305 43.0627 13.773 44.1152 13.2608 44.7317C12.7551 45.355 12.0674 45.6633 11.2111 45.6633C10.5437 45.6633 9.93026 45.5091 9.36381 45.1942C8.79764 44.8858 8.33927 44.4167 7.98868 43.8V23.5377C8.25814 22.5658 8.73029 21.7749 9.39765 21.1515C10.0584 20.5282 10.7865 20.2198 11.5617 20.2198C12.3844 20.2198 13.018 20.5416 13.4631 21.1783C13.9146 21.8218 14.2248 22.8942 14.4003 24.409C14.5754 25.9239 14.6631 28.0754 14.6631 30.8704V35.4751H14.6697Z"
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
        </div>
        <div data-layer="Rectangle 51" class="Rectangle51"
            style="width: 829px; height: 1000px; left: 103px; top: 181px; position: absolute; background: #D9D9D9; border-radius: 64px">
        </div>

        <a href="index.php"
            style="width: 125px; height: 54px; left: 170px; top: 250px; position: absolute; color: black; font-size: 46px; font-family: Inter; font-weight: 400; word-wrap: break-word; text-decoration: none;">
            Login
        </a>
        <div data-layer="Register" class="Register"
            style="width: 197px; height: 54px; left: 339px; top: 250px; position: absolute; opacity: 0.90; color: black; font-size: 48px; font-family: Inter; font-weight: 700; word-wrap: break-word">
            Register</div>

        <div data-layer="Username" class="Username"
            style="width: 155px; height: 36px; left: 174px; top: 583px; position: absolute; color: black; font-size: 30px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Username</div>
        <div data-layer="Email" class="Email"
            style="width: 81px; height: 37px; left: 174px; top: 698px; position: absolute; color: black; font-size: 30px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Email</div>
        <div data-layer="Confirm Password" class="ConfirmPassword"
            style="width: 277px; height: 36px; left: 174px; top: 934px; position: absolute; color: black; font-size: 30px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Confirm Password</div>
        <div data-layer="Password" class="Password"
            style="width: 148px; height: 36px; left: 174px; top: 815px; position: absolute; color: black; font-size: 30px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Password</div>
        <div data-layer="Foto Profil" class="FotoProfile"
            style="width: 277px; height: 36px; left: 401px; top: 340px; position: absolute; color: black; font-size: 30px; font-family: Inter; font-weight: 400; word-wrap: break-word">
            Select Photo Profile </div>
        <img id="previewImage" src="Profile/NoProfile.jpg"
            style="width: 130px; height: 130px; left: 455px; top: 373px; position: absolute; border-radius: 200px; object-fit: cover;"
            alt="Preview Foto">


        <img data-layer="YoutubeMascott" class="YoutubeMascott"
            style="width: 510.92px; height: 369px; left: 944px; top: 312px; position: absolute"
            src="Assets/Youtube-Mascott.png">


        <form method="POST" action="register.php" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required style="width: 693px; height: 59px; left: 171px; top: 624px; position: absolute; 
           background: white; border-radius: 12px; border: 1px solid #ccc; 
           padding-left: 16px; font-size: 20px; box-sizing: border-box; color: black;">

            <input type="password" name="password" placeholder="Password" required style="width: 693px; height: 59px; left: 171px; top: 854px; position: absolute; 
           background: white; border-radius: 12px; border: 1px solid #ccc; 
           padding-left: 16px; font-size: 20px; box-sizing: border-box; color: black;">

            <input type="email" name="email" placeholder="Email" required style="width: 693px; height: 59px; left: 171px; top: 736px; position: absolute; 
           background: white; border-radius: 12px; border: 1px solid #ccc; 
           padding-left: 16px; font-size: 20px; box-sizing: border-box; color: black;">

            <input type="password" name="confirm_password" placeholder="Confirm Password" required style="width: 693px; height: 59px; left: 171px; top: 972px; position: absolute; 
           background: white; border-radius: 12px; border: 1px solid #ccc; 
           padding-left: 16px; font-size: 20px; box-sizing: border-box; color: black;">

            <button type="submit" name="register" style="width: 333px; height: 59px; left: 351px; top: 1080px; position: absolute; 
           background: #4CAF50; border-radius: 12px; border: none; color: white; 
           font-size: 24px; font-weight: bold; cursor: pointer;">
                Register
            </button>

            <input type="file" id="photoInput" name="fotoProfil" accept="image/*" style="display: none;"
                onchange="document.getElementById('labelFoto').innerText = this.files[0]?.name || 'Select Photo'">


            <button type="button" onclick="document.getElementById('photoInput').click();" style="width: 150px; height: 40px; left: 450px; top: 520px; position: absolute;
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