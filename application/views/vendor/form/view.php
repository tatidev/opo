<pre class=''>
<?php//=var_dump($info)?>
</pre>

<form id='frmEditVendor' action='<?php echo site_url('vendor/save')?>' class='row p-4'>
  
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
	
  <div class='col-12 mb-4 py-2 bg-danger text-white text-center <?php echo (!$isNew && $info['archived']==='Y'?'':'hide')?>'>
    <i class="fas fa-box-open"></i> This <?php echo trim(str_replace('Edit', '', $formTitle))?> has been deleted.<?php if($hasPermission){ ?> If you want to retrieve it, <u id='btnRetrieveVendor'>click here</u>.<?php } ?>
  </div>
  
  <div class='col-12'>
    <div class='row'>
      <div class='col-6'>
        <a href='#' class="btn btn-secondary float-left" data-dismiss="modal"><i class="far fa-window-close"></i> Close</a>
      </div>
      <div class='col-6'>
        <?php echo ($hasPermission ? ' <a href="#" class="btn btn-success float-right btnSave btnSaveContact">Save <i class="far fa-square"></i></a> ' : "")?>
      </div>
    </div>
    <h3 class='mt-4'>
      <?php echo $formTitle?>
    </h3>
      
  </div>
	
  <input type='hidden' class='form-control' id='files_encoded' name='files_encoded' value='<?php echo $files_encoded?>'>
  <input type='hidden' id='vid' name='vid' value='<?php echo $vid?>'>
  <input type='hidden' id='ctype' name='ctype' value='<?php echo $ctype?>'>
	
	<div class='offset-6 col-6 col-form-label'>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input form-control" id="active" name="active" <?php echo ( $isNew || $info['active'] === 'Y' ? 'checked' : '')?>>
			<label class="custom-control-label" for="active">Active</label>
		</div>
	</div>

  <div class='col-12'>
    <div class='form-group row'>
      <label for="vendors_abrev" class="col col-form-label" >Abbreviation</label>
      <div class="col">
        <input type="text" class="form-control" id="vendors_abrev" name='vendors_abrev' value='<?php echo set_value('vendors_abrev', $info['vendors_abrev'])?>' placeholder="Abbreviation">
      </div>
    </div>
  </div>
  
  <div class='col-12'>
    <div class='form-group row'>
      <label for="vendors_name" class="col col-form-label" >Name</label>
      <div class="col">
        <input type="text" class="form-control" id="vendors_name" name='vendors_name' value='<?php echo set_value('vendors_name', $info['vendors_name'])?>' placeholder="Name">
      </div>
    </div>
  </div>

  <div class='col-12'>
    
    <div class='form-group row'>
      <div class='col-12'>
        
        <h3 class='mb-4'>
          Files
          <button id='btnSSCollapseForm' class='btn btn-light float-right' type='button' data-toggle='collapse' data-target='#specForm' aria-expanded='false' aria-controls='specForm'>
                <i class='fas fa-plus'></i>
          </button>
        </h3>
        
        <div class='collapse' id='specForm'>
          <div class='card card-body'>
              
            <div class='form-group row'>
              <label for='descr' class='col-form-label' >Description</label>
              <div class='col'>
                <input type='text' class='form-control' name='descr' >
              </div>
							<label for='file' class='col-form-label' >Attach file</label>
              <div class='col'>
                <span class="col" data-for="pfiles" onclick="javascript: trigger_file_upload()"><i class="fa fa-plus" aria-hidden="true"></i></span>
              </div>
            </div>
            
            <div class='form-group row hide'>

              <div class='col'>
                <button type='button' class='btn btn-link float-left' onclick='reset_files_form()' tabindex='-1'> Reset </button>
                <button type='button' name='btnSS' id='btnAddSS' data-spectype='vendor_file' class='btn float-right' onClick='add_new_spec_data(this)'><i class="fas fa-plus"></i> Add</button>
              </div>

            </div>
            
          </div>
        </div>
        
        <table id='files_url' class='table modal-spec-content mt-2 table-sm table-responsiveX' style='' cellpadding='4' cellspacing='0'>
          <tbody><?php echo $tbody_files?></tbody>
          <tfoot></tfoot>
        </table>
        
      </div>
			
    </div>
    
  </div>
	
  <?php if( $hasPermission ) { ?>
      <div class='col-12'>
        <a id='btnArchiveVendor' href='#' class='btn no-border btn-outline-danger float-right mr-4 <?php echo (!$isNew && $info['archived']==='N'?'':'hide')?>'><i class="fas fa-archive"></i> Delete <?php echo trim(str_replace('Edit', '', $formTitle))?></a>
      </div>
  <?php } ?>
		
