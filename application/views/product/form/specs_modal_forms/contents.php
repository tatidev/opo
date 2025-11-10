    
    <div class='form-group row'>
      <label for='new_perc' class='col-3 col-form-label' >Percentage</label>
      <div class='col'>
        <input type='decimal' class='form-control' name='new_perc' value='' maxlength='5' id='new_perc' style=''  />
      </div>
    </div>	
    
    <div class='form-group row'>
      <label for='new_content_id' class='col-3 col-form-label' >Content</label>
      <div class='col'>
        <?php echo $dropdown_contents?>
      </div>
    </div>
    
    <div class='form-group row'>
      <div class='col'>
        <button type='button' class='btn btn-link float-left' onclick='reset_spec_form()' tabindex='-1'> Reset </button>
        <button type='button' name='btnSS' id='btnAddSS' data-spectype='<?php echo $spectype?>' class='btn float-right' onClick='add_new_spec_data(this)'><i class="fas fa-plus"></i> Add</button>
      </div>
    </div>

