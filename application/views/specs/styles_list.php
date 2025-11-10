
<div class='my-4'>
  <div class='input-group col-12 px-0' style='border-bottom: 1px dotted #bfac02; box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;'>
		<div class="input-group-prepend">
			<span class="input-group-text" style='background-color: transparent; border: none; font-size: 30px;'><i class="fas fa-search"></i></span>
		</div>
    <input id="input_search" type="text" placeholder="Search pattern name" class="form-control input_search" value="">
		<div class="input-group-prepend">
			<span class="input-group-text" style='background-color: transparent; border: none; font-size: 30px;'><i class="fas fa-arrow-right"></i></span>
		</div>
		<div class='col-3 p-0'><?php echo $select_editing?></div>
  </div>
</div>

  <table id='style_table' class='row-border order-column hover compact' width='100%'></table>

<script>

    // init_dropdowns();
    var mtable_id = "#style_table";
		var this_table;
    var style_type = $("select[name='select_editing']").val();
  
    var table = $(mtable_id).DataTable({
      dom: '< <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <t> i p >',
      "ajax": {
        "url": "<?php echo site_url('specs/get_styles')?>",
        "type": "POST",
        // Set the data to send to Ajax
        "data": function(d){
          d.style_type = style_type;
          return d;
        },
        // Retrieve the data after completion
        "dataSrc": function(json){
          return json.tableData;
        }
      },
  		stateSave: false,
			"search": {
				"search": ''
			},
      "pageLength": 50,
      "createdRow": function ( row, data, index ) {
        $(row).attr('data-style-id', data.id);
      },
      "columns": [
        { "orderable": false, 'searchable': false,
          "render": function ( data, type, row, meta ) {
            return "<i class='fas fa-pen-square btn-action btnEditStyle' aria-hidden='true' data-style-type='"+style_type+"' data-style-id='"+row.id+"'></i>";
          }
        },
        { "data": "name", "title": "Name", "defaultContent": '-' },
        { "data": "repeats", "title": "Repeats", "defaultContent": '-',
          "render": function ( data, type, row, meta ) {
           var txt = '';
           if( row.vrepeat !== null && row.vrepeat !== '0.00' ){
             txt += 'V: '+row.vrepeat+'"';
              if( row.hrepeat !== null && row.hrepeat !== '0.00' ){
                 txt += ' / H: '+row.hrepeat+'"';
              }
           } else if( row.hrepeat !== null && row.hrepeat !== '0.00' ) {
                txt += 'H: '+row.hrepeat+'"';
           }
						
					 if( txt.length === 0 ){
							txt = 'No repeat';
					 }
           return txt;
          }
        },
        { "data": "active", "title": "Active", 'defaultContent': '-',
          "render": function ( data, type, row, meta ) {
            if( row.active === 'Y' ){
              return 'Yes';
            } else if( row.active === 'N' ) {
              return 'No';
            }
          }
        },
        { "title": "# Web Colors (active/total)", "defaultContent": '0 / 0',
          "render": function ( data, type, row, meta ) {
						return row.count_active_items + ' / ' + row.count_items;
          }
				},
        { "title": "# Related Products", "data": 'count_relations', "defaultContent": '0' }
      ],
      "order": [ 1, "asc" ],
      "buttons": [
        custom_buttons.back(),
        custom_buttons.new( open_edit_modal ),
        custom_buttons.view([0]),
        custom_buttons.export(
          {
            title: function(){ return $("[name='select_editing']").children( "option[value='"+$("[name='select_editing']").val()+"']" ).html(); }
          }
        )
      ]
    });
		this_table = table;

	$("#input_search").on('focus', function() {
		$(this).select();
	})
	
	$('#input_search').keyup(function(){
		var me = $(this);
    delay(function(){
			//var obj = JSON.parse( localStorage.getItem('products_view') );
			//localStorage.setItem('products_view', JSON.stringify( { search: me.val(), view: obj.view} ) );
      this_table.search( me.val() ).draw()
    }, 700 );
	})
	
	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
  
  $("#nav-content").on('change', "[name='select_editing']", function(e){
    // Update view table so that we can select which spec to modify
    style_type = $(this).val();
    table.search('');
    table.ajax.reload();
  });
  
  $(mtable_id).on('click', '.btnEditStyle', function(e){
    open_edit_modal( $(this).attr('data-style-id') );
  })
  
  function open_edit_modal(id=0){
      $.ajax({
            method: "POST",
            url: "<?php echo site_url('specs/edit_style')?>",
            dataType: 'json',
            data: {
              style_type: style_type,
              style_id: id
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
  }
  
  function add_row_to_view(row){
    
    var isNew = true;
    var rows = $(mtable_id).DataTable().rows().data();
    $.each(rows, function(index, value){
      if( value.id === row.id.toString() ){
        // Existing item, modify!
        isNew = false;
        value.name = row.name;
        value.vrepeat = row.vrepeat;
        value.hrepeat = row.hrepeat;
        value.active = row.active;
				value.count_items = row.count_items;
				value.count_active_items = row.count_active_items;
      }
    });
      
    if(isNew){
      console.log('new item');
      $(mtable_id).DataTable().row.add( row ).draw();    
    }
    $(mtable_id).DataTable().rows().invalidate();
  }
	
	function remove_style_from_view(style_id){
		var rows = table.rows().data();
		$.each(rows, function(index, value){
			if( value.id === style_id.toString() ) {
				table.row("[data-style-id='"+style_id+"']").remove().draw();
				return;
			}
		});
	}
  
</script>