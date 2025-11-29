<?php session_start(); /* kalau sudah login, langsung alihkan */ ?>
<!DOCTYPE html>

<head>
  <title>Login Page</title>
</head>

<body>
  <div data-layer="Login Page" class="LoginPage"
    style="width: 1512px; height: 1009px; position: relative; background: white; overflow: hidden">
    <div data-layer="Youtube-Logo" class="YoutubeLogo"
      style="width: 258px; height: 57px; left: 56px; top: 53px; position: absolute; overflow: hidden">
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 0px; top: 0px; position: absolute">
        <svg width="82" height="57" viewBox="0 0 82 57" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M80.1884 8.90123C79.2447 5.39571 76.4735 2.64088 72.9475 1.70249C66.5631 5.0962e-07 40.9503 0 40.9503 0C40.9503 0 15.3379 5.0962e-07 8.95326 1.70249C5.42726 2.64088 2.65632 5.39571 1.71245 8.90123C5.126e-07 15.2487 0 28.5 0 28.5C0 28.5 5.126e-07 41.7514 1.71245 48.0989C2.65632 51.6044 5.42726 54.3592 8.95326 55.2974C15.3379 57 40.9503 57 40.9503 57C40.9503 57 66.5631 57 72.9475 55.2974C76.4735 54.3592 79.2447 51.6044 80.1884 48.0989C81.9009 41.7514 81.901 28.5 81.901 28.5C81.901 28.5 81.8941 15.2487 80.1884 8.90123Z"
            fill="#FF0000" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 32.75px; top: 16.29px; position: absolute">
        <svg width="23" height="25" viewBox="0 0 23 25" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.752441 24.7133L22.03 12.501L0.752441 0.288574V24.7133Z" fill="white" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 90px; top: 4.04px; position: absolute">
        <svg width="27" height="49" viewBox="0 0 27 49" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M9.19354 33.0601L-0.00244141 0.0424805H8.0205L11.2432 15.0097C12.0657 18.6962 12.6656 21.8397 13.0567 24.4404H13.2926C13.5623 22.577 14.1692 19.4536 15.1063 15.0633L18.4434 0.0424805H26.4664L17.1557 33.0601V48.8987H9.18695V33.0601H9.19354Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 114.40px; top: 16.51px; position: absolute">
        <svg width="23" height="38" viewBox="0 0 23 38" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M4.87991 35.852C3.26196 34.7662 2.10899 33.077 1.42127 30.7847C0.740439 28.4925 0.396729 25.4495 0.396729 21.6422V16.4609C0.396729 12.6202 0.787738 9.53042 1.56976 7.20456C2.35179 4.87874 3.57213 3.17623 5.2305 2.1105C6.88915 1.04477 9.06667 0.508545 11.7636 0.508545C14.4199 0.508545 16.5435 1.05147 18.148 2.13732C19.7459 3.22314 20.9189 4.92565 21.6605 7.23138C22.4021 9.54381 22.7731 12.6202 22.7731 16.4609V21.6422C22.7731 25.4495 22.409 28.5059 21.6878 30.8115C20.9662 33.124 19.7932 34.8132 18.1749 35.8788C16.557 36.9447 14.3591 37.4808 11.5882 37.4808C8.72955 37.4873 6.49814 36.9379 4.87991 35.852ZM13.9546 30.262C14.3995 29.0958 14.6289 27.1988 14.6289 24.558V13.4382C14.6289 10.8777 14.4064 9.00089 13.9546 7.82122C13.5028 6.63485 12.7142 6.04501 11.5813 6.04501C10.4894 6.04501 9.71397 6.63485 9.26906 7.82122C8.81728 9.00761 8.59482 10.8777 8.59482 13.4382V24.558C8.59482 27.1988 8.8104 29.1024 9.24212 30.262C9.67355 31.4282 10.4487 32.0114 11.5813 32.0114C12.7142 32.0114 13.5028 31.4282 13.9546 30.262Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 140.97px; top: 17.20px; position: absolute">
        <svg width="23" height="37" viewBox="0 0 23 37" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M22.8708 35.9057H16.5469L15.8457 31.5355H15.6706C13.9512 34.8332 11.3757 36.4819 7.93746 36.4819C5.55755 36.4819 3.79799 35.7045 2.66537 34.1563C1.53275 32.6011 0.966309 30.1749 0.966309 26.8772V0.206892H9.05002V26.4078C9.05002 28.0032 9.22517 29.1358 9.57577 29.8129C9.92636 30.4898 10.5129 30.8315 11.3353 30.8315C12.0365 30.8315 12.7108 30.6172 13.358 30.1883C14.0053 29.7591 14.4772 29.2162 14.794 28.5595V0.200195H22.8708V35.9057Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 160.88px; top: 4.05px; position: absolute">
        <svg width="25" height="49" viewBox="0 0 25 49" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M24.8297 6.50953H16.8067V48.9042H8.89843V6.50953H0.875488V0.0480957H24.8297V6.50953Z" fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 182.42px; top: 17.20px; position: absolute">
        <svg width="23" height="37" viewBox="0 0 23 37" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M22.3269 35.9057H16.003L15.3018 31.5355H15.1266C13.4075 34.8332 10.8321 36.4819 7.39352 36.4819C5.01361 36.4819 3.25406 35.7045 2.12144 34.1563C0.988819 32.6011 0.422363 30.1749 0.422363 26.8772V0.206892H8.50607V26.4078C8.50607 28.0032 8.68123 29.1358 9.03182 29.8129C9.38242 30.4898 9.96894 30.8315 10.7917 30.8315C11.4926 30.8315 12.1668 30.6172 12.8141 30.1883C13.4614 29.7591 13.9332 29.2162 14.25 28.5595V0.200195H22.3269V35.9057Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 209.20px; top: 2.24px; position: absolute">
        <svg width="23" height="52" viewBox="0 0 23 52" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M22.0792 20.9102C21.587 18.6581 20.7981 17.0293 19.7062 16.0172C18.614 15.0051 17.1104 14.5024 15.1958 14.5024C13.7125 14.5024 12.3236 14.918 11.0359 15.7558C9.74823 16.5937 8.75034 17.6862 8.04915 19.0469H7.98868V0.239014H0.20166V50.8982H6.87612L7.69856 47.5201H7.87401C8.50095 48.7265 9.43805 49.6716 10.6853 50.3755C11.9326 51.0726 13.3215 51.4212 14.8452 51.4212C17.5757 51.4212 19.5915 50.1678 20.8792 47.6674C22.1669 45.1606 22.8139 41.253 22.8139 35.9311V30.2807C22.8139 26.2925 22.5645 23.1623 22.0792 20.9102ZM14.6697 35.4751C14.6697 38.0758 14.5619 40.1135 14.3461 41.5881C14.1305 43.0627 13.773 44.1152 13.2608 44.7317C12.7551 45.355 12.0674 45.6633 11.2111 45.6633C10.5437 45.6633 9.93026 45.5091 9.36381 45.1942C8.79764 44.8858 8.33927 44.4167 7.98868 43.8V23.5377C8.25814 22.5658 8.73029 21.7749 9.39765 21.1515C10.0584 20.5282 10.7865 20.2198 11.5617 20.2198C12.3844 20.2198 13.018 20.5416 13.4631 21.1783C13.9146 21.8218 14.2248 22.8942 14.4003 24.409C14.5754 25.9239 14.6631 28.0754 14.6631 30.8704V35.4751H14.6697Z"
            fill="black" />
        </svg>
      </div>
      <div data-svg-wrapper data-layer="Vector" class="Vector" style="left: 235.38px; top: 16.52px; position: absolute">
        <svg width="22" height="38" viewBox="0 0 22 38" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M8.28177 23.5329C8.28177 25.8252 8.34914 27.5411 8.48416 28.6874C8.6189 29.8334 8.90211 30.6647 9.33354 31.1943C9.76498 31.717 10.4257 31.9783 11.3224 31.9783C12.5293 31.9783 13.3652 31.5092 13.8101 30.5775C14.2619 29.6459 14.5047 28.0909 14.5451 25.9192L21.5163 26.3279C21.5567 26.6363 21.5768 27.0652 21.5768 27.6081C21.5768 30.9059 20.6666 33.3725 18.8532 35.0013C17.0397 36.6301 14.4709 37.4477 11.1539 37.4477C7.1695 37.4477 4.37824 36.2077 2.78036 33.7211C1.17588 31.2345 0.380371 27.3938 0.380371 22.1923V15.9587C0.380371 10.6034 1.20971 6.68898 2.86808 4.22239C4.52673 1.75577 7.365 0.522461 11.3898 0.522461C14.1607 0.522461 16.2912 1.02517 17.7745 2.03729C19.2577 3.04938 20.3026 4.61783 20.9094 6.75601C21.5163 8.89417 21.8196 11.8434 21.8196 15.6102V21.7232H8.28177V23.5329ZM9.3066 6.7091C8.89523 7.21178 8.62578 8.03623 8.48416 9.18239C8.34914 10.3286 8.28177 12.0646 8.28177 14.3972V16.9577H14.1946V14.3972C14.1946 12.1048 14.1137 10.3688 13.9586 9.18239C13.8035 7.99602 13.5203 7.16487 13.1089 6.67558C12.6979 6.19299 12.064 5.94498 11.2078 5.94498C10.3449 5.95168 9.71108 6.20639 9.3066 6.7091Z"
            fill="black" />
        </svg>
      </div>
    </div>
    <div data-layer="Rectangle 51" class="Rectangle51"
      style="width: 829px; height: 780px; left: 99px; top: 182px; position: absolute; background: #D9D9D9; border-radius: 64px">
    </div>
    <div data-layer="Forgot Password" class="ForgotPassword"
      style="width: 184px; left: 170px; top: 616px; position: absolute; justify-content: center; display: flex; flex-direction: column; color: black; font-size: 20px; font-family: Roboto; font-weight: 400; text-decoration: underline; line-height: 16px; letter-spacing: 0.40px; word-wrap: break-word">
      Forgot Password</div>
    <div data-layer="Login" class="Login"
      style="width: 169px; left: 160px; top: 254px; position: absolute; color: black; font-size: 48px; font-family: Inter; font-weight: 700; word-wrap: break-word">
      Login</div>
    <a href="register.php"
      style="display: inline-block; width: 266px; left: 330px; top: 254px; position: absolute; opacity: 0.90; color: black; font-size: 48px; font-family: Inter; font-weight: 400; word-wrap: break-word; text-decoration: none;">
      Register
    </a>
    <div data-layer="Username / Email" class="UsernameEmail"
      style="width: 288px; left: 160px; top: 356px; position: absolute; color: black; font-size: 32px; font-family: Inter; font-weight: 400; word-wrap: break-word">
      Username / Email
    </div>

    <form method="POST" action="login.php">
      <!-- input username -->
      <input type="text" name="username" placeholder="Masukkan username" required style="width: 693px; height: 63px; left: 167px; top: 404px; position: absolute; background: white; border-radius: 12px;
           font-size: 20px; padding-left: 16px; border: 1px solid #ccc; box-sizing: border-box;">

      <!-- input password -->
      <input type="password" name="password" placeholder="Masukkan password" required style="width: 693px; height: 63px; left: 167px; top: 534px; position: absolute; background: white; border-radius: 12px;
           font-size: 20px; padding-left: 16px; border: 1px solid #ccc; box-sizing: border-box;">

      <!-- submit button -->
      <button type="submit" name="login" style="width: 287px; height: 63px; left: 370px; top: 664px; position: absolute; background: white; border-radius: 12px;
           font-size: 24px; font-weight: bold; color: black; border: 1px solid #ccc; cursor: pointer;">
        Login
      </button>
    </form>

    <div data-layer="Password" class="Password"
      style="width: 202px; left: 160px; top: 486px; position: absolute; color: black; font-size: 32px; font-family: Inter; font-weight: 400; word-wrap: break-word">
      Password</div>

    <!-- Grup Gugel -->
    <div style="position: absolute; left: 267.73px; top: 838px; width: 209.19px; height: 81px;">
      <div data-layer="Rectangle 53" class="Rectangle53"
        style="width: 100%; height: 100%; background: white; border-radius: 12px; position: absolute;">
      </div>
      <img data-layer="GugelImg" class="GugelImg"
        style="width: 64.02px; height: 64.02px; left: 17.49px; top: 7.49px; position: absolute; border-radius: 112.50px"
        src="Assets/gugel.png">
      <div data-layer="Gugel" class="Gugel"
        style="left: 90px; top: 26px; position: absolute; color: black; font-size: 25px; font-family: Inter; font-weight: 700;">
        Gugel
      </div>
    </div>

    <!-- Grup Yuhuu -->
    <div style="position: absolute; left: 550.09px; top: 838px; width: 209.19px; height: 81px;">
      <div data-layer="Rectangle 55" class="Rectangle55"
        style="width: 100%; height: 100%; background: white; border-radius: 12px; position: absolute;">
      </div>
      <img data-layer="YuhuImg" class="YuhuImg"
        style="width: 116.15px; height: 66.71px; left: 5.27px; top: 7.59px; position: absolute; border-radius: 33.35px"
        src="Assets/yuhuu.png">
      <div data-layer="Yuhuu" class="Yuhuu"
        style="left: 130px; top: 26px; position: absolute; color: black; font-size: 25px; font-family: Inter; font-weight: 700;">
        Yuhuu
      </div>
    </div>


    <div data-layer="Line 8" class="Line8"
      style="width: 335px; height: 0px; left: 160px; top: 771px; position: absolute; outline: 1px black solid; outline-offset: -0.50px">
    </div>
    <div data-layer="Line 9" class="Line9"
      style="width: 333px; height: 0px; left: 527px; top: 771px; position: absolute; outline: 1px black solid; outline-offset: -0.50px">
    </div>
    <div data-layer="or" class="Or"
      style="width: 30px; left: 503px; top: 756px; position: absolute; color: black; font-size: 20px; font-family: Inter; font-weight: 700; word-wrap: break-word">
      or</div>
    <img data-layer="Youtube Mascott " class="YoutubeMascott"
      style="width: 510.92px; height: 369px; left: 944px; top: 312px; position: absolute"
      src="Assets/Youtube-Mascott.png">

</body>

</html>