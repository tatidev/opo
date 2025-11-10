
<form id='frmStyle' action='<?php echo site_url('specs/save_style')?>' class=''>
  
  <input type='hidden' id='style_type' name='style_type' value='<?php echo $style_type?>'>
  <input type='hidden' id='style_id' name='style_id' value='<?php echo $style_id?>'>
  
	<div class='row p-4'>
		
		<div class='col-12'>

			<div id='error-alert' class='alert alert-danger error-alert mx-auto hide'>
				<div class='d-flex justify-content-between'>
					<i class="fa fa-exclamation-triangle" ></i>
					<h4>Oops!</h4>
					<i class="fa fa-times btnCloseAlert" ></i>
				</div>
				<div id='error-msg' class='my-1'>

				</div>
			</div>
		</div>

		<div class='col-12 mb-4 py-2 bg-danger text-white text-center <?php echo (!$isNew && $info['archived']==='Y'?'':'hide')?>'>
			<i class="fas fa-box-open"></i> Pattern has been deleted.<? if($hasPermission){ ?> If you want to retrieve it, <u id='btnRetrieveStyle'>click here</u>.<? } ?>
		</div>

		<div class='col-12'>
			<div class='row'>
				<div class='col-6'>
					<a href='#' class="btn btn-secondary float-left" data-dismiss="modal"><i class="far fa-window-close"></i> Close</a>
				</div>
				<div class='col-6'>
					<?php echo ( $hasPermission ? ' <a class="btn btn-success float-right btnSave btnSaveStyle"><i class="far fa-square" ></i> Save</a> ' : '')?>
				</div>
			</div>
			<h3 class='mt-4'>
				Pattern Edit
			</h3>

		</div>
		
		<div class='col-12'>
			<div class='form-group row'>
				<label for="active" class="col col-form-label" ></label>				
				<div class='col-form-label col'>
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input form-control" id="active" name="active" <?php echo ( $isNew || $info['active'] === 'Y' ? 'checked' : '')?>>
						<label class="custom-control-label" for="active">Active</label>
					</div>
				</div>
			</div>
		</div>
		
		<div class='col-12'>
			<div class='form-group row'>
				<label for="info_name" class="col col-form-label" >Name</label>
				<div class="col">
					<input type="text" class="form-control" id="info_name" name='info_name' value='<?php echo ( $isNew ? '' : $info['name'] )?>' placeholder="">
				</div>
			</div>
		</div>
		
		<div class='col-12'>
			<div class='form-group row'>
				<label for="hrepeat" class="col col-form-label" ></label>				
				<div class='col-form-label col'>
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input form-control" id="no_repeat" name="no_repeat" <?php echo ( !$isNew && is_null($info['hrepeat']) && is_null($info['vrepeat']) ? 'checked' : '')?> >
						<label class="custom-control-label" for="no_repeat">No Repeat</label>
					</div>
				</div>
			</div>
		</div>

		<div class='col-12'>
			<div class='form-group row'>
				<label for="vrepeat" class="col col-form-label" >Vertical Repeat</label>
				<div class="col">
					<input type="text" class="form-control" id="vrepeat" name='vrepeat' value='<?php echo ( $isNew ? '' : $info['vrepeat'] )?>' placeholder="">
				</div>
			</div>
		</div>

		<div class='col-12'>
			<div class='form-group row'>
				<label for="hrepeat" class="col col-form-label" >Horizontal Repeat</label>
				<div class="col">
					<input type="text" class="form-control" id="hrepeat" name='hrepeat' value='<?php echo ( $isNew ? '' : $info['hrepeat'] )?>' placeholder="">
				</div>
			</div>
		</div>
		
	</div>
	
	<hr class="w-100">

