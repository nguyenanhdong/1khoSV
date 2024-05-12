// 1kho JS
  $('.banner_index').slick({
    autoplay: false,
    speed: 800,
    slidesToShow: 1,
    slidesToScroll: 1,
    centerMode: false,
    focusOnSelect: false,
    pauseOnHover:false,
    dots:true,
    infinite: false,
  });
  $('.slide_sale').slick({
    autoplay: false,
    speed: 800,
    slidesToShow: 4,
    slidesToScroll: 1,
    centerMode: false,
    focusOnSelect: false,
    pauseOnHover:false,
    dots:false,
    infinite: false,
		responsive: [
			{
        breakpoint: 1024,
        settings: {
          autoplay: false,
          speed: 800,
          slidesToShow: 2,
          slidesToScroll: 1,
          centerMode: false,
          focusOnSelect: false,
          infinite: false,
          centerPadding: '20%',
          variableWidth: true,
          pauseOnHover:false,
        }
			}
		]
  });
  $('.product_slide').slick({
    autoplay: false,
    speed: 800,
    slidesToShow: 5,
    slidesToScroll: 1,
    centerMode: false,
    focusOnSelect: false,
    pauseOnHover:false,
    dots:false,
    infinite: false,
		responsive: [
			{
        breakpoint: 1024,
        settings: {
          autoplay: false,
          speed: 800,
          slidesToShow: 2,
          slidesToScroll: 1,
          centerMode: false,
          focusOnSelect: false,
          infinite: false,
          centerPadding: '20%',
          variableWidth: true,
          pauseOnHover:false,
        }
			}
		]
  });

  $(document).on('click', '#btn_toggle_menu', function(){
    $('.header_top_mobi').toggleClass('open');
  });
  $(document).on('click', '.close_sidebar', function(){
    $('.header_top_mobi').removeClass('open');
  });
//js image product
const imgs = document.querySelectorAll('.img-select a');
const imgBtns = [...imgs];
let imgId = 1;

imgBtns.forEach((imgItem) => {
    imgItem.addEventListener('click', (event) => {
        event.preventDefault();
        imgId = imgItem.dataset.id;
        slideImage();
    });
});

function slideImage(){
    const displayWidth = document.querySelector('.img-showcase img:first-child').clientWidth;

    document.querySelector('.img-showcase').style.transform = `translateX(${- (imgId - 1) * displayWidth}px)`;
}

window.addEventListener('resize', slideImage);
//end js image product

$(document).on('click','.choose_btn_code',function(){
  $('.choose_btn_code').removeClass('active');
  $(this).addClass('active');
});
$(document).on('click','.choose_btn_color',function(){
  $('.choose_btn_color').removeClass('active');
  $(this).addClass('active');
});
if ($(window).width() <= 768) {
  $('.product_top_cat').slick({
    autoplay: false,
    speed: 800,
    slidesToShow: 1,
    slidesToScroll: 1,
    centerMode: false,
    focusOnSelect: false,
    pauseOnHover:false,
    dots:true,
    infinite: false,
  });
}


































var setSlideCourse = function () {
  if ($('.list-course-by-group').length > 0) {
    if ($(window).width() <= 600) {
      if ($('.list-course-by-group.slick-initialized').length <= 0)
        $('.list-course-by-group').slick({
          arrows: false,
          dots: true,
          infinite: true,
          speed: 300,
          slidesToShow: 1,
          centerMode: true,
          variableWidth: true,
          autoplay: false,
          autoplaySpeed: 3000,
        });
    } else {
      if ($('.list-course-by-group.slick-initialized').length > 0)
        $('.list-course-by-group').slick('unslick');
    }
  }
}
var rating = 0;
$.fn.stars = function () {
  return $(this).each(function () {
    rating = parseInt($(this).data("rating"));
    var fullStar = new Array(Math.floor(rating + 1)).join('<i class="fas fa-star"></i>');
    var halfStar = ((rating % 1) !== 0) ? '<i class="fas fa-star-half-alt"></i>' : '';
    var noStar = new Array(Math.floor($(this).data("numStars") + 1 - rating)).join('<i class="far fa-star"></i>');
    $(this).html(fullStar + halfStar + noStar);
    $(this).find('.fa-star').each(function (index, item) {
      $(item).attr('data-stt', index + 1);
    });
  });
}
// var getDataSearch = function(type, query){

//   $.ajax({
//     type: 'GET',
//     url: '/tim-kiem',
//     data: {type: type, q: query},
//     success: function(res){
//       console.log('data ' + res.data);
//         var html_search = '';
//         if( res.data.length <= 0 ){
//           html_search = '<li class="no-results">Không tìm thấy dữ liệu.</li>';
//         }else{
//             for(var i = 0; i < res.data.length; i++){
//               var dt = res.data[i];
//               html_search += '<li class="result_item_search"><a href="'+ dt.link +'"><img src="' + dt.avatar + '" alt=""><div><span>'+ dt.name +'</span><p>'+ dt.description +'</p></div></a></li>';
//             }
//         }
//         $('.search-results ul').html(html_search);
//     }
//   })
// }

// function onScroll(event){
//   var scrollPos = $(document).scrollTop();
//   $('.list_title a').each(function () {
//       var currLink = $(this);
//       var refElement = $(currLink.attr("href"));
//       if (refElement.position().top < scrollPos && refElement.position().top + refElement.height() > scrollPos) {
//           $('list_title a.active').removeClass("active");
//           currLink.addClass("active");
//       }
//       else{
//           currLink.removeClass("active");
//       }
//   });
// }

