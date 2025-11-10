
<div class='my-4'>
  <div class='input-group col-12 px-0' style='border-bottom: 1px dotted #bfac02; box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;'>
		<div class="input-group-prepend">
			<span class="input-group-text" style='background-color: transparent; border: none; font-size: 30px;'><i class="fas fa-search"></i></span>
		</div>
    <input id="input_search" type="text" placeholder="Search product or vendor name" class="form-control input_search" value="">
  </div>
</div>

<div class="internal-loader-spin fa-3x mx-4">
	<i class="fas fa-circle-notch fa-spin"></i>
</div>
<table id='products_table' style='opacity:0.2;' class='row-border order-column hover compact' width='100%'></table>

<script>
  var mtable_id = "#products_table";
  var this_table; // datatable
  var ajaxUrl = '<?php echo site_url('product/get_products')?>';
  var special_cases = <?php echo $special_cases?>;
  var hasEditPermission = '<?php echo $hasEditPermission?>' === '1';
  var stamps = <?php echo json_encode($stamps)?>;
  var is_showroom = '<?php echo $is_showroom?>' === '1';

	var this_view = localStorage.getItem('products_view');
	if( this_view === null || this_view === undefined || this_view === 'specs' || this_view === 'prices' ) localStorage.setItem('products_view', JSON.stringify( { search: '', view: 'prices'} ) );
	this_view = localStorage.getItem('products_view');
	$('#input_search').val( JSON.parse( this_view ).search );
	
	this_table = custom_datatables.products( {
        is_showroom: is_showroom,
		target: this_table, 
		table_id: mtable_id, 
		serverSideUrl: ajaxUrl, 
		current_state: JSON.parse( this_view ).view,
		search: JSON.parse( this_view ).search
	} );
	
	var switch_viewer = {
		current_state: JSON.parse( this_view ).view, //or prices
		colVis: {
			commons: !is_showroom ? [0, 1, 3, 4, 5, 6] : [0, 2, 3, 4],
			specs: !is_showroom ? [7, 8, 9, 10, 11, 12] : [5, 6, 7, 8, 9, 10],
			prices: !is_showroom ? [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24] : [11, 12, 13]
		},
		columns: function(state) {
			var a = [];
			if (state === 'specs') {
				a = this.colVis.specs;
			} else if (state === 'prices') {
				a = this.colVis.prices;
			}
			return $.merge( a, this.colVis.commons );
		},
		switch: function() {
			this.current_state = JSON.parse(localStorage.getItem('products_view')).view;
			if (this.current_state === 'specs') {
				this.current_state = 'prices';
			} else if (this.current_state === 'prices') {
				this.current_state = 'specs';
			}
			localStorage.setItem('products_view', JSON.stringify( { search: $('#input_search').val(), view: this.current_state} ) );
			this.update();
		},
		update: function(){
			var vis = this.columns(this.current_state);
			var total_cols = this_table.columns().visible().length;
			for (var i = 0; i < total_cols; i++) {
				this_table.column(i).visible(vis.indexOf(i) >= 0);
			}
			//this_table.columns.adjust().draw( false ); // adjust column sizing and redraw
			this_table
    		.columns.adjust()
    		.responsive.recalc();;//.draw( false ); // adjust column sizing and redraw
		}
	};
	
	$('#products_table_wrapper').on('click', '.btnSwitchView', function(){
		switch_viewer.switch();
	})
	
	$("#input_search").on('focus', function() {
		$(this).select();
	})
	
	$('#input_search').keyup(function(){
		var me = $(this);
    delay(function(){
			var obj = JSON.parse( localStorage.getItem('products_view') );
			localStorage.setItem('products_view', JSON.stringify( { search: me.val(), view: obj.view} ) );
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
	
</script>
