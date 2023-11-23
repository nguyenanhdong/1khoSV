<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;

$this->title = 'Dashboard';
?>
<div class="site-index">
    <div class="right" style="text-align: right; margin-bottom: 12px; width: 100%;">
        <div id="date-ranger-all" style="width: auto;" class="pull-right tooltips btn btn-fit-height grey-salt" data-placement="top" data-original-title="Tất cả thời gian">
            <i class="fal fa-calendar-alt"></i>&nbsp;<span class="visible-lg-inline-block">Tất cả thời gian</span>&nbsp; <i class="fal fa-angle-down"></i>
        </div>
    </div>
    <div class="row" style="justify-content: space-between;">
        <div class="col-sm-6 col-xl-2">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        <a href="/customer/index" target="_blank" class="link-detail total_customer_reg" style="color:#fff"><?= $dataStatistic['user_register'] ?></a>
                        <small style="font-size: 14px;" class="m-0 l-h-n">Khách hàng đăng ký</small>
                    </h3>
                </div>
                <i class="fal fa-user position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        <a href="javascript:;" class="link-detail total_order_work" style="color:#fff"><?= $dataStatistic['total_transaction'] ?></a>
                        <small style="font-size: 14px;" class="m-0 l-h-n">Đơn hàng đặt dịch vụ</small>
                    </h3>
                </div>
                <i class="fal fa-gem position-absolute pos-right pos-bottom opacity-15  mb-n1 mr-n4" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-danger-400 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        <a href="javascript:;" class="link-detail total_order_work_complete" style="color:#fff"><?= $dataStatistic['total_money'] ?>đ</a>
                        <small style="font-size: 14px;" class="m-0 l-h-n">Doanh thu (Đơn hoàn thành)</small>
                    </h3>
                </div>
                <i class="fal fa-gem position-absolute pos-right pos-bottom opacity-15  mb-n1 mr-n4" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-200 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        <a href="javascript:;" class="link-detail total_order_work_draft" style="color:#fff"><?= $dataStatistic['total_money_draft'] ?>đ</a>
                        <small style="font-size: 14px;" class="m-0 l-h-n">Doanh thu tạm tính <span class="fal fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="Tổng doanh thu tạm tính của các đơn có phí và có trạng thái: Đang chờ, Đang diễn ra, Hoàn thành"></span></small>
                    </h3>
                </div>
                <i class="fal fa-lightbulb position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Biểu đồ thống kê
                    </h2>
                    <div id="date-ranger" style="width: auto;" class="pull-right tooltips btn btn-fit-height grey-salt" data-placement="top" data-original-title="Ngày đăng bài viết">
                        <i class="fal fa-calendar-alt"></i>&nbsp;<span class="visible-lg-inline-block"><?= date('d/m/Y', strtotime($date_start)) . ' - ' . date('d/m/Y', strtotime($date_end)) ?></span>&nbsp; <i class="fal fa-angle-down"></i>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content bg-subtlelight-fade">
                        <div id="js-checkbox-toggles" class="d-flex mb-3">
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-0" id="gra-0" checked="checked">
                                <label class="custom-control-label" for="gra-0">KH đăng ký</label>
                            </div>
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-1" id="gra-1" checked="checked">
                                <label class="custom-control-label" for="gra-1">Tổng đơn hàng</label>
                            </div>
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-2" id="gra-2" checked="checked">
                                <label class="custom-control-label" for="gra-2">Tổng đơn hàng hoàn thành</label>
                            </div>
                            <!-- <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-3" id="gra-3" checked="checked">
                                <label class="custom-control-label" for="gra-3">Đăng ký form offline</label>
                            </div> -->
                        </div>
                        <div id="legendContainer" style="float:right"></div>
                        <div id="flot-toggles" class="w-100 mt-1" style="height: 300px;display: inline-block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<style>
