<div class='row my-4'>
  <div class='col' style='border-bottom: 1px dotted #bfac02;'>
    <input id="input_search" type="text" placeholder="Search <?php echostrtolower($column_name)?>" class="form-control input_search" value="<?php echo(isset($datatable_title['name']) ? $datatable_title['name'] : '')?>">
  </div>
  <div class='col hide'>
    - Some data ???
  </div>
</div>

<table id='contacts_table' class='row-border order-column hover compact' width='100%'>
  <thead>
    <td></td>
    <td><?php echo$column_name?></td>
    <td>Name</td>
    <td>Company</td>
    <td>Position</td>
    <td>Address1</td>
    <td>Address2</td>
    <td>City</td>
    <td>State</td>
    <td>Zipcode</td>
    <td>Country</td>
    <td>Email1</td>
    <td>Email2</td>
    <td>Tel1</td>
    <td>Tel2</td>
  </thead>
</table>


<script>
  var vid = '<?php echo$vid?>';
  var ctype = '<?php echo$ctype?>';
  var mtable_id = "#contacts_table";
  var ajaxUrl = '<?php echo$ajaxUrl?>';
  var editContactUrl = '<?php echo$editContactUrl?>';
  var typeAheadSearchUrl = '<?php echosite_url('vendor/typeahead_vendor_list')?>';
  var hasEditPermission = '<?php echo$hasEditPermission?>' === '1';
    
    var table = $(mtable_id)
    .on('preXhr.dt', function ( e, settings, data ) {
      if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
    })
    .DataTable({
      'serverSide': true,
      "processing": true,
      "ajax": {
        "url": ajaxUrl,
        "type": "POST",
        "dataSrc": "tableData",
        "data": function(d){
          //var val = ( $('input[name="chained_id"]').val() === '' ? product_id : $('input[name="chained_id"]').val() );
          d.vid = vid;
          return d;
        }
      },
      "columns": [
        { "data": "", "searchable": false, "orderable": false,
          "render": function ( data, type, row, meta ) {
            return "<i class='fas fa-pen-square btn-action btnEditContact' aria-hidden='true' data-cid='"+row.id+"'></i>"
          }
        },
        { "data": "c_owner_abrev", "searchable": true, "orderable": true },
        { "data": "c_name", "searchable": true, "orderable": true },
        { "data": "c_company", "searchable": true, "orderable": true },
        { "data": "c_position", "searchable": true, "orderable": true },
        { "data": "c_address_1", "searchable": true, "orderable": true },
        { "data": "c_address_2", "searchable": false, "orderable": true, "visible": false },
        { "data": "c_city", "searchable": true, "orderable": true },
        { "data": "c_state", "searchable": true, "orderable": true },
        { "data": "c_zipcode", "searchable": true, "orderable": true },
        { "data": "c_country", "searchable": false, "orderable": true },
        { "data": "c_email_1", "searchable": false, "orderable": true },
        { "data": "c_email_2", "searchable": false, "orderable": true, "visible": false },
        { "data": "c_tel_1", "searchable": false, "orderable": true },
        { "data": "c_tel_2", "searchable": false, "orderable": true, "visible": false }
      ],
      "order": [[ 1, "asc" ]],
      "buttons": [
        custom_buttons.back(),
        custom_buttons.new(open_contact_modal),
        custom_buttons.view([0]),
        custom_buttons.export()
        ]
    });
  

	/* Typeahead */
		var options = {
			url: typeAheadSearchUrl,

			getValue: "label",

			list: {
				match: {
					enabled: true
				},
				maxNumberOfElements: 100,
				onClickEvent: function() {
					var value = $("#input_search").getSelectedItemData().id;
					vid = value;
					console.log('new vid: ' + vid);
					table.search('');
					table.ajax.reload();
					//$("#data-holder").val(value).trigger("change");
				}
			},
			
			ajaxSettings: {
				dataType: "json",
				method: "GET",
				data: {
					dataType: "json"
				}
			},
			
			preparePostData: function(data) {
				data.query = $("#input_search").val();
				data.ctype = ctype;
				return data;
			},
			requestDelay: 500
		};

		$("#input_search").easyAutocomplete(options);

  $('.dataTable').on('click', 'i.btnEditContact', function(){
      open_contact_modal( $(this).attr('data-cid') );
  });
    
    function open_contact_modal(id=0){
      
      $.ajax({
            method: "POST",
            url: editContactUrl,
            dataType: 'json',
            data: { 
              'cid': id,
              'ctype': ctype,
              'vid': vid
            },
            error: function(jqXHR, textStatus, errorThrown){
              console.log(errorThrown);
            },
            success: function(data, msg){
              $('.modal#globalmodal').children().find('.modal-content').html(data.html);
              $('.modal#globalmodal').modal('show');
              init_dropdowns();
            }
      });
    };
  
  

  function add_contact_to_view(data){
    
    var isNew = true;
    var rows = $(mtable_id).DataTable().rows().data();
    $.each(rows, function(index, value){
      if( value.id === data.id.toString() ){
        // Existing item, modify!
        //console.log('existing');
        isNew = false;
        value.c_owner_abrev = data.c_owner_abrev;
        value.c_name = data.name;
        value.c_company = data.company;
        value.c_position = data.position;
        value.c_address_1 = data.address_1;
        value.c_address_2 = data.address_2;
        value.c_city = data.city;
        value.c_state = data.state;
        value.c_zipcode = data.zipcode;
        value.c_country = data.country;
        value.c_email_1 = data.email_1;
        value.c_email_2 = data.email_2;
        value.c_tel_1 = data.tel_1;
        value.c_tel_2 = data.tel_2;
      }
    });
      
    if(isNew){
      //console.log('new item');
      $(mtable_id).DataTable().row.add( {
        "id": data.id.toString(),
        "c_owner_abrev": data.c_owner_abrev,
        "c_name": data.name,
        "c_company": data.company,
        "c_position": data.position,
        "c_address_1": data.address_1,
        "c_address_2": data.address_2,
        "c_city": data.city,
        "c_state": data.state,
        "c_zipcode": data.zipcode,
        "c_country": data.country,
        "c_email_1": data.email_1,
        "c_email_2": data.email_2,
        "c_tel_1": data.tel_1,
        "c_tel_2": data.tel_2
      } ).draw();    
    }
    $(mtable_id).DataTable().rows().invalidate();
  }

</script>