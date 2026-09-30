<div id="login">
    <div class="login_group">
        <h1 class="text-center">Đăng Nhập</h1>
        <div class="form-group">
            <label for="">Số điện thoại của bạn</label>
            <div class="group_phone">
                <img src="/images/icon/phone-input.svg" alt="">
                <input id="phone_number" type="tel" inputmode="tel" autocomplete="tel" placeholder="0987888999">
                <input style="display: none;" id="otp" type="text" placeholder="123456">
            </div>
            <span>*Chúng tôi sẽ gửi cho bạn một OTP để hoàn tất <br> đăng ký của bạn</span>
        </div>
        <div class="submit_login">
            <div id="recaptcha-container"></div>
            <button type="button" id="btnPhone" class="btn_login">Đăng nhập</button>
        </div>
        <span class="text-center">- Hoặc tiếp tục với -</span>
        <div class="social_login">
            <button type="button" class="login_fb flex-center"><img src="/images/icon/facebook.svg" alt=""> Facebook</button>
            <button type="button" class="login_gg flex-center"><img src="/images/icon/google.svg" alt=""> Google</button>
        </div>
    </div>
    <div class="verify_otp">
        <h2>Mã xác nhận</h2>
        <p>Vui lòng nhập mã 6 chữ số được gửi đến <strong>số điện thoại</strong> của bạn</p>
        <div class="otp-container flex-center">
            <!-- Six input fields for OTP digits -->
            <input type="text" class="otp-input" pattern="\d" maxlength="1">
            <input type="text" class="otp-input" pattern="\d" maxlength="1" disabled>
            <input type="text" class="otp-input" pattern="\d" maxlength="1" disabled>
            <input type="text" class="otp-input" pattern="\d" maxlength="1" disabled>
            <input type="text" class="otp-input" pattern="\d" maxlength="1" disabled>
            <input type="text" class="otp-input" pattern="\d" maxlength="1" disabled>
        </div>
        <button type="button" id="verify_otp">Đăng nhập</button>
        <!-- Field to display entered OTP -->
        <input type="hidden" id="otp" placeholder="Enter verification code" readonly>
    </div>
</div>


<script src="https://www.gstatic.com/firebasejs/5.2.0/firebase.js"></script>
<script type="text/javascript">
    (function() {
    // Initialize Firebase
    var config = JSON.parse('<?= json_encode(Yii::$app->params['fireBase']['login']) ?>');
    firebase.initializeApp(config);
    firebase.auth().languageCode = 'en';//Chú ý dòng này -> Lấy ngôn ngữ hiện tại đang active
    function sendIdToken(idToken) {
        return $.ajax({ type: 'POST', url: '/site/login', data: {type: 'idToken', token: idToken} }).done(function (res) {
            if (res && res.status) {
                toastr['success']('Đăng nhập thành công');
                setTimeout(function () { window.location.href = res.redirect || '/'; }, 500);
            } else {
                toastr['error']((res && res.message) || 'Đăng nhập thất bại');
            }
        }).fail(function () { toastr['error']('Có lỗi xảy ra vui lòng thử lại sau'); });
    }

    // Đăng nhập Google / Facebook qua Firebase (provider cần được bật trong Firebase Console → Authentication)
    function socialLogin(provider, $btn) {
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);
        firebase.auth().signInWithPopup(provider)
            .then(function (result) { return result.user.getIdToken(true); })
            .then(sendIdToken)
            .catch(function (error) {
                var messages = {
                    'auth/popup-closed-by-user': 'Bạn đã đóng cửa sổ đăng nhập',
                    'auth/cancelled-popup-request': 'Bạn đã đóng cửa sổ đăng nhập',
                    'auth/popup-blocked': 'Trình duyệt đang chặn cửa sổ đăng nhập, vui lòng cho phép popup',
                    'auth/operation-not-allowed': 'Hình thức đăng nhập này chưa được bật, vui lòng dùng số điện thoại',
                    'auth/account-exists-with-different-credential': 'Email này đã đăng nhập bằng phương thức khác'
                };
                toastr['error'](messages[error.code] || 'Đăng nhập thất bại, vui lòng thử lại');
            })
            .then(function () { $btn.prop('disabled', false); });
    }
    $('.login_gg').on('click', function () { socialLogin(new firebase.auth.GoogleAuthProvider(), $(this)); });
    $('.login_fb').on('click', function () { socialLogin(new firebase.auth.FacebookAuthProvider(), $(this)); });

    const btnPhone = document.getElementById('btnPhone');
    const verify_otp = document.getElementById('verify_otp');

    var flagShowOtp = false;
    var isLoadingSendOTP = false;
    var isLoadingVerifyOTP = false;
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier(
        "recaptcha-container",
        {
        size: "invisible",
        callback: function(response) {
                console.log('response:',response, window.confirmationResult);
                // alert("OTP code has been sent")
                isLoadingSendOTP = false;
                $(".img_loading").hide();
            }
        }
    );
    
    btnPhone.addEventListener('click', e => {
        $('#btnPhone').append('<i class="spinner-border text-light"></i>');
        if( !flagShowOtp ){
            var phone_number = $.trim($('#phone_number').val());
            if( phone_number == '' ){
                $('#phone_number').focus();
            }else{
                // if( isLoadingSendOTP ){
                //     alert('OTP code is being sent. Pls wait');
                //     return false;
                // }
                $(".img_loading").show();
                isLoadingSendOTP = true;
                // window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container');
                var e164 = phone_number.replace(/[^0-9+]/g, '');
                if (/^0\d{9}$/.test(e164)) e164 = '+84' + e164.substring(1);
                if (!/^\+\d{10,13}$/.test(e164)) {
                    $('#btnPhone').find('.spinner-border').remove();
                    toastr['warning']('Số điện thoại không hợp lệ');
                    return;
                }
                firebase.auth().signInWithPhoneNumber(e164, window.recaptchaVerifier)
                .then(function(confirmationResult) {
                    flagShowOtp = true;
                    $('.verify_otp').show(500);
                    $('.login_group').remove();
                    window.confirmationResult = confirmationResult;
                })
                .catch(function (error) {
                    $('#btnPhone').find('.spinner-border').remove();
                    toastr['error'](error.code === 'auth/too-many-requests' ? 'Bạn thử quá nhiều lần, vui lòng thử lại sau' : 'Không gửi được mã OTP, vui lòng kiểm tra số điện thoại');
                });
            }
        }
    }, false)
    verify_otp.addEventListener('click', e => {
        // $('#verify_otp').append('<i class="spinner-border text-light"></i>');
        var otp = $.trim($('#otp').val());
        if( otp == '' || otp.length != 6 ){
            toastr['warning']('Vui lòng nhập mã xác nhận!');
        }else{
            if( isLoadingVerifyOTP ){
                toastr['success']('OTP đang được xác minh. Xin vui lòng đợi');
                return false;
            }
            isLoadingVerifyOTP = true;
            $(".img_loading").show();
            window.confirmationResult.confirm(otp).then((result) => {
                // User signed in successfully.
                $(".img_loading").hide();
                isLoadingVerifyOTP = false;
                const user = result.user;
                firebase.auth().currentUser.getIdToken(true).then(sendIdToken).catch(function(error) {
                    toastr['error']('Có lỗi xảy ra vui lòng thử lại sau');
                });
            }).catch((error) => {
                isLoadingVerifyOTP = false;
                $(".img_loading").hide();
                toastr['error'](error.code === 'auth/invalid-verification-code' ? 'Mã xác nhận không đúng' : 'Mã xác nhận đã hết hạn, vui lòng gửi lại');
            });
        }
    }, false);
}())
</script>
