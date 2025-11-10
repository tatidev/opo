<div class='row justify-content-center'>
  <div class='col-md-12'>
    
    <div class='row table-heading'>
      <div class='col text-center'>
        <h3 class='display-4'><?php echo lang('create_group_heading');?></h3>
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
        <?php echo form_open("auth/create_group");?>
        
          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('create_group_name_label', 'group_name');?></label>
            <div class="col">
              <?php echo form_input($group_name);?>
            </div>
          </div>
        
          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('create_group_desc_label', 'description');?></label>
            <div class="col">
              <?php echo form_input($description);?>
            </div>
          </div>
        
          <?php if ($this->ion_auth->is_admin()) { ?>
          <div class='form-group row'>
            <div class='col-12'>

               <h3>Group Permissions by App/Module</h3>
               <div class='d-flex flex-row flex-wrap justify-content-between'>
                  
                  <?php 
                    $prev_app = null;
                    $prev_module = null;
                    $this_group_permissions = array_column($groupPermissions, 'id');
                    foreach ($allPermissions as $p) { ?>
                    <?php
                      if( $prev_app !== $p['app'] ){
                        echo "<h4 class='my-4' style='flex: 0 0 100%;'>App: ".ucfirst($p['app'])."</h4>";
                      }

                      if ( $prev_module !== $p['module'] ) {
                        if( !is_null($prev_module) ){
                          echo "</tbody></table>";
                        }
                        echo "<table class='table table-hover table-sm table-bordered' style='max-width:22%' ><thead><tr><td colspan='2'><b>".ucfirst(htmlspecialchars($p['module'],ENT_QUOTES,'UTF-8'))."</b></td></tr></thead><tbody>";
                      }
                      $checked = ( in_array($p['id'], $this_group_permissions) ? ' checked="checked" ' : '');
                    ?>
                      <tr><td><?php echo htmlspecialchars($p['action'],ENT_QUOTES,'UTF-8')?></td><td><input type='checkbox' name='permissions[]' value="<?php echo $p['id']?>" <?php echo $checked?> ></td></tr>
                    <?php
                      $prev_app = $p['app'];
                      $prev_module = $p['module'];
                    }
                  ?>
              
                </div> 
            </div>
          </div>
          <?php }
          ?>
        
            <div class='row'>
              <div class='col text-left'>
                <a class="btn btn-outline-warning btnBack" href="<?php echo site_url('auth/')?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
              </div>
              <div class='col text-right'>
                <p><?php echo form_submit('submit', lang('create_group_submit_btn'), ' class="btn btn-outline-primary" ');?></p>
              </div>
            </div>

        <?php echo form_close();?>
      </div>
      
    </div>
    
    
    
    
  </div>
</div>