<? if( $style_type === constant('Digital') ){ ?>
			
  <div class='col-12'>
        
    <div class='row align-items-center'>
      <div class='col-12'>
        <h4 class=''>Pattern Files</h4>
      </div>
    </div>
        
    <div class="row">
      <label for="pfiles" class="col-sm-12 col-md-3 col-form-label">Add New</label>
      <div class='col-sm-12 col-md'>
        <?php echo $dropdown_category_files?>
      </div>
      <div class='col-sm-12 hide'>
        <input type="text" maxlength="150" class="form-control" id="file_descr" name='file_descr' value='' placeholder="">
      </div>
      <? if ($hasPermission) { ?>
      <div class='col'>
        <span class="btn btn-link" data-for="pfiles" onclick="javascript: upload_product_file(this)">Upload new file <i class="fa fa-plus" ></i></span>
      </div>
      <? } ?>
    </div>
				
    <div class='form-group row'>
      <div class='col-12 my-4'>
        <table id='list_files' class='table modal-spec-content m-auto table-sm table-responsiveX' style='' cellpadding='4' cellspacing='0'> 
          <tbody>
            <?php echo $list_files['tbody']?>
          </tbody>
          <tfoot>
            <?php echo $list_files['tfoot']?>
          </tfoot>
        </table>
      </div>
      <input type='hidden' class='form-control' id='files_encoded' name='files_encoded' value='<?php echo $files_encoded?>'>
    </div>
      
  </div>	
  
	<hr class="w-100">
	
	<div class='row p-4'>
		<div class='col-12'>
		
			<h4 class=''>
				Showcase / Website Information
			</h4>

			<div class="form-group row">
				<div class='col-6 col-form-label'>
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input form-control" id="showcase_visible" name="showcase_visible" <?php echo ( !$isNew && $info['showcase_visible'] === 'Y' ? 'checked' : '')?> >
						<label class="custom-control-label" for="showcase_visible">Web Visible</label>
					</div>
				</div>
				<div class='col-6'>
					<a class='' target='_blank' href='<?php echo ( $isNew ? '#' : $info['url_title'] )?>'>Website view</a>
				</div>
			</div>
			
			<div class='form-group row'>
				<div class='col-12 text-center'>
					<img id='img_pic_big_url' src='<?php echo ( $isNew ? '#' : $info['pic_big_url'] )?>' class='img-fluid'>
				</div>
				<input type='text' class='form-control hide' id='pic_big_url' name='pic_big_url' value='<?php echo ( $isNew ? '' : $info['pic_big_url'] )?>'>
				<div class='col'>
					Beauty shot (980x457)
				</div>
				<div class="col">
					
<? if ($hasPermission) { ?>
					<span class="btn btn-link" data-for="pic_big_url" data-cat-id='beauty' onclick="javascript: upload_product_file(this)">
						<?php echo ( !$isNew && !empty($info['pic_big_url']) ? 'Replace Image' : 'Upload Image' )?> 
						<i class="fa fa-plus" ></i>
					</span>
<? } ?>
				</div>
			</div>
			
			<div class="form-group row">
				<label for="showcase_patterns" class="col-2 col-form-label">Pattern</label>
				<div class="col">
					<?php echo $dropdown_showcase_patterns?>
				</div>
			</div>
			
			<h4 class='my-4'>
				Colorways for website
			</h4>
			
			<table id='style_items_table' class='table table-sm'>
				<thead>
					<tr>
						<td></td>
						<td>Web Visible</td>
						<td>Color</td>
						<td colspan='3'>Thumbnail (400x400)</td>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
			<table class='table table-sm'>
				<tbody>
					<tr>
						<td colspan='5'><a class="btn btn-outline-primary no-border float-left" onclick="reset_newitem_form()"><i class="fas fa-plus"></i> To create a new one: click here, type and select desired colors in the searchbox below. Then click add.</a></td>
					</tr>
					<tr>
						<td>
							<label for="new_color_input" class="col-6 col-form-label" >Select Colors</label>
							<i class="fas fa-question-circle hide" data-toggle='tooltip' data-trigger='hover' data-title="In case the add new color button doesn't appear, press space after the color name" data-placement='top'></i>
						</td>
						<td>
							<div class='row'>
								<div class="col">
									<input type="text" class="form-control" id="new_color_input" name='new_color_input' placeholder="Color">
								</div>
								<div class='col'>
									<i id='btnNewColor' class="fa fa-plus hide" ></i>
								</div>
							</div>
						</td>
						<td colspan='2'>
							<table id='new-colors-table' class="w-100">
								<tbody>
								</tbody>
							</table>
						</td>
						<td>
							<input type='hidden' id='new_item_id' name='new_item_id' value='new'>
							<input type='hidden' id='new_color_ids' name='new_color_ids' value='[]'>
							<input type='hidden' id='new_color_names' name='new_color_names' value='[]'>
							<a class="btn btn-outline-primary no-border float-right" onclick="add_new_item()"><i class="fas fa-plus"></i> Add</a>
						</td>
					</tr>
				</tbody>
			</table>
			<!--
			<table id='style_items_datatable' class='row-border hover compact' width='100%'></table>
			-->
			
			<input type='hidden' id='item_ids' name='item_ids' value='[]'>
			<input type='hidden' id='item_ids_edited' name='item_ids_edited' value='[]'>
			<input type='hidden' id='item_ids_deleted' name='item_ids_deleted' value='[]'>
			
		</div>
	</div>
	
<? } ?>
	
  <? if( $hasPermission ) { ?>
      <div class='col-12'>
        <a id='btnArchiveStyle' href='#' class='btn no-border btn-outline-danger float-right <?php echo (!$isNew && $info['archived']==='N'?'':'hide')?>'><i class="fas fa-archive"></i> Delete Pattern</a>
      </div>
  <? } ?>

  
