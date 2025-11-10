/*
  Start
  product_form_validator
*/


// Object in charge of validating the form
// and set variables that are being change for form submission

var validator = {
    formID: null,
    css_required_field: 'required-field',
    valid_form: true,
    errors: [],
    // Methods

    edit: function (spec_name, data) {

        switch (spec_name) {

            // Products validation
            case 'active':
            case 'info_name':
            case 'product_name':
            case 'width':
            case 'vrepeat':
            case 'hrepeat':
            case 'no_repeat':
            case 'outdoor':
            case 'dig_product_name':
            case 'dig_width':
            //case 'shelf_list':
            case 'product_status':
            case 'stock_status':
            case 'lightfastness':
            case 'seam_slippage':
                this.addHiddenInput('change_product');
                break;

            case 'in_master_no':
            case 'in_master_yes':
            case 'in_master':
                if (this.formID.indexOf('Item') >= 0) {
                    this.addHiddenInput('change_item');
                } else if (this.formID.indexOf('Product') >= 0) {
                    this.addHiddenInput('change_product');
                } else if (this.formID.indexOf('Combined') >= 0) {
                    this.addHiddenInput('change_product');
                }
                break;


            case 'dropdown_roll_location':
                this.addHiddenInput('change_roll_location');
                this.addHiddenInput('change_item');
                break;

            case 'dropdown_bin_location':
                this.addHiddenInput('change_bin_location');
                this.addHiddenInput('change_item');
                break;

            case 'shelf':
            case 'uses':
            case 'weave':
            case 'content_f_encoded':
            case 'content_b_encoded':
            case 'abrasion_encoded':
            case 'firecode_encoded':
            case 'origin':
            case 'vendor':
            case 'files_encoded':
            case 'product_messages_encoded':
            case 'cleaning_instructions':
            case 'warranty':
                this.addHiddenInput('change_' + spec_name);
                break;

            case 'category_files':
                if (jQuery.inArray('2', data) >= 0) {
                    $('#file_descr').parent().removeClass('hide');
                } else {
                    $('#file_descr').parent().addClass('hide');
                }
                break;

            case 'finish':
                if (jQuery.inArray("7", data) >= 0) {
                    $('#special_finish_instr').parent().removeClass('hide');
                } else {
                    $('#special_finish_instr').parent().addClass('hide');
                }
                this.addHiddenInput('change_' + spec_name);
                break;

            case 'cleaning':
                if (jQuery.inArray("20", data) >= 0) {
                    $('#special_cleaning_instr').parent().removeClass('hide');
                } else {
                    $('#special_cleaning_instr').parent().addClass('hide');
                }
                this.addHiddenInput('change_' + spec_name);
                break;

            case 'fob':
            case 'cost_cut':
            case 'cost_cut_type_id':
            case 'cost_half_roll':
            case 'cost_half_roll_type_id':
            case 'cost_roll':
            case 'cost_roll_type_id':
            case 'cost_roll_landed':
            case 'cost_roll_landed_type_id':
            case 'cost_roll_ex_mill':
            case 'cost_roll_ex_mill_type_id':
                this.addHiddenInput('change_costs');
                break;

            case 'p_res_cut':
            // case 'p_hosp_cut':
            case 'p_hosp_roll':
            case 'p_dig_res':
            case 'p_dig_hosp':
                this.addHiddenInput('change_prices');
                break;

            case 'min_order_qty':
                if (this.formID.indexOf('Item') >= 0) {
                    this.addHiddenInput('change_item');
                } else if (this.formID.indexOf('Product') >= 0) {
                    this.addHiddenInput('change_various');
                }
                break;

            case 'weight_n':
            case 'weight_unit':
            case 'prop_65_yes':
            case 'prop_65_no':
            case 'prop_65_dk':
            case 'ab_2998_compliant_yes':
            case 'ab_2998_compliant_no':
            case 'ab_2998_compliant_dk':
            case 'dyed_options_dk':
            case 'dyed_options_piece':
            case 'dyed_options_yarn':
            case 'tariff_code':
            case 'tariff_surcharge':
            case 'duty_perc':
            case 'freight_surcharge':
            case 'vendor_notes':
            case 'vendor_product_name':
            case 'yards_per_roll':
            case 'lead_time':
            case 'railroaded':
                this.addHiddenInput('change_various');
                break;

            case 'pic_big_url':
            case 'pic_hd_url':
            case 'descr':
            case 'showcase_descr':
            case 'showcase_visible':
            case 'showcase_collection':
            case 'showcase_contents_web':
            case 'showcase_patterns':
            case 'pic_big_delete':
                this.addHiddenInput('change_showcase');
                break;

            // Items validation
            case 'new_code':
            case 'dropdown_status':
            case 'dropdown_stock_status':
            case 'min_order_qty':
            case 'vendor_code':
            case 'vendor_color':
            case 'bin_quantity':
            case 'roll_yardage':
                this.addHiddenInput('change_item');
                break;

            case 'reselections_ids':
                this.addHiddenInput('change_reselections');
                break;

            case 'color_ids':
                this.addHiddenInput('change_item_colors');
                break;

            case 'showcase_coord_color':
                this.addHiddenInput('change_item_coord_color');
                break;

            case 'item_messages_encoded':
                this.addHiddenInput('change_item_messages');
                break;

            case 'sales_id':
                this.addHiddenInput('change_item_sales_id');
                break;

        } // End Switch

        //console.log(this);
    },

    addHiddenInput: function (inputName, new_value = 1) {
        var frmID = this.formID;
        var existingInput = $("form" + frmID + " > input[type='hidden'][name='" + inputName + "']");
        
        if (existingInput.length > 0) {
            // Update existing hidden input value
            existingInput.val(new_value);
        } else if ($('form' + frmID).length) {
            // Add new hidden input
            $('<input>').attr({
                type: 'hidden',
                name: inputName,
                value: new_value
            }).appendTo('form' + frmID);
        }
    },

    val: function () {
        this.compare();
        var frmID = this.formID;
        if (this.valid_form) {
            $.ajax({
                method: "POST",
                url: $('form' + frmID).attr('action'),
                dataType: 'json',
                data: $('form' + frmID).serialize(),
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                },
                success: function (data, msg) {

                    // Check for success in both response formats
                    // Item forms return: {success: true, ...}
                    // Other forms return: {status: 'OK', ...}
                    if (data.status == 'OK' || data.success === true) {
                        change_in_form = false;
                        
                        // ============================================================================
                        // CLOSE MODAL AND REFRESH DATATABLE AFTER ITEM SAVE
                        // ============================================================================
                        // For item forms: Close modal and reload DataTable to show updated values
                        if (frmID === '#frmItem') {
                            console.log('Item form saved successfully - refreshing DataTable');
                            
                            // Close the item edit modal
                            $('.modal').modal('hide');
                            
                            // Reload the colorline DataTable to show updated values
                            // Try multiple methods to access the DataTable
                            setTimeout(function() {
                                console.log('Attempting to reload DataTable...');
                                
                                // Method 1: Use global this_table variable (most reliable)
                                if (typeof this_table !== 'undefined' && this_table !== null) {
                                    console.log('Reloading via this_table global variable');
                                    this_table.ajax.reload(null, false);
                                    console.log('✓ Item DataTable reloaded via this_table');
                                }
                                // Method 2: Fallback to table ID selector
                                else if (typeof mtable_id !== 'undefined' && $.fn.DataTable.isDataTable(mtable_id)) {
                                    console.log('Reloading via mtable_id selector');
                                    $(mtable_id).DataTable().ajax.reload(null, false);
                                    console.log('✓ Item DataTable reloaded via mtable_id');
                                }
                                // Method 3: Try direct selector
                                else if ($.fn.DataTable.isDataTable('#items_table')) {
                                    console.log('Reloading via #items_table selector');
                                    $('#items_table').DataTable().ajax.reload(null, false);
                                    console.log('✓ Item DataTable reloaded via #items_table');
                                } else {
                                    console.warn('⚠ Could not find DataTable to reload');
                                }
                            }, 300); // Brief delay for modal close animation
                        } else {
                            // For non-item forms: Use original view reload logic
                            if (typeof (data.continueUrl) === 'undefined') {
                                get_ajax_view();
                            } else {
                                get_ajax_view(data.continueUrl);
                            }
                        }
                        // ============================================================================

                    } else {
                        show_alert(data.message);
                    }
                }
            });

        } else {
            // Show errors

        }

    },

    compare: function () {
        // Reinitialize
        this.valid_form = true;
        var css_required_field = this.css_required_field;
        var errors = [];
        var required;

        switch ($("input[name='product_type']").val()) {
            case 'R':
                required = [
                    {
                        field: 'input',
                        selector: '#product_name',
                        label: 'Product Name'
                    },
                    {
                        field: 'select',
                        selector: "select[name='vendor']",
                        label: 'Vendor'
                    }
                ];
                break;

            case 'D':
            case 'SP':
                required = [
                    {
                        field: 'select',
                        selector: "select[name='style']",
                        label: 'Pattern'
                    },
                    {
                        field: 'select',
                        selector: "select[name='ground']",
                        label: 'Ground'
                    }
                ];
                break;
        }


        $('.' + this.css_required_field).each(function (index, value) {
            $(this).removeClass(css_required_field);
        });

        required.forEach(function (value) {
            //$.each(required, function(index, value){
            var aux = false;
            switch (value.field) {
                case 'input':
                    if ($(value.selector).val().length === 0) {
                        $(value.selector).addClass(css_required_field);
                        aux = true;
                    }
                    break;

                case 'select':
                    if ($(value.selector).val() === "0") {
                        $(value.selector).next('.btn-group').children("button.dropdown-toggle").addClass(css_required_field);
                        aux = true;
                    }
                    break;

                default:
                    break;
            }

            if (aux) {
                errors.push(value.label + " is required.");
            }

        });
        if (errors.length > 0) {
            this.valid_form = false;
            var msg = '';
            $.each(errors, function (index, value) {
                msg += value + '<br>';
            });
            show_alert(msg);
        }
    }
}

