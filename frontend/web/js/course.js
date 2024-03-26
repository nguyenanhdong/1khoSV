$(document).ready(function(){
      
     $(document).on('click','.item_course',function(){
          var id_lesson = $(this).attr('less-id');
          $('.item_node').hide();
          $(".item_node").removeClass('active_node');
          $(".item_node_"+id_lesson).show().addClass('active_node');
          $('.bt_lesson').remove();
          $('.document').hide();

          $.ajax({
               type:'POST',
               url:"/course/get-description",
               data:{id_lesson:id_lesson},
               success:function(res){
                    var data = $.parseJSON(res);
                    $('#description').html(data['des']);
                    $('#documents').html(data['doc']);
                    $('#files').html(data['tailieu']);
               }
          });
     });

     $("#check_show_all").click(function() {
          if($('#check_show_all').is(':checked')) { 
               $('.item_node').show();
          } else {
               $('.item_node').hide();
               var id_lesson = $('.item_course.active_video.active').attr('less-id');
               $(".item_node_"+id_lesson).show();
          }
      });

     $(document).on('click','.active_video,.item_course',function(){
          $("html, body").animate({ scrollTop: 0 }, 500);
          if ($(this).hasClass('active')){
               
               return false;
          }
         
          $('.item_course').removeClass('active');
          $(this).addClass('active');
          var id_lesson = $(this).attr('less-id');
          var course_id = $(this).attr('course-id');
          var link_video = $(this).attr('data-url');


          
          // $.ajax({
          //      type:'POST',
          //      url:"/course/get-question-answer-lesson",
          //      data:{id_lesson:id_lesson,course_id:course_id},
          //      success:function(res){
          //           if(res != ''){
          //                var data = $.parseJSON(res);
          //                if(data != 'undefined'){
          //                     if(data['modal_bt'] != 'undefined'){
          //                          $('.video_course').append(data['modal_bt']);
          //                          $('.btn_show_exr').show();

          //                          $('.vjs-big-play-button').remove();
          //                     }
          //                     if(typeof(data['item_doc']) != "undefined" && data['item_doc'] != ''){
          //                          $('.list_document').html(data['item_doc']);
          //                          $('.document').show();
          //                     }
          //                     if(data['next'] != ''){
          //                          $('#item_lesson_'+ data['next']).attr('data-url','https://elearning.abe.edu.vn'+data['link_video']).find('.lock_lesson ').addClass('d-none');
          //                     }
          //                }
          //           }

          //      }
          // });






          if(link_video != ''){                                     
               _0xd85bx11();
               $('.video_main').attr('less-id', $(this).attr('less-id'));
               $('.video_main').attr('course-id', $(this).attr('course-id'));


               var video = document.createElement("video");
               video.setAttribute("id", "video");
               video.setAttribute("class", "video-js vjs-default-skin vjs-fluid vjs-16-9 vjs-big-play-centered ui-min ui-smooth v_5e8494e7ccb30-dimensions vjs-controls-enabled vjs-workinghover vjs-v7 vjs-hls-quality-selector vjs-has-started vjs-paused vjs-user-inactive");
               var video_source = document.createElement("source");
               video_source.setAttribute("src", link_video);
               // video_source.setAttribute("src", "https://www.youtube.com/watch?v=5HQcct5jzDk");
               video_source.setAttribute("type", "application/x-mpegURL");
               video.appendChild(video_source);
               document.getElementById("video_player").appendChild(video);
               _0xd85bx13();
          }else{
               $.ajax({
                    type:'POST',
                    url:"/course/get-question-answer-lesson-next",
                    data:{course_id:course_id},
                    success:function(res){
                         if(res != ''){
                              var data = $.parseJSON(res);
                              if(data != 'undefined'){
                                   if(data['modal_bt'] != 'undefined'){
                                        $('.video_course').append(data['modal_bt']);
                                         $('body').append('<div class="modal fade show" id="modal_exr_next" tabindex="-1" role="dialog" aria-labelledby="modal_exrTitle" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content" style="background:unset"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="far fa-times-circle fz-30 text-white"></i></button></div><div class="modal-body text-center"><img class="img_warning" src="/images/page/Warning.png" alt=""><p class="text-white mt-3 mb-3">Vui lòng làm bài tập để xem bài học tiếp theo!</p><button type="button" class="btn btn-primary btn_show_exr btn_exr_next" data-toggle="modal" data-target="#modal_exrcise" style="display: inline-block;position: unset;transform:unset;">Làm bài tập <i class="fas fa-arrow-right"></i></button></div></div></div></div>');
                                         $('#modal_exr_next').modal('show');
                                   }
                                   if(data['next'] != ''){
                                        $('#item_lesson_'+ data['next']).attr('data-url','https://elearning.abe.edu.vn'+data['link_video']).find('.lock_lesson ').addClass('d-none');
                                   }
                              }
                         }
                    }
               });
          }

          //check xem hết video show câu hỏi
          // $('#video_html5_api').bind('ended', function(e) {
          //      e.preventDefault();
          //      if(id_lesson != ''){
          //           $.ajax({
          //                type:'POST',
          //                url:"/course/get-question-answer",
          //                data:{id_lesson:id_lesson,course_id:course_id},
          //                success:function(res){
          //                     if(res != ''){
          //                          var data = $.parseJSON(res);
          //                          if(data != 'undefined'){
          //                               if(data['modal_bt'] != 'undefined'){
          //                                    $('.video_course').append(data['modal_bt']);
          //                                    $('.btn_show_exr').show();
          
          //                                    $('.vjs-big-play-button').remove();
          //                               }
          //                               if(typeof(data['item_doc']) != "undefined" && data['item_doc'] != ''){
          //                                    $('.list_document').html(data['item_doc']);
          //                                    $('.document').show();
          //                               }
          //                               if(data['next'] != ''){
          //                                    $('#item_lesson_'+ data['next']).attr('data-url','https://frontend.frontend.frontend.elearning.abe.edu.vn'+data['link_video']).find('.lock_lesson ').addClass('d-none');
          //                               }
          //                          }
          //                     }

          //                }
          //           });
          //      }
          //      $('.btn_show_exr').show();
          // });
     });
     $(document).on('click','.btn_exr_next', function(){
          $('#modal_exr_next').modal('hide');
     });
     $(".active_video").trigger("click");
     function _0xd85bx11() {
          if ($('#video_player video').length > 0)
              _0xd85bx5.dispose();
      }
     function _0xd85bx13() {
          jQuery(".videojs-hls-player-wrapper video").each(function (_0xd85bx2) {
  
              var _0xd85bx3 = jQuery(this).attr("id");
              if (jQuery(".u" + "i" + "-m" + "in").length > 0) {
                  jQuery(this).on("contextmenu", function (_0xd85bx4) {
                      _0xd85bx4.preventDefault()
                  })
              };
  
              _0xd85bx5 = videojs(_0xd85bx3, {
                  html5: {
                      hls: {
                          withCredentials: true,
                          overrideNative: true,
                      }
                  },
                  "playbackRates": [0.25, 0.5, 1, 1.5, 2],
                  "preload": "auto"
              });
              _0xd85bx5.ready(function () {
                    var curren_time = 0;
                    var lesson_id = $('.item_course.active').attr('less-id');
                    var course_id = $('.item_course.active').attr('course-id');
                    var continues  = $('.item_course.active').attr('continue');

                    this.on("loadedmetadata", function(){
                         this.currentTime(continues);
                    });
                    //check thời gian xem video
                    this.on('timeupdate', function () {
                         curren_time = this.currentTime();
                    });

                    var interval = null;
                    this.on("play", function (_0xd85bx4) {
                         interval = setInterval(function() {
                              $.ajax({
                                   type:'POST',
                                   url:"/video/save-time-video",
                                   data:{time:curren_time,lesson_id:lesson_id,course_id:course_id},
                                   success:function(res){
                                        
                                   }
                              });
                         }, 5000);
                         //   this.isPlaying = true;
                         //   appendUserInfo();
                         //   jQuery(".video-js").each(function (_0xd85bx7) {
                         //       if (_0xd85bx2 !== _0xd85bx7) {
                         //           this.player.pause()
                         //       }
                         //   })
                    });
               //    this.on("playing", function (_0xd85bx4) {
               //        appendUserInfo();
               //    });
                  this.on("pause", function (_0xd85bx4) {
                    clearInterval(interval);
                    //   clearTimeout(timeout);
                    //   $('#usr').remove();
                  });
                  //xem hết video
                  this.on("ended", function () {
                    $.ajax({
                         type:'POST',
                         url:"/video/save-time-video",
                         data:{time:curren_time,lesson_id:lesson_id,course_id:course_id},
                         success:function(res){
                              
                         }
                    });

                    if(lesson_id != ''){
                         $.ajax({
                              type:'POST',
                              url:"/course/get-question-answer-lesson",
                              data:{id_lesson:lesson_id,course_id:course_id},
                              success:function(res){
                                   if(res != ''){
                                        var data = $.parseJSON(res);
                                        if(data != 'undefined'){
                                             if(data['modal_bt'] != 'undefined'){
                                                  $('.video_course').append(data['modal_bt']);
                                                  $('.btn_show_exr').show();
               
                                                  $('.vjs-big-play-button').remove();
                                             }
                                             if(typeof(data['item_doc']) != "undefined" && data['item_doc'] != ''){
                                                  $('.list_document').html(data['item_doc']);
                                                  $('.document').show();
                                             }
                                             if(data['next'] != ''){
                                                  $('#item_lesson_'+ data['next']).attr('data-url','https://elearning.abe.edu.vn'+data['link_video']).find('.lock_lesson ').addClass('d-none');
                                             }
                                        }
                                   }
     
                              }
                         });
                    }
                    $('.btn_show_exr').show();

                    if (document.webkitExitFullscreen) { /* Safari */
                         document.webkitExitFullscreen();
                    } 
                    if (document.exitFullscreen) {
                         document.exitFullscreen();
                    } 
                    if (document.msExitFullscreen) { /* IE11 */
                         document.msExitFullscreen();
                    }
                    // setTimeout(() => {
                    //      $('.btn_show_exr').trigger('click');
                    //      alert(1);
                    // }, 3000);
                    // $('.bt_lesson').appendTo($('#video_player'));
                    // console.log('vao roii')
                  });
                  if (this.tech_.hls && this.tech_.hls.xhr) {
                      this.tech_.hls.xhr.beforeRequest = function (_0xd85bx8) {
                          if (_0xd85bx8.uri.indexOf(".ts") >= 0) {
                              jQuery.ajax({
                                  url: "/video/touch",
                                  success: function (_0xd85bx9) {
                                      _0xd85bx8.uri += "?token=" + _0xd85bx9;
                                  },
                                  async: false,
                                  cache: false
                              })
                          };
                          return _0xd85bx8
                      }
                  } else {
                      this.on("play", function (_0xd85bx4) {
                          this.isPlaying = false;
                          // this.reQuestKeyForStream()
                      });
                      _0xd85bx5.on("loadedmetadata", function () {
                          var _0xd85bxe = this;
                          // setTimeout(function () {
                          //     _0xd85bxe.waitForStreamkey()
                          // }, 2000)
                      });
                      _0xd85bx5.__proto__.waitForStreamkey = function _0xd85bxf() {
                          var _0xd85bx10 = _0xd85bx5;
                          jQuery.ajax({
                              url: "/video/point",
                              success: function (_0xd85bx11) {
                                  console.log(_0xd85bx11);
                                  if (_0xd85bx11 > 0) {
                                      _0xd85bx10.nss = _0xd85bx11;
                                      _0xd85bx10.reQuestKeyForStream()
                                  } else {
                                      setTimeout(function () {
                                          _0xd85bx10.waitForStreamkey()
                                      }, 1000)
                                  }
                              },
                              cache: false
                          })
                      };
                      _0xd85bx5.__proto__.reQuestKeyForStream = function _0xd85bx12() {
                          if (!this.isPlaying) {
                              return
                          };
                          var _0xd85bxe = this;
                          jQuery.ajax({
                              url: "/video/touch",
                              pid: this.nss,
                              success: function (_0xd85bx9) {
                                  _0xd85bx15(_0xd85bx9, parseInt(this.pid) + 999);
                                  setTimeout(function () {
                                      _0xd85bxe.reQuestKeyForStream()
                                  }, 3000)
                              },
                              cache: false
                          })
                      };
                      _0xd85bx5.on("error", function (_0xd85bx13) {
                          this.isPlaying = false
                      });
                      _0xd85bx5.on("pause", function (_0xd85bx13) {
                          this.isPlaying = false
                      })
                  }
              })
          });
      }

     $(document).on('click','.play_vd_course',function(){
          $(this).hide();
          $(this).parent().find('video').get(0).play();
     });

  
     $(document).on('click','#exercise_end',function(){
          var course_id = $(this).attr('course-id');
          // var get_qa = $(this).attr('get-qa');
          if(course_id != ''){          
               $.ajax({
                    type:'POST',
                    url:"/course/get-question-answer",
                    data:{course_id:course_id},
                    success:function(res){
                         //đã hoàn thành khóa học
                         if(res == 1){
                              var img = $('#exercise_end').attr('exr');
                              $('.chungchi').attr('src','http://elearning.abe.edu.vn'+img)
                              $('#modal_chungchi').modal('show');
                              console.log('img: ' +img);
                              return;
                         }
                         if(res != ''){
                              var data = $.parseJSON(res);
                              if(data != 'undefined'){
                                   if(data['modal_bt'] != 'undefined'){
                                        $('.video_course').append(data['modal_bt']);
                                        $('.vjs-big-play-button').remove();
                                   }
                                   if(typeof(data['item_doc']) != "undefined" && data['item_doc'] != ''){
                                        $('.list_document').html(data['item_doc']);
                                        $('.document').show();
                                   }
                                   $('.btn_show_exr').trigger('click');
                              }
                         }
                    }
               });
          }
          // else{
          //      toastr['warning']('Vui lòng hoàn thành bài tập của bài học.');
          // }
     });  

     var y = 0;
     $(document).on('click','.btn_next_qa_lesson',function(){
          var less_id = $(this).attr('id-less');
          if ($('input[name="'+ less_id +'"]:checked').length == 0) {
               toastr['warning']('Bạn chưa chọn đáp án');
               return;
          }else{
               y++;
          }
          
          var data = $('#form_qa').serializeArray();
          var total_page = $('.sp_total_page').text();
          console.log('total_page: ' + total_page);
          console.log('y: ' + y);
          console.log('less_id: ' + less_id);
          console.log('data: ' + data);

          if(total_page == y){
               $('.submit_exr').hide();
               //gửi ajax check câu trả lời
               $.ajax({
                    type:'POST',
                    url:"/course/check-result-qa-lesson",
                    data:{data:data,total_question:total_page},
                    success:function(res){
                         y = 0;
                         var data = $.parseJSON(res);
                         if(data['show_exer'] == true){
                              $('#exercise_end').attr('course-id',data['cours_id']).attr('get-qa',0).find('.lock_lesson').hide();
                         }

                         $('.quiz_' + data['lesson_id']).text(data['quiz']);
                         if(data['status'] == 1){//hoan thanh khoa hoc
                              $('#modal_exrcise').addClass('success_kh');
                              $('.exit_exr').find('i').removeClass('text-white');
                         }

                         $('.body_exr').append(data['str_res']);
                         if(typeof data['lesson_next'] != 'undefined')
                              $('#item_lesson_'+ data['lesson_next']['id']).attr('data-url','https://elearning.abe.edu.vn'+data['lesson_next']['link_video']).find('.lock_lesson ').addClass('d-none');
                         $('.curent_page').text(0);
                    }
               });
               $('.group_qa_' + y).addClass('d-none');

          }else{
               $('.group_qa_' + y).addClass('d-none');
               $('.group_qa_' + (y + 1)).removeClass('d-none');
          }

          $('.curent_page').text(y + 1);

     });

     var i = 0;
     $(document).on('click','.btn_next_qa',function(){
          var less_id = $(this).attr('id-less');
          if ($('input[name="'+ less_id +'"]:checked').length == 0) {
               toastr['warning']('Bạn chưa chọn đáp án');
               return;
          }else{
               i++;
          }
          
          var data = $('#form_qa').serializeArray();
          var total_page = $('.sp_total_page').text();
          console.log('total_page: ' + total_page);
          console.log('i: ' + i);
          console.log('less_id: ' + less_id);

          if(total_page == i){
               $('.submit_exr').hide();
               //gửi ajax check câu trả lời
               $.ajax({
                    type:'POST',
                    url:"/course/check-result-qa",
                    data:{data:data,total_question:total_page},
                    success:function(res){
                         i = 0;
                         var data = $.parseJSON(res);
                         if(data['show_exer'] == true){
                              $('#exercise_end').attr('course-id',data['cours_id']).attr('get-qa',0).find('.lock_lesson').hide();
                         }

                         $('.quiz_' + data['lesson_id']).text(data['quiz']);
                         if(data['status'] == 1){//hoan thanh khoa hoc
                              $('#modal_exrcise').addClass('success_kh');
                              $('.exit_exr').find('i').removeClass('text-white');
                         }

                         $('.body_exr').append(data['str_res']);
                         if(typeof data['lesson_next'] != 'undefined')
                              $('#item_lesson_'+ data['lesson_next']['id']).attr('data-url','https://elearning.abe.edu.vn'+data['lesson_next']['link_video']).find('.lock_lesson ').addClass('d-none');
                         $('.curent_page').text(0);
                    }
               });
               $('.group_qa_' + i).addClass('d-none');

          }else{
               $('.group_qa_' + i).addClass('d-none');
               $('.group_qa_' + (i + 1)).removeClass('d-none');
          }

          $('.curent_page').text(i + 1);

     });
     $(document).on('click','.remake',function(){
          $('.step_kt').addClass('d-none');
          $('.group_qa_1').removeClass('d-none');
          $('.submit_exr').show();
          $('.result_exr').remove();
          $('.curent_page').text('1');
          i = 0;
          $( ".radio_answer" ).prop( "checked", false );
     });
     $(document).on('click','.remake_lesson',function(){
          $('.step_kt').addClass('d-none');
          $('.group_qa_1').removeClass('d-none');
          $('.submit_exr').show();
          $('.result_exr').remove();
          $('.curent_page').text('1');
          i = 0;
          $( ".radio_answer" ).prop( "checked", false );
          $('.bt_lesson').remove();
          $.ajax({
               type:'POST',
               url:"/course/get-question-answer-lesson",
               data:{},
               success:function(res){
                    $(".modal-backdrop").remove();
                    if(res != ''){
                         var data = $.parseJSON(res);
                         if(data != 'undefined'){
                              if(data['modal_bt'] != 'undefined'){
                                   $('.video_course').append(data['modal_bt']);
                                   $('.btn_show_exr').trigger('click');

                                   $('.vjs-big-play-button').remove();
                              }
                              if(typeof(data['item_doc']) != "undefined" && data['item_doc'] != ''){
                                   $('.list_document').html(data['item_doc']);
                                   $('.document').show();
                              }
                              if(data['next'] != ''){
                                   $('#item_lesson_'+ data['next']).attr('data-url','https://elearning.abe.edu.vn'+data['link_video']).find('.lock_lesson ').addClass('d-none');
                              }
                         }
                    }

               }
          });
     });
     $(document).on('click','.next_less',function(){
          $(".bt_lesson").remove();
          $(".modal-backdrop").remove();
          $('body').removeClass('modal-open');
          $('.document').hide();
     });
     $(document).on('click','.udpate_node',function(){
          var form_data = $('#form_node').serializeArray();
          if(form_data != ''){
               $.ajax({
                    type:'POST',
                    url:"/course/update-note",
                    data:{form_data:form_data},
                    success:function(res){
                         if(res == 1){
                              toastr['success']('Cập nhật thành công');
                         }
                    }
               });
          }
     });

     $(document).on('click','.submit_form_address',function(){
          var name = $('#name').val();
          var phone = $('#phone').val();
          var email = $('#email').val();
          var province = $('#province').val();
          var district = $('#district').val();
          var address = $('#address').val();
          var course_id = $(this).attr('course-id');

          var check = false;
          if(name == ''){
               $('#name').addClass('error');
               check = true;
          }
          if(phone == ''){
               $('#phone').addClass('error');
               check = true;
          }
          if(province == ''){
               $('#province').addClass('error');
               check = true;
          }
          if(district == ''){
               $('#district').addClass('error');
               check = true;
          }
          if(!check){
               $.ajax({
                    type:'POST',
                    url:"/course/save-address-user",
                    data:{name:name,phone:phone,province:province,district:district,address:address,course_id:course_id,email:email},
                    success:function(res){
                         if(res == 1){
                              toastr['success']('Cập nhật thông tin thành công');
                              $('#modal_exrcise').modal('hide');
                              $('#modal_address').modal('hide');
                              $('.btn_show_exr').hide();
                         }
                    }
               });
          }
     });
     $(document).on('change','#province',function(){
          var province = $(this).val();
          if(province != ''){
               $.ajax({
                    type:'POST',
                    url:"/course/get-district",
                    data:{province:province},
                    success:function(res){
                         if(res != ''){
                              $('#district').html(res);
                         }
                    }
               });
          }
     });
     $(document).on('click','.scroll_video',function(){
          $('html, body').animate({
               scrollTop: $(".list_title").offset().top
           }, 800);
           $('#trailer_course').trigger('play');
     });


     var timeout_export = 1000;
     var timeInterval;
     var flagClickPDF = true;
     $(document).on('click','.down_cer',function(){
          if( !flagClickPDF )
               return false;
          flagClickPDF = false;
          totalProcessSuccess  = 0;
          var listItem = $('.item_pdf');
          if( listItem.length > 0 ){
               var zip = new JSZip();
               var t0 = performance.now();
               for( var i = 0; i < listItem.length; i++ ){
                    CreatePDFfromHTML($(listItem[i]),zip);
                    console.log('flag:',i,zip);
               }
               var t1 = performance.now();
               console.log("Call to doSomething took " + (t1 - t0) + " milliseconds.");
               timeInterval = setInterval(function(){
                    if( totalProcessSuccess == listItem.length ){
                    flagClickPDF = true;
                    clearInterval(timeInterval);
                    zip.generateAsync({type:'blob'}).then(function(content) {
                    // console.log('content zip',content,arrDateInput);
                    // var fileZipName = "";
                    // if(!isExportWeek){
                    //      fileZipName = arrDateInput[0].toString().replace(/\//g,'_');
                    //      if( arrDateInput.length == 2 )
                    //      fileZipName += '-' + arrDateInput[1].toString().replace(/\//g,'_');
                    // }else
                    var fileZipName = 'chung_chi';
                    saveAs(content, fileZipName + '.zip');
                    });
                    }else{
                    console.log('run again interval:',totalProcessSuccess);
                    }
               },timeout_export);
          }else{
               alert('Không có dữ liệu để xuất file pdf');
          }
     });
});


