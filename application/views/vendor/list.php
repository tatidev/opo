
<table id='vendor_table' class='row-border order-column hover compact' width='100%'></table>

<script>
  var mtable_id = "#vendor_table";
	var this_table;
  var ctype = '<?php echo $ctype?>';
  var editVendorUrl = '<?php echo site_url('vendor/edit')?>';
  var contactsUrl = '<?php echo $contactsUrl?>';
  var hasEditPermission = '<?php echo $hasEditPermission?>' === '1';
  var ctypes = JSON.parse('<?php echo json_encode(array('showroom'=>showroom, 'vendor'=>vendor))?>');
    
  this_table = $(mtable_id)
    .on('preXhr.dt', function ( e, settings, data ) {
      if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
    })
    .DataTable({
     	"dom": '< <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <t> i p >',
      'serverSide': true,
      "processing": true,
      "ajax": {
        "url": '<?php echo site_url('vendor/get')?>',
        "type": "POST",
        "dataSrc": "tableData",
        "data": {
          'ctype': ctype
        }
      },
			"rowId": "id",
      "columns": [
        { "title": "", "data": "", "searchable": false, "orderable": false,
          "render": function ( data, type, row, meta ) {
            return "<i class='fas fa-pen-square btn-action btnEditVendor' aria-hidden='true' data-vid='"+row.id+"'></i>"
          }
        },
        { "title": "Abrev", "data": "vendors_abrev", "searchable": false, "orderable": true },
        { "title": "Name", "data": "vendors_name", "searchable": true, "orderable": true },
        { "title": "Active", "data": "active", "searchable": false, "orderable": true,
          "render": function ( data, type, row, meta ) {
            return ( row.active === 'Y' ? 'Yes' : 'No' );
          }
        },
        { "title": ( ctype == ctypes.vendor ? "Products Assoc." : "Lists Assoc." ), "data": 'count_assoc', "searchable": false, "orderable": true,
          "render": function ( data, type, row, meta ) {
            return row.count_assoc;
          }
        },
        { "title": "Files", "data": "count_files", "searchable": false, "orderable": true,
          "render": function ( data, type, row, meta ) {
            return row.count_files;
          }
        },
        { "title": "Contacts", "data": "count_contacts", "searchable": false, "orderable": true,
          "render": function ( data, type, row, meta ) {
            return row.count_contacts + " <i class='fas fa-address-book btn-action btnViewContacts' aria-hidden='true' data-vid='"+row.id+"'></i>"
          }
        },
      ],
      "order": [[ 1, "asc" ]],
      "buttons": [
        custom_buttons.back(),
        custom_buttons.new(open_vendor_modal),
        custom_buttons.view([0]),
        custom_buttons.export()
        ]
    });
  
  
  $('.dataTable').on('click', '.btnViewContacts', function(){
    get_ajax_view(contactsUrl, {vid: $(this).attr('data-vid') } );
  });

  $('.dataTable').on('click', 'i.btnEditVendor', function(){
      open_vendor_modal( $(this).attr('data-vid') );
  });
    
    function open_vendor_modal(id=0){
      
      $.ajax({
            method: "POST",
            url: editVendorUrl,
            dataType: 'json',
            data: { 
              'vid': id,
              'ctype': ctype
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
  
  function add_vendor_to_view(data){
    
    var isNew = true;
    var rows = this_table.rows().data();
    $.each(rows, function(index, value){
      if( value.id === data.id.toString() ){
        // Existing item, modify!
        //console.log('existing');
        isNew = false;
				value = data;
				this_table.row( '#'+data.id.toString() ).invalidate().draw();
      }
    });
      
    if(isNew){
      //console.log('new item');
      this_table.row.add( data ).invalidate().draw();    
    }
    //this_table.DataTable().rows().invalidate();
  }
	
	function remove_vendor_from_view(id){
		var rows = this_table.rows().data();
		$.each(rows, function(index, value){
			if( value.id === id.toString() ) {
				this_table.row("#"+id.toString()).remove().draw();
				return;
			}
		});
	}

</script>