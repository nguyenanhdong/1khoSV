function addCommas(str) {
   return str.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function formatRepo (repo) {
   if (repo.loading) {
     return repo.text;
   }
 
   var $container = $(
     "<div class='select2-result-repository clearfix'>" +
       "<div class='select2-result-repository__meta'>" +
         "<div class='select2-result-repository__title'></div>" +
       "</div>" +
     "</div>"
   );
 
   $container.find(".select2-result-repository__title").text(repo.name);
   return $container;
}
 
function formatRepoSelection (repo) {
   return repo.name || repo.text;
}
var callBackModal = function(response, status, jqXHR){
   if( status == "error" ){
       var msg = "";
       if (jqXHR.status === 0) {
           msg = "Không có kết nối mạng [0]";
       }
       else if (jqXHR.status == 403) {
           msg = "Bạn không đủ quyền để thực hiện hành động này. [403]";
       } else if (jqXHR.status == 404) {
           msg = "Trang yêu cầu không tìm thấy. [404]";
       } else if (jqXHR.status == 500) {
           msg = "Lỗi máy chủ nội bộ [500].";
       } else if (exception === "parsererror") {
           msg = "Phân tích JSON không thành công.";
       } else if (exception === "timeout") {
           msg = "Time out error.";
       } else if (exception === "abort") {
           msg = "";
       } else {
           msg = `Uncaught Error.\n${jqXHR.responseText}`;
       }
       if(msg != "")
           toastr["error"](msg);
   }else{
   }
};
jQuery(document).ready(function(){
   $('.select2').select2();
   toastr.options = {
       closeButton:true,
       progressBar:true,
       showDuration:300,
       hideDuration:2500,//3 giây ẩn
       showEasing: "swing",
       hideEasing: "linear",
       showMethod: "fadeIn",
       hideMethod: "fadeOut"
   };
   if( $('.input-price').length > 0 ){
       $('.input-price').each(function(){
            $(this).val(addCommas($(this).val()));
       });
   }
   $(document).on('keyup','.input-price',function(){
        if( $.trim($(this).val()) != "" )
            $(this).val(addCommas($(this).val()));
    });
   var ajaxUpload;
   $(document).on('click','.cancel-upload',function(){
       ajaxUpload.abort();
       $(".meter").slideUp();
   });
   $(document).on('change','.file-upload-ajax', function() {
       var _this   = $(this);
       var _parent = _this.parent().parent();
       if(this.files[0]){
           var formData = new FormData();
           formData.append("file",this.files[0]);
           formData.append("folder",_this.attr('data-folder'));
           ajaxUpload = $.ajax({
               url: "/common/upload-file",
               type: "POST",
               data : formData,
               // dataType: 'jsonp',
               crossOrigin:true,
               processData: false,
               contentType: false,
               xhr: function() {
                   var xhr = new window.XMLHttpRequest();
                   xhr.upload.addEventListener("progress", function(evt) {
                       if (evt.lengthComputable) {
                           var percentComplete = ((evt.loaded / evt.total) * 100);
                           _parent.find(".meter > span").width(percentComplete + '%');
                           var percentCompleteShow = percentComplete.toFixed(1);
                           percentCompleteShow     = percentCompleteShow.toString().replace('.0','');
                           _parent.find(".meter > i").html(percentCompleteShow+'%');
                           if( percentComplete == 100 ){
                               _parent.find(".meter").slideUp();
                           }
                       }
                   }, false);
                   return xhr;
               },
               beforeSend: function() {
                   if( _parent.find('.meter').length <= 0 ){
                       _parent.append('<div class="meter" style="display:none"><span style="width:0"></span><i></i><p class="cancel-upload"><b class="fal fa-times-circle"></b> Huỷ</p></div>');
                   }
                   _parent.find(".meter").slideDown();
                   _parent.find(".meter > span").width('0%');
                   _parent.find(".meter > i").html('');
               },
               success: function(data){
                   let result = JSON.parse(data);
                   if( result.status ){
                       _parent.find(".meter").slideUp();
                       _parent.find('.cancel-upload').show();
                       toastr["success"](result.message);
                       _parent.find('.input-hidden-value').val(result.url);
                       _parent.find('.img-preview').attr('src',result.url).show();
                   }else{
                       toastr["error"](result.message);
                   }
               },
               error: function(xhr, ajaxOptions, thrownError) {
                   if(xhr.statusText != 'abort')
                       toastr["error"](thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
               }
           });
       }
   });
   
   $(document).on('change','.sl-set-name',function(){
       var _parent = $(this).parent().parent();
       if( $(this).val() != '' )
           _parent.find('input[type="hidden"]').val($(this).find('option:selected').text());
       else
           _parent.find('input[type="hidden"]').val('');
   });
   
   $(document).on('click','.icon-calendar-form',function(){
       $(this).parent().find('.input-date').focus();
   });
   $('.input-date').each(function(index,_this){
       $(_this).daterangepicker({
           singleDatePicker: true,
           showDropdowns: true,
           minYear: parseInt(moment().format('YYYY'),10),
           maxYear: parseInt(moment().add(3, 'Y').format('YYYY')),
           format: $(_this).attr('data-format'),
           locale: {
               format: $(_this).attr('data-format'),
               daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
               monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
           }
       }, function(start, end, label) {
           
       });
   });
   
   $(document).on('keyup','.count-char',function(){
       var maxChar     = parseInt($(this).attr('maxlength'));
       var valInput    = $(this).val();
       var lengthInput = valInput.length;
       var _parent     = $(this).parent();
       if(lengthInput > maxChar ){
           valInput    = valInput.substring(0, maxChar);
           $(this).val(val);
           _parent.find('.sp-number-char').addClass("red").html(maxChar + "/" + maxChar);
       }
       else{
           _parent.find('.sp-number-char').removeClass("red").html(lengthInput+"/" + maxChar);
           _parent.find('.sp-number-char')
       }
   });
});