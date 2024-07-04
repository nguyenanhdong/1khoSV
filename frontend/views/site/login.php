<div id="login">
    <div class="login_group">
        <h1 class="text-center">Đăng Nhập</h1>
        <div class="form-group">
            <label for="">Số điện thoại của bạn</label>
            <div class="group_phone">
                <img src="/images/icon/phone-input.svg" alt="">
                <input id="phone_number" type="text" placeholder="0987888999">
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
            <button class="login_fb flex-center"><img src="/images/icon/facebook.svg" alt=""> Facebook</button>
            <button class="login_gg flex-center"><img src="/images/icon/google.svg" alt=""> Google</button>
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
    console.log('Start file login with firebase');
    // Initialize Firebase
    var config = JSON.parse('<?= json_encode(Yii::$app->params['fireBase']['login']) ?>');
    firebase.initializeApp(config);
    firebase.auth().languageCode = 'en';//Chú ý dòng này -> Lấy ngôn ngữ hiện tại đang active
    //Google singin provider
    var ggProvider = new firebase.auth.GoogleAuthProvider();

    //Login in variables
    const btnGoogle = document.getElementById('btnGoogle');
    const btnPhone = document.getElementById('btnPhone');
    const verify_otp = document.getElementById('verify_otp');

    //Sing in with Google
    // btnGoogle.addEventListener('click', e => {
    //     firebase.auth().signInWithPopup(ggProvider).then(function(result) {
    //         //Dùng 1 trong 2 cách bên dưới
    //         //Cách 1
    //         firebase.auth().currentUser.getIdToken(true).then(function(idToken) {
    //             $.ajax({
    //                 type: 'POST',
    //                 url: window.location.href,
    //                 data: {type: 'idToken', token: idToken},
    //                 success: function(res){
    //                     console.log('res idToken:',res);
    //                     if( res.status ){
    //                         alert('Login Success');//Dòng này dùng tạm => Chạy product thì xoá đi
    //                         // window.location.reload();
    //                     }else{
    //                         alert(res.message);
    //                     }
    //                 },
    //                 error: function(err){
    //                     alert("Có lỗi xảy ra vui lòng thử lại sau");
    //                 }
    //             });
    //         }).catch(function(error) {
    //             alert("Có lỗi xảy ra vui lòng thử lại sau");
    //         });

    //         //Cách 2
    //         // var accessToken = result.credential.accessToken;
    //         // var user        = result.user;
    //         // $.ajax({
    //         //     type: 'POST',
    //         //     url: window.location.href,
    //         //     data: {type: 'accessToken', token: accessToken},
    //         //     success: function(res){
    //         //         console.log('res accessToken:',res);
    //         //         if( res.status ){
    //         //                 alert('Login Success');//Dòng này dùng tạm => Chạy product thì xoá đi
    //         //                 // window.location.reload();
    //         //             }else{
    //         //                 alert(res.message);
    //         //             }
    //         //     },
    //         //     error: function(err){
    //         //         alert("Có lỗi xảy ra vui lòng thử lại sau");
    //         //     }
    //         // });
    //     }).catch(function(error) {
    //         alert(error.message);
    //         console.error('Error: hande error here>>>', error)
    //     })
    // }, false)

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
        console.log(111);
        if( !flagShowOtp ){
            console.log(222);
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
                firebase.auth().signInWithPhoneNumber(phone_number, window.recaptchaVerifier) 
                .then(function(confirmationResult) {
                console.log(333);
                    flagShowOtp = true;
                    $('.verify_otp').show(500);
                    $('.login_group').remove();
                    window.confirmationResult = confirmationResult;
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
                console.log('user:',user);
                firebase.auth().currentUser.getIdToken(true).then(function(idToken) {
                    $.ajax({
                        type: 'POST',
                        url: window.location.href,
                        data: {type: 'idToken', token: idToken},
                        success: function(res){
                            console.log('res idToken:',res);
                            // $('#verify_otp').find('.spinner-border').remove();
                            if( res.status ){
                                toastr['success']('Đăng nhập thành công');
                                setTimeout(function(){
                                    window.location.href = '/';
                                },300);
                            }else{
                                alert(res.message);
                            }
                        },
                        error: function(err){
                            toastr['error']('Có lỗi xảy ra vui lòng thử lại sau');
                        }
                    });
                }).catch(function(error) {
                    toastr['error']('Có lỗi xảy ra vui lòng thử lại sau');
                });
            }).catch((error) => {
                console.log('err verify otp:',error)
                isLoadingVerifyOTP = false;
                $(".img_loading").hide();
                alert(error.message);
            });
        }
    }, false);
}())
</script>
