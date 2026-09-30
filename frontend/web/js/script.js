// Ajax cần đăng nhập (server trả 401 {login:true}) → báo và chuyển tới trang đăng nhập
$(document).ajaxError(function (event, xhr) {
  var res = xhr.responseJSON;
  if (xhr.status === 401 && res && res.login) {
    if (window.toastr) toastr['warning'](res.message || 'Vui lòng đăng nhập để tiếp tục');
    setTimeout(function () { window.location.href = res.loginUrl || '/site/login'; }, 900);
  }
});

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
    const showcase = document.querySelector('.img-showcase');
    const first = showcase && showcase.querySelector('img');
    if (!first) return;
    showcase.style.transform = `translateX(${- (imgId - 1) * first.clientWidth}px)`;
}

window.addEventListener('resize', slideImage);
//end js image product

$(document).on('click','.option_product',function(){
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
$(document).on('click','.remove_noti', function(){
  let _this = $(this), id = _this.attr('data-id');
  if (!id || !confirm('Xoá thông báo này?')) return;
  $.post('/info/remove-notify', {id: id}).done(function (res) {
    if (res && res.status) {
      $(`#noti_detail_${id}`).remove();
      $(`.notification-list[data-detail="${id}"]`).remove();
      $('.notification-ui_dd-content, body').removeClass('block_scroll');
      if (!$('.notification-list').length) $('.notification-ui_dd-content').html('<p class="text-center p-3 color-gray">Chưa có thông báo</p>');
      toastr['success']('Đã xoá thông báo');
    } else {
      toastr['error']((res && res.message) || 'Không xoá được thông báo');
    }
  }).fail(function () { toastr['error']('Không xoá được thông báo, vui lòng thử lại'); });
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
$(document).on('click','.see_more_product_cat', function(){
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
      if(res['append'])
        $('.product_list').append(res['data']);
      else
        $('.product_list').html(res['data']);
      $('.see_more_product').toggle(!!res['checkLoadMore']);
      if(typeof res['total'] !== 'undefined')
        $('#category_total').text(Number(res['total']).toLocaleString('vi-VN') + ' sản phẩm');
    },
    error: function () {
      checkSendAjaxProduct = true;
      $('.see_more_btn').find('.spinner-border').remove();
      toastr['error']('Không tải được sản phẩm, vui lòng thử lại');
    }
  });
}
//end find product page category


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
var shopState = {sort: 'popular', page: 0, q: '', cate: 0};
function getProductShop(append){
  var shop_id = $('.see_more_shop').attr('shop-id');
  if (!shop_id || !checkSendAjaxProductShop) return;
  checkSendAjaxProductShop = false;
  if (!append) { shopState.page = 0; $('#shop_products').css('opacity', .5); }
  $.ajax({
    url: '/product/get-product-shop',
    type: 'POST',
    data: {sort: shopState.sort, page: shopState.page, shop_id: shop_id, q: shopState.q, cate_id: shopState.cate},
    success: function (res) {
      if (res['append']) $('#shop_products').append(res['data']);
      else $('#shop_products').html(res['data']);
      $('.see_more_product').toggle(!!res['checkLoadMore']);
      $('#shop_total').text(Number(res['total'] || 0).toLocaleString('vi-VN') + ' sản phẩm');
    },
    error: function () {
      if (append) shopState.page--;
      toastr['error']('Không tải được sản phẩm, vui lòng thử lại');
    },
    complete: function () {
      checkSendAjaxProductShop = true;
      $('#shop_products').css('opacity', '');
      $('.see_more_shop').find('.spinner-border').remove();
    }
  });
}
$(document).on('click','.btn_sort_shop', function(){
  $('.btn_sort_shop').removeClass('active');
  $(this).addClass('active');
  shopState.sort = $(this).attr('sort');
  getProductShop(false);
});
$(document).on('click','.see_more_shop', function(){
  shopState.page++;
  $(this).append('<i class="spinner-border text-light"></i>');
  getProductShop(true);
});
$(document).on('click', '.shop_cat_filter', function(){
  shopState.cate = parseInt($(this).data('cat'), 10) || 0;
  $('.shop_cat_filter').removeClass('active');
  if (shopState.cate) $(this).addClass('active');
  getProductShop(false);
  $('html, body').animate({scrollTop: $('#shop_products').offset().top - 140}, 300);
});
var shopSearchTimer = null;
$(document).on('input', '#shop_search', function(){
  var q = $.trim(this.value);
  clearTimeout(shopSearchTimer);
  shopSearchTimer = setTimeout(function(){ shopState.q = q; getProductShop(false); }, 300);
});
$(document).on('click', '.btn_follow', function(){
  var $btn = $(this);
  if ($btn.prop('disabled')) return;
  $btn.prop('disabled', true);
  $.post('/product/toggle-follow', {agentId: $btn.data('agent')}).done(function (res) {
    if (!res || !res.status) { toastr['error']((res && res.message) || 'Không cập nhật được'); return; }
    $btn.toggleClass('following', res.following).text(res.following ? 'Đang theo dõi' : 'Theo dõi');
    $('.follow_count').text(res.total);
    toastr['success'](res.following ? 'Đã theo dõi shop' : 'Đã bỏ theo dõi shop');
  }).always(function () { $btn.prop('disabled', false); });
});


// $(document).on('click', '.btn_login', function () {
//   $(this).append('<i class="spinner-border text-light"></i>');
//   setTimeout(function () {
//     $('.verify_otp').show(500);
//     $('.login_group').remove();
//   }, 2000);
// });
// $(document).on('click','#verify_otp', function(){
//   $(this).append('<i class="spinner-border text-light"></i>');
//   toastr['success']('Đăng nhập thành công');
//   setTimeout(function(){
//     $('#verify_otp').find('.spinner-border').remove();
//       // window.location.href = '/';
//   },1000);
// });
$(document).on('change','#users-province, #userdeliveryaddress-province', function(){
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
        $('#users-district, #userdeliveryaddress-district').html(option);
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
  if (!otpInputs.length) return;

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

  // Initial focus on first OTP input field
  otpInputs[0].focus();
});
// end render OPT login

//js product
//get price product
$(document).on('click', '.option_product', function () {
  let totalClassification = $('#total_classification').val();
  let productId = $('#product_id').val();
  let arrOptionId = [];
  $('.option_product.active').each(function(){
    var optionId = $(this).attr('dt-id');
    arrOptionId.push(optionId);
  });
  if (totalClassification > 0) {
    if (totalClassification == arrOptionId.length) {
        $.ajax({
          url: '/product/get-price',
          type: 'POST',
          data: {productId: productId, arrOptionId:arrOptionId},
          success: function (res) {
            console.log(res.classification_id);
            if (res.price) 
              $('.price_product').text(res.price);
            if(res.classification_id)
              $('#classification_id').val(res.classification_id);
          }
        });
    }
  }
});
$(document).on('click', '#add_cart, #buy_now', function () {
  let typeBtn = $(this).attr('dt-type');
  let totalClassification = $('#total_classification').val();
  let productId = $('#product_id').val();
  let productQty = $('.quantity_product').val();
  let classificationId = $('#classification_id').val();
  let arrOptionId = [];
  $('.option_product.active').each(function(){
    var optionId = $(this).attr('dt-id');
    arrOptionId.push(optionId);
  });
  if (totalClassification > 0) {
    if (totalClassification > arrOptionId.length) {
      toastr['warning']('Vui lòng chọn Phân loại sản phẩm');
      return;
    }
  }
  $.ajax({
    url: '/cart/add-cart',
    type: 'POST',
    data: {productId: productId, classificationId:classificationId, productQty:productQty},
    success: function (res) {
      if (res == 1) {
        if(typeBtn == 'buynow'){
          window.location.href = '/cart/index';
        }
        toastr['success']('Sản phẩm đã được thêm vào Giỏ hàng');
      }
    }
  });
});
$(document).on('click', '.update_qty_product_cart', function () {
  let _this = $(this);
  let type = $(this).attr('dt-type');
  let _inputQty = $(this).parent().find('.quantity_product');
  let qtyCurrent = parseInt(_inputQty.val());
  let productId = $(this).attr('prod-id');
  
  if (type == 'decrease') {
    if (qtyCurrent > 1)
      _inputQty.val(qtyCurrent - 1);
  } else {
    _inputQty.val(qtyCurrent + 1);
  }
  $.ajax({
    url: '/cart/update-info-product',
    type: 'POST',
    data: {productId:productId, qty: _inputQty.val()},
    success: function (res) {
      if (res) {
        _this.parent().parent().find('.price_cart').text(formatNumber(res.price_order));
      }
    }
  });
  setTimeout(function(){
    updateCart();
  }, 100);
});
$(document).on('click','#check_all_product', function(){
  if ($(this).prop('checked') == true) {
    $('.input_choose_product').prop('checked', true);
  } else {
    $('.input_choose_product').prop('checked', false);
  }
  setTimeout(function(){
    updateCart();
  }, 100);
});
$(document).on('click','.input_choose_product', function(){
  setTimeout(function(){
    updateCart();
  }, 100);
});

function updateCart() {
  let arrProductId = [];
  $('.input_choose_product:checked').each(function () {
    arrProductId.push($(this).val());
  });
  let voucherId = $('.input_voucher:checked').val() || 0;
  $.ajax({
    url: '/cart/get-info-order',
    type: 'POST',
    data: {arrProductId: arrProductId, voucherId: voucherId},
    success: function (res) {
      if (!res) return;
      $('.price_order').text(formatNumber(res.price_order));
      $('.fee_ship').text(formatNumber(res.fee_ship));
      $('.total_price_order').text(formatNumber(res.total_price_order));
      $('.voucher_deduct').text('-' + formatNumber(res.voucher_deduct || 0));
      $('.voucher_deduct_row').toggle((res.voucher_deduct || 0) > 0);
      if (voucherId > 0 && arrProductId.length && res.voucher_valid === false) {
        $('#voucher_label span').prepend('<em class="voucher_warn" style="color:#E45625">Voucher chưa áp dụng được cho sản phẩm đã chọn · </em>');
      }
    }
  });
}

$(document).on('change', 'input.type_payment', function () {
  let $r = $(this);
  $('#payment_method_label').html('<p>' + $('<div>').text($r.data('label')).html() + '</p><span>' + $('<div>').text($r.data('sub') || '').html() + '</span>');
  $('#modalTypePayment').modal('hide');
});
$(document).on('change', '.input_voucher', function () {
  $('#voucher_label').show().find('p').text($(this).data('name') || 'Đã chọn voucher');
  $('#voucher_label .voucher_warn').remove();
  $('#modalVoucherPayment').modal('hide');
  updateCart();
});
$(document).on('click', '.clear_voucher', function () {
  $('.input_voucher').prop('checked', false);
  $('#voucher_label').hide().find('.voucher_warn').remove();
  updateCart();
});

$(document).on('click', '#ajax-submit-delivery', function(e){
    e.preventDefault();
    
    var form = $('#ajax-form-delivery');
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
            if(response.success) {
              location.reload();
            } else {
              $.each(response.errors, function (key, val) {
                toastr['warning'](val[0]);
              });
            }
        },
        error: function() {
            toastr['warning']('Có lỗi vui lòng thử lại sau');
        }
    });
});
$(document).on('click', '.remove_product_cart', function(){
  let _this = $(this);
  let productId = $(this).attr('prod-id');
  if (confirm('Bạn có trắc chắn muốn xoá sản phẩm')) {
    $.ajax({
        url: '/cart/remove-product-cart',
        type: 'POST',
        data: {productId: productId},
        success: function(response) {
          if(response){
            _this.parent().parent().remove();
            setTimeout(function(){
              updateCart();
            }, 500);
          }
        },
        error: function() {
            toastr['warning']('Có lỗi vui lòng thử lại sau');
        }
    });
  }
});
$(document).on('click', '#submit_order', function(){
  let $btn = $(this);
  if ($btn.prop('disabled')) return;
  let delivery_address_id = $('#idDeliveryAddress').val();
  let type_payment = $('input.type_payment:checked').val();
  let voucher_id = $('.input_voucher:checked').val();
  let arr_product_id = [];
  $('.input_choose_product:checked').each(function () {
    arr_product_id.push($(this).val());
  });
  if(!arr_product_id.length){
    toastr['warning']('Vui lòng tích chọn sản phẩm cần đặt hàng');
    return;
  }
  if(delivery_address_id == 0){
    toastr['warning']('Vui lòng cập nhật Địa chỉ giao hàng');
    return;
  }
  if(!type_payment){
    toastr['warning']('Vui lòng chọn Phương thức thanh toán');
    return;
  }
  if(!voucher_id){
    voucher_id = 0;
  }

  $btn.prop('disabled', true).append(' <i class="spinner-border spinner-border-sm"></i>');
  $.ajax({
      url: '/cart/order',
      type: 'POST',
      data: {delivery_address_id: delivery_address_id, type_payment: type_payment, voucher_id: voucher_id, arr_product_id: arr_product_id},
      success: function(response) {
        if(response.status){
          toastr['success']('Đặt hàng thành công');
          setTimeout(function(){
            window.location.href = '/info/await-confirmed';
          }, 1000);
          return;
        }
        toastr['warning'](response.msg || 'Không đặt được hàng, vui lòng thử lại');
        $btn.prop('disabled', false).find('.spinner-border').remove();
      },
      error: function() {
          toastr['warning']('Có lỗi vui lòng thử lại sau');
          $btn.prop('disabled', false).find('.spinner-border').remove();
      }
  });
});
//end js product

