var _boxServiceParent;
var dataPriceService = null;
var service_id = "";
var validateFormData = function(){
    var data = {
        statusValidate: false,
    }
    var arrError = [];
    var fullname = $.trim($('#order-fullname').val());
    var phone    = $.trim($('#order-phone').val());
    var email    = $.trim($('#order-email').val());
    
    var addressP        = $.trim($('#orderwork-address option:selected').text());//Địa điểm làm việc
    var addressSt       = $.trim($('#order-address').val());//Số nhà/Đường phố
    var work_day        = $('#order-workday').val();
    var working_time    = $('#order-working_time').val();
    var total_staff     = parseInt($('#orderwork-total_staff').val());
    var note            = $('#orderwork-note').val();
    var type_periodical = parseInt($('#order-type_periodical').val());
    var repeate_job     = $('#repeate_job').is(':checked') ? 1 : 0;
    var day_periodical  = [];
    $('.input_day_periodical:checked').each(function() {
        day_periodical.push($(this).val());
    });

    if( fullname == '' ){
        arrError.push('Họ tên không được trống');
    }
    if( phone == '' ){
        arrError.push('Số điện thoại không được trống');
    }else if( phone.length != 10 || phone.charAt(0) != "0"){
        arrError.push('Số điện thoại không đúng định dạng');
    }
    if( service_id  == '' ){
        arrError.push('Dịch vụ không được trống');
    }
    if( addressSt == '' ){
        arrError.push('Số nhà/Đường phố không được trống');
    }
    if( work_day == '' ){
        arrError.push('Ngày làm không được trống');
    }
    if( day_periodical.length <= 0 && service_id == 10 ){
        arrError.push('Thời gian làm việc trong tuần không được trống');
    }
    if( arrError.length <= 0 ){
        $('.form-error').addClass('hide');
        data.statusValidate = true;
        data.service_id     = parseInt(service_id);
        data.fullname       = fullname;
        data.phone          = phone;
        data.email          = email;
        data.address        = [addressP, addressSt];
        data.workday       = work_day;
        data.working_time   = working_time;
        data.total_staff    = total_staff;
        data.note           = note;
        data.from           = "CMS";
        if( service_id == 10 ){
            data.type_periodical = type_periodical;
            data.repeate_job = repeate_job;
            if( type_periodical >= 2 )
                data.day_periodical  = day_periodical;
        }
    }else{
        var html_error = '';
        arrError.map((err) => {
            html_error += '<li>' + err + '</li>';
        });
        $('.form-error').removeClass('hide').find('ul').html(html_error);
        $('#modal-add-order').animate({
            scrollTop: 0
        }, 500);
    }
    return data;
}
var getDataPriceService = function(sv_id){
    $.ajax({
        type: "POST",
        url: "/api/price-service",
        data: {"service_id" : sv_id, date: $('#order-workday').val(), from: 'CMS'},
        success: function(res){
            console.log(res);
            dataPriceService = res.data;
            renderTimeWork(dataPriceService.list_time);
        },
        error: function(){
            toastr['error']("Có lỗi! Vui lòng liên hệ quản trị viên");
        }
    })
}
var getTotalPrice = function(ca){
    var price = 0;
    dataPriceService.price_today.map((item)=>{
        if (ca == item.key){
            price = item.price;
        }
    });
    if( service_id == 10 ){//Dọn nhà định kỳ
        price = price * parseInt($('#orderwork-total_staff').val());
    }

    return addCommas(price) + ' VNĐ';
}

var renderTimeWork = function(data){
    var html = '<div class="list-ca-content">';
    var work_hour = "";
    data.map((item, index) => {
        var active = "";
        if( (index == 0 && $('#order-working_time').val() == "") || $('#order-working_time').val() == item.key ){
            work_hour = item.desc;
            active = 'active"';
            $('#order-working_time').val(item.key);
            $('.total-price').text(getTotalPrice(item.key));
        }
        html += '<div class="list-ca-item ' + active + '" data-des="' + item.desc + '" data-id="' + item.key + '"><b>' + item.name + '</b></div>';
    });
    html += '</div>';
    html += '<p><span class="work-hour">' + work_hour + '</span></p>';
    $('.box-same').find('.list-ca').html(html);
}

