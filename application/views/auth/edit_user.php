<div class='row justify-content-center'>
  <div class='col-md-12'>
    
    <div class='row table-heading'>
      <div class='col text-center'>
        <h3 class='display-4'><?php echo lang('edit_user_heading');?></h3>
        <p class='hide'><?php //echo lang('edit_user_subheading');?></p>
      </div>
    </div>
    
    <div class='row table-body'>
      
      <div class='col-12 <?php echo (is_null($message) ? 'hide' : '')?>'>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div id="infoMessage" class=''><?php echo $message;?></div>
        </div>
        
      </div>
      
      <div class='col-12'>
        <?php echo form_open(uri_string());?>
        
          <?php echo form_hidden('id', $user->id);?>
          <?php echo form_hidden($csrf); ?>
        
          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('edit_user_fname_label', 'first_name');?></label>
            <div class="col">
              <?php echo form_input($first_name);?>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('edit_user_lname_label', 'last_name');?></label>
            <div class="col">
              <?php echo form_input($last_name);?>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('edit_user_company_label', 'company');?></label>
            <div class="col">
              <?php echo form_input($company);?>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('edit_user_phone_label', 'phone');?></label>
            <div class="col">
               <?php echo form_input($phone);?>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('edit_user_password_label', 'password');?> </label>
            <div class="col">
              <?php echo form_input($password);?>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('edit_user_password_confirm_label', 'password_confirm');?></label>
            <div class="col">
              <?php echo form_input($password_confirm);?>
            </div>
          </div>

          <div class="form-group row">
              <label class="col-3 col-form-label" >Location (for Restock orders)</label>
              <div class="col">
			      <?php echo form_dropdown('restock_destination', $destinations_list, $currentDestinations);?>
              </div>
          </div>

          <div class='row'>
            <div class='col'>
              <?php if ($this->ion_auth->is_admin()): ?>

                  <h3><?php echo lang('edit_user_groups_heading');?></h3>
              
                  <?php foreach ($groups as $group):?>
                    <div class='checkbox'>
                      
                      <label class="checkbox">
                      <?php
                          $gID=$group['id'];
                          $checked = null;
                          $item = null;
                          foreach($currentGroups as $grp) {
                              if ($gID == $grp->id) {
                                  $checked= ' checked="checked"';
                              break;
                              }
                          }
                      ?>
                      <input type="radio" name="groups[]" value="<?php echo $group['id'];?>"<?php echo $checked;?>>
                      <?php echo htmlspecialchars($group['name'],ENT_QUOTES,'UTF-8');?>
                      </label>
                    </div>
                  <?php endforeach?>

              <?php endif ?>
              
              </div>
            
            <div id='showrooms_list' class='col <?php echo ( $currentGroups[0]->id === strval(constant('Showroom_Group_id')) ? '' : 'hide')?>'>
              
              <?php if ($this->ion_auth->is_admin()): ?>

                  <h3>Member of Showrooms</h3>
              
                  <?php foreach ($showrooms_list as $group):?>
                    <div class='checkbox'>
                      
                      <label class="checkbox">
                      <?php
                          $gID=$group['id'];
                          $checked = null;
                          $item = null;
                          foreach($currentShowrooms as $grp) {
                              if ($gID == $grp->id) {
                                  $checked= ' checked="checked"';
                              break;
                              }
                          }
                      ?>
                      <input type="checkbox" name="showrooms[]" value="<?php echo $group['id'];?>"<?php echo $checked;?>>
                      <?php echo htmlspecialchars($group['name'],ENT_QUOTES,'UTF-8');?>
                      </label>
                    </div>
                  <?php endforeach?>

              <?php endif ?>
              
              </div>
            
            </div>
        
            <div class='row'>
              <div class='col text-left'>
                <a class="btn btn-outline-warning btnBack" href="<?php echo site_url('auth/')?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
              </div>
              <div class='col text-right'>
                <p><?php echo form_submit('submit', lang('edit_user_submit_btn'), ' class="btn btn-outline-primary" ');?></p>
              </div>
            </div>

        <?php echo form_close();?>
      </div>
      
    </div>
    
    
    
    
  </div>
</div>


<script>

  $(document).on('click', "input[type='radio']", function(){
    var group_id = $("input[type='radio']:checked").val();
    if( group_id === '15' ){
       // Showroom group is selected
       $('#showrooms_list').removeClass('hide');
    } else {
       $('#showrooms_list').addClass('hide');
    }
  })

</script>