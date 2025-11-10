var change_in_form = false; // This is toggled after any change in an input/multiselect field in made, and set to false every time a new page is rendered
var history_array = [];

function get_stamp_html(type) {
    switch (type) {
        case '30U':
            return " <span class='is_30under' data-toggle='tooltip'data-title='Value Collection'>VC</span>";
        case 'DG':
            return " <span class='is_digitalground' data-toggle='tooltip' data-title='Digital Ground'>DG</span>";
        case 'FS':
            return " <span class='is_fabricseen' data-toggle='tooltip' data-title='Closeout'>CO</span>";
        case 'MPL':
            return " <span class='is_mpl' data-toggle='tooltip' data-title='In Master Price List'>MPL</span>";
        default:
            return '';
    }
}

/*
  Ajax views controllers
*/

// Only add new state to the History
// DOES NOT UPDATED FRONTEND VIEW
// Used when a different stage of Datatables is accomplished
function add_history_state(obj) {
    console.log("add_history_state(obj) : " + url);
    var url = obj.url;
    var data = obj.data;
    history_array.push(History.getState());
    History.replaceState(data, document.title, url);
}
//  obj =  { url: 'editListUrl',  lid: <lid> }
function get_ajax_view(given_url = null, given_data = {}) {
    var ask_before_leave = [
        'product/edit',
        'product/render_form',
        'lists/edit'
    ];

    console.log("function get_ajax_view(given_url, given_data)", given_url, given_data);


    var this_stage = History.getState().cleanUrl.replace(window.location.origin + (ENVIRONMENT === 'development' ? '/dev_pms/' : '/pms/'), '');
    //console.log(change_in_form);
    //console.log(this_stage);
    var need_to_ask = (change_in_form && $.inArray(this_stage, ask_before_leave) >= 0);
    var obj = {url: given_url, data: given_data};
    if (need_to_ask) {
        show_swal(
            obj,
            {
                title: "Are you sure you want to continue?",
                text: "Be aware that any change made won't be saved.",
                icon: 'info'
            },
            {
                complete: function (obj) {
                    load_content_view(obj);
                }
            }
        );
    } else {
        load_content_view(obj);
    }
}

function load_content_view(obj) {
    var url = obj.url;
    var data = obj.data;
    var proceed = true;
    console.log("load_content_view(obj) : ", obj);
    console.log("load_content_view(obj) obj : " + JSON.stringify(data));

    if (url === null) {
        // Go Back
        if (history_array.length === 0) {
            url = window.location.origin + '';
            data = {};
        } else {
            var hist_state = history_array.pop();
            url = hist_state.cleanUrl;
            data = hist_state.data;
        }
        History.replaceState(data, document.title, url);
        //console.log("Go back to: " + url);
    } else {
        // New view requested
        // Check if requested URL is the same to the current one
        if (!(
            History.getState().cleanUrl === obj.url &&
            JSON.stringify(History.getState().data) === JSON.stringify(obj.data)
        )
        ) {
            history_array.push(History.getState());
            History.replaceState(data, document.title, url);
        } else {
            // Same direction asked two times
            proceed = false;
        }
    }

    if (proceed) {
        console.log("load_content_view(obj) proceed URL : " + url);
        console.log("load_content_view(obj) proceed obj : " + JSON.stringify(data));
        const preAjaxData = JSON.stringify(data);
        console.log("===> pkl ajax preAjaxData", preAjaxData);
        $.ajax({
            method: "POST",
            url: url,
            dataType: 'json',
            data: data,
            cache: true,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            success: function (data, msg) {
                console.log("pkl ajax result", data);
                console.log("pkl ajax msg", msg);

                if (data.logged === false) {
                    console.log("About to window.location.reload...");
                    window.location.reload(true);
                } else {
                    show_alert(false);
                    change_in_form = false;
                    $(".tooltip").tooltip("hide");
                    $('#nav-content').html(data.html);
                }
            }
        });
    }
}

$(document).on('click', '.btnEdit', function () {
    var formData = {
        'product_id': $(this).attr('data-product_id'),
        'product_type': $(this).attr('data-product_type')
    };
    get_ajax_view(editProductUrl+'/'+formData.product_type+'/'+formData.product_id, formData);``
})

