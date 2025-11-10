<div class='row justify-content-center'>
  <div class='col-md-12'>
    
    <div class='row table-heading'>
      <div class='col text-center'>
        <h3 class='display-4'><?php echo lang('reset_password_heading');?></h3>
        <p class='hide'><?php //echo lang('create_group_subheading');?></p>
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
        <?php echo form_open('auth/reset_password?code=' . $code);?>
				
					<?php echo form_input($user_id);?>
					<?php echo form_hidden($csrf); ?>
				
          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo sprintf(lang('reset_password_new_password_label'), $min_password_length);?></label>
            <div class="col">
              <?php echo form_input($new_password);?>
            </div>
          </div>
		
          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('reset_password_new_password_confirm_label', 'new_password_confirm');?></label>
            <div class="col">
              <?php echo form_input($new_password_confirm);?>
            </div>
          </div>
        
            <div class='row'>
              <div class='col text-left'>
                <a class="hide btn btn-outline-warning btnBack" href="<?php echo site_url()?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
              </div>
              <div class='col text-right'>
                <p><?php echo form_submit('submit', lang('reset_password_submit_btn'), ' class="btn btn-outline-primary" ');?></p>
              </div>
            </div>

        <?php echo form_close();?>
      </div>
      
    </div>
    
    
    
    
  </div>
</div>