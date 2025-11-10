<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH.'/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Lists extends REST_Controller {
  
	private $_default_filters = [
		'verify_rows_data' => true,
		//     'list' => [ 'id'=>int, 'active'=>true ],
		'select' => [ 'status', 'stock_status', 'width', 'content_front', 'price', 'outdoor' ],
		'group_by' => product,
		'order_by' => null,
		'isPrinting' => true,
		'debug' => false
	];
  
	function __construct(){
		parent::__construct();
		$this->load->model('Lists_model', 'model');
		$this->load->model('Search_model', 'search');
	}
  
	private function _retrieve_item_list(){
		$this->filters = array_merge( $this->_default_filters, $this->filters );
// 		$this->response($this->filters); exit;

		if( array_key_exists('list', $this->filters) ){
			$this->data['list_id'] = $this->filters['list']['id'];
			$this->data['info'] = $this->model->get_list_edit($this->data['list_id']);
		}
		else {
			$this->data['info'] = [];
		}

		$aux_items = $this->search->do_search($this->filters);
		//     var_dump($aux_items); exit;
		if( $this->filters['debug'] ){
			$this->response($aux_items); exit;
		}

		$this->data['items'] = array();
		$this->data['digital_items'] = array();
		//     $this->data['items_missing_data'] = array();
		// 			Get ALL the $30 and Under items
		$this->under30_ids = $this->model->get_30_under_product_ids();

		// Separate items and format some data
		if( count($aux_items) > 0 ){
		foreach($aux_items as $i){
			if( $this->filters['group_by'] === item && $this->filters['order_by'] === 'n_order' && $i['big_piece'] === '0' ) { continue; }

			// Clean the TBD status
			if ( isset($i['stock_status']) && strpos($i['stock_status'], 'TBD') !== false ) {
				$i['stock_status'] = trim(str_replace('TBD', '', $i['stock_status']));
				$i['stock_status'] = trim(str_replace('-', '', $i['stock_status']));
			}

			if( $this->filters['group_by'] === item ){
				// Show the 'Limited Stock' note
				if( isset($i['status_id']) && in_array($i['status_id'], $this->model->product_status_discontinued) ){
					$i['color'] .= "  <span class='limited-stock-row'>(Limited stock)</span>";
				}
			}

			// Start conditionals if an item should be shown
			if( $this->_row_valid_to_show($i) ){

				if( in_array('30_under', $this->filters['select']) ){
					$this->under30Icon = '<span style="color:#bfac02;">$30</span>';
					$i['30_under'] = false;
					if( in_array( $i['product_id'], $this->under30_ids ) ){
						$i['30_under'] = true;
						$i['30_under_icon'] = $this->under30Icon;
					}
				}

				//           if( in_array('url_title', $this->filters['select']) ){
				//             $i['url_title'] = "https://www.opuzen.com/product/" . $i['url_title'];
				//           }

				if( $i['product_type'] === Regular ){
					array_push($this->data['items'], $i);
				} 
				else if( $i['product_type'] === Digital ) {
					array_push($this->data['digital_items'], $i);
				}
			}
			else {
			// Here are the items that are missing some info !
			//           array_push($this->data['items_missing_data'], $i);
			}
		}
		}

		// Prepare results
		if( $this->get('format') === 'html' ){
		$this->data = array_merge($this->data['items'], $this->data['digital_items']);
		}
		else {
		$this->data = [
		'info' => $this->data['info'],
		'size' => count($this->data['items']) + count($this->data['digital_items']),
		'items' => array_merge($this->data['items'], $this->data['digital_items'])
		];
		}
	}
  
  private function _row_valid_to_show($i){
    if( !$this->filters['verify_rows_data'] ){ return true; }
    return  
            (
              $this->filters['list']['id'] !== 1 &&
              ( !in_array('stock_status', $this->filters['select']) || strlen($i['stock_status'] ) > 0 ) &&
              ( !in_array('price', $this->filters['select']) || !($i['p_res_cut'] === '-' && $i['p_hosp_roll'] === '-') ) &&
//              ( !in_array('price', $this->filters['select']) || !($i['p_res_cut'] === '-' && $i['p_hosp_cut'] === '-' && $i['p_hosp_roll'] === '-') ) &&
              (
//                 ( is_bool($this->filters['select']) && !$this->filters['select'] )
//                 ||
//                 ( is_array($this->filters['select']) && !in_array('content_front', $this->filters['select']) )
//                 ||
//                 isset($i['content_front']) && strlen( trim($i['content_front']) ) > 0 
                !in_array('content_front', $this->filters['select']) || strlen( trim($i['content_front']) ) > 0 
              ) &&
              (
//                 ( is_bool($this->filters['select']) && !$this->filters['select'] )
//                 ||
//                 ( is_array($this->filters['select']) && in_array('width', $this->filters['select']) )
//                 ||
//                 ( isset($i['width']) && $i['width'] !== '0.00"' )
                !in_array('width', $this->filters['select']) || $i['width'] !== '0.00"'
              )
            )
            ||
            (
              $this->filters['list']['id'] === 1 &&
              ( !in_array('price', $this->filters['select']) || !($i['p_dig_res'] === '-' && $i['p_dig_hosp'] === '-') ) &&
              ( !in_array('stock_status', $this->filters['select']) || strlen($i['stock_status']) > 0 )
            );
  }
  
  private function _set_list_id(){
    switch( $this->_detect_method() ){
      case 'get':
        $list_id = $this->get('id');
        break;
      case 'post':
        $list_id = $this->post('id');
        break;
      default:
        $this->_invalid_req();
        break;
    }
    if( is_null($list_id) ){
      $this->_invalid_req();
    }
    
    if( is_array($list_id) ){
      foreach($list_id as $id){
        $this->filters['list']['id'][] = intval($id);
      }
    }
    else if( is_numeric($list_id) ) {
      $this->filters['list'] = [ 'id' => intval($list_id) ];
    }
    else {
      $this->_invalid_req();
    }
    
  }
  
  private function _debug(){
    $this->_default_filters['debug'] = true;
  }
  
  private function _invalid_req(){
    $this->response(NULL, 400); exit;
  }
  
  //
  
	public function index_get(){
		//     $this->response( $this->get() ); exit;
		//     $this->_debug();
		// Initialize inputs
		$this->_set_list_id();

		if( $this->get('group_by') ){
			switch( $this->get('group_by') ){
				case item:
				case 'item':
					$this->filters['group_by'] = item;
					break;
				case product:
				case 'product':
				default:
					$this->filters['group_by'] = product;
					break;
			}
		}

		if( $this->get('order_by') ){
			$this->filters['order_by'] = $this->get('order_by');
		}

		$this->_retrieve_item_list();
		$this->response( $this->data );
	}
  
	public function index_post(){
		//     $this->response( $this->post() ); exit;

		// Initialize inputs
		$this->_set_list_id();

		if( $this->post('group_by') ){
			switch( $this->post('group_by') ){
				case item:
				case 'item':
					$this->filters['group_by'] = item;
					break;
				case product:
				case 'product':
				default:
					$this->filters['group_by'] = product;
					break;
			}
		}

		if( $this->post('order_by') ){
			$this->filters['order_by'] = $this->post('order_by');
		}

		if( $this->post('select') && is_array($this->post('select')) ){
			$this->filters['select'] = $this->post('select');
		}

		$this->_retrieve_item_list();
		$this->response( $this->data );
	}
	
	public function materialbank_get(){
		/*
			Material Bank Faux Furs & Outdoor	list_id 398
			Hollywood Squares					product_id 3405
		*/
		$this->filters = [
			'verify_rows_data' => false,
// 			'debug' => true,
 			'list' => [ 'id' => [507], 'active' => true ],
// 			'product_ids' => [ Regular => [16266, 4351, 10080, 1808] ],
//			'item_ids' => [16266, 4351, 10080, 1808],
			'restrictType' => [Regular],
			'group_by' => item,
			'order_by' => 'product_id',
			'isPrinting' => true, // if false, will include MSO, DISCO, NOTRUN
			'select' => [
				'width', 'repeats', 'content_front', 'outdoor', 'origin', 'description', 'finish',
				'firecode', 'abrasion', 'yards_per_roll', 'coord_colors', 'pattern', 'weave', 'price',
				'uses', 'url_title', 'cleaning', 'railroaded', 'pic_hd_url', 'beauty_shot_url', 'lead_time'
			],
			'add_columns' => [
				'format'=>'Roll',
				'collection_name'=>'',
				'category'=>'Textile',
// 				'price_range'=>'$',
				'min_units'=>'3 yards',
				'ship_internationally'=>'Yes',
				'availability'=>'Typically Stocked',
				'warranty'=>'1-3 years depending on usage'
			]
		];
		$this->_retrieve_item_list();
		$return = [];

		/*
			Tweaks to Filter items from the previously sent JSON
		*/
// 		$string = file_get_contents(asset_url() . 'MB_04-24-2019.json');
// 		$json_a = json_decode($string, true);
// 		$item_ids_missing_hd = [];
// // 		var_dump($json_a); exit;
// 		foreach($json_a as $i){
// 			if( is_null($i['pic_hd_url']) ){
// 				array_push($item_ids_missing_hd, $i['item_id']);
// 			}
// 		}
// // 		var_dump($item_ids_missing_hd); exit;
		/*
			END of tweaks
		*/

		// Some special attributes to add
		foreach($this->data['items'] as &$i){
// 			if( !in_array($i['item_id'], $item_ids_missing_hd) ) { continue; } // Tweaks to Filter items from the previously sent JSON
			$i['category'] = $i['category'] . ', ' . $i['uses'];
			$i['specsheet_url'] = "https://www.opuzen.com/product/specsheet/" . $i['url_title'];
			$i['url_title'] = "https://www.opuzen.com/product/" . $i['url_title'];
			
			if( strpos($i['lead_time'], 'for production') !== false ){
				# string found
				# leave it as it is
			} else if( strlen($i['lead_time']) > 0 ){
				$i['lead_time'] = "If not in stock, ". strtolower($i['lead_time']) ." for production";
			} else {
				$i['lead_time'] = '';
			}
			
			$i['outdoor'] = ( $i['outdoor'] === 'N' ? 'Indoor Only' : 'Indoor/Outdoor' );
			$i['railroaded'] = ( $i['railroaded'] === 'N' ? 'No' : 'Yes' );
			$i['res_comm_use'] = "Residential & Commercial Only";
			
			$i['price_range'] = $this->evaluate_material_bank_price($i);
			
			$i['weave'] = trim(str_replace('Vinyl / Faux Leather,', '', $i['weave']));
			$i['weave'] = trim(str_replace(', Plush', '', $i['weave']));
			$return[] = $i;
		}
		/*
			Tweak
		*/
		$this->data['items'] = $return;
		$this->data['size'] = count($return);

		if( $this->get('format') === 'read' ){
			$this->format_for_attributes_review();
		}
		$this->response( $this->data['items'] );
		$this->response( $this->data );
	}
	
	function evaluate_material_bank_price($item){
		$eval = (float) $item['p_hosp_roll'];
		
		$one = [0, 25];
		$two = [25, 60];
		$three = [60, 95];
		$four = [95, 125];
		$five = [125, 1000];
		
		if($eval <= $one[1]){
			return '$';
		}
		else if ($eval <= $two[1]){
			return '$$';
		}
		else if ($eval <= $three[1]){
			return '$$$';
		}
		else if ($eval <= $four[1]){
			return '$$$$';
		}
		else{
			return '$$$$$';
		}
	}
  
	function format_for_attributes_review($misses_only=false){
		$page_break = "<div style='page-break-after:always;'></div>";
		//     var_dump($this->data['items']); exit;
		$product_ids_looped = [];
		$colors = [];
		foreach($this->data['items'] as $item){
			if( is_null($item['pic_hd_url']) ){
				$item['pic_hd_url'] = '<b style="color:green">!!!MISSING!!!</b>';
			}
			else {
// 				$size = getimagesize($item['pic_hd_url']);
// 				$item['pic_hd_url'] = "<a href='".$item['pic_hd_url']."'>Link (".$size[0]."x".$size[1].")</a>"; 
				$item['pic_hd_url'] = "<a href='".$item['pic_hd_url']."'>Link</a>"; 
// 				continue;
			}
			
			if( empty($item['coord_colors']) ){
				$item['coord_colors'] = '<b style="color:green">!!!MISSING!!!</b>';
			}

			$unique_id = $item['product_type'].$item['product_id'];
			if( in_array( $unique_id, $product_ids_looped) ){
// 				array_push($colors, ['item_id'=>$item['item_id'], 'code'=>$item['code'], 'color'=>$item['color'], 'coord_colors'=>$item['coord_colors'], 'pic_big_url'=>$item['pic_big_url'], 'pic_hd_url'=>$item['pic_hd_url'] ]);
				array_push($colors, ['item_id'=>$item['item_id'], 'code'=>$item['code'], 'color'=>$item['color'], 'coord_colors'=>$item['coord_colors'], 'pic_hd_url'=>$item['pic_hd_url'] ]);
				continue;
			}
			else {
// 				New pattern being looped!
// 				Show colors and break page
				if( !empty($colors) ){
					$colors_txt = '';
					foreach($colors as $c){
// 						$colors_txt .= "<tr><td>".$c['item_id']."</td><td>".$c['code']."</td><td>".$c['color']."</td><td>".$c['coord_colors']."</td> <td><a href='".$c['pic_big_url']."'>Link</a></td> <td>".$c['pic_hd_url']."</td> </tr>";
						$colors_txt .= "<tr><td>".$c['item_id']."</td><td>".$c['code']."</td><td>".$c['color']."</td><td>".$c['coord_colors']."</td> <td>".$c['pic_hd_url']."</td> </tr>";
					}
// 					if( !$misses_only ) echo "<table border='1'><thead><tr><td>Item ID</td><td>SKU</td><td>Color Name</td><td>Color Variety</td><td>Small Image URL</td><td>HighRes Image URL</td></tr></thead><tbody>$colors_txt</tbody></table><br>$page_break";
					if( !$misses_only ) echo "<table border='1'><thead><tr><td>Item ID</td><td>SKU</td><td>Color Name</td><td>Color Variety</td><td>HighRes Image URL</td></tr></thead><tbody>$colors_txt</tbody></table><br>$page_break";
				}
				$colors = [];
// 				array_push($colors, ['item_id'=>$item['item_id'], 'code'=>$item['code'], 'color'=>$item['color'], 'coord_colors'=>$item['coord_colors'], 'pic_big_url'=>$item['pic_big_url'], 'pic_hd_url'=>$item['pic_hd_url'] ]);
				array_push($colors, ['item_id'=>$item['item_id'], 'code'=>$item['code'], 'color'=>$item['color'], 'coord_colors'=>$item['coord_colors'], 'pic_hd_url'=>$item['pic_hd_url'] ]);
			}

			if( !$misses_only ){
				$miss = "<b>---MISSING---</b>";
				$txt = "<table>" .
				"<tr><td><b>Product ID:</b></td><td> " . $item['product_id'] . "</td></tr>" .
				"<tr><td><b>Product Name:</b></td><td> " . $item['product_name'] . "</td></tr>" .
				"<tr><td><b>Collection Name:</b></td><td> " . $item['collection_name'] . "</td></tr>" .
				"<tr><td><b>Category:</b></td><td> " . $item['category'] . "</td></tr>" .
				"<tr><td><b>Weave/Construction:</b></td><td> " . ( strlen($item['weave']) > 0 ? $item['weave'] : $miss ) . "</td></tr>" .
				"<tr><td><b>Recommended Application:</b></td><td> " . ( strlen($item['uses']) > 0 ? $item['uses'] : $miss ) . "</td></tr>" .
				"<tr><td><b>Pattern:</b></td><td> " . ( strlen($item['pattern']) > 0 ? $item['pattern'] : $miss ) . "</td></tr>" .
				"<tr><td><b>Width:</b></td><td> " . ( strlen($item['width']) > 0 ? $item['width'] : $miss ) . "</td></tr>" .
				"<tr><td><b>Repeats:</b></td><td> " . $item['repeats'] . "</td></tr>" .
				"<tr><td><b>Content:</b></td><td> " . ( strlen($item['content_front']) > 0 ? $item['content_front'] : $miss ) . "</td></tr>" .
				"<tr><td><b>Outdoor:</b></td><td> " . $item['outdoor'] . "</td></tr>" .
				"<tr><td><b>Country of Origin:</b></td><td> " . ( strlen($item['origin']) > 0 ? $item['origin'] : $miss ) . "</td></tr>" .
				"<tr><td><b>Description:</b></td><td> " . ( strlen($item['description']) > 0 ? $item['description'] : $miss ) . "</td></tr>" .
				"<tr><td><b>Finish:</b></td><td> " . $item['finish'] . "</td></tr>" .
				"<tr><td><b>Firecode:</b></td><td> " . $item['firecode'] . "</td></tr>" .
				"<tr><td><b>Abrasion:</b></td><td> " . $item['abrasion'] . "</td></tr>" .
				"<tr><td><b>Cleaning:</b></td><td> " . $item['cleaning'] . "</td></tr>" .
				"<tr><td><b>Railroaded:</b></td><td> " . $item['railroaded'] . "</td></tr>" .
				"<tr><td><b>Format:</b></td><td> " . $item['format'] . "</td></tr>" .
				"<tr><td><b>Yards per roll:</b></td><td> " . ( strlen($item['yards_per_roll']) > 0 ? $item['yards_per_roll'] . ' yds/roll' : $miss ) . "</td></tr>" .
				"<tr><td><b>Lead time:</b></td><td> " . ( strlen($item['lead_time']) > 0 ? $item['lead_time'] : $miss ) . "</td></tr>" .
				"<tr><td><b>URL:</b></td><td> " . $item['url_title'] . "</td></tr>" .
				"<tr><td><b>Specsheet URL:</b></td><td> " . $item['specsheet_url'] . "</td></tr>" .
				"<tr><td><b>Beauty shot URL:</b></td><td> " . $item['beauty_shot_url'] . "</td></tr>" .
				//              "<tr><td><b>Price:</b></td><td> " . $item['price'] . "</td></tr>" .
				"<tr><td><b>Price Range:</b></td><td> " . $item['price_range'] . "</td></tr>" .
				"<tr><td><b>Minimum:</b></td><td> " . $item['min_units'] . "</td></tr>" .
				"<tr><td><b>Ships Internationally:</b></td><td> " . $item['ship_internationally'] . "</td></tr>" .
				"<tr><td><b>Availability:</b></td><td> " . $item['availability'] . "</td></tr>" .
				"<tr><td><b>Residential/Commercial:</b></td><td> " . $item['res_comm_use'] . "</td></tr>" .
				"<tr><td><b>Warranty:</b></td><td> " . $item['warranty'] . "</td></tr>" .
				"</table>"
				;
				echo $txt; 
			}
			else {
				// Check what data is missing and display
				$skip = ['repeats', 'finish', 'railroaded'];
				$data_missing = [];
				foreach($this->filters['select'] as $selects){
					if( !in_array($selects, $skip) && strlen($item[$selects]) < 1 ){
						$data_missing[] = $selects;
					}
				}
				if( !empty($data_missing) ){
					echo "<u><b>" . $item['product_name'] . "</b> is MISSING:</u><br>";
					echo implode("<br>", $data_missing) . "<br><br>";
				}
			}
			array_push($product_ids_looped, $unique_id);
		}
		if( !empty($colors) ){
			$colors_txt = '';
			foreach($colors as $c){
				$colors_txt .= "<tr><td>".$c['item_id']."</td><td>".$c['code']."</td><td>".$c['color']."</td><td>".$c['coord_colors']."</td><td>".$c['pic_hd_url']."</td></tr>";
			}
			if( !$misses_only ) echo "<table border='1'><thead><tr><td>Item ID/SKU</td><td>Color Number</td><td>Color Name</td><td>Color Variety</td><td>Image URL</td></tr></thead><tbody>$colors_txt</tbody></table><br><div style='page-break-after:always;'></div>";
		}
		exit;
	}
  
}