// 1kho JS
  $('.banner_index').slick({
    autoplay: false,
    speed: 300,
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
    speed: 300,
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
          speed: 300,
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
    speed: 300,
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
          speed: 300,
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
  let classCurrent = $(this).attr('dt-class');
  $('.'+classCurrent).removeClass('active');
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

function openTab(tabName) {
  $('.tablinks').removeClass('active');
  $('.tab_content').removeClass('active');
  $('#' + tabName).addClass('active');

  $('[data-tab="' + tabName + '"]').addClass('active');
}

$('.tablinks').click(function() {
  var tabName = $(this).data('tab');
  openTab(tabName);
});

$('.btn_voucher').click(function() {
  let dt_tab = $(this).attr('dt-tab');
  $('.voucher_list').removeClass('active');
  $('#'+dt_tab).addClass('active');
  $('.btn_voucher').removeClass('active');
  $(this).addClass('active');
});


//js notification
$(document).on('click','.toggle_noti', function(){
  $('.notification-ui_dd').toggleClass('show');
});
$(document).on('click','.notification-list', function(){
  let noti_id = $(this).attr('data-detail');
  $(`#noti_detail_${noti_id}, #noti_detail_mb_${noti_id}`).addClass('show');
  $('.notification-ui_dd-content').animate({ scrollTop: 0 }, 1);
  $('.notification-ui_dd-content, body').addClass('block_scroll');
});
$(document).on('click','.hide_noti_detail', function(){
  $(this).parent().parent().removeClass('show');
  $('.notification-ui_dd-content, body').removeClass('block_scroll');
});
$(document).click(function(event) {
  var $target = $(event.target);
  if(!$target.closest('.notification-ui_dd, .toggle_noti').length && $('.notification-ui_dd, .toggle_noti').is(":visible")) {
      $('.notification-ui_dd').removeClass('show');
  }
  if(!$target.closest('.content_tooltips, .show_detail_voucher').length && $('.content_tooltips, .show_detail_voucher').is(":visible")) {
      $('.content_tooltips').addClass('hide');
  }
});

$(document).on('click', '.show_detail_voucher', function(){
  $(this).parent().find('.content_tooltips').toggleClass('hide');
});

$(document).on('click','.category_child', function(){
  let _this = $(this);
  _this.parent().find('.active').removeClass('active');
  _this.addClass('active');
  let catId = _this.attr('cat-id');

  $.ajax({
    url: '/category/get-product-category-child',
    type: 'POST',
    data: {catId: catId},
    success: function (res) {
      if(res){
        _this.parent().parent().find('.product_slide').slick('slickRemove', null, null, true);
        _this.parent().parent().find('.product_slide').slick('slickAdd', res);
      }
    }
  });
});

var page_sale = 0;
var checkSendAjaxSale = true;
$(document).on('click','.load_more_product_sale', function(){
  page_sale ++;
  let _this = $(this);
  _this.append('<i class="spinner-border text-light"></i>')
  if(checkSendAjaxSale){
    checkSendAjaxSale = false;
    $.ajax({
      url: '/category/get-product-sale',
      type: 'POST',
      data: {page_sale: page_sale},
      success: function (res) {
        _this.find('.spinner-border').remove();
        checkSendAjaxSale = true;
        if(res['data']){
          $('.sale_list').append(res['data']);
        }
        if(!res['checkLoadMore']){
          $('.sale_see_more').remove();
          $('.noti_prod').removeClass('hide');
        }
      }
    });
  }
});

$(document).on('click','.sort_product_wap', function(){
  let sort = $(this).attr('sort');
  if(sort == 'price_desc')
    $(this).attr('sort', 'price_asc');
  else
    $(this).attr('sort', 'price_desc');
});

//find product page category
var page_product_cat = 0;
var checkSendAjaxProduct = true;
$(document).on('click','.see_more_btn', function(){
  page_product_cat ++;
  let _this = $(this);
  let cate_parent_id = _this.attr('cate-parent-id');
  let cate_child_id = _this.attr('cate-child-id');
  _this.append('<i class="spinner-border text-light"></i>')
  let sort = $('.btn_sort.active').attr('sort');
  if(checkSendAjaxProduct){
    checkSendAjaxProduct = false;
    getProductCategory(sort, page_product_cat, cate_parent_id, cate_child_id)
  }
});

$(document).on('click','.tab_cat_child', function(){
  page_product_cat = 0;
  let _this = $(this);
  $('.tab_cat_child').removeClass('active');
  _this.addClass('active');
  let cate_child_id = _this.attr('cat-id');
  $('.see_more_btn').attr('cate-child-id', cate_child_id)
  let sort = $('.btn_sort.active').attr('sort');
  if(checkSendAjaxProduct){
    checkSendAjaxProduct = false;
    getProductCategory(sort, null, null, cate_child_id)
  }
});
$(document).on('click','.btn_sort', function(){
  page_product_cat = 0;
  $('.btn_sort').removeClass('active');
  let _this = $(this);
  _this.addClass('active');
  let sort = _this.attr('sort');
  let cate_parent_id = $('.see_more_btn').attr('cate-parent-id');
  let cate_child_id = $('.see_more_btn').attr('cate-child-id');
  if(checkSendAjaxProduct){
    checkSendAjaxProduct = false;
    getProductCategory(sort, null, cate_parent_id, cate_child_id)
  }
});

function getProductCategory(sort = null, page = null, cate_parent_id = null, cate_child_id = null){
  $.ajax({
    url: '/category/get-product-category',
    type: 'POST',
    data: {sort:sort, page:page, cate_parent_id:cate_parent_id,cate_child_id:cate_child_id},
    success: function (res) {
      $('.see_more_btn').find('.spinner-border').remove();
      checkSendAjaxProduct = true;
      if(res['data']){
        if(res['append'])
          $('.product_list').append(res['data']);
        else
          $('.product_list').html(res['data']);
      }
      if(!res['checkLoadMore']){
        $('.see_more_product').remove();
      }
    }
  });
}
//end find product page category

$(document).on('click','.tab_advertis', function(){
  let tab = $(this).attr('dt-tab');
  $('.tab_advertis').removeClass('active');
  $(this).addClass('active');

  $('.tab_adver_content').addClass('hide');
  $('#'+tab).removeClass('hide');
  $('.product_slide').slick('setPosition');
});

$(document).on('click','.update_qty', function(){
  let type = $(this).attr('dt-type');
  let _inputQty = $('.quantity_product');
  let qtyCurrent = parseInt(_inputQty.val());
  
  if (type == 'decrease') {
    if (qtyCurrent > 1)
      _inputQty.val(qtyCurrent - 1);
  } else {
    _inputQty.val(qtyCurrent + 1);
  }
});
$(document).on('click','.btn_buy_now', function(){
  let _inputQty = $('.quantity_product');
  let qtyCurrent = parseInt(_inputQty.val());
  console.log(qtyCurrent);
});

$('.slider-comment-nav').slick({
  slidesToShow: 6,
  slidesToScroll: 1,
  asNavFor: '.slider-comment-for',
  dots: false,
  focusOnSelect: true
});
$('.slider-comment-for').slick({
 slidesToShow: 1,
 slidesToScroll: 1,
 arrows: false,
 fade: true,
 asNavFor: '.slider-comment-nav'
});

// Track clicks
let clickCount = 0;
let lastClickedSlide = null;
$(document).on('click', '.slide_nav', function () {
  $('.slider-comment-for').addClass('hide');
  $(this).parent().parent().parent().parent().find('.slider-comment-for').removeClass('hide');
  $('.slider-comment-for').slick('setPosition').css("visibility","visible");

  //check hide slide
  const currentSlide = $(this);
  if (lastClickedSlide && lastClickedSlide.is(currentSlide)) {
      clickCount++;
  } else {
      clickCount = 1;
      lastClickedSlide = currentSlide;
  }

  if (clickCount === 2) {
    $('.slider-comment-for').addClass('hide');
    console.log('Second click detected on the current slide');
    clickCount = 0;
  }
});

//xem them comment
var options_for = {
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  fade: true,
  asNavFor: '.slider-comment-nav'
}
var options_nav = {
  slidesToShow: 6,
  slidesToScroll: 1,
  asNavFor: '.slider-comment-for',
  dots: false,
  focusOnSelect: true
}
var page_comment = 1;
var checkSendAjaxComment = true;
$(document).on('click','.see_more_comment', function(){
  page_comment ++;
  let _this = $(this);
  let product_id = _this.attr('product-id');
  _this.append('<i class="spinner-border text-light"></i>')
  if(checkSendAjaxComment){
    checkSendAjaxComment = false;
    $.ajax({
      url: '/product/view-more-review',
      type: 'POST',
      data: {product_id:product_id, page:page_comment},
      success: function (res) {
        _this.find('.spinner-border').remove();
        checkSendAjaxComment = true;
        if(res['data']){
            $('.comment_list').append(res['data']);
            setTimeout(function () {
              $(".slider-comment-for").not('.slick-initialized').slick(options_for)
              $(".slider-comment-nav").not('.slick-initialized').slick(options_nav)
            }, 100);
        }
        if(!res['checkLoadMore']){
          $('.more_comment').remove();
        }
      }
    });
  }
});


//get product shop
var page_product_shop = 0;
var checkSendAjaxProductShop = true;
$(document).on('click','.btn_sort_shop', function(){
  page_product_cat = 0;
  $('.btn_sort_shop').removeClass('active');
  let _this = $(this);
  _this.addClass('active');
  let sort = _this.attr('sort');
  let shop_id = $('.see_more_shop').attr('shop-id');
  if(checkSendAjaxProductShop){
    checkSendAjaxProductShop = false;
    getProductShop(sort, null, shop_id)
  }
});

$(document).on('click','.see_more_shop', function(){
  page_product_shop ++;
  let _this = $(this);
  _this.append('<i class="spinner-border text-light"></i>')
  let sort = $('.btn_sort_shop.active').attr('sort');
  let shop_id = $('.see_more_shop').attr('shop-id');
  if(checkSendAjaxProductShop){
    checkSendAjaxProductShop = false;
    getProductShop(sort, page_product_shop, shop_id )
  }
});

function getProductShop(sort = null, page = null, shop_id = null){
  $.ajax({
    url: '/product/get-product-shop',
    type: 'POST',
    data: {sort:sort, page:page, shop_id:shop_id},
    success: function (res) {
      $('.see_more_shop').find('.spinner-border').remove();
      checkSendAjaxProductShop = true;
      if(res['data']){
        if(res['append'])
          $('.product_list').append(res['data']);
        else
          $('.product_list').html(res['data']);
      }
      if(!res['checkLoadMore']){
        $('.see_more_product').remove();
      }
    }
  });
}


$(document).on('click','.btn_login', function(){
  $('.verify_otp').show(500);
  $('.login_group').remove();
});
$(document).on('click','#verify_otp', function(){
  toastr['success']('Đăng nhập thành công');
  setTimeout(function(){
      window.location.href = '/';
  },200);
});
$(document).on('change','#users-province', function(){
  let province_name = $(this).val();
  $.ajax({
    url: '/helper/get-district',
    type: 'POST',
    data: {province_name: province_name},
    success: function (res) {
      if (res) {
        let option = '<option value="">Chọn quận huyện</option>';
        $.each(res, function (key, value) {
          option += '<option value="'+key+'">'+ value +'</option>';
        });
        $('#users-district').html(option);
      }
    }
  });
});

setTimeout(function() {
    $('.alert').alert('close');
}, 5000); 



// render OPT login 
document.addEventListener("DOMContentLoaded", function () {
  var otpInputs = document.querySelectorAll(".otp-input");

  function setupOtpInputListeners(inputs) {
    inputs.forEach(function (input, index) {
      input.addEventListener("paste", function (ev) {
        var clip = ev.clipboardData.getData("text").trim();
        if (!/^\d{6}$/.test(clip)) {
          ev.preventDefault();
          return;
        }

        var characters = clip.split("");
        inputs.forEach(function (otpInput, i) {
          otpInput.value = characters[i] || "";
        });

        enableNextBox(inputs[0], 0);
        inputs[5].removeAttribute("disabled");
        inputs[5].focus();
        updateOTPValue(inputs);
      });

      input.addEventListener("input", function () {
        var currentIndex = Array.from(inputs).indexOf(this);
        var inputValue = this.value.trim();

        if (!/^\d$/.test(inputValue)) {
          this.value = "";
          return;
        }

        if (inputValue && currentIndex < 5) {
          inputs[currentIndex + 1].removeAttribute("disabled");
          inputs[currentIndex + 1].focus();
        }

        if (currentIndex === 4 && inputValue) {
          inputs[5].removeAttribute("disabled");
          inputs[5].focus();
        }
        updateOTPValue(inputs);
      });

      input.addEventListener("keydown", function (ev) {
        var currentIndex = Array.from(inputs).indexOf(this);

        if (!this.value && ev.key === "Backspace" && currentIndex > 0) {
          inputs[currentIndex - 1].focus();
        }
      });
    });
  }

  function updateOTPValue(inputs) {
    var otpValue = "";
    inputs.forEach(function (input) {
      otpValue += input.value;
    });
    if (inputs === otpInputs) {
      document.getElementById("otp").value = otpValue;
    }
  }

  // Setup listeners for OTP inputs
  setupOtpInputListeners(otpInputs);

  // Add event listener for verify button
  document.getElementById("verifyMobileOTP").addEventListener("click", function () {
    var otpValue = document.getElementById("otp").value;
    alert("Submitted OTP: " + otpValue);
    // Add your submit logic here (e.g., AJAX request or form submission)
  });

  // Initial focus on first OTP input field
  otpInputs[0].focus();
});
// end render OPT login 
































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






