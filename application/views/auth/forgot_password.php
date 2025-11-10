<div class='row justify-content-center'>
  <div class='col-md-6'>
    
    <div class='row table-heading'>
      <div class='col text-center'>
        <h3 class='display-4'><?php echo lang('forgot_password_heading');?></h3>
        <p><?php echo sprintf(lang('forgot_password_subheading'), $identity_label);?></p>
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
        <?php echo form_open("auth/forgot_password", " class='' ");?>

          <div class="form-group row">
            <label for="identity" class="col-3 col-form-label"><?php echo (($type=='email') ? sprintf(lang('forgot_password_email_label'), $identity_label) : sprintf(lang('forgot_password_identity_label'), $identity_label));?></label> <br />
            <div class="col">
              <?php echo form_input($identity);?>
            </div>
          </div>
          
          <div class='row'>
            <div class='col-6'>
              <button type="button" class="btn btn-outline-warning" onclick="javascript: window.location = '<?php echo site_url(); ?>';">Back</button>
            </div>
            <div class='col-6 text-right'>
              <p><?php echo form_submit('submit', lang('forgot_password_submit_btn'), " class='btn btn-outline-primary' ");?></p>
            </div>
          </div>
        
        <?php echo form_close();?>
      </div>
    </div>
    
    
  
  </div>
</div>