//js history order
//find product page category
var page_product_his = 0;
var checkSendAjaxHis = true;
$(document).on('click','.see_more_product_history', function(){
  page_product_his ++;
  let type = $(this).attr('dt-type');
  let _this = $(this);
  _this.append('<i class="spinner-border text-light"></i>')
  let sort = $('.btn_sort.active').attr('sort');
  if(checkSendAjaxHis){
    checkSendAjaxHis = false;
    $.ajax({
      url: '/info/get-product-his',
      type: 'POST',
      data: {type: type, page: page_product_his},
      success: function (res) {
        _this.find('.spinner-border').remove();
        checkSendAjaxHis = true;
        if(res['data']){
          $('.product_info_list').append(res['data']);
        }
        if(!res['checkLoadMore']){
          $('.see_more_product').remove();
        }
      }
    });
  }
});


function formatNumber(number) {
  console.log(number);
  number = number.toLocaleString('it-IT', {style : 'currency', currency : 'VND'});
  number = number.replace("VND", "");
  return number;
}



var pageNotReview = 0;
var checkSendAjaxNotReview = true;
$(document).on('click','.see_more_product_not_review', function(){
  let type = $(this).attr('dt-type');
  pageNotReview ++;
  let _this = $(this);
  _this.append('<i class="spinner-border text-light"></i>');
  if(checkSendAjaxNotReview){
    checkSendAjaxNotReview = false;
    $.ajax({
      url: '/product/get-product-review',
      type: 'POST',
      data: {page:pageNotReview, type: type},
      success: function (res) {
        $('.see_more_product_not_review').find('.spinner-border').remove();
        checkSendAjaxNotReview = true;
        if(res['data']){
          $('.list_review').append(res['data']);
        }
        if(!res['checkLoadMore']){
          _this.parent().remove();
        }
      }
    });
  }
});

