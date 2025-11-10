<table id='price_table' class='row-border order-column hover compact' width='100%'>
</table>

<script>
  var mtable_id = "#price_table";
  var this_table; // datatable
  var ajaxUrl = '<?php echo site_url('product/get_prices')?>';
  var special_cases = <?php echo $special_cases?>;
  var hasEditPermission = '<?php echo $hasEditPermission?>' === '1';
  
  // For frontend view - this way I can add the stamps
  var stamps = <?php echo json_encode($stamps)?>;
  var searchtype;
    
  //this_table = custom_datatables.products_by_pricing( { table_id: mtable_id, serverSideUrl: ajaxUrl } );
	if( localStorage.getItem('product_price_list') === null ) localStorage.setItem('product_price_list', 'Pr');
  set_table();
	
  function get_custom_searchtype(){
    return <?php echo json_encode($searchtypeDropdown)?>;
  }
  
  $('#nav-content').off('change', 'select#searchtype.single-dropdown').on('change', 'select#searchtype.single-dropdown', function(){
    searchtype = $(this).val();
    //console.log(searchtype);
    var s = $('input[type="search"]').val();
    this_table.destroy();
    $(mtable_id).empty();
    //
		localStorage.setItem('product_price_list', searchtype);
		set_table(s);
  });
	
	function set_table(s=null){
    switch( localStorage.getItem('product_price_list') ){
      case 'Pr':
    		$(mtable_id).append( price_list_headers() );
        this_table = custom_datatables.products_by_pricing( { target: this_table, table_id: mtable_id, serverSideUrl: ajaxUrl } );
        break;
      case 'It':
        this_table = custom_datatables.items( { target: this_table, table_id: mtable_id, serverSideUrl: ajaxUrl, isGeneralSearch: true } );
        break;
    }
    if( s !== null ) this_table.search(s).draw();
	}
	
	function price_list_headers(){
		return " <thead>\
				<tr>\
					<th  rowspan='2'></th>\
					<th  rowspan='2'></th>\
					<th  rowspan='2'>Product Type</th>\
					<th  rowspan='2'>Shelf</th>\
					<th  rowspan='2'>Product name</th>\
					<th  rowspan='2'></th>\
					<!--<th  rowspan='2'>Status</th><th  rowspan='2'>Stock Status</th>-->\
					<th colspan='3'>Opuzen Data</th>\
					<th colspan='12'>Vendors Data</th>\
				</tr>\
				<tr>\
					<th>Res Net/Yard</th>\
					<th>Hosp Cut/Yard</th>\
					<th>Hosp Roll/Yard</th>\
					<!--<td data-vabrev>Vendor</td>-->\
					<td>Vendor</td>\
					<!--<th>Vendor's name</th><th>Yds/Roll</th><th>Lead Time</th><th>Min. Qty</th>-->\
					<th>Cut</th>\
					<th>Half roll</th>\
					<th>Roll</th>\
					<th>Landed</th>\
					<th>Exmill</th>\
					<th>Costs Date</th>\
					<th>FOB</th>\
				</tr>\
			</thead>";
	}
    
</script>