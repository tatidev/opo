<div class='row my-4'>
  <div class='col-12 px-0' style='border-bottom: 1px dotted #bfac02;'>
    <input id="input_search" type="text" placeholder="Search here" class="form-control input_search" value="<?php echo $pname?>">
  </div>
</div>

<table id='specs_table' class='row-border order-column hover compact' width='100%'></table>

<script>
  var mtable_id = "#specs_table";
  var this_table; // datatable
  var ajaxUrl = '<?php echo site_url('product/get_products')?>';
  var special_cases = <?php echo $special_cases?>;
  var hasEditPermission = '<?php echo $hasEditPermission?>' === '1';
  
  // For frontend view - this way I can add the stamps
  var stamps = <?php echo json_encode($stamps)?>;
  var searchtype;
  
  //this_table = custom_datatables.products_by_specs( { table_id: mtable_id, serverSideUrl: ajaxUrl } );
	if( localStorage.getItem('product_specs_list') === null ) localStorage.setItem('product_specs_list', 'Pr');
  set_table();
	
  function get_custom_searchtype(){
    return <?php echo json_encode($searchtypeDropdown)?>;
  }
    
	this_table = custom_datatables.products_by_specs( { target: this_table, table_id: mtable_id, serverSideUrl: ajaxUrl } );
	
	function set_table(s=null){
		
	}

</script>