// Events where data has changed
// Updated the product_form_validator object to get it ready to submit the form
$(document).on('change', ".form-control:not(.rbt-input, .multiselect-search, [type='file'])", function () {
    change_in_form = true;
    var new_val = $(this).val();
    var spec_name = $(this).attr('id')
    
    // Special handling for checkboxes
    if ($(this).attr('type') === 'checkbox') {
        new_val = $(this).is(':checked') ? '1' : '0';
    }
    
    console.log('form-control:not()...' + spec_name  + ' new value: ' + new_val + ' / ' + typeof (new_val));
    //console.log(new_val);
    $(this).removeClass(validator.css_required_field);
    // validator.edit is where the magic happens to set the hidden inputs
    validator.edit(spec_name, new_val);
});

$(document).on('change', '.multi-dropdown, .single-dropdown', function () {
    change_in_form = true;
    var formElem = $(this);
    
    var new_val = $(this).val();
    var spec_name = $(this).attr('name').replace('[]', '');
    console.log('.multi-dropdown... ' + spec_name + ' new value: ' + new_val + ' / ' + typeof (new_val));
    //console.log(new_val);
    $(this).next('.btn-group').children("button.dropdown-toggle").removeClass(validator.css_required_field);
    validator.edit(spec_name, new_val);
});