</form>

<form id='fileupload' class='row px-4' action='<?php echo site_url('fileupload')?>' method='POST'  enctype='multipart/form-data'>
	<input type='file' class='hide' name='files[]' id='pfiles' multiple>
	<input type='hidden' class='btn form-control' name='upload_for' value='vendor_file' style='display:none;'>
</form>

<script> 
	function trigger_file_upload() {
		if ($("input[name='descr']").val().length === 0) {
			$("input[name='descr']").addClass('required-field');
		} else {
			$('#pfiles').trigger('click');
		}
	}

	function reset_files_form() {
		$("input[name='descr']").val('').removeClass('required-field');
		$('table#temp_list_files > tbody').html('');
	}

	$('form#frmEditVendor').on('click', '.btnSaveContact', function() {
		$.ajax({
			method: "POST",
			url: $('#frmEditVendor').attr('action'),
			dataType: 'json',
			data: $('#frmEditVendor').serialize(),
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(errorThrown);
			},
			success: function(data, msg) {

				if (data.success === true) {
					add_vendor_to_view(data.item);
					$('.modal#globalmodal').modal('hide');
				} else {
					// Some error ocurred
					$('#frmEditVendor').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
				}

			}
		});
	});

  $('form#frmEditVendor').on('click', '#btnArchiveVendor', function(){
    show_swal(
      {},
      {
        title: 'Are you sure you want to delete?'
      },
      {
        complete: function(){
          $.ajax({
            method: "POST",
            url: '<?php echo site_url('vendor/archive')?>',
            dataType: 'json',
            data: {
              vid: $('input#vid').val(),
              ctype: $('input#ctype').val()
            },
            error: function(jqXHR, textStatus, errorThrown){
              console.log(errorThrown);
            },
            success: function(data, msg){
              //console.log(data);

              if( data.success === true ){
                // Update datatables
                // data.item_id;
                show_success_swal();
                remove_vendor_from_view(data.vid);
                $('.modal#globalmodal').modal('hide');
              } else {
                // Some error ocurred
                $('#frmItem').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
              }

            }
          });
        }
      }
    );
  })
  
  $('form#frmEditVendor').on('click', '#btnRetrieveVendor', function(){
    show_swal(
      {},
      {
        title: 'Are you sure you want to retrieve?'
      },
      {
        complete: function(){
          $.ajax({
            method: "POST",
            url: '<?php echo site_url('vendor/retrieve')?>',
            dataType: 'json',
            data: {
              vid: $('input#vid').val(),
              ctype: $('input#ctype').val()
            },
            error: function(jqXHR, textStatus, errorThrown){
              console.log(errorThrown);
            },
            success: function(data, msg){
              
              if( data.success === true ){
                // Update datatables
                // data.item_id;
                show_success_swal();
                add_vendor_to_view(data.vendor);
                $('.modal#globalmodal').modal('hide');
              } else {
                // Some error ocurred
                $('#frmItem').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
              }

            }
          });
        }
      }
    );
  })
  
	
	$('#fileupload').fileupload({
		dataType: 'json',
		done: function(e, data) {
			$.each(data.result.files, function(index, file) {
				add_temp_file_list(file);
				reset_files_form();
			});
		}
	});

	function add_temp_file_list(file) {
		var now = $.format.date(new Date(), "MM-dd-yyyy");
		var descr = $("input[name='descr']").val();
		var new_row = $("<tr><td><a href='" + file.url + "' target='_blank'><i class='fa fa-file btnViewFile' aria-hidden='true'></i></a> " + descr + "</td><td>" + now + "</td><td><i class='fa fa-times-circle delete_temp_url' aria-hidden='true'></i></td></tr>");
		$('table#files_url > tbody').prepend(new_row);

		var aux = [];
		if ($('#files_encoded').val() !== '') {
			aux = JSON.parse($('#files_encoded').val());
		}
		//var ne = [file.url, descr, now, user_id];
		var ne = {
			url_dir: file.url,
			descr: descr,
			date_add: now,
			user_id: user_id
		};
		aux.push(ne);
		$('#files_encoded').val(JSON.stringify(aux)).trigger('change');
	}
</script>