var totalProcessSuccess = 0;
window.jsPDF = window.jspdf.jsPDF;
function CreatePDFfromHTML(_element,zip) {
  // return new Promise((resolve, reject) => {
    var HTML_Width = _element.outerWidth();
    var HTML_Height = _element.outerHeight();
    var top_left_margin = 0;
    var PDF_Width = HTML_Width;// + (top_left_margin * 2);
    var PDF_Height = HTML_Height;//(PDF_Width * 1.5) + (top_left_margin * 2);
    var canvas_image_width = HTML_Width;
    var canvas_image_height = HTML_Height;
    
    var totalPDFPages = 1;
    html2canvas(_element[0],{quality: 4, scale: 5,allowTaint: true}).then(function (canvas) {
        var imgData = canvas.toDataURL("image/jpeg", 1.0);
        var orientation = HTML_Width >= HTML_Height ? 'l' : 'p';
        var pdf = new jsPDF(orientation, 'px', [PDF_Width, PDF_Height]);
        pdf.addImage(imgData, 'JPEG', 0, 0, canvas_image_width, canvas_image_height);
        // for (var i = 1; i <= totalPDFPages; i++) { 
        //     // pdf.addPage(PDF_Width, PDF_Height);
        //     pdf.addImage(imgData, 'JPG', top_left_margin, -(PDF_Height*i)+(top_left_margin*4),width,height);
        // }
        
        totalProcessSuccess++;
        zip.file( _element.attr('id') +  ".pdf", pdf.output('blob'),{binary:true});
        // pdf.save( _element.attr('id') +  ".pdf");
        // resolve(true);
    });
    // resolve(true);
  // });
}