$('#nav-content').off('click').on('click', 'button.btnFormValidator', function (e) {
    if ($(this).attr('data-for') === 'product') {
        validator.val();
    }
})

/*

  SPECS VALIDATION

*/


/*
  Start
  product_form_spec.js
*/

// Initialize fileuploads if any
function initialize_fileupload_inputs() {
    $('#fileupload').fileupload({
        dataType: 'json',
        dropZone: null,
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                add_temp_url(file);
            });
        }
    });
}

// Set the view for the temporary file upload
function add_temp_url(file) {
    //var new_row = $("<li class='mx-3' style='min-width: 30px;'><a href='"+file.url+"' target='_blank'><i class='fa fa-file' aria-hidden='true'></i></a> <i class='fas fa-times-circle pull-right delete_temp_url' aria-hidden='true'></i> </li>");
    //$('#temp_url').append(new_row);
    var new_row = $("<tr><td><a href='" + file.url + "' target='_blank'><i class='fas fa-file' aria-hidden='true'></i> </a></td> <td><i class='fas fa-times-circle delete_temp_url' aria-hidden='true'></td></tr>");
    $('table#temp_url > tbody').prepend(new_row);


}

function clean_temp_url() {
    $('table#temp_url > tbody').html('');
}

// Resets the whole form
function reset_spec_form(state = 'new') { //state: new or edit
    $('#specForm').find('input:not( [type="radio"] ), select, textarea').each(function () {
        $(this).val('')
            .prop('checked', true);
    });
    $('#specForm').find('input[type="radio"], input#data_in_vendor_specsheet').each(function () {
        $(this).prop('checked', false);
    });
    clean_temp_url();

    $('#btnAddSS').children('span').html('Add new');
    show_alert(false, {wrap: '#spec-error-alert', container: '#spec-error-msg'});
}

