<div class='row justify-content-center'>
  <div class='col-md-6'>
    
    <div class='row table-heading'>
      <div class='col text-center'>
        <h3 class='display-4'><?php echo lang('login_heading');?></h3>
        <p class='hide'><?php //echo lang('login_subheading');?></p>
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
        <?php echo form_open("auth/login", " id='frmlogin' ");?>
          
          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('login_identity_label', 'identity');?></label>
            <div class="col">
              <?php echo form_input($identity);?>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-3 col-form-label" ><?php echo lang('login_password_label', 'password');?></label>
            <div class="col">
              <?php echo form_input($password);?>
            </div>
          </div>
        
          <div class="form-group row hide">
            <div class="btn-group btn-group-toggle mx-auto" data-toggle="buttons">
              <label class="btn btn-outline-warning active">
                <input type="radio" name="user_category" id="employee" autocomplete="off" value='employee'> Employee
              </label>
              <label class="btn btn-outline-warning">
                <input type="radio" name="user_category" id="showroom" autocomplete="off" value='showroom'> Showroom
              </label>
            </div>
          </div>
          
          <div class='row'>
            <div class='col-6'>
              <p>
                <?php echo lang('login_remember_label', 'remember');?>
                <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"');?>
              </p>
            </div>
            <div class='col-6 text-right'>
              <div id='recaptcha' 
                   class='' 
                   data-size='invisible'>
                </div>
              <p><?php echo form_submit('btnSubmit', lang('login_submit_btn'), " class='btn btn-outline-primary no-border' ");?></p>
              
							<p><?php //echo form_button('btnSubmit', lang('login_submit_btn'), " class='btn btn-outline-primary no-border g-recaptcha' data-sitekey='6Lf1yTMUAAAAAK0gNjemckiAzbka6w1v3LCXi8y4' data-callback='captcha_callback' ");?></p>
            </div>
          </div>
        
        <?php echo form_close();?>
      </div>
      
      <div class='col-12 text-right'>
        <p><a href="forgot_password"><?php echo lang('login_forgot_password');?></a></p>
      </div>
      
      <script>
        function captcha_callback(token){
          //document.getElementById("frmlogin").submit();
        }
      </script>
      
    </div>
  </div>
</div>

