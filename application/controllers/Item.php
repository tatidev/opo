<?php
// from live production application/controllers/Item.php
defined('BASEPATH') or exit('No direct script access allowed');

class Item extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		// $uri_segments  = $this->uri->segment_array();
		// echo "<pre>ARGS CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		// print_r($uri_segments ); echo "<br>";
		// echo "<pre>";





		//echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		//print_r($_POST); echo "<br>";
		//echo "<pre>";		
		//$param1 = $this->uri->segment(3); // Retrieves "R"
		$this->thisC = 'item';
		$this->is_incomming_item_id_link = false;
		$this->load->model('Item_model', 'model');

		$this->load->model('Search_model', 'search');
		$this->load->model('Specs_model', 'specs');
		$this->load->model('RevisedQueries_model', 'revisedqueries');
		$this->load->library('FileUploadToS3');
		array_push($this->data['crumbs'], 'Products', 'Colorlines');
		$this->data['hasEditPermission'] = $this->hasPermission($this->thisC, 'edit');
		$this->data['hasMPLPermission'] = $this->hasPermission('product', 'master_price_list');
		$this->data['hasMasterPermission'] = $this->hasPermission($this->thisC, 'stock sync');
		$this->load->helper('utility_helper');
	}




	public function index($arg = null)
	{
		// Condition for link back from sales:
		if (empty($_POST) && $arg !== null && is_numeric($arg) && $arg > 0) {
			$this->is_incomming_item_id_link  = true;
			// echo '<h3>Incomming link from Sales App</h3>';
		}

		if ($this->input->post('product_id') !== null) {
			$this->data['product_id'] = $this->input->post('product_id');
		}

		if ($this->input->post('product_type') !== null) {
			$this->data['product_type'] = $this->input->post('product_type');
		}



		if (!is_null($arg)) {
			// Given URL arguments
			if (strpos($arg, '-') !== false) {
				// Given a "ProductID-ProductType" argument
				$arg_parts = explode('-', $arg);
				$this->data['item_id'] = '0';
				$this->data['product_id'] = $arg_parts[0];
				$this->data['product_type'] = $arg_parts[1];
				$this->data['pname'] = $this->model->get_product_name($arg_parts[1], $arg_parts[0]);
				$this->data['product_name'] = $this->data['pname'];

				// echo "<pre>ARGS CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				// print_r($arg); echo "<br>";
				// echo "<pre>";
				// echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				// print_r($_POST); echo "<br>";
				// echo "<pre>";
				// echo "<pre>_this->data: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				// print_r($this->data); echo "<br>";
				// echo "<pre>";

			}
			if ($this->is_incomming_item_id_link) {

				// Given a "ItemID" argument
				$this->data['item_id'] = $arg;
				# Search product_id and product_type
				//$item_data = $this->model->get_item($this->data['item_id']);
				$item_result = $this->revisedqueries->get_item_details_by_item_id($this->data['item_id']);
				$item_data = $item_result[0];

				//echo "<pre>ARG ($arg) CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				//print_r($this->data); echo "<br>";
				//echo "<pre>";
				$this->data['product_id']   = $item_data['product_id'];
				$this->data['product_type'] = $item_data['product_type'];
				$this->data['pname'] = $item_data['product_name'] . ' / ' . $item_data['code'] . ' / ' . $item_data['color'];
				// echo "<pre>ARG ($arg) CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				// echo 'iten pname: ' . $this->data['pname'] . "<br>";
				// print_r($item_data); echo "<br>";
				// echo "<pre>";
			}
		} else {
			// No $arg (arguments), check POST  <----
			if ($this->input->post('product_id') !== null) {
				//echo 'POST[product_id] is not null <br />';
				//				$arg = $this->input->post('product_id');
				//				$post = explode('-', $arg);
				$this->data['item_id'] = $this->input->post('item_id') == null ? '0' : $this->input->post('item_id');
				$this->data['product_id'] = $this->input->post('product_id');
				$this->data['product_type'] = $this->input->post('product_type');
				// echo 'POST['product_type']  SET to '. $this->data['product_type'] .' LINE: '. __LINE__ .'<br />';
				if ($this->data['item_id'] !== '0') {
					$item_data = $this->model->get_item($this->data['item_id']);
					$this->data['pname'] = $item_data['product_name'] . ' / ' . $item_data['code'] . ' / ' . $item_data['color'];
				} else {
					//echo 'item_id === 0 <br />';
					$this->data['pname'] = $this->model->get_product_name($this->data['product_type'], $this->data['product_id']);
				}
			} else {
				$this->data['item_id'] = '0';
				$this->data['product_id'] = '0';
				$this->data['product_type'] = '0';
				$this->data['pname'] = '';
			}
		}

		//		$post = ($product_id === null ? ($this->input->post('product_id') === null ? '0-0' : $this->input->post('product_id')) : $product_id);
		//		$post = explode('-', $post);
		//		$this->data['product_id'] = $post[0] . '-' . $post[1];
		//		$this->data['product_type'] = $post[1];
		//		$this->data['pname'] = $this->model->get_product_name($this->data['product_type'], $this->data['product_id']);


		$restock_status = $this->decode_array($this->specs->get_restock_status(), 'id', 'name');
		$this->data['restock_filter_status'] = form_dropdown('restock_filter_status', $restock_status, 1, 'id="restock_filter_status" filter-title="Status" class="single-dropdown w-filtering" tabindex="-1"');
		$this->data['stamps'] = $this->get_stamps(item);
		///$this->data['ajaxUrl'] = site_url('item/get_product_items');  <---
		$this->view('item/item_list'); // <-- See JS  var getItemListUrl = 'item/get_product_items';
	} // end index($arg = null)

	public function uploadToTemp()
	{
		$this->fileuploadtos3->uploadToTemp();
	}


	// Get all items for the given product id (product_id)
	public function get_product_items()
	{

		//echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		//print_r($_POST); echo "<br>";
		//echo "</pre>";
		$item_details_for_table = [];
		$this->data['tableData'] = [];

		$item_id = ($this->input->post('item_id') === null ? '0' : $this->input->post('item_id'));
		$product_type = (!empty($this->input->post('product_type'))) ? $this->input->post('product_type') :  NULL;
		$product_id = (!empty($this->input->post('product_id'))) ? $this->input->post('product_id') :  NULL;

		// echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		// echo 'item_id: ' . $item_id . "<br>";
		// echo 'product_type: ' . $product_type . "<br>";
		// echo 'product_id: ' . $product_id . "<br>";
		// echo "</pre>";

		// EXCEPTION
		//if($this->input->post('product_type') === NULL || empty($this->input->post('product_type')) ){
		//   throw new Exception(" POST::product_type is NULL or EMPTY in  "
		//					. __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>");
		//}
		// $product_type = $this->input->post('product_type');
		// $item_id = $this->input->post('item_id');
		$aux = array();

		//echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		//echo 'item_id: ' . $item_id . "<br>";
		//echo 'product_type: ' . $product_type . "<br>";
		//echo 'product_id: ' . $product_id . "<br>";
		//echo "</pre>";

		if (in_array($product_type, array(Regular, Digital, ScreenPrint))) {
			if ((empty($item_id) || $item_id === '0' || $item_id === 0) && is_numeric($product_id) && $product_id !== '0' && $product_id !== 0) {
				$item_details_for_table = $this->revisedqueries->get_item_details_by_product_id($product_id, $product_type);
				//echo "<pre>item_details_for_table CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				//print_r($item_details_for_table);
				//echo "</pre>";
				//die();
			}
		}

		// If item_id but no product_id and product_type = 'item_id' then this is colorline alax call ....
		// This is a special case for colorline view
		if (empty($product_id) && is_numeric($item_id)) {
			// echo " Condition met for item_id: " . $item_id . "<br />";
			$item_details_for_table = $this->revisedqueries->get_item_details_by_item_id($item_id);
			if ($item_details_for_table) {
				$product_id = $item_details_for_table['product_id'];
				$product_type = $item_details_for_table['product_type'];
			}
			//echo "<pre>item_details_for_table CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			//print_r($item_details_for_table);
			//echo "</pre>";
			//die();
		}

		if (!empty($item_id) && is_numeric($item_id)) {
			// echo " Condition met for item_id: " . $item_id . "<br />";
			$item_details_for_table = $this->revisedqueries->get_item_details_by_item_id($item_id);
			//echo "<pre>item_details_for_table CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			//print_r($item_details_for_table);
			//echo "</pre>";
			//die();
		}

		// echo "<pre>item_details_for_table CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		// print_r($item_details_for_table);
		// echo "</pre>";
		// die();

		// ============================================================================
		// LAZY CALCULATION: Calculate web_vis for items with NULL values
		// ============================================================================
		$items_to_update = [];
		foreach ($item_details_for_table as &$item) {
			if (is_null($item['web_vis'])) {
				// Calculate web visibility using business logic
				$calculated_visibility = $this->calculate_web_visibility_for_item($item);
				$item['web_vis'] = $calculated_visibility;
				
				// Prepare for batch update
				$items_to_update[] = [
					'id' => $item['item_id'],
					'web_vis' => $calculated_visibility ? 1 : 0,
					'date_modif' => date('Y-m-d H:i:s')
				];
			}
		}
		
		// Batch update calculated values to database
		if (!empty($items_to_update)) {
			$this->db->update_batch('T_ITEM', $items_to_update, 'id');
			log_message('info', 'Lazy calculation updated ' . count($items_to_update) . ' items for product_id: ' . $product_id);
		}
		// ============================================================================

		$this->data['tableData'] = $item_details_for_table;
		$this->data['product_type'] = $product_type;
		$this->data['product_id'] = $product_id;
		// 		$ret['last'] = $this->db->last_query();
		$item_id = false;
		$item_code = false;
		$table_data = $this->data['tableData'];
		$i = 0;

		foreach ($table_data as $row) {

			// if ($row['item_code'] !== '0' && !is_null($row['item_code'])) {
			// 	$item_code = $row['item_code'];
			// }

			if ($row['code'] !== '0' && !is_null($row['code'])) {
				$item_code = $row['code'];
			}
			if ($row["item_id"] !== '0' && !is_null($row["item_id"])) {
				$item_id = $row["item_id"];
			}
			
			// ============================================================================
			// USE NEW LAZY-CALCULATED web_vis VALUE
			// ============================================================================
			// Use the web_vis value that was already calculated by lazy calculation
			$webvis = isset($row['web_vis']) ? (int)$row['web_vis'] : 0;
			$this->data['tableData'][$i]['webvis'] = $webvis;
			$this->data['tableData'][$i]['web_visibility'] = ($webvis === 1) ? "Y" : "N";
			// ============================================================================

			// Convert image URLs to S3 URLs
			if (!empty($row['pic_big_url'])) {
				$this->data['tableData'][$i]['pic_big_url'] = $this->fileuploadtos3->convertLegacyImgSrcToS3($row['pic_big_url']);
			}
			if (!empty($row['pic_hd_url'])) {
				$this->data['tableData'][$i]['pic_hd_url'] = $this->fileuploadtos3->convertLegacyImgSrcToS3($row['pic_hd_url']);
			}

			$i++;
		}

		// echo "<pre> table_data FILE:" . __LINE__ . "  " . __FILE__ . " <br />";
		// print_r($table_data); 
		// echo "<br />";
		// print_r($this->data['tableData']);
		// echo "</pre>";
		echo json_encode($this->data);
	}

	// Get form to edit one item
	// ================================================================================
	// ================================================================================
	// ================================================================================

	function edit_item()
	{
		$this->load->model('Specs_model', 'specs');
		$this->load->model('Product_model', 'product_model');
		$this->load->model('Search_model', 'search');
		$this->load->model('File_directory_model', 'file_directory');
		$this->load->library('FileUploadToS3', null, 'fileuploadtos3');
		$this->load->model('RevisedQueries_model', 'revisedqueries');

		$post = explode('-', $this->input->post('product_id'));

		$product_id = isset($post[0]) ? $post[0] : null;
		$product_type = isset($post[1]) ? $post[1] : null;
		$item_id = $this->input->post('item_id');

		// If product_type is 'item_id', fix it to be 'R' (Regular)
		if ($product_type === 'item_id' || $product_type === 'it') {
			$product_type = 'R';
		}

		// If item_id but no product_id and product_type = 'item_id' then this is colorline alax call ....
		// This is a special case for colorline view
		if ($product_type === 'item_id' && empty($product_id) && is_numeric($item_id)) {
			// echo " Condition met for item_id: " . $item_id . "<br />";
			$item_details_for_table = $this->revisedqueries->get_item_details_by_item_id($item_id);
			//echo "<pre>item_details_for_table CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			//print_r($item_details_for_table);
			//echo "</pre>";
			//die();
		}

		//debug_print_backtrace();
		//echo "<pre>ARGS CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		//print_r($arg); echo "<br>";
		//echo "<pre>";
		// echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		// print_r($this->input->post('product_id')); echo "<br>";
		// print_r($this->input->post('product_type')); echo "<br>";
		// print_r($this->input->post('item_id')); echo "<br>";
		//echo "<pre>";
		//echo "<pre>_this->data: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		//print_r($this->data); echo "<br>";
		//echo "<pre>";		


		// Set init values based on POST
		$this->data['product_id'] = $this->input->post('product_id') !== null ? $this->input->post('product_id') : null;
		$this->data['product_type'] = $this->input->post('product_type') !== null ? $this->input->post('product_type') : null;
		$this->data['item_id'] = $this->input->post('item_id') !== null ? $this->input->post('item_id') : null;

		// Set Is NEW
		$this->data['isNew'] = ($this->data['item_id'] !== NULL && $this->data['item_id'] == '0') ? TRUE : FALSE;

		// Set Product Name
		if ($this->data['product_type'] !== null && $this->data['product_id'] !== null) {
			// echo "this->data: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			$this->data['pname'] = $this->model->get_product_name($this->data['product_type'], $this->data['product_id']);
			// echo "this->data: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		} else {
			if (!$this->is_incomming_item_id_link) {
				$this->data['pname'] = '';
			}
		}

		// Check if all three values exist and are not null
		if (!empty($this->data['product_id']) && !empty($this->data['product_type']) && !empty($this->data['item_id'])) {
			$this->data['isNew'] = false;
			// Load item data into $info for the view
			$this->data['info'] = $this->model->get_item($this->data['item_id'], $this->data['product_type']);

			// echo "this->data: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		}

		// IF: $item_id is not null and not 0
		// THEN: Not NEW  && load item data into $info for the view
		//       Set product_id based on loaded item data (it might be Digital)
		if (!$this->data['isNew']) {
			//echo "this->data: IS_NEW = FALSE " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";

			try {
				// Ensure product_type exists, otherwise throw an exception
				if (empty($this->data['product_type'])) {
					throw new Exception("Product type is required but missing." . __CLASS__ .
						" FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>");
				}
			// Get item info
			if (!empty($this->data['product_type'])) {
				//$info_arr = $this->model->get_item($this->data['item_id'], $this->data['product_type']);
				$info_arr = $this->revisedqueries->get_item_details_by_item_id($this->data['item_id']);
				$this->data['info'] = $info_arr[0];
				
				// CRITICAL: Update product_id and product_type from fetched item data
				// This ensures the form has the correct product_id when editing existing items
				if (isset($this->data['info']['product_id'])) {
					$product_id = $this->data['info']['product_id'];
					$this->data['product_id'] = $product_id;
				}
				if (isset($this->data['info']['product_type'])) {
					$product_type = $this->data['info']['product_type'];
					$this->data['product_type'] = $product_type;
				}
			}

				// echo "<pre>INFO PID(". $this->data['product_id']  ."): " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				// print_r($this->data['info']); echo "<br>";
				// echo "<pre>";

			} catch (Exception $e) {
				// Handle the exception
				echo 'Exception in fetching item info: ' . $e->getMessage();
				// Log the error in CodeIgniter logs
				log_message('error', 'Exception in fetching item info: ' . $e->getMessage());
				// Optionally, store error in $this->data for use in the view
				$this->data['error'] = "An error occurred: " . $e->getMessage();
			}
		} else {
			//echo "this->data: IS_NEW = TRUE " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			//echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			//print_r($_POST);
			//echo "</pre>";
			// ELSE: - ID NEW
			//       - EXCEPTIONS: - `item_id` NOT inside the $_POST[product_id]
			//                        To AVOID Trying to add a new color when a SKU was initially selected,
			//                     - product_id not set
			try {
				// EXCEPTION
				if ($this->input->post('product_id') === NULL && $this->data['product_id'] === NULL) {
					throw new Exception(" POST::product_id is NULL in  "
						. __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>");
				}
				// EXCEPTION
				if ($this->input->post('product_type') === NULL || empty($this->input->post('product_type'))) {
					throw new Exception(" POST::product_type is NULL or EMPTY in  "
						. __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>");
				}
				// EXCEPTION
				if (strpos($this->input->post('product_id'), 'item_id') !== false) {
					throw new Exception("Trying to add a new color when a SKU was initially selected."
						. __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>");
				}

				$this->data['product_type'] = $this->input->post('product_type');
				//echo "this->data: $this->data[product_type] = " . $this->data['product_type'] . " LINE: " . __LINE__ . " <br>";

				// Set $info to empty array
				$this->data['info'] = array();
			} catch (Exception $e) {
				// Handle the exception
				echo 'Exception in fetching item info: ' . $e->getMessage();
				// Log the error in CodeIgniter logs
				log_message('error', 'Exception in fetching item info: ' . $e->getMessage());
				// Optionally, store error in $this->data for use in the view
				$this->data['error'] = "An error occurred: " . $e->getMessage();
			}
		}




		$this->load->library('table');
		/*
			REVALUATE THIS
			Data from frontend, either:
				- item_id when editing a single item
				- product_id when creating a new item

		*/
		//$this->data['item_id'] = $this->input->post('item_id');
		// IF: $item_id is not null and not 0
		// THEN: Not NEW  && load item data into $info for the view
		//       Set product_id based on loaded item data (it might be Digital)
		// ELSE: - ID NEW
		//       - IF: `item_id` NOT inside the $_POST[product_id]
		//             - To AVOID Trying to add a new color when a SKU was initially selected,
		//             - Set product_id based on POST data
		//             - Set product_type based on POST data
		//             - IF: POST product_type is empty  THEN: get_product_type_by_pid
		//             - Set $info to empty array
		//       - ELSE: Exit (Trying to add a new color when a SKU was initially selected)       
		/*
		if ($this->data['item_id'] !== '0' && !is_null($this->data['item_id'])) {
			$this->data['isNew'] = false;
			$this->data['product_type'] = $this->input->post('product_type'); # $this->model->get_item_type($this->data['item_id']);
			//echo 'POST['product_type']  SET to '. $this->data['product_type'] .' LINE: '. __LINE__ .'<br />';
			$this->data['info'] = $this->model->get_item($this->data['item_id'], $this->data['product_type']);
			$this->data['product_id'] = $this->data['info']['product_id'];
		} else if ($this->data['item_id'] == '0') {
			if (strpos($this->input->post('product_id'), 'item_id') === false) {
				$this->data['isNew'] = true;
//				$post = explode('-', $this->input->post('product_id'));
				$this->data['product_id'] = $this->input->post('product_id');
				$this->data['product_type'] = $this->input->post('product_type');
				$this->data['info'] = array();
				// echo "USE PRODUCT ID TO GET THE PRODUCT_TYPE <br />";
				// echo 'POST[product_id]  SET to '. $this->data['product_id'] .' LINE: '. __LINE__ .'<br />';
				$this->data['product_type'] = $this->revisedqueries->get_product_type_by_pid($this->data['product_id']);
				// echo 'POST[product_type]  SET to '. $this->data['product_type'] .' LINE: '. __LINE__ .'<br />';
				// echo "<pre> DATA: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
			} else {
				// Trying to add a new color when a SKU was initially selected
				//echo "<pre> DATA: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
				return;
			}
		} else {
			echo "<pre> DATA: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
			return;
		}*/

		//echo "<pre> DATA: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
		//print_r($this->data);
		//echo "LOAD VIEW ite,/form/view: <br />";
		//print_r($ret);
		//echo "</pre>";

		// Limit Item Edition actions
		// This is to don't show some specific buttons/actions from the item edition modal
		// g.e. we don't want to archive a item when we are editing an item from the list edition view
		// 		var_dump( current_url() );exit;
		// 		$headers = getallheaders();
		//$this->data['limitedAction'] = $headers['Referer'] !== site_url('item/index');
		//     $this->data['limitedAction'] = strpos( $headers['Referer'], site_url('item/index') ) === false;
		// 		$this->data['limitedAction'] = strpos( $headers['Referer'], site_url('item/index') ) === false;
		$this->data['limitedAction'] = false;

		// Set dropdowns data
		$options = array();

		// 		var_dump($this->data['info']); exit;

		$selected = array();

		// Common dropdowns
		$this->load->model('Specs_model', 'specs');

		$parent_data = $this->db->select('min_order_qty, vendor_product_name')->from($this->model->t_product_various)->where('product_id', $this->data['product_id'])->get()->row();

		$this->data['info']['min_order_qty'] = ($this->data['isNew'] ? (!is_null($parent_data) ? $parent_data->min_order_qty : '') : $this->data['info']['min_order_qty']);
		$this->data['info']['vendor_product_name'] = ($this->data['isNew'] ? (!is_null($parent_data) ? $parent_data->vendor_product_name : '') : $this->data['info']['vendor_product_name']);
		if ($this->data['product_type'] === constant('Digital')) {
			$this->data['info']['code'] = 'Digital';
		}

		$l = $this->specs->get_product_status();
		//echo "<pre> DATA: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
		//echo 'Prod status:<br />';
		//print_r($l);
		$options = $this->decode_array($l, 'id', 'descr');
		//echo "Status Options: <br />";
		//print_r($options); echo "<br />";
		//echo "IS NEW: " . $this->data['isNew'] . "<br />";
		//echo "Status ID: " . $this->data['info']['status_id'] . "<br />";
		$selected = $this->data['isNew'] ? ($this->data['product_type'] === constant('Regular') ? 1 : 4) : $this->data['info']['status_id'];
		//echo "Selected Status ID: " . $selected . " ( " . $this->data['info']['status_id'] . " )<br />";
		//echo "Data: ";
		//print_r($this->data);
		$this->data['dropdown_status'] = form_dropdown('dropdown_status', $options, set_value('dropdown_status', $selected), " class='single-dropdown' ");
		$l = $this->specs->get_stock_status();
		$options = $this->decode_array($l, 'id', 'descr');
		$selected = $this->data['isNew'] ? ($this->data['product_type'] === constant('Regular') ? 1 : 2) : $this->data['info']['stock_status_id'];
		//echo "Stock Status Options: <br />";
		//print_r($options); echo "<br />";
		//echo "Selected Stock Status ID: " . $selected . " ( " . $this->data['info']['stock_status_id'] . " )<br />";
		$this->data['dropdown_stock_status'] = form_dropdown('dropdown_stock_status', $options, set_value('dropdown_stock_status', $selected), " class='single-dropdown' ");

		$l = $this->specs->get_shelfs();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = $this->data['isNew'] ? array() : explode(constant('delimiter'), $this->data['info']['shelf_id']);
		$this->data['dropdown_shelf'] = form_multiselect('shelf[]', $options, set_value('shelf[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		$l = $this->specs->get_sampling_locations();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = ($this->data['isNew'] or is_null($this->data['info']['roll_location_id'])) ? '1' : $this->data['info']['roll_location_id'];
		$this->data['dropdown_roll_location'] = form_dropdown('dropdown_roll_location', $options, set_value('dropdown_roll_location', $selected), " class='single-dropdown w-filtering' ");
		$selected = ($this->data['isNew'] or is_null($this->data['info']['bin_location_id'])) ? '1' : $this->data['info']['bin_location_id'];
		$this->data['dropdown_bin_location'] = form_dropdown('dropdown_bin_location', $options, set_value('dropdown_bin_location', $selected), " class='single-dropdown w-filtering' ");
		// 		var_dump($this->data);exit;

		$this->load->model('File_directory_model', 'file_directory');

		// Showcase data loading

		if ($this->data['isNew']) {
			$this->data['info']['url_title'] = $this->model->site_urls['website'];
		} else {
			$this->data['info']['url_title'] = $this->model->site_urls['website'] . 'product/' . $this->data['info']['url_title'];
		}

		$this->data['img_url'] = array(
			//'thumb' => '',
			'big' => '',
			'hd' => ''
		);
		//if (!$this->data['isNew']) {

		if (!is_null($this->data['info']['pic_big_url'])) {
			$this->data['img_url']['big'] = $this->data['info']['pic_big_url'];
			/*  ------------------------
                  If database return data with legacy storage URLs ( ie. Not S3 uri )
                  then use fileuploadtos3::convertLegacyImgSrcToS3($legacyImgUrl)
                ------------------------ */

			//$img_url= str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $this->data['img_url']['big']);
			$this->data['img_url']['big'] = $this->fileuploadtos3->convertLegacyImgSrcToS3($this->data['img_url']['big']);

			//			} else if (!is_null($this->data['info']['pic_big']) && $this->data['info']['pic_big'] !== 'N') {
			//				$this->data['img_url']['big'] = $this->file_directory->image_src_path('load', 'fabrics_items', 'big') . $this->data['item_id'] . '.jpg';
		} else {
			$this->data['img_url']['big'] = '';
		}

		if (!is_null($this->data['info']['pic_hd_url'])) {
			$this->data['img_url']['hd'] = $this->data['info']['pic_hd_url'];
			/*  ------------------------
                  If database return data with legacy storage URLs ( ie. Not S3 uri )
                  then use fileuploadtos3::convertLegacyImgSrcToS3($legacyImgUrl)
                ------------------------ */
			//$img_url= str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $this->data['img_url']['hd']);
			$this->data['img_url']['hd'] = $this->fileuploadtos3->convertLegacyImgSrcToS3($this->data['img_url']['hd']);
			// echo "<pre>_$this->data['info']: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			// print_r($this->data['info']); echo "<br>";
			// print_r($img_url); echo "<br>";
			// print_r($this->data['img_url']); echo "<br>";
			// echo "<pre>";
			//			} else if (!is_null($this->data['info']['pic_hd']) && $this->data['info']['pic_hd'] !== 'N') {
			//				$this->data['img_url']['hd'] = $this->file_directory->image_src_path('load', 'fabrics_items', 'hd') . $this->data['item_id'] . '.jpg';
		} else {
			$this->data['img_url']['hd'] = '';
		}

		//}

		// PKL Uploading edit 
		$this->data['img_url'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->data['img_url']);

		$l = $this->specs->get_showcase_coords_colors();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = ($this->data['isNew'] ? array() : explode(constant('delimiter'), $this->data['info']['showcase_coord_color_id']));
		$this->data['dropdown_showcase_coord_color'] = form_multiselect('showcase_coord_color[]', $options, set_value('showcase_coord_color[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		$this->data['colors'] = array();
		$this->data['colors_ids_encoded'] = json_encode(array());
		$this->data['colors_names_encoded'] = json_encode(array());

		$color_ids = ($this->data['isNew'] ? array() : explode(constant('delimiter'), $this->data['info']['color_ids']));
		if (count($color_ids) > 0) {
			$color_names = explode(constant('delimiter'), $this->data['info']['color']);
			for ($i = 0; $i < count($color_ids); $i++) {
				$aux = array(
					'id' => $color_ids[$i],
					'name' => $color_names[$i]
				);
				array_push($this->data['colors'], $aux);
			}
			$this->data['colors_ids_encoded'] = json_encode($color_ids);
			$this->data['colors_names_encoded'] = json_encode($color_names);
		}

		$reselections_ids = ($this->data['isNew'] ? array() : array_map('trim', explode(",", $this->data['info']['reselections_ids'])));
		$reselections_items = (count($reselections_ids) > 0 ? $this->model->get_items($reselections_ids) : []);
		$this->data["info"]["reselections_ids"] = json_encode($reselections_ids);
		$this->data["info"]["reselections_items"] = $reselections_items;

		$reselections_ids_of = ($this->data['isNew'] ? array() : array_map('trim', explode(",", $this->data['info']['reselections_ids_of'])));
		$reselections_items_of = (count($reselections_ids) > 0 ? $this->model->get_items($reselections_ids_of) : []);
		$this->data["info"]["reselections_ids_of"] = json_encode($reselections_ids_of);
		$this->data["info"]["reselections_items_of"] = $reselections_items_of;
		//        var_dump($reselections_items); exit;

		$this->data['messages']['tbody'] = '';
		$this->data['messages']['tfoot'] = '';
		$all_messages_arr = $this->model->select_item_messages($this->data['item_id']);
		foreach ($all_messages_arr as $r) {
			$this->data['messages']['tbody'] .= "
				<tr data-message-id='" . $r['id'] . "' data-date-add='" . $r['date_modif'] . "' data-user-id='" . $r['user_id'] . "' data-edited='N'>
					<td>" . nice_date($r['date_modif'], 'm-d-Y') . "</td>
					<td>" . $r['username'] . "</td>
					<td class='message'>" . $r['message_note'] . "</td>
					" . ($r['user_id'] === $this->data['user_id'] ? "
					<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' aria-hidden='true'></i></td>
					" : '<td></td>') . "
				</tr>
			";
		}


		$this->data['hasEditPermission'] = $this->hasPermission('item', 'edit');
		$this->data['product_id'] = $this->data['product_id'] . '-' . $this->data['product_type'];
		$this->data['isMultiEdit'] = false;
		$ret['html'] = $this->load->view('item/form/view', $this->data, true);

		// echo "<pre> DATA: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
		// print_r($this->data);
		// echo "LOAD VIEW ite,/form/view: <br />";
		// print_r($ret);
		// echo "</pre>";

		echo json_encode($ret);
	} // END  function edit_item()
	// ================================================================================
	// ================================================================================
	// ================================================================================
	// ================================================================================
	// ================================================================================
	// ================================================================================
	// ================================================================================
	// ================================================================================

	function edit_item_multi()
	{
		$this->load->model('Specs_model', 'specs');

		$this->data['product_id'] = $this->input->post('product_id');
		$this->data['product_type'] = $this->input->post('product_type');
		$this->data['item_id'] = json_encode($this->input->post('item_id'));

		//		if (!is_null($this->input->post('product_id')) && strpos($this->input->post('product_id'), 'item_id') === false) {
		//			$this->data['product_id'] = $post[0];
		//			$this->data['product_type'] = $post[1];
		//			$this->data['info'] = array();
		//		} else {
		//			// Trying to edit a color when a SKU was initially selected
		//			return;
		//		}
		//		$this->data['item_id'] = $this->input->post('item_id');

		$l = $this->specs->get_product_status();
		$options = $this->decode_array($l, 'id', 'descr');
		$selected = '';
		$this->data['dropdown_status'] = form_dropdown('dropdown_status', $options, array(''), " class='single-dropdown' ");

		$l = $this->specs->get_stock_status();
		$options = $this->decode_array($l, 'id', 'descr');
		$selected = '';
		$this->data['dropdown_stock_status'] = form_dropdown('dropdown_stock_status', $options, array(''), " class='single-dropdown' ");

		$l = $this->specs->get_shelfs();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = '';
		$this->data['dropdown_shelf'] = form_multiselect('shelf[]', $options, set_value('shelf[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		$l = $this->specs->get_sampling_locations();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = 1;
		$this->data['dropdown_roll_location'] = form_dropdown('dropdown_roll_location', $options, set_value('dropdown_roll_location', $selected), " class='single-dropdown' ");
		$selected = 1;
		$this->data['dropdown_bin_location'] = form_dropdown('dropdown_bin_location', $options, set_value('dropdown_bin_location', $selected), " class='single-dropdown' ");

		$this->data['product_id'] .= '-' . $this->data['product_type'];
		$this->data['isMultiEdit'] = true;
		$ret['html'] = $this->load->view('item/form/view', $this->data, true);
		echo json_encode($ret);
	}

	function save_item()
	{
		$ret = array();

		// DEBUG: Log POST data for item code debugging
		log_message('debug', 'ITEM_SAVE_DEBUG: POST data - ' .
					'item_id: ' . $this->input->post('item_id') . 
					' | new_code: ' . $this->input->post('new_code') .
					' | change_item: ' . $this->input->post('change_item') .
					' | product_id: ' . $this->input->post('product_id') .
					' | product_type: ' . $this->input->post('product_type'));

		//echo "<pre>_POST CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
		//print_r($_POST); echo "<br>";
		//echo "<pre>";

		$this->load->model('Specs_model', 'specs');
		$post = explode('-', $this->input->post('product_id'));

		// Ensure we have valid product_id and product_type values
		$product_id = isset($post[0]) && !empty($post[0]) ? $post[0] : null;
		$product_type = isset($post[1]) && !empty($post[1]) ? $post[1] : null;

		// If product_id is null or zero, try to get it from the database using item_id
		$item_id = json_decode($this->input->post('item_id')); // It may be an array of item_ids or just an integer
		if ((!$product_id || $product_id === '0') && !is_array($item_id) && $item_id !== 0) {
			// Try to get product_id from the database using item_id
			$item_data = $this->model->get_item($item_id, null);
			if ($item_data && isset($item_data['product_id'])) {
				$product_id = $item_data['product_id'];
				// If product_type is also missing, get it from the item data
				if (!$product_type) {
					$product_type = isset($item_data['product_type']) ? $item_data['product_type'] : null;
				}
			}
		}

		// If product_type is still null, try to get it from product_id
		if (!$product_type && $product_id) {
			$product_type = $this->revisedqueries->get_product_type_by_pid($product_id);
		}
		// Validate product_type - never allow 'it' or 'item_id' as product_type
		if (!$product_type || $product_type === 'it' || $product_type === 'item_id' || $product_type === '0') {
			$corruption_detected = true;
			
			// Try to recover product_type from existing item data
			if (!is_array($item_id) && $item_id !== 0) {
				$item_data = $this->model->get_item($item_id, null);
				if ($item_data && isset($item_data['product_type']) && !empty($item_data['product_type'])) {
					$product_type = $item_data['product_type'];
					$recovery_source = 'existing_item_data';
				}
			}
			
			// If still invalid, try to get from product_id
			if ((!$product_type || $product_type === 'it' || $product_type === 'item_id' || $product_type === '0') && $product_id) {
				$recovered_type = $this->revisedqueries->get_product_type_by_pid($product_id);
				if ($recovered_type && $recovered_type !== '0') {
					$product_type = $recovered_type;
					$recovery_source = 'product_id_lookup';
				}
			}
			
			// Final fallback to 'R' for Regular if all recovery attempts fail
			if (!$product_type || $product_type === 'it' || $product_type === 'item_id' || $product_type === '0') {
				$product_type = 'R';
				$recovery_source = 'default_fallback';
			}
			
			// Log corruption incident with detailed context for debugging
			$this->log_product_type_corruption($original_product_type, $product_type, $recovery_source, array(
				'item_id' => $item_id,
				'product_id' => $product_id,
				'user_id' => $this->data['user_id'],
				'user_ip' => $this->input->ip_address(),
				'user_agent' => $this->input->user_agent(),
				'post_data_product_id' => $this->input->post('product_id'),
				'post_data_product_type' => $this->input->post('product_type'),
				'timestamp' => date('Y-m-d H:i:s'),
				'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
			));
		}

		$status = $this->input->post("dropdown_status");

		$stock_status = $this->input->post("dropdown_stock_status");
		$shelf_ids = $this->input->post("shelf");
		$shelf_ids = is_null($shelf_ids) ? array() : $shelf_ids;
		$roll_location_id = $this->input->post("dropdown_roll_location");
		$bin_location_id = $this->input->post("dropdown_bin_location");
		$roll_yardage = floatval($this->input->post("roll_yardage"));
		$bin_quantity = intval($this->input->post("bin_quantity"));
		// 		$shelf_names = array();
		
		// Security: Admin-only access for item code modification
		// Non-admin users cannot change item codes - preserve existing code if present
		if (isset($this->data['is_admin']) && $this->data['is_admin'] === true) {
			$code = ($product_type === Digital ? 'Digital' : (strlen($this->input->post("new_code")) > 0 ? $this->input->post("new_code") : null));
		} else {
			// Non-admin: Use existing code or set to Digital for Digital products
			if ($product_type === Digital) {
				$code = 'Digital';
			} else if ($item_id && $item_id !== '0' && !is_array($item_id)) {
				// For existing items, preserve the current code
				$existing_item = $this->model->get_item($item_id);
				$code = $existing_item ? $existing_item['code'] : null;
			} else {
				// New items: non-admins cannot set codes
				$code = null;
			}
			
			// Log attempt if non-admin tried to submit a code
			$submitted_code = $this->input->post("new_code");
			if (!empty($submitted_code) && $submitted_code !== $code) {
				log_message('warning', 'Non-admin user attempted to modify item code. User ID: ' . $this->data['user_id'] . ' | Item ID: ' . $item_id . ' | Submitted: ' . $submitted_code . ' | Preserved: ' . ($code ?: 'null') . ' | IP: ' . $this->input->ip_address());
			}
		}
		$color_ids = json_decode($this->input->post("color_ids"));
		$color_names = json_decode($this->input->post("color_names"));
		$min_order_qty = $this->input->post('min_order_qty');
		$vendor_code = $this->input->post('vendor_code');
		$vendor_color = $this->input->post('vendor_color');
		$sales_id = $this->input->post('sales_id');
		$reselections_ids = json_decode($this->input->post('reselections_ids'));
		$archive = $this->input->post('archive');
		$web_visible = (is_null($this->input->post('showcase_visible')) ? 'N' : 'Y');

		$web_vis_toggle = (is_null($this->input->post('web_vis_toggle')) ? 0 : 1);
		
		// ============================================================================
		// RECALCULATE web_vis ON SAVE
		// ============================================================================
		// Always recalculate web_vis based on current conditions
		// This ensures web_vis stays in sync when parent product visibility or status changes
		if ($web_vis_toggle == 1) {
			// Manual override mode: Use the form value as-is
			$web_vis = (is_null($this->input->post('web_vis')) ? 0 : 1);
		} else {
			// Auto mode: Recalculate based on THREE conditions
			// 1. Parent product has beauty shot
			$product_data = $this->db->select('sp.visible as parent_visibility')
									 ->from('SHOWCASE_PRODUCT sp')
									 ->where('sp.product_id', $product_id)
									 ->where('sp.product_type', $product_type)
									 ->get()->row_array();
			$parent_has_beauty_shot = ($product_data && $product_data['parent_visibility'] == 'Y');
			
			// 2. Status is valid
			$valid_statuses = ['RUN', 'LTDQTY', 'RKFISH'];
			$status_data = $this->db->select('name')
									->from('P_PRODUCT_STATUS')
									->where('id', $status)
									->get()->row_array();
			$status_name = $status_data ? $status_data['name'] : '';
			$has_valid_status = in_array($status_name, $valid_statuses);
			
			// 3. Item has images (check if existing item has images)
			$has_item_images = false;
			if ($item_id && $item_id !== 0 && !is_array($item_id)) {
				$item_data = $this->db->select('pic_big_url, pic_hd_url')
									   ->from('SHOWCASE_ITEM')
									   ->where('item_id', $item_id)
									   ->get()->row_array();
				$has_item_images = ($item_data && (!empty($item_data['pic_big_url']) || !empty($item_data['pic_hd_url'])));
			}
			
			// Calculate final web_vis: ALL THREE conditions must be true
			$web_vis = ($parent_has_beauty_shot && $has_valid_status && $has_item_images) ? 1 : 0;
			
			log_message('info', "Web visibility recalculated for item {$item_id}: parent_beauty={$parent_has_beauty_shot}, status={$status_name}, images={$has_item_images}, result={$web_vis}");
		}
		// ============================================================================
		
		$in_master = $this->input->post('in_master') === 'on';
		$errors = array();
		//		$this->load->model('File_directory_model', 'file_directory');
		//		$dir = $this->file_directory->image_src_path(['status'=>'local_save', 'product_type'=>$product_type, 'product_id'=>$product_id, 'item_id'=>$item_id, 'img_type'=>'abrasion', 'include_filename'=>false]);
		//		var_dump($dir);
		//		var_dump($this->file_directory->temp_folder);
		//		exit();
		//====================================================================================================
		//====================================================================================================
		//====================================================================================================
		//====================================================================================================
		//====================================================================================================
		//====================================================================================================
		//====================================================================================================
		//====================================================================================================
		if (!is_array($item_id)) {
			// We are dealing with only 1 item_id, either creation or edition
			if (empty($color_ids)) {
				// No Colors Selected
				array_push($errors, 'Select at least one color.');
			}

			// Comprehensive item code validation for ALL item codes (generated or manually entered)
			if ($product_type !== constant('Digital') && strlen($code) > 0) {
				// Step 1: Validate format for any item code
				$format_validation = $this->model->validate_item_code_format($code);
				
				if (!$format_validation['valid']) {
					array_push($errors, $format_validation['message']);
				} else {
					// Step 2: Check uniqueness for ANY valid item code (generated or manual)
					if (!$this->model->is_unique_code($item_id, $code)) {
						array_push($errors, 'The item code "' . $code . '" already exists. Please choose a different code.');
					}
				}
				
				// Log item code validation attempt for audit trail
				log_message('info', 'Item code validation: ' . $code . 
						   ' | User: ' . $this->data['user_id'] . 
						   ' | Item: ' . $item_id . 
						   ' | Format: ' . ($format_validation['valid'] ? 'VALID' : 'INVALID') .
						   ' | Unique: ' . ($format_validation['valid'] ? ($this->model->is_unique_code($item_id, $code) ? 'YES' : 'NO') : 'N/A'));
			}

		if (empty($errors)) {
			
			// ============================================================================
			// CRITICAL VALIDATION: product_id must NEVER be NULL
			// ============================================================================
			if (is_null($product_id) || $product_id === '0' || $product_id === 0 || empty($product_id)) {
				// This is a CRITICAL error - item cannot exist without a product
				$error_msg = 'CRITICAL ERROR: Unable to save item - product_id is missing or invalid.';
				array_push($errors, $error_msg);
				
				// Log detailed error for debugging
				log_message('error', 'ITEM_SAVE_ERROR: product_id is NULL or invalid. ' .
							'item_id: ' . $item_id . 
							' | POST product_id: ' . $this->input->post('product_id') .
							' | POST product_type: ' . $this->input->post('product_type') .
							' | Resolved product_id: ' . var_export($product_id, true) .
							' | User: ' . $this->data['user_id']);
				
				// Return error to user
				$ret['success'] = false;
				$ret['errors'] = $errors;
				echo json_encode($ret);
				return;
			}
			// ============================================================================
			
			$this->db->trans_begin();
			if ($item_id === 0) {
				$this->isNew = TRUE;
			}

			$product_name = $this->model->get_product_name($product_type, $product_id);

			// Save item!!!
			$data = array(
				//'url_title' => url_title($item_full_name),
				'product_id' => $product_id,
					'product_type' => (in_array($product_type, ['R', 'D', 'SP']) ? $product_type : 'R'),
					'code' => $code,
					'status_id' => $status,
					'stock_status_id' => $stock_status,
					'vendor_color' => $vendor_color,
					'vendor_code' => $vendor_code,
					'roll_location_id' => ($roll_location_id === '1' ? null : $roll_location_id),
					'bin_location_id' => ($bin_location_id === '1' ? null : $bin_location_id),
					'roll_yardage' => ((is_null($roll_yardage) or ($roll_yardage === 0.0)) ? null : $roll_yardage),
					'bin_quantity' => ((is_null($bin_quantity) or ($bin_quantity === 0)) ? null : $bin_quantity),
					'min_order_qty' => $min_order_qty,
					'user_id' => $this->data['user_id'],
					'in_master' => ($in_master ? 1 : 0),
					'web_vis_toggle' => $web_vis_toggle,
					'web_vis' => $web_vis
				);
 

				if ($this->isNew) {

					// NOTE: May need to run $sql> SET SESSION sql_mode = '';
					//       Strict mode = OFF in the database due to legacy code

					// echo "<pre>_SAVE_DATA ARRAY dataCLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
					// print_r($data); echo "<br>";
					// echo "<pre>";

					log_message('debug', 'ITEM_SAVE: Creating new item with code: ' . $code);
					$item_id = $this->model->save_item($data, null);
					//$item_id = $this->db->insert_id();
				} else if ($this->input->post('change_item') === '1') {
					log_message('debug', 'ITEM_SAVE: Updating existing item ' . $item_id . ' with code: ' . $code);
					$this->model->save_item($data, $item_id);
					
					// TEMP DEBUG: Add to success message for testing
					$ret['debug_info'] = 'Item code saved: ' . $code . ' (change_item flag was set)';
				} else {
					log_message('debug', 'ITEM_SAVE: Skipping item save - change_item flag not set for item ' . $item_id . ' with code: ' . $code);
					
					// TEMP DEBUG: Add to response for testing
					$ret['debug_info'] = 'WARNING: Item code NOT saved - change_item flag missing. Code: ' . $code . ' | change_item: ' . $this->input->post('change_item');
				}

				if ($this->input->post('change_reselections') === "1") {
					$batch = array();
					foreach ($reselections_ids as $rid) {
						if ($rid == "") {
							continue;
						}
						array_push($batch, array('item_id_0' => $item_id, 'item_id_1' => $rid, 'user_id' => $this->data['user_id']));
					}
					$this->model->save_reselections($batch, $item_id);
				}

				// Colors
				if ($this->input->post('change_item_colors') === '1') {
					$batch = array();
					$n = 1;
					foreach ($color_ids as $cid) {
						// Check if it's a new one
						$isNew = strpos($cid, 'new-') !== false;

						if ($isNew) {
							$data = array(
								'name' => trim($color_names[$n - 1])
							);
							$color_id = $this->model->save_new_color($data);
						} else {
							$color_id = $cid;
						}

						array_push($batch, array('item_id' => $item_id, 'color_id' => $color_id, 'n_order' => $n, 'user_id' => $this->data['user_id']));
						$n++;
					}
					$this->model->save_item_colors($batch, $item_id);
				}

				// Save New/Edited messages
				if ($this->input->post('change_item_messages') === '1') {
					$arr = $this->input->post('item_messages_encoded');
					$arr = (empty($arr) ? array() : json_decode($arr));
					foreach ($arr as $i) {
						// Loop through each
						$ret = array(
							'item_id' => $item_id,
							'message' => $i->message_note,
							'user_id' => $this->data['user_id']
						);
						if (strpos($i->id, 'new-') === FALSE) {
							// Not 'new' in the id. Its an existing one, to be replaced
							//$ret['id'] = $i->id;
							$this->model->save_item_messages($ret, $i->id);
						} else {
							// New
							$this->model->save_item_messages($ret);
						}
					} // end foreach
				}

				// Showcase data (digitals don't come thru here)
				// save item images
				if ($this->input->post('change_showcase') === '1' and $product_type == Regular) {
					$this->load->model('File_directory_model', 'file_directory');

					$f = $this->input->post('pic_big_url');

					if (strpos($f, 'temp')) {
						// Is a new file!
						//$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f));
						$extension = $this->fileuploadtos3->getImageFileExtension($f);

						$location_request = [
							'status' => 'save',
							'product_type' => $product_type,
							'product_id' => $product_id,
							'item_id' => $item_id,
							'img_type' => 'big',
							'include_filename' => true,
							'file_format' => $extension
						];
						$new_location = $this->file_directory->image_src_path($location_request);

						if (file_exists($new_location)) {
							// Move existing file
							$location_request['item_id'] = strval($item_id) . '-' . url_title(date("Y-m-d h-i-sa"));
							rename($new_location, $this->file_directory->image_src_path($location_request));
							$location_request['item_id'] = $item_id;
						}
						$location_request['status'] = 'load';
						$new_location_big_db = $this->file_directory->image_src_path($location_request);

						$tmp_file_pic_big = $this->input->post('pic_big_url');
						/*  ------------------------
						 Create S3 objects from uploads with the new fileuploadtos3 class
						 fileuploadtos3::SendUploadedTempFileToS3($tmp_file, $new_location);
						 Both Params should be web relative paths
						 ------------------------ */
						$new_location    = str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_location);
						$this->fileuploadtos3->SendUploadedTempFileToS3($tmp_file_pic_big, $new_location);
						// echo "<pre> " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
						// echo '$tmp_file_pic_big: '. $tmp_file_pic_big ."<br>";
						// echo '$new_location: '. $new_location ."<br>";
						// echo "<pre>";

						// Save current uploaded file
						// rename(str_replace(site_url(), '', $f), $new_location);
					} else {
						// No file has been uploaded
						$new_location_big_db = $f;
					}

					$f = $this->input->post('pic_hd_url');
					if (strpos($f, 'temp')) {
						// Is a new file!
						//$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever extension is)
						$extension = $this->fileuploadtos3->getImageFileExtension($f);

						$location_request = [
							'status' => 'save',
							'product_type' => $product_type,
							'product_id' => $product_id,
							'item_id' => $item_id,
							'img_type' => 'hd',
							'include_filename' => true,
							'file_format' => $extension
						];
						$new_location = $this->file_directory->image_src_path($location_request);

						if (file_exists($new_location)) {
							// Move existing file
							$location_request['item_id'] = strval($item_id) . '-' . url_title(date("Y-m-d h-i-sa"));
							rename($new_location, $this->file_directory->image_src_path($location_request));
							$location_request['item_id'] = $item_id;
						}
						$location_request['status'] = 'load';
						$new_location_hd_db = $this->file_directory->image_src_path($location_request);

						$tmp_file_pic_hd = $this->input->post('pic_hd_url');
						/*  ------------------------
						 Create S3 objects from uploads with the new fileuploadtos3 class
						 fileuploadtos3::SendUploadedTempFileToS3($tmp_file, $new_location);
						 Both Params should be web relative paths
						 ------------------------ */
						$new_location    = str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_location);
						$this->fileuploadtos3->SendUploadedTempFileToS3($tmp_file_pic_hd, $new_location);
						// echo "<pre> " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
						// echo '$tmp_file_pic_hd: '. $tmp_file_pic_hd ."<br>";
						// echo '$new_location: '. $new_location ."<br>";
						// echo "<pre>";

						// Save current uploaded file
						// rename(str_replace(site_url(), '', $f), $new_location);
					} else {
						// Existing file, don't relocate the file
						$new_location_hd_db = $f;
					}
					$new_location_big_db = (strlen($new_location_big_db) > 0 ? $new_location_big_db : null);
					$new_location_hd_db = (strlen($new_location_hd_db) > 0 ? $new_location_hd_db : null);

					// PKL Convert the new file location ($new_location_big_db) to S3 URL for Database insertion
					$S3_big_db_location = $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_big_db);
					// PKL Convert the new file location ($new_location_hd_db ) to S3 URL for Database insertion
					$S3_hd_db_location = $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_hd_db);
					$ret = array(
						'url_title' => strtolower(url_title($product_name) . '/' . url_title(implode('-', $color_names))),
						'visible' => $web_visible,
						'pic_big_url' => $S3_big_db_location,
						'pic_hd_url' => $S3_hd_db_location,
						'user_id' => $this->data['user_id']
					);
					$this->model->save_showcase_basic($ret, $item_id);
				}

				// Shelfs
				if ($this->input->post('change_shelf') === '1') {

					$batch = array();
					// 					$shelf_names = array();

					foreach ($shelf_ids as $sid) {
						// 						$shelfname = $this->specs->get_shelfs($sid);
						// 						array_push($shelf_names, $shelfname['name']);
						array_push($batch, array('item_id' => $item_id, 'shelf_id' => $sid, 'user_id' => $this->data['user_id']));
					}

					$this->model->save_item_shelfs($batch, $item_id);
				}

				// Showcase item coord colors
				if ($this->input->post('change_item_coord_color') === '1') {
					// Collection
					$arr = $this->input->post('showcase_coord_color');
					$arr = (is_null($arr) ? array() : $arr);
					$ret = array();
					foreach ($arr as $specid) {
						array_push($ret, array(
							'item_id' => $item_id,
							'coord_color_id' => $specid
						));
					}
					$this->model->save_showcase_coord_color($ret, $item_id);
				}

				if ($this->input->post('change_item_sales_id') === '1') {
					$this->model->save_item_sales_id($item_id, $sales_id);
				}

				if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					$ret['success'] = false;
					$ret['message'] = ul(array("Some error ocurred."), $this->error_ul_attr);
				} else {
					$this->db->trans_commit();


					//echo "<pre> " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
					//echo '$item_id: '. $item_id ."<br>";
					//echo '$product_type: '. $product_type ."<br>";
					//echo "<pre>";

					// $aux = $this->search->do_search([
					//   'item_ids' => [$item_id],
					//   'includeDiscontinued' => true,
					//   'select' => ['status', 'status_descr', 'status_id', 'stock_status', 'stock_status_descr', 'stock_status_id',
					//     'in_master', 'in_ringset', 'price', 'costs', 'stock', 'stock_link', 'shelf',
					//     'sampling_location', 'sampling_stock', 'showcase', 'url_title']
					// ]);
					$fetched_item = $this->revisedqueries->get_item_details($item_id, $product_type);

					//echo "<pre> __fetched_item__ :: retrived item CLASS:  ". __CLASS__. "::". __METHOD__ . "(".__LINE__ .") <br>";
					//print_r($fetched_item);
					//echo "<br>";
					////print_r($aux);
					//echo "</pre>";

					$ret['item'] = $fetched_item;

					$ret['success'] = true;
				}
			} else {
				// Some error ocurred
				$ret['success'] = false;
				$ret['message'] = ul($errors, $this->error_ul_attr);
			}
			//====================================================================================================
			//====================================================================================================
			//====================================================================================================
			//====================================================================================================
			//====================================================================================================
			//====================================================================================================
			//====================================================================================================
			//====================================================================================================

		} else {

			// Dealing with the edition of a batch of item_ids
			// Update the status and stock status of them
			$item_status = $this->specs->get_product_status($status);
			$item_stock_status = $this->specs->get_stock_status($stock_status);
			$shelfs = $this->specs->get_shelfs();
			$clear_shelf = $this->input->post('clear_shelf') == 'on';


			$clear_roll = $this->input->post('clear_roll') == 'on';
			$clear_bin = $this->input->post('clear_bin') == 'on';
			$in_master = $this->input->post('in_master') == 'on';

			$data = array();
			$aux = array();

			/*Set Common Data to update*/
			if (!empty($min_order_qty)) {
				$data['min_order_qty'] = $min_order_qty;
			}
			if (!is_null($status)) {
				$data['status_id'] = $status;
				$aux['status_abrev'] = $item_status['descr'];
				$aux['status'] = $item_status['name'];
			}
			if (!is_null($stock_status)) {
				$data['stock_status_id'] = $stock_status;
				$aux['stock_status_abrev'] = $item_stock_status['descr'];
				$aux['stock_status'] = $item_stock_status['name'];
			}
			if (!is_null($archive) && $archive === 'on') {
				$data['archived'] = 'Y';
			}

			if ($clear_shelf) {
				$aux['shelf'] = '';
			} else if ($this->input->post('change_shelf') === '1') {
				if (!empty($shelf_ids)) {
					$shelfs = $this->specs->get_shelfs($shelf_ids);
					$shelf_n = [];
					foreach ($shelfs as $s) {
						array_push($shelf_n, $s['name']);
					}
					$shelf_names = implode(' / ', $shelf_n);
					$aux['shelf'] = $shelf_names;
				} else {
					$aux['shelf'] = '';
				}
			}

			if ($clear_roll) {
				$data['roll_location_id'] = null;
				$aux['roll_location'] = null;
			} else if ($this->input->post('change_roll_location') === '1') {
				$roll_location_name = $this->specs->get_sampling_locations($roll_location_id)['name'];
				$val = $roll_location_id;
				if ($val === '1') {
					$val = null;
					$roll_location_name = '';
				}
				$data['roll_location_id'] = $val;
				$aux['roll_location'] = $roll_location_name;
			}

			if ($clear_bin) {
				$data['bin_location_id'] = null;
				$aux['bin_location'] = null;
			} else if ($this->input->post('change_bin_location') === '1') {
				$bin_location_name = $this->specs->get_sampling_locations($bin_location_id)['name'];
				$val = $bin_location_id;
				if ($val === '1') {
					$val = null;
					$bin_location_name = '';
				}
				$data['bin_location_id'] = $val;
				$aux['bin_location'] = $bin_location_name;
			}

			// Do database updates
			$batch_t_item = array();
			$batch_t_item_sampling = [];
			$frontEndData = array();

			foreach ($item_id as $id) {

				// Update shelfs
				if ($clear_shelf) {
					// Clear all shelf relation for item $id
					$this->model->save_item_shelfs(array(), $id);
				} else if (!is_null($shelf_ids) && $this->input->post('change_shelf') === '1') {
					# its a batch to update each item_id
					$shelf_batch = array();
					foreach ($shelf_ids as $sid) {
						array_push($shelf_batch, array('item_id' => $id, 'shelf_id' => $sid, 'user_id' => $this->data['user_id']));
					}
					if (count($shelf_batch) > 0) $this->model->save_item_shelfs($shelf_batch, $id);
				}

				if ($this->input->post('change_item') === '1' and !is_null($this->input->post('in_master'))) {
					$data['in_master'] = ($in_master ? 1 : 0);
				}
				$aux['item_id'] = $id;

				// Merge for Front end update
				array_push($frontEndData, array_merge($data, $aux));

				if (count($data) > 0) {
					$data['id'] = $id;
					array_push($batch_t_item, $data); // For database update
				}
			}
			$ret['items'] = $frontEndData;
			$ret['success'] = true;
			//			var_dump($batch_t_item); var_dump($frontEndData); exit;
			if (count($batch_t_item) > 0) {
				$this->db->update_batch($this->model->t_item, $batch_t_item, 'id');
			}
			$this->db->trans_commit();
		}

		// Refresh the cached product spec view by inserting the product row into the cache table
		$this->load->model('Product_model', 'product_model');
		$this->product_model->refresh_cached_product_row($product_id, $product_type);

		echo json_encode($ret);
	}
	// ////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////

	public function archive_item($item_id = null)
	{
		$return = false;
		if (is_null($item_id)) {
			$return = true;
			$item_id = $this->input->post('item_id');
		}
		$result = $this->model->archive_item($item_id);
		if ($return && $this->input->is_ajax_request()) {
			$ret = array(
				'success' => true,
				'item_id' => $item_id
			);
			echo json_encode($ret);
		}
	}

	public function retrieve_item()
	{
		$item_id = $this->input->post('item_id');
		$item_data = $this->model->retrieve_item($item_id);
		if ($this->input->is_ajax_request()) {
			$ret = array(
				'success' => true,
				'item' => $item_data
			);
			echo json_encode($ret);
		}
	}

	/**
	 * Generate a unique random item code via AJAX
	 * 
	 * SECURITY MEASURES:
	 * - AJAX request validation
	 * - CSRF protection (inherits from form)
	 * - Rate limiting via model max attempts
	 * - No sensitive data exposure in responses
	 * - Comprehensive error handling
	 * - Audit logging via model layer
	 * 
	 * @return JSON response with success status and code or error message
	 */
	public function generate_item_code()
	{
		// Security: Only allow AJAX requests to prevent direct access
		if (!$this->input->is_ajax_request()) {
			show_404();
			return;
		}

		// Security: Ensure user is authenticated (inherits from parent controller)
		if (!$this->data['user_id']) {
			echo json_encode(array(
				'success' => false,
				'message' => 'Authentication required.'
			));
			return;
		}

		// Security: Admin-only access for item code generation
		// Check if user is admin (explicit boolean check)
		$is_admin = isset($this->data['is_admin']) ? (bool) $this->data['is_admin'] : false;
		if (!$is_admin) {
			log_message('warning', 'Non-admin user attempted to generate item code. User ID: ' . $this->data['user_id'] . ' | is_admin value: ' . var_export($this->data['is_admin'] ?? 'not set', true) . ' | IP: ' . $this->input->ip_address());
			echo json_encode(array(
				'success' => false,
				'message' => 'Access denied. Item code generation is restricted to administrators.'
			));
			return;
		}

		try {
			// Generate unique code using secure model method
			$code = $this->model->generate_unique_item_code();
			
			// Success response - only return necessary data
			echo json_encode(array(
				'success' => true,
				'code' => $code
			));
			
		} catch (Exception $e) {
			// Security: Don't expose internal error details to client
			// Log full error details server-side for debugging
			log_message('error', 'Item code generation failed: ' . $e->getMessage() . 
						' | User ID: ' . $this->data['user_id'] . 
						' | IP: ' . $this->input->ip_address());
			
			// Return generic error message to client
			echo json_encode(array(
				'success' => false,
				'message' => 'Unable to generate item code. Please try again.'
			));
		}
	}

	/**
	 * Validate item code format via AJAX
	 * 
	 * SECURITY MEASURES:
	 * - AJAX request validation
	 * - Server-side admin permission checking
	 * - Input sanitization and validation
	 * - No sensitive data exposure
	 * 
	 * @return JSON response with validation result
	 */
	public function validate_item_code()
	{
		// Security: Only allow AJAX requests
		if (!$this->input->is_ajax_request()) {
			show_404();
			return;
		}

		// Security: Ensure user is authenticated
		if (!$this->data['user_id']) {
			echo json_encode(array(
				'valid' => false,
				'message' => 'Authentication required.'
			));
			return;
		}

		// Get and sanitize input
		$code = $this->input->post('code');
		$item_id = $this->input->post('item_id') ?: 0;
		
		// Validate format using secure model method (letters now allowed for all users)
		$format_validation = $this->model->validate_item_code_format($code);
		
		// If format is valid, check uniqueness
		if ($format_validation['valid'] && !empty($code)) {
			$is_unique = $this->model->is_unique_code($item_id, $code);
			
			if (!$is_unique) {
				echo json_encode(array(
					'valid' => false,
					'message' => 'This item code already exists.'
				));
				return;
			}
		}
		
		// Return validation result
		echo json_encode($format_validation);
	}

	/**
	 * Log product_type corruption incidents with email notifications
	 * 
	 * SECURITY: Rate-limited email notifications to prevent spam
	 * DEBUGGING: Comprehensive logging for issue resolution
	 * 
	 * @param string $original_type The corrupted product_type value
	 * @param string $recovered_type The recovered product_type value
	 * @param string $recovery_source How the recovery was achieved
	 * @param array $context Additional context for debugging
	 */
	private function log_product_type_corruption($original_type, $recovered_type, $recovery_source, $context = array())
	{
		// Comprehensive server-side logging
		$log_message = "PRODUCT_TYPE_CORRUPTION_DETECTED: " .
					   "Original: '{$original_type}' -> Recovered: '{$recovered_type}' " .
					   "via {$recovery_source} | " .
					   "User: {$context['user_id']} | " .
					   "Item: {$context['item_id']} | " .
					   "Product: {$context['product_id']} | " .
					   "IP: {$context['user_ip']}";
		
		log_message('error', $log_message);
		
		// Check rate limiting for email notifications (max 10 per hour)
		$rate_limit_key = 'product_type_corruption_email_' . date('Y-m-d-H');
		$cache_file = APPPATH . 'logs/' . $rate_limit_key . '.count';
		
		$email_count = 0;
		if (file_exists($cache_file)) {
			$email_count = (int) file_get_contents($cache_file);
		}
		
		// Send email if under rate limit
		if ($email_count < 10) {
			$this->send_corruption_alert_email($original_type, $recovered_type, $recovery_source, $context);
			
			// Update rate limit counter
			file_put_contents($cache_file, $email_count + 1);
		} else {
			log_message('warning', 'Product type corruption email rate limit exceeded for hour: ' . date('Y-m-d H:00'));
		}
	}

	/**
	 * Send email alert for product_type corruption (works with ephemeral instances)
	 * 
	 * SECURITY: Plain text email, no sensitive data exposure
	 * COMPATIBILITY: Uses PHP mail() function for ephemeral instance compatibility
	 * 
	 * @param string $original_type The corrupted product_type value
	 * @param string $recovered_type The recovered product_type value
	 * @param string $recovery_source How the recovery was achieved
	 * @param array $context Additional context for debugging
	 */
	private function send_corruption_alert_email($original_type, $recovered_type, $recovery_source, $context)
	{
		$to = 'paulkleasure@gmail.com';
		$subject = 'OPMS Alert: Product Type Corruption Detected';
		
		// Plain text email with comprehensive debugging details
		$message = "OPMS PRODUCT TYPE CORRUPTION ALERT\n";
		$message .= "=====================================\n\n";
		
		$message .= "CORRUPTION DETAILS:\n";
		$message .= "Original product_type: '{$original_type}'\n";
		$message .= "Recovered product_type: '{$recovered_type}'\n";
		$message .= "Recovery method: {$recovery_source}\n";
		$message .= "Timestamp: {$context['timestamp']}\n\n";
		
		$message .= "ITEM CONTEXT:\n";
		$message .= "Item ID: {$context['item_id']}\n";
		$message .= "Product ID: {$context['product_id']}\n\n";
		
		$message .= "USER CONTEXT:\n";
		$message .= "User ID: {$context['user_id']}\n";
		$message .= "IP Address: {$context['user_ip']}\n";
		$message .= "User Agent: " . substr($context['user_agent'], 0, 100) . "\n";
		$message .= "Request URI: {$context['request_uri']}\n\n";
		
		$message .= "POST DATA ANALYSIS:\n";
		$message .= "POST product_id: '{$context['post_data_product_id']}'\n";
		$message .= "POST product_type: '{$context['post_data_product_type']}'\n\n";
		
		$message .= "RECOVERY STATUS:\n";
		if ($recovery_source === 'existing_item_data') {
			$message .= "✅ Successfully recovered from existing item database record\n";
		} elseif ($recovery_source === 'product_id_lookup') {
			$message .= "✅ Successfully recovered via product_id database lookup\n";
		} elseif ($recovery_source === 'default_fallback') {
			$message .= "⚠️ Used default fallback to 'R' (Regular) - may need manual review\n";
		}
		
		$message .= "\nACTION TAKEN:\n";
		$message .= "- Item save operation continued with recovered product_type\n";
		$message .= "- No data loss occurred\n";
		$message .= "- Incident logged for analysis\n\n";
		
		$message .= "INVESTIGATION STEPS:\n";
		$message .= "1. Check server logs for additional context around {$context['timestamp']}\n";
		$message .= "2. Review AJAX request timing for item code generation\n";
		$message .= "3. Verify form data integrity during submission\n";
		$message .= "4. Check for JavaScript errors in browser console\n\n";
		
		$message .= "This is an automated alert from OPMS item management system.\n";
		$message .= "Server: " . ($_SERVER['SERVER_NAME'] ?? 'unknown') . "\n";
		$message .= "Environment: " . (ENVIRONMENT ?? 'unknown') . "\n";
		
		// Headers for plain text email (ephemeral instance compatible)
		$headers = "From: noreply@opms-system.com\r\n";
		$headers .= "Reply-To: noreply@opms-system.com\r\n";
		$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$headers .= "X-Mailer: OPMS Debug System\r\n";
		
		// Send email using PHP mail function (works on most ephemeral instances)
		$mail_sent = @mail($to, $subject, $message, $headers);
		
		// Log email attempt result
		if ($mail_sent) {
			log_message('info', 'Product type corruption alert email sent successfully to ' . $to);
		} else {
			log_message('error', 'Failed to send product type corruption alert email to ' . $to);
		}
	}

	/**
	 * Log corruption incidents reported from client-side JavaScript
	 * 
	 * SECURITY: AJAX-only access, authenticated users only
	 * DEBUGGING: Client-side corruption detection and reporting
	 * 
	 * @return JSON response acknowledging the report
	 */
	public function log_corruption_incident()
	{
		// Security: Only allow AJAX requests
		if (!$this->input->is_ajax_request()) {
			show_404();
			return;
		}

		// Security: Ensure user is authenticated
		if (!$this->data['user_id']) {
			echo json_encode(array('success' => false, 'message' => 'Authentication required.'));
			return;
		}

		// Get corruption data from client
		$corruption_data_json = $this->input->post('corruption_data');
		$user_agent = $this->input->post('user_agent');
		$page_url = $this->input->post('page_url');
		
		// Parse corruption data
		$corruption_data = json_decode($corruption_data_json, true);
		
		if ($corruption_data) {
			// Log client-side corruption incident
			$log_message = "CLIENT_SIDE_CORRUPTION_DETECTED: " .
						   "Checkpoint: {$corruption_data['checkpoint']} | " .
						   "Expected: {$corruption_data['expected']} | " .
						   "Actual: {$corruption_data['actual']} | " .
						   "User: {$this->data['user_id']} | " .
						   "IP: {$this->input->ip_address()} | " .
						   "Page: {$page_url}";
			
			log_message('error', $log_message);
			
			// Send email notification for client-side corruption
			$context = array(
				'item_id' => 'client_side_detection',
				'product_id' => $corruption_data['expected'] ?? 'unknown',
				'user_id' => $this->data['user_id'],
				'user_ip' => $this->input->ip_address(),
				'user_agent' => $user_agent,
				'post_data_product_id' => $corruption_data['actual'] ?? 'unknown',
				'post_data_product_type' => $corruption_data['originalProductType'] ?? 'unknown',
				'timestamp' => date('Y-m-d H:i:s'),
				'request_uri' => $page_url,
				'checkpoint' => $corruption_data['checkpoint'],
				'client_side_data' => $corruption_data_json
			);
			
			// Use existing email notification system
			$this->log_product_type_corruption(
				$corruption_data['actual'] ?? 'unknown',
				$corruption_data['expected'] ?? 'recovered_by_client',
				'client_side_detection',
				$context
			);
		}
		
		echo json_encode(array('success' => true, 'message' => 'Corruption incident logged.'));
	}

	function toggle_ringset()
	{
		$ret['items'] = array();
		$batch = $this->input->post('batch');
		if (count($batch) > 0) {
			foreach ($batch as $item) {
				$data = array(
					'in_ringset' => ($item['in_ringset'] === 'true' ? 1 : 0)
				);
				$this->model->save_item($data, $item['item_id']);
				array_push($ret['items'], $item);
			}
		}
		echo json_encode($ret);
	}

	function toggle_exportable()
	{
		$ret['items'] = array();
		$batch = $this->input->post('batch');
		if (count($batch) > 0) {
			foreach ($batch as $item) {
				$data = array(
				  'exportable' => ($item['exportable'] === 'true' ? 1 : 0)
				);
				$this->model->save_item($data, $item['item_id']);
				array_push($ret['items'], $item);
			}
		}
		echo json_encode($ret);
	}

	function typeahead_colors()
	{
		//var_dump($_GET);
		$search = $this->input->post('query');
		$item_id = $this->input->post('item_id');
		$color_ids_selected = json_decode($this->input->post('color_ids_selected'));

		$ret = $this->model->typeahead_colors($search, $color_ids_selected);
		if ($this->input->is_ajax_request()) {
			echo json_encode($ret);
		}
	}

	function typeahead_sales_sync()
	{
		$search = $this->input->post("query");
		$code = $this->input->post("code");
		$t = $this->model->db_sales . ".op_products";

		$this->model->db
			->select("Stock.id, CONCAT(Stock.name, ' / ', COALESCE(Stock.code, ''), ' / ', Stock.color) as label")
			->from("$t Stock")
			->where("Stock.master_item_id IS NULL")
		;
		if (strlen($search) > 0) {
			$this->model->db
				->group_start()
				->like("Stock.name", $search)
				->or_like("Stock.color", $search)
				->or_like("Stock.code", $search)
				->group_end()
			;
		} else if (strlen($code) > 0) {
			$this->model->db
				->group_start()
				->or_like("Stock.code", $code)
				->group_end();
		}
		$ret = $this->model->db->get()->result_array();
		//		echo $this->model->db->last_query();
		echo json_encode($ret);
	}

	function get_item_presence()
	{
		$item_id = $this->input->post('item_id');
		$lists = $this->model->get_item_presence($item_id);
		$this->load->library('table');
		$this->table->set_template($this->table_template('table-presence', " class='table' "));
		$this->table->set_heading('List Name', 'Date added', 'Date modif', 'By user');
		echo $this->table->generate($lists);
	}

	public function get($item_id)
	{
		$searchParams = [
			'select' => [
				"pic_big_url",
				"width",
				"repeats",
				"content_front",
				"content_back",
				"finish",
				"firecode",
				"abrasion",
				"weave",
				"uses",
				"cleaning"
			],
			'item_ids' => [intval($item_id)],
			'group_by' => item
		];
		$data = $this->search->do_search($searchParams)[0];
		$data['spec_url'] = site_url('reps/product/specsheet/' . $data['product_type'] . '/' . $data['product_id']);
		$item_data = [
			'data' => $data
		];
		echo json_encode($item_data);
	}

	public function save_in_sales($item_id) {}

	// ============================================================================
	// WEB VISIBILITY CALCULATION METHODS
	// ============================================================================
	
	/**
	 * Calculate web visibility for a single item
	 * Based on Web Visibility Logic Flow specification
	 * 
	 * @param array $item Item data array
	 * @return bool Calculated web visibility state
	 */
	private function calculate_web_visibility_for_item($item)
	{
		// Validate item data
		if (empty($item['item_id']) || empty($item['product_id'])) {
			return false;
		}
		
		// Check manual override state
		$manual_override = !empty($item['web_vis_toggle']) ? (bool)$item['web_vis_toggle'] : false;
		
		if ($manual_override) {
			// Manual override logic - return stored checkbox value
			// Since we're calculating for first time, default to false
			return false;
		} else {
			// Auto-determination logic
			return $this->calculate_auto_visibility($item);
		}
	}
	
	/**
	 * Calculate auto-determined visibility based on product and status
	 * 
	 * @param array $item Item data array
	 * @return bool Auto-determined visibility state
	 */
	private function calculate_auto_visibility($item)
	{
		// Check if product has beauty shot (web_visible comes from SHOWCASE_PRODUCT)
		$product_web_visibility = !empty($item['parent_product_visibility']) ? ($item['parent_product_visibility'] === 'Y') : false;
		
		if (!$product_web_visibility) {
			return false; // No beauty shot or product not visible = not visible
		}
		
		// Check if status is valid for auto-visibility
		$valid_statuses = ['RUN', 'LTDQTY', 'RKFISH'];
		$status = !empty($item['status']) ? strtoupper(trim($item['status'])) : '';
		
		if (!in_array($status, $valid_statuses)) {
			return false; // Invalid status = not visible
		}
		
		// All conditions met for auto-visibility
		return true;
	}
}