var pageReviewed = 0;
var checkSendAjaxReviewed = true;
$(document).on('click','.see_more_product_reviewed', function(){
  let type = $(this).attr('dt-type');
  pageReviewed ++;
  let _this = $(this);
  _this.append('<i class="spinner-border text-light"></i>');
  if(checkSendAjaxReviewed){
    checkSendAjaxReviewed = false;
    $.ajax({
      url: '/product/get-product-review',
      type: 'POST',
      data: {page:pageReviewed, type: type},
      success: function (res) {
        $('.see_more_product_reviewed').find('.spinner-border').remove();
        checkSendAjaxReviewed = true;
        if(res['data']){
          $('.comment_list').append(res['data']);

          setTimeout(function () {
            $(".slider-comment-for").not('.slick-initialized').slick(options_for)
            $(".slider-comment-nav").not('.slick-initialized').slick(options_nav)
          }, 100);
        }
        if(!res['checkLoadMore']){
          _this.parent().remove();
        }
      }
    });
  }
});

$(document).on('click','.btn_submit_review', function(){
  let _this = $(this);
  let productId = $(this).attr('pro-id');
  let orderId = $(this).attr('od-id');
  let files = $('#fileInput_'+orderId)[0].files;
  let content = $('#content_'+orderId).val();
  let rating = $(`.rating_${+orderId}:checked`).val();  

  if(rating == 0){
    toastr['error']('Vui lòng chọn chất lượng sản phẩm');
    return;
  }
  
  if(!content){
    toastr['error']('Vui lòng nhập nội dung đánh giá');
    return;
  }

  // Create a FormData object
  let formData = new FormData();
  $.each(files, function(i, file) {
    formData.append('files[]', file);
  });
  formData.append('rating', rating);
  formData.append('content', content);
  formData.append('product_id', productId);
  formData.append('order_id', orderId);

  $.ajax({
    url: '/info/save-review',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function (res) {
      if(res.status){
        $('#modalReview'+orderId).modal('hide');
        $('.modal-backdrop').hide();
        setTimeout(() => {
          _this.parent().parent().parent().parent().parent().parent().remove();
        }, 300);
        toastr['success'](res.message);
      }else{
        toastr['error'](res.message);
      }
      console.log("🚀 ~ $ ~ res:", res)
    }
  });
});

