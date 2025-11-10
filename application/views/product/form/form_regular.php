<?php $isNew = ($product_id == 0 ? true : false); ?>

<div class="container">
    <form id='frmProduct' method='post' action='<?php echo $saveUrl ?>' class=''>

        <input type='hidden' class='form-control' id='product_messages_encoded' name='product_messages_encoded'
               value=''>
        <input type='hidden' name='product_id' value='<?php echo $product_id ?>'>
        <input type='hidden' name='product_type' value='<?php echo $product_type ?>'>
        <input type='hidden' id='change_showcase' name='change_showcase' value='0'>
        <input type='hidden' id='change_files_encoded' name='change_files_encoded' value='0'>

        <div id='validation_errors' class='row'>

        </div>

        <div class='row mx-auto'>

            <div class='col-lg-12 offset-xl-2 col-xl-8'>

                <div class='row align-items-center'>
                    <div class='col-6'>
                        <h4 class='my-3'>Technical Information</h4>
                    </div>
                    <div class='col-6'>
                        <a href='#' class="form-control-sm float-right specFolder <?php echo ($isNew ? '' : '') ?> <?php echo ($is_showroom ? "hide" : "")?>"
                           data-for="product_messages" <?php echo (!$isNew && $info['cant_messages'] !== '0' ? ' style="color:red" ' : '') ?>>
                            <i class="fa fa-comments" aria-hidden="true"></i> <label>Product Notes (<span
                                        id='cant_messages'><?php echo ($isNew ? '0' : $info['cant_messages']) ?></span>)</label>
                        </a>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="product_name" class="col-xs-12 col-sm-3 col-form-label">Product
                        name <?php echo ($isNew ? '<span class="required"><i class="fas fa-asterisk"></i></span>' : '') ?></label>
                    <div class="col-xs-12 col-sm-9">
                        <input type="text" class="form-control" id="product_name" name='product_name'
                               value='<?php echo ($isNew ? '' : $info['product_name']) ?>' placeholder="Product name">
                    </div>
                </div>

                <div class="form-group row">

                    <label for="width" class="col-xs-12 col-sm-3 col-md-1 col-form-label">Width</label>
                    <div class="col-xs-12 col-sm-9 col-md-2">
                        <input type="number" maxlength="5" class="form-control" id="width" name='width'
                               value='<?php echo ($isNew ? '' : $info['width']) ?>' placeholder="">
                    </div>

                    <label for="vrepeat" class="col-xs-12 col-sm-3 col-md-1 col-form-label">VR</label>
                    <div class="col-xs-12 col-sm-9 col-md-2">
                        <input type="number" maxlength="5" class="form-control" id="vrepeat" name='vrepeat'
                               value='<?php echo ($isNew ? '' : $info['vrepeat']) ?>' placeholder="">
                    </div>

                    <label for="hrepeat" class="col-xs-12 col-sm-3 col-md-1 col-form-label">HR</label>
                    <div class="col-xs-12 col-sm-9 col-md-2">
                        <input type="number" maxlength="5" class="form-control" id="hrepeat" name='hrepeat'
                               value='<?php echo ($isNew ? '' : $info['hrepeat']) ?>' placeholder="">
                    </div>

                    <div class='col-form-label col'>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input form-control" id="no_repeat"
                                   name="no_repeat" <?php echo (isset($no_repeat) && $no_repeat ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="no_repeat">No Repeat</label>
                        </div>
                    </div>

                </div>

                <div class="form-group row">
                    <label for="weight_n" class="col-sm-12 col-md-3 col-form-label">Weight</label>
                    <div class='col-sm-12 col-md-6'>
                        <div class="input-group">
                            <input type="number" maxlength="6" name='weight_n' id='weight_n'
                                   value='<?php echo ($isNew ? '' : $info['weight_n']) ?>' class="form-control" aria-label="">
                            <div class="input-group-btn">
								<?php echo $dropdown_weight_unit ?>
                            </div>
                        </div>
                    </div>
                    <!--         </div>

									<div class="form-group row"> -->

                    <div class="col-form-label col-sm-12 col-md-3">
						<?php $isOutdoor = (isset($info['outdoor']) && $info['outdoor'] === 'Y'); ?>
                        <div class="custom-control custom-checkbox col">
                            <input type="checkbox" class="custom-control-input form-control" id="outdoor"
                                   name="outdoor" <?php echo ($isOutdoor ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="outdoor">Outdoor</label>
                        </div>
                    </div>

                    <!--
					<label for="shelf_list" class="col-sm-12 col-md-3 col-form-label" >Shelf List</label>
					<div class="col-sm-12 col-md">
						<?php //=$dropdown_shelf?>
					</div>
          -->

                </div>

                <div class="form-group row">
                    <label for="uses" class="col-xs-12 col-sm-3 col-form-label" tabindex="-1">Use</label>
                    <div class="col-xs-12 col-sm-9">
						<?php echo $dropdown_uses ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="weave" class="col-xs-12 col-sm-3 col-form-label">Weave</label>
                    <div class="col-xs-12 col-sm-9">
						<?php echo $dropdown_weave ?>
                    </div>
                </div>

                <div class="form-group row align-items-start">
                    <div class='col-sm-12 col-md-6 col-lg-3'>
                        <div>
                            <label for="content_f" class="">Front Content</label>
                            <span class="ml-auto specFolder <?php echo ($is_showroom ? "hide" : "")?>" data-for='content_f'><i class="fas fa-pen-square"></i></span>
                        </div>
                        <div class='col col-form-label p-0'>
							<?php echo $list_content_f ?>
                            <input type='hidden' class='form-control' id='content_f_encoded' name='content_f_encoded'
                                   value=''>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-6 col-lg-3'>
                        <div>
                            <label for="content_b" class="">Back Content</label>
                            <span class="ml-auto specFolder <?php echo ($is_showroom ? "hide" : "")?>" data-for='content_b'><i class="fas fa-pen-square"></i></span>
                        </div>
                        <div class='col col-form-label p-0'>
							<?php echo $list_content_b ?>
                            <input type='hidden' class='form-control' id='content_b_encoded' name='content_b_encoded'
                                   value=''>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-6 col-lg-3'>
                        <div>
                            <label for="abrasion" class="">Abrasion</label>
                            <span class="ml-auto specFolder <?php echo ($is_showroom ? "hide" : "")?>" data-for='abrasion'><i class="fas fa-pen-square"></i></span>
                        </div>
                        <div class='col col-form-label p-0'>
							<?php echo $list_abrasion ?>
                            <input type='hidden' class='form-control' id='abrasion_encoded' name='abrasion_encoded'
                                   value=''>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-6 col-lg-3'>
                        <div>
                            <label for="firecode" class="">Firecodes</label>
                            <span class="ml-auto specFolder <?php echo ($is_showroom ? "hide" : "")?>" data-for='firecode'><i class="fas fa-pen-square"></i></span>
                        </div>
                        <div class='col col-form-label p-0'>
							<?php echo $list_firecode ?>
                            <input type='hidden' class='form-control' id='firecode_encoded' name='firecode_encoded'
                                   value=''>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="prop_65" class="col-xs-12 col-sm-3 col-form-label">Prop 65 Compliance</label>
                    <div class="col-xs-12 col-sm">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="prop_65" id="prop_65_dk"
                                   value='' <?php echo ($isNew || is_null($info['prop_65']) || $info['prop_65'] === '' ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="prop_65_dk">Don't know</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="prop_65"
                                   id="prop_65_yes"
                                   value='Y' <?php echo (!$isNew && $info['prop_65'] === 'Y' ? 'checked' : '') ?> >
                            <label class="custom-control-label" for="prop_65_yes">Yes</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="prop_65" id="prop_65_no"
                                   value='N' <?php echo (!$isNew && $info['prop_65'] === 'N' ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="prop_65_no">No</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="ab_2998_compliant" class="col-xs-12 col-sm-3 col-form-label">AB 2998 Compliance</label>
                    <div class="col-xs-12 col-sm">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="ab_2998_compliant" id="ab_2998_compliant_dk"  value='' <?php echo ($isNew || is_null($info['ab_2998_compliant']) || $info['ab_2998_compliant'] === '' ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="ab_2998_compliant_dk">Don't know</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="ab_2998_compliant"  id="ab_2998_compliant_yes"  value='Y' <?php echo (!$isNew && $info['ab_2998_compliant'] === 'Y' ? 'checked' : '') ?> >
                            <label class="custom-control-label" for="ab_2998_compliant_yes">Yes</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="ab_2998_compliant" id="ab_2998_compliant_no"  value='N' <?php echo (!$isNew && $info['ab_2998_compliant'] === 'N' ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="ab_2998_compliant_no">No</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="dyed_options" class="col-xs-12 col-sm-3 col-form-label">Dyed Options</label>
                    <div class="col-xs-12 col-sm">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="dyed_options" id="dyed_options_dk"  value='' <?php echo ($isNew || is_null($info['dyed_options']) || $info['dyed_options'] === '' ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="dyed_options_dk">Don't know</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="dyed_options"  id="dyed_options_piece"  value='p' <?php echo (!$isNew && $info['dyed_options'] === 'p' ? 'checked' : '') ?> >
                            <label class="custom-control-label" for="dyed_options_piece">Piece</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input form-control" name="dyed_options" id="dyed_options_yarn"  value='y' <?php echo (!$isNew && $info['dyed_options'] === 'y' ? 'checked' : '') ?>>
                            <label class="custom-control-label" for="dyed_options_yarn">Yarn</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="finish" class="col-xs-12 col-sm-3 col-form-label">Finish</label>
                    <div class="col-xs-12 col-sm">
                        <?php echo $dropdown_finish ?>
                    </div>
                    <div class='col-xs-12 col-sm <?php echo (!$isNew && strlen($info['special_finish_instr']) > 0 ? '' : 'hide') ?>'>
                        <input type="text" maxlength="150" class="form-control" id="special_finish_instr"
                               name='special_finish_instr' value='<?php echo ($isNew ? '' : $info['special_finish_instr']) ?>'
                               placeholder="">
                    </div>
                    <div class='col-form-label col-xs-12 col-2'>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input form-control" id="railroaded"
                                   name="railroaded" <?php echo (isset($railroaded) && $railroaded ? 'checked' : '') ?> >
                            <label class="custom-control-label" for="railroaded">Railroaded</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="lightfastness" class="col-xs-12 col-sm-3 col-form-label">Lightfastness</label>
                    <div class="col-xs-12 col-sm">
                        <input type="text" class="form-control" id="lightfastness" name='lightfastness'
                               value='<?php echo ($isNew ? '' : $info['lightfastness']) ?>' placeholder="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="seam_slippage" class="col-xs-12 col-sm-3 col-form-label">Seam Slippage</label>
                    <div class="col-xs-12 col-sm">
                        <input type="text" class="form-control" id="seam_slippage" name='seam_slippage'
                               value='<?php echo ($isNew ? '' : $info['seam_slippage']) ?>' placeholder="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="cleaning" class="col-xs-12 col-sm-3 col-form-label">Cleaning</label>
                    <div class="col-xs-12 col-sm">
						<?php echo $dropdown_cleaning ?>
                    </div>
                    <div class='col-xs-12 col-sm <?php echo (!$isNew && strlen($info['special_cleaning_instr']) > 0 ? '' : 'hide') ?>'>
                        <input type="text" maxlength="150" class="form-control" id="special_cleaning_instr"
                               name='special_cleaning_instr'
                               value='<?php echo ($isNew ? '' : $info['special_cleaning_instr']) ?>' placeholder="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="cleaning" class="col-xs-12 col-sm-3 col-form-label">Care Instructions</label>
                    <div class="col-xs-12 col-sm">
			            <?php echo $dropdown_cleaning_instructions ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="warranty" class="col-xs-12 col-sm-3 col-form-label">Warranty</label>
                    <div class="col-xs-12 col-sm">
			            <?php echo $dropdown_warranty ?>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="origin" class="col-xs-12 col-sm-3 col-form-label">Origin</label>
                    <div class="col-xs-12 col-sm">
						<?php echo $dropdown_origin ?>
                    </div>
                </div>

            </div>

            <hr class="w-75">

            <div class='col-lg-12 offset-xl-2 col-xl-8'>

                <div class='row align-items-center'>
                    <div class='col-6'>
                        <h4 class='my-3'>Opuzen Prices <small style='font-size:0.8rem;'
                                                              class='align-middle <?php echo ($isNew ? 'hide' : '') ?>'>~ Price
                                last update: <?php echo ($isNew ? '' : $info['price_date']) ?></small></h4>
                    </div>
                    <div class='col-6'>
                        <a href='<?php echo site_url('history/prices/' . $product_type . '/' . $product_id) ?>'
                           class="form-control-sm float-right <?php echo ($isNew ? 'hide' : '') ?> <?php echo ($is_showroom ? "hide" : "")?>" target='_blank'>
                            <label><i class="fa fa-history" aria-hidden="true"></i> Price History (<?php echo (!$isNew ? intval($info['cant_price_updates']) + intval($info['cant_cost_updates']) : '0') ?>)</label>
                        </a>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="in_master" class="col-3 col-form-label">Master Price List</label>
                    <div class='col-3 col-form-label'>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input form-control" id="in_master" name="in_master" <?php echo (!$isNew && $info['in_master']==='1' ? 'checked' : '') ?> <?php echo ($hasMPLPermission ? '' : 'disabled')?>>
                            <label class="custom-control-label" for="in_master">Product</label>
                        </div>
                    </div>
                    <div class='col'>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="p_res_cut" class="col-xs-12 col-md-3 col-form-label">Price</label>
                    <div class="col-xs-12 col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$/yd</span>
                            </div>
                            <input type="number" maxlength="6" class="form-control" name='p_res_cut' id='p_res_cut'
                                   value='<?php echo ($isNew ? '' : $info['p_res_cut']) ?>'
                                   aria-label="Amount (to the nearest dollar)"><!-- readonly commented out to enable editing -->
                        </div>
                    </div>

                    <!-- <label for="p_hosp_roll" class="col-sm-12 col-md-3 col-form-label">Volume Price</label>
                    <div class="col-sm-12 col-md">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$/yd</span>
                            </div>
                            <input type="number" maxlength="6" class="form-control" name='p_hosp_roll' id='p_hosp_roll'
                                   value='<?php echo ($isNew ? '' : $info['p_hosp_roll']) ?>'
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div><br></br><br></br> -->
                    <label for="p_hosp_roll" class="col-xs-12 col-md-3 col-form-label">Roll Price</label>
                    <div class="col-xs-12 col-md-3 ">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$/yd</span>
                            </div>
                            <?php ?>
                            <input type="number" maxlength="6" class="form-control" name='p_hosp_roll' id='p_hosp_roll'
                                   value='<?php echo ($isNew ? '' : $info['p_hosp_roll']) ?>'
                                   aria-label="Amount (to the nearest dollar)"><!-- readonly commented out to enable editing -->
                        </div>
                    </div>
                </div>

<!--                <div class="form-group row">-->
<!--                    <label for="p_hosp_cut" class="col-sm-12 col-md-3 col-form-label">Hosp Cut/Yard</label>-->
<!--                    <div class="col-sm-12 col-md">-->
<!--                        <div class="input-group">-->
<!--                            <div class="input-group-prepend">-->
<!--                                <span class="input-group-text">$/yd</span>-->
<!--                            </div>-->
<!--                            <input type="number" maxlength="6" class="form-control" name='p_hosp_cut' id='p_hosp_cut'-->
<!--                                   value='--><?//= ($isNew ? '' : $info['p_hosp_cut']) ?><!--'-->
<!--                                   aria-label="Amount (to the nearest dollar)">-->
<!--                        </div>-->
<!--                    </div>-->
<!---->
<!--                    <label for="p_hosp_roll" class="col-sm-12 col-md-3 col-form-label">Hosp Roll/Yard</label>-->
<!--                    <div class="col-sm-12 col-md">-->
<!--                        <div class="input-group">-->
<!--                            <div class="input-group-prepend">-->
<!--                                <span class="input-group-text">$/yd</span>-->
<!--                            </div>-->
<!--                            <input type="number" maxlength="6" class="form-control" name='p_hosp_roll' id='p_hosp_roll'-->
<!--                                   value='--><?//= ($isNew ? '' : $info['p_hosp_roll']) ?><!--'-->
<!--                                   aria-label="Amount (to the nearest dollar)">-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->

            </div>

            <hr class="w-75">

            <div class='col-lg-12 offset-xl-2 col-xl-8'>

                <div class='row align-items-center'>
                    <div class='col-12'>
                        <h4 class='my-3'>
                            Digitally Printed Information
                            <i style='color:red' class="fas fa-exclamation-circle" data-toggle='tooltip'
                               data-trigger='hover' data-title='Information that changes when this product is used as a digital ground'
                               data-placement='top'></i>
                        </h4>

                    </div>
                </div>

                <div class="form-group row">

                    <label for="dig_product_name" class="col-sm-12 col-md-3 col-form-label">Digital name
                        <i style='color:red' class="fas fa-exclamation-circle" data-toggle='tooltip'
                           data-trigger='hover' data-title='Change only if different from product name'
                           data-placement='top'></i>
                    </label>
                    <div class="col-sm-12 col-md">
                        <input type="text" class="form-control" id="dig_product_name" name='dig_product_name'
                               value='<?php echo ($isNew ? '' : $info['dig_product_name']) ?>' placeholder="Digital name">
                    </div>

                    <label for="dig_width" class="col-sm-12 col-md-3 col-form-label">Width
                        <i style='color:red' class="fas fa-exclamation-circle" data-toggle='tooltip'
                           data-trigger='hover' data-title='Change only if different from product width'
                           data-placement='top'></i>
                    </label>
                    <div class="col-sm-12 col-md">
                        <input type="number" maxlength="5" class="form-control" id="dig_width" name='dig_width'
                               value='<?php echo ($isNew ? '' : $info['dig_width']) ?>' placeholder="">
                    </div>

                </div>

                <div class="form-group row">
                    <label for="p_dig_res" class="col-sm-12 col-md-3 col-form-label">Price</label>
                    <div class="col-sm-12 col-md">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$/yd</span>
                            </div>
                            <input type="number" maxlength="6" class="form-control" name='p_dig_res' id='p_dig_res'
                                   value='<?php echo ($isNew ? '' : $info['p_dig_res']) ?>'
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div>

                    <label for="p_dig_hosp" class="col-sm-12 col-md-3 col-form-label">Volume Price</label>
                    <div class="col-sm-12 col-md">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$/yd</span>
                            </div>
                            <input type="number" maxlength="6" class="form-control" name='p_dig_hosp' id='p_dig_hosp'
                                   value='<?php echo ($isNew ? '' : $info['p_dig_hosp']) ?>'
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div>
                </div>

            </div>

            <hr class="w-75">

	        <?php if(!$is_showroom){ ?>
            <div class='col-lg-12 offset-xl-2 col-xl-8'>


                <div class='row align-items-center'>
                    <div class='col-12'>
                        <h4 class='my-3'>Vendor Information <small style='font-size:0.8rem;'
                                                                   class='align-middle <?php echo ($isNew ? 'hide' : '') ?>'>~
                                Costs last update: <?php echo ($isNew ? '' : $info['cost_date']) ?></small></h4>
                    </div>
                </div>

                <div class="form-group row">
                    <label for=""
                           class="col-xs-12 col-sm-3 col-form-label">Vendor <?php echo ($isNew ? '<span class="required"><i class="fas fa-asterisk"></i></span>' : '') ?></label>
                    <div class="col-xs-12 col-sm">
						<?php echo $dropdown_vendor ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="vendor_product_name" class="col-sm-12 col-md-3 col-form-label">Name</label>
                    <div class="col-sm-12 col-md">
                        <input type="text" class="form-control" name='vendor_product_name' id='vendor_product_name'
                               value='<?php echo ($isNew ? '' : $info['vendor_product_name']) ?>' aria-label="">
                    </div>

                    <label for="lead_time" class="col-sm-12 col-md-3 col-form-label">Production Lead-time
                        <i style='color:red' class="fas fa-exclamation-circle" data-toggle='tooltip'
                           data-trigger='hover' data-title='Does not include transit/packing' data-placement='top'></i>
                    </label>
                    <div class="col-sm-12 col-md">
                        <input type="text" class="form-control" name='lead_time' id='lead_time'
                               value='<?php echo ($isNew ? '' : $info['lead_time']) ?>' aria-label="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="yards_per_roll" class="col-sm-12 col-md-3 col-form-label">Yards/roll</label>
                    <div class="col-sm-12 col-md">
                        <input type="text" class="form-control" name='yards_per_roll' id='yards_per_roll'
                               value='<?php echo ($isNew ? '' : $info['yards_per_roll']) ?>' aria-label="">
                    </div>

                    <label for="min_order_qty" class="col-sm-12 col-md-3 col-form-label">M.O.Q.
                        <i class="fas fa-question-circle" data-toggle='tooltip' data-trigger='hover'
                           data-title='Minimum Order Quantity' data-placement='top'></i>
                    </label>
                    <div class="col-sm-12 col-md">
                        <input type="text" class="form-control" name='min_order_qty' id='min_order_qty'
                               value='<?php echo ($isNew ? '' : $info['min_order_qty']) ?>' aria-label="">
                    </div>
                </div>

                <div class='form-group row'>

                    <label for="cost_cut" class="col-sm-12 col-md-3 col-form-label">Cut</label>
                    <div class='col-sm-12 col-md'>
                        <div class="input-group">
                            <div class="input-group-btn">
								<?php echo $dropdown_cost_cut_type ?>
                            </div>
                            <input type="number" maxlength="6" name='cost_cut' id='cost_cut'
                                   value='<?php echo ($isNew ? '' : $info['cost_cut']) ?>' class="form-control"
                                   aria-label="Amount (to the nearest dollar)"><!-- readonly commented out to enable editing -->
                        </div>
                    </div>

                    <label for="cost_roll_landed" class="col-sm-12 col-md-3 col-form-label">Roll-landed</label>
                    <div class='col-sm-12 col-md'>
                        <div class="input-group">
                            <div class="input-group-btn">
								<?php echo $dropdown_cost_roll_landed_type ?>
                            </div>
                            <input type="number" maxlength="6" name='cost_roll_landed' id='cost_roll_landed'
                                   value='<?php echo ($isNew ? '' : $info['cost_roll_landed']) ?>' class="form-control"
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div>

                </div>

                <div class='form-group row'>

                    <label for="cost_half_roll" class="col-sm-12 col-md-3 col-form-label">Half-roll</label>
                    <div class='col-sm-12 col-md'>
                        <div class="input-group">
                            <div class="input-group-btn">
								<?php echo $dropdown_cost_half_roll_type ?>
                            </div>
                            <input type="number" maxlength="6" name='cost_half_roll' id='cost_half_roll'
                                   value='<?php echo ($isNew ? '' : $info['cost_half_roll']) ?>' class="form-control"
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div>

                    <label for="cost_roll_ex_mill" class="col-sm-12 col-md-3 col-form-label">Ex-mill</label>
<!--                    <i style='color:red'-->
<!--                       class="fa fa-exclamation-circle hide --><?////=(!$isNew && strlen($info['cost_roll_ex_mill_text']) > 0 ? '' : 'hide')?><!--"-->
<!--                       aria-hidden="true" data-toggle='tooltip' data-trigger='hover'-->
<!--                       data-title="--><?////=($isNew ? '' : $info['cost_roll_ex_mill_text'])?><!--" data-placement='top'></i>-->
                    <div class='col-sm-12 col-md'>
                        <div class="input-group">
                            <div class="input-group-btn">
								<?php echo $dropdown_cost_roll_ex_mill_type ?>
                            </div>
                            <input type="number" maxlength="6" name='cost_roll_ex_mill' id='cost_roll_ex_mill'
                                   value='<?php echo ($isNew ? '' : $info['cost_roll_ex_mill']) ?>' class="form-control"
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div>

                </div>

                <div class='form-group row'>

                    <label for="cost_roll" class="col-sm-12 col-md-3 col-form-label">Roll</label>
                    <div class='col-sm-12 col-md'>
                        <div class="input-group">
                            <div class="input-group-btn">
								<?php echo $dropdown_cost_roll_type ?>
                            </div>
                            <input type="number" maxlength="6" name='cost_roll' id='cost_roll'
                                   value='<?php echo ($isNew ? '' : $info['cost_roll']) ?>' class="form-control"
                                   aria-label="Amount (to the nearest dollar)"><!-- readonly commented out to enable editing -->
                        </div>
                    </div>

                    <label for="fob" class="col-sm-12 col-md-3 col-form-label">FOB</label>
                    <div class='col-sm-12 col-md'>
                        <input type="text" name='fob' id='fob' value='<?php echo ($isNew ? '' : $info['fob']) ?>'
                               class="form-control" aria-label="">
                    </div>

                </div>

                <div class="form-group row">
                    <label for="tariff_code" class="col-xs-12 col-sm-3 col-form-label">Tariff / Harmonized Code</label>
                    <div class="col-xs-12 col-sm">
                        <input type="text" class="form-control" id="tariff_code" name='tariff_code'
                               value='<?php echo ($isNew ? '' : $info['tariff_code']) ?>' placeholder="">
                    </div>

                    <label for="tariff_surcharge" class="col-xs-12 col-sm-3 col-form-label">Tariff Surcharge</label>
                    <div class="col-xs-12 col-sm">
                        <input type="text" maxlength="50" min="0" max="99" class="form-control" id="tariff_surcharge"
                               name='tariff_surcharge' value='<?php echo ($isNew ? '' : $info['tariff_surcharge']) ?>'
                               placeholder="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="duty_perc" class="col-xs-12 col-sm-3 col-form-label">Duty</label>
                    <div class="col-xs-12 col-sm">
                        <input type="text" class="form-control" id="duty_perc" name='duty_perc'
                               value='<?php echo ($isNew ? '' : $info['duty_perc']) ?>' placeholder="">
                    </div>

                    <label for="freight_surcharge" class="col-xs-12 col-sm-3 col-form-label">Freight Surcharge</label>
                    <div class="col-xs-12 col-sm">
                        <input type="text" class="form-control" id="freight_surcharge"
                               name='freight_surcharge' value='<?php echo ($isNew ? '' : $info['freight_surcharge']) ?>'
                               placeholder="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="vendor_notes" class="col-xs-12 col-sm-3 col-form-label">Notes</label>
                    <div class="col-xs-12 col-sm">
                        <textarea class="form-control" name="vendor_notes" value="" cols="40" rows="3" id="vendor_notes" style=""><?php echo ($isNew ? '' : $info['vendor_notes']) ?></textarea>
                    </div>
                </div>

            </div>


            <hr id='anchor-product-files' class="w-75">

            <div class='col-lg-12 offset-xl-2 col-xl-8'>

                <div class='row align-items-center'>
                    <div class='col-12'>
                        <h4 class=''>Product Files</h4>
                    </div>
                </div>

                <div class="row">
                    <label for="pfiles" class="col-sm-12 col-md-3 col-form-label">Add New
                        (<small>Max: <?php echo ini_get('upload_max_filesize') ?></small>)</label>
                    <div class='col-sm-12 col-md'>
						<?php echo $dropdown_category_files ?>
                    </div>
                    <div class='col-sm-12 hide'>
                        <input type="text" maxlength="150" class="form-control" id="file_descr" name='file_descr'
                               value='' placeholder="">
                    </div>
					<?php if ($hasEditPermission) { ?>
                        <div class='col text-right'>
                            <span class="btn btn-link" data-for="pfiles" onclick="javascript: upload_product_file(this)">
                                      Upload new file <i class="fa fa-plus" aria-hidden="true"></i>
                            </span>
                        </div>
					<?php } ?>
                </div>

                <div class='form-group row'>
                    <div class='col-12 my-4'>
                        <table id='list_files' class='table modal-spec-content m-auto table-sm table-responsiveX'
                               style='' cellpadding='4' cellspacing='0'>
                            <tbody>
							<?php echo $list_files['tbody'] ?>
                            </tbody>
                            <tfoot>
							<?php echo $list_files['tfoot'] ?>
                            </tfoot>
                        </table>
                    </div>
                    <input type='hidden' class='form-control' id='files_encoded' name='files_encoded'
                           value='<?php echo $files_encoded ?>'>
                </div>

            </div>

            <hr class="w-75">
	        <?php } ?>

            <div class='col-lg-12 offset-xl-2 col-xl-8'>

                <div class='row align-items-center'>
                    <div class='col-12'>
                        <h4 class=''>Showcase / Website Information</h4>
                    </div>
                </div>

                <div class="form-group row">
                    <div class='col-6'>
                        <div class='col-form-label'>
                            <div class="custom-control custom-checkbox">
                                <?php 
                                // Beauty shot dependency: disable checkbox if no beauty shot exists
                                $has_beauty_shot = !empty($info['pic_big_url']) && $info['pic_big_url'] !== '';
                                $checkbox_disabled = !$has_beauty_shot ? 'disabled' : '';
                                $checkbox_checked = (!$isNew && $info['showcase_visible'] === 'Y' && $has_beauty_shot) ? 'checked' : '';
                                ?>
                                <input type="checkbox" class="custom-control-input form-control" id="showcase_visible"
                                       name="showcase_visible" value="1" <?php echo $checkbox_checked . ' ' . $checkbox_disabled ?> >
                                <label class="custom-control-label" for="showcase_visible">Web Visible</label>
                                <?php if (!$has_beauty_shot): ?>
                                    <small class="form-text text-muted">Upload a beauty shot to enable web visibility</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class='col-6 text-right'>
                        <a class='' target='_blank' href='<?php echo $info['url_title'] ?>'><i class="far fa-browser"></i>
                            Website view</a>
                    </div>
                </div>

                <div class='form-group row'>
                    <div class='col-12 text-center border py-5'>
                        <img id='img_pic_big_url' src='<?php echo $img_url ?>' class='img-fluid'>
                    </div>
                    <input type='text' class='form-control hide' id='pic_big_url' name='pic_big_url'
                           value='<?php echo $img_url ?>'>
                    <div class='col'>
                        Beauty shot (980x457)
                    </div>
                    <div class="col text-right">
                        <div class='row'>

							<?php if ($hasEditPermission) { ?>
                                <div class='col'>
                                    <span class="btn btn-link" data-for="pic_big_url"
                                          onclick="javascript: upload_product_file(this)"><?php echo (!empty($img_url) ? 'Replace Image' : 'Upload Image') ?> <i
                                                class="fa fa-plus" aria-hidden="true"></i></span>
                                </div>
                                <div class="col-form-label col-sm-12 col-md-6 col-form-label">
                                    <div class="custom-control custom-checkbox col">
                                        <input type="checkbox" class="custom-control-input form-control"
                                               id="pic_big_delete" name="pic_big_delete">
                                        <label class="custom-control-label" for="pic_big_delete">Delete Image</label>
                                    </div>
                                </div>
							<?php } ?>

                        </div>

                    </div>
                </div>

                <div class="form-group row">
                    <label for="showcase_descr" class="col-xs-12 col-sm-3 col-form-label">Description</label>
                    <div class="col-xs-12 col-sm">
                        <textarea class="form-control" name="showcase_descr" value="" cols="40" rows="5"
                                  id="showcase_descr" style=""><?php echo ($isNew ? '' : $info['showcase_descr']) ?></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="showcase_collection" class="col-xs-12 col-sm-3 col-form-label">Collection</label>
                    <div class="col-xs-12 col-sm">
						<?php echo $dropdown_showcase_collection ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="showcase_contents_web" class="col-xs-12 col-sm-3 col-form-label">Contents Web</label>
                    <div class="col-xs-12 col-sm">
						<?php echo $dropdown_showcase_contents_web ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="showcase_patterns" class="col-xs-12 col-sm-3 col-form-label">Pattern</label>
                    <div class="col-xs-12 col-sm">
						<?php echo $dropdown_showcase_patterns ?>
                    </div>
                </div>

            </div>
            
            <?php if(count($portfolio_urls) > 0) { ?>
            <div class="col-lg-12 offset-xl-2 col-xl-8">
                <div class="row align-items-center">
                    <div class="col-12">
                        <h4>Portfolio</h4>
                    </div>
                </div>
                <div class="pictures-container d-flex flex-wrap align-items-start">
                    <?php foreach($portfolio_urls as $url){ ?>
                        <div class="card">
                            <div class="card-img-container">
                                <a href="<?php echo base_url().$url?>" target="_blank">
                                    <img src="<?php echo base_url().$url?>" class="card-img-top"/>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            
        </div>

		<?php if ($hasEditPermission) { ?>
            <div class='row'>
                <div class='col'>
                    <a id='btnArchive' href='#'
                       class='btn no-border btn-outline-danger float-right mr-4 <?php echo (!$isNew && $info['archived'] === 'N' ? '' : 'hide') ?>'><i
                                class="fas fa-archive"></i> Delete Product</a>
                </div>
            </div>
		<?php } ?>
    </form>

    <form id='fileupload_product' action='<?php echo site_url('product/uploadToTemp') ?>' method='POST'
          enctype='multipart/form-data'>
        <input type='file' class='btn form-control' name='files[]' id='pfiles' style='display:none;' multiple>
        <input type='hidden' class='btn form-control' name='category_id' id='category_id' style='display:none;'>
        <input type='hidden' class='btn form-control' name='category_name' id='category_name' style='display:none;'>
    </form>

</div>

<script>
    function upload_product_file(me) {
        switch ($(me).attr('data-for')) {
            case 'pfiles':
                $('#pfiles').attr('multiple', 1).attr('name', 'files[]');
                var category_id = $("select#category_files").val();
                var category_name = $("select#category_files").children("option[value='" + category_id + "']").html();
                $('#category_id').val(category_id);
                $('#category_name').val(category_name);
                break;

            case 'pic_big_url':
                $('#pfiles').attr('multiple', null).attr('name', 'files');
                $('#category_id').val('0');
                $('#category_name').val('pic_big_url');
                break;
        }
        $('#pfiles').trigger('click');
    }

    $(document).ready(function () {

        validator.formID = '#frmProduct';

        init_dropdowns();
        if ($('[data-toggle="tooltip"]').length > 0) {
            $('[data-toggle="tooltip"]').tooltip('update');
        }

        // Handle delete image checkbox
        $('#pic_big_delete').change(function() {
            if ($(this).is(':checked')) {
                $('#change_showcase').val('1');
            }
        });

        $('#fileupload_product').fileupload({
            dataType: 'json',
            dropZone: null,
            formData: function (e, data) {
                return [
                    {
                        name: 'category_id',
                        value: $('#category_id').val()
                    },
                    {
                        name: 'category_name',
                        value: $('#category_name').val()
                    }
                ]
            },
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    if (file.category_id === '0') {
                        // Beauty shot uploaded
                        $('#img_' + file.category_name).attr('src', file.url);
                        $('#' + file.category_name).val(file.url).trigger('change');
                        // Set the flag to indicate showcase has changed
                        $('#change_showcase').val('1');
                    } else {
                        add_file_list(file);
                    }
                });
            }
        });

        function add_file_list(file) {
            var file_category_id = file.category_id; //$("select#category_files").val();
            var file_category_name = file.category_name; //$("select#category_files").children("option[value='"+file_category_id+"']").html();
            if (file_category_id === '2') {
                // Has different name given by user
                file_category_name = $('#file_descr').val();
                $('#file_descr').val('');
            }
            var now = $.format.date(new Date(), "MM-dd-yyyy");

            var new_row = $("<tr><td><a href='" + file.url + "' target='_blank'><i class='fas fa-file' aria-hidden='true'></i> " + file_category_name + "</a></td><td>" + now + "</td> <td><i class='fas fa-times-circle delete_temp_url' aria-hidden='true'></i></td></tr>");
            $('table#list_files > tbody').prepend(new_row);

            var aux = [];
            if ($('#files_encoded').val() !== '') {
                aux = JSON.parse($('#files_encoded').val());
            }
            var ne = {
                url_dir: file.url,
                date: now,
                user_id: user_id,
                category_id: file_category_id,
                category_name: file_category_name
            };
            aux.push(ne);
            $('#files_encoded').val(JSON.stringify(aux)).trigger('change');
            // Set the flag to indicate files have changed
            $('#change_files_encoded').val('1');
        }

    });

</script>