

<div class="modal-body">
  
  <div id='spec-error-alert' class='alert alert-danger error-alert mx-auto <?php echo (isset($error_msg)?'':'hide')?>'>
      <div class='d-flex justify-content-between'>
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <h4>Oops!</h4>
        <i class="fa fa-times btnCloseAlert" aria-hidden="true"></i>
      </div>
      <div id='spec-error-msg' class='my-1'>
        <?php echo (isset($error_msg)?$error_msg:'')?>
      </div>
  </div>
  
  <div class='col-12 mb-4'>
    <h3>
      <?php echo $title?>
      <button id='btnSSCollapseForm' class='btn btn-light float-right' type='button' data-toggle='collapse' data-target='#specForm' aria-expanded='false' aria-controls='specForm' onclick='reset_spec_form("new")'>
        <i class='fas fa-plus'></i> Create new
      </button>
    </h3>
  </div>
	
  <div class='collapse the-background-color' id='specForm'>
  	<div class='card-body' style='    border: 1px solid rgba(0,0,0,.125);'>
  		<?php echo $form?>
		</div>
	</div>
  
  <div class='col-12 my-4'>
    <table class='table modal-spec-content m-auto table-sm table-responsiveX' style='' cellpadding='4' cellspacing='0'> 
      <tbody>
        <?php echo $tbody?>
      </tbody>
      <tfoot>
        <?php echo $tfoot?>
      </tfoot>
    </table>
  </div>
  
  <div class="col-12 fileupload-progress fade">
    <!-- The global progress bar -->
    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
      <div class="progress-bar progress-bar-success" style="width:0%;"></div>
    </div>
    <!-- The extended global progress state -->
    <div class="progress-extended">&nbsp;</div>
  </div>
  
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal"><i class="far fa-window-close"></i> Close</button>
  <button type="button" class="btn btn-info float-right" id='btnSubmitSS' data-spectype='<?php echo $spectype?>'><i class="far fa-check-square"></i> Proceed</button>
</div>

<script>
  
  $('i.btnDeleteRow').off('click').on('click', function(){
    deleteRow( $(this) );
  });
  


</script>