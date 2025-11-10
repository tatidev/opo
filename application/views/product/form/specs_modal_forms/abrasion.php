
    <form id='fileupload' action='<?php echo site_url('fileupload')?>' method='POST'  enctype='multipart/form-data'>
      
      <input type='hidden' name='upload_for' id='upload_for' value='abrasion' >
      <input type='hidden' name='abrasion_id' id='abrasion_id' value='' >

      <div class='row mx-auto'>

        <div class='col-12'>
          
          <div class='form-group row'>
						<div class='col-form-label col'>
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input form-control" id="new_abrasion_visible" name="new_abrasion_visible" checked>
								<label class="custom-control-label" for="new_abrasion_visible">Public visible</label>
							</div>
						</div>
						<div class='col-form-label col'>
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input form-control" id="data_in_vendor_specsheet" name="data_in_vendor_specsheet">
								<label class="custom-control-label" for="data_in_vendor_specsheet">Data is in vendors specsheet</label>
							</div>
						</div>
          </div>
					
          <div class='form-group row'>

            <label for='new_perc' class='col-form-label col-2' >Limit</label>
            <div class='col'>
              <?php//=$dropdown_new_abrasion_limit?>
<?php foreach($new_abrasion_limit_options as $key => $value) { ?>
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" class="custom-control-input" name="new_abrasion_limit" id="new_abrasion_limit_<?php echo $key?>" value='<?php echo $key?>'>
									<label class="custom-control-label" for="new_abrasion_limit_<?php echo $key?>"><?php echo $value?></label>
								</div>
<?php } ?>
            </div>

          </div>

          <div class='form-group row'>

            <label for='new_rubs' class='col-form-label col-2' >Rubs</label>
            <div class='col'>
              <input type='number' class='form-control' name='new_rubs' id='new_rubs' value='' maxlength='5' style=''  />
            </div>
          </div>

          <div class='form-group row'>

            <label for='new_perc' class='col-form-label col-2' >Test made</label>
            <div class='col'>
              <?php//=$dropdown_new_abrasion_test?>
<?php foreach($new_abrasion_test_options as $key => $value) { ?>
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" class="custom-control-input" name="new_abrasion_test" id="new_abrasion_test_<?php echo $key?>" value='<?php echo $key?>'>
									<label class="custom-control-label" for="new_abrasion_test_<?php echo $key?>"><?php echo $value?></label>
								</div>
<?php } ?>
            </div>
          </div>
          
          <div class='form-group row'>
            <div class='offset-2 col'>
              <input type='file' class='btn form-control' name='files[]' multiple>
              <input type='hidden' name='product_id' value='<?php echo $product_id?>' >
            </div>
          </div>
          
          <!--
          <ul id='temp_url' class='d-flex flex-wrap'>
          </ul>
          -->      
          
          <div class='form-group row'>
            <div class='offset-3 col-6'>
              <table id='temp_url' class='table modal-spec-content m-auto table-sm table-responsiveX' style='' cellpadding='4' cellspacing='0'> 
                <tbody></tbody>
                <tfoot></tfoot>
              </table>
            </div>
            <input type='hidden' class='form-control' id='files_encoded' name='files_encoded' value='<?php echo (isset($files_encoded) ? $files_encoded : '')?>'>
          </div>
					
					<div class='form-group row'>
						<div class='col'>
							<button type='button' class='btn btn-link float-left' onclick='reset_spec_form("new")' tabindex='-1'> Reset Form</button>
							<button type='button' name='btnSS' id='btnAddSS' data-spectype='<?php echo $spectype?>' class='btn float-right' onClick='add_new_spec_data(this)'><i class="fas fa-plus"></i> <span>Add</span></button>
						</div>
					</div>

        </div>
      </div>
      
    </form>