$(document).on('click', '.btnColorline', function () {
    var formData = {
        'product_id': $(this).attr('data-product_id'),
        'product_type': $(this).attr('data-product_type')
    };
    get_ajax_view(viewColorlinesUrl, formData);
})

$(document).on('click', '.btnEditList', function () {
    var formData = {
        'lid': $(this).attr('data-lid')
    };
    get_ajax_view(editListUrl, formData);
});

$(document).on('click', '.btnEditItem', function () {
    
    var item_id = $(this).attr('data-item-id');
    var product_type = $(this).attr('data-product_type');
    var item_product_id = $(this).attr('data-product_id');

    console.log("commin.js -- .btnEditItem", item_id, product_type, item_product_id);

    // Use the item's specific product_id, not the global one
    open_item_modal(item_id, false, product_type, item_product_id, 'common.js 166');
});

$(document).on('click', '.btnEditSpec', function (e) {
    open_spec_modal($(this).attr('data-spec-id'));
});


function open_item_modal(item_id = '0', isMulti = false, product_type = null, item_product_id = null, refer='') {
    var url = (isMulti ? multiEditItemModalUrl : editItemModalUrl);
    //console.trace("Tracing function open_item_modal()");
    
    // Use item_product_id if provided, otherwise fall back to global product_id
    var use_product_id = item_product_id !== null ? item_product_id : product_id;
    
    console.log("common.js open_item_modal(from: "+ refer +") :: isMulti: "+isMulti, [use_product_id, product_type, item_id]);

    // Validate product_type - never allow 'item_id' as product_type
    if (product_type === 'item_id' || product_type === 'it' || !product_type) {
        product_type = 'R'; // Default to Regular
    }

    // if (product_type.indexOf('item_id') >= 0 || (product_type === '0')) {
    //     swal('Go to the fabric full colorline to add or edit an item.');
    //     return;
    // }

    //console.log("AJAX DATA:", { 'URL': url,
    //    'product_type': product_type,
    //    'product_id': use_product_id,
    //    'item_id': item_id
    //});

    $.ajax({
        method: "POST",
        url: url,
        dataType: 'json',
        data: {
            'product_type': product_type,
            'product_id': use_product_id,
            'item_id': item_id
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        success: function (data, msg) {
            $('.modal#globalmodal').children().find('.modal-content').html(data.html);
            $('.modal#globalmodal').modal('show');
            init_dropdowns('.modal#globalmodal');
        }
    });
}

// Ringset manipulation

$(document).on('click', 'i.btnToggleRingset', function () {
    let node = $(this).closest('td');
    let target_table = $($(this).parents('table')).DataTable();
    let tr_data = target_table.row(node).data();
    toggleRingset({
        batch: [
            {
                item_id: tr_data.item_id,
                in_ringset: tr_data.in_ringset !== '1'
            }
        ]
    }, target_table);
});

function toggleRingset(given_data, target_table) {
    var defaults = {
        batch: []
    };
    $.ajax({
        method: "POST",
        url: toggleRingsetUrl,
        dataType: 'json',
        data: $.extend({}, defaults, given_data),
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        success: function (data, msg) {
            $.each(data.items, function (index, item) {
                //console.log( typeof(item.active) );
                toggleRingsetView(item, target_table);
            });
            show_success_swal();
        }
    });
}

function toggleRingsetView(item, target_table) {
    let tr_data = target_table.row("#" + item.item_id).data();
    if (typeof (tr_data.in_ringset) === 'undefined') {
        tr_data.in_ringset = '1';
    } else {
        tr_data.in_ringset = (item.in_ringset === 'true' ? '1' : '0');
    }
    init_tooltips('hide');
    change_in_form = true;
    target_table.row("#" + item.item_id).data(tr_data).invalidate().draw(false);
}

// Exportable toggle manipulation
$(document).on('change', 'input.btnToggleExportable', function () {
    let checkbox = $(this);
    let node = checkbox.closest('td');
    let target_table = $(checkbox.parents('table')).DataTable();
    let tr_data = target_table.row(node).data();
    let newValue = checkbox.is(':checked');
    
    toggleExportable({
        batch: [
            {
                item_id: tr_data.item_id,
                exportable: newValue
            }
        ]
    }, target_table, checkbox);
});

function toggleExportable(given_data, target_table, checkbox) {
    var defaults = {
        batch: []
    };
    $.ajax({
        method: "POST",
        url: toggleExportableUrl,
        dataType: 'json',
        data: $.extend({}, defaults, given_data),
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
            // Revert checkbox on error
            if (checkbox) {
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        },
        success: function (data, msg) {
            $.each(data.items, function (index, item) {
                toggleExportableView(item, target_table);
            });
            show_success_swal();
        }
    });
}

function toggleExportableView(item, target_table) {
    let tr_data = target_table.row("#" + item.item_id).data();
    if (typeof (tr_data.exportable) === 'undefined') {
        tr_data.exportable = '1';
    } else {
        tr_data.exportable = (item.exportable === 'true' ? '1' : '0');
    }
    change_in_form = true;
    target_table.row("#" + item.item_id).data(tr_data).invalidate().draw(false);
}

function update_item_in_view(item, datatable) {

    var isNew = datatable.row('#' + item.item_id).length === 0;
    if (isNew) {
        item.in_master = 0;
        item.in_ringset = 0;
        item.item_id = item.item_id.toString();
        datatable.row.add(item).invalidate().draw(false);
    } else if (typeof (item.archived) !== 'undefined' && item.archived === 'Y') {
        remove_item_from_view(item.item_id, datatable);
    } else {
        const tr_data = datatable.row('#' + item.item_id).data();
        console.log(tr_data);
        console.log(item);
        // if (typeof (item.status_id) !== 'undefined') {
        //     tr_data.status_id = item.status_id;
        //     tr_data.status = item.status;
        //     tr_data.status_abrev = item.status_abrev;
        // }
        // if (typeof (item.stock_status_id) !== 'undefined') {
        //     tr_data.stock_status_id = item.stock_status_id;
        //     tr_data.stock_status = item.stock_status;
        //     tr_data.stock_status_abrev = item.stock_status_abrev;
        // }
        // if (typeof (item.roll_location) !== 'undefined') tr_data.roll_location = item.roll_location;
        // if (typeof (item.bin_location) !== 'undefined') tr_data.bin_location = item.bin_location;
        // if (typeof (item.roll_yardage) !== 'undefined') tr_data.roll_yardage = item.roll_yardage;
        // if (typeof (item.bin_quantity) !== 'undefined') tr_data.bin_quantity = item.bin_quantity;
        // if (typeof (item.code) !== 'undefined') tr_data.code = item.code;
        // if (typeof (item.color) !== 'undefined') tr_data.color = item.color;
        // if (typeof (item.shelf) !== 'undefined') tr_data.shelf = item.shelf;
        // if (typeof (item.web_visible) !== 'undefined') tr_data.web_visible = item.web_visible;
        // if (typeof (item.pic_big) !== 'undefined') tr_data.pic_big = item.pic_big;
        // if (typeof (item.pic_hd) !== 'undefined') tr_data.pic_hd = item.pic_hd;
        // if (typeof (item.in_master) !== 'undefined') tr_data.in_master = item.in_master;

        datatable.row('#' + item.item_id).data({...tr_data, ...item}).invalidate().draw(false);
        // datatable.row('#'+item.item_id).data(tr_data).draw();
    }
}

function remove_item_from_view(item_id, datatable = this_table) {
    datatable.row("#" + item_id).remove().draw();
}

$(document).on('click', '.btnBack', function () {
    get_ajax_view();
})

$(document).on('click', '.menu-item:not(.noajax), .menu-item-sub:not(.noajax)', function () {
    //console.log( $(this).attr('data-href') );
    var dir = $(this).attr('data-href');
    if (typeof (dir) !== 'undefined' && dir !== '') {
        style_menu($(this));
        get_ajax_view(dir);
    }

});

function style_menu(target) {
    var selectedClass = "menu-selected-perm";
    $("a.menu-item." + selectedClass).removeClass(selectedClass);
    if (target.hasClass('menu-item')) {
        target.addClass(selectedClass);
    } else if (target.hasClass('menu-item-sub')) {
        //console.log($(this).parent().parent().siblings("a.menu-item"));
        target.parent().parent().siblings("a.menu-item").addClass(selectedClass);
    }
}


/*
  Some events
*/

var global_modal_id = ".modal#globalmodal";

function open_global_modal(html) {
    $(global_modal_id).children().find('.modal-content').html(html);
    $(global_modal_id).modal('show');
}

$(global_modal_id).on('hidden.bs.modal', function (e) {
    $('#globalmodal > .modal-dialog').css('max-width', '1000px');
})

$(document).on('click', '.btnCloseAlert', function () {
    $(this).parent().parent().toggleClass('hide');
    //$('#error-alert').addClass('hide');
});

$(document).on('mouseover', '.btnSave, .btnSaveItem', function () {
    var i = $(this).children('i.far');
    i.removeClass('far fa-square').addClass('far fa-check-square');
});

$(document).on('mouseleave', '.btnSave, .btnSaveItem', function () {
    var i = $(this).children('i.far');
    i.removeClass('far fa-check-square').addClass('far fa-square');
});

function numberWithCommas(x){
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

function show_alert(msg, target = {
    wrap: '#error-alert',
    container: '#error-msg'
}) {
    if (msg === false) {
        $(target.wrap).addClass('hide');
        $(target.container).html('');
    } else {
        $(target.container).html(msg);
        $(target.wrap).removeClass('hide');
        window.scrollTo(0, 0);
    }
}

function hide_alert(){
    $("#error-alert").addClass("hide");
}

/*

	Popups for
	- Delete confirmation
	- Message when moving from a 'Saving Stage' after a change is made
	- Show any sort of alert
	
*/


function deleteRow(row) {
    var this_row = $(row);
    show_swal(
        this_row,
        {},
        {
            complete: function (this_row) {
                var total = parseFloat($('#perc_total').html());
                //console.log( $(this).parent().parent().attr('data-perc') );
                $('#perc_total').html(total - parseFloat(this_row.parent().parent().attr('data-perc')));
                this_row.parent().parent().remove()
            }
        }
    );

}

// Frontend Notifications

function show_success_swal(params = 'Saved.', type = 'success') {
    // More options on https://notifyjs.jpillora.com/
    if (typeof (params) === 'string') {
        $.notify(params, type);
    } else if (typeof (params) === 'object') {
        $.notify(params);
    }

}

function show_swal(target, init = {}, act = {}) {
    var def_init = {
        title: "Are you sure you want to delete?",
        text: '',
        icon: "warning",
        buttons: true,
        dangerMode: true,
    };
    var def_act = {
        complete: function () {
        },
        abort: function () {
        }
    };

    var settings = {
        init: $.extend({}, def_init, init),
        act: $.extend({}, def_act, act)
    };

    swal(settings.init)
        .then((willDelete) => {
            if (willDelete) {
                settings.act.complete(target)
                /*
                swal("Poof! Your imaginary file has been deleted!", {
                    icon: "success",
                });
                */
            } else {
                settings.act.abort();
                //swal("Your imaginary file is safe!");
            }
        });
}

var DateDiff = {

    inDays: function(d1, d2) {
        var t2 = d2.getTime();
        var t1 = d1.getTime();

        return Math.floor((t2-t1)/(24*3600*1000));
    },

    inWeeks: function(d1, d2) {
        var t2 = d2.getTime();
        var t1 = d1.getTime();

        return parseInt((t2-t1)/(24*3600*1000*7));
    },

    inMonths: function(d1, d2) {
        var d1Y = d1.getFullYear();
        var d2Y = d2.getFullYear();
        var d1M = d1.getMonth();
        var d2M = d2.getMonth();

        return (d2M+12*d2Y)-(d1M+12*d1Y);
    },

    inYears: function(d1, d2) {
        return d2.getFullYear()-d1.getFullYear();
    }
}