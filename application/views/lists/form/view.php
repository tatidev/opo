<div class='row'>

    <form id='frmList' class='col-12 p-0' action='<?php echo  site_url('lists/save_list') ?>' method='post'>

        <input type='hidden' name='lid' value='<?php echo  $lid ?>'>

        <div class='col-12 mb-4 py-2 bg-danger text-white text-center <?php echo  (!$isNew && $info['archived'] === 'Y' ? '' : 'hide') ?>'>
            <i class="fas fa-box-open"></i> List has been archived and cannot be
            modified.<br><?php if ($hasEditPermission) { ?> If you want to retrieve it, <u id='btnRetrieveList'>click
                here</u>.<?php } ?>
        </div>

        <div class='container my-4'>
            <div class='row'>

                <div class='col text-left'>
					<?php echo  $btnBack ?>
                </div>
                <div class='col text-right'>
                    <div class="dropdown d-inline">
                        <a class="btn btn-outline-danger no-border " href="#" role="button" id="dropdownMenuLink"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            List Options <i class="fas fa-caret-down"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="#" id='btnEmptyList'><i class="far fa-empty-set"></i> Empty
                                list</a>
                            <a class="dropdown-item" href="#" id='btnArchiveList'><i class="fas fa-archive"></i> Archive
                                list</a>
                        </div>
                    </div>
					<?php echo  ($hasEditPermission && $info['archived'] === 'N' ? "<a class='btn btn-info btnSave btnSaveList'><i class='far fa-square'></i> Save</a> " : '') ?>
					<?php echo  ($hasEditPermission && $info['archived'] === 'N' ? "<a class='btn btn-success btnSave btnSaveList' data-continue='1'><i class='far fa-square'></i> Save & Continue</a> " : '') ?>
                </div>

            </div>
        </div>

        <div class='container list-header'>

            <h4>
                List Information
            </h4>

            <div class='row'>

                <div class='col-xs-12 col-md-6'>
                    <div class='form-group row'>
                        <label for="list_name" class="col-3 col-form-label">List name</label>
                        <div class="col">
                            <input type="text" class="form-control" id="list_name" name='list_name'
                                   value='<?php echo  set_value('list_name', $info['name']) ?>' placeholder="List name">
                        </div>
                    </div>
                </div>

                <div class='col-xs-12 col-md-6'>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="active"
                               name="active" <?php echo  (!$isNew && $info['active'] === 'Y' ? 'checked' : '') ?>>
                        <label class="custom-control-label" for="active">Active for Showroom View <i
                                    class="fas fa-question-circle" data-toggle="tooltip"
                                    data-title="Enable/disable in the Sample Request App for the Showrooms"></i></label>
                    </div>
                </div>

                <div class='col-xs-12 col-md-6'>
                    <div class='form-group row'>

                        <label for="category" class="col-3 col-form-label">Category</label>
                        <div class="col">
							<?php echo  $dropdown_category ?>
                        </div>

                    </div>
                </div>

                <div class='col-xs-12 col-md-6'>
                    <div class='form-group row'>

                        <label for="showroom" class="col-3 col-form-label">Showrooms</label>
                        <div class="col">
							<?php echo  $dropdown_showroom ?>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </form>

    <div class='container list-content'>

        <nav class='hide'>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a class="nav-item nav-link active" id="nav-items-tab" data-toggle="tab" href="#nav-items" role="tab"
                   aria-controls="nav-items" aria-selected="true">
                    <h4>Invidividual Item</h4>
                </a>
                <a class="nav-item nav-link" id="nav-products-tab" data-toggle="tab" href="#nav-products" role="tab"
                   aria-controls="nav-products" aria-selected="false">
                    <h4>Product Lines</h4>
                </a>
                <!--<a class="nav-item nav-link hide" id="nav-files-tab" data-toggle="tab" href="#nav-files" role="tab" aria-controls="nav-files" aria-selected="false">
				  <h4>Files</h4>
				</a>
				-->
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-items" role="tabpanel" aria-labelledby="nav-items-tab">
                <div class="internal-loader-spin fa-3x mx-4">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <table id='items_table' style='opacity:0.2;width:100%'
                       class='row-border order-column hover compact'></table>
            </div>
            <div class="tab-pane fade" id="nav-products" role="tabpanel" aria-labelledby="nav-products-tab">
                <div class="internal-loader-spin fa-3x mx-4">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <table id='products_table' style='opacity:0.2;width:100%'
                       class='row-border order-column hover compact'></table>
            </div>
            <div class="tab-pane fade" id="nav-files" role="tabpanel" aria-labelledby="nav-files-tab">

            </div>
        </div>

    </div>

</div>


