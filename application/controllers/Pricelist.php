<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pricelist extends MY_Controller
{
    private $get_query = false;
    private $altfield_master_list_id = 370;

    function __construct()
    {
        parent::__construct();
// 		$this->thisC = 'lists';
        $this->load->library('table');
        $this->load->model('Lists_model', 'model');
        $this->load->model('Search_model', 'search');
// 		array_push( $this->data['crumbs'], 'Lists' );
// 		$this->data['printUrl'] = site_url('lists/price_list');
        $this->data['hasEditPermission'] = $this->hasPermission('lists', 'view');

        // Tolerance when inserting batch items
        $this->max_tolerance = 1500;
    }

    public function index($list_id = null, $group_by = null, $order_by = null, $load_view = true, $add_type = product)
    {
        /*
            The search is only looking items that are present in a list
                (Basically everything that is in $this->t_list_items : T_ITEM_LIST table)
        */
		$this->load_view = $load_view;
        if (is_null($list_id)) redirect('lists');

        $this->list_id = intval($list_id);
        switch ($group_by) {
            case Spec_Type:
				//echo "CASE: Spec_Type ($group_by)<br />";
                $this->group_by = Spec_Type;
                $this->selects = [
					'product_name', 'vendor', 'product_id', 'product_type', 
					'p_res_cut', 'p_hosp_roll',
					'stock', 'stock_status', 'status', 'status_id', 
					'count_items', 'pic_big_url', 'content_front', 'width', 'repeats', 
					'abrasion', 'count_items_with_code'
				];
                $this->add_type = $add_type;
//                if($this->add_type == product){
//                    $this->data['warningMessage'] = 'This print *REQUIRES* a `Big Piece` selection to choose the Product image. Product without `Big Piece` will NOT display here.';
//                }
                break;
            case item:
            case 'item':
                $this->group_by = item;
                $this->selects = ['status', 'status_id', 'stock_status', 'width', 'content_front', 'outdoor', 'price', 'stock', 'showcase'];
                break;
            case product:
				$this->group_by = product;
                $this->selects = ['status', 'status_id', 'stock_status', 'width', 'content_front', 'outdoor', 'price'];
                break;
            case 'product':
				$this->group_by = product;
                $this->selects = ['status', 'status_id', 'stock_status', 'width', 'content_front', 'outdoor', 'price'];
                break;
            default:
			    //echo "DEFAULT CASE: $group_by <br />";
                $this->group_by = product;
                $this->selects = ['status', 'status_id', 'stock_status', 'width', 'content_front', 'outdoor', 'price'];
                break;
        }
        $this->order_by = $order_by;

        if (is_int($this->list_id)) {
	        $this->data['list_id'] = $this->list_id;
	        $this->data['info'] = $this->model->get_list_edit($this->list_id);

	        $showroom_ids = explode(' / ', $this->data['info']['showroom_id']);
			$this->data['showroom_data'] = $this->model->get_showroom_address($showroom_ids[0]);
			$this->data['showroom_data'] = is_null($this->data['showroom_data']) ? [] : ['tel'=> '(No tel)', 'email'=> '(No email)'];
			
	        ($this->list_id === $this->altfield_master_list_id ? $this->list_id = 0 : '');

            $filters = [
                'select' => $this->selects,
                'list' => ['id' => $this->list_id, 'active' => true, 'item_info' => true, 'list_price' => true],
                'group_by' => ($this->group_by == Spec_Type ? $this->add_type : $this->group_by),
//              'where_literal' => ($this->group_by == Spec_Type && $this->add_type == product ? ["ListItems.big_piece" => "1"] : null),
                'isPrinting' => false,
                'order_by' => $this->order_by,
                'discount' => $this->data['info']['initial_discount'],
                'get_compiled_select' => $this->get_query
                // 					'includeDiscontinued'=>true
            ];
  
			// echo "<pre> FILTERS: [".__LINE__."] <br />";
			// print_r($filters);
			// echo "</pre>";

	        $results_array = $this->search->do_search($filters);

	        // echo "<pre>[".__LINE__."]  FILE:". __FILE__  ." <br />";
			// echo $this->db->last_query();
			// echo "<br/></pre>";
	        
			//echo "<br /><pre> [".__LINE__."] results_array: <br />"; 
			//print_r($results_array); 
			//echo "</pre>"; 
			//exit;

			$this->print_pricelist($results_array);
        }
    }

    // PKL produce full comma sep usage list for each product
	private function get_product_usage($item)
	{	
		$usage_str = "";
        if( isset($item['product_type']) && $item['product_type'] === "R"){
			$this->db->select('GROUP_CONCAT(DISTINCT u.name ORDER BY u.name ASC SEPARATOR ", ") AS use_names');
			$this->db->from('P_USE AS u');
			$this->db->join('T_PRODUCT_USE AS pu', 'pu.use_id = u.id');
			$this->db->where('pu.product_id', $item['product_id']);
			$usage_str = "";
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
            	$usage_str = $query->row()->use_names; 
				if(!$usage_str){ $usage_str = "Missing Usage"; }
				$usage_str = "" . $usage_str;
            } else {
            	return null; // Return null if no result
            }
		}
        if( isset($item['product_type']) && $item['product_type'] === "D"){
            // Build the query
            $this->db->select('GROUP_CONCAT(DISTINCT u.name ORDER BY u.name ASC SEPARATOR ", ") AS use_names');
			$this->db->from('T_PRODUCT_X_DIGITAL AS pxd');
            $this->db->join('T_ITEM AS i', 'pxd.item_id = i.id', 'inner');
			$this->db->join('T_PRODUCT AS p', 'p.id = i.product_id', 'inner');
            $this->db->join('T_PRODUCT_USE AS pu', 'p.id = pu.product_id', 'inner');
            $this->db->join('P_USE AS u', 'pu.use_id = u.id', 'inner');
            $this->db->where('u.active', 'Y');
            $this->db->where('pxd.id', $item['product_id']);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
            	$usage_str = $query->row()->use_names; 
				if(!$usage_str){ $usage_str = "Missing Usage"; }
				$usage_str = "" . $usage_str;
            } else {
            	return null; // Return null if no result
            }
		}
		return $usage_str;		
	}


    private function print_pricelist($results_array)
    {

        // PKL produce full comma sep usage list for each item
       foreach ($results_array as $key => $i ) {
	   	  $usage_names_str = $this->get_product_usage($i);
		  //$usage_names_str = $usage_names_arr['use_names'];
	   	  $results_array[$key]['use'] = $usage_names_str;
	   }

	   	//	echo "<br /><pre>PKL TEST (gone in a moment) results_array: <br />"; 
		//	print_r($results_array); 
		//	echo "</pre>";

		/*
		 *  Requirements to call this function
		 *      $this->group_by
		 *      $this->list_id
		 *      $results_array items should have 'width', 'content_front', status, stock_status and all prices
		 *
		 */
//	    echo "<pre>"; echo "Group by `" . $this->group_by . "`<br>"; var_dump($results_array); echo "</pre>"; exit;
	    $this->data['items'] = array();
	    $this->data['itemsjson'] = json_encode($results_array);
	    $this->data['digital_items'] = array();
	    $this->data['items_missing_data'] = array();
	    // 			Get ALL the $30 and Under items
	    $this->under30_ids = $this->model->get_30_under_product_ids();

	    $this->create_print_list_header();

	    if($this->group_by === product or $this->group_by === item){
		    // Separate items and format some data
		    foreach ($results_array as $i) {
	//		    if ($this->group_by === item && $this->order_by === 'n_order' && $i['big_piece'] === '0') continue;

                // get usage for each item
				// $usage_array = $this->get_product_usage($i['product_id']);

                // $i['use'] = implode(', ', array_column($usage_array, 'name'));

			    // Clean the TBD status
			    if (strpos($i['stock_status'], 'TBD') !== false) {
				    $i['stock_status'] = trim(str_replace('TBD', '', $i['stock_status']));
				    $i['stock_status'] = trim(str_replace('-', '', $i['stock_status']));
			    }

			    if ($this->group_by === item) {
				    // Show the 'Limited Stock' note
				    if (in_array($i['status_id'], $this->model->product_status_discontinued)) {
					    $i['color'] .= "  <span class='limited-stock-row'>(Limited stock)</span>";
				    }
			    }

			    if ($this->group_by === Spec_Type) {
				    $ratio_with_number = floatval($i['count_items_with_code']) / floatval($i['count_items']);
				    $i['product_name'] = ($ratio_with_number < 0.2 ? $i['vendor'] . ' ' . $i['product_name'] : $i['product_name']);
				    $vals = array_values($this->create_print_list_row($i));
				    array_push($this->data['items'], $i);
				    $this->table->add_row($vals);
			    } // Start conditionals if an item should be shown
			    else if (
			      $this->is_valid_stock_status($i['stock_status']) &&
			      (
				    !$this->is_digital_ground_list($this->list_id) &&
//				    !(($i['p_res_cut']) == $this->no_price_sql_output && $i['p_hosp_cut'] == $this->no_price_sql_output && $i['p_hosp_roll'] == $this->no_price_sql_output) &&
                    !(($i['p_res_cut']) == $this->no_price_sql_output && $i['p_hosp_roll'] == $this->no_price_sql_output) &&
	//			    $this->is_valid_price($i['p_res_cut']) && $this->is_valid_price($i['p_hosp_cut']) && $this->is_valid_price($i['p_hosp_roll']) &&
			        $this->is_valid_width($i['width']) &&
				    $this->is_valid_content($i['content_front'])
			      ) ||
			      (
				    $this->is_digital_ground_list($this->list_id) &&
				    !($i['p_dig_res'] == $this->no_price_sql_output && $i['p_dig_hosp'] == $this->no_price_sql_output)
	//		        $this->is_valid_price($i['p_dig_res']) && $this->is_valid_price($i['p_dig_hosp'])
			      )
			    ) {
				    $this->under30Icon = '<span style="color:#bfac02;">$30</span>';
				    
				    if(is_null($this->order_by)){
					    // Separate Regular from Digitals
					    if ($i['product_type'] === Regular) {
						    array_push($this->data['items'], $i);
						    $this->table->add_row(array_values($this->create_print_list_row($i)));
					    } else {
						    array_push($this->data['digital_items'], $i);
					    }
				    }
				    else {
					    array_push($this->data['items'], $i);
					    $this->table->add_row(array_values($this->create_print_list_row($i)));
				    }

			    }
			    else {
				    // Here are the items that are missing some info !
				    array_push($this->data['items_missing_data'], $i);
			    }
		    }
	    }
	    else if ($this->group_by === Spec_Type) {
            $this->data['items'] = $results_array;
//	    	$product_ids_arr = array_column($results_array, 'product_id');
//	    	$product_ids = array_unique($product_ids_arr);
////	    	var_dump("ALL IDS", $product_ids_arr); echo "<br><br>";
//	    	foreach($product_ids as $pid){
////	    		var_dump("Product_ID", $pid); echo "<br>";
//	    		$item_ixs = array_keys($product_ids_arr, $pid);
////	    		var_dump("Item_ixs", $item_ixs); echo "<br>";
//	    		$slice = array_slice($results_array, $item_ixs[0], (count($item_ixs) > 1? end($item_ixs)-1 : 1));
////			    var_dump("Slice", $slice); echo "<br>";
//	    		$item_images = array_column($slice, 'pic_big_url');
////			    var_dump("Images", $item_images); echo "<br>";
//
//			    $product_name = $results_array[$item_ixs[0]]['product_name'];
//			    $product_type = $results_array[$item_ixs[0]]['product_type'];
////			    var_dump("Name", $product_name);echo "<br><br>";
//
//			    $c = 0;
//			    $images_html = "";
//			    foreach($item_images as $url){
//			    	if(is_null($url)) continue;
//				    if($c >= 1) break;
//			    	$images_html .= img($url, false, ["class" => "img-thumbnails"]);
//			    	$c += 1;
//			    }
//
//			    $d = [
//			      ['data' => $product_name, 'class' => 'col-30'],
//			      ['data' => anchor($this->specsheet_url($product_type, $pid), "Open specsheet", ['target' => '_blank']), 'class' => 'col-10'],
//			      ['data' => $images_html, 'class' => '']
//			    ];
//			    array_push($this->data['items'], $d);
//			    $this->table->add_row($d);
//		    }
	    }
		
	    // Process Digital Items Rows
	    if (count($this->data['digital_items']) > 0) {
		    if (count($this->data['items']) > 0) {
			    $this->table->add_row(array('data' => 'Digital', 'class' => 'h6 py-3', 'id' => 'row-digital-title', 'colspan' => count($this->data['table_header'])));
		    }
		    foreach ($this->data['digital_items'] as $i) {
			    $this->table->add_row(array_values($this->create_print_list_row($i)));
		    }
	    }

	    $this->data['table'] = array(
	      'count' => count($this->data['items']) + count($this->data['digital_items']),
	      'html' => $this->table->generate()
	    );

	    // Process missing Items Rows
	    if (count($this->data['items_missing_data']) > 0) {
		    $this->create_print_list_header();
		    foreach ($this->data['items_missing_data'] as $i) {
			    $this->table->add_row(array_values($this->create_print_list_row($i)));
		    }
	    }
	    $this->data['tableHidden'] = array(
	      'count' => count($this->data['items_missing_data']),
	      'html' => $this->table->generate()
	    );

	    // End
	    if ($this->load_view) {
		    $this->load_print_libraries(true);
		    $this->data['group_by'] = $this->group_by;
		    $this->data['header'] = $this->load->view('lists/print/_header', $this->data, true);
            if($this->group_by === Spec_Type){
                $this->load->view('lists/print/digital_book', $this->data);
//                $this->load->view('lists/print/spec_list_horizontal', $this->data);
//                $this->load->view('lists/print/spec_list_vertical', $this->data);
            }else{
                $this->load->view('lists/print/price_list', $this->data);
            }

	    }
    }

    private function specsheet_url($product_type, $product_id){
    	return site_url('reps/product/specsheet/' . $product_type . '/' . url_title($product_id));
    }

    private function create_print_list_header()
    {
        switch ($this->group_by) {
            case Spec_Type:
				//echo "CASE: [".__LINE__."] Spec_Type ($this->group_by)<br />";
                $this->data['table_header'] = array(
                    //array('thead' => 'Product name', 'filter_name' => 'Product name', 'dataName' => "no-fa-toggle"),
                    //array('thead' => 'Specsheet Link', 'filter_name' => 'Specsheet Link', 'dataName' => "no-fa-toggle"),
					array('thead' => 'use', 'filter_name' => 'Use', 'dataName' => "use", "toggle_state" => "fa-toggle-on"),
					array('thead' => 'price', 'filter_name' => 'Price', 'dataName' => "price", "toggle_state" => "fa-toggle-on"),
                    array('thead' => 'volume_price', 'filter_name' => 'Volume Price', 'dataName' => "volume_price", "toggle_state" => "fa-toggle-on"),
					array('thead' => 'product_status', 'filter_name' => 'Product Status', 'dataName' => "product_status", "toggle_state" => "fa-toggle-on"),
					array('thead' => 'Stock', 'filter_name' => 'Stock available', 'dataName' => "yardsAvailable", "toggle_state" => "fa-toggle-on"),
					array('thead' => 'stock_status', 'filter_name' => 'Stock Status', 'dataName' => "stock_status", "toggle_state" => "fa-toggle-off"),
                    //array('thead' => '', 'filter_name' => 'images', 'dataName' => "no-fa-toggle"),
					array('thead' => 'Specsheet Link', 'filter_name' => 'Specsheet Link', 'dataName' => "specsheet_link", "toggle_state" => "fa-toggle-on"),

                );
                break;
            case product:
                if ($this->data['list_id'] === $this->model->digital_grounds_list_id) {

					//echo "CASE: [".__LINE__."] Spec_Type ($this->group_by)<br />";

                    $this->data['table_header'] = array(
                        array('thead' => 'Product name', 'filter_name' => 'Product name'),
                        array('thead' => 'Stock Status', 'filter_name' => 'Stock Status'),
                        array('thead' => 'Price', 'filter_name' => 'Price', 'rename' => true),
                        array('thead' => 'Volume Price', 'filter_name' => 'Volume Price', 'rename' => true),
                        array('thead' => 'Width', 'filter_name' => 'Width'),
                        array('thead' => 'Content', 'filter_name' => 'Content'),
                        array('thead' => '', 'filter_name' => 'Show Outdoor note'),
                        array('thead' => '', 'filter_name' => 'Show 30 & Under highlight')
                    );
                } else {
					
					//echo "CASE: [".__LINE__."] Spec_Type ($this->group_by)<br />";

                    $this->data['table_header'] = array(
                        array('thead' => 'Product name', 'filter_name' => 'Product name',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Stock Status', 'filter_name' => 'Stock Status',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Price', 'filter_name' => 'Price', 'rename' => true),
//                        array('thead' => 'Hosp Cut/Yard', 'filter_name' => 'Hosp Cut/Yard', 'rename' => true),
                        array('thead' => 'Volume Price', 'filter_name' => 'Volume Price', 'rename' => true,"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Width', 'filter_name' => 'Width',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Content', 'filter_name' => 'Content',"toggle_state" => "fa-toggle-on"),
                        array('thead' => '', 'filter_name' => 'Show Outdoor note',"toggle_state" => "fa-toggle-on"),
                        array('thead' => '', 'filter_name' => 'Show 30 & Under highlight',"toggle_state" => "fa-toggle-off")
                    );
                }
                break;

            case item:
                if ($this->data['list_id'] === $this->model->digital_grounds_list_id) {
					
					//echo "CASE: [".__LINE__."] Spec_Type ($this->group_by)<br />";

                    $this->data['table_header'] = array(
                      array('thead' => 'Order', 'filter_name' => 'Order',"toggle_state" => "fa-toggle-on"),
//                      array('thead' => 'Stock Status', 'filter_name' => 'Stock Status'),
                      array('thead' => 'Stock Status', 'filter_name' => 'Stock Status',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Product name', 'filter_name' => 'Product name',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Color', 'filter_name' => 'Color',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Item #', 'filter_name' => 'Item #',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Price', 'filter_name' => 'Price', 'rename' => true),
                        array('thead' => 'Volume Price', 'filter_name' => 'Volume Price', 'rename' => true),
                        array('thead' => array('data' => 'Width', 'style' => 'display:none'), 'filter_name' => 'Width', 'visible' => false),
                        array('thead' => array('data' => 'Content', 'style' => 'display:none'), 'filter_name' => 'Content', 'visible' => false),
                        array('thead' => '', 'filter_name' => 'Show Outdoor note',"toggle_state" => "fa-toggle-on"),
                        array('thead' => '', 'filter_name' => 'Show 30 & Under highlight',"toggle_state" => "fa-toggle-off")
                    );
                } else {

					//echo "CASE: [".__LINE__."] Spec_Type ($this->group_by)<br />";
					
                    $this->data['table_header'] = array(
                      array('thead' => 'Order', 'filter_name' => 'Order',"toggle_state" => "fa-toggle-on"),
                      array('thead' => 'Status', 'filter_name' => 'Status',"toggle_state" => "fa-toggle-on"),
                      array('thead' => 'Stock Status', 'filter_name' => 'Stock Status',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Product name', 'filter_name' => 'Product name',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Color', 'filter_name' => 'Color',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Item #', 'filter_name' => 'Item #',"toggle_state" => "fa-toggle-on"),
                        array('thead' => 'Price', 'filter_name' => 'Price', 'rename' => true),
//                        array('thead' => 'Hosp Cut/Yard', 'filter_name' => 'Hosp Cut/Yard', 'rename' => true),
                        array('thead' => 'Volume Price', 'filter_name' => 'Volume Price', 'rename' => true),
                        array('thead' => array('data' => 'In Stock'), 'filter_name' => 'YardsInStock',"toggle_state" => "fa-toggle-on"),
                        array('thead' => array('data' => 'Avail.'), 'filter_name' => 'YardsAvailable',"toggle_state" => "fa-toggle-on"),
                        array('thead' => array('data' => 'Web'), 'filter_name' => 'InWebsite',"toggle_state" => "fa-toggle-on"),
                        array('thead' => array('data' => 'Width', 'style' => 'display:none'), "toggle_state" => "fa-toggle-on" ,'filter_name' => 'Width', 'visible' => false),
                        array('thead' => array('data' => 'Content', 'style' => 'display:none'), "toggle_state" => "fa-toggle-on", 'filter_name' => 'Content', 'visible' => false),
                        array('thead' => '', 'filter_name' => 'Show Outdoor note',"toggle_state" => "fa-toggle-on"),
                        array('thead' => '', 'filter_name' => 'Show 30 & Under highlight',"toggle_state" => "fa-toggle-off")
                    );
                }
                break;
        }
        $this->table->set_template($this->table_template("", " class='table table-sm table-price-list' "));
        $this->table->set_heading(array_column($this->data['table_header'], 'thead'));
    }

    private function create_print_list_row($i)
    {
        switch ($this->group_by) {
            case Spec_Type:
                return array(
                    $i['product_name'],
                    '',
                    anchor($this->specsheet_url($i['product_type'], $i['product_id']), 'Link', array('target' => '_blank'))
                );
                break;
            case product:
                if ($this->data['list_id'] === $this->model->digital_grounds_list_id) {
                    // Digital Grounds show different columns !
                    return array(
                        $i['product_name'],
                        $i['stock_status'],
                        $i['p_dig_res'],
                        $i['p_dig_hosp'],
                        $i['width'],
                        $i['content_front'],
                        ($i['outdoor'] === 'Y' ? array('data' => 'Outdoor', 'class' => 'outdoor') : '')
                    );
                } else {
                    return array(
                        $i['product_name'],
                        $i['stock_status'],
                        $i['p_res_cut'],
//                        $i['p_hosp_cut'],
                        $i['p_hosp_roll'],
                        $i['width'],
                        $i['content_front'],
                        ($i['outdoor'] === 'Y' ? array('data' => 'Outdoor', 'class' => 'outdoor') : ''),
                        ($this->data['list_id'] !== $this->model->under30_list_id && $i['product_type'] === constant('Regular') && in_array($i['product_id'], $this->under30_ids) ? array('data' => $this->under30Icon, 'class' => 'is_30under') : '')
                    );
                }
                break;

            case item:
                if ($this->data['list_id'] === $this->model->digital_grounds_list_id) {
                    // Digital Grounds show different columns !
                    return array(
                        $i['n_order'],
                        $i['stock_status'],
                        $i['product_name'],
                        $i['color'],
                        $i['code'],
                        $i['p_dig_res'],
                        $i['p_dig_hosp'],
                        array('data' => $i['width'], 'style' => 'display:none'),
                        array('data' => $i['content_front'], 'style' => 'display:none'),
                        ($i['outdoor'] === 'Y' ? array('data' => 'Outdoor', 'class' => 'outdoor') : '')
                    );
                } else {
                    return array(
                        $i['n_order'],
                        $i['status'],
                        $i['stock_status'],
                        $i['product_name'],
                        $i['color'],
                        ['data' => $i['code'], 'class' => '' . ($i['big_piece'] == '1' ? 'big-piece' : 'not-big-piece')],
                        '$ ' . $i['p_res_cut'],
//                        '$ ' . $i['p_hosp_cut'],
                        '$ ' . $i['p_hosp_roll'],
						$i['yardsInStock'],
						$i['yardsAvailable'],
						($i['web_visible'] == 'Y' ? 'Yes' : ''),
                        array('data' => $i['width'], 'style' => 'display:none'),
                        array('data' => $i['content_front'], 'style' => 'display:none'),
                        ($i['outdoor'] === 'Y' ? array('data' => 'Outdoor', 'class' => 'outdoor') : ''),
                        ($this->data['list_id'] !== $this->model->under30_list_id && $i['product_type'] === constant('Regular') && in_array($i['product_id'], $this->under30_ids) ? array('data' => $this->under30Icon, 'class' => 'is_30under') : '')
                    );
                }
                break;
        }
    }

    public function altfield()
    {
        $this->get_query = true;
        $this->index(0, product, null, false);
    }

    public function do_column_renaming(array $column_names){
    	$new_names = [];
    	foreach($column_names as $name){
    		if(strpos($name, 'p_') !== false){
//    			$new_name = substr($name, 2);
////    			$new_name = str_replace('p_', '', $new_name);
//    			$new_name = str_replace('_', ' ', $new_name);
//    			$new_name = ucwords($new_name);
//    			$new_name = str_replace(' ', '/', $new_name);
                $rename_map = [
                    "p_res_cut" => "Price",
                    "p_hosp_roll" => "Volume Price",
                    "p_dig_res" => "Dig. Price",
                    "p_dig_hosp" => "Dig. Volume Price"
                ];
                $new_name = $rename_map[$name];
		    }
    		else if(strpos($name, 'cost_') !== false) {
    			$new_name = substr($name, 5);
			    $new_name = str_replace('cost_', '', $new_name);
			    $new_name = str_replace('_', ' ', $new_name);
			    $new_name = ucwords($new_name);
			    $new_name = 'Cost/' . $new_name;
		    }
    		else {
			    $new_name = str_replace('_', ' ', $name);
			    $new_name = ucwords($new_name);
		    }
		    $new_names[] = $new_name;
	    }
    	return $new_names;
    }

    public function set_master_filters(){
    	$filters_select = [];
		foreach($this->search->filter_options['single_checkboxes'] as $checkbox_data){
			$checkbox_value = $checkbox_data['value'];
			if($this->input->post($checkbox_value) == 'on'){
				$filters_select[] = $checkbox_value;
			}
		}

		$_SESSION['master_filters'] = [
	      'restrictType' => (!is_null($this->input->post('product_type')) ? [$this->input->post('product_type')] : [Regular]),
	      'select' => $filters_select,
		  'in_master' => null,
	      'group_by' => (!is_null($this->input->post('group_by')) ? $this->input->post('group_by') : product),
	      'download_format' => (!is_null($this->input->post('download_format')) ? $this->input->post('download_format') : 'excel'),
		  'order_by' => 'product_name',
		  'run_compatibility' => false, # This compatibility run forces a couple of 'select' when not present!
		  'print_name' => $this->input->post('print_name'),
		  'filter_unnecessary_status_for_print' => true,
		  'only_inactives' => false, # One case deal with Felicia to see inactives
		  'date_format' => '%Y-%m-%d'
	    ];

		// List Selection Filters
		foreach($this->search->filter_options['multiselect'] as $multiselect_name => $_){
			if(!is_null($this->input->post($multiselect_name.'[]'))){
				$_SESSION['master_filters']['list'] = [
				  'id' => $this->input->post($multiselect_name.'[]')
				];
			}
		}

		echo json_encode(['success'=>true, 'url'=>site_url('pricelist/master')]);
    }

    public function get_master_filters(){
		$this->master_filters = $_SESSION['master_filters'];

		if(isset($this->master_filters['list'])){
			if(in_array('0', $this->master_filters['list']['id'])){
				$this->master_filters['in_master'] = true;
				$ix = array_search('0', $this->master_filters['list']['id']);
				unset($this->master_filters['list']['id'][$ix]);
			}
			if(count($this->master_filters['list']['id']) == 0){
				unset($this->master_filters['list']);
			} else {
				$this->master_filters['list']['active'] = true;
			}
		}
		else {
			// If no list was chosen, force to print Master Price List
			$this->master_filters['in_master'] = true;
		}
    }

    public function master(){
    	/*
    	 * Criteria for seaching master price list items
    	 */
	    $this->get_master_filters();

	    /*
	     * One time run where Felicia wants to see what has been inactivated since the last MPL print with OLD Criteria
	     */
//	    if($this->master_filters['only_inactives']){
//			$this->master_filters['in_master'] = false;
//			$this->master_filters['filter_unnecessary_status_for_print'] = false;
////			$this->master_filters['isPrinting'] = true;
//			$this->master_filters['print_name'] = 'Inactives List using OLD Master Price List Criteria';
//			$needed_selects = ['width', 'content_front', 'status', 'stock_status', 'price', 'outdoor'];
//			$added_selects = [];
//			foreach($needed_selects as $s){
//				if(!in_array($s, $this->master_filters['select'])){
//					$this->master_filters['select'][] = $s;
//					$added_selects[] = $s;
//				}
//			}
//	    }
//	    echo "<pre>"; var_dump($this->master_filters); return;
//        $this->master_filters["debug"] = true;
	    $items = $this->search->do_search($this->master_filters);
//	    echo "<pre>"; var_dump($items); return;

	    /*
	     * One time run where Felicia wants to see what has been inactivated since the last MPL print with OLD Criteria
	     */
	    if($this->master_filters['only_inactives']){
			$this->load_view = false;
			$this->data['list_id'] = 0;
			$this->list_id = 0;
			$this->group_by = $this->master_filters['group_by'];
			$this->print_pricelist($items);
			/*
			 * We can now access
			 *  $this->data['items']
			 *  $this->data['items_missing_data']
			 */
		    $items = $this->data['items'];
		    $added_selects = array_diff( $added_selects, ['price'] );
		    delete_col($items, $added_selects);

//		    $items[] = $this->data['items_missing_data'];
	    }
//	    echo "<pre>"; var_dump($items); return;

	    if($this->master_filters['download_format'] == 'pdf'){
	    	$title = $this->master_filters['print_name'];
	    	$this->_print_pdf($items, $title);
	    }
	    else if($this->master_filters['download_format'] == 'excel'){
	    	$filename = $this->master_filters['print_name'] . ' ' . date("Y-m-d h-i") . '.xlsx';
		    $this->load->library("SimpleXLSXGen", [], 'SimpleXLSXGen');
		    if(count($items) == 0){
			    $xlsx = SimpleXLSXGen::fromArray([["No results were found."]]);
			    $xlsx->downloadAs($filename);
			    return;
		    }
		    $column_names = array_keys($items[0]);
		    $column_names = $this->do_column_renaming($column_names);
		    $xlsx_result = array_merge([$column_names], $items);
		    $xlsx = SimpleXLSXGen::fromArray( $xlsx_result );
		    $xlsx->downloadAs($filename);
	    }
	    else if($this->master_filters['download_format'] == 'json'){
	    	echo json_encode($items);
	    }
	    else if($this->master_filters['download_format'] == 'dump'){
	    	echo "<pre>"; var_dump(['filters'=>$this->master_filters, 'result'=>$items]); return;
	    }
    }

	private function _print_pdf($results_array, $title='')
	{
		if(count($results_array) == 0)
		{
			echo "No results"; return;
		}
		$column_keys = array_keys($results_array[0]);
		$cols_to_exclude = ['product_id', 'product_type', 'item_id', 'p_dig_res', 'p_dig_hosp', 'price_date', 'cost_date'];
//		array_push($cols_to_exclude, 'p_hosp_roll', 'p_hosp_cut');
		$price_columns = [
            'p_res_cut',
//            'p_hosp_cut',
            'p_hosp_roll'
        ];
//		$column_key_to_name = [];
//		foreach($column_keys as $col_name){
//			$column_key_to_name[$col_name] = $this->do_column_renaming([$col_name]);
//		}
		$table_column_keys = array_diff($column_keys, $cols_to_exclude);
		$table_column_title = $this->do_column_renaming($table_column_keys);

//		echo "<pre>";
//		var_dump($table_column_keys);
//		var_dump($table_column_title);
//		var_dump($results_array);
//		return;

		$table_regular = [];
		$table_digital = [];
		// Need to separate Regular vs Digital items as these will print on different tables
		foreach($results_array as $result_row){
			$tr = [];
			if($result_row['product_type'] == Regular){
//				Regular product line
				foreach($table_column_keys as $k){
					$val = $result_row[$k];
					if(in_array($k, $price_columns)){
						$val = "$ " . $val;
					}
					$tr[] = $val;
				}
				$table_regular[] = $tr;
			}
			else {
				// Digital product line
				foreach($table_column_keys as $k){
					$val = $result_row[$k];
					if(in_array($k, $price_columns)){
						$val = "$ " . $val;
					}
					if($k == 'p_res_cut'){
						$tr[] = $val;
					}
					else if($k == 'p_hosp_cut'){
						$tr[] = $val;
					}
					else {
						$tr[] = $val;
					}

				}
				$table_digital[] = $tr;
			}
		}

		$this->table->set_template($this->table_template("", " class='table table-sm table-price-list' "));
		$this->table->set_heading($table_column_title);

		$this->data['table'] = [
		  'title' => $title,
		  'html' => $this->table->generate($table_regular)
		];

		$this->load_print_libraries();
		$this->load->view('lists/print/price_list_new', $this->data);
	}


	/*
	 *
	 *
	 */

    private function is_digital_ground_list($id)
    {
        return $this->list_id === 1;
    }

    private function is_valid_stock_status($str)
    {
        return strlen($str) > 0;
    }

    private function is_valid_content($str)
    {
        return strlen(trim($str)) > 0;
    }

    private function is_valid_width($str)
    {
        return $str !== '0.00"';
    }

    private function is_valid_price($str){
    	return $str !== $this->no_price_sql_output;
    }
}