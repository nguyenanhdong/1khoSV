jQuery(document).ready(function () {
    var _0xd85bx5;
    _0xd85bx13();
    _0xd85bx10();
    var usr = "";
    if( $('#usrif').length > 0 )
        usr = $('#usrif').val();
    jQuery(document).on('click', '.list_video li a', function () {
        if ($(this).hasClass('active'))
            return false;
        _0xd85bx11();
        $('.list_video li a').removeClass('active');
        $(this).addClass('active');
        var _this = $(this);
        var id_less = _this.attr('data-id');
        $("html, body").animate({ scrollTop: 100 }, "fast");
        if ($(this).hasClass('show')) {
            var link_youtube = _this.attr('link_youtube');
            $.post('/product/check-lesson', { id: id_less }, function (res) {
                $('.title_lession').text(res.title);
                $('.lesson-description').html(res.short_desc);
                $('.lesson-description-full').html(res.desc);
                $('.point,.pt').text(res.star);
                $('.stars').attr('data-rating',res.star).stars();
                if (parseInt(res.status) === 1) {
                    _0xd85bx12(id_less, link_youtube);
                    usr = res.userInfo;
                } else {
                    $('#video_player').html('<img class="img-course" src="' + $('#video_player').attr('data-img') + '" />');
                    var url_open_modal = !res.isLogin ? '/dang-nhap' : '/noi-dung-khoa?id=' + $('#course_id').val();
                    $('#modal .modal-content').load(url_open_modal,function(){
                        setTimeout(function(){
                            $('#modal').modal('show');
                        }, 500);
                    });
                }
                
            });

        } else {
            $.post('/product/check-lesson', { id: id_less }, function (res) {
                $('.title_lession').text(res.title);
                $('.lesson-description').html(res.short_desc);
                $('.lesson-description-full').html(res.desc);
                $('#video_player').html('<img class="img-course" src="' + $('#video_player').attr('data-img') + '" />');
                var url_open_modal = !res.isLogin ? '/dang-nhap' : '/noi-dung-khoa?id=' + $('#course_id').val();
                $('#modal .modal-content').load(url_open_modal,function(){
                    setTimeout(function(){
                        $('#modal').modal('show');
                    }, 500);
                });
            });
        }
    });

    function _0xd85bx10() {
        if( $('.lesson-list a.active').length > 0 ){
            if( $('.lesson-list a.active').attr('link_youtube') != "" ){
                _0xd85bx12($('.lesson-list a.active').attr('data-id'), $('.lesson-list a.active').attr('link_youtube'));
            }
        }
    }
    function _0xd85bx11() {
        if ($('#video_player video').length > 0)
            _0xd85bx5.dispose();
    }
    var timeout;
    function appendUserInfo(){
        clearTimeout(timeout);
        $('#usr').remove();
        if( usr == '' )
            return false;
        var minTimeShow = 30;//40;
        var maxTimeShow = 60;//70;
        var minTimeHide = 10;
        var maxTimeHide = 15;
        timeout = setTimeout(function(){
            $('.video-js').append('<div id="usr" style="color: white;position: absolute;z-index: 1;opacity: 0.3;width: 100%;text-align: center;top: 50%;transform: translate(0, -50%);font-size: 50px;">' + usr + '</div>');
            setTimeout(function(){
                $('#usr').remove();
                appendUserInfo();
            },(Math.floor(Math.random() * (maxTimeHide - minTimeHide + 1)) + minTimeHide)*1000);
        },(Math.floor(Math.random() * (maxTimeShow - minTimeShow + 1)) + minTimeShow)*1000);
    }
    function _0xd85bx12(id, link_youtube) {
        $('#video_player .img-course').remove();
        if (link_youtube) {
            $('#iframe').remove();
            var _height = $( window ).width() <= 600 ? 250 : 410;
            var video = `<iframe id= 'iframe' width="100%" height="${_height}" src="https://www.youtube.com/embed/${link_youtube}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            $('#video_player').append(video);
            
        } else {
            
            $('#iframe').remove();
            var video = document.createElement("video");
            video.setAttribute("id", "video");
            video.setAttribute("class", "video-js vjs-default-skin vjs-fluid vjs-16-9 vjs-big-play-centered ui-min ui-smooth v_5e8494e7ccb30-dimensions vjs-controls-enabled vjs-workinghover vjs-v7 vjs-hls-quality-selector vjs-has-started vjs-paused vjs-user-inactive");
            var video_source = document.createElement("source");
            video_source.setAttribute("src", "/uploads/video-lesson/" + id + "/video.m3u8");
            // video_source.setAttribute("src", "https://www.youtube.com/watch?v=5HQcct5jzDk");
            video_source.setAttribute("type", "application/x-mpegURL");
            video.appendChild(video_source);
            document.getElementById("video_player").appendChild(video);

            _0xd85bx13();
        }
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
                        overrideNative: true
                    }
                },
                "playbackRates": [0.25, 0.5, 1, 1.5, 2],
                "preload": "auto"
            });

            _0xd85bx5.ready(function () {
                this.on("play", function (_0xd85bx4) {
                    this.isPlaying = true;
                    appendUserInfo();
                    jQuery(".video-js").each(function (_0xd85bx7) {
                        if (_0xd85bx2 !== _0xd85bx7) {
                            this.player.pause()
                        }
                    })
                });
                this.on("playing", function (_0xd85bx4) {
                    appendUserInfo();
                });
                this.on("pause", function (_0xd85bx4) {
                    clearTimeout(timeout);
                    $('#usr').remove();
                });
                this.on("ended", function () {
                    var stt = parseInt($('.list_video li a.active').attr('data-stt')) + 1;
                    if( $('.list_video li a[data-stt=' + stt + ']').length > 0 )
                        $('.list_video li a[data-stt=' + stt + ']').trigger('click');
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
                        this.isPlaying = true;
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
    function _0xd85bx14(_0xd85bxa) {
        var _0xd85bxb = "";
        var _0xd85bxc = "123456780ABCDEFGHKLMNOPYTRQW";
        for (var _0xd85bxd = 0; _0xd85bxd < _0xd85bxa.length; _0xd85bxd++) {
            if (_0xd85bxd % 2 == 0) {
                _0xd85bxb += _0xd85bxa[_0xd85bxd]
            } else {
                _0xd85bxb += _0xd85bxc[Math.floor((Math.random() * _0xd85bxc.length))];
                _0xd85bxb += _0xd85bxa[_0xd85bxd]
            }
        };
        return _0xd85bxb
    }

    function _0xd85bx15(_0xd85bx9, _0xd85bx16) {
        jQuery.ajax({
            url: "/video/press?bnid=" + _0xd85bx16 + "&tk=" + _0xd85bx14(_0xd85bx9),
            success: function (_0xd85bx11) { },
            cache: false
        })
    }
})