button.applyBtn.btn.btn-sm.blue { background-color: #886ab5; color: #fff; }
button.applyBtn.btn.btn-sm.blue:hover {opacity:.8 }
#date-ranger-all{background-color: palegoldenrod;padding: 8px 4px;min-width: 175px;width:100%;width: 100%;border: 1px solid #E5E5E5;text-align: left; padding-left: 14px;}
</style>
<script type="text/javascript">
    /* defined datas */
    var dataUserRegister = $.parseJSON('<?= json_encode($dataStatistic['chart']['user_register']) ?>');
    var dataTransaction  = $.parseJSON('<?= json_encode($dataStatistic['chart']['transaction']) ?>');
    var dataTransactionSuccess  = $.parseJSON('<?= json_encode($dataStatistic['chart']['transaction_success']) ?>');
    
    var dataTargetProfit = [];
    var flot_toggle = function(){
        var data = [
            {
            label: "KH đăng ký",
            data: dataUserRegister,
            color: '#6ab8f7',
            lines:{
                show: true,
                lineWidth: 2
            },
            shadowSize: 0,
            points:
            {
                show: true
            }
        },
        {
            label: "Đơn hàng",
            data: dataTransaction,
            color: 'green',
            lines:{
                show: true,
                lineWidth: 2
            },
            shadowSize: 0,
            points:{
                show: true
            }
        },
        {
            label: "Đơn hàng hoàn thành",
            data: dataTransactionSuccess,
            color: 'burlywood',
            lines:{
                show: true,
                lineWidth: 2
            },
            shadowSize: 0,
            points:{
                show: true
            }
        },
        // {
        //     label: "Đăng ký Form Offline",
        //     data: dataRegisterOffline,
        //     color: 'gold',
        //     lines:{
        //         show: true,
        //         lineWidth: 2
        //     },
        //     shadowSize: 0,
        //     points:{
        //         show: true
        //     }
        // }
        ];

        var options = {
            legend:{         
                container:$("#legendContainer"),
                backgroundOpacity: 0.5,
                noColumns: 0,
                backgroundColor: "#fff",   
                position: "ne"
            },
            grid:
            {
                hoverable: true,
                clickable: true,
                tickColor: '#f2f2f2',
                borderWidth: 1,
                borderColor: '#f2f2f2'
            },
            tooltip: true,
            tooltipOpts:
            {
                cssClass: 'tooltip-inner',
                defaultTheme: false,
                content: "%s (%x): %y",
            },
            xaxis:
            {
                mode: "categories"
            },
            yaxes:
            {
                tickFormatter: function(val, axis)
                {
                    return "$" + val;
                },
                max: 10000
            }

        };

        var plot2 = null;

        function plotNow(){
            var d = [];
            $("#js-checkbox-toggles").find(':checkbox').each(function(){
                if ($(this).is(':checked'))
                {
                    d.push(data[$(this).attr("name").substr(4, 1)]);
                }
            });
            if (d.length > 0){
                if (plot2)
                {
                    plot2.setData(d);
                    plot2.draw();
                }
                else
                {
                    plot2 = $.plot($("#flot-toggles"), d, options);
                }
            }

        };

        $("#js-checkbox-toggles").find(':checkbox').on('change', function(){
            plotNow();
        });
        plotNow();
    }
    function ajaxStatistic(type,date_start,date_end){
        $.ajax({
            url : window.location.href,
            type : 'POST',
            data : {ajax : 1,type : type, date_start: date_start,date_end: date_end },
            success : function(res){
                var arr = $.parseJSON(res);
                if( type == 1 ){
                    dataUserRegister = arr.chart.user_register;
                    dataTransaction  = arr.chart.transaction;
                    dataRegisterOffline  = arr.chart.register_offline;
                    dataTransactionSuccess  = arr.chart.transaction_success;
                    flot_toggle();
                }else{
                    $('.total_order_work_complete').html(arr.total_money + 'đ').attr('href','/order-work/index?OrderWorkSearch[status]=2&OrderWorkSearch[date_start]=' + date_start + '&OrderWorkSearch[date_end]='+date_end);
                    $('.total_order_work_draft').html(arr.total_money_draft + 'đ').attr('href','/order-work/index?OrderWorkSearch[date_start]=' + date_start + '&OrderWorkSearch[date_end]='+date_end);
                    $('.total_order_work').html(arr.total_transaction).attr('href','/order-work/index?OrderWorkSearch[date_start]=' + date_start + '&OrderWorkSearch[date_end]='+date_end);
                    $('.total_customer_reg').html(arr.user_register).attr('href','/customer/index?CustomerSearch[date_start]=' + date_start + '&CustomerSearch[date_end]='+date_end);
                }
            }
        })
    }
    jQuery(document).ready(function(){
        var startDate = moment('<?= $date_start ?>');
        var endDate   = moment('<?= $date_end ?>');
        $('#date-ranger').daterangepicker({
                opens: 'left',
                startDate: startDate,
                endDate: endDate,
                minDate: '01/03/2021',
                maxDate: '12/31/2025',
                dateLimit: {
                    days: 14
                },
                showDropdowns: false,
                showWeekNumbers: true,
                timePicker: false,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                ranges: {
                    'Hôm nay': [moment(), moment()],
                    'Hôm qua': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Tuần này(Thứ 2 - Hôm nay)': [moment().startOf('week'), moment()],
                    'Tuần trước(Thứ 2 - Chủ Nhật trước)': [moment().subtract('week', 1).startOf('week'), moment().subtract('week', 1).endOf('week')],
                    '7 ngày qua': [moment().subtract('days', 6), moment()],
                    '14 ngày qua': [moment().subtract('days', 13), moment()],
                },
                buttonClasses: ['btn btn-sm'],
                applyClass: ' blue',
                cancelClass: 'default',
                format: 'MM/DD/YYYY',
                separator: ' đến ',
                locale: {
                    applyLabel: 'Chọn',
                    cancelLabel: 'Hủy',
                    fromLabel: 'Từ',
                    toLabel:'Đến',
                    customRangeLabel: 'Tùy chỉnh',
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                    firstDay: 1
                }
            },
            function (start, end) {
                $('#date-ranger span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                ajaxStatistic(1,start.format('YYYY-MM-DD'),end.format('YYYY-MM-DD'));
            }
        );
        $('#date-ranger').show();
        $('#date-ranger').on('cancel.daterangepicker', function(ev, picker) {
            $('#date-ranger span').html('Chọn ngày');
        });
        
        flot_toggle();
        if( $(window).width() <= 767 ){
            $('#js-checkbox-toggles').removeClass('d-flex');
            $('.custom-control.custom-switch.mr-2').css('margin-bottom','6px');
        }else{
            $('#js-checkbox-toggles').addClass('d-flex');
        }

        $('#date-ranger-all').daterangepicker({
                opens: 'left',
                // startDate: startDate,
                // endDate: endDate,
                minDate: '01/03/2021',
                maxDate: '12/31/2025',
                dateLimit: {
                    days: 14
                },
                showDropdowns: false,
                showWeekNumbers: true,
                timePicker: false,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                ranges: {
                    'Hôm nay': [moment(), moment()],
                    'Hôm qua': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Tuần này(Thứ 2 - Hôm nay)': [moment().startOf('week'), moment()],
                    'Tuần trước(Thứ 2 - Chủ Nhật trước)': [moment().subtract('week', 1).startOf('week'), moment().subtract('week', 1).endOf('week')],
                    '7 ngày qua': [moment().subtract('days', 6), moment()],
                    '14 ngày qua': [moment().subtract('days', 13), moment()],
                },
                buttonClasses: ['btn btn-sm'],
                applyClass: ' blue',
                cancelClass: 'default',
                format: 'MM/DD/YYYY',
                separator: ' đến ',
                locale: {
                    applyLabel: 'Chọn',
                    cancelLabel: 'Hủy',
                    fromLabel: 'Từ',
                    toLabel:'Đến',
                    customRangeLabel: 'Tùy chỉnh',
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                    firstDay: 1
                }
            },
            function (start, end) {
                $('#date-ranger-all span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                ajaxStatistic(0,start.format('YYYY-MM-DD'),end.format('YYYY-MM-DD'));
            }
        );
        $('#date-ranger-all').show();
        $('#date-ranger-all').on('cancel.daterangepicker', function(ev, picker) {
            $('#date-ranger-all span').html('Tất cả thời gian');
            ajaxStatistic(0,'','');
        });
    });

</script>