$(document).on('change','.fileInput', function(event){
  const orderId = $(this).attr('order-id'); 
  const previewBox = $('#previewBox_'+orderId);
        previewBox.html(''); // Clear the current previews
  Array.from(event.target.files).forEach(file => {
      const reader = new FileReader();
      reader.onload = function(e) {
          const previewItem = document.createElement('div');
          previewItem.classList.add('preview-item');

          if (file.type.startsWith('image/')) {
              const img = document.createElement('img');
              img.src = e.target.result;
              previewItem.appendChild(img);
          } else if (file.type.startsWith('video/')) {
              const video = document.createElement('video');
              video.src = e.target.result;
              video.controls = true;
              previewItem.appendChild(video);
          }

          previewBox.append(previewItem);
      };
      reader.readAsDataURL(file);
  });
});

var pageSeen = 0;
var checkSendAjaxSeen = true;
$(document).on('click','.see_more_product_seen', function(){
  pageSeen ++;
  let _this = $(this);
  _this.append('<i class="spinner-border text-light"></i>');
  if(checkSendAjaxSeen){
    checkSendAjaxSeen = false;
    $.ajax({
      url: '/product/get-product-seen',
      type: 'POST',
      data: {page:pageSeen},
      success: function (res) {
        $('.see_more_product_seen').find('.spinner-border').remove();
        checkSendAjaxSeen = true;
        if(res['data']){
          $('.list_seen').append(res['data']);
        }
        if(!res['checkLoadMore']){
          _this.parent().remove();
        }
      }
    });
  }
});

