<?php //echo "<pre>"; var_dump($info); echo "</pre>"; 
?>
<?php
// echo "<pre> DATA: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
// print_r($data);
// echo "isMultiEdit: (" . $isMultiEdit . ")<br />";
// echo "PROD TYPE(_product_type): " . $product_type . "<br />";
// echo "PROD TYPE(_info[product_type]): " . $info['product_type'] . "<br />";
// print_r($info);
// echo "</pre>";
// die();
?>
<?php $isNew = $item_id === '0'; ?>
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
    
    /* Web Visibility Readonly State */
    .web-vis-readonly {
        cursor: not-allowed !important;
    }
    
    .web-vis-readonly + label::after {
        content: " 🔒";
        font-size: 0.8em;
    }
</style>
<form id='frmItem' action='<?php echo site_url('item/save_item') ?>' class='row p-4'>

    <div class='col-12'>

        <div id='error-alert' class='alert alert-danger error-alert mx-auto hide'>
            <div class='d-flex justify-content-between'>
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                <h4>Oops!</h4>
                <i class="fa fa-times btnCloseAlert" aria-hidden="true"></i>
            </div>
            <div id='error-msg' class='my-1'>

            </div>
        </div>
    </div>

    <div class='col-12 mb-4 py-2 bg-danger text-white text-center <?php echo (!$isMultiEdit && !$isNew && $info['archived'] === 'Y' ? '' : 'hide') ?>'>
        <i class="fas fa-box-open"></i> Color has been
        archived.<?php if ($hasEditPermission) { ?> If you want to retrieve it, <u id='btnRetrieveItem'>click
            here</u>.<?php } ?>
    </div>

    <div class='col-12'>
        <div class='row'>
            <div class='col-6'>
                <a class="btn btn-secondary btnClose float-left" data-dismiss="modal"><i
                        class="far fa-window-close"></i> Close</a>
            </div>
            <div class='col-6'>
                <?php if ($hasEditPermission) { ?>
                    <?php if ($isMultiEdit) { ?>
                        <a class="btn btn-success float-right btnSave btnSaveItem" data-multi='yes'>Save for selection
                            <i class="far fa-square"></i></a>
                    <?php } else { ?>
                        <a class="btn btn-success float-right btnSave btnSaveItem" data-more="no">Save <i
                                class="far fa-square"></i></a>
                        <?php if ($isNew) { ?>
                            <a class="btn btn-info float-right btnSave btnSaveItem mr-4" data-more="yes">Save & New <i
                                    class="far fa-square"></i></a>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <h3 class='my-4'>
            <?php if (isset($info['product_name'])) { ?> <?php echo $info['product_name'] ?> / <?php echo $info['color'] ?><?php } ?>
                <?php if ($isMultiEdit) { ?> Edit Selection <?php } else if ($isNew) { ?> New Item <?php } else { ?><?php } ?>
                    <?php if (!$isNew) {
                        echo '<small style="float:right;font-size: 60%;"><b>id: ' . $item_id . '</b></small>';
                    } ?>
        </h3>

        <?php
        // echo 'hasEditPermission = '   . $info['hasEditPermission'] . '<br />';
        // echo 'hasMPLPermission = '    . $info['hasMPLPermission'] . '<br />';
        // echo 'hasMasterPermission = ' . $info['hasMasterPermission'] . '<br />';
        ?>
    </div>

    <input type='hidden' id='product_id' name='product_id' value='<?php echo $product_id ?>'>
    <input type='hidden' id='item_id' name='item_id' value='<?php echo $item_id ?>'>
    
    <!-- Required for saving changes to existing items -->
    <?php if (!$isNew) { ?>
        <input type='hidden' name='change_item' value='1'>
    <?php } ?>

    <div class="col-12">
        <div class="row">

            <?php if ($isMultiEdit) { ?>
                <div class='col-12 <?php echo ($hasMPLPermission ? '' : 'hide') ?>'>
                    <div class="form-group row">
                        <label for="in_master" class="col-6 col-form-label">In Master Price List</label>
                        <div class="col">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input form-control" name="in_master" id="in_master_yes"
                                    value='on'>
                                <label class="custom-control-label" for="in_master_yes">Yes</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input form-control" name="in_master" id="in_master_no"
                                    value='0'>
                                <label class="custom-control-label" for="in_master_no">No</label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class='col-12'>
                    <div class='form-group row'>
                        <label for="in_master" class="col-6 col-form-label">Master Price List</label>
                        <div class='col-3 col-form-label'>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input form-control" id="in_master" name="in_master" <?php echo (!$isNew && $info['in_master'] === '1' ? 'checked' : '') ?> <?php echo ($hasMPLPermission ? '' : 'disabled') ?>>
                                <label class="custom-control-label" for="in_master">Item</label>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input form-control" <?php echo (!$isNew && $info['product_in_master'] === '1' ? 'checked' : '') ?> disabled>
                                <label class="custom-control-label">Product</label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class='col-12'>
                <div class='form-group row'>
                    <label for="new_item_status" class="col-6 col-form-label">Status</label>
                    <div class="col">
                        <?php echo $dropdown_status ?>
                    </div>
                </div>
            </div>

            <div class='col-12'>
                <div class='form-group row'>
                    <label for="new_stock_status" class="col-6 col-form-label">Stock Status</label>
                    <div class="col">
                        <?php echo $dropdown_stock_status ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php if ($isMultiEdit) { ?>

        <div class='col-12'>
            <div class='form-group row'>
                <label for="min_order_qty" class="col col-form-label">Minimum Order Quantity</label>
                <div class="col">
                    <input type="text" class="form-control" id="min_order_qty" name='min_order_qty' value=''
                        placeholder="Minimum Order Quantity">
                </div>
            </div>
        </div>

        <div class='col-12'>
            <div class='form-group row'>
                <div class='col col-form-label'>
                </div>
                <div class='col text-right'>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="archive" name="archive">
                        <label class="custom-control-label" for="archive">Delete selection</label>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('select[name="dropdown_status"]').val('').multiselect('refresh');
                $('select[name="dropdown_stock_status"]').val('').multiselect('refresh');
            });
        </script>
    <?php } ?>
    <?php if (!$isMultiEdit) { ?>

        <?php if (isset($is_admin) && $is_admin === true) { ?>
        <div class='col-12'>
            <div class='form-group row'>
                <label for="new_code" class="col-6 col-form-label">Item #</label>
                <div class="col">
                    <!-- Enhanced item code input with security features -->
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="new_code" 
                               name='new_code'
                               value='<?php echo set_value('new_code', (isset($info['code']) ? $info['code'] : '')) ?>'
                               placeholder="nnnn-nnnn or nnnn-nnnnX"
                               pattern="^[0-9]{4}-[0-9]{4}[A-Za-z]?$"
                               title="Format: nnnn-nnnn with optional letter"
                               maxlength="10"
                               autocomplete="off"
                               <?php echo ($product_type === constant('Digital') ? ' disabled ' : ''); ?> >
                        
                        <?php if ($product_type !== constant('Digital')) { ?>
                            <!-- Generate button - dynamically shown/hidden based on field content -->
                            <div class="input-group-append" id="generateButtonContainer">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="btnGenerateCode"
                                        title="Generate random item code"
                                        style="display: none;">
                                    <i class="fas fa-random"></i> Generate
                                </button>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <!-- Validation feedback area -->
                    <div id="code-validation-feedback" class="invalid-feedback" style="display: none;"></div>
                    
                    <!-- Code change warning (bright orange alert) -->
                    <div id="code-change-warning" class="alert alert-warning" 
                         style="display: none; background-color: #ff8c00; border-color: #ff8c00; color: white; margin-top: 10px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><strong>⚠️ WARNING: Changing Item Code</strong></h6>
                                <p class="mb-2">
                                    Changing this item code might disrupt other systems that depend on the legacy code 
                                    (reports, exports, integrations, external references).
                                </p>
                                <div>
                                    <button type="button" class="btn btn-light btn-sm mr-2" id="btnAcknowledgeWarning">
                                        <i class="fas fa-check"></i> I Understand - Continue
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="btnCancelCodeChange">
                                        <i class="fas fa-times"></i> Cancel Change
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Help text for all users -->
                    <?php if (!$isMultiEdit) { ?>
                        <small class="form-text text-muted">
                            Format: 4 digits, dash, 4 digits, optional letter (e.g., 1234-5678 or 1234-5678A)
                        </small>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } else { ?>
        <!-- Non-admin users: Display read-only item code if it exists -->
        <?php if (isset($info['code']) && !empty($info['code'])) { ?>
        <div class='col-12'>
            <div class='form-group row'>
                <label for="item_code_display" class="col-6 col-form-label">Item #</label>
                <div class="col">
                    <input type="text" 
                           class="form-control" 
                           id="item_code_display"
                           value='<?php echo htmlspecialchars($info['code'], ENT_QUOTES, 'UTF-8'); ?>'
                           readonly
                           disabled>
                    <small class="form-text text-muted">
                        Item code is view-only. Contact an administrator to modify.
                    </small>
                </div>
            </div>
        </div>
        <?php } ?>
        <?php } ?>

        <div class='col-12'>
            <div class='form-group row'>
                <label for="" class="col-6 col-form-label">Color</label>
                <div class='col m-auto'>
                    <table id='colors-table' class="w-100">
                        <tbody>
                            <?php
                            if (count($colors) > 0) {
                                foreach ($colors as $c) {
                                    echo "<tr> <td>" . $c['name'] . "</td> <td> <i class='fa fa-trash pull-right' data-id='" . $c['id'] . "' onclick='deleteColor(this)'></i> </td> </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php } ?>
    <?php if (!$isMultiEdit and $hasEditPermission) { ?>

        <div class='col-12'>
            <div class='form-group row'>
                <label for="new_color" class="col-6 col-form-label">Select Colors</label>
                <i class="fas fa-question-circle" data-toggle='tooltip' data-trigger='hover'
                    data-title="In case the add new color button doesn't appear, you may add it with the plus icon on the right"
                    data-placement='top'></i>
                <div class="col">
                    <input type="text" class="form-control" id="new_color" name='new_color' placeholder="Color">
                </div>
                <div class='col-1'>
                    <i id='btnNewColor' class="fa fa-plus hide" aria-hidden="true"></i>
                </div>

            </div>
        </div>
    <?php } ?>

    <?php if (!$isMultiEdit) { ?>
        <!-- ============================================================
             COMMENTED OUT: Sales Management Sync UI Section
             Date: 2025-10-16
             Reason: Removing Sales App links per aiRemoveSalesApLinks branch
             Related: sales_m_searchbox, sales_id fields, and toggleSalesManagementSync()
             ============================================================ -->
        <!--
        <div class='col-12'>
            <div class='form-group row'>
                <label for="sales_id" class="col-6 col-form-label">
                    Sales Management Sync <i class="far <?php echo (($isNew or is_null($info['sales_id'])) ? "fa-toggle-off" : "fa-toggle-on") ?> " style="color:green" onclick="toggleSalesManagementSync(this)"></i>
                </label>
                <div class="col">
                    <?php if ($hasMasterPermission) { ?>
                        <input type="text" class="form-control" id="sales_m_searchbox" name='sales_m_searchbox' value='<?php echo $isNew ? '' : $info['sales_name'] ?>' placeholder="Search Sales Management items">
                        <input type="hidden" class="form-control" id="sales_id" name="sales_id" value="<?php echo $isNew ? '' : $info['sales_id'] ?>">
                    <?php
                    } else {
                        echo $isNew ? '' : $info['sales_name'];
                    } ?>
                </div>
            </div>
        </div>
        -->
    <?php } ?>

    <?php if (!$isMultiEdit && $hasMasterPermission) { ?>
        <div class='col-12'>
            <div class='form-group row'>
                <label for="reselections_id" class="col-6 col-form-label">
                    Select Reselections
                </label>
                <div class="col">
                    <input type="text" class="form-control" id="reselections_m_searchbox" name='reselections_m_searchbox' value='' placeholder="Search to add reselections">
                    <input type="hidden" class="form-control" id="reselections_ids" name="reselections_ids" value='<?php echo $isNew ? '[]' : $info['reselections_ids'] ?>'>
                </div>
            </div>
        </div>

        <div class='col-12'>
            <div class='form-group row'>
                <label for="" class="col-6 col-form-label">Reselections</label>
                <div class='col m-auto'>
                    <table id='reselections-table' class="w-100">
                        <tbody>
                            <?php
                            if (isset($info["reselections_items"]) && count($info["reselections_items"]) > 0) {
                                foreach ($info["reselections_items"] as $c) {
                                    echo "<tr> <td>" . $c['name'] . " / " . $c['code'] . " / " . $c['color'] . "</td> <td> <i class='fa fa-trash pull-right' data-id='" . $c['id'] . "' onclick='deleteReselection(this)'></i> </td> </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (isset($info["reselections_items_of"]) && count($info["reselections_items_of"]) > 0) { ?>
            <div class='col-12'>
                <div class='form-group row'>
                    <label for="" class="col-6 col-form-label">Reselections Of</label>
                    <div class='col m-auto'>
                        <table id='reselections-of-table' class="w-100">
                            <tbody>
                                <?php
                                foreach ($info["reselections_items_of"] as $c) {
                                    echo "<tr> <td>" . $c['name'] . " / " . $c['code'] . " / " . $c['color'] . "</td> <td> </td> </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>


    <hr class='w-100 <?php echo (!$isMultiEdit ? '' : 'hide') ?>'>

    <div class='col-12'>
        <?php if (!$isMultiEdit) { ?>
            <h3>
                Sampling Information
            </h3>
        <?php } ?>
        <div class='form-group row'>

            <div class='col-12'>
                <div class='form-group row'>

                    <label for="shelf_id" class="col-6 col-form-label">Shelf</label>
                    <div class="col-6">
                        <?php echo $dropdown_shelf ?>
                    </div>
                    <?php if ($isMultiEdit) { ?>
                        <div class='col-12 text-left'>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input form-control" id="clear_shelf"
                                    name="clear_shelf">
                                <label class="custom-control-label" for="clear_shelf">No shelf</label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class='col-12'>
                <div class='form-group row'>
                    <label for="new_bin_location" class="col-6 col-form-label">Bin Location</label>
                    <div class="col-6">
                        <?php echo $dropdown_bin_location ?>
                    </div>
                    <?php if ($isMultiEdit) { ?>
                        <div class='col-12 text-left'>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input form-control" id="clear_bin"
                                    name="clear_bin">
                                <label class="custom-control-label" for="clear_bin">Clear</label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if (!$isMultiEdit) { ?>
                <div class='col-12'>
                    <div class='form-group row'>
                        <label for="bin_quantity" class="col-6 col-form-label">Bin Quantity</label>
                        <div class="col">
                            <input type="number" class="form-control" id="bin_quantity" name='bin_quantity'
                                value='<?php echo set_value('bin_quantity', (isset($info['bin_quantity']) ? $info['bin_quantity'] : '')) ?>'
                                placeholder="Bin quantity">
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class='col-12'>
                <div class='form-group row'>
                    <label for="new_roll_location" class="col-6 col-form-label">Roll Location</label>
                    <div class="col-6">
                        <?php echo $dropdown_roll_location ?>
                    </div>
                    <?php if ($isMultiEdit) { ?>
                        <div class='col-12 text-left'>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input form-control" id="clear_roll"
                                    name="clear_roll">
                                <label class="custom-control-label" for="clear_roll">Clear</label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if (!$isMultiEdit) { ?>
                <div class='col-12'>
                    <div class='form-group row'>
                        <label for="roll_yardage" class="col-6 col-form-label">Roll Yardage</label>
                        <div class="col">
                            <input type="number" class="form-control" id="roll_yardage" name='roll_yardage'
                                value='<?php echo set_value('roll_yardage', (isset($info['roll_yardage']) ? $info['roll_yardage'] : '')) ?>'
                                placeholder="Roll Yardage">
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>

    <?php
    //echo "<pre> INFO: ";
    //echo "isMultiEdit: (" . $isMultiEdit . ")<br />";
    //echo "PROD TYPE: " . $product_type . "<br />";
    //print_r($info);
    //echo "</pre>";
    ?>



    <?php if (
        !$isMultiEdit and (
            (isset($info['product_type']) and $info['product_type'] === constant('Regular'))  ||
            (isset($product_type)         and $product_type         === constant('Regular'))
        )
    ) {
    ?>

        <hr class='w-100'>

        <div class='col-12'>
            <h3>
                Vendor Information
            </h3>
            <div class='form-group row'>
                <label for="min_order_qty" class="col col-form-label">Minimum Order Quantity</label>
                <div class="col">
                    <input type="text" class="form-control" id="min_order_qty" name='min_order_qty'
                        value='<?php echo $info['min_order_qty'] ?>' placeholder="Minimum Order Quantity">
                </div>
            </div>
            <div class='form-group row'>
                <label for="vendor_product_name" class="col col-form-label">Vendor Product Name</label>
                <div class="col">
                    <?php echo $info['vendor_product_name'] ?>
                </div>
            </div>
            <div class='form-group row'>
                <label for="vendor_code" class="col col-form-label">Vendor Item Code</label>
                <div class="col">
                    <input type="text" class="form-control" id="vendor_code" name='vendor_code'
                        value='<?php echo set_value('vendor_code', isset($info['vendor_code']) ? $info['vendor_code'] : '') ?>'
                        placeholder="Vendor Code">
                </div>
            </div>
            <div class='form-group row'>
                <label for="vendor_color" class="col col-form-label">Vendor Item Color</label>
                <div class="col">
                    <input type="text" class="form-control" id="vendor_color" name='vendor_color'
                        value='<?php echo set_value('vendor_color', isset($info['vendor_color']) ? $info['vendor_color'] : '') ?>'
                        placeholder="Vendor Color">
                </div>
            </div>
        </div>

    <?php } ?>
    <?php if (!$isMultiEdit) { ?>
        <hr class='w-100'>

        <div class='col-12'>
            <h3>
                Item Notes
                <button id='btnSSCollapseForm'
                    class='btn btn-light pull-right <?php echo ($hasEditPermission ? '' : 'hide') ?>' type='button'
                    data-toggle='collapse' data-target='#specForm' aria-expanded='false' aria-controls='specForm'>
                    <i class='fa fa-plus' aria-hidden='true'></i>
                </button>
            </h3>
        </div>

        <div class='collapse col-12' id='specForm'>
            <div class='card card-body'>

                <input type='hidden' name='message_id' id='message_id' value=''>

                <div class='form-group row'>
                    <label for='new_note' class='col-3 col-form-label'>New Note</label>
                    <div class='col'>
                        <textarea class='form-control' name='new_note' value='' cols="40" rows="5" id='new_note'
                            style='' />
                    </div>
                </div>

                <div class='form-group row'>
                    <div class='col'>
                        <button type='button' class='btn btn-link pull-left' onclick='reset_spec_form()' tabindex='-1'>
                            Reset
                        </button>
                        <button type='button' name='btnSS' id='btnAddSS <?php echo ($hasEditPermission ? '' : 'hide') ?>'
                            data-spectype='item_messages' class='btn pull-right' onClick='add_new_spec_data(this)'>
                            Add
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class='col-12 my-4'>
            <table id='item-notes-table' class='table modal-spec-content m-auto table-sm table-responsiveX' style=''
                cellpadding='4' cellspacing='0'>
                <tbody>
                    <?php echo $messages['tbody'] ?>
                </tbody>
                <tfoot>
                    <?php echo $messages['tfoot'] ?>
                </tfoot>
            </table>
        </div>

        <input type='hidden' id='item_messages_encoded' name='item_messages_encoded' class='form-control' value=''>
        <input type='hidden' id='color_ids' name='color_ids' class='form-control' value='<?php echo $colors_ids_encoded ?>'>
        <input type='hidden' id='color_names' name='color_names' value='<?php echo $colors_names_encoded ?>'>
    <?php } ?>

    <?php
    //echo "<pre> INFO: ";
    //echo "isMultiEdit: (" . $isMultiEdit . ")<br />";
    //echo "PROD TYPE(_product_type): " . $product_type . "<br />";
    //echo "PROD TYPE(_info[product_type]): " . $info['product_type'] . "<br />";
    //print_r($info);
    //echo "</pre>";
    ?>


    <?php if (!$isMultiEdit and $product_type === constant('Regular')) { ?>

        <hr class='w-100'>

        <div class='col-12'>
            <h3>
                Showcase / Website Information
                <?php 
                // Show parent product web visibility status
                $parent_vis_status = (isset($info['parent_product_visibility']) && $info['parent_product_visibility'] == 'Y');
                $parent_vis_icon = $parent_vis_status ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                $parent_vis_text = $parent_vis_status ? 'Parent product web visibility: ON' : 'Parent product web visibility: OFF';
                ?>
                <small class="text-muted" style="font-weight: normal; font-size: 0.8em;">
                    <i class="fas <?php echo $parent_vis_icon; ?>"></i>
                    <?php echo $parent_vis_text; ?>
                </small>
            </h3>
            <?php 
            // Determine if checkbox should be checked based on web_vis value
            // Manual override OFF: Use auto-calculated value
            // Manual override ON: Use stored web_vis value
            $is_manual_override = (isset($info['web_vis_toggle']) && $info['web_vis_toggle'] === '1');
            
            if ($is_manual_override) {
                // Manual mode: Use stored web_vis value from database
                $is_checked = (isset($info['web_vis']) && $info['web_vis'] === '1');
            } else {
                // Auto mode: Calculate based on THREE conditions
                // Item checkbox is CHECKED when ALL THREE are true:
                // 1. Item has images uploaded (pic_big_url OR pic_hd_url)
                // 2. Status is RUN, LTDQTY, or RKFISH
                // 3. Parent product has beauty shot (parent_product_visibility = "Y")
                $has_item_images = (!empty($info['pic_big_url']) || !empty($info['pic_hd_url']));
                $has_valid_status = (isset($info['status']) && in_array($info['status'], ['RUN', 'LTDQTY', 'RKFISH']));
                $parent_has_beauty_shot = (isset($info['parent_product_visibility']) && $info['parent_product_visibility'] == "Y");
                
                $is_checked = ($has_item_images && $has_valid_status && $parent_has_beauty_shot);
            }
            
            $checked = $is_checked ? 'checked' : '';
            ?>
            <div class="form-group row">
                <div class='col-5 col-form-label' style="display: flex;  align-items: anchor-center;    justify-content: space-between;">
                    <div class="custom-control custom-checkbox">
                        <?php 
                        // CRITICAL: Checkbox should be ENABLED whenever images exist
                        // Check if images are uploaded (check both $img_url and $info arrays)
                        $has_big_img = !empty($img_url['big']) || !empty($info['pic_big_url']);
                        $has_hd_img = !empty($img_url['hd']) || !empty($info['pic_hd_url']);
                        $has_images = ($has_big_img || $has_hd_img);
                        
                        // Check if manual override is enabled
                        $manual_override_enabled = (isset($info['web_vis_toggle']) && $info['web_vis_toggle'] === '1');
                        
                        // CRITICAL: Checkbox is ONLY disabled when NO images uploaded
                        // When images exist, checkbox is ALWAYS enabled (never disabled)
                        $is_disabled = !$has_images;
                        
                        // DEBUG CONFIRMED: Images detected correctly, checkbox enabled
                        // has_images=TRUE, is_disabled=FALSE when images exist
                        ?>
                        <input type="checkbox" 
                               class="custom-control-input form-control" 
                               id="web_vis" 
                               name="web_vis"
                               <?php echo $checked; ?>
                               <?php echo $is_disabled ? 'disabled' : ''; ?>>
                        <label class="custom-control-label" for="web_vis">
                            Web Visible
                            <?php if (!$manual_override_enabled && $has_images) { ?>
                                <i class="fas fa-lock text-muted" title="Auto-calculated - toggle manual override to edit"></i>
                            <?php } ?>
                        </label>
                        <?php if (!$has_images) { ?>
                            <small class="form-text text-danger d-block">Upload images to enable</small>
                        <?php } elseif (!$parent_vis_status) { ?>
                            <small class="form-text text-danger d-block">Parent product must be web visible to enable override</small>
                        <?php } elseif (!$manual_override_enabled) { ?>
                            <small class="form-text text-info d-block">Auto-calculated (toggle manual override to edit)</small>
                        <?php } ?>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <label class="switch" title="Manual Override">
                            <input type="checkbox" name="web_vis_toggle" id="web_vis_toggle" <?php echo (isset($info['web_vis_toggle']) && $info['web_vis_toggle'] === '1' ? 'checked' : '') ?> class="form-control" <?php echo (!$has_images || !$parent_vis_status) ? 'disabled' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                        <small class="form-text text-muted d-block">Manual Override</small>
                    </div>

                </div>
                <!-- <?php echo "<pre>";
                        print_r($info['web_vis']);
                        ?> -->
                <div class='col-7 text-right'>
                    <a class='' target='_blank' href='<?php echo $info['url_title'] ?>'><i class="far fa-browser"></i>
                        Website view</a>
                </div>
            </div>

            <table class='table table-sm'>
                <thead>
                    <tr>
                        <td>Thumbnail</td>
                        <td>Link</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Big (400x400)</td>
                        <td>
                            <div class='img-thumbnail-container'>
                                <a onclick="window.open( $(this).children().attr('src') )">
                                    <img id="img_pic_big_url" src="<?php echo $img_url['big'] ?>" class="img-thumbnail">
                                    <input type='hidden' class='form-control' id='pic_big_url' name='pic_big_url'
                                        value='<?php echo $img_url['big'] ?>'>
                                </a>
                            </div>
                        </td>
                        <td class='text-right'>
                            <?php if ($hasEditPermission) { ?>
                                <span class="btn btn-link" data-for="pic_big_url"
                                    onclick="javascript: upload_item_file(this);">
                                    <?php echo (!empty($img_url['big']) ? 'Replace Image' : 'Upload Image') ?>
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </span>
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <td>HD (~800x800)</td>
                        <td>
                            <div class='img-thumbnail-container'>
                                <a onclick="window.open( $(this).children().attr('src') )">
                                    <img id="img_pic_hd_url" src="<?php echo $img_url['hd'] ?>" class="img-thumbnail">
                                    <input type='hidden' class='form-control' id='pic_hd_url' name='pic_hd_url'
                                        value='<?php echo $img_url['hd'] ?>'>
                                </a>
                            </div>
                        </td>
                        <td class='text-right'>
                            <?php if ($hasEditPermission) { ?>
                                <span class="btn btn-link" data-for="pic_hd_url"
                                    onclick="javascript: upload_item_file(this);">
                                    <?php echo (!empty($img_url['hd']) ? 'Replace Image' : 'Upload Image') ?>
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </span>
                            <?php } ?>
                        </td>
                    </tr>

                </tbody>
            </table>


            <div class="form-group row">
                <label for="showcase_coord_color" class="col-2 col-form-label">Coord. Colors</label>
                <div class="col">
                    <?php echo $dropdown_showcase_coord_color ?>
                </div>
            </div>

        </div>

    <?php } ?>

    <hr class='w-100'>

    <?php if (!$isMultiEdit) { ?>
        <div class='col-12'>
            <h3>
                Item Presence <i class="fas fa-chevron-square-down" style='color:green;'
                    onclick='update_item_presence()'></i>
            </h3>
            <div id='item-presence-wrap'>
            </div>
        </div>
    <?php } ?>

    <?php if (!$isMultiEdit and !$limitedAction && $hasEditPermission) { ?>
        <div class='col-12'>
            <a id='btnArchiveItem' href='#'
                class='btn no-border btn-outline-danger float-right mr-4 <?php echo (!$isNew && $info['archived'] === 'N' ? '' : 'hide') ?>'><i
                    class="fas fa-archive"></i> Delete Color</a>
        </div>
    <?php } ?>

</form>

<form id='fileupload_product' action='<?php echo site_url('item/uploadToTemp') ?>' method='POST' enctype='multipart/form-data'>
    <input type='file' class='btn form-control' name='files' id='pfiles' style='display:none;'>
    <input type='hidden' class='btn form-control' name='category_id' id='category_id' style='display:none;'>
    <input type='hidden' class='btn form-control' name='category_name' id='category_name' style='display:none;'>
</form>

<script>
    // ============================================================================
    // OLD WEB VISIBILITY CODE REMOVED - Using new lazy calculation logic
    // See lines 1250-1281 for new implementation
    // ============================================================================
    $(document).ready(function() {

        validator.formID = '#frmItem';

        var colorTypeAheadOptions = {
            url: '<?php echo site_url('item/typeahead_colors') ?>',
            getValue: "label",
            list: {
                match: {
                    enabled: true
                },
                maxNumberOfElements: 100,
                onClickEvent: function() {
                    add_new_color($("#new_color").getSelectedItemData().id, $("#new_color").getSelectedItemData().label);
                },
                onLoadEvent: function() {
                    var n = $("#new_color").getItems().length;
                    if (n > 0) {
                        // Results available
                        $('#btnNewColor').addClass('hide');
                    } else {
                        // No Results available
                        $('#btnNewColor').removeClass('hide');
                    }
                }
            },
            cssClasses: 'w-100',
            ajaxSettings: {
                dataType: "json",
                method: "POST",
                data: {
                    dataType: "json"
                }
            },
            preparePostData: function(data) {
                data.query = $("#new_color").val();
                data.item_id = $('#item_id').val();
                data.color_ids_selected = $('#color_ids').val();
                return data;
            },
            requestDelay: 500
        };
        $("#new_color").easyAutocomplete(colorTypeAheadOptions);

        /* ============================================================
           COMMENTED OUT: Sales Management Sync Searchbox Initialization
           Date: 2025-10-16
           Reason: Removing Sales App links per aiRemoveSalesApLinks branch
           Related: Initializes easyAutocomplete for sales_m_searchbox field
           ============================================================ */
        /*
        // Sales Management sync search box
        var salesSyncSearchboxId = "#sales_m_searchbox";
        var salesSearchBoxTypeAheadOptions = {
            url: '<?php echo site_url('item/typeahead_sales_sync') ?>',
            getValue: "label",
            list: {
                match: {
                    enabled: true
                },
                maxNumberOfElements: 10,
                onClickEvent: function() {
                    const sales_id = $(salesSyncSearchboxId).getSelectedItemData().id;
                    //const sales_id = $("#sales_m_searchbox").getSelectedItemData().id;
                    $("#sales_id").val(sales_id).trigger("change");
                },
                // onLoadEvent: function () {
                // }
            },
            cssClasses: 'w-100',
            ajaxSettings: {
                dataType: "json",
                method: "POST",
                data: {
                    dataType: "json"
                }
            },
            preparePostData: function(data) {
                data.query = $(salesSyncSearchboxId).val();
                data.code = $("#new_code").val();
                return data;
            },
            requestDelay: 500
        };
        $("#sales_m_searchbox").easyAutocomplete(salesSearchBoxTypeAheadOptions);
        $(salesSyncSearchboxId).easyAutocomplete(salesSearchBoxTypeAheadOptions);
        */

        // Reselection search box
        var reselectionSearchboxId = "#reselections_m_searchbox";
        var reselectionPostInputId = "#reselections_ids";
        var reselectionMaxNumberOfElements = 25;
        var reselectionSearchBoxTypeAheadOptions = {
            url: '<?php echo site_url('product/typeahead_products_list') ?>',
            getValue: "label",
            list: {
                match: {
                    enabled: true
                },
                maxNumberOfElements: reselectionMaxNumberOfElements,
                onClickEvent: function() {
                    add_new_reselection($(reselectionSearchboxId).getSelectedItemData(), reselectionPostInputId, reselectionSearchboxId);
                },
                // onLoadEvent: function () {
                // }
            },
            cssClasses: 'w-100',
            ajaxSettings: {
                dataType: "json",
                method: "POST",
                data: {
                    dataType: "json"
                }
            },
            preparePostData: function(data) {
                data.query = $(reselectionSearchboxId).val();
                data.limit = reselectionMaxNumberOfElements;
                data.itemsOnly = true;
                // data.code = $("#new_code").val();
                return data;
            },
            requestDelay: 500
        };
        $(reselectionSearchboxId).easyAutocomplete(reselectionSearchBoxTypeAheadOptions);

        $('#fileupload_product').fileupload({
            dataType: 'json',
            dropZone: null,
            formData: function(e, data) {
                return [{
                        name: 'category_id',
                        value: $('#category_id').val()
                    },
                    {
                        name: 'category_name',
                        value: $('#category_name').val()
                    }
                ]
            },
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    console.log(file)
                    if (file.category_id === '0') {
                        // Beauty shot uploaded
                        $('#img_' + file.category_name).attr('src', file.url);
                        $('#' + file.category_name).val(file.url).trigger('change');
                    } else {
                        //add_file_list(file);
                    }
                });
            }
        });

    })

    /* ============================================================
       COMMENTED OUT: toggleSalesManagementSync() Function
       Date: 2025-10-16
       Reason: Removing Sales App links per aiRemoveSalesApLinks branch
       Related: Toggle function for Sales Management Sync feature
       ============================================================ */
    /*
    function toggleSalesManagementSync(obj) {
        const jobj = $(obj);
        if (jobj.attr('class').includes('toggle-on')) {
            // Turn it off
            $("#sales_id").val(null).trigger('change');
            $("#sales_m_searchbox").val("");
            const sales_id = $("#sales_id").val();

            jobj.removeClass("fa-toggle-on");
            jobj.addClass("fa-toggle-off");
            console.log(jobj, sales_id);

        } else {
            // To turn it on, choose an item from the dropdown
            show_swal({}, {
                title: 'To turn on the sync, use the "Search Sales Management items" field to search and select an item'
            }, {
                complete: function() {
                    $("#sales_m_searchbox").focus();
                }
            })
        }

    }
    */


    function add_new_reselection(selectedData, reselectionPostInputId, reselectionSearchboxId) {
        // var selectedData = ;
        console.log(selectedData);
        var value = selectedData.id;
        var label = selectedData.label;
        [id, type] = value.split('-');
        let item_id = id;
        // console.log([product_type, product_id, item_id]);

        let currentIds = JSON.parse($(reselectionPostInputId).val().replace(/&quot;/g, '"'));
        console.log("CurrentIDs", currentIds);
        if (currentIds.length >= 64) {
            show_swal({}, {
                title: "You can only add up to 64 reselections"
            })
            return;
        } else if (currentIds.includes(item_id)) {
            console.log("Already exists");
            return;
        } else {
            currentIds.push(item_id);
            $(reselectionPostInputId).val(JSON.stringify(currentIds)).trigger("change");
            $(reselectionSearchboxId).val("");
            add_new_reselection_to_view(item_id, label);
        }
    }

    function add_new_reselection_to_view(id, name) {
        var row = "<tr> <td>" + name + "</td> <td> <i class='fa fa-trash pull-right' data-id='" + id + "' onclick='deleteReselection(this)'></i> </td> </tr>";
        $('#reselections-table>tbody').append(row);
    }


    function add_new_color(new_color_id, new_color_name) {
        var current_color_ids = JSON.parse($('#color_ids').val());
        var current_color_names = JSON.parse($('#color_names').val());
        var index = current_color_ids.indexOf(new_color_id);
        if (index === -1) { // Check existence
            current_color_ids.push(new_color_id);
            current_color_names.push(new_color_name);
            $('#color_ids').val(JSON.stringify(current_color_ids)).trigger('change');
            $('#color_names').val(JSON.stringify(current_color_names));
            add_new_color_to_view(new_color_id, new_color_name);
            $('#new_color').val('');
        }
    }

    function add_new_color_to_view(id, name) {
        var row = "<tr> <td>" + name + "</td> <td> <i class='fa fa-trash pull-right' data-id='" + id + "' onclick='deleteColor(this)'></i> </td> </tr>";
        $('#colors-table>tbody').append(row);
    }

    $('form#frmItem').on('click', '#btnNewColor', function() {
        var target = $('input#new_color');
        if (target.val().length > 0) {
            var new_color_id = 'new-' + Math.floor(Math.random() * 1000);
            var new_color_name = $('#new_color').val();
            add_new_color(new_color_id, new_color_name);
        }
    })

    $('form#frmItem').on('click', '#btnArchiveItem', function() {
        show_swal({}, {
            title: 'Are you sure you want to delete this item?'
        }, {
            complete: function() {
                $.ajax({
                    method: "POST",
                    url: '<?php echo site_url('item/archive_item') ?>',
                    dataType: 'json',
                    data: {
                        item_id: $('input#item_id').val()
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                    },
                    success: function(data, msg) {
                        //console.log(data);

                        if (data.success === true) {
                            // Update datatables
                            // data.item_id;
                            show_success_swal();
                            remove_item_from_view(data.item_id);
                            $('.modal#globalmodal').modal('hide');
                        } else {
                            // Some error ocurred
                            $('#frmItem').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
                        }

                    }
                });
            }
        });
    })

    $('form#frmItem').on('click', '#btnRetrieveItem', function() {
        show_swal({}, {
            title: 'Are you sure you want to retrieve this item?'
        }, {
            complete: function() {
                $.ajax({
                    method: "POST",
                    url: '<?php echo site_url('item/retrieve_item') ?>',
                    dataType: 'json',
                    data: {
                        item_id: $('input#item_id').val()
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                    },
                    success: function(data, msg) {

                        if (data.success === true) {
                            // Update datatables
                            // data.item_id;
                            show_success_swal();
                            //update_item_in_view(data.item_id, this_table);
                            $('.modal#globalmodal').modal('hide');
                        } else {
                            // Some error ocurred
                            $('#frmItem').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
                        }

                    }
                });
            }
        });
    })

    $('form#frmItem').on('click', '.btnSaveItem', function() {
        var add_another = ($(this).attr('data-more') === 'yes');
        var multiedit = ($(this).attr('data-multi') === 'yes');
        var batch = [];
        var aux;

        if (multiedit) {
            // Get IDs for rows selected
            var excessItems = false;
            $.each(this_table.rows('.selected').data(), function(index, value) {
                if (batch.length > 10) excessItems = true;
                batch.push(value.item_id);
            });
            $('input#item_id').val(JSON.stringify(batch));
        }

        $.ajax({
            method: "POST",
            url: $('#frmItem').attr('action'),
            dataType: 'json',
            data: $('#frmItem').serialize(),
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            beforeSend: function(data, msg) {
                if (excessItems) $('.full-loader').removeClass('hide');
            },
            success: function(data, msg) {
                console.log('item/form/view.php 971  data: ', data);
                $('.full-loader').addClass('hide');
                
                // TEMP DEBUG: Show debug info from server
                if (data.debug_info) {
                    console.warn('SERVER DEBUG INFO:', data.debug_info);
                    // Show alert for critical debugging
                    if (data.debug_info.includes('NOT saved')) {
                        alert('DEBUG: ' + data.debug_info);
                    }
                }
                
                if (data.success === true) {
                    console.log('common.js 985 ajax response data: ', data, msg);
                    if (multiedit) {
                        $.each(data.items, function(index, value) {
                            update_item_in_view(value, this_table);
                        });
                    } else {
                        // Update datatables
                        update_item_in_view(data.item, this_table);
                    }
                    show_success_swal();
                    if (add_another) {
                        //open_item_modal();
                        reset_item_form();
                    } else {
                        $('.modal#globalmodal').modal('hide');
                        
                        // ====================================================================
                        // REFRESH DATATABLE AFTER SAVE (for web_vis and other field updates)
                        // ====================================================================
                        // Reload entire DataTable from server to get fresh calculated values
                        setTimeout(function() {
                            if (typeof this_table !== 'undefined' && this_table !== null) {
                                console.log('🔄 Reloading DataTable after item save...');
                                this_table.ajax.reload(null, false); // false = stay on same page
                                console.log('✓ DataTable reloaded successfully');
                            }
                        }, 300); // Brief delay for modal close animation
                        // ====================================================================
                    }
                } else {
                    // Some error ocurred
                    $('#frmItem').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
                }

            }
        });
    });

    function update_item_presence() {
        var item_id = $('input#item_id').val();
        if ($('#item-presence-wrap').children().length == 0) {
            var data = {
                item_id: $('input#item_id').val()
            };
            $('#item-presence-wrap').load('<?php echo site_url('item/get_item_presence') ?>', data);
        }
    }

    function deleteColor(me) {
        var this_row = $(me);
        show_swal(
            this_row, {}, {
                complete: function(this_row) {
                    var item_id = this_row.attr('data-id');
                    var current_color_names = jQuery.parseJSON($('#color_names').val());
                    var current_color_ids = jQuery.parseJSON($('#color_ids').val());
                    var index = current_color_ids.indexOf(item_id);
                    if (index > -1) {
                        current_color_ids.splice(index, 1);
                        current_color_names.splice(index, 1);
                    }
                    $('#color_ids').val(JSON.stringify(current_color_ids)).trigger('change');
                    $('#color_names').val(JSON.stringify(current_color_names));
                    this_row.closest('tr').remove();
                }
            }
        );
    }

    function deleteReselection(me) {
        var this_row = $(me);
        show_swal(
            this_row, {}, {
                complete: function(this_row) {
                    var targetInput = "#reselections_ids";
                    var item_id = this_row.attr('data-id');
                    // var current_color_names = jQuery.parseJSON($('#color_names').val());
                    var current_ids = jQuery.parseJSON($(targetInput).val());
                    var index = current_ids.indexOf(item_id);
                    if (index > -1) {
                        current_ids.splice(index, 1);
                    }
                    $(targetInput).val(JSON.stringify(current_ids)).trigger('change');
                    this_row.closest('tr').remove();
                }
            }
        );
    }

    function reset_item_form() {
        var to_reset_jsons = ["color_ids", "color_names"];
        var to_reset_input = ["item_messages_encoded", "vendor_color", "vendor_code", "min_order_qty", "new_color", "new_code"];
        var to_reset_table = ["colors-table", "item-notes-table"];
        //var to_reset_select = ["[name='dropdown_status']", "[name='dropdown_stock_status']"];
        $.each(to_reset_jsons, function(index, value) {
            $('#' + value).val('[]');
        });
        $.each(to_reset_input, function(index, value) {
            $('#' + value).val(function() {
                this.defaultValue;
            })
        });
        $.each(to_reset_table, function(index, value) {
            $('#' + value + ' > tbody').html('');
        });
        /*
	$.each( to_reset_select, function(index, value){
	  $('select'+value).val(1).multiselect('refresh');
	})
		*/
    }

    if ($('[data-toggle="tooltip"]').length > 0) {
        $('[data-toggle="tooltip"]').tooltip('update');
    }

    function upload_item_file(me) {
        var datafor = $(me).attr('data-for');
        switch (datafor) {
            /*
		case 'pfiles':
			$('#pfiles').attr('multiple', 1).attr('name', 'files[]');
			var category_id = $("select#category_files").val();
			var category_name = $("select#category_files").children("option[value='" + category_id + "']").html();
			$('#category_id').val( category_id );
			$('#category_name').val( category_name );
			break;
		*/
            case 'pic_big_url':
            case 'pic_hd_url':
                $('#pfiles').attr('multiple', null).attr('name', 'files');
                $('#category_id').val('0');
                $('#category_name').val(datafor);
                break;
        }
        $('#pfiles').trigger('click');
    }

    // ============================================================================
    // WEB VISIBILITY MANUAL OVERRIDE TOGGLE HANDLER
    // ============================================================================
    $(document).on('change', '#web_vis_toggle', function() {
        const isEnabled = $(this).is(':checked');
        const $webVisCheckbox = $('#web_vis');
        const $label = $webVisCheckbox.next('label');
        
        if (isEnabled) {
            // Manual override enabled - remove lock icon
            $label.find('.fa-lock').remove();
        } else {
            // Manual override disabled - add lock icon if not present
            if ($label.find('.fa-lock').length === 0) {
                $label.append(' <i class="fas fa-lock text-muted" title="Auto-calculated - toggle manual override to edit"></i>');
            }
        }
    });
    
    // Handle web_vis checkbox clicks when in auto-calculated mode
    $(document).on('click', '#web_vis', function(e) {
        const $toggle = $('#web_vis_toggle');
        const manualOverrideOn = $toggle.is(':checked');
        
        if (!manualOverrideOn) {
            // In auto-calculated mode - prevent changes
            e.preventDefault();
            const currentState = $(this).is(':checked');
            $(this).prop('checked', currentState); // Keep current state
            return false;
        }
    });
    
    // Ensure product_id is maintained when item code changes
    $(document).ready(function() {
        // Store the original product_id value
        var originalProductId = $('#product_id').val();

        // When the item code changes, ensure we don't lose product_id information
        $('#new_code').on('change', function() {
            // Check if product_id is still valid (contains a hyphen)
            var currentProductId = $('#product_id').val();
            if (currentProductId.indexOf('-') === -1 || currentProductId === '0') {
                // Restore the original product_id if it was lost
                $('#product_id').val(originalProductId);
                console.log('Restored product_id to: ' + originalProductId);
            }
        });
    });

    /**
     * ITEM CODE GENERATION AND VALIDATION FUNCTIONALITY
     * 
     * SECURITY FEATURES:
     * - Client-side validation (complementing server-side)
     * - CSRF token handling via existing form infrastructure
     * - Input sanitization and length limits
     * - Admin privilege UI enforcement
     * - Rate limiting via button disable states
     * - No sensitive data exposure in console logs
     */
    
    // Configuration - passed securely from PHP (prevent redeclaration)
    if (typeof window.itemCodeConfig === 'undefined') {
        window.itemCodeConfig = {};
    }
    
    // Update configuration for current modal instance
    window.itemCodeConfig = {
        isAdmin: <?php echo json_encode(isset($is_admin) && $is_admin === true); ?>,
        isNew: <?php echo json_encode($isNew); ?>,
        isDigital: <?php echo json_encode($product_type === constant('Digital')); ?>,
        generateUrl: '<?php echo site_url('item/generate_item_code'); ?>',
        validateUrl: '<?php echo site_url('item/validate_item_code'); ?>',
        currentItemId: <?php echo json_encode($item_id); ?>,
        // Product type protection
        originalProductId: '<?php echo addslashes($product_id ?? ''); ?>',
        originalProductType: '<?php echo addslashes($product_type ?? ''); ?>',
        // Code change warning system
        originalCode: '<?php echo addslashes(isset($info['code']) ? trim($info['code']) : ''); ?>',
        warningAcknowledged: false,
        fieldLocked: false
    };

    // Initialize item code functionality when DOM is ready
    $(document).ready(function() {
        initItemCodeFeatures();
    });

    function initItemCodeFeatures() {
        const $codeInput = $('#new_code');
        const $generateBtn = $('#btnGenerateCode');
        const $feedback = $('#code-validation-feedback');
        
        if (!$codeInput.length || window.itemCodeConfig.isDigital) {
            return; // Skip if input doesn't exist or is digital product
        }

        // Bind event handlers
        bindGenerateCodeHandler($generateBtn, $codeInput);
        bindCodeValidationHandler($codeInput, $feedback);
        bindDynamicButtonVisibility($codeInput, $generateBtn);
        bindCodeChangeWarningSystem($codeInput, $generateBtn);
        
        // Initial setup
        updateGenerateButtonVisibility($codeInput, $generateBtn);
        
        // Initial validation if field has value
        if ($codeInput.val().trim()) {
            validateItemCodeAsync($codeInput.val().trim(), $feedback);
        }
    }

    /**
     * Bind dynamic button visibility based on field content
     */
    function bindDynamicButtonVisibility($codeInput, $generateBtn) {
        if (!$generateBtn.length) {
            return;
        }

        // Show/hide button as user types or changes field
        $codeInput.on('input keyup paste', function() {
            updateGenerateButtonVisibility($codeInput, $generateBtn);
        });

        // Also check on focus/blur events
        $codeInput.on('focus blur', function() {
            updateGenerateButtonVisibility($codeInput, $generateBtn);
        });
    }

    /**
     * Update generate button visibility based on current field value and warning state
     */
    function updateGenerateButtonVisibility($codeInput, $generateBtn) {
        const currentValue = $codeInput.val().trim();
        
        if (currentValue === '' && window.itemCodeConfig.warningAcknowledged) {
            // Field is empty AND warning acknowledged - show generate button
            $generateBtn.show();
        } else if (currentValue === '' && window.itemCodeConfig.originalCode === '') {
            // Field is empty AND was originally empty (new item or blank item) - show generate button
            $generateBtn.show();
        } else {
            // Field has content OR warning not acknowledged - hide generate button
            $generateBtn.hide();
        }
    }

    /**
     * Bind code change warning system for existing items with codes
     */
    function bindCodeChangeWarningSystem($codeInput, $generateBtn) {
        const $warning = $('#code-change-warning');
        const $acknowledgeBtn = $('#btnAcknowledgeWarning');
        const $cancelBtn = $('#btnCancelCodeChange');
        
        // Only apply warning system to existing items with codes
        if (!window.itemCodeConfig.originalCode || window.itemCodeConfig.originalCode === '') {
            return; // Skip for new items or items without codes
        }

        // Bind warning acknowledgment
        $acknowledgeBtn.on('click', function() {
            window.itemCodeConfig.warningAcknowledged = true;
            window.itemCodeConfig.fieldLocked = false;
            $warning.slideUp();
            
            // CRITICAL: Ensure change_item flag is set for existing items when code changes
            // This tells the server to save the modified item data
            if (!window.itemCodeConfig.isNew) {
                // Add or update the change_item hidden field
                let $changeItemField = $('input[name="change_item"]');
                if ($changeItemField.length === 0) {
                    // Field doesn't exist - create it
                    $('#frmItem').append('<input type="hidden" name="change_item" value="1">');
                } else {
                    // Field exists - ensure it's set to 1
                    $changeItemField.val('1');
                }
                console.info('change_item flag set for code modification');
            }
            
            // Update button visibility after acknowledgment
            updateGenerateButtonVisibility($codeInput, $generateBtn);
            
            // Log warning acknowledgment
            console.info('Code change warning acknowledged by user');
        });

        // Bind cancel action
        $cancelBtn.on('click', function() {
            // Restore original code
            $codeInput.val(window.itemCodeConfig.originalCode);
            window.itemCodeConfig.warningAcknowledged = false;
            window.itemCodeConfig.fieldLocked = false;
            $warning.slideUp();
            
            // Reset validation state
            resetValidationState($codeInput, $('#code-validation-feedback'));
            updateGenerateButtonVisibility($codeInput, $generateBtn);
            
            console.info('Code change cancelled - restored original code');
        });

        // Monitor for code changes that should trigger warning
        $codeInput.on('input', function() {
            const currentValue = $(this).val().trim();
            
            // Show warning if user is modifying an existing code
            if (window.itemCodeConfig.originalCode && 
                currentValue !== window.itemCodeConfig.originalCode && 
                !window.itemCodeConfig.warningAcknowledged) {
                
                // Lock the field temporarily
                window.itemCodeConfig.fieldLocked = true;
                $warning.slideDown();
                
                // Hide generate button until warning is resolved
                $generateBtn.hide();
                
                console.warn('Code change detected - showing warning');
            } else if (currentValue === window.itemCodeConfig.originalCode) {
                // User restored original code - hide warning
                $warning.slideUp();
                window.itemCodeConfig.fieldLocked = false;
            }
        });
    }

    /**
     * Bind generate code button click handler
     * Updated to work for both new items and existing items with blank codes
     */
    function bindGenerateCodeHandler($generateBtn, $codeInput) {
        if (!$generateBtn.length) {
            return; // Button will only exist if conditions are met (new item OR blank code)
        }

        $generateBtn.on('click', function(e) {
            e.preventDefault();
            generateUniqueItemCode($generateBtn, $codeInput);
        });
    }

    /**
     * Bind real-time code validation handler with change_item flag management
     */
    function bindCodeValidationHandler($codeInput, $feedback) {
        let validationTimeout;
        
        $codeInput.on('input', function() {
            const value = $(this).val().trim();
            
            // CRITICAL: Set change_item flag when user modifies code in existing items
            if (!window.itemCodeConfig.isNew && value !== window.itemCodeConfig.originalCode) {
                ensureChangeItemFlagSet();
            }
            
            // Clear previous timeout
            clearTimeout(validationTimeout);
            
            // Reset visual state
            resetValidationState($codeInput, $feedback);
            
            if (!value) {
                return;
            }
            
            // Debounce validation to avoid excessive API calls
            validationTimeout = setTimeout(function() {
                validateItemCodeAsync(value, $feedback);
            }, 500);
        });
        
        // Also validate on blur for immediate feedback
        $codeInput.on('blur', function() {
            const value = $(this).val().trim();
            
            // Set change_item flag on blur if code changed
            if (!window.itemCodeConfig.isNew && value !== window.itemCodeConfig.originalCode) {
                ensureChangeItemFlagSet();
            }
            
            if (value) {
                clearTimeout(validationTimeout);
                validateItemCodeAsync(value, $feedback);
            }
        });
    }

    /**
     * Ensure change_item flag is set for existing items when code is modified
     * This is CRITICAL for saving code changes to existing items
     */
    function ensureChangeItemFlagSet() {
        let $changeItemField = $('input[name="change_item"]');
        if ($changeItemField.length === 0) {
            // Field doesn't exist - create it
            $('#frmItem').append('<input type="hidden" name="change_item" value="1">');
            console.info('change_item flag created for code modification');
        } else {
            // Field exists - ensure it's set to 1
            $changeItemField.val('1');
            console.info('change_item flag updated for code modification');
        }
    }

    /**
     * Generate unique item code via AJAX with product_type corruption protection
     * Security: Uses existing CSRF protection from form
     * Protection: Validates and preserves product_type integrity
     */
    function generateUniqueItemCode($generateBtn, $codeInput) {
        // Prevent multiple simultaneous requests
        if ($generateBtn.prop('disabled')) {
            return;
        }
        
        // Store current form state before AJAX call for corruption detection
        const preAjaxState = {
            productId: $('#product_id').val(),
            productType: window.itemCodeConfig.originalProductType,
            timestamp: new Date().toISOString()
        };
        
        // UI feedback
        $generateBtn.prop('disabled', true)
                   .html('<i class="fas fa-spinner fa-spin"></i> Generating...');
        
        $.ajax({
            url: window.itemCodeConfig.generateUrl,
            type: 'POST',
            dataType: 'json',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                // Validate product_type integrity after AJAX call
                validateProductTypeIntegrity(preAjaxState, 'generate_success');
                
                if (response.success && response.code) {
                    $codeInput.val(response.code);
                    $codeInput.addClass('is-valid').removeClass('is-invalid');
                    showValidationMessage('Generated unique code successfully!', 'success');
                    
                    // Update button visibility after code is generated
                    updateGenerateButtonVisibility($codeInput, $generateBtn);
                    
                    // Final integrity check after code insertion
                    setTimeout(function() {
                        validateProductTypeIntegrity(preAjaxState, 'post_code_insert');
                    }, 100);
                } else {
                    showValidationMessage(response.message || 'Failed to generate code', 'error');
                }
            },
            error: function(xhr, status, error) {
                // Check for corruption even on error
                validateProductTypeIntegrity(preAjaxState, 'generate_error');
                
                // Security: Don't expose internal error details
                let message = 'Unable to generate code. Please try again.';
                
                if (status === 'timeout') {
                    message = 'Request timed out. Please try again.';
                }
                
                showValidationMessage(message, 'error');
                console.warn('Code generation failed:', status); // Minimal logging
            },
            complete: function() {
                // Reset button state
                $generateBtn.prop('disabled', false)
                           .html('<i class="fas fa-random"></i> Generate');
                
                // Final integrity validation
                setTimeout(function() {
                    validateProductTypeIntegrity(preAjaxState, 'generate_complete');
                }, 200);
            }
        });
    }

    /**
     * Validate product_type integrity and detect corruption
     * Logs corruption incidents and attempts recovery
     */
    function validateProductTypeIntegrity(preAjaxState, checkpoint) {
        const currentProductId = $('#product_id').val();
        const expectedFormat = window.itemCodeConfig.originalProductId;
        
        // Check if product_id has been corrupted
        if (currentProductId !== expectedFormat && expectedFormat) {
            const corruptionData = {
                checkpoint: checkpoint,
                expected: expectedFormat,
                actual: currentProductId,
                originalProductType: window.itemCodeConfig.originalProductType,
                timestamp: new Date().toISOString(),
                preAjaxState: preAjaxState
            };
            
            // Log corruption incident
            console.error('PRODUCT_TYPE_CORRUPTION_DETECTED:', corruptionData);
            
            // Attempt to restore product_id
            if (expectedFormat && expectedFormat !== '0') {
                $('#product_id').val(expectedFormat);
                console.warn('CORRUPTION_RECOVERY: Restored product_id to:', expectedFormat);
                
                // Send corruption report to server for email notification
                reportCorruptionToServer(corruptionData);
            }
        }
    }

    /**
     * Report corruption incident to server for logging and email alerts
     */
    function reportCorruptionToServer(corruptionData) {
        // Send AJAX request to log corruption incident
        $.ajax({
            url: '<?php echo site_url('item/log_corruption_incident'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                corruption_data: JSON.stringify(corruptionData),
                user_agent: navigator.userAgent,
                page_url: window.location.href
            },
            success: function(response) {
                console.info('Corruption incident logged successfully');
            },
            error: function() {
                console.warn('Failed to log corruption incident to server');
            }
        });
    }

    /**
     * Validate item code format and uniqueness via AJAX
     */
    function validateItemCodeAsync(code, $feedback) {
        const $codeInput = $('#new_code');
        
        $.ajax({
            url: window.itemCodeConfig.validateUrl,
            type: 'POST',
            dataType: 'json',
            timeout: 5000,
            data: {
                code: code,
                item_id: window.itemCodeConfig.currentItemId || 0
            },
            success: function(response) {
                if (response.valid) {
                    $codeInput.addClass('is-valid').removeClass('is-invalid');
                    hideValidationMessage($feedback);
                } else {
                    $codeInput.addClass('is-invalid').removeClass('is-valid');
                    showValidationMessage(response.message || 'Invalid code format', 'error', $feedback);
                }
            },
            error: function() {
                // Fail silently for validation - don't disrupt user experience
                // Server-side validation will catch issues on form submit
                resetValidationState($codeInput, $feedback);
            }
        });
    }

    /**
     * Show validation message with appropriate styling
     */
    function showValidationMessage(message, type, $feedback) {
        const $target = $feedback || $('#code-validation-feedback');
        
        $target.removeClass('invalid-feedback valid-feedback')
               .addClass(type === 'success' ? 'valid-feedback' : 'invalid-feedback')
               .text(message)
               .show();
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(function() {
                hideValidationMessage($target);
            }, 3000);
        }
    }

    /**
     * Hide validation message
     */
    function hideValidationMessage($feedback) {
        const $target = $feedback || $('#code-validation-feedback');
        $target.hide();
    }

    /**
     * Reset validation visual state
     */
    function resetValidationState($codeInput, $feedback) {
        $codeInput.removeClass('is-valid is-invalid');
        hideValidationMessage($feedback);
    }
</script>