</form>

  <form id='fileupload_product' action='<?php echo site_url('FileuploadToS3/uploadToTemp')?>' method='POST'  enctype='multipart/form-data'>
    <input type='file' class='btn form-control' name='files' id='pfiles' >
		<input type='hidden' class='btn form-control' name='category_id' id='category_id' >
		<input type='hidden' class='btn form-control' name='category_name' id='category_name' >
  </form>



<script>
	var thisForm = '#frmStyle';
	validator.formID = thisForm;

	console.log("styles_form.php validator.", validator);
	
	function upload_product_file(me) {
        
		console.log('TEST styles_form:function upload_product_file(me) ', $(me));

			switch ($(me).attr('data-for')) {
				case 'pfiles':
					console.log('upload_product_file(me) pfiles ', $(me));
					$('#pfiles').attr('multiple', null).attr('name', 'files');
					var category_id = $("select#category_files").val();
					var category_name = $("select#category_files").children("option[value='" + category_id + "']").html();
					$('#category_id').val(category_id);
					$('#category_name').val(category_name);
					break;

                default:
				    console.log('TEST styles_form: upload_product_file(me) switch = default ', $(me));
					$('#pfiles').attr('multiple', null).attr('name', 'files');
                    $('#category_id').val( typeof( $(me).attr('data-cat-id') ) === 'undefined' ? '0' : $(me).attr('data-cat-id') );
                    $('#category_name').val( $(me).attr('data-for') );
					console.log('TEST styles_form: END switch = default ', $(me));
					break;
			}
      $('#pfiles').trigger('click');
	}
	
  $(document).ready(function() {
	console.log('TEST (top) styles_form:$(#fileupload_product).fileupload({... ');
  	$('#fileupload_product').fileupload({
  		dataType: 'json',
  		dropZone: null,
  		formData: function(e, data) {
  			return [
					{
  					name: 'category_id',
  					value: $('#category_id').val()
  				},
  				{
  					name: 'category_name',
  					value: $('#category_name').val()
  				}
  			]
  		},
  		done: function(e, data) {
			console.log('TEST (done: function(e, data)) styles_form:$(#fileupload_product).fileupload({... ', data);
  			$.each(data.result.files, function(index, file) {
					if( file.category_id === 'colorway' ){
						// file.category_name is the item_id
						mark_as_edited(file.category_name);
						console.log('TEST item_ids_edited: ', $('#item_ids_edited'). val());
						$('#img_pic_big_url_'+file.category_name).attr('src', file.url);
						$('#pic_big_url_'+file.category_name).val(file.url).trigger('change');
					}
          else if( file.category_id === 'beauty' ){
			console.log('TEST (else if beauty) styles_form:$(#fileupload_product).fileupload({... ', file);
						$('#img_pic_big_url').attr('src', file.url);
						$('#pic_big_url').val(file.url).trigger('change');
					}
          else {
			console.log('TEST (else) styles_form:$(#fileupload_product).fileupload({... ', file);
            add_file_list(file);
          }
  			});
  		}
  	});
		
		var options = {
				url: '<?php echo site_url('item/typeahead_colors')?>',

				getValue: "label",

				list: {
					match: {
						enabled: true
					},
					maxNumberOfElements: 100,
					onClickEvent: function() {
						add_new_color( $("#new_color_input").getSelectedItemData().id, $("#new_color_input").getSelectedItemData().label );
					},
					onLoadEvent: function(){
						var n = $("#new_color_input").getItems().length;
						if( n > 0 ){
							// Results available
							$('#btnNewColor').addClass('hide');
						} else {
							// No Results available
							$('#btnNewColor').removeClass('hide');
						}
					}
				},
				cssClasses: 'w-100',
				ajaxSettings: {
					dataType: "json",
					method: "POST",
					data: {
						dataType: "json"
					}
				},

				preparePostData: function(data) {
					data.query = $("#new_color_input").val();
					return data;
				},
				requestDelay: 500
			};

		$("#new_color_input").easyAutocomplete(options);
		
		// Edit!
		$('form'+thisForm).on('click', 'i.btnEditStyleItem', function(){
			var item_id = $(this).attr('data-item-id');
			$('#new-colors-table > tbody').html('');
			var color_ids = JSON.parse($('#color_ids_'+item_id).val());
			var color_names = JSON.parse($('#color_names_'+item_id).val());
			//console.log(color_ids);
			//console.log(color_names);
			for( var i = 0; i < color_ids.length; i++ ){
				add_new_color_to_view( color_ids[i], color_names[i] );
			}

			$('#new_item_id').val(item_id);
			$('#new_color_names').val( $('#color_names_'+item_id).val() );
			$('#new_color_ids').val( $('#color_ids_'+item_id).val() );
		})
		
		$('form'+thisForm).on('click', "input[name^='showcase_visible_']", function(){
			mark_as_edited( $(this).attr('data-for') );
		});
	
	});
  
  	function add_file_list(file) {
  	  var file_category_id = file.category_id; //$("select#category_files").val();
  	  var file_category_name = file.category_name; //$("select#category_files").children("option[value='"+file_category_id+"']").html();
  	  if (file_category_id === '2') {
  	    // Has different name given by user
  	    file_category_name = $('#file_descr').val();
  	    $('#file_descr').val('');
  	  }
  	  var now = $.format.date(new Date(), "MM-dd-yyyy");

  	  var new_row = $("<tr><td><a href='" + file.url + "' target='_blank'><i class='fas fa-file' style='display:none'></i> " + file_category_name + "</a></td><td>" + now + "</td> <td><i class='fas fa-times-circle delete_temp_url' style='display:none'></td></tr>");
  	  $('table#list_files > tbody').prepend(new_row);

  	  var aux = [];
  	  if ($('#files_encoded').val() !== '') {
  	    aux = JSON.parse($('#files_encoded').val());
  	  }
  	  var ne = {
  	    url_dir: file.url,
  	    date: now,
  	    user_id: user_id,
  	    category_id: file_category_id,
  	    category_name: file_category_name
  	  };
  	  aux.push(ne);
	  console.log('view/specs/styles_form/add_file_list() ', aux);
  	  $('#files_encoded').val(JSON.stringify(aux)).trigger('change');
  	}
	
	function add_new_color(new_color_id, new_color_name) {
		var current_color_ids = JSON.parse($('#new_color_ids').val());
		var current_color_names = JSON.parse($('#new_color_names').val());
		var index = current_color_ids.indexOf(new_color_id);
		if (index === -1) { // Check existence
			current_color_ids.push(new_color_id);
			current_color_names.push(new_color_name);
			$('#new_color_ids').val(JSON.stringify(current_color_ids)).trigger('change');
			$('#new_color_names').val(JSON.stringify(current_color_names));
			add_new_color_to_view(new_color_id, new_color_name);
			$('#new_color_input').val('');
		}
	}

	function add_new_color_to_view(id, name) {
		var row = "<tr> <td>" + name + "</td> <td> <i class='fa fa-trash pull-right' data-id='" + id + "' onclick='deleteColor(this)'></i> </td> </tr>";
		$('#new-colors-table>tbody').append(row);
	}

  $('form'+thisForm).on('click', '.btnSaveStyle', function(e){
    $.ajax({
      method: "POST",
      url: $(thisForm).attr('action'),
      dataType: 'json',
      data: $(thisForm).serialize(),
      error: function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown);
      },
      success: function(data, msg){
        // Update frontend!!
        if( data.success === true ){
          add_row_to_view(data.row);
          $('.modal#globalmodal').modal('hide');
        } else {
          $(thisForm).children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
        }
      }
    });
  })
	
  $('form'+thisForm).on('click', '#btnArchiveStyle', function(){
    show_swal(
      {},
      {
        title: 'Are you sure you want to delete this pattern?'
      },
      {
        complete: function(){
          $.ajax({
            method: "POST",
            url: '<?php echo site_url('specs/archive_style')?>',
            dataType: 'json',
            data: {
              style_id: $('input#style_id').val(),
              style_type: $('input#style_type').val()
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
                remove_style_from_view(data.style_id);
                $('.modal#globalmodal').modal('hide');
              } else {
                // Some error ocurred
                $(thisForm).children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
              }

            }
          });
        }
      }
    );
  })
  
  $('form'+thisForm).on('click', '#btnRetrieveStyle', function(){
    show_swal(
      {},
      {
        title: 'Are you sure you want to retrieve this pattern?'
      },
      {
        complete: function(){
          $.ajax({
            method: "POST",
            url: '<?php echo site_url('specs/retrieve_style')?>',
            dataType: 'json',
            data: {
              style_id: $('input#style_id').val(),
              style_type: $('input#style_type').val()
            },
            error: function(jqXHR, textStatus, errorThrown){
              console.log(errorThrown);
            },
            success: function(data, msg){
              
              if( data.success === true ){
                // Update datatables
                // data.item_id;
                show_success_swal();
                add_row_to_view(data.style_data);
                $('.modal#globalmodal').modal('hide');
              } else {
                // Some error ocurred
                $(thisForm).children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
              }

            }
          });
        }
      }
    );
  })
  
	// Load Style Items
	var items = JSON.parse('<?php echo $items_list['json']?>');
	for( var i = 0; i < items.length; i++ ){
		add_new_style_item_to_view(items[i]);
	}
	
	function add_new_style_item_to_view (item, isNew=true) {
		//console.log(item);
		// item .id .showcase_visible .color_ids .color .pic_big_url needed
		
		var row = "\
						<td><i data-toggle='tooltip' data-trigger='hover' data-title='Edit Item' data-placement='top' class='fas fa-pen-square btn-action btnEditStyleItem' data-item-id='"+item.id+"'></i></td> \
						<td> \
									<div class='col'> \
										<div class='custom-control custom-checkbox'> \
											<input type='checkbox' class='custom-control-input form-control' id='showcase_visible_"+item.id+"' name='showcase_visible_"+item.id+"' data-for='"+item.id+"' "+( item.showcase_visible === 'Y' ? 'checked' : '' )+"> \
											<label class='custom-control-label' style='position:absolute;' for='showcase_visible_"+item.id+"'></label> \
										</div> \
									</div> \
						</td> \
						<td>"+item.color_names+" \
							<input type='hidden' id='color_ids_"+item.id+"' name='color_ids_"+item.id+"' value='"+JSON.stringify( item.color_ids.split(' / ') )+"'> \
							<input type='hidden' id='color_names_"+item.id+"' name='color_names_"+item.id+"' value='"+JSON.stringify( item.color_names.split(' / ') )+"'> \
						</td> \
			";
		if( !isNew ){
			mark_as_edited(item.id);
			// If is not new, just replace information, except image
			$('#style_items_table > tbody > tr#'+item.id+' > td:lt(3)').remove();
			$('#style_items_table > tbody > tr#'+item.id).prepend( row );
		} else {
			row = " \
						<tr id='"+item.id+"'> \
						" + row + " \
						<td> \
							<div class='img-thumbnail-container'> \
								<a onclick=\"window.open( $(this).children().attr('src') )\"> \
									<img id='img_pic_big_url_"+item.id+"' src='"+item.pic_big_url+"' class='img-thumbnail'> \
									<input type='hidden' class='form-control' id='pic_big_url_"+item.id+"' name='pic_big_url_"+item.id+"' value='"+item.pic_big_url+"'> \
								</a> \
							</div> \
						</td> \
						<td> \
							<span class='btn btn-link' data-for='"+item.id+"' data-cat-id='colorway' onclick=\"javascript: upload_product_file(this);\"> \
								"+ ( item.pic_big_url !== '' ? 'Replace Image' : 'Upload Image') +" \
								<i class='fa fa-plus' style='display:none'></i> \
							</span> \
						</td> \
						<td> \
							<i data-toggle='tooltip' data-trigger='hover' data-title='Delete Item' data-placement='top' class='fa fa-trash pull-right' data-id='"+item.id+"' onclick='deleteItem(this)'></i> \
						</td> \
			</tr>";
			
			$('#style_items_table > tbody').append( row );
			var ids = JSON.parse($('#item_ids').val());
			ids.push( item.id );
			$("#item_ids").val( JSON.stringify(ids) );
		}
		// Gets edited both ways, when creating/editing
		console.log($('#item_ids').val() + $('#item_ids_edited').val() + $('#item_ids_deleted').val());
	}

	function add_new_item(){
		var valid = ( $('#new_color_ids').val() !== '[]' );
		if( valid ){
			var isNew = $('#new_item_id').val() === 'new';
			add_new_style_item_to_view({
				id: ( isNew ? "new-"+Math.floor(Math.random()*1000) : $('#new_item_id').val() ),
				showcase_visible: 'Y',
				color_ids: JSON.parse($('#new_color_ids').val()).join(' / '),
				color_names: JSON.parse($('#new_color_names').val()).join(' / '),
				pic_big_url: ''
			}, isNew );
			reset_newitem_form();
		} else {
			show_swal({}, {
		title: "Incomplete form",
		text: 'Select the color please',
		icon: "warning",
		buttons: true,
		dangerMode: true,
	}, {} );
		}
	}
	
	function reset_newitem_form(){
		$('#new_item_id').val('new');
		$('#new-colors-table > tbody').html('');
		$('#new_color_names').val('[]');
		$('#new_color_ids').val('[]');
		$('#new_color_input').focus();
	}
	
	function deleteColor(me) {
    var this_row = $(me);
    show_swal(
      this_row,
      {},
      {
        complete: function(this_row){
          var item_id = this_row.attr('data-id');
          var current_color_names = jQuery.parseJSON($('#new_color_names').val());
          var current_color_ids = jQuery.parseJSON($('#new_color_ids').val());
          var index = current_color_ids.indexOf(item_id);
          if( index > -1 ){
            current_color_ids.splice(index, 1); 
            current_color_names.splice(index, 1); 
          }
          $('#new_color_ids').val( JSON.stringify(current_color_ids) ).trigger('change');
          $('#new_color_names').val( JSON.stringify(current_color_names) );
          this_row.closest('tr').remove();
        }
      }
    );
  }
	
	function deleteItem(me){
    var this_row = $(me);
    show_swal(
      this_row,
      {},
      {
        complete: function(this_row){
					var index;
          var item_id = this_row.attr('data-id');
          var isNew = item_id.indexOf('new-') >= 0;
          var item_ids = jQuery.parseJSON($('#item_ids').val());
          var item_ids_edited = jQuery.parseJSON($('#item_ids_edited').val());
          var item_ids_deleted = jQuery.parseJSON($('#item_ids_deleted').val());
					
          index = item_ids.indexOf(item_id);
          if( index > -1 ) item_ids.splice(index, 1);
          index = item_ids_edited.indexOf(item_id);
          if( !isNew && index > -1 ) item_ids_edited.splice(index, 1);
          index = item_ids_deleted.indexOf(item_id);
          if( !isNew && index < 0 ) item_ids_deleted.push(item_id);
					
          $('#item_ids').val( JSON.stringify(item_ids) );
          $('#item_ids_edited').val( JSON.stringify(item_ids_edited) );
          $('#item_ids_deleted').val( JSON.stringify(item_ids_deleted) );
          this_row.closest('tr').remove();
        }
      }
    );
	}
	
	function mark_as_edited(item_id){
		var i;
		var ids = JSON.parse($('#item_ids').val());
		var ids_edited = JSON.parse($('#item_ids_edited').val());
		var isNew = item_id.indexOf('new-') > -1;
		if( !isNew ){
			i = ids.indexOf(item_id);
			if( i >= 0 ) ids.splice(i, 1);
			
			i = ids_edited.indexOf(item_id);
			if( i < 0 ) ids_edited.push(item_id);
		} else {
			i = ids.indexOf(item_id);
			if( i < 0 ) ids.push(item_id);
		}
		$('#item_ids').val( JSON.stringify(ids) );
		$('#item_ids_edited').val( JSON.stringify(ids_edited) );
	}
	
  $('form#frmStyle').on('click', '#btnNewColor', function(){
    var target = $('input#new_color_input');
    if( target.val().length > 0 ){
      var new_color_id = 'new-'+Math.floor(Math.random()*1000);
      var new_color_name = target.val();
      add_new_color(new_color_id, new_color_name);
    }
  })
	
</script>