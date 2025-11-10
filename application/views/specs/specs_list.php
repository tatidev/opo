<style>
    .reordered {
        background-color: #fcf5b2 !important;
    }
</style>
<div class='my-4'>
    <div class='input-group col-12 px-0'
         style='border-bottom: 1px dotted #bfac02; box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;'>
        <div class="input-group-prepend">
            <span class="input-group-text" style='background-color: transparent; border: none; font-size: 30px;'><i
                        class="fas fa-search"></i></span>
        </div>
        <input id="input_search" type="text" placeholder="Search" class="form-control input_search" value="">
        <div class="input-group-prepend">
            <span class="input-group-text" style='background-color: transparent; border: none; font-size: 30px;'><i
                        class="fas fa-arrow-right"></i></span>
        </div>
        <div class='col-3 p-0'><?php echo  $select_editing ?></div>
    </div>
</div>

<table id='spec_table' class='row-border order-column hover compact' width='100%'></table>

<script>
    $(document).ready(function(){
        instantiate_spec_datatables();
        init_dropdowns();
    })
    var table = null;
    var mtable_id = "#spec_table";
    var this_table;
    var spec_name = '0';

    function instantiate_spec_datatables(params={}) {
        let _default_params = {
            'rowReorder': {
                'enable': false,
            }
        }
        params = Object.assign({}, _default_params, params);
        // console.log(params);
        if(table != null){
            table.destroy();
        }
        table = $(mtable_id).DataTable({
            dom: '< <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <t> i p >',
            "ajax": {
                "url": "<?php echo site_url('specs/get_table_data')?>",
                "type": "POST",
                // Set the data to send to Ajax
                "data": function (d) {
                    d.spec_name = spec_name;
                    return d;
                },
                // Retrieve the data after completion
                "dataSrc": function (json) {
                    return json.tableData;
                }
            },
            "rowReorder": params['rowReorder'],
            "stateSave": false,
            "search": {
                "search": ''
            },
            "pageLength": 50,
            "columns": [
                {
                    "orderable": false, 'searchable': false,
                    "render": function (data, type, row, meta) {
                        if (row.id == 0) {
                            return '';
                        } else {
                            return "<i class='fas fa-pen-square btn-action btnEditSpec' aria-hidden='true' data-spec-name='" + spec_name + "' data-spec-id='" + row.id + "'></i>";
                        }
                    }
                },
                {"data": "n_order", "title": (params['rowReorder']['enable'] ? "Order" : ''), "defaultContent": '', 'searchable': false, 'class': (params['rowReorder']['enable'] ? "td_reorder" : ''), 'orderable': params['rowReorder']['enable']},
                {"data": "name", "title": "Name"},
                {"data": "descr", "title": "Description", "defaultContent": ''},
                {
                    "data": "active", "title": "Active", "defaultContent": '',
                    "render": function (data, type, row, meta) {
                        return (row.active === 'Y' ? 'Yes' : 'No');
                    }
                },
                {"data": "relations", "title": "# of Relations", "defaultContent": '0'}
            ],
            "order": (params['order'] ? params['order'] : [2, "asc"]),
            "buttons": [
                custom_buttons.back(),
                custom_buttons.new(open_spec_modal),
                custom_buttons.view([0]),
                custom_buttons.export(
                    {
                        title: function () {
                            return $("[name='select_editing']").children("option[value='" + $("[name='select_editing']").val() + "']").html();
                        }
                    }
                ),
                {
                    text: '<i class="far fa-check-square"></i> Save Reorder',
                    className: 'btn btn-outline-danger no-border ' + ((typeof (hasEditPermission) !== 'undefined' && !hasEditPermission) || !params['rowReorder']['enable'] ? ' hide ' : ''),
                    action: function (e, dt, node, config) {
                        save_reordering();
                    }
                }
            ]
        });
        this_table = table;

        this_table.off('row-reordered').on('row-reordered', function ( e, diff, edit ) {
            for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
                $(diff[i].node).addClass("reordered");
            }
            console.log("Change event end.")
        } );
    }

    $("#input_search").on('focus', function () {
        $(this).select();
    })

    $('#input_search').keyup(function () {
        var me = $(this);
        delay(function () {
            //var obj = JSON.parse( localStorage.getItem('products_view') );
            //localStorage.setItem('products_view', JSON.stringify( { search: me.val(), view: obj.view} ) );
            this_table.search(me.val()).draw()
        }, 700);
    })

    var delay = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();


    $("[name='select_editing']").on('change', function (e) {
        // Update view table so that we can select which spec to modify
        spec_name = $(this).val();
        let params = {
            'rowReorder': {
                'enable': false,
            }
        }
        if(spec_name == 'product_checklist'){
            params = {
                'rowReorder': {
                    'enable': true,
                    'dataSrc': 'n_order',
                    'selector': 'td:nth-child(2)',
                    'snapX': 10
                },
                'order': [1, 'asc']
            }
        }
        instantiate_spec_datatables(params);
        // table.search('');
        // table.ajax.reload();
    });

    function open_spec_modal(id = 0) {
        $.ajax({
            method: "POST",
            url: "<?php echo site_url('specs/edit_spec')?>",
            dataType: 'json',
            data: {
                spec_name: spec_name,
                spec_id: id
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            success: function (data, msg) {
                $('.modal#globalmodal').children().find('.modal-content').html(data.html);
                $('.modal#globalmodal').modal('show');
                init_dropdowns();
            }
        });
    }

    function add_row_to_view(row) {

        var isNew = true;
        var rows = $(mtable_id).DataTable().rows().data();
        $.each(rows, function (index, value) {
            if (value.id === row.id.toString()) {
                // Existing item, modify!
                isNew = false;
                value.name = row.name;
                value.active = row.active;
                value.descr = (row.descr === undefined ? '' : row.descr);
            }
        });

        if (isNew) {
            console.log('new item');
            $(mtable_id).DataTable().row.add(row).draw();
        }
        $(mtable_id).DataTable().rows().invalidate();
    }

    function save_reordering(){
        let new_reorder = [];
        let rows = this_table.rows('.reordered').data().toArray();
        let attrs = ['id', 'n_order'];
        for(let i = 0; i < rows.length; i++){
            let d = {};
            for(let j = 0; j < attrs.length; j++){
                d[attrs[j]] = rows[i][attrs[j]];
            }
            new_reorder.push(d);
        }
        $.ajax({
            method: "POST",
            url: "<?php echo site_url('specs/save_spec_reorder')?>",
            dataType: 'json',
            data: {
                'spec_name': spec_name,
                'new_reorder': new_reorder
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            success: function (data, msg) {
                $(".reordered").toggleClass("reordered")
            }
        });
    }

</script>