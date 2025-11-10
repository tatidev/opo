<div class='my-4'>
    <div class='input-group col-12 px-0'
         style='border-bottom: 1px dotted #bfac02; box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;'>
        <div class="input-group-prepend">
            <span class="input-group-text" style='background-color: transparent; border: none; font-size: 30px;'><i
                        class="fas fa-search"></i></span>
        </div>
        <input id="input_search" type="text" placeholder="Search product name, color or item #"
               class="form-control input_search" value="<?php echo $pname ?>">
    </div>
    <div class='col-12 d-inline-flex justify-content-end mt-2 hide'>
        <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="includeRegular" checked>
            <label class="custom-control-label" for="includeRegular">Fabrics</label>
        </div>
        <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="includeDigital">
            <label class="custom-control-label" for="includeDigital">Digitals</label>
        </div>
        <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="includeSKU" checked>
            <label class="custom-control-label" for="includeSKU">SKUs</label>
        </div>

    </div>
</div>

<div class="internal-loader-spin fa-3x mx-4">
    <i class="fas fa-circle-notch fa-spin"></i>
</div>
<table id='items_table' style='opacity:0.2;' class='row-border hover compact' width='100%'></table>

<?php if ($hasEditPermission) { ?>
    <!-- Modal for Lists Preview! -->
    <div class="modal fade" id="list_modal" style='z-index:9999;' tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style='max-width:900px;' role="document">
            <div class="modal-content" id='modal-content'>
                <div class='p-4'>

                    <div class="row">
                        <div class="col-6">
                            <a class="btn btn-secondary btnClose float-left" data-dismiss="modal"><i
                                        class="far fa-window-close"></i> Close</a>
                        </div>
                    </div>
                    <h3 class="mt-4">
                        Add Selected Items to List
                    </h3>

                    <table id='list_preview' class='row-border order-column hover compact' width='100%'>
                        <thead>
                            <th></th>
                            <td>Name</td>
                            <td>Category</td>
                            <td>Items</td>
                            <td>Date</td>
                        </thead>
                    </table>

                </div>

            </div>
        </div>
    </div>

    <!-- Modal for Restocking -->
    <div class="modal fade" id="restock_modal" style='z-index:9999;' tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style='max-width:1200px;' role="document">
            <div class="modal-content" id='modal-content'>
                <div class='p-4'>

                    <div class="row">
                        <div class="col-6">
                            <a class="btn btn-secondary btnClose float-left" data-dismiss="modal"><i class="far fa-window-close"></i> Close</a>
                        </div>
                        <div class="col-6">
                            <a class="btn btn-success float-right btnSubmitRestock">Save <i class="far fa-square"></i></a>
                        </div>
                    </div>
                    <h3 class="mt-4">
                        Add to Samples Restock
                    </h3>

                    <div id="restock_duplicates" class="row hide">
                        <div class="alert alert-danger w-100">
                            <h5>There are items in your order that are still pending at the Sampling Dep and could be potential duplicates.<br>Do you still want to send them? They will be added to the original order.</h5>
                            <div id="restock_duplicates_table"></div>
                            <a class="btn btn-warning float-right btnConfirmDuplicates">Yes <i class="far fa-square"></i></a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class='form-group row my-0'>
                                <label for="restock_destination_all" class="col-6 col-form-label">Destination</label>
                                <div class="col col_dropdown_restock_destination_all">
<!--                                    <select name="dropdown_restock_destination_all" id="dropdown_restock_destination_all" class='single-dropdown w-filtering' tabindex='-1'>-->
<!--                                        <option value="Office">Office</option>-->
<!--                                        <option value="Warehouse">Warehouse</option>-->
<!--                                    </select>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class='form-group row my-0'>
                                <label for="restock_sizes_all" class="col-6 col-form-label">Size (for all)</label>
                                <div class="col">
<!--                                    <select name="dropdown_restock_sizes_all" id="dropdown_restock_sizes_all" class='single-dropdown w-filtering' tabindex='-1'>-->
                                    <select name="dropdown_restock_sizes_all" id="dropdown_restock_sizes_all" class='' tabindex='-1'>
                                        <option value="6">6x6</option>
                                        <option value="9">9x9</option>
                                        <option value="12">12x12</option>
                                        <option value="12R">12xRepeat</option>
                                        <option value="18">18x18</option>
                                        <option value="24">24x24</option>
                                        <option value="26">26x26</option>
                                    </select>
                                </div>
                            </div>
                        </div>
<!--                        <div class="col-6"></div>-->
<!--                        <div class="col-6">-->
<!--                            <div class='form-group row my-0'>-->
<!--                                <label for="restock_priority_all" class="col-6 col-form-label">Priority (for all)</label>-->
<!--                                <div class="col">-->
<!--                                    <select name="dropdown_restock_priority_all" id="dropdown_restock_priority_all" class='' tabindex='-1'>-->
<!--                                        <option value="0">0</option>-->
<!--                                        <option value="1">1</option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
                        <input type="hidden" id="OK_with_duplicates" name="OK_with_duplicates" value="0">
                    </div>

                    <table id='restock_preview' class='row-border order-column hover compact' width='100%'>
                    </table>

                </div>

            </div>
        </div>
    </div>
<?php } ?>