$(window).scroll(function () {
  var scrollDistance = $(window).scrollTop();
  // Assign active class to nav links while scolling
  $('section').each(function (i) {
    if ($(this).position().top - 400 <= scrollDistance) {
      $('.list_title a.active').removeClass('active');
      $('.list_title a').eq(i).addClass('active');
    }
  });
}).scroll();












// //js scroll show hide nav
var width = $(window).width();
if (width < 768) {
  var prevScrollpos = window.pageYOffset;
  window.onscroll = function () {
    var currentScrollPos = window.pageYOffset;
    var height_banner = $('.banner_top_mobi').height();
    if (currentScrollPos > 61.56 + height_banner) {
      $('.box_fix_head').addClass('fixed_h');
    }
    if (currentScrollPos < 61.56 + height_banner) {
      $('.box_fix_head').removeClass('fixed_h');
    }
  }
} else {
  var height_head = $('.theme_home').height();
  var prevScrollpos = window.pageYOffset;
  window.onscroll = function () {
    var currentScrollPos = window.pageYOffset;
    if (currentScrollPos > height_head + 24) {
      $('.home_sticky,.not_home_sticky').addClass('fixed_h');
    }
    if (currentScrollPos < height_head + 24) {
      $('.home_sticky,.not_home_sticky').removeClass('fixed_h');
    }
  }
}
function vh(percent) {
  var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
  return (percent * h) / 100;
}
$(document).ready(function () {

  // $(window).on('scroll', function () {
  //   if ($(window).scrollTop() >= $('.map').offset().top + $('.map').outerHeight() - window.innerHeight) {
  //     $(".map_right").addClass("animate__bounceInRight animate__animated");
  //   }
  //   if ($(window).scrollTop() >= $('.content_about').offset().top + $('.content_about').outerHeight() - window.innerHeight) {
  //     $(".content_about_gr").addClass("animate__animated animate__fadeInUp");
  //   }
  // });


  $('.list_cus').slick({
    arrows: false,
    dots: true,
    infinite: true,
    speed: 300,
    slidesToShow: 1,
    // centerMode: true,
    // variableWidth: true,
    autoplay: false,
    autoplaySpeed: 3000,
  });
  $('.banner_home_gr').slick({
    draggable: true,
    autoplay: true,
    autoplaySpeed: 7000,
    arrows: false,
    dots: true,
    fade: true,
    speed: 1500,
    infinite: true,
    cssEase: 'ease-in-out',
    touchThreshold: 100
  });
  $(document).on('click', '.top_option', function () {
    $(this).parent().toggleClass('active');
  });
  $(document).on('click', '.mobile-nav__toggler', function () {
    $('.mobile-nav__default').toggleClass('expanded');
  });
  $(document).on('click', '.mobile-filter__toggler', function () {
    $('.toggle_filter').toggleClass('expanded');
  });



  let page_new = 0;
  $(document).on('click', '.see_more_td', function () {
    let cat_id = $(this).attr('cat-id');
    page_new++;
    $.ajax({
      type: 'POST',
      url: '/news/more-new',
      data: { page: page_new, category_id: cat_id },
      success: function (res) {
        var data = $.parseJSON(res);
        console.log(data['data']);
        if (data['data'] != "") {
          $('.parent_new').append(data['data']);
        }
        if (!data['check_more']) {
          $('.see_more_td').hide();
        }
      }
    });
  });

  var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  $(document).on('click', '#send_mail', function () {
    var email = $('.email_offer').val();

    if (email == '') {
      toastr['warning']('Vui lòng nhập địa chỉ email!');
      return;
    }
    if (!pattern.test(email)) {
      toastr['warning']('Email không đúng định dạng!');
      return;
    }
    $.ajax({
      type: 'POST',
      url: "/site/save-email-offer",
      data: { email: email },
      success: function (res) {
        if (res == 1) {
          toastr['success']('Đăng ký nhận ưu đãi thành công!');
          $('.email_offer').val('');
        } else {
          toastr['warning']('Email đã tồn tại!');
        }
      }
    });
  });
  $(document).on('click', '.sp_new', function () {
    $(this).parent().parent().toggleClass('active');
    let class_i = $(this).find('i').attr('class');
    if (class_i == 'fal fa-plus')
      $(this).find('i').attr('class', 'far fa-window-minimize');
    else
      $(this).find('i').attr('class', 'fal fa-plus');
  });










  $(document).on('click', '.btn_sendemail_rp', function () {
    var email = $('#email_res').val();

    if (email == '') {
      toastr['warning']('Vui lòng nhập địa chỉ email!');
      return;
    }
    if (!pattern.test(email)) {
      toastr['warning']('Email không đúng định dạng!');
      return;
    }
    $.ajax({
      type: 'POST',
      url: "/site/mail-resetpass",
      data: { email: email },
      success: function (res) {
        if (res == 1) {
          toastr['success']('Yêu cầu khôi phục mật khẩu thành công! Vui lòng kiểm tra email của bạn.');
          $('#email_res').val();
        } else if (res == 3) {
          toastr['warning']('Tài khoản của bạn không tồn tại!');
        } else {
          toastr['warning']('Có lỗi vui lòng thử lại sau!');
        }
      }
    });
  });


});






