<style>
    .badgy {
        /*display: inline-block;*/
        /*min-width: 10px;*/
        /*font-weight: 700;*/
        /*line-height: 1;*/
        /*color: black;*/
        /*-webkit-text-fill-color: black;*/
        /*-webkit-opacity: 1;*/
        text-align: center;
        white-space: nowrap;
        /*vertical-align: middle;*/
        /*!* background-color: #777; *!*/
        /*background-color: transparent;*/
        /*border-radius: 10px;*/
        border-style: none;
    }
	.edited_row {
		background-color: #ffeb0047 !important;
	}
    .left-separator {
        border-left: 1px solid;
    }
    .btn-secondary {
        color: #000 !important;
        background-color: #e9ecef !important;
        border-color: #e9ecef !important;
    }
    .btn-secondary:not(:disabled):not(.disabled).active, .btn-secondary:not(:disabled):not(.disabled):active, .show>.btn-secondary.dropdown-toggle {
        color: #fff !important;
        background-color: #007bfe94 !important;
        border-color: #72b1fe !important;
    }
    .qty-null { opacity: 0.2; color: black !important; }
    .qty-priority { color: #ff0000 }
    .status_bg_CANCEL { background: #000000; color: white; }
    .status_bg_NEW { background: #ff00007a; }
    .status_bg_STOCK { background: #ff5100; }
    .status_bg_CUT { background: #ffa800bf; }
    .status_bg_OVERLOCK { background: #80007569; }
    .status_bg_LABEL { background: #a6ff00d4; }
    .status_bg_BACKORDER { background: #f8ff009e; }
    .status_bg_ON_ORDER { background: #7eb4ff; }
    .status_bg_COMPLETED { background: green; color: white; }
    
    /* Validation feedback styles */
    .quantity-error {
        background-color: #ffcccc !important;
        border: 2px solid #ff4444 !important;
    }
    .quantity-warning {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
    }
    .quantity-valid {
        background-color: #d4edda !important;
        border: 2px solid #28a745 !important;
    }

</style>
<form id="restock_filters">
    <div class="row py-1">
        <div class="col">
            History<br>
            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                <label class="btn btn-secondary <?php echo (!$start_completed ? "active" : "")?> w-50">
                    <input type="radio" filter-title="History" name="restock_filter_order_history" id="pendings" value="pendings" autocomplete="off" onchange='reload_table()' <?php echo (!$start_completed ? "checked" : "")?>> Pendings
                </label>
                <label class="btn btn-secondary <?php echo ($start_completed ? "active" : "")?> w-50">
                    <input type="radio" filter-title="History" name="restock_filter_order_history" id="completed" value="completed" autocomplete="off" onchange='reload_table()' <?php echo ($start_completed ? "checked" : "")?>> Completed
                </label>
            </div>
        </div>
        <div class="col">
            Date From (include) <input class='form-control' type="date" filter-title="Date from" id="restock_filter_from" name="restock_filter_from" min="2020-01-01" max="2100-12-31" value="<?php echo date("Y-m-d", strtotime("-12 months"))?>">
        </div>
        <div class="col">
            Date To (include) <input class='form-control' type="date" filter-title="Date to" id="restock_filter_to" name="restock_filter_to" min="2020-01-01" max="2100-12-31" value="<?php echo date("Y-m-d")?>">
        </div>
        <div class="col">
            Destination <?php echo $restock_filter_destinations;?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-3">
            Status <?php echo $restock_filter_status;?>
        </div>
        <div class="col-6">

        </div>
        <div class="col">
            <a class="btn btn-outline-danger float-right" tabindex="0" aria-controls="restock_table" onclick="reload_table();"><span><i class="fad fa-filter"></i> Refresh</span></a>
        </div>
    </div>
</form>
<div class="internal-loader-spin fa-3x mx-4">
    <i class="fas fa-circle-notch fa-spin"></i>
</div>
<table id='restock_table' class='row-border order-column hover compact' width='100%'></table>

<!-- Modal for Restocking -->
<div class="modal fade" id="edit_restock_modal" style='z-index:9999;' tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style='max-width:900px;' role="document">
        <div class="modal-content" id='modal-content'>
            <div class='p-4'>

                <div class="row">
                    <div class="col-6">
                        <a class="btn btn-secondary btnClose float-left" data-dismiss="modal"><i class="far fa-window-close"></i> Close</a>
                    </div>
                    <div class="col-6">
                        <a class="btn btn-success float-right btnSubmitEditRestock">Save <i class="far fa-square"></i></a>
                    </div>
                </div>
                <h3 class="mt-4">
                    Edit Restock Order
                </h3>

                <div class="row">
                    <div class="col-6">
                        <div class='form-group row my-0'>
                            <label for="restock_destination_all" class="col-6 col-form-label">Destination</label>
                            <div class="col col_dropdown_restock_destination_all">
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class='form-group row my-0'>
                            <label for="restock_sizes_all" class="col-6 col-form-label">Size</label>
                            <div class="col">
                                <select name="dropdown_restock_sizes_all" id="dropdown_restock_sizes_all" class='' tabindex='-1'>
                                    <option value="6">6x6</option>
                                    <option value="12">12x12</option>
                                    <option value="18">18x18</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    var mtable_id = "#restock_table";
    var this_table = undefined;
    var hasEditPermission = '<?php echo $hasEditPermission?>' === '1';

    $(document).ready(function() {
        init_dropdowns();


        this_table = $(mtable_id)
            .on('preXhr.dt', function (e, settings, data) {
                if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
            })
            .DataTable({
                'dom': '< <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"d-flex flex-row justify-content-between filters_view"> <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <"d-flex justify-content-center my-3" p> <t> i <"d-flex justify-content-center my-3" p> >',
                'serverSide': false,
                "processing": false,
                "ajax": {
                    "url": '<?php echo $ajaxUrl?>',
                    "type": "POST",
                    "dataSrc": "tableData",
                    "data": build_ajax_data
                },
                "pageLength": 50,  // Enable simple pagination with 50 rows per page
                "paging": true,
                "language": {
                    "searchPlaceholder": "Search"
                },
	            'rowId': 'id',
                "columns": [
                    {"title": "id", "data": "id", "visible": false, "searchable": false},
                    {"title": "Item ID", "data": "item_id", "visible": false, "searchable": false},
                    {"title": "Date Req", "data": "date_add", "visible": true, "searchable": false},
                    {"title": "Date Modif", "data": "date_modif", "visible": true, "searchable": false},
                    {"title": "Shelfs", "data": "shelfs", "visible": true, "searchable": false},
                    {"title": "Vendor", "data": "vendor_name", "visible": false, "searchable": false},
                    {"title": "Product Name", "data": "product_name"},
                    {"title": "Item #", "data": "code"},
                    {"title": "Color", "data": "color"},
                    {"title": "For", "data": "destination", "searchable": true},
                    {"title": "By", "data": "username", "searchable": true},
                    {"title": "Size", "data": "size", "searchable": false},
                    {
                        "title": "Pending Total", "visible": true, "searchable": false,
                        "render": function (data, type, row, meta) {
                            // REVERTED: Use original legacy logic - quantity_total represents main samples needed
                            let qty = parseInt(row.quantity_total) - parseInt(row.quantity_shipped);
                            return '<span class="'+(qty > 0 ? '' : 'qty-null')+'"><i class="fas fa-tag"></i> ' + qty + '</span>';
                        }
                    },
                    {
                        "title": "Pending Priority", "visible": true, "searchable": false,
                        "render": function (data, type, row, meta) {
                            let qty = Math.max(0, parseInt(row.quantity_priority) - parseInt(row.quantity_shipped));
                            return '<span class="qty-priority '+(qty > 0 ? '' : 'qty-null')+'"><i class="fas fa-tag"></i> ' + qty + '</span>';
                        }
                    },
                    {
                        "title": "Pending Ringsets", "visible": true, "searchable": false,
                        "render": function (data, type, row, meta) {
                            let qty = parseInt(row.quantity_ringsets) - parseInt(row.quantity_ringsets_shipped);
                            return '<span class="'+(qty > 0 ? '' : 'qty-null')+'"><i class="fas fa-book"></i> ' + qty;
                        }
                    },
                    {
                        "title": "Original Request", "visible": false, "searchable": false, 'class': 'completed-view',
                        "render": function (data, type, row, meta) {
                            let qty_1 = parseInt(row.quantity_total);
                            let qty_1_class = (qty_1 > 0 ? '' : 'qty-null');
                            let qty_2 = parseInt(row.quantity_ringsets);
                            let qty_2_class = (qty_2 > 0 ? '' : 'qty-null');
                            return '<span class="'+qty_1_class+'"><i class="fas fa-tag"></i> ' + qty_1 + '</span> <span class="'+qty_2_class+'"><i class="fas fa-book"></i> ' + qty_2 + '</span>';
                        }
                    },
                    {
                        "title": "Status", "visible": true, "searchable": true, "className": "",
                        "render": function(data, type, row, meta) {
                            if(row.is_completed == "true" || row.is_completed == true){
                                return "<span class='p-1 status_bg_COMPLETED'>COMPLETED</span>";
                            }
                            else {
                                // FIXED: Handle both dropdown HTML and read-only text
                                let status_str;
                                
                                // Check if status_dropdown is read-only text (contains status-readonly class)
                                if (row.status_dropdown && row.status_dropdown.includes('status-readonly')) {
                                    // Extract text from read-only span
                                    const tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = row.status_dropdown;
                                    status_str = tempDiv.textContent || tempDiv.innerText || '';
                                } else {
                                    // Extract from dropdown HTML
                                    status_str = get_dropdown_selected_text(row.status_dropdown);
                                }
                                
                                // Safety check to prevent undefined errors
                                if (!status_str || typeof status_str !== 'string') {
                                    status_str = 'Unknown';
                                }
                                
                                const cls = 'p-1 status_bg_'+status_str.replace(' ', '_');
                                return "<span class='"+cls+"'>"+status_str+"</span>";
                            }
                        }
                    },
                    {"title": "New Status", "data": "status_dropdown", "searchable": false, "orderable": false, "className": "no-export left-separator action-column" },
                    {
                        "title": "Fill", "searchable": false, "orderable": false, "className": 'no-export action-column',
                        "render": function (data, type, row, meta) {
                            return get_action_buttons(row);
                        }
                    },
                    {
                        "title": "", "searchable": false, "orderable": false, "className": 'no-export ',
                        "render": function(data, type, row, meta){
                            return get_add_memos_to_printer_btn(row);
                        }
                    }
                ],
                "columnDefs": [
                    { "width": "10%", "targets": [-2, -3] }
                ],
                "orderClasses": false,
                "order": [[6, "asc"], [3, "asc"]],
                "buttons": [
                    custom_buttons.back(),
                    custom_buttons.view([0]),
                    custom_buttons.export({
                        title: function(){
                            return "Restock List"
                        },
                        messageTop: function(){
                            return $('.filters_view').html();
                        }
                    }),
                    // {
                    //     text: "<i class=\"fad fa-filter\"></i> Filter",
                    //     className: 'btn btn-outline-danger no-border',
                    //     action: function (e, dt, node, config) {
                    //         this_table.ajax.reload();
                    //     }
                    // },
                    {
                        text: '<i class="far fa-cloud-upload-alt"></i> Commit',
                        className: 'btn btn-outline-success no-border',
                        action: function (e, dt, node, config) {
                            restock_save();
                        }
                    }
                ],
                "drawCallback": function (settings) {
                    myDefaultDatatablesDrawCallback(settings);
                    // Update filters list
                    updateFiltersView(settings);
                },
            });
            
            // FIX: Ensure action columns are visible on initial load for Pendings tab
            // The build_ajax_data function only runs on subsequent AJAX calls, not initial load
            var initial_tab = 'pendings'; // Default tab
            if (initial_tab == 'pendings') {
                this_table.columns(".action-column").visible(true);
            }

        function get_action_buttons(row) {
            let input_samples_id = 'restock_complete_quantity_samples_' + row.id;
            let input_ringset_id = 'restock_complete_quantity_ringset_' + row.id;
            let max_samples = Math.max(0, row.quantity_total - row.quantity_shipped);
            let max_ringsets = Math.max(0, row.quantity_ringsets - row.quantity_ringsets_shipped);
            let input_samples = '<input type="number" maxlength="2" style="width:50px" id="'+input_samples_id+'" name="'+input_samples_id+'" data-id="'+row.id+'" value="0" min="0" max="'+max_samples+'" data-max-allowed="'+max_samples+'" onchange="validateQuantity(this)" oninput="validateQuantity(this)">';
            let input_ringset = '<input type="number" maxlength="2" style="width:50px" id="'+input_ringset_id+'" name="'+input_ringset_id+'" data-id="'+row.id+'" value="0" min="0" max="'+max_ringsets+'" data-max-allowed="'+max_ringsets+'" onchange="validateQuantity(this)" oninput="validateQuantity(this)">';
            let edit_button = '<i class="fas fa-pen-square btn-action btnEditRestockOrder" data-id="'+row.id+'" onclick="edit_restock_order(this)">';
            return '<i class="fas fa-tag"></i> ' + input_samples + ' <i class="fas fa-book"></i> ' + input_ringset; // + ' ' + edit_button;
        }
        
        function get_add_memos_to_printer_btn(row){
            const ptype = row.product_type;
            const pid = row.product_id;
            const item_id = row.item_id;
            const qty = row.quantity_total;
            const args = ["'"+ptype+"'", "'"+pid+"'", "'"+item_id+"'", "'"+qty+"'"].join(',');
            // const icon_tag = '<span class="fa-stack" style="vertical-align: top;">\n' +
            //     '  <i class="fal fa-tags fa-stack-1x"></i>\n' +
            //     '  <i class="fal fa-plus fa-stack-1x" data-fa-transform="shrink-8 up-12 right-12"></i>\n' +
            //     '</span>';
            const icon_tag = '<i class="fal fa-tags"></i>';
            return '<span onclick="add_memos_to_printer('+args+')">'+icon_tag+'</span>';
        }

    });

    function add_memos_to_printer(ptype, pid, item_id, qty){
        the_printing_cart.add_item(ptype, pid, item_id, parseInt(qty));
        show_success_swal('top');
    }

    function updateFiltersView(settings){
        let filters_values = []
        let ajax_data = settings.oAjaxData;
        // console.log(ajax_data);
        for (key in ajax_data) {
            // console.log(key, ajax_data[key]);
            let field_title, selected_text;
            if (key === 'restock_filter_order_history'){
                field_title = 'History';
                selected_text = ajax_data[key];
            } else {
                field_title = $("#"+key).attr('filter-title');
                selected_text = get_dropdown_selected_text("#"+key);
                if (typeof(selected_text) == 'undefined'){
                    selected_text = $("#"+key).val();
                }
                if (selected_text.length == 0) continue;
            }

            let new_val = field_title + ": " + selected_text;
            // console.log(key, field_title, selected_text, selected_text.length);
            filters_values.push(new_val);
        }

        $('.filters_view').html("Results where " + filters_values.join(', '));
    }

    function reload_table(){
        if(validate_dates()) {
            $('.internal-loader-spin').removeClass('hide');
            $(mtable_id).css('opacity', '0.2');
            
            // Direct table reload - auto-completion removed for better performance
            this_table.search('');
            this_table.ajax.reload();
        }
        else {
            // console.log("Too long date ranges.")
        }
    }

    function validate_dates(){
        let date_from = $("input[name='restock_filter_from']")[0].value;
        let date_to = $("input[name='restock_filter_to']")[0].value;
        let validDates = date_from != '' && date_to != '';

        if( !validDates ){
            
            show_success_swal("Please enter some dates.", "warning");
            return false;
                } else {
            // Date range validation removed - now using pagination and memory limits instead
            // Basic date parsing for validation only
            date_from = new Date(date_from);
            date_to = new Date(date_to);
            
            // Verify dates are valid
            if (isNaN(date_from.getTime()) || isNaN(date_to.getTime())) {
                show_success_swal("Please enter valid dates.", "warning");
                return false;
            }
            
            // Ensure from date is before to date
            if (date_from > date_to) {
                show_success_swal("'From' date must be before 'To' date.", "warning");
                return false;
            }
        }

        return true;
    }

    var _DT_HEADERS_RENAME = {
        "pendings": {
            3: "Date Modif",
            // 9: "Pending Qty",
            // 13: "Status"
        },
        "completed": {
            3: "Date Completed",
            // 9: "Pending Qty",
            // 13: "Last Status"
        }
    };

    function build_ajax_data() {
        var result = {};
        $.each($("#restock_filters").serializeArray(), function(){
            result[this.name] = this.value;
        });
        // console.log(result);

        if (typeof(this_table) == 'undefined'){
            // Skip everything if the datatables was not defined yet
            return result;
        }

        let order_status_being_shown = result['restock_filter_order_history']; // 'pendings' or 'completed'
        let new_dt_headers = _DT_HEADERS_RENAME[order_status_being_shown];
        // for (const [key, value] of Object.entries(new_dt_headers)) {
        for (key in new_dt_headers) {
            value = new_dt_headers[key];
            var head_item = this_table.columns(key).header();
            $(head_item).html(value);
        }

        if (order_status_being_shown == 'pendings') {
            this_table.columns(".action-column").visible(true);
        } else {
            this_table.columns(".action-column").visible(false);
            this_table.columns(".completed-view").visible(true);
        }

        return result;
    }

    function validateQuantity(element) {
        let $element = $(element);
        let maxAllowed = parseInt($element.attr('data-max-allowed'));
        let currentValue = parseInt($element.val());
        let row_id = $element.attr('data-id');
        
        // If value is empty or NaN, set to 0
        if (isNaN(currentValue) || currentValue === '') {
            $element.val(0);
            currentValue = 0;
        }
        
        // If value is negative, set to 0
        if (currentValue < 0) {
            $element.val(0);
            currentValue = 0;
            show_success_swal('Quantity cannot be negative', 'warning');
        }
        
        // Apply validation styling based on value
        $element.removeClass('quantity-error quantity-warning quantity-valid');
        
        if (currentValue > maxAllowed) {
            // Over the limit - cap it and show error
            $element.val(maxAllowed);
            $element.addClass('quantity-error');
            show_success_swal(`Maximum allowed quantity is ${maxAllowed}. Value capped to maximum.`, 'warning');
            setTimeout(() => {
                $element.removeClass('quantity-error');
                if (maxAllowed > 0) $element.addClass('quantity-valid');
            }, 2000);
        } else if (currentValue === maxAllowed && maxAllowed > 0) {
            // At maximum - show warning style
            $element.addClass('quantity-warning');
            setTimeout(() => {
                $element.removeClass('quantity-warning');
                $element.addClass('quantity-valid');
            }, 1000);
        } else if (currentValue > 0) {
            // Valid positive value - show success
            $element.addClass('quantity-valid');
            setTimeout(() => {
                $element.removeClass('quantity-valid');
            }, 1000);
        }
        
        // Only mark as edited if value is valid and > 0
        if (currentValue > 0 && currentValue <= maxAllowed) {
            mark_row_as_edit(element);
        }
    }

    function mark_row_as_edit(element) {
        let row_id = $(element).attr('data-id');
		let row_node = this_table.row("#"+row_id).node();
		$(row_node).addClass('edited_row');
    }

    function mark_row_as_non_edit(row_id){
        // let row_id = $(element).attr('data-id');
        let row_node = this_table.row("#"+row_id).node();
        $(row_node).removeClass('edited_row');
    }

    function restock_save() {
        var row_changes = this_table.rows('.edited_row').data();
        var restock_updates = [];
        var validation_errors = [];

        for(var i = 0; i < row_changes.length; i++) {
			let row = {};
			row['id'] = row_changes[i].id;
			row['ship_quantity_samples'] = parseInt($('#restock_complete_quantity_samples_'+row['id']).val()) || 0;
			row['ship_quantity_ringset'] = parseInt($('#restock_complete_quantity_ringset_'+row['id']).val()) || 0;
			row['restock_status_id'] = $('#restock_status_'+row['id']).val();
			
			// Final validation before submission
			let row_data = row_changes[i];
			let max_samples = Math.max(0, row_data.quantity_total - row_data.quantity_shipped);
			let max_ringsets = Math.max(0, row_data.quantity_ringsets - row_data.quantity_ringsets_shipped);
			
			if (row['ship_quantity_samples'] > max_samples) {
			    validation_errors.push(`Order ${row['id']}: Cannot ship ${row['ship_quantity_samples']} samples, only ${max_samples} remaining`);
			}
			
			if (row['ship_quantity_ringset'] > max_ringsets) {
			    validation_errors.push(`Order ${row['id']}: Cannot ship ${row['ship_quantity_ringset']} ringsets, only ${max_ringsets} remaining`);
			}

            restock_updates.push(row);
        }

        if(restock_updates.length == 0){
            return;
        }
        
        // If there are validation errors, show them and stop submission
        if (validation_errors.length > 0) {
            show_success_swal('Validation Errors:\n' + validation_errors.join('\n'), 'error');
            return;
        }

        // console.log(restock_updates);
        $.ajax({
            method: "POST",
            url: "<?php echo site_url('restock/save')?>",
            dataType: 'json',
            data: {
                'restock_updates': restock_updates
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Restock save error:', {jqXHR, textStatus, errorThrown});
                let errorMsg = 'Failed to save restock data';
                
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    errorMsg = jqXHR.responseText;
                } else if (errorThrown) {
                    errorMsg = errorThrown;
                }
                
                show_success_swal('Save Error: ' + errorMsg, 'error');
            },
            success: function (data, msg) {
                update_submission(data);
                show_success_swal('top');
            }
        });
    }

    function update_submission(data){
        var row_changes = this_table.rows('.edited_row').data();
        // console.log("Update", data)

        for(var i = 0; i < row_changes.length; i++){
            let row_data = row_changes[i];
            let row_id = parseInt(row_data.id);

            if( data.completed.includes(row_id) ){
                // Order was completed - remove from table
                this_table.row("#"+row_id).remove();
            } else {
                // Order was partially updated or status changed - update the row
                
                // Update quantities based on what was shipped
                let samples_shipped = parseInt($('#restock_complete_quantity_samples_'+row_id).val()) || 0;
                let ringsets_shipped = parseInt($('#restock_complete_quantity_ringset_'+row_id).val()) || 0;
                
                if (samples_shipped > 0 || ringsets_shipped > 0) {
                    row_data['quantity_shipped'] = parseInt(row_data['quantity_shipped']) + samples_shipped;
                    row_data['quantity_ringsets_shipped'] = parseInt(row_data['quantity_ringsets_shipped']) + ringsets_shipped;
                }

                // Update status dropdown if changed
                let new_status_id = $('#restock_status_'+row_id).val();
                if (new_status_id != row_data.restock_status_id) {
                    let dropdown_str = row_data['status_dropdown'].replace('selected="selected"', '');
                    row_data['status_dropdown'] = dropdown_str.replace('value="'+new_status_id+'"', 'value="'+new_status_id+'" selected="selected"');
                    row_data['restock_status_id'] = new_status_id;
                }

                // Find server data for more complex updates (status changes, completions)
                let server_row_data = null;
                for(var j = 0; j < data.updates.length; j++){
                    if( data.updates[j].id == row_id ){
                        server_row_data = data.updates[j];
                        break;
                    }
                }
                
                // Update date_modif only if server provided updated data
                if (server_row_data && server_row_data['date_modif']) {
                    row_data['date_modif'] = server_row_data['date_modif'];
                }
                // For partial shipments without status changes, keep original date_modif

                this_table.row("#"+row_id).data(row_data);
                mark_row_as_non_edit(row_id);
            }
        }
        this_table.draw();
    }

    function edit_restock_order(btn){
        let order_id = $(btn).attr('data-id');
        let row_data = this_table.rows("#"+order_id).data();
                    // console.log(row_data);
    }

    // $(".btnEditRestockOrder").on('click', function(){
    //     let order_id = $(this).attr('data-id');
    //     let row_data = this_table.rows("#"+order_id).data();
    //     console.log(row_data);
    // })




</script>