$('#btn-add-order').click(function(){
    $('#modal-add-order').modal('show').find('.modal-body').load($(this).attr('data-url'));
});

$(document).on('click', '.list-day-item', function(){
    if( !$(this).hasClass('active') ){
        $('.list-day-item').removeClass('active');
        $(this).addClass('active');
        $('#order-workday').val( $(this).attr('data-id') );
        getDataPriceService(service_id);
    }
});

$(document).on('click', '.list-time-item', function(){
    if( !$(this).hasClass('active') ){
        var time = parseInt($(this).attr('data-id'));
        $('.list-time-item').removeClass('active');
        $(this).addClass('active');
        $('#order-type_periodical').val( time );
        if( time >= 2 ){
            $('.list-day-repeat').removeClass('hide');
        }else{
            $('.list-day-repeat').addClass('hide');
        }
    }
});

$(document).on('click', '.list-ca-item', function(){
    if( !$(this).hasClass('active') ){
        $('.list-ca-item').removeClass('active');
        $(this).addClass('active');
        $('.work-hour').text( $(this).attr('data-des') );
        $('#order-working_time').val( $(this).attr('data-id') );
        $('.total-price').text(getTotalPrice($(this).attr('data-id')));
    }
});

$(document).on('change', '#orderwork-total_staff', function(){
    if( service_id == 10 ){
        $('.total-price').text(getTotalPrice($('#order-working_time').val()));
    }
});

$(document).on('click', '.list-day-repeat-item input[type=checkbox]', function(){
    if( $(this).is(':checked') ){
        $(this).parent().parent().addClass('active');
    }else{
        $(this).parent().parent().removeClass('active');
    }
});

$(document).on('change', '#orderwork-service_id', function(){
    $('.box-service').addClass('hide');
    service_id = $(this).val(); 
    if( service_id != "" ){
        $('.box-same').removeClass('hide');
        _boxServiceParent = $('#order_service_' + service_id);
        _boxServiceParent.removeClass('hide');
        if( service_id == 9 ){
            $('#order-workday').val( $('.list-day-item.active').attr('data-id') );
        }
        else if( service_id == 10 ){
            $('#order-workday').val( $('.btn-edit-workday').attr('data-start') );
            $('.btn-edit-workday').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minDate: new Date(),
                startDate: moment($('.btn-edit-workday').attr('data-start')).format($('.btn-edit-workday').attr('data-format')),
                minYear: parseInt(moment().format('YYYY'),10),
                maxYear: parseInt(moment().add(3, 'Y').format('YYYY')),
                format: $('.btn-edit-workday').attr('data-format'),
                locale: {
                    format: $('.btn-edit-workday').attr('data-format'),
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                }
            }, function(start, end, label) {
                $(".sp-workday").text(start.format('DD/MM/YYYY'));
                $('#order-workday').val(start.format('YYYY-MM-DD'));
                $('.btn-edit-workday').attr('data-start', start.format('YYYY-MM-DD'));
            });
        }
        getDataPriceService(service_id);
    }else{
        $('.box-same').addClass('hide');
    }
});
var flagSubmit = false;
$(document).on('click', '#btn-submit-add-order', function(){
    if( flagSubmit )
        return;
    var data = validateFormData();
    if( data.statusValidate ){
        flagSubmit = true;
        var _this = $(this);
        _this.find('loading-submit-form').removeClass('hide');
        $.ajax({
            type: "POST",
            url: "/api/order-work",
            data: data,
            success: function(res){
                _this.find('loading-submit-form').addClass('hide');
                if( res.errorCode == 0 ){
                    toastr['success'](res.errorMessage);
                    setTimeout(function(){
                        window.location.reload();
                    }, 3000)
                }else{
                    flagSubmit = false;
                    toastr['error'](res.errorMessage);
                }
            },
            error: function(){
                flagSubmit = false;
                _this.find('loading-submit-form').addClass('hide');
                toastr['error']("Có lỗi! Vui lòng liên hệ quản trị viên");
            }
        })
    }
});