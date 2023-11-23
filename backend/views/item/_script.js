// $('i.glyphicon-refresh-animate').hide();
function updateItems(r) {
    _opts.items.available = r.available;
    _opts.items.assigned = r.assigned;
    search('available');
    search('assigned');
}

$('.btn-assign').click(function () {
    var $this = $(this);
    var target = $this.data('target');
    var items = $('select.list[data-target="' + target + '"]').val();

    if (items && items.length) {
        $this.children('i').show();
        $.post($this.attr('href'), {items: items}, function (r) {
            updateItems(r);
        }).always(function () {
            $this.children('i').hide();
        });
    }
    return false;
});

$('.search[data-target]').keyup(function () {
    search($(this).data('target'));
});

$('.controller-toggle').click(function(){
    var _parent = $(this).parent();
    if( _parent.find('.list-action').hasClass('hide') )
        _parent.find('.list-action').removeClass('hide');
    else
        _parent.find('.list-action').addClass('hide');
});

$('.btn-submit').click(function(){
    var _this = $(this);
    _this.find('i').attr('class','fal fa-spin fa-spinner').show();
    var listActionAssign    = [];
    var inputChecked        = $('input.input_action:checked');
    if( inputChecked.length > 0 ){
        for(var i = 0; i < inputChecked.length ; i++){
            listActionAssign.push(inputChecked[i].value);
        }
    }

    $.post(window.location.href, {dataAssign: listActionAssign, typeAssign:'permission'}, function (r) {
        _this.find('i').attr('class','fal fa-check').show();
    }).always(function () {
        _this.find('i').attr('class','fal fa-check').show();
    });

});

$('.btn-submit-role').click(function(){
    var _this = $(this);
    _this.find('i').attr('class','fal fa-spin fa-spinner').show();
    var listActionAssign    = [];
    var inputChecked        = $('input.input_action_role:checked');
    if( inputChecked.length > 0 ){
        for(var i = 0; i < inputChecked.length ; i++){
            listActionAssign.push(inputChecked[i].value);
        }
    }

    $.post(window.location.href, {dataAssign: listActionAssign, typeAssign:'role'}, function (r) {
        _this.find('i').attr('class','fal fa-check').show();
    }).always(function () {
        _this.find('i').attr('class','fal fa-check').show();
    });

});

var listActionAssign = $('input.input_action:checked');
console.log(listActionAssign.length);
if( listActionAssign.length > 0 ){
    for(var i = 0; i < listActionAssign.length ; i++){
        var id = listActionAssign[i].getAttribute('parentid');
        $('.' + id).removeClass('hide');
    }
}

// function search(target) {
//     var $list = $('select.list[data-target="' + target + '"]');
//     $list.html('');
//     var q = $('.search[data-target="' + target + '"]').val();

//     var groups = {
//         role: [$('<optgroup label="Roles">'), false],
//         permission: [$('<optgroup label="Permission">'), false],
//         route: [$('<optgroup label="Routes">'), false],
//     };
//     $.each(_opts.items[target], function (name, group) {
//         if (name.indexOf(q) >= 0) {
//             $('<option>').text(name).val(name).appendTo(groups[group][0]);
//             groups[group][1] = true;
//         }
//     });
//     $.each(groups, function () {
//         if (this[1]) {
//             $list.append(this[0]);
//         }
//     });
// }


// initial
// search('available');
// search('assigned');
