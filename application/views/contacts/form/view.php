<pre class=''>
<?php//=var_dump($info)?>
</pre>

<form id='frmEditContact' action='<?php echosite_url('contact/save')?>' class='row p-4'>
  
  <div class='col-12'>
    
    <div id='error-alert' class='alert alert-danger error-alert mx-auto hide'>
      <div class='d-flex justify-content-between'>
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <h4>Oops!</h4>
        <i class="fa fa-times btnCloseAlert" aria-hidden="true"></i>
      </div>
      <div id='error-msg' class='my-1'>
        
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='row'>
      <div class='col-6'>
        <button type="button" class="btn btn-secondary float-left" data-dismiss="modal"><i class="far fa-window-close"></i> Close</button>
      </div>
      <div class='col-6'>
        <?php echo($hasPermission ? ' <a class="btn btn-success float-right btnSave btnSaveContact">Save <i class="far fa-square"></i></a> ' : "")?>
      </div>
    </div>
    <h3 class='mt-4'>
      <?php echo$formTitle?>
    </h3>
      
  </div>
  
  <input type='hidden' id='cid' name='cid' value='<?php echo$cid?>'>
  <input type='hidden' id='ctype' name='ctype' value='<?php echo$ctype?>'>
  <input type='hidden' id='vid' name='vid' value='<?php echo$vid?>'>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="dropdown" class="col col-form-label" ><?php echo$dropdown_name?></label>
      <div class="col">
        <?php echo$dropdown?>
      </div>
    </div>
  </div>

  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_name" class="col col-form-label" >Name</label>
      <div class="col">
        <input type="text" class="form-control" id="c_name" name='c_name' value='<?php echoset_value('c_name', $info['c_name'])?>' placeholder="Name">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_company" class="col col-form-label" >Company</label>
      <div class="col">
        <input type="text" class="form-control" id="c_company" name='c_company' value='<?php echoset_value('c_company', $info['c_company'])?>' placeholder="Company">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_position" class="col col-form-label" >Position</label>
      <div class="col">
        <input type="text" class="form-control" id="c_position" name='c_position' value='<?php echoset_value('c_position', $info['c_position'])?>' placeholder="Position">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_address_1" class="col col-form-label" >Address 1</label>
      <div class="col">
        <input type="text" class="form-control" id="c_address_1" name='c_address_1' value='<?php echoset_value('c_address_1', $info['c_address_1'])?>' placeholder="Address 1">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_address_2" class="col col-form-label" >Address 2</label>
      <div class="col">
        <input type="text" class="form-control" id="c_address_2" name='c_address_2' value='<?php echoset_value('c_address_2', $info['c_address_2'])?>' placeholder="Address 2">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_city" class="col col-form-label" >City</label>
      <div class="col">
        <input type="text" class="form-control" id="c_city" name='c_city' value='<?php echoset_value('c_city', $info['c_city'])?>' placeholder="City">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_state" class="col col-form-label" >State</label>
      <div class="col">
        <input type="text" class="form-control" id="c_state" name='c_state' value='<?php echoset_value('c_state', $info['c_state'])?>' placeholder="State">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_zipcode" class="col col-form-label" >Zipcode</label>
      <div class="col">
        <input type="text" class="form-control" id="c_zipcode" name='c_zipcode' value='<?php echoset_value('c_zipcode', $info['c_zipcode'])?>' placeholder="Zipcode">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_country" class="col col-form-label" >Country</label>
      <div class="col">
        <input type="text" class="form-control" id="c_country" name='c_country' value='<?php echoset_value('c_country', $info['c_country'])?>' placeholder="Country">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_email_1" class="col col-form-label" >Email 1</label>
      <div class="col">
        <input type="text" class="form-control" id="c_email_1" name='c_email_1' value='<?php echoset_value('c_email_1', $info['c_email_1'])?>' placeholder="Email 1">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_email_2" class="col col-form-label" >Email 2</label>
      <div class="col">
        <input type="text" class="form-control" id="c_email_2" name='c_email_2' value='<?php echoset_value('c_email_2', $info['c_email_2'])?>' placeholder="Email 2">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_tel_1" class="col col-form-label" >Tel 1</label>
      <div class="col">
        <input type="text" class="form-control" id="c_tel_1" name='c_tel_1' value='<?php echoset_value('c_tel_1', $info['c_tel_1'])?>' placeholder="Tel 1">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="c_tel_2" class="col col-form-label" >Tel 2</label>
      <div class="col">
        <input type="text" class="form-control" id="c_tel_2" name='c_tel_2' value='<?php echoset_value('c_tel_2', $info['c_tel_2'])?>' placeholder="Tel 2">
      </div>
    </div>
  </div>

</form>

<script> 
  
  $('form#frmEditContact').on('click', '.btnSaveContact', function(){
    $.ajax({
      method: "POST",
      url: $('#frmEditContact').attr('action'),
      dataType: 'json',
      data: $('#frmEditContact').serialize(),
      error: function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown);
      },
      success: function(data, msg){
        
        if( data.success === true ){
          // Update datatables
          add_contact_to_view( data.item );
          $('.modal#globalmodal').modal('hide');
          
        } else {
          // Some error ocurred
          $('#frmEditContact').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
        }
        
      }
    });
  });
  
</script>