<!-- Shared Modal -->
<div class="modal fade" id="this_modal" style='z-index:9998;' tabindex="-1" role="dialog" aria-labelledby=""
     aria-hidden="true">
    <div class="modal-dialog" style='max-width:80%;' role="document">
        <div class="modal-content p-4">

            <div class="row">
                <div class="col-6">
                    <a class="btn btn-secondary btnClose float-left" data-dismiss="modal"><i
                                class="far fa-window-close"></i> Close</a>
                </div>
            </div>

            <h3 id='modal_title' class='mt-4'>
            </h3>

            <table id='modal_table_items' class='row-border order-column hover compact' width='100%' data-for='items'>
            </table>

            <table id='modal_table_lists' class='row-border order-column hover compact' width='100%' data-for='lists'>
            </table>

        </div>
    </div>
</div>

<style>

    .table-input-modif {
        border: none;
        background-color: transparent;
        resize: none;
        outline: none;
        width: 70%;
    }
</style>

<script>

    var this_item_table, this_product_table;
    var items_json = <?php echo json_encode($items);?>;

    
    

    var printUrl = '<?php echo $printUrl?>';
    var list_id = '<?php echo $lid?>';
    var hasEditPermission = '<?php echo $hasEditPermission?>' === '1';

    var table, datatable_items, datatable_lists;
    var modal_table_items = $('#modal_table_items');
    var modal_table_lists = $('#modal_table_lists');

    $(document).ready(function () {
        init_dropdowns();

        console.log("ITEMS: ", items_json);

        this_item_table = $("#items_table").DataTable({
            "dom": '<  <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f > <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" l> > <t> i p >',
            "data": items_json,
            //"createdRow": function ( row, data, index ) {},
            "rowId": 'item_id',
            "rowReorder": {
                'enable': true,
                'dataSrc': 'n_order',
                'selector': 'td:nth-child(1)',
                'snapX': 10
            },
            "language": {
                "searchPlaceholder": "Search items within the list",
            },
            "rowCallback": function (row, data) {
                let row_node = $(row);
                if (data.deleted === '1') {
                    row_node.addClass('row-deleted');
                } else {
                    row_node.removeClass('row-deleted');
                }
                switch (data.status_id) {
                    case '3':
                        // Discontinued
                        row_node.addClass('row-discontinued');
                        break;
                    case '18':
                        // MSO
                        row_node.addClass('row-mso');
                        break;
                    default:
                        row_node.removeClass('row-mso row-discontinued')
                        break;
                }
                if (data.in_ringset === '1') {
                    row_node.addClass('row-ringset');
                } else {
                    row_node.removeClass('row-ringset');
                }
            },
            "columns": [
                {'data': 'item_id', 'title': 'SKU', 'visible': false},
                {'data': 'n_order', 'title': 'Order', 'visible': true, 'className': 'reorder'},
                {
                    "data": "shelf",
                    "title": "Shelf",
                    "defaultContent": "",
                    "searchable": false,
                    "orderable": true,
                    "className": ''
                },
                {'data': 'status', 'title': 'Status', "className": "colorways-unique"},
                {'data': 'stock_status', 'title': 'Stock Status', "className": "colorways-unique"},
                {
                    "title": "",
                    "data": "btnInRingset",
                    "searchable": false,
                    "orderable": false,
                    "className": 'no-export noVis',
                    "render": function (data, type, row, meta) {
                        //console.log(row);
                        return "<i class='fab fa-gg-circle rs-icon  " + (row.in_ringset === '1' ? ' rs-active ' : '') + (hasEditPermission ? " btnToggleRingset " : '') + " ' data-id='" + row.item_id + "' " + (hasEditPermission ? "data-toggle='tooltip' data-title='Toggle ringset' data-placement='top'" : '') + "></i>";
                    }
                },
                {'data': 'code', 'title': 'Item #', "className": "colorways-unique"},
                {
                    'data': 'product_name', 'title': 'Product name',
                    'render': function (data, type, row, meta) {
                        return "<a href='#' class='btnEdit' data-toggle='tooltip' data-trigger='hover' data-title='Edit Product' data-placement='top' data-product_id='" + row.product_id + "' data-product_type='" + row.product_type + "'>" + row.product_name + "</a>";
                    }
                },
                {
                    'data': 'color', 'title': 'Color', "className": "colorways-unique",
                    'render': function (data, type, row, meta) {
                        return row.color;
                        //return "<a href='#' class='btnEditItem' data-toggle='tooltip' data-trigger='hover' data-title='Edit Item' data-placement='top' data-item-id='"+row.item_id+"' data-product_id='"+row.product_id+"-"+row.product_type+"'>"+row.color+"</a>";
                    }
                },
                {
                    'data': 'p_res_cut', 'title': 'Price',
                    "render": function (data, type, row, meta) {
                        const val = (row.list_p_res_cut == null ? row.p_res_cut : row.list_p_res_cut);
                        return "<span id='p_res_cut_"+row.item_id+"' data-row-id='"+row.item_id+"' data-type='list_p_res_cut' data-val='"+val+"' class='table-click-modif'>$ "+val+"</span>";
                        // return row.p_res_cut;
                    }
                },
                // {
                //     'data': 'p_hosp_cut', 'title': 'Hosp Cut/Yard',
                //     "render": function (data, type, row, meta) {
                //         const val = (row.list_p_hosp_cut == null ? row.p_hosp_cut : row.list_p_hosp_cut);
                //         return "<span id='p_hosp_cut"+row.item_id+"' data-row-id='"+row.item_id+"' data-type='list_p_hosp_cut' data-val='"+val+"' class='table-click-modif'>$ "+val+"</span>";
                //         // return row.p_hosp_cut;
                //     }
                // },
                {
                    'data': 'p_hosp_roll', 'title': 'Volume Price',
                    "render": function (data, type, row, meta) {
                        const val = (row.list_p_hosp_roll == null ? row.p_hosp_roll : row.list_p_hosp_roll);
                        return "<span id='p_hosp_roll"+row.item_id+"' data-row-id='"+row.item_id+"' data-type='list_p_hosp_roll' data-val='"+val+"' class='table-click-modif'>$ "+val+"</span>";
                        // return row.p_hosp_roll;
                    }
                },
                {
                    'data': 'width',
                    'title': 'Width',
                    'visible': false,
                    'orderable': false,
                    "searchable": false,
                    'defaultContent': ''
                },
                {
                    'data': 'content_front',
                    'title': 'Content',
                    'visible': false,
                    'orderable': false,
                    "searchable": false,
                    'defaultContent': ''
                },
                {'data': 'cost_cut', 'title': 'Cost Cut', 'visible': false, 'defaultContent': ''},
                {'data': 'cost_half_roll', 'title': 'Cost Half Roll', 'visible': false, 'defaultContent': ''},
                {'data': 'cost_roll', 'title': 'Cost Roll', 'visible': false, 'defaultContent': ''},
                {'data': 'cost_roll_landed', 'title': 'Cost Landed', 'visible': false, 'defaultContent': ''},
                {'data': 'cost_roll_ex_mill', 'title': 'Cost Exmill', 'visible': false, 'defaultContent': ''},
                {
                    "title": "In Stock", "data": "yardsInStock", "defaultContent": '-', "searchable": false, "visible": false,
                    "render": function (data, type, row, meta) {
                        var txt = '';
                        if (row.yardsInStock !== null && typeof (row.yardsInStock) !== 'undefined' /*&& row.yardsInStock !== '0.00'*/) {
                            txt += row.yardsInStock;
                        } else {
                            txt += '-';
                        }
                        if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
                            txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
                        }
                        return txt;
                    }
                },
                {
                    "title": "Available", "data": "yardsAvailable", "defaultContent": '-', "searchable": false, "visible": false,
                    "render": function (data, type, row, meta) {
                        if (row.yardsAvailable === null || typeof (row.yardsAvailable) === 'undefined' || row.yardsAvailable === '0.00') {
                            return '-';
                        } else {
                            return "<span class='text-success'>" + row.yardsAvailable + "</span>";
                        }
                    }
                },
                {
                    "title": "Web Visible", "data": "web_visible", "defaultContent": "-", "searchable": false,
                    "render": function (data, type, row, meta) {
                        var txt = "";
                        if (row.web_visible === 'Y') {
                            if (row.url_title !== '') {
                                txt = "<span class='hide'>Yes</span><a href='https://www.opuzen.com/product/" + row.url_title + "' target='_blank'><i class='far fa-eye'></i></a>";
                            } else if (stamps.digital_ground_ids.indexOf(row.item_id) >= 0) {
                                txt = "<span class='hide'>Yes</span><a href='https://opuzen.com/digital/grounds/view-all' target='_blank'><i class='far fa-eye'></i></a>";
                            }
                        }
                        return txt;
                    }
                },
                {
                    'title': 'Actions',
                    'className': 'no-export noVis text-center',
                    'orderable': false,
                    "searchable": false,
                    'defaultContent': '',
                    'render': function (data, type, row, meta) {
                        //console.log(row);
                        var txt = "<div class='d-flex justify-content-around'>";

                        var big_piece_status = typeof (row.big_piece) !== 'undefined' && row.big_piece === '1' ? 'big-piece-on' : 'big-piece-off';
                        txt += "<i class='fas fa-tag " + big_piece_status + " " + (hasEditPermission ? 'btnToggleListProp' : '') + " ' data-for='big_piece' data-id='" + row.item_id + "' data-toggle='tooltip' data-title='Big piece sent'></i>";

                        var active_status = typeof (row.active) === 'undefined' || row.active === '1' ? 'fa-toggle-on' : 'fa-toggle-off';
                        txt += "<i class='fa " + active_status + " " + (hasEditPermission ? 'btnToggleListProp' : '') + " ' data-for='active' data-id='" + row.item_id + "' data-toggle='tooltip' data-title='Active/Inactive in list'></i>";

                        if (row.deleted === '1') {
                            txt += " <i class='fas fa-undo " + (hasEditPermission ? 'btnToggleListProp' : '') + " ' data-for='deleted' data-id='" + row.item_id + "' data-toggle='tooltip' data-title='Retrieve to list'></i>";
                        } else {
                            txt += " <i class='fa fa-trash " + (hasEditPermission ? 'btnToggleListProp' : '') + " ' data-for='deleted' data-id='" + row.item_id + "' data-toggle='tooltip' data-title='Delete from list'></i>";
                        }

                        txt += "</div>";
                        return txt;
                    }
                }
            ],
            "columnDefs": [
                {"targets": [9, 10, 11], "width": "92px"}
            ],
            "buttons": [
                {
                    extend: 'collection',
                    text: '<i class="fas fa-plus-circle"></i> Add',
                    className: 'btn btn-outline-success no-border' + (!hasEditPermission ? ' hide ' : ''),
                    autoClose: true,
                    buttons: [
                        {
                            className: 'btn btn-outline-primary btnAddItems',
                            text: "Individual/whole products",
                            action: function (e, dt, node, config) {
                                btnAddItems();
                            }
                        },
                        {
                            className: 'btn btn-outline-primary btnAddLists',
                            text: "From other list",
                            action: function (e, dt, node, config) {
                                btnAddLists();
                            }
                        }
                    ]
                },
                custom_buttons.view([]),
                custom_buttons.export(
                    {
                        title: function () {
                            return $('#list_name').val();
                        },
                        extraButtons:
                            [
                                {
                                    extend: 'collection',
                                    text: '<i class="fas fa-print"></i> OZ Header <i class="fas fa-caret-down"></i>',
                                    className: '',
                                    autoClose: true,
                                    buttons: [
                                        {
                                            text: '<i class="fas fa-print"></i> by Products',
                                            action: function (e, dt, node, config) {
                                                window.open(printUrl + "/" + list_id + "/product", "_blank");
                                            }
                                        },
                                        {
                                            text: '<i class="fas fa-print"></i> by Colorways',
                                            action: function (e, dt, node, config) {
                                                window.open(printUrl + "/" + list_id + "/item", "_blank");
                                            }
                                        },
                                        {
                                            text: '<i class="fas fa-print"></i> by Order',
                                            action: function (e, dt, node, config) {
                                                window.open(printUrl + "/" + list_id + "/item/n_order", "_blank");
                                            }
                                        },
                                        // {
                                        //     text: '<i class="fas fa-print"></i> Specs Package (old)',
                                        //     action: function (e, dt, node, config) {
                                        //         window.open(printUrl + "/" + list_id + "/specs", "_blank");
                                        //     }
                                        // }
                                    ]
                                },
                                {
                                    extend: 'collection',
                                    text: '<i class="fas fa-print"></i> Digital Booklet <i class="fas fa-caret-down"></i>',
                                    className: '',
                                    autoClose: true,
                                    buttons: [
                                        {
                                            text: '<i class="fas fa-print"></i> by Product',
                                            action: function (e, dt, node, config) {
                                                window.open(printUrl + "/" + list_id + "/specs/n_order/1/Pr", "_blank");
                                            }
                                        },
                                        {
                                            text: '<i class="fas fa-print"></i> by Color',
                                            action: function (e, dt, node, config) {
                                                window.open(printUrl + "/" + list_id + "/specs/n_order/1/It", "_blank");
                                            }
                                        }
                                    ]
                                },
                                {
                                    text: '<i class="fas fa-print"></i> Stock Sheet',
                                    className: '',
                                    action: function (e, dt, node, config) {
                                        window.open(printUrl + "/" + list_id + "/stock", "_blank");
                                    }
                                }
                            ],
                        replacers: {
                            'excel':
                                {
                                    extend: 'collection',
                                    text: '<i class="far fa-file-excel"></i> Excel <i class="fas fa-caret-down"></i>',
                                    className: '',
                                    autoClose: true,
                                    buttons: [
                                        {
                                            extend: 'excel',
                                            text: '<i class="far fa-file-excel"></i> by Colorways',
                                            title: function () {
                                                return $('#list_name').val();
                                            },
                                            autoClose: true,
                                            messageTop: '',
                                            exportOptions: {
                                                columns: ':visible:not(.no-export)'
                                            }
                                        },
                                        {
                                            extend: 'excel',
                                            text: '<i class="far fa-file-excel"></i> by Products',
                                            title: function () {
                                                return $('#list_name').val();
                                            },
                                            autoClose: true,
                                            messageTop: '',
                                            customizeData: function (data) {
                                                // console.log(data.body);
                                                let unique_index = 2; // we will use the product name because we don't have the id here

                                                let unique_product = [];
                                                for (let i = data.body.length - 1; i >= 0; i--) {
                                                    if (unique_product.includes(data.body[i][unique_index])) {
                                                        data.body.splice(i, 1);
                                                        continue;
                                                    }
                                                    unique_product.push(data.body[i][unique_index]);
                                                }
                                                //console.log(unique_product); console.log(data.body);
                                            },
                                            exportOptions: {
                                                columns: ':visible:not(.no-export):not(.colorways-unique)'
                                            }
                                        }
                                    ]
                                }
                        }
                    }
                )
            ]
        });

        this_item_table.on('row-reorder', function (e, diff, edit) {
            //console.log(diff);
            //console.log(edit);
            var result = 'Reorder started on row: ' + edit.triggerRow.data()[1] + '<br>';
            for (var i = 0; i < diff.length; i++) {
                var rowData = this_item_table.row(diff[i].node).data();
                result += rowData[1] + ' updated to be in position ' + diff[i].newData + ' (was ' + diff[i].oldData + ')<br>';
            }
        });
        // Reinit
        this_item_table.search('')
            .columns().search('')
            .columns.adjust()
            .responsive.recalc()
            .draw();

        /*

		  Main Actions Controls

		*/

        $('#items_table_wrapper').on('click', '.btnToggleListProp', function () {
            //let this_tr = this_item_table.row( this ).data();
            let node = $(this).closest('td');
            toggleStatus(this_item_table, node, $(this).attr('data-for'));
        });


        function toggleStatus(target_table, node, forWhat) {
            let tr_data = target_table.row(node).data();
            if (typeof (tr_data[forWhat]) === 'undefined') {
                tr_data[forWhat] = '1';
            } else {
                tr_data[forWhat] = (tr_data[forWhat] === '1' ? '0' : '1');
            }
            init_tooltips('hide');
            change_in_form = true;
            target_table.row(node).data(tr_data).invalidate().draw(false);
        }

        /*
			Modals Controller
		*/

        function btnAddItems() {
            // Open Modal and initialize the datatables
            if (!$.fn.DataTable.isDataTable(modal_table_items)) {
                // Reinitialize
                datatable_items = modal_table_items
                    .on('preXhr.dt', function (e, settings, data) {
                        if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
                    })
                    .DataTable({
                        "dom": '< <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" l> > <t> i p >',
                        'serverSide': true,
                        "processing": true,
                        "rowId": 'item_id',
                        "ajax": {
                            "url": '<?php echo site_url('lists/preview_add_items')?>',
                            "type": "POST",
                            "dataSrc": "tableData",
                            "data": {
                                'list_id': list_id
                            }
                        },
                        "initComplete": function (settings, json) {
                            //console.log(settings);
                            myDefaultDatatablesInitComplete();
                            $(settings.nTable).dataTable().fnFilterOnReturn();
                        },
                        "language": {
                            "searchPlaceholder": "Search product name, color or item #",
                        },
                        "columns": [
                            {
                                "title": "Shelf",
                                "data": "shelf",
                                "defaultContent": "",
                                "searchable": false,
                                "orderable": true,
                                "className": ''
                            },
                            {"title": 'Status', "data": "status", 'searchable': false, "visible": true},
                            {"title": 'Stock Status', "data": "stock_status", 'searchable': false, "visible": true},
                            {
                                "title": "",
                                "data": "btnInRingset",
                                "searchable": false,
                                "orderable": false,
                                "className": 'no-export noVis',
                                "render": function (data, type, row, meta) {
                                    return "<i class='fab fa-gg-circle rs-icon  " + (row.in_ringset === '1' ? ' rs-active ' : '') + (hasEditPermission ? " btnToggleRingset " : '') + " ' data-id='" + row.item_id + "' " + (hasEditPermission ? "data-toggle='tooltip' data-title='Toggle ringset'" : '') + "></i>";
                                }
                            },
                            {"title": 'Item #', "data": "code", "visible": true},
                            {"title": 'Product name', "data": "product_name", "visible": true},
                            {"title": 'Color', "data": "color", "visible": true},
                            {
                                "title": 'Price', "data": "p_res_cut", 'searchable': false, "visible": true,
                                "render": function (data, type, row, meta) {
                                    return "$ " + row.p_res_cut;
                                }
                            },
                            // {
                            //     "title": 'Hosp Cut/Yard', "data": "p_hosp_cut", 'searchable': false, "visible": true,
                            //     "render": function (data, type, row, meta) {
                            //         return "$ " + row.p_hosp_cut;
                            //     }
                            // },
                            {
                                "title": 'Volume Price', "data": "p_hosp_roll", 'searchable': false, "visible": true,
                                "render": function (data, type, row, meta) {
                                    return "$ " + row.p_hosp_roll;
                                }
                            },
                            {
                                'title': 'Cost Cut',
                                'data': 'cost_cut',
                                'visible': false,
                                'searchable': false,
                                'defaultContent': ''
                            },
                            {
                                'title': 'Cost Half Roll',
                                'data': 'cost_half_roll',
                                'visible': false,
                                'searchable': false,
                                'defaultContent': ''
                            },
                            {
                                'title': 'Cost Roll',
                                'data': 'cost_roll',
                                'visible': false,
                                'searchable': false,
                                'defaultContent': ''
                            },
                            {
                                'title': 'Cost Landed',
                                'data': 'cost_roll_landed',
                                'visible': false,
                                'searchable': false,
                                'defaultContent': ''
                            },
                            {
                                'title': 'Cost Exmill',
                                'data': 'cost_roll_ex_mill',
                                'visible': false,
                                'searchable': false,
                                'defaultContent': ''
                            },
                            {
                                "title": 'Add Item',
                                "searchable": false,
                                "orderable": false,
                                "className": 'no-export noVis text-center',
                                "render": function (data, type, row, meta) {
                                    return "<i class='far fa-plus-square btnAddItemToList' data-item-type='" + row.product_type + "' data-item-id='" + row.item_id + "' data-toggle='tooltip' data-title='Add item to my list'></i>";
                                }
                            },
                            {
                                "title": 'Add Ringset',
                                "searchable": false,
                                "orderable": false,
                                "className": 'no-export noVis text-center',
                                "render": function (data, type, row, meta) {
                                    return "<i class='far fa-plus-square btnAddRingsetToList' data-item-type='" + row.product_type + "' data-product-id='" + row.product_id + "' data-toggle='tooltip' data-title='Add ringset to my list'></i>";
                                }
                            },
                            {
                                "title": 'Add Product',
                                "searchable": false,
                                "orderable": false,
                                "className": 'no-export noVis text-center hide',
                                "render": function (data, type, row, meta) {
                                    return "<i class='far fa-plus-square btnAddProductToList' data-product-type='" + row.product_type + "' data-product-id='" + row.product_id + "' data-toggle='tooltip' data-title='Add product line to my list'></i>";
                                }
                            }
                        ],
                        "buttons": [
                            custom_buttons.view()
                        ],
                        "order": [[4, "ASC"]]
                    });
                datatable_items.search('')
                    .columns().search('')
                    .columns.adjust()
                    .responsive.recalc()
                    .draw();
                $('#modal_title').html('Add Items to Current List');
            } else {
                //datatable_items.draw();
            }
            modal_table_items.parents('div.dataTables_wrapper').first().show();
            modal_table_lists.parents('div.dataTables_wrapper').first().hide();
            $('.modal#this_modal').modal('show');
            /*
			datatable_items
				.search('')
				.columns.adjust()
				.responsive.recalc()
				.draw();
				*/
        };

        function btnAddLists() {
            // Open Modal and initialize the datatables
            if (!$.fn.DataTable.isDataTable(modal_table_lists)) {
                // Reinitialize
                datatable_lists = modal_table_lists
                    .on('preXhr.dt', function (e, settings, data) {
                        if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
                    })
                    .DataTable({
                        "dom": '< <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" l> > <t> i p >',
                        'serverSide': true,
                        "processing": true,
                        "ajax": {
                            "url": '<?php echo site_url('lists/preview_add_lists')?>',
                            "type": "POST",
                            "dataSrc": "tableData",
                            "data": {
                                'list_id': list_id
                            }
                        },
                        "initComplete": function (settings, json) {
                            //console.log(settings);
                            myDefaultDatatablesInitComplete();
                            $(settings.nTable).dataTable().fnFilterOnReturn();
                        },
                        "language": {
                            "searchPlaceholder": "Search list name, category or showroom",
                        },
                        "columns": [
                            {"title": 'List', "data": "list_name"},
                            {"title": 'Category', "data": "category"},
                            {"title": 'Showrooms', "data": "showrooms"},
                            {
                                "title": 'Products (colors)', "data": "total_items",
                                "render": function (data, type, row, meta) {
                                    return (row.id !== '0' ? row.total_products + " (" + row.total_items + ")" : '');
                                }
                            },
                            {
                                "title": 'Action', "searchable": false, "orderable": false,
                                "render": function (data, type, row, meta) {
                                    return "<i class='far fa-plus-square btnAddListToList' data-list-id='" + row.id + "' data-toggle='tooltip' data-trigger='hover' data-title='Add items from this list' data-placement='top'></i> ";
                                }
                            }
                        ],
                        "buttons": []
                    });
                $('#modal_title').html('Add Items from an Existing List');
            } else {
                //datatable_lists.draw();
            }
            modal_table_lists.parents('div.dataTables_wrapper').first().show();
            modal_table_items.parents('div.dataTables_wrapper').first().hide();
            $('.modal#this_modal').modal('show');
            /*
			datatable_lists
				.search('')
				.columns.adjust()
				.responsive.recalc()
				.draw();
			*/
        };

        $('table#modal_table_items').on('click', 'i.btnAddItemToList', function () {
            let node = $(this).closest('td');
            var new_row = datatable_items.row(node).data();
            add_row_to_list_datatables('it', new_row);
            show_success_swal("Entered.");
        });

        $('table#modal_table_items').on('click', 'i.btnAddProductToList', function () {
            let node = $(this).closest('td');
            var new_row = datatable_items.row(node).data();
            add_row_to_list_datatables('pr', new_row);
            show_success_swal("Entered.");
        });

        $('table#modal_table_items').on('click', 'i.btnAddRingsetToList', function () {
            let node = $(this).closest('td');
            var new_row = datatable_items.row(node).data();
            $.ajax({
                method: "POST",
                url: '<?php echo site_url('lists/get_ringset')?>',
                dataType: 'json',
                data: {
                    product_id: new_row.product_id,
                    product_type: new_row.product_type
                    //,list_id: list_id
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                },
                beforeSend: function (data, msg) {
                    $('.full-loader').removeClass('hide');
                },
                success: function (data, msg) {
                    $('.full-loader').addClass('hide');
                    if (data.success == true) {
                        // Add to original table
                        $.each(data.items, function (index, value) {
                            //console.log(value);
                            add_row_to_list_datatables('it', value);
                        });
                        show_success_swal("Entered.");
                    } else {
                        console.log("Some error ocurred");
                    }
                }
            });
        });

        $('table#modal_table_lists').on('click', 'i.btnAddListToList', function () {
            var from_list_id = $(this).attr('data-list-id');
            var tr_childrens = $(this).parents('tr').children(); // .remove().draw();
            $.ajax({
                method: "POST",
                url: '<?php echo site_url('lists/add_list_to_list')?>',
                dataType: 'json',
                data: {
                    from_list_id: from_list_id,
                    to_list_id: list_id
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                },
                beforeSend: function (data, msg) {
                    $('.full-loader').removeClass('hide');
                },
                success: function (data, msg) {
                    $('.full-loader').addClass('hide');
                    if (data.success == true) {
                        // Add to original table
                        $.each(data.item, function (index, value) {
                            //console.log(value);
                            add_row_to_list_datatables('it', value);
                        });
                        /*
						$.each(data.product, function(index, value){
						  //console.log(value);
						  add_row_to_list_datatables( 'pr', value );
						});
						*/
                        show_success_swal();
                    } else {
                        console.log("Some error ocurred");
                    }
                }
            });
        });

        function add_row_to_list_datatables(target_table, cols) {
            let isNew = true;
            change_in_form = true;
            switch (target_table) {
                case 'it':
                    target_table = this_item_table;
                    cols.active = '1';
                    cols.big_piece = '0';
                    isNew = typeof (target_table.row('#' + cols.item_id).data()) === 'undefined';

                    if (isNew) {
                        cols.n_order = target_table.data().count() + 1;
                        target_table.row.add(cols).draw(false); // no page, sorting update
                        //mtable.rows().invalidate();
                    } else {
                        // Update price (or other info) for a specific item
                    }

                    break;
                /*
				case 'pr':
				  Not being used yet
				  target_table = this_product_table;
				  let arr = target_table.rows().data();
				  for( let i = 0; i < arr.length; i++ ){
					if( arr[i].product_id === cols.product_id && arr[i].product_type === cols.product_type ){
					  isNew = false;
					  break;
					}
				  }
				  break;
				*/
            }

        }

        /*

		  SUBMITTING LIST

		*/

        $('form#frmList').off('click').on('click', '.btnSaveList', function () {
            const toContinue = $(this).attr('data-continue') === '1';
            const dt_rows = this_item_table.order([1, 'asc']).draw().rows().data().toArray();
            const coll = [];

            // Collect Items
            for (let i = 0; i < dt_rows.length; i++) {
                const p = {
                    item_id: dt_rows[i].item_id,
                    n_order: dt_rows[i].n_order,
                    active: dt_rows[i].active,
                    big_piece: dt_rows[i].big_piece,
                    deleted: dt_rows[i].deleted,
                    user_id: user_id
                };
                if(dt_rows[i].list_p_res_cut != null){
                    p.p_res_cut = dt_rows[i].list_p_res_cut;
                }
                // if(dt_rows[i].list_p_hosp_cut != null){
                //     p.p_hosp_cut = dt_rows[i].list_p_hosp_cut;
                // }
                if(dt_rows[i].list_p_hosp_roll != null){
                    p.p_hosp_roll = dt_rows[i].list_p_hosp_roll;
                }
                coll.push(p);
            }
            $("input#items").remove();
            $('<input>').attr({
                id: 'items',
                type: 'hidden',
                name: 'items',
                value: JSON.stringify(coll)
            }).appendTo('#frmList');

            $.ajax({
                method: "POST",
                url: $('#frmList').attr('action'),
                dataType: 'json',
                data: $('#frmList').serialize(),
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                },
                success: function (data, msg) {
                    //console.log(data);
                    if (data.status == 'OK') {
                        change_in_form = false;
                        if (toContinue) {
                            get_ajax_view(data.continueUrl);
                        } else {
                            show_success_swal('Saved.');
                        }
                    } else {
                        show_alert(data.message);
                    }
                }
            });
        })

        $('form#frmList').on('click', '#btnRetrieveList', function () {
            show_swal(
                {},
                {
                    title: 'Are you sure you want to retrieve the list?'
                },
                {
                    complete: function () {
                        $.ajax({
                            method: "POST",
                            url: '<?php echo site_url('lists/retrieve_list')?>',
                            dataType: 'json',
                            data: {
                                list_id: list_id
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.log(errorThrown);
                            },
                            success: function (data, msg) {
                                //console.log(data);
                                if (data.success === true) {
                                    show_success_swal();
                                    get_ajax_view(data.continueUrl);
                                } else {
                                    // Some error ocurred
                                    show_success_swal(data.message, 'error');
                                }

                            }
                        });
                    }
                }
            );
        });

        $('form#frmList').on('click', '#btnArchiveList', function () {
            show_swal(
                {},
                {
                    title: 'Are you sure you want to archive the list?'
                },
                {
                    complete: function () {
                        $.ajax({
                            method: "POST",
                            url: '<?php echo site_url('lists/archive_list')?>',
                            dataType: 'json',
                            data: {
                                list_id: list_id
                            },
                            success: function (data, msg) {
                                //console.log(data);
                                if (data.success === true) {
                                    show_success_swal();
                                    get_ajax_view(data.continueUrl);
                                } else {
                                    // Some error ocurred
                                    show_success_swal(data.message, 'error');
                                }

                            }
                        });
                    }
                }
            );
        });

        $('form#frmList').on('click', '#btnEmptyList', function () {
            show_swal(
                {},
                {
                    title: 'Are you sure you want to empty the list?'
                },
                {
                    complete: function () {
                        this_item_table.rows().every(function (rowIdx, tableLoop, rowLoop) {
                            var data = this.data();
                            data.deleted = '1';
                            this.invalidate().draw(false);
                        });
                    }
                }
            );
        });

        $("table#items_table tbody").on('click', "span.table-click-modif", function() {
            const row_id = $(this).attr('data-row-id');
            const type = $(this).attr('data-type');
            const value = $(this).attr('data-val');
            // console.log(row_id, type, value);
            const input_id = "in_"+type+"_"+row_id;
            const new_field = $('<input>').attr({
                class: "table-input-modif",
                type: 'number',
                id: input_id,
                value: value,
            }).focusout(() => tweak_list_price(input_id, this));
            $(this).replaceWith(new_field);
            new_field.focus();
        });

        function tweak_list_price(input_id, obj){
            const row_id = $(obj).attr('data-row-id');
            const type = $(obj).attr('data-type');
            const value = $("input#"+input_id).val() === '' ? null : parseFloat($("input#"+input_id).val());
            console.log(row_id, type, value);
            data = {item_id: row_id};
            data[type] = value;
            update_item_in_view(data, this_item_table);
        }


        // $(".table-input-modif").on('change', function(){
        //     const row_id = $(this).attr('data-row-id');
        //     const type = $(this).attr('data-type');
        //     const value = $(this).val();
        //     data = {item_id: row_id}
        //     data[type] = value;
        //     console.log(data);
        //     update_item_in_view(data, this_item_table);
        // });
    });


</script>