var pageFavourite = 0;
var checkSendAjaxFavourite = true;
$(document).on('click','.see_more_product_favourite', function(){
  pageFavourite ++;
  let _this = $(this);
  _this.append('<i class="spinner-border text-light"></i>');
  if(checkSendAjaxFavourite){
    checkSendAjaxFavourite = false;
    $.ajax({
      url: '/product/get-product-favourite',
      type: 'POST',
      data: {page:pageFavourite},
      success: function (res) {
        $('.see_more_product_favourite').find('.spinner-border').remove();
        checkSendAjaxFavourite = true;
        if(res['data']){
          $('.list_favourite').append(res['data']);
        }
        if(!res['checkLoadMore']){
          _this.parent().remove();
        }
      }
    });
  }
});























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




  $(document).on('click', '.sp_new', function () {
    $(this).parent().parent().toggleClass('active');
    let class_i = $(this).find('i').attr('class');
    if (class_i == 'fal fa-plus')
      $(this).find('i').attr('class', 'far fa-window-minimize');
    else
      $(this).find('i').attr('class', 'fal fa-plus');
  });












});







// Chia sẻ liên kết (Web Share API trên điện thoại, không có thì copy link)
function copyText(text) {
  if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text);
  var $t = $('<textarea>').val(text).css({position: 'fixed', opacity: 0}).appendTo('body');
  $t[0].select();
  var ok = document.execCommand('copy');
  $t.remove();
  return ok ? Promise.resolve() : Promise.reject();
}
$(document).on('click', '.js_share', function (e) {
  e.preventDefault();
  var url = $(this).data('url') || window.location.href, title = $(this).data('title') || document.title;
  if (navigator.share) {
    navigator.share({title: title, url: url}).catch(function () {});
    return;
  }
  copyText(url).then(function () { toastr['success']('Đã sao chép liên kết'); }, function () { toastr['error']('Không sao chép được liên kết'); });
});
$(document).on('click', '.js_copy', function (e) {
  e.preventDefault();
  copyText(String($(this).data('copy'))).then(function () { toastr['success']('Đã sao chép'); }, function () { toastr['error']('Không sao chép được'); });
});

