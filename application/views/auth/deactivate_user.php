<div class='row justify-content-center'>
  <div class='col-md-12'>
    
    <div class='row table-heading'>
      <div class='col text-center'>
        <h3 class='display-4'><?php echo lang('deactivate_heading');?></h3>
      </div>
    </div>
    
    <div class='row table-body'>
      
      <div class='col-12'>
        <?php echo form_open("auth/deactivate/".$user->id);?>
        
          <?php echo form_hidden($csrf); ?>
          <?php echo form_hidden(array('id'=>$user->id)); ?>
        
            <div class="form-group row">
              <label class="col-6 col-form-label" ><?php echo sprintf(lang('deactivate_subheading'), $user->username);?></label>
              <div class="col text-center">
                <?php echo lang('deactivate_confirm_y_label', 'confirm');?>
                <input type="radio" name="confirm" value="yes" checked="checked" />
                <?php echo lang('deactivate_confirm_n_label', 'confirm');?>
                <input type="radio" name="confirm" value="no" />
              </div>
            </div>
        
            <div class='row'>
              <div class='col text-left'>
                <a class="btn btn-outline-warning btnBack" href="<?php echo site_url('auth/')?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
              </div>
              <div class='col text-right'>
                <p><?php echo form_submit('submit', lang('deactivate_submit_btn'), ' class="btn btn-outline-primary" ');?></p>
              </div>
            </div>

        <?php echo form_close();?>
      </div>
      
    </div>
    
    
    
    
  </div>
</div>