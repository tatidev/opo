<?php $isNew = ($product_id == 0 ? true : false); ?>

<div class="container">
    <form id='frmCombined' method='post' action='<?php echo  $saveUrl ?>' class=''>

        <input type='hidden' name='product_id' value='<?php echo  $product_id ?>'>
        <input type='hidden' name='product_type' value='<?php echo  $product_type ?>'>

        <div id='validation_errors' class='row'>

        </div>

        <!-- Product Data Fields -->
        <div class='row mx-auto'>
            <div class='col-lg-12 offset-xl-2 col-xl-8'>

                <div class='row align-items-center'>
                    <div class='col-12'>
                        <h4 class='my-3'><?php echo  $title ?></h4>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="style" class="col-xs-12 col-sm-3 col-form-label">Pattern</label>
                    <div class="col-xs-12 col-sm">
						<?php echo  $dropdown_style ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="ground" class="col-xs-12 col-sm-3 col-form-label">Ground</label>
                    <div class="col-xs-12 col-sm">
						<?php echo  $dropdown_ground ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="reverse_ground" class="col-xs-12 col-sm-3 col-form-label">Reverse Ground?</label>
                    <div class="col-xs-12 col-sm">
                        <button type="button"
                                class="btn col btn-toggler <?php echo  ($info['reverse_ground'] === 'Y' ? 'btn-group-active' : '') ?>" <?php echo  ($product_id === 0 ? '' : 'disabled') ?> >
							<?php echo  ($info['reverse_ground'] === 'Y' ? 'Yes' : 'No') ?>
                        </button>
                        <input type="hidden" class="form-control form-check-input" id="reverse_ground"
                               name="reverse_ground"
                               value='<?php echo  set_value('reverse_ground', $info['reverse_ground']) ?>'>
                        <script>
                            $('.btn-toggler').off('click').on('click', function () {
                                var new_status = !$(this).hasClass('btn-group-active');
                                $(this).toggleClass('btn-group-active');
                                $(this).html() === 'Yes' ? $(this).html('No') : $(this).html('Yes');
                                $('#reverse_ground').val((new_status ? 'Y' : 'N')).trigger('change');
                            })
                        </script>
                    </div>
                </div>

                <!--
        <div class="form-group row">
          <label for="shelf_list" class="col-xs-12 col-sm-3 col-form-label" >Shelf List</label>
          <div class="col-xs-12 col-sm">
            <?php //=$dropdown_shelf?>
          </div>
        </div>
        -->

            </div>
            <div class='col-lg-12 offset-xl-2 col-xl-8'>

                <!--
        <div class="row hide">
          <div class="col">
            <h4 class='my-3'>Status</h4>
          </div>
          <div class="col">
            <h4 class='my-3'>Stock Status</h4>
          </div>
        </div>
        
        <div class="form-group row hide">
          <div class="col">
            <?php //=$dropdown_product_status?>
          </div>
          <div class="col">
            <?php //=$dropdown_stock_status?>
          </div>
        </div>
        -->

                <div class='row align-items-center'>
                    <div class='col-6'>
                        <h4 class='my-3'>Opuzen Prices</h4>
                    </div>
                    <div class='col-6'>
                        <a href='<?php echo  site_url('history/prices/' . $product_type . '/' . $product_id) ?>'
                           class="form-control-sm float-right <?php echo ($is_showroom ? "hide" : "")?> <?php echo  ($product_id == 0 ? 'hide' : '') ?>" target='_blank'>
                            <label><i class="fa fa-history" aria-hidden="true"></i> Price History
                                (<?php echo  intval($info['cant_price_updates']) + intval($info['cant_cost_updates']) ?>
                                )</label>
                        </a>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="in_master" class="col-3 col-form-label">Master Price List</label>
                    <div class='col-3 col-form-label'>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input form-control" id="in_master" name="in_master" <?php echo (!$isNew && $info['in_master']==='1' ? 'checked' : '')?> <?php echo ($hasMPLPermission ? '' : 'disabled')?>>
                            <label class="custom-control-label" for="in_master">Digital Product</label>
                        </div>
                    </div>
                    <div class='col'>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="p_res_cut" class="col-sm-12 col-md-3 col-form-label">Price</label>
                    <div class="col-sm-12 col-md">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" maxlength="6" class="form-control" name='p_res_cut' id='p_res_cut'
                                   value='<?php echo  set_value('p_res_cut', $info['p_res_cut']) ?>'
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="p_hosp_roll" class="col-sm-12 col-md-3 col-form-label">Volume Price</label>
                    <div class="col-sm-12 col-md">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" maxlength="6" class="form-control" name='p_hosp_roll' id='p_hosp_roll'
                                   value='<?php echo  set_value('p_hosp_roll', $info['p_hosp_roll']) ?>'
                                   aria-label="Amount (to the nearest dollar)">
                        </div>
                    </div>
                </div>
                
            </div>
	
	        <?php if(count($portfolio_urls) > 0){?>
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
                       class='btn no-border btn-outline-danger float-right mr-4 <?php echo  (!$isNew && $info['archived'] === 'N' ? '' : 'hide') ?>'><i
                                class="fas fa-archive"></i> Delete Product</a>
                </div>
            </div>
		<?php } ?>

    </form>

</div>


<script>
    $(document).ready(function () {
        init_dropdowns();
    });

    validator.formID = '#frmCombined';

    var grounds = JSON.parse('<?php echo $grounds_json?>');

    $('form#frmCombined').on('change', 'select[name="ground"]', function () {
        var item_id = $(this).val()
        $.each(grounds, function (index, value) {
            if (value.item_id === item_id) {
                $('#p_hosp_roll').val(parseInt(value.p_dig_hosp.replace('$ ', ''))).trigger('change');
                $('#p_res_cut').val(parseInt(value.p_dig_res.replace('$ ', ''))).trigger('change');
            }
        });
    })

</script>