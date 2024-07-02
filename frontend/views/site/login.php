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
            <button type="button" id="btnPhone">Đăng nhập</button>
        </div>
        <span class="text-center">- Hoặc tiếp tục với -</span>
        <div class="social_login">
            <button class="login_fb flex-center"><img src="/images/icon/facebook.svg" alt=""> Facebook</button>
            <button class="login_gg flex-center"><img src="/images/icon/google.svg" alt=""> Google</button>
        </div>
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
                alert("OTP code has been sent")
                isLoadingSendOTP = false;
                $(".img_loading").hide();
            }
        }
    );
    
    btnPhone.addEventListener('click', e => {
        console.log(111);
        if( !flagShowOtp ){
            console.log(222);
            var phone_number = $.trim($('#phone_number').val());
            if( phone_number == '' ){
                $('#phone_number').focus();
            }else{
                if( isLoadingSendOTP ){
                    alert('OTP code is being sent. Pls wait');
                    return false;
                }
                $(".img_loading").show();
                isLoadingSendOTP = true;
                // window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container');
                firebase.auth().signInWithPhoneNumber(phone_number, window.recaptchaVerifier) 
                .then(function(confirmationResult) {
                console.log(333);
                    flagShowOtp = true;
                    $('#phone_number').hide();
                    $('#otp').show();
                    window.confirmationResult = confirmationResult;
                });
            }
        }else{
            var otp = $.trim($('#otp').val());
            if( otp == '' || otp.length != 6 ){
                $('#otp').focus();
            }else{
                if( isLoadingVerifyOTP ){
                    alert('OTP has being verify. Pls Wait');
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
                                if( res.status ){
                                    alert('Login Success');//Dòng này dùng tạm => Chạy product thì xoá đi
                                    // window.location.reload();
                                }else{
                                    alert(res.message);
                                }
                            },
                            error: function(err){
                                alert("Có lỗi xảy ra vui lòng thử lại sau");
                            }
                        });
                    }).catch(function(error) {
                        alert("Có lỗi xảy ra vui lòng thử lại sau");
                    });
                }).catch((error) => {
                    console.log('err verify otp:',error)
                    isLoadingVerifyOTP = false;
                    $(".img_loading").hide();
                    alert(error.message);
                });
            }
            
        }
    }, false)

    $(".type_login").click(function(){
        $('.content_form').hide();
        $('.content_' + $(this).val()).show();

        flagShowOtp = false;
        isLoadingSendOTP = false;
        isLoadingVerifyOTP = false;
        $('#phone_number').show();
        $('#otp').hide();
    });
}())
</script>