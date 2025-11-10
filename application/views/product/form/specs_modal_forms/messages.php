
    <input type='hidden' name='message_id' id='message_id' value='' >
    
    <div class='form-group row'>
      <label for='new_note' class='col-3 col-form-label' >New Note</label>
      <div class='col'>
        <textarea class='form-control' name='new_note' value='' cols="40" rows="5" id='new_note' style=''  />
      </div>
    </div>
    
    <div class='form-group row'>
      <div class='col'>
        <button type='button' class='btn btn-link float-left' onclick='reset_spec_form()' tabindex='-1'> Reset </button>
        <button type='button' name='btnSS' id='btnAddSS' data-spectype='<?php echo $spectype?>' class='btn float-right' onClick='add_new_spec_data(this)'><i class="fas fa-plus"></i> Add</button>
      </div>
    </div>