// Xoá tài khoản
$(document).on('click', '.js_delete_account', function (e) {
  e.preventDefault();
  Swal.fire({
    title: 'Xoá tài khoản?',
    html: 'Tài khoản sẽ bị khoá và gỡ số điện thoại, bạn sẽ không xem lại được lịch sử đơn hàng.<br>Gõ <b>XOA</b> để xác nhận.',
    input: 'text',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xoá tài khoản',
    cancelButtonText: 'Huỷ',
    confirmButtonColor: '#E45625',
    preConfirm: function (value) {
      if ($.trim(value).toUpperCase() !== 'XOA') {
        Swal.showValidationMessage('Vui lòng gõ XOA để xác nhận');
        return false;
      }
      return $.post('/info/delete-account', {confirm: 'XOA'}).then(function (res) {
        if (!res.status) Swal.showValidationMessage(res.message || 'Không xoá được tài khoản');
        return res;
      }, function () { Swal.showValidationMessage('Có lỗi xảy ra, vui lòng thử lại'); });
    }
  }).then(function (result) {
    if (result.value && result.value.status) {
      toastr['success'](result.value.message);
      setTimeout(function () { window.location.href = '/'; }, 900);
    }
  });
});

// Thả tim sản phẩm (chi tiết, đã xem, yêu thích)
$(document).on('click', '.btn_favourite', function () {
  var $btn = $(this);
  if ($btn.prop('disabled')) return;
  $btn.prop('disabled', true);
  $.post('/product/toggle-favourite', {productId: $btn.data('product')}).done(function (res) {
    if (!res || !res.status) { toastr['error']((res && res.message) || 'Không cập nhật được'); return; }
    $('.btn_favourite[data-product="' + $btn.data('product') + '"]').each(function () {
      $(this).toggleClass('active', res.liked).attr('aria-pressed', res.liked ? 'true' : 'false')
        .attr('title', res.liked ? 'Bỏ yêu thích' : 'Thêm vào yêu thích')
        .find('img').attr('src', '/images/icon/' + (res.liked ? 'heart-active' : 'heart-inactive') + '.svg');
    });
    if (!res.liked && $btn.data('remove-card')) $btn.closest('.group_item_shop').fadeOut(200, function () { $(this).remove(); });
    toastr['success'](res.message);
  }).always(function () { $btn.prop('disabled', false); });
});

// Huỷ đơn đang chờ xác nhận
$(document).on('click', '.js_cancel_order', function () {
  var $btn = $(this);
  Swal.fire({title: 'Huỷ đơn hàng?', text: 'Đơn hàng sẽ bị huỷ và không thể khôi phục.', icon: 'warning', showCancelButton: true,
    confirmButtonText: 'Huỷ đơn', cancelButtonText: 'Không', confirmButtonColor: '#E45625'}).then(function (r) {
    if (!r.value) return;
    $btn.prop('disabled', true);
    $.post('/info/cancel-order', {id: $btn.data('id')}).done(function (res) {
      toastr[res.status ? 'success' : 'error'](res.message);
      if (res.status) setTimeout(function () { location.reload(); }, 800); else $btn.prop('disabled', false);
    }).fail(function () { $btn.prop('disabled', false); toastr['error']('Có lỗi xảy ra, vui lòng thử lại'); });
  });
});

// Gửi yêu cầu trả hàng / hoàn tiền
$(document).on('submit', '#form_refund', function (e) {
  e.preventDefault();
  var $f = $(this), $btn = $f.find('[type=submit]');
  var data = {id: $f.data('id'), situation: $f.find('[name=situation]:checked').val() || '', reason: $f.find('[name=reason]').val(), note: $f.find('[name=note]').val()};
  if (!data.situation) { toastr['warning']('Vui lòng chọn tình huống đang gặp'); return; }
  if (!data.reason) { toastr['warning']('Vui lòng chọn lý do'); return; }
  $btn.prop('disabled', true);
  $.post('/info/refund-request', data).done(function (res) {
    toastr[res.status ? 'success' : 'error'](res.message);
    if (res.status) setTimeout(function () { location.reload(); }, 900); else $btn.prop('disabled', false);
  }).fail(function () { $btn.prop('disabled', false); toastr['error']('Có lỗi xảy ra, vui lòng thử lại'); });
});
