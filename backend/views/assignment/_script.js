$('i.glyphicon-refresh-animate').hide();
var idUser = 0;
function updateItems(r) {
    _opts.items.available = r.available;
    _opts.items.assigned = r.assigned;
    search('available');
    search('assigned');
}
$('.hide_role').click(function(){
    $('.row_role').slideUp();
});
$(document).on('click','.label_move',function (){
    var $this   = $(this);
    var target  = $this.data('target');
    var items   = [$this.data('name')];

    if (items && items.length)
        var url = (target == 'assigned') ? '/admin/assignment/revoke?id=' + idUser : '/admin/assignment/assign?id=' + idUser; 
        $.post(url, {items: items}, function (r) {
            updateItems(r);
        }).always(function (){

        });
});



$(document).on('change','.select_type select',function (){
    var _this   = $(this);
    if( _this.val() == 1 )
        $('.select_shop select').parent().parent().parent().addClass('hide');
    else
        $('.select_shop select').parent().parent().parent().removeClass('hide');
    $('.btn_new_account').attr('href','/admin/assignment/create?type=' + _this.val());
});
$('.btn-change-status').click(function(){
    var list_input_checked = $('.checkbox_banned:checked');
    if( list_input_checked.length > 0 ){
        var listData       = [];
        for( var i = 0 ; i < list_input_checked.length ; i++ ){
            listData.push( list_input_checked[i].value );
            if($('#tr-user' + list_input_checked[i].value).hasClass('disabled')){
                $('#tr-user' + list_input_checked[i].value).removeClass('disabled').find('.status-user').html('active');
            }else{
                $('#tr-user' + list_input_checked[i].value).addClass('disabled').find('.status-user').html('inactive');
            }
        }
        list_input_checked.removeAttr('checked');
        $('.checkbox_banned').parent().removeClass('checked');
        $.post('/admin/assignment/changestatus', {data: listData, type_account : $('.select_type select').val()}, function (r) {
            
        });
    }
});

$('.btn_banned').click(function(){
    var userid = $(this).attr('dtid');
    var text_confirm = '';
    if($('#tr-user' + userid).hasClass('disabled'))
        text_confirm = 'Bạn có chắc chắn muốn mở khoá tài khoản ' + $(this).attr('dtname') + '?';
    else
        text_confirm = 'Bạn có chắc chắn muốn banned tài khoản ' + $(this).attr('dtname') + '?';

    if( confirm( text_confirm ) ){
        
        if($('#tr-user' + userid).hasClass('disabled')){
            $('#tr-user' + userid).removeClass('disabled').find('.status-user').html('active');
        }else{
            $('#tr-user' + userid).addClass('disabled').find('.status-user').html('inactive');
        }
        $.post('/admin/assignment/changestatus', {data: [userid], type_account : $('.select_type select').val()}, function (r) {
            
        });
    }
});

$('.search[data-target]').keyup(function () {
    search($(this).data('target'));
});

$('.permission').click(function(e){
    e.preventDefault();
    $('.row_role').slideDown();
    $('.sp_assign').html($(this).attr('name'));
    idUser = $(this).attr('id');
    $('html, body').animate({
        scrollTop: ($( '.row_role' ).offset().top - 80)
    }, 500);
    $('.list_role').html('<li style="padding-top:100px" class="text-center"><i class="fal fa-spinner fa-spin"></i></li>');
    $.post($(this).attr('href'), {id: idUser}, function (r) {
        updateItems(r);
    }).always(function () {
        // $this.children('i.glyphicon-refresh-animate').hide();
    });
    return false;
});

function search(target) {
    var $list = $('.list_role[data-target="' + target + '"]');
    $list.html('');
    var q = $('.search[data-target="' + target + '"]').val();

    $.each(_opts.items[target], function (name, group) {
        if (name.indexOf(q) >= 0) {
            var nameShow = name;
            if( target == 'assigned' )
                nameShow = '<i class="fal fa-chevron-left"></i> ' + nameShow;
            else
                nameShow += ' <i class="fal fa-chevron-right"></i>';

            $list.append('<li><label class="label_move" data-target="' + target + '" data-name="' + name + '">' + nameShow  + '</label> <a style="text-decoration:none;color:#0096D7" href="/admin/role/view?id=' + (name.replace(/\ /g,'+')) + '" target="_blank" class="fal fa-search"></a></li>');
        }
    });
}