<script>
    var mtable_id = "#items_table";
    var modal_table_id = "#list_preview";
    var restock_table_id = "#restock_preview";
    var this_table; // datatable

    var item_id = '<?php echo  $item_id ?>';
    var product_id = '<?php echo  $product_id ?>';
    var product_type = '<?php echo  $product_type ?>';
    var pname = "<?php echo str_replace('"', '\"', $pname)?>";
    var printSpecUrl = '<?php echo site_url("product/specsheet")?>';
    var getItemListUrl = '<?php echo site_url('item/get_product_items')?>';
    var typeAheadSearchUrl = '<?php echo site_url('product/typeahead_products_list')?>';
    var stamps = JSON.parse('<?php echo json_encode($stamps)?>');

    var restock_destinations_dropdown = null;

    var hasEditPermission = '<?php echo $hasEditPermission?>' === '1';
    var is_showroom = '<?php echo $is_showroom?>' === '1';

    /*
	  Start
	  item_list.js
	*/

    var states_done = 0;
    var mysettings = {
        dom: '< <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <t> i p >',
        'serverSide': false,
        "processing": false,
        "ajax": {
            "url": getItemListUrl,
            "type": "POST",
            // Set the data to send to Ajax
            "data": function (d) {
                return {...d, item_id: item_id, product_id, product_id, product_type: product_type};
            },
            "dataSrc": function (json) {
                if (states_done > 0) {
                    var obj = {
                        data: {item_id: item_id, product_id, product_id, product_type: product_type},
                        url: window.location.href
                    };
                    add_history_state(obj);
                }
                states_done++;

				if( json.tableData.length > 0 ){
                  console.log('json.tableData[0].product_name: ' + json.tableData[0].product_name);
                  console.log('pname: ' + '<?php echo $pname ?>');
				  // add logic to not do this if $pname is not empty
                  if( json.tableData[0].product_name === '<?php echo $pname ?>' ){
                    $('#input_search').val( json.tableData[0].product_name );
                  }
				}
				
                if (product_id !== '0' && json.hasEditPermission && json.tableData.length === 0) {
                    // Product is empty
                    // Show modal to add items
                    // open_item_modal( item_id, isMulti, product_type, item_product_id, refer )
                    // eg. open_item_modal( '0', false , product_type, product_id, 'item_list.php');
                    open_item_modal( '0', false , product_type, product_id, 'item_list.php');
                }

                return json.tableData;
            }
        },
        "initComplete": function (settings, json) {
            //console.log(settings);
            myDefaultDatatablesInitComplete();
            //$(settings.nTable).dataTable().fnFilterOnReturn();
            //$('select#searchtype').val('It');//.multiselect('refresh');
        }
    };
    var this_table = custom_datatables.items({
        is_showroom: is_showroom,
        target: this_table,
        table_id: mtable_id,
        serverSideUrl: getItemListUrl,
        isGeneralSearch: false
    }, mysettings).search('');

    // Elements in the view
    $("div.items-filter").html("<div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input form-control' id='filter_discontinued' name='filter_discontinued'><label class='custom-control-label' for='filter_discontinued'>Hide Discontinued</label>");

	<?php if($is_admin){ ?>
    $('div.items-filter').html("<div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input form-control' id='filter_archived' name='filter_archived'><label class='custom-control-label' for='filter_archived'>Hide Archived</label>");
	<?php } ?>

    $.fn.dataTable.ext.search.push(
        function (settings, searchData, index, rowData, counter) {
            var row_to_show = true;
            if ($('#filter_discontinued').prop('checked')) {
                if (rowData.status_id === '3') {
                    row_to_show = false;
                }
            }
            return row_to_show;
        }
    );


    $('#filter_discontinued').change(function () {
        this_table.draw();
    });

    /* Typeahead */
    var options = {
        url: typeAheadSearchUrl,

        getValue: "label",
        //placeholder: "",
        cssClasses: "col pl-0 eac-description",
        /*
		template: {
			type: "description",
			fields: {
				description: "description"
			}
		},
		*/
        template: {
            type: "custom",
            method: function (value, item) {
                var icon = '';
                var iden = item.id;
// 					console.log(item);
                if (iden.indexOf('-item_id') >= 0) {
                    icon = '<i class="fas fa-tag text-primary"></i>';
                } else if (iden.indexOf('-digital_item_id') >= 0) {
                    icon = '<i class="fas fa-tag"></i>';
                } else if (iden.indexOf('-R') >= 0) {
                    icon = '<i class="fas fa-book"></i>';
                } else if (iden.indexOf('-D') >= 0) {
                    icon = '<i class="fas fa-book"></i>';
                } else if (iden.indexOf('-SP') >= 0) {
                    icon = '<i class="fas fa-book"></i>';
                }
                return icon + '   ' + value + " - <span>" + item.description + "</span>";
            }
        },

        list: {
            match: {
                enabled: true
            },
            maxNumberOfElements: 100,
            onChooseEvent: function () {

                $('.internal-loader-spin').removeClass('hide');
                $(mtable_id).css('opacity', '0.2');

                var selectedItemData = $("#input_search").getSelectedItemData();
                console.log(selectedItemData);
                var value = selectedItemData.id;
                [id, type] = value.split('-');
                if(type === 'item_id'){
                    item_id = id;
                    product_id = '0';
                } else {
                    item_id = '0';
                    product_id = id;
                }
                product_type = type;
                console.log('new product_id: ' + [product_id, product_type]);
                this_table.search('');
                this_table.ajax.reload();
                //$("#data-holder").val(value).trigger("change");
            },
            onKeyEnterEvent: function () {
// 					$("#input_search").trigger('change');
                console.log("Enter key");
            }
        },

        ajaxSettings: {
            dataType: "json",
            method: "POST",
            data: {
                dataType: "json"
            }
        },

        preparePostData: function (data) {
            data.query = $("#input_search").val();
            data.includeDigital = $('#includeDigital').prop('checked');
// 				data.filters = {
// 					includeRegular: $('#includeRegular').prop('checked'),
// 					includeDigital: $('#includeDigital').prop('checked'),
// 					includeSKU: $('#includeSKU').prop('checked')
// 				};
            return data;
        },
        requestDelay: 500
    };

    $("#input_search").easyAutocomplete(options);
    $("#input_search").on('focus', function () {
        $(this).select();
    })


    var preview_table;
    var restock_table;

    $('#list_modal').on('show.bs.modal', function (e) {

        if (!$.fn.DataTable.isDataTable(modal_table_id)) {
            preview_table = $(modal_table_id).DataTable({
                "dom": '<  <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f > <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" l> > <t> i p >',
                "ajax": {
                    "url": '<?php echo site_url('lists/preview')?>',
                    "type": "POST",
                    "dataSrc": "tableData"
                },
                "pageLength": 50,
                "columns": [
                    {"data": "action"},
                    {"data": "list_name"},
                    {"data": "category"},
                    {"data": "total_items"},
                    {"data": "date_modif"}
                ],
                "columnDefs": [
                    {
                        "targets": [0],
                        "searchable": false,
                        "orderable": false,
                    }
                ],
                "order": [[4, "desc"]],
                "buttons": []
            });
        } else {
            preview_table.ajax.reload();
        }

    });

    function _get_sample_size_dropdown_copy(_new_id){
        return $('#dropdown_restock_sizes_all').clone().attr('id', _new_id).attr('name', _new_id).get(0).outerHTML;
    }

    $('#dropdown_restock_sizes_all').on('change', function(){
        let new_val = $(this).val();
        edit_doms_like_ids_2_new_value('restock_size_', new_val);
    })

    $('#dropdown_restock_priority_all').on('change', function(){
        let new_val = $(this).val();
        edit_doms_like_ids_2_new_value('restock_priority_', new_val);
    })

    function edit_doms_like_ids_2_new_value(id_like, new_val){
        $("[id^="+id_like+"]").each(function(){
            $(this).val(new_val);
        })
    }

    $('#restock_modal').on('show.bs.modal', function (e) {

        if (!$.fn.DataTable.isDataTable(restock_table_id)) {
            restock_table = $(restock_table_id).DataTable({
                "dom": '< <"d-flex flex-row justify-content-between align-items-center my-4" B  > <t> i p >',
                "paging": false,
                "columns": [
                    {   "title": "Product Name", "data": "product_name"},
                    {   "title": "Item #", "data": "code"},
                    {   "title": "Color", "data": "color"},
                    {   "title": "Size",
                        "render": function(data, type, row, meta){
                            return _get_sample_size_dropdown_copy('restock_size_'+row.item_id);
                        }
                    },
                    {
                        "title": "Regular Qty",
                        "render": function(data, type, row, meta){
                            let _input_id = 'restock_quantity_' + row.item_id;
                            return '<input type="number" maxlength="2" class="" id="'+_input_id+'" name="'+_input_id+'" value="1">';
                        }
                    },
                    {
                        "title": "Priority Qty",
                        "render": function(data, type, row, meta){
                            let _input_id = 'restock_quantity_priority_' + row.item_id;
                            return '<input type="number" maxlength="2" class="" id="'+_input_id+'" name="'+_input_id+'" value="0">';
                        }
                    },
                    {
                        "title": "Ringsets Qty",
                        "render": function(data, type, row, meta){
                            let _input_id = 'restock_quantity_ringsets_' + row.item_id;
                            return '<input type="number" maxlength="2" class="" id="'+_input_id+'" name="'+_input_id+'" value="0">';
                        }
                    },
                    {
                        "title": "Status",
                        "render": function(data, type, row, meta){
                            let dropdown = `<?php echo $restock_filter_status?>`;
                            dropdown = dropdown.replace('id="restock_filter_status"', 'id="restock_filter_status_'+row.item_id+'"');
                            return dropdown;
                        }
                    }
                ],
                "order": [[0, "desc"]],
                "buttons": []
            });
        } else {
            restock_table.clear();
        }

        if(restock_destinations_dropdown == null){
            $.ajax({
                method: "POST",
                url: '<?php echo site_url('restock/get_destinations')?>',
                dataType: 'json',
                data: {

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                },
                success: function (data, msg) {
                    restock_destinations_dropdown = true;
                    $(".col_dropdown_restock_destination_all").html(data.dropdown_html);
                }
            });
        }

        // Get all selected and add them to this list!
        var dt_data = $(mtable_id).DataTable().rows({selected: true}).data();
        restock_table.rows.add(dt_data).draw(false);

    });

    $('#restock_modal').on('hide.bs.modal', function () {
        $("#OK_with_duplicates").val('0');
        $("input[name='duplicate_order_ids']").val('');
        $("#restock_duplicates").addClass('hide');
    })

    $(".btnSubmitRestock").on('click', submit_restock)

    function collect_restock_data(){
        let dt_restock_data = restock_table.rows().data();
        let restock_items = [];
        $.each(dt_restock_data, function(i, v){
            restock_items.push({
                'item_id': v.item_id,
                'size': $("#restock_size_"+v.item_id).val(),
                'quantity': parseInt($("#restock_quantity_"+v.item_id).val()),
                'quantity_priority': parseInt($("#restock_quantity_priority_"+v.item_id).val()),
                'quantity_ringsets': parseInt($("#restock_quantity_ringsets_"+v.item_id).val()),
                'restock_status_id': parseInt($("#restock_filter_status_"+v.item_id).val())
            })
        })
        // console.log(restock_items);
        return restock_items;
    }

    function submit_restock(){
        let restock_items = collect_restock_data();
        let destination = $('#dropdown_restock_destination_all').val();

        // console.log(destination);
        // console.log(restock_items);

        $.ajax({
            method: "POST",
            url: '<?php echo site_url('restock/add')?>',
            dataType: 'json',
            data: {
                'OK_with_duplicates': $("#OK_with_duplicates").val(),
                'duplicate_order_ids': JSON.parse($("input[name='duplicate_order_ids']").val() || null),
                'destination': destination,
                'items': restock_items,
                'debug': false
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            success: function (data, msg) {
                if( data.success ){
                    show_success_swal();
                    $('#restock_modal').modal('hide');
                }
                else if ( data.status == 'duplicates' ){
                    $("#restock_duplicates_table").html(data.response);
                    $("#restock_duplicates").removeClass('hide');
                }
            }
        });
    }

    $('.btnConfirmDuplicates').on('click', submit_restock_duplicates)

    function submit_restock_duplicates(){
        $("#OK_with_duplicates").val('1');
        $(".btnSubmitRestock").trigger('click');
    }

    $('#list_modal').on('click', '.btnSelectList', function () {
        var btn = $(this);
        var list_id = $(this).attr('data-list-id');
        var dt_data = $(mtable_id).DataTable().rows({selected: true}).data();
        var items = [];
        $.each(dt_data, function (i, v) {
            items.push(v.item_id);
        });

        $.ajax({
            method: "POST",
            url: '<?php echo site_url('lists/add_item_to_list')?>',
            dataType: 'json',
            data: {
                'item_id': items,
                'list_id': list_id
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            success: function (data, msg) {

                btn.css('color', 'red');
                show_success_swal();
            }
        });

    });


</script>