/*
  
    Modals for 
    - Content
    - Content Back
    - Abrasion
    - Firecodes
    - Messaging
  
  */
// PKL Upload Trace
// Gets and pastes html view for the specs modal of 'this' product
$(document).on('click', '.specFolder', function () {

    console.log('form_validation.js jQuery on click  .specFolder called');

    var spectype = $(this).attr('data-for');
    var user_inputed = $('input#' + spectype + '_encoded').val();

    // Use the same modal for other purposes!
    /*
    if(spectype == 'history_prices'){
       var altUrl = btnHistoryModalUrl;
    }
    */
    $.ajax({
        method: "POST",
        url: btnSpecsModalUrl,
        dataType: 'json',
        data: {
            'product_id': product_id,
            'product_type': product_type,
            'spectype': spectype,
            'user_inputed': user_inputed
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        success: function (data, msg) {
            open_global_modal(data.html);
            initialize_fileupload_inputs();
        }
    });
})

function evaluate_add_button($max = 1) {
    var target = $('#btnSSCollapseForm');
    var count = $('table.modal-spec-content > tbody').children().length;
    if (count >= $max) {
        target.addClass('hide');
    } else {
        target.removeClass('hide');
    }
}

// Deletes the temp url
// Takes it out of the view and takes it out of the #files_encoded input
$(document).on('click', '.delete_temp_url', function () {
    var li = $(this).parent();
    show_swal(
        li,
        {},
        {
            complete: function (li) {
                // Treat the product files as exception since we are modifying the files directly
                if (li.is('td')) {
                    var url_to_delete = li.parent().children().children().attr('href');
                    //console.log(url_to_delete);
                    var aux = [];
                    if ($('#files_encoded').val() !== '') {
                        aux = JSON.parse($('#files_encoded').val());
                        aux = $.grep(aux, function (e) {
                            // Clean both paths for comparison (remove domain and filesystem prefix)
                            var clean_url = url_to_delete.replace(window.location.origin, '').replace('/opuzen-efs/prod/opms', '');
                            var clean_db_path = e.url_dir.replace('/opuzen-efs/prod/opms', '');
                            return (clean_url.indexOf(clean_db_path) === -1);
                        })
                    }
                    li.parent().remove();
                    $('#files_encoded').val(JSON.stringify(aux)).trigger('change');
                    $('#change_files_encoded').val('1');
                } else {
                    li.remove();
                }
            }
        }
    );


});

/*

  The information for contents, abrasion, firecodes, etc.
  needs to be stored somewhere before the form is submitted.
  Here are the controllers in charge.

*/


// Adds temporarily the information in the modal view

function add_new_spec_data(me) {
    //console.log('add_new_spec_data call');
    var validEntry = true;
    var msg = '';
    var spectype = $(me).attr('data-spectype');
    var table = $('.modal-spec-content:not(#list_files) > tbody');
    var table_files = $('#list_files.modal-spec-content > tbody');

    switch (spectype) {

        case 'content_f':
            var perc = parseFloat($('#new_perc').val());
            var content_id = $('#new_content_id').val();
            var content_name = $('#new_content_id').find("option[value='" + content_id + "']").html();
            var total = parseFloat($('#perc_total').html());

            if ($.isNumeric(perc) && (total + perc) >= 0 && (total + perc) <= 100) {
                var num_rows = table.children().length;

                // Add to view
                var new_row = "<tr data-perc='" + perc + "' data-content-id='" + content_id + "' data-content-name='" + content_name + "'><td>" + perc + "%</td><td>" + content_name + "</td><td><i class='fas fa-trash btnDeleteRow pull-right' aria-hidden='true' onclick='deleteRow(this)'></i></td></tr>";
                table.append(new_row);
                $('#perc_total').html(total + perc);

            } else {
                console.log('not-valid');
            }

            break;

        case 'content_b':
            var perc = parseFloat($('#new_perc').val());
            var content_id = $('#new_content_id').val();
            var content_name = $('#new_content_id').find("option[value='" + content_id + "']").html();
            var total = parseFloat($('#perc_total').html());
            if ($.isNumeric(perc) && (total + perc) >= 0 && (total + perc) <= 100) {
                var num_rows = table.children().length;

                // Add to view
                var new_row = "<tr data-perc='" + perc + "' data-content-id='" + content_id + "' data-content-name='" + content_name + "'><td>" + perc + "%</td><td>" + content_name + "</td><td><i class='fas fa-trash btnDeleteRow pull-right' aria-hidden='true' onclick='deleteRow(this)'></i></td></tr>";
                table.append(new_row);
                $('#perc_total').html(total + perc);

            } else {

                console.log('not-valid');
            }

            break;

        case 'abrasion':

            var abrasion_id = ($('#abrasion_id').val() !== '' ? $('#abrasion_id').val() : 'new-' + Math.floor((Math.random() * 1000) + 1).toString());
            var rubs = parseFloat($('#new_rubs').val());
            var abrasion_limit_id = $('input[type="radio"][name="new_abrasion_limit"]:checked').val();//$('#new_abrasion_limit').val();
            var abrasion_limit_name = $('input[type="radio"][name="new_abrasion_limit"]:checked').siblings().html();//$('#new_abrasion_limit').find("option[value='"+abrasion_limit_id+"']").html();
            var abrasion_test_id = $('input[type="radio"][name="new_abrasion_test"]:checked').val(); //$('#new_abrasion_test').val();
            var abrasion_test_name = $('input[type="radio"][name="new_abrasion_test"]:checked').siblings().html();//$('#new_abrasion_test').find("option[value='"+abrasion_test_id+"']").html();
            var abrasion_visible = ($('#new_abrasion_visible').prop('checked') ? 'Y' : 'N');
            var abrasion_visible_css = ($('#new_abrasion_visible').prop('checked') ? 'fa-eye text-success' : 'fa-eye-slash text-danger');
            var data_in_vendor_specsheet = ($('#data_in_vendor_specsheet').prop('checked') ? 'Y' : 'N');

            var special_cases = [1, 4, 5]; // 1: DOES NOT APPLY 4: ASK VENDOR 5: NOT TESTED
            // Validate
            var is_special_case = jQuery.inArray(parseInt(abrasion_test_id), special_cases) !== -1; // Different to -1 when found!
            if (!is_special_case) {
                if (!(rubs > 0)) {
                    validEntry = false;
                    msg += "The amount of rubs is required.<br>";
                }
                if (abrasion_limit_id === null || abrasion_limit_id === undefined) {
                    validEntry = false;
                    msg += "Abrasion limit is required.<br>";
                }
                if (abrasion_test_id === null || abrasion_test_id === undefined) {
                    validEntry = false;
                    msg += "Abrasion test is required.<br>";
                }

            }


            if (validEntry) {
                var files = ''; // Create files view
                $('table#temp_url > tbody > tr').each(function () {
                    var a = $(this).children('td').children('a').clone();
                    files += a.prop('outerHTML') + ' ';
                });

                // If is editing, we erase the old one
                table.children("tr[data-id='" + abrasion_id + "']").remove();

                var now = $.format.date(new Date(), "MM-dd-yyyy");
                // Add to view
                var new_row = "<tr data-id='" + abrasion_id + "' data-rubs='" + rubs + "' data-abrasion-limit-id='" + abrasion_limit_id + "' data-abrasion-limit-name='" + abrasion_limit_name + "' data-abrasion-test-id='" + abrasion_test_id + "' data-abrasion-test-name='" + abrasion_test_name + "' data-date-add='" + now + "' data-visible='" + abrasion_visible + "' data-in-vendor-specsheet='" + data_in_vendor_specsheet + "'>" +
                    "<td> <i class='fa " + abrasion_visible_css + "' aria-hidden='true'></i> </td>" +
                    "<td>" + now + "</td>" +
                    "<td id='files'>" + files + "</td>" +
                    "<td>" + abrasion_limit_name + "</td>" +
                    "<td>" + rubs + "</td>" +
                    "<td>" + abrasion_test_name + "</td>" +
                    "<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' aria-hidden='true'></i></td>" +
                    "<td class='align-middle'><i class='fas fa-trash btnDeleteRow pull-right' aria-hidden='true' onclick='deleteRow(this)'></i></td> " +
                    "</tr>";

                table.append(new_row);

                reset_spec_form('new');
                $('#specForm').collapse('hide');
            }
            break;

        case 'firecode':
            var firecode_id = ($('#firecode_id').val() !== '' ? $('#firecode_id').val() : 'new-' + Math.floor((Math.random() * 1000) + 1).toString());
            var firecode_test_id = $('#new_firecode').val();
            var firecode_test_name = $('#new_firecode').find("option[value='" + firecode_test_id + "']").html();
            var firecode_visible = ($('#new_firecode_visible').prop('checked') ? 'Y' : 'N');
            var firecode_visible_css = ($('#new_firecode_visible').prop('checked') ? 'fa-eye text-success' : 'fa-eye-slash text-danger');
            var data_in_vendor_specsheet = ($('#data_in_vendor_specsheet').prop('checked') ? 'Y' : 'N');

            if (firecode_test_id === null) {
                validEntry = false;
                msg += "Firecode is required.<br>";
            }

            if (validEntry) {
                var files = ''; // Create files view
                $('table#temp_url > tbody > tr').each(function () {
                    var a = $(this).children('td').children('a').clone();
                    files += a.prop('outerHTML') + ' ';
                });

                // If is editing, we erase the old one
                table.children("tr[data-id='" + firecode_id + "']").remove();

                var now = $.format.date(new Date(), "MM-dd-yyyy");
                // Add to view
                var new_row = "<tr data-id='" + firecode_id + "' data-firecode-test-id='" + firecode_test_id + "' data-firecode-test-name='" + firecode_test_name + "' data-in-vendor-specsheet='" + data_in_vendor_specsheet + "' data-date-add='" + now + "' data-visible='" + firecode_visible + "' >" +
                    "<td> <i class='fa " + firecode_visible_css + "'></i> </td>" +
                    "<td>" + now + "</td>" +
                    "<td id='files'>" + files + "</td>" +
                    "<td>" + firecode_test_name + "</td>" +
                    "<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' aria-hidden='true'></i></td>" +
                    "<td class='align-middle'><i class='fas fa-trash btnDeleteRow pull-right' aria-hidden='true' onclick='deleteRow(this)'></i></td> " +
                    "</tr>";

                table.append(new_row);

                reset_spec_form('new');
                $('#specForm').collapse('hide');
            }
            break;


        case 'product_messages':
        case 'item_messages':
            var message_id = ($('#message_id').val() !== '' ? $('#message_id').val() : 'new-' + Math.floor((Math.random() * 1000) + 1).toString());
            var message_note = $('#new_note').val();

            if (message_note.length === 0) {
                validEntry = false;
                msg += "The note is required.<br>";
            }

            if (validEntry) {
                var now = $.format.date(new Date(), "MM-dd-yyyy");
                // Add to view
                var new_row = "<tr data-message-id='" + message_id + "' data-date-add='" + now + "' data-user-id='" + user_id + "' data-edited='Y'>" +
                    "<td>" + now + "</td>" +
                    "<td>" + username + "</td>" +
                    "<td class='message'>" + message_note + "</td>" +
                    "<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' aria-hidden='true'></i></td>" +
                    //"<td class='align-middle'><i class='fa fa-trash btnDeleteRow pull-right' aria-hidden='true' onclick='deleteRow(this)'></i></td>" +
                    "</tr>";

                table.children("tr[data-message-id='" + message_id + "']").remove();
                table.prepend(new_row);

                if (spectype === 'product_messages') {

                } else if (spectype === 'item_messages') {
                    var arr = [];
                    var aux;

                    table.children("[data-edited='Y']").each(function () {
                        aux = {
                            id: $(this).attr('data-message-id'),
                            date_add: $(this).attr('data-date-add'),
                            message_note: $(this).children('.message').html(),
                            user_id: $(this).attr('data-user-id')
                        };
                        arr.push(aux);
                    });
                    $('#item_messages_encoded').val(JSON.stringify(arr)).trigger('change');
                }

                reset_spec_form('new');
                $('#specForm').collapse('hide');
            }
            break;


    }

    if (!validEntry) {
        show_alert(msg, {wrap: '#spec-error-alert', container: '#spec-error-msg'});
    } else {
        show_alert(false);
    }


}

/*
  Edit existing spec information for:
    - Abrasion
    - Finish
*/

$(document).on('click', '.btnEditSSRow', function () {
    var tr = $(this).parent().parent();
    //var purpose = '';

    if (typeof (tr.attr('data-firecode-test-id')) !== 'undefined') {
        // Firecode edit
        $('#firecode_id').val(tr.attr('data-id'));
        $('#new_firecode').val(tr.attr('data-firecode-test-id'));
        $('#new_firecode_visible').prop('checked', (tr.attr('data-visible') === 'Y'));
        $('#data_in_vendor_specsheet').prop('checked', (tr.attr('data-in-vendor-specsheet') === 'Y'));
    } else if (typeof (tr.attr('data-abrasion-test-id')) !== 'undefined') {
        // Abrasion edit
        $('#abrasion_id').val(tr.attr('data-id'));

        $('input[type="radio"][name="new_abrasion_limit"]#new_abrasion_limit_' + tr.attr('data-abrasion-limit-id')).prop('checked', true);
        //$('#new_abrasion_limit').val( tr.attr('data-abrasion-limit-id') );
        $('input[type="radio"][name="new_abrasion_test"]#new_abrasion_test_' + tr.attr('data-abrasion-test-id')).prop('checked', true);
        //$('#new_abrasion_test').val( tr.attr('data-abrasion-test-id') );

        $('#new_rubs').val(tr.attr('data-rubs'));
        $('#new_abrasion_visible').prop('checked', (tr.attr('data-visible') === 'Y'));
        $('#data_in_vendor_specsheet').prop('checked', (tr.attr('data-in-vendor-specsheet') === 'Y'));
    } else if (typeof (tr.attr('data-message-id')) !== 'undefined') {
        // Message edit
        $('#message_id').val(tr.attr('data-message-id'));
        $('#new_note').val(tr.children('.message').html());
    }

    clean_temp_url();
    tr.children('td#files').children('a').each(function () {
        add_temp_url({url: $(this).attr('href')});
    });

    $('#btnAddSS').children('span').html('Save edition');
    $('#specForm').collapse('show'); // collapse show form if needed
});


/* 
   Once user has finished inserting the data,
   they have to SUBMIT the modal so that the data is processed
   and stored in the frontend.
*/

$('#globalmodal').on('click', '#btnSubmitSS', function () {
    console.log('form_validation.js #btnSubmitSS call');
    var validEntry = true;
    var msg;
    var table = $('.modal-spec-content:not(#list_files) > tbody');
    var table_files = $('#list_files.modal-spec-content > tbody');
    var spectype = $(this).attr('data-spectype');
    var target = ''; // Data holder until is saved in the database

    var arr = [];
    var new_rows = '';
    var aux, data;
    var counter = 0;
    switch (spectype) {

        case 'content_f':

            table.children().each(function () {
                console.log($(this));
                counter += parseFloat($(this).attr('data-perc'));
                new_rows += "<li>" + $(this).attr('data-perc') + "% " + $(this).attr('data-content-name') + "</li>";
                aux = {
                    perc: $(this).attr('data-perc'),
                    name: $(this).attr('data-content-name'),
                    id: $(this).attr('data-content-id')
                };
                arr.push(aux);
            });

            if (counter !== 100 && counter !== 0) {
                validEntry = false;
                ;
                msg = "Total content must sum up 100%.";
            } else {
                $('ul#list_content_f').html('').append(new_rows);
            }

            break;

        case 'content_b':

            table.children().each(function () {
                counter += parseFloat($(this).attr('data-perc'));
                new_rows += "<li>" + $(this).attr('data-perc') + "% " + $(this).attr('data-content-name') + "</li>";
                aux = {
                    perc: $(this).attr('data-perc'),
                    name: $(this).attr('data-content-name'),
                    id: $(this).attr('data-content-id')
                };
                arr.push(aux);
            });

            if (counter !== 100 && counter !== 0) {
                validEntry = false;
                msg = "Total content must sum up 100%.";
            } else {
                $('ul#list_content_b').html('').append(new_rows);
            }

            break;

        case 'abrasion':
            var special_cases_ids = [1, 4, 5]; // Does not apply / Ask Vendor / Not tested
            $('ul#list_abrasion').html('');

            table.children().each(function () {
                data = {
                    id: $(this).attr('data-id'),
                    date_add: $(this).attr('data-date-add'),
                    rubs: $(this).attr('data-rubs'),
                    abrasion_limit_name: $(this).attr('data-abrasion-limit-name'),
                    abrasion_limit_id: $(this).attr('data-abrasion-limit-id'),
                    abrasion_test_name: $(this).attr('data-abrasion-test-name'),
                    abrasion_test_id: $(this).attr('data-abrasion-test-id'),
                    data_in_vendor_specsheet: $(this).attr('data-in-vendor-specsheet'),
                    files: [],
                    visible: $(this).attr('data-visible')
                };

                // Treat special cases for view !!!
                if ($.inArray(parseInt(data.abrasion_test_id), special_cases_ids) >= 0) {
                    new_row = data.abrasion_test_name;
                } else if ($.inArray(parseInt(data.abrasion_limit_id), [1]) >= 0) {
                    new_row = data.rubs + " " + data.abrasion_test_name;
                } else {
                    new_row = data.abrasion_limit_name + " @ " + data.rubs + " " + data.abrasion_test_name;
                }
                if (data.visible === 'Y') {
                    front = '<i class="fal fa-eye"></i>&nbsp;';
                } else {
                    front = '<i class="fal fa-minus"></i>&nbsp;';
                }

                // Each file
                let files_view = '&nbsp;'
                if ($(this).children('td#files').children('a').length > 0) {
                    $(this).children('td#files').children('a').each(function () {
                        //console.log($(this));
                        files_view += '&nbsp;<a href="' + $(this).attr('href') + '" target="_blank"><i class="fa fa-file"></i></a>';
                        data.files.push($(this).attr('href'));
                    });
                } else if (data.data_in_vendor_specsheet == 'Y') {
                    files_view = '&nbsp;&nbsp;<a href="#anchor-product-files"><i class="fas fa-level-down" data-toggle="tooltip" data-title="Data is in vendor specsheet"></i></a>';
                }
                // Insert
                $('ul#list_abrasion').append("<li>" + front + new_row + files_view + "</li>");

                arr.push(data);
            });

            $('#abrasion_encoded').val(JSON.stringify(arr));

            break;

        case 'firecode':
            $('ul#list_firecode').html('');
            table.children().each(function () {
                data = {
                    id: $(this).attr('data-id'),
                    date_add: $(this).attr('data-date-add'),
                    firecode_test_id: $(this).attr('data-firecode-test-id'),
                    firecode_test_name: $(this).attr('data-firecode-test-name'),
                    data_in_vendor_specsheet: $(this).attr('data-in-vendor-specsheet'),
                    files: [],
                    visible: $(this).attr('data-visible')
                };
                new_row = data.firecode_test_name;

                // Front
                if (data.visible === 'Y') {
                    front = '<i class="fal fa-eye"></i>&nbsp;';
                } else {
                    front = '<i class="fal fa-minus"></i>&nbsp;';
                }
                // Each file
                let files_view = '&nbsp;'
                if ($(this).children('td#files').children('a').length > 0) {
                    $(this).children('td#files').children('a').each(function () {
                        files_view += '&nbsp;<a href="' + $(this).attr('href') + '" target="_blank"><i class="fa fa-file"></i></a>';
                        data.files.push($(this).attr('href'));
                    });
                } else if ($(this).attr('data-in-vendor-specsheet') === 'Y') {
                    files_view = '&nbsp;&nbsp;<a href="#anchor-product-files"><i class="fas fa-level-down" data-toggle="tooltip" data-title="Data is in vendor specsheet"></i></a>';
                }

                // Insert
                $('ul#list_firecode').append("<li>" + front + new_row + files_view + "</li>");

                arr.push(data);
            });

            break;

        case 'product_messages':
            //var count = 0;

            table.children("[data-edited='Y']").each(function () {
                //count++;
                aux = {
                    id: $(this).attr('data-message-id'),
                    date_add: $(this).attr('data-date-add'),
                    message_note: $(this).children('.message').html(),
                    user_id: $(this).attr('data-user-id')
                };
                arr.push(aux);
            });
            var n = $('table.table.modal-spec-content > tbody').children('tr').length;
            $('span#cant_messages').html(n);
            break;

        default:

            break;
    }

    if (validEntry) {
        $('#' + spectype + '_encoded').val(JSON.stringify(arr)).trigger('change');
        $(global_modal_id).modal('hide');
    } else {
        show_alert(msg, {wrap: '#spec-error-alert', container: '#spec-error-msg'});
    }

})


/*




*/

$(document).on('change', ".col > select#category_files", function () {
    $("form#fileupload_product > input[name='category_file_upload']").val($(this).val());
});
      

      
      