<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product extends MY_Controller
{

	protected $product_id;
	protected $product_type;

	function __construct()
	{
		parent::__construct();
		$this->data['info'] = [];
		$this->thisC = 'product';
		$this->load->model('Product_model', 'model');
		$this->load->model('Search_model', 'search');
		$this->load->library('FileUploadToS3');
		array_push($this->data['crumbs'], 'Products');

		if (strpos($this->input->post('product_id'), '-') !== false) {
			$post = explode('-', $this->input->post('product_id'));
			$this->data['product_id'] = intval($post[0]);
			$this->data['product_type'] = (!in_array($post[1], array(constant('Regular'), constant('Digital'), constant('ScreenPrint'))) ? constant('Regular') : $post[1]);
		} else {
			$this->data['product_id'] = ($this->input->post('product_id') == NULL || $this->input->post('product_id') == '0' ? 0 : intval($this->input->post('product_id')));
			$this->data['product_type'] = ($this->input->post('product_type') == NULL ? (isset($this->data['info']['product_type']) ? $this->data['info']['product_type'] : constant('Regular')) : $this->input->post('product_type'));
		}

		$this->data['hasEditPermission'] = $this->hasPermission('product', 'edit');
		$this->data['hasMPLPermission'] = $this->hasPermission('product', 'master_price_list');
	}

	public function index()
	{
		array_push($this->data['crumbs'], 'Specifications & Pricing');
		$this->data['special_cases'] = json_encode($this->special_cases);
		$this->data['stamps'] = $this->get_stamps(product);
		$this->view('product/list');
	}

    public function uploadToTemp()
	{
		$this->fileuploadtos3->uploadToTemp();
	}

	public function get_products()
	{
		$list = array();
		$list['arr'] = array();
		$search = $this->input->post('search');
		
		if (strlen($search['value']) > 0) {
			// Set the search text for the regular search method
			$this->model->searchText = $search['value'];
			$list = $this->model->get_products_spec_view($this->data['is_showroom']);
		}

		echo json_encode($this->return_datatables_data($list['arr'], $list));
	}



	/*
	public function get_prices(){
		$list = array(); $list['arr'] = array();
		$search = $this->input->post('search');
	$searchtype = $this->input->post('searchtype');
		if( strlen($search['value']) > 0 ){
	  switch($searchtype){
		case constant('product'):
		  $list = $this->model->get_products_prices_view();
		  break;
		case constant('item'):
		  $filters = array( 'includeCosts'=>true, 'includeStock'=>true, 'model_table'=>$this->model->t_item, 'datatable'=>true );
		  $list = $this->search->do_search($filters);
		  break;
	  }
		}
		echo json_encode( $this->return_datatables_data($list['arr'], $list) );
	}
	*/

	public function specsheet($type = null, $uri_product_id = null)
	{
		if (is_null($type) and is_null($uri_product_id)) {
			# No specsheets for pure digital styles
			return;
		}
		if ($type == 'item_id') {
			$row = $this->model->get_product_id(intval($uri_product_id));
			$uri_product_id = $row['product_id'];
			$type = $row['product_type'];
		}

		// Format the parameters for search
		if (is_numeric($uri_product_id)) {
			$id = intval($uri_product_id);
		} else {
			// To implement search
			// Assume is an url_title in the T_PRODUCT table for ALL products
			$id = $this->model->search_for_id($uri_product_id, $type);
			if (is_null($id)) {
//				echo "No specsheet";
				return;
			}
		}

		if ($type === Regular or $type === Digital) {
			// If PRODUCT specsheet is requested
			$this->data['specType'] = 'fabrics';
			$this->data['product_type'] = $type;
			$this->data['product_id'] = $id;
			$this->data['info'] = $this->model->get_product_specsheet($type, $id, array('visibleOnly' => true));

			//echo "<pre> PRODUCT::specsheet(type, uri_product_id) ";
			//echo "<br />TYPE: ".  $type ." <br />URI_PRODUCT_ID:". $uri_product_id ."<br>";
			//print_r( $this->data );
			//echo "</pre>";

			// Start preparing data for the specsheet!
			$this->prepareSpecsheet($this->data['product_id']);
// 			echo "<pre>"; var_dump($this->data);exit;
		} else if ($type === item) {
			// If ITEM spec sheet is requested
			$this->data['specType'] = 'items';
			$this->data['product_type'] = $type;
			$item_id = $id;
			$this->load->model('Item_model', 'item_model');
			$aux = $this->model->get_product_id($item_id);
			$this->data['product_type'] = $aux['product_type'];
			$this->data['product_id'] = $aux['product_id'];
			$this->data['item_id'] = $item_id;
			$item_data = $this->item_model->get_item($item_id, $this->data['product_type']);
			$item_data['id'] = $item_data['item_id'];
			if (!is_null($item_data)) {
				// Checks if the items is existing
				$item_data['type'] = 'fabrics_items';
				$this->data['item_data'] = $item_data;
				$this->data['info'] = $this->model->get_product_specsheet($this->data['product_type'], $this->data['product_id'], array('visibleOnly' => true));
				// Get the related data to this specific product
				$this->prepareSpecsheet($item_data['product_id']);
				// Get rid of data that is shared with other views and not needed on the spec sheet
				$only_items = $this->data['colors_arr'];
// 				$this->data['colors_arr'] = $only_items;
				// Delete the item with ID $item_id from the $this->data['colors_arr']
				for ($i = 0; $i < count($this->data['colors_arr']); $i++) {
					if ($this->data['colors_arr'][$i]['id'] == $item_id) {
						$this->data['item_data'] = array_merge($this->data['item_data'], $this->data['colors_arr'][$i]);
						unset($this->data['colors_arr'][$i]);
						break;
					}
				}
				//echo "<pre>"; var_dump($this->data);exit;
			}
		} else {
			echo "Invalid type provided.";
			return;
		}
// 		$this->load->view('product/spec_view2', $this->data);

		$this->load_specsheet_libraries();
		$this->load->view('product/spec_view', $this->data);
	}

	private function prepareSpecsheet($product_id)
	{
		// Prepare the data for the frontend, 1 product at a time

		$this->load->model('Specs_model', 'specs');
		$this->data['spec'] = array();

		$aux = explode(constant('delimiter'), $this->data['info']['uses']);
		if ($this->is_valid_spec_arr($aux)) {
			$aux = array('text' => 'Use', 'data' => $aux);
			array_push($this->data['spec'], $aux);
		}

		$aux = explode(constant('delimiter'), $this->data['info']['weaves']);
		if ($this->is_valid_spec_arr($aux)) {
			$aux = array('text' => 'Weave', 'data' => $aux);
			array_push($this->data['spec'], $aux);
		}

		if ($this->is_valid_spec_str($this->data['info']['width'])) {
			$aux = array('text' => 'Width', 'data' => array($this->data['info']['width'] . ' "'));
			array_push($this->data['spec'], $aux);
		}

		if ($this->is_valid_spec_str($this->data['info']['weight_n'])) {
			$unit = $this->specs->get_weight_units($this->data['info']['weight_unit_id']);
			$aux = array('text' => 'Weight', 'data' => array($this->data['info']['weight_n'] . ' ' . $unit['name']));
			array_push($this->data['spec'], $aux);
		}

		if ($this->is_valid_spec_str($this->data['info']['vrepeat'])) {
			$aux = array('text' => 'Vertical Repeat', 'data' => array($this->data['info']['vrepeat'] . ' "'));
			array_push($this->data['spec'], $aux);
		}

        if ($this->is_valid_spec_str($this->data['info']['hrepeat'])) {
            $aux = array('text' => 'Horizontal Repeat', 'data' => array($this->data['info']['hrepeat'] . ' "'));
            array_push($this->data['spec'], $aux);
        }

        if (array_key_exists("lightfastness", $this->data['info']) AND $this->is_valid_spec_str($this->data['info']['lightfastness'])) {
            $aux = array('text' => 'Lightfastness', 'data' => array($this->data['info']['lightfastness']), 'row_style' => 'line-height: normal!important;');
            array_push($this->data['spec'], $aux);
        }

		$aux = explode(constant('delimiter'), $this->data['info']['content_front']);
		if ($this->is_valid_spec_arr($aux)) {
			$aux = array('text' => 'Face Content', 'data' => $aux);
			array_push($this->data['spec'], $aux);
		}

        $aux = explode(constant('delimiter'), $this->data['info']['content_back']);
        if ($this->is_valid_spec_arr($aux)) {
            $aux = array('text' => 'Back Content', 'data' => $aux);
            array_push($this->data['spec'], $aux);
        }

		// Cleaning of special cases
		$aux = array();
		$temp = explode(constant('delimiter'), $this->data['info']['abrasions']);
		//var_dump($temp); exit;
		if (count($temp) > 0) {
			foreach ($temp as $a) {
				$s = explode('*', $a);
				if (!empty($s[0]) && !in_array($s[0], $this->special_cases['abrasion'])) {
					$pc = explode('-', $s[1]);
					$limit = $pc[0];
					$rubs = number_format($pc[1], 0);
					$test = $pc[2];
					$string = /*$limit.' '.*/
					  $rubs . ' ' . $test;
					$string = str_replace('Unknown', '', $string);
					array_push($aux, trim($string));
				}
			}
		}
		if ($this->is_valid_spec_arr($aux)) {
			$aux = array('text' => 'Abrasion', 'data' => $aux);
			array_push($this->data['spec'], $aux);
		}

		$aux = explode(constant('delimiter'), $this->data['info']['firecodes']);
		if ($this->is_valid_spec_arr($aux)) {
			$aux = array('text' => 'Fire Rating', 'data' => $aux);
			array_push($this->data['spec'], $aux);
		}

		$aux = explode(constant('delimiter'), $this->data['info']['finishs']);
		if ($this->is_valid_spec_arr($aux)) {
			$aux = array('text' => 'Finish', 'data' => $aux);
			array_push($this->data['spec'], $aux);
		}

		$aux = explode(constant('delimiter'), $this->data['info']['cleanings']);
		if ($this->is_valid_spec_arr($aux)) {
			$aux = array('text' => 'Cleaning', 'data' => $aux);
			array_push($this->data['spec'], $aux);
		}

		$aux = [];
		if (strlen($this->data['info']['cleaning_instructions_files']) > 0) {
			$care_files = explode('**', $this->data['info']['cleaning_instructions_files']);
			$care_names = explode(delimiter, $this->data['info']['cleaning_instructions']);
			for ($i = 0; $i < count($care_files); $i++) {
				$a = anchor($care_files[$i], $care_names[$i] . ' link', ['target' => '_blank', 'style' => 'text-decoration:none']);
				array_push($aux, $a);
			}
			if ($this->is_valid_spec_arr($aux)) {
				array_push($this->data['spec'], ['text' => 'Care Instructions', 'data' => $aux]);
			}
		}

		if ($this->is_valid_spec_str($this->data['info']['origin'])) {
			$aux = array('text' => 'Origin', 'data' => array($this->data['info']['origin']));
			array_push($this->data['spec'], $aux);
		}

		$aux = array('text' => 'Outdoor', 'data' => array(($this->data['info']['outdoor'] === 'Y' ? 'Yes' : 'No')));
		array_push($this->data['spec'], $aux);

		if ($this->data['info']['prop_65'] === 'Y') {
			$aux = array('text' => 'Prop 65', 'data' => ['Yes']);
			array_push($this->data['spec'], $aux);
		}

		if ($this->data['info']['ab_2998_compliant'] === 'Y') {
			$aux = array('text' => 'AB 2998', 'data' => ['Yes']);
			array_push($this->data['spec'], $aux);
		}


		// Images
		$this->load->model('File_directory_model', 'file_directory');

		// Beauty Shot image
		if (!is_null($this->data['info']['pic_big_url']) and strpos($this->data['info']['pic_big_url'], 'placeholder') == false) {
			// $this->data['img_url'] = $this->data['info']['pic_big_url'];
			$this->data['img_url'] = $this->fileuploadtos3->convertLegacyImgSrcToS3($this->data['info']['pic_big_url']);

//		} else if (!is_null($this->data['info']['pic_big']) && !in_array($this->data['info']['pic_big'], array('N', 'P'))) {
//			if ($this->data['info']['product_type'] === Regular) {
//				$img_id = $this->data['info']['product_id'];
//			} else {
//				$img_id = $this->data['info']['style_id'];
//			}
//			$this->data['img_url'] = $this->file_directory->image_src_path('load', $this->data['product_type']) . $img_id . '.jpg';
		} else {
			$this->data['img_url'] = '';
		}

        // PKL Uploading edit 
		//$this->data['img_url'] = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $this->data['img_url'] );

		//$this->data['imgUrl'] = ( !isset($this->data['info']['pic_big']) || in_array($this->data['info']['pic_big'], array('P', 'N')) ? 'noimage' : $this->file_directory->image_src_path('load', 'fabrics').$id.'.jpg' );

		$this->data['info']['count_with_item_num'] = 0;
		$colors = $this->model->get_products_items_for_specsheet($this->data['product_type'], $product_id, array('get_all' => false));
        sort_by_key($colors, "color", true);
        $this->data['colors_arr'] = $colors;

		// Set the image URL for the items
//		$folder = $this->file_directory->image_src_path('load', $this->data['product_type'] . "_items");
//		if ($this->data['info']['product_type'] === Regular) {
//			$img_key = 'id';
//		} else if ($this->data['info']['product_type'] === Digital) {
//			$img_key = 'style_item_id';
//		}

		for ($i = 0; $i < count($this->data['colors_arr']); $i++) {
			if (!is_null($this->data['colors_arr'][$i]['code'])) {
				$this->data['info']['count_with_item_num'] += 1;
			}
			if (!is_null($this->data['colors_arr'][$i]['pic_big_url'])) {
				// Good url, keep as it is
//			} else if (!is_null($this->data['colors_arr'][$i]['pic_big']) && $this->data['colors_arr'][$i]['pic_big'] !== 'N') {
//				$this->data['colors_arr'][$i]['pic_big_url'] = $folder . $this->data['colors_arr'][$i][$img_key] . '.jpg';
			} else {
				$this->data['colors_arr'][$i]['pic_big_url'] = '';
			}
		}

		$count_color_arr = count($this->data['colors_arr']) > 0 ? count($this->data['colors_arr']) : 1;
		$ratio_with_number = floatval($this->data['info']['count_with_item_num']) / $count_color_arr;
		if ($this->data['info']['product_type'] === Regular and $ratio_with_number < 0.2) {
			$this->data['info']['product_name'] = $this->data['info']['vendors_abrev'] . ' ' . $this->data['info']['product_name'];
		}

	}

	/*
		Product editing
	*/

	public function edit($product_type=null, $product_id=null)
	{
		 
		$this->data['product_type'] = (!is_null($product_type) ? $product_type : $this->data['product_type']);
		$this->data['product_id'] = (!is_null($product_type) ? $product_id : $this->data['product_id']);

		$this->isNew = ($this->data['product_id'] === 0 ? true : false);
		array_push($this->data['crumbs'], ($this->isNew ? 'Create New' : 'Edit'));
		$this->data['saveUrl'] = site_url('product/submit_form');
		$this->data['info'] = $this->model->get_product_edit($this->data['product_type'], $this->data['product_id']);
		// print_r($this->data['info']);
// 		var_dump($this->data['info']);
		$this->data['hasPermission'] = $this->hasPermission('product', 'edit');
		/*
		$btnDelete = "<i class='fa fa-trash btn-action btnDelete' aria-hidden='true' data-product_id='".$this->data['product_id']."' data-product_type='".$this->data['product_type']."'></i>";
		$this->data['btnDelete'] = ( $this->hasPermission('product', 'edit') ? $btnDelete : '' );
		*/
// 		echo "<pre>"; var_dump($this->data['info']); exit;
 
		switch ($this->data['product_type']) {

			case constant('Regular'):
				$this->set_dropdowns_general();
				$this->data['form'] = $this->load->view('product/form/form_regular', $this->data, true);
				break;

			case constant('Digital'):
				$this->data['title'] = 'Digital Product';
				$this->set_dropdowns_combination();
				$this->data['form'] = $this->load->view('product/form/form_combined', $this->data, true);
				break;

// 			case constant('ScreenPrint'):
// 				$this->data['title'] = 'Screen Print Product';
// 				$this->set_dropdowns_combination();
// 				$this->data['form'] = $this->load->view('product/form/form_combined', $this->data, true);
// 				break;

			default:

				break;
		}

		$this->view('product/form/_header');

	}

	public function set_dropdowns_general()
	{
		$options = array();
		$selected = array();
		// Common dropdowns
		$this->load->model('Specs_model', 'specs');

		/*
		$l = $this->specs->get_product_status();
		$options = $this->decode_array($l, 'id', 'descr');
		$options[count($options)+1] = 'None selected';
		$selected = $this->data['info']['product_status_id'];
		if( $selected == NULL ) $selected = array(count($options));
		$this->data['dropdown_product_status'] = form_dropdown('product_status', $options, set_value('product_status', $selected), " class='single-dropdown' tabindex='-1' ");

		$l = $this->specs->get_stock_status();
		$options = $this->decode_array($l, 'id', 'descr');
		$options[count($options)+1] = 'None selected';
		$selected = $this->data['info']['stock_status_id'];
		if( $selected == NULL ) $selected = array(count($options));
		$this->data['dropdown_stock_status'] = form_dropdown('stock_status', $options,  set_value('stock_status', $selected), " class='single-dropdown' tabindex='-1' ");
		*/

		$this->data['no_repeat'] = (!$this->isNew && is_null($this->data['info']['vrepeat']) && is_null($this->data['info']['hrepeat']));
		$this->data['railroaded'] = isset($this->data['info']['railroaded']) && $this->data['info']['railroaded'] === 'Y';

		/*
			$l = $this->specs->get_shelfs();
			$options = $this->decode_array($l, 'id', 'name');
			$selected = explode(constant('delimiter'), $this->data['info']['shelf_id'] );
			$this->data['dropdown_shelf'] = form_multiselect('shelf[]', $options, set_value('shelf[]', $selected), " class='multi-dropdown' tabindex='-1' ");
			*/

		$l = $this->specs->get_weight_units();
		$options = $this->decode_array($l, 'id', 'name');
		$options[0] = 'None selected';
		
		$selected = explode(constant('delimiter'), $this->data['info']['weight_unit_id']);
		if ($selected[0] == '') $selected = array(0);
		$this->data['dropdown_weight_unit'] = form_dropdown('weight_unit', $options, set_value('weight_unit', $selected), " class='single-dropdown' tabindex='-1' ");

		$l = $this->specs->get_uses();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = explode(constant('delimiter'), $this->data['info']['uses_id']);
		$this->data['dropdown_uses'] = form_multiselect('uses[]', $options, set_value('uses[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		$l = $this->specs->get_weaves();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = explode(constant('delimiter'), $this->data['info']['weaves_id']);
		$this->data['dropdown_weave'] = form_multiselect('weave[]', $options, set_value('weave[]', $selected), " class='multi-dropdown w-filtering' tabindex='-1' ");

		$data = !is_null($this->data['info']['content_front']) ? explode(constant('delimiter'), $this->data['info']['content_front']) : array();
		$this->data['list_content_f'] = ul($data, " id='list_content_f' class='list-unstyled content-list' ");

		$data = !is_null($this->data['info']['content_back']) ? explode(constant('delimiter'), $this->data['info']['content_back']) : array();
		$this->data['list_content_b'] = ul($data, " id='list_content_b' class='list-unstyled content-list' ");

		$data = array();
		$rawdata = $this->model->select_product_abrasion($this->data['product_id'], array());
		//var_dump($rawdata);
		if (!empty($rawdata)) {
			foreach ($rawdata as $a) {
				if (in_array($a['abrasion_test_id'], $this->special_cases['abrasion'])) {
					$str = $a['abrasion_test_name'];
				} else if ($a['abrasion_limit_id'] === '1') {
					$str = $a['rubs'] . ' ' . $a['abrasion_test_name'];
				} else {
					$str = $a['abrasion_limit_name'] . ' @ ' . $a['rubs'] . ' ' . $a['abrasion_test_name'];
				}

				if(! $this->data['is_showroom']) {
					if (!is_null($a['files'])) {
						$files = explode(constant('delimiterFiles'), $a['files']);
						$str .= "&nbsp;&nbsp;";
						foreach ($files as $f) {
							$str .= '&nbsp;<a href="' . site_url() . $f . '" target="_blank"><i class="fa fa-file"></i></a>';
						}
					} else if ($a['data_in_vendor_specsheet'] == 'Y') {
						$str .= '&nbsp;&nbsp;<a href="#anchor-product-files"><i class="fas fa-level-down" data-toggle="tooltip" data-title="Data is in vendor specsheet"></i></a>';
					}
				}

				if ($a['visible'] === 'Y') {
					$str = '<i class="fal fa-eye"></i>&nbsp;' . $str;
				} else {
					$str = '<i class="fal fa-minus"></i>&nbsp;' . $str;
				}
				array_push($data, $str);
			}
		}
		$this->data['list_abrasion'] = ul($data, " id='list_abrasion' class='list-unstyled content-list' ");

		$data = array();
		//$data = !is_null($this->data['info']['firecodes']) ? explode(constant('delimiter'), $this->data['info']['firecodes']) : array();
		$rawdata = $this->model->select_product_firecodes($this->data['product_id'], array());
		//var_dump($rawdata);
		if (!empty($rawdata)) {
			foreach ($rawdata as $a) {
				$str = $a['firecode_test_name'];
				if(! $this->data['is_showroom']) {
					if (!is_null($a['files'])) {
						$files = explode(constant('delimiterFiles'), $a['files']);
						$str .= "&nbsp;&nbsp;";
						foreach ($files as $f) {
							$str .= '&nbsp;<a href="' . site_url() . $f . '" target="_blank"><i class="fa fa-file"></i></a>';
						}
					} else if ($a['data_in_vendor_specsheet'] == 'Y') {
						$str .= '&nbsp;&nbsp;<a href="#anchor-product-files"><i class="fas fa-level-down" data-toggle="tooltip" data-title="Data is in vendor specsheet"></i></a>';
					}
				}
				if ($a['visible'] === 'Y') {
					$str = '<i class="fal fa-eye"></i>&nbsp;' . $str;
				} else {
					$str = '<i class="fal fa-minus"></i>&nbsp;' . $str;
				}
				array_push($data, $str);
			}
		}
		//var_dump($data);
		$this->data['list_firecode'] = ul($data, " id='list_firecode' class='list-unstyled content-list' ");

		$l = $this->specs->get_finishs();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = explode(constant('delimiter'), $this->data['info']['finishs_id']);
		$this->data['dropdown_finish'] = form_multiselect('finish[]', $options, set_value('finish[]', $selected), " class='multi-dropdown w-filtering' tabindex='-1' ");

		$l = $this->specs->get_cleanings();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = explode(constant('delimiter'), $this->data['info']['cleanings_ids']);
		$this->data['dropdown_cleaning'] = form_multiselect('cleaning[]', $options, set_value('cleaning[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		$l = $this->specs->get_cleanings_instructions();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = explode(constant('delimiter'), $this->data['info']['cleaning_instructions_ids']);
		$this->data['dropdown_cleaning_instructions'] = form_multiselect('cleaning_instructions[]', $options, set_value('cleaning_instructions[]', $selected), " class='multi-dropdown' tabindex='-1' ");
		
		$l = $this->specs->get_warranties();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = explode(constant('delimiter'), $this->data['info']['warranty_ids']);
		$this->data['dropdown_warranty'] = form_multiselect('warranty[]', $options, set_value('warranty[]', $selected), " class='single-dropdown w-filtering' tabindex='-1' ");
		
		$l = $this->specs->get_origins();
		$options = $this->decode_array($l, 'id', 'name');
		$options[0] = 'None selected';
		$selected = explode(constant('delimiter'), $this->data['info']['origin_id']);
		if ($selected[0] == '') $selected = array(0);
		$this->data['dropdown_origin'] = form_dropdown('origin', $options, set_value('origin', $selected), " class='single-dropdown w-filtering' tabindex='-1' ");

		$l = $this->specs->get_vendors();
		$options = $this->decode_array($l, 'id', 'name');
		$options[0] = 'None selected';
		$selected = explode(constant('delimiter'), $this->data['info']['vendors_id']);
		if ($selected[0] == '') $selected = array(0);
		$this->data['dropdown_vendor'] = form_dropdown('vendor', $options, set_value('vendor', $selected), " class='single-dropdown w-filtering' tabindex='-1' ");

		$this->data['list_files']['tbody'] = '';
		$this->data['list_files']['tfoot'] = '';
		$data = explode(constant('delimiterFiles'), $this->data['info']['files']);
//		if ($this->data['product_id'] == '2380') {
//			var_dump($this->data['info']['files']);
//			var_dump($data);
//		}
		$lis = array();
		$files_encoded = array();
		if ($data[0] !== '') {
			foreach ($data as $d) {
//				unset($aux);
				$aux = explode('#', $d);
				$isOther = ($aux[3] === $this->category_files_ids['other']);
				$aux_d = array(
				  'url_dir' => $aux[0],
				  'date' => nice_date($aux[1], 'm-d-Y'),
				  'user_id' => $aux[2],
				  'category_id' => $aux[3],
				  'category_name' => ($isOther ? $aux[5] : $aux[4]),
				  'descr' => $aux[5]
				);

				array_push($files_encoded, $aux_d);
				// Clean up URL path for web access (strip filesystem prefix if present)
				$web_url_path = str_replace('/opuzen-efs/prod/opms', '', $aux_d['url_dir']);
				
				$this->data['list_files']['tbody'] .= "
				<tr>
					<td><a href='" . site_url() . $web_url_path . "' target='_blank'><i class='fa fa-file btnViewFile' aria-hidden='true'></i> " . ($isOther ? $aux_d['descr'] : $aux_d['category_name']) . "</a></td>
					<td>" . $aux_d['date'] . "</td>
					" . ($this->hasPermission('product', 'edit') ? "<td><i class='fa fa-times-circle delete_temp_url' aria-hidden='true'></i></td>" : '') . "
				</tr>";
			}
		}
		$this->data['files_encoded'] = json_encode($files_encoded);
		//$this->data['list_files'] = ul($lis, " id='list_files' class='list-unstyled d-flex flex-wrap flex-c-50' ");

		$l = $this->specs->get_categories_files();
		$options = $this->decode_array($l, 'id', 'name');
		$this->data['dropdown_category_files'] = form_dropdown('category_files', $options, array(), " id='category_files' class='single-dropdown btn btn-default' ");

		$l = $this->specs->get_prices_types();
		$options = $this->decode_array($l, 'id', 'name');
		//$options[0] = 'None selected';

		$selected = explode(constant('delimiter'), $this->data['info']['cost_cut_type_id']);
		if ($selected[0] == '') $selected = array(1);
		$this->data['dropdown_cost_cut_type'] = form_dropdown('cost_cut_type_id', $options, set_value('cost_cut_type_id', $selected), " class='single-dropdown' tabindex='-1' ");

		$selected = explode(constant('delimiter'), $this->data['info']['cost_half_roll_type_id']);
		if ($selected[0] == '') $selected = array(1);
		$this->data['dropdown_cost_half_roll_type'] = form_dropdown('cost_half_roll_type_id', $options, set_value('cost_half_roll_type_id', $selected), " class='single-dropdown' tabindex='-1' ");

		$selected = explode(constant('delimiter'), $this->data['info']['cost_roll_type_id']);
		if ($selected[0] == '') $selected = array(1);
		$this->data['dropdown_cost_roll_type'] = form_dropdown('cost_roll_type_id', $options, set_value('cost_roll_type_id', $selected), " class='single-dropdown' tabindex='-1' ");

		$selected = explode(constant('delimiter'), $this->data['info']['cost_roll_landed_type_id']);
		if ($selected[0] == '') $selected = array(1);
		$this->data['dropdown_cost_roll_landed_type'] = form_dropdown('cost_roll_landed_type_id', $options, set_value('cost_roll_landed_type_id', $selected), " class='single-dropdown' tabindex='-1' ");

		$selected = explode(constant('delimiter'), $this->data['info']['cost_roll_ex_mill_type_id']);
		if ($selected[0] == '') $selected = array(1);
		$this->data['dropdown_cost_roll_ex_mill_type'] = form_dropdown('cost_roll_ex_mill_type_id', $options, set_value('cost_roll_ex_mill_type_id', $selected), " class='single-dropdown' tabindex='-1' ");


		// Showcase data loading
		if ($this->isNew) {
			$this->data['info']['url_title'] = $this->model->site_urls['website'];
		} else {
			$this->data['info']['url_title'] = $this->model->site_urls['website'] . 'product/' . $this->data['info']['url_title'];
		}

		$this->load->model('File_directory_model', 'file_directory');
		if (!$this->isNew && !is_null($this->data['info']['pic_big_url'])) {
			//$this->data['img_url'] = $this->data['info']['pic_big_url'];
			$this->data['img_url'] = $this->fileuploadtos3->convertLegacyImgSrcToS3($this->data['info']['pic_big_url']);
		} else {
			$this->data['img_url'] = $this->file_directory->image_src_path(['img_type' => 'placeholder']);
		}

        // PKL Uploading edit 
		$this->data['img_url'] = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $this->data['img_url'] );

		$l = $this->specs->get_showcase_collections();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = $this->isNew ? array() : explode(constant('delimiter'), $this->data['info']['showcase_collection_id']);
		$this->data['dropdown_showcase_collection'] = form_multiselect('showcase_collection[]', $options, set_value('showcase_collection[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		$l = $this->specs->get_showcase_contents_web();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = $this->isNew ? array() : explode(constant('delimiter'), $this->data['info']['showcase_contents_web_id']);
		$this->data['dropdown_showcase_contents_web'] = form_multiselect('showcase_contents_web[]', $options, set_value('showcase_contents_web[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		$l = $this->specs->get_showcase_patterns();
		$options = $this->decode_array($l, 'id', 'name');
		$selected = $this->isNew ? array() : explode(constant('delimiter'), $this->data['info']['showcase_pattern_id']);
		$this->data['dropdown_showcase_patterns'] = form_multiselect('showcase_patterns[]', $options, set_value('showcase_patterns[]', $selected), " class='multi-dropdown' tabindex='-1' ");

		if(!$this->isNew && !is_null($this->data['info']['portfolio_urls'])){
			$this->data['portfolio_urls'] = explode('**', $this->data['info']['portfolio_urls']);
		} else {
			$this->data['portfolio_urls'] = [];
		}
		
		
	}

	public function set_dropdowns_combination()
	{
		$options = array();
		$selected = array();
		// Common dropdowns
		$this->load->model('Specs_model', 'specs');

		/*
		$l = $this->specs->get_product_status();
		$options = $this->decode_array($l, 'id', 'name');
		$options[count($options)+1] = 'None selected';
		$selected = $this->data['info']['product_status_id'];
		if( $selected == NULL ) $selected = array(count($options));
		$this->data['dropdown_product_status'] = form_dropdown('product_status', $options, set_value('product_status', $selected), " class='single-dropdown' ");

		$l = $this->specs->get_stock_status();
		$options = $this->decode_array($l, 'id', 'name');
		$options[count($options)+1] = 'None selected';
		$selected = $this->data['info']['stock_status_id'];
		if( $selected == NULL ) $selected = array(count($options));
		$this->data['dropdown_stock_status'] = form_dropdown('stock_status', $options,  set_value('stock_status', $selected), " class='single-dropdown' ");
		*/

		if ($this->data['product_type'] == constant('Digital')) {
			$grounds_arr = $this->specs->get_digital_grounds();
			$styles_arr = $this->specs->get_digital_styles(array('onlyActive' => true));
		} else if ($this->data['product_type'] == constant('ScreenPrint')) {
			$grounds_arr = $this->specs->get_screenprint_grounds();
			$styles_arr = $this->specs->get_screenprint_styles(array('onlyActive' => true));
		}

		/*
			$l = $this->specs->get_shelfs();
			$options = $this->decode_array($l, 'id', 'name');
			$selected = explode(constant('delimiter'), $this->data['info']['shelf_id'] );
			$this->data['dropdown_shelf'] = form_multiselect('shelf[]', $options, set_value('shelf[]', $selected), " class='multi-dropdown' tabindex='-1' ");
			*/

		$options = $this->decode_array($styles_arr, 'id', 'name');
		$options[0] = 'None selected';
		if ($this->isNew) {
			$selected = array(0);
		} else {
			$style = $this->model->get_mapping_style_id($this->data['product_type'], $this->data['product_id']);
			$selected = array($style['id']);
		}
		$this->data['dropdown_style'] = ($this->isNew ?
		  form_dropdown('style', $options, set_value('style', $selected), " class='single-dropdown w-filtering' tabindex='-1' ")
		  : $style['name'] . form_hidden('style', $selected[0]));

		$ret = array();
		if (is_array($grounds_arr) && !empty($grounds_arr)) {
			foreach ($grounds_arr as $a) {
				$id = $a['item_id'];
				$name = $a['product_name'] . " / " . $a['color'];
				$ret[$id] = $name;
			}
		}
		$options = $ret;
		$options[0] = 'None selected';
		if ($this->isNew) {
			$selected = array(0);
		} else {
			$ground = $this->model->get_mapping_item_id($this->data['product_type'], $this->data['product_id']);
			$selected = array($ground['id']);
		}
		$this->data['dropdown_ground'] = ($this->isNew ?
		  form_dropdown('ground', $options, set_value('ground', $selected), " class='single-dropdown w-filtering' tabindex='-1' ")
		  : $ground['name'] . form_hidden('ground', $selected[0]));

		$this->data['grounds_json'] = json_encode($grounds_arr);
		
		if(!is_null($this->data['info']['portfolio_urls'])){
			$this->data['portfolio_urls'] = explode('**', $this->data['info']['portfolio_urls']);
		} else {
			$this->data['portfolio_urls'] = [];
		}
	}

	/*

			Save Product Form

	*/

	public 	function submit_form()
	{

		//echo "<pre> POST: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
// 		print_r($_POST);
// 		echo "</pre>";
// die;

		
		if ($this->input->is_ajax_request()) {
			$this->data['product_id'] = $this->input->post('product_id');
			$this->data['product_type'] = $this->input->post('product_type');
			$this->isNew = ($this->data['product_id'] === '0' ? true : false);
			$this->load->library('form_validation');
			$this->form_validation->set_rules($this->model->rules($this->data['product_type'], $this->data['product_id']));

			$this->load->model('File_directory_model', 'file_directory');

			if ($this->form_validation->run()) {
				if ($this->_my_validation()) {
					switch ($this->data['product_type']) {

						case constant('Regular'):
							$this->save_regular_product();
							break;

						case constant('Digital'):
						case constant('ScreenPrint'):
							$this->save_combined_product();
							break;

						default:
							$this->errors = array('Some data is missing - Error 434.');
							break;

					}
				}

				// Evaluate the transactions!
				if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					$error = $this->db->error();
					$this->data['answer']['message'] = 'Some error during the saving ocurred. <br>' . $error['message'];
					$this->data['answer']['status'] = $error['code'];
				} else if (!empty($this->errors)) {
					$this->db->trans_rollback();
					$this->data['answer']['message'] = ul($this->errors, $this->error_ul_attr);
					$this->data['answer']['status'] = 'error';
				} else {
					$this->db->trans_commit();
					if ($this->isNew) $this->data['answer']['continueUrl'] = site_url('item/index/' . $this->data['product_id'] . '-' . $this->data['product_type']);
					$this->data['answer']['status'] = 'OK';
				}

			} else {
				$this->data['answer']['message'] = validation_errors();
				$this->data['answer']['status'] = 'error';
			}

			echo json_encode($this->data['answer']);
			exit;

		} else {
			// Some data error
			$this->edit();
		}

	}

	function _my_validation()
	{
		$valid = true;

		$product_name = $this->input->post('product_name');
		$vendor_id = $this->input->post('vendor');

		if ($this->isNew && $this->data['product_type'] === Regular) {
			// Check if product_name/vendor combination exists
			$q = $this->model->get_product_name_vendor_combination($product_name);
			if ($q > 0) {
				foreach ($q as $p) {
					if ($p['vendor_id'] === $vendor_id) {
						$valid = false;
						$this->errors[] = "Product Name and Vendor combination selected already exists.";
						break;
					}
				}
			}
		}

		return $valid;
	}

	function save_regular_product()
	{
		$this->db->trans_begin();

		$product_name = $this->input->post('product_name');
		$url_title = strtolower(url_title($product_name));
		$width = $this->input->post('width');
		$vrepeat = $this->input->post('vrepeat');
        $hrepeat = $this->input->post('hrepeat');
        $lightfastness = $this->input->post('lightfastness');
		$no_repeat = $this->input->post('no_repeat');
		$outdoor = $this->input->post('outdoor');
		$in_master = $this->input->post('in_master');
		$dig_product_name = empty($this->input->post('dig_product_name')) ? null : $this->input->post('dig_product_name');
		$dig_width = empty($this->input->post('dig_width')) ? null : $this->input->post('dig_width');
		$product_type = constant('Regular');
		$user_id = $this->data['user_id'];

		if (!is_null($no_repeat)) {
			$vrepeat = null;
			$hrepeat = null;
		}

		$outdoor = (is_null($outdoor) ? 'N' : 'Y');
		$in_master = ($in_master == 'on');

		$data = array(
		  'name' => $product_name,
		  'width' => $width,
		  'vrepeat' => $vrepeat,
		  'hrepeat' => $hrepeat,
		  'outdoor' => $outdoor,
		  'in_master' => ($in_master ? 1 : 0),
		  'dig_product_name' => $dig_product_name,
		  'dig_width' => $dig_width,
		  'user_id' => $user_id,
          'lightfastness' => $lightfastness
		);

		if ($this->isNew) {
			
			$this->data['product_id'] = $this->model->save_product($product_type, $data);
		} else if ($this->input->post('change_product') === '1' || $this->input->post('change_showcase') === '1') { // Save only if a modification was logged via JS
			// Editing
			$this->model->save_product($product_type, $data, $this->data['product_id']);
			//echo $this->db->last_query();exit;
			/* OLD: because Products dont have a status

			// Is there any combined product that requires data modification?
			//$related_products = $this->model->get_related_products($this->data['product_id']);
			// Update related products
			if( count($related_products) > 0 ){
				$copyret = array(
					'stock_status_id' => $stock_status,
					'user_id' => $user_id
				);
				foreach($related_products as $r){
					//$this->model->save_product($copyret, $r['product_id']);
				}
			}
			*/
		}
		/*
			Save specs!
		*/
		$product_id = $this->data['product_id'];

		if ($this->input->post('change_cleaning') === '1') { // Save only if a modification was logged via JS
			$marked = false;
			// Cleaning (many)
			$arr = $this->input->post('cleaning');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			$copyret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'cleaning_id' => $specid,
				  'user_id' => $this->data['user_id']
				));

				if ($specid === "20") {
					$marked = true;
					// Special Instruction
					$aux = array(
					  'product_id' => $product_id,
					  'special_instruction' => $this->input->post("special_cleaning_instr"),
					  'user_id' => $this->data['user_id']
					);
					$this->model->save_cleaning_special($aux, $product_id);
				}
			}
			if (!$marked) {
				$this->model->clean_logics($product_id, $this->model->t_product_cleaning_specials);
			}
			$this->model->save_cleaning($ret, $product_id);
		}

		if ($this->input->post('change_cleaning_instructions') == '1') {
			// Cleaning (many)
			$arr = $this->input->post('cleaning_instructions');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'cleaning_instructions_id' => $specid,
				  'user_id' => $this->data['user_id']
				));
			}
			$this->model->save_cleaning_instructions($ret, $product_id);
		}
		
		
		if ($this->input->post('change_warranty') == '1') {
			// Cleaning (many)
			$arr = $this->input->post('warranty');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
					'product_id' => $product_id,
					'warranty_id' => $specid,
					'user_id' => $this->data['user_id']
				));
			}
			$this->model->save_warranty($ret, $product_id);
		}
		
		if ($this->input->post('change_finish') === '1') { // Save only if a modification was logged via JS
			$marked = false;
			// Finish (many)
			$arr = $this->input->post('finish');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'finish_id' => $specid,
				  'user_id' => $this->data['user_id']
				));

				if ($specid === "7") {
					$marked = true;
					// Special Instruction
					$aux = array(
					  'product_id' => $product_id,
					  'special_instruction' => $this->input->post("special_finish_instr"),
					  'user_id' => $this->data['user_id']
					);
					$this->model->save_finish_special($aux, $product_id);
				}
			}
			if (!$marked) {
				$this->model->clean_logics($product_id, $this->model->t_product_finish_specials);
			}
			$this->model->save_finish($ret, $product_id);
		}

		if ($this->input->post('change_origin') === '1') { // Save only if a modification was logged via JS
			// Origin (1) *required
			$ret = array(
			  'product_id' => $product_id,
			  'origin_id' => $this->input->post('origin'),
			  'user_id' => $this->data['user_id']
			);
			$this->model->save_origin($ret, $product_id);
		}

		if ($this->input->post('change_costs') === '1') { // Save only if a modification was logged via JS
			// Pricing (1)
			$ret = array(
			  'product_id' => $product_id,
			  'fob' => $this->input->post('fob'),
			  'cost_cut_type_id' => $this->input->post('cost_cut_type_id'),
			  'cost_cut' => empty($this->input->post('cost_cut')) ? null : $this->input->post('cost_cut'),
			  'cost_half_roll_type_id' => $this->input->post('cost_half_roll_type_id'),
			  'cost_half_roll' => empty($this->input->post('cost_half_roll')) ? null : $this->input->post('cost_half_roll'),
			  'cost_roll_type_id' => $this->input->post('cost_roll_type_id'),
			  'cost_roll' => empty($this->input->post('cost_roll')) ? null : $this->input->post('cost_roll'),
			  'cost_roll_landed_type_id' => $this->input->post('cost_roll_landed_type_id'),
			  'cost_roll_landed' => empty($this->input->post('cost_roll_landed')) ? null : $this->input->post('cost_roll_landed'),
			  'cost_roll_ex_mill_type_id' => $this->input->post('cost_roll_ex_mill_type_id'),
			  'cost_roll_ex_mill' => empty($this->input->post('cost_roll_ex_mill')) ? null : $this->input->post('cost_roll_ex_mill'),
			  'user_id' => $user_id
			);
			$this->model->save_cost($ret, $product_id);
		}

		if ($this->input->post('change_prices') === '1') { // Save only if a modification was logged via JS
 

			// Special cases

			// 1- If the digital print price changes we must update all related digital products
			// a) Direct relation by digital product
//			$dig_res_to_update = $this->db
//			  ->query("
//						SELECT PD.id
//
//						FROM T_PRODUCT_X_DIGITAL PD
//						JOIN T_ITEM I ON PD.item_id = I.id
//						LEFT OUTER JOIN T_PRODUCT_PRICE PPI ON I.product_id = PPI.product_id AND PPI.product_type = ?
//						LEFT OUTER JOIN T_PRODUCT_PRICE PPD ON PD.id = PPD.product_id AND PPD.product_type = ?
//
//						WHERE PD.item_id IN (
//							SELECT I.id
//							FROM T_ITEM I
//							WHERE I.product_id = ?
//						)
//						AND (PPD.p_res_cut = PPI.p_dig_res OR PPD.p_res_cut IS NULL)
//					", array(constant('Regular'), constant('Digital'), $product_id))
//			  ->result_array();
//			//var_dump($dig_res_to_update);exit;
//			$IDS_dig_res_to_update = array_column($dig_res_to_update, 'id');
//			if (count($IDS_dig_res_to_update) > 0) {
//				$this->db
//				  ->set('p_res_cut', $this->input->post('p_dig_res'))
//				  ->where("product_type", constant('Digital'))
//				  ->where_in("product_id", $IDS_dig_res_to_update)
//				  ->update($this->model->t_product_price);
//			}
//
//			//
//			$dig_hosp_to_update = $this->db
//			  ->query("
//						SELECT PD.id
//
//						FROM T_PRODUCT_X_DIGITAL PD
//						JOIN T_ITEM I ON PD.item_id = I.id
//						LEFT OUTER JOIN T_PRODUCT_PRICE PPI ON I.product_id = PPI.product_id AND PPI.product_type = ?
//						LEFT OUTER JOIN T_PRODUCT_PRICE PPD ON PD.id = PPD.product_id AND PPD.product_type = ?
//
//						WHERE PD.item_id IN (
//							SELECT I.id
//										FROM T_ITEM I
//										WHERE I.product_id = ?
//						)
//						AND (PPD.p_hosp_roll = PPI.p_dig_hosp OR PPD.p_hosp_roll IS NULL OR  PPD.p_hosp_roll = '0.00')
//					", array(constant('Regular'), constant('Digital'), $product_id))
//			  ->result_array();
//			$IDS_dig_hosp_to_update = array_column($dig_hosp_to_update, 'id');
//			if (count($IDS_dig_hosp_to_update) > 0) {
//				$this->db
//				  ->set('p_hosp_roll', $this->input->post('p_dig_hosp'))
//				  ->where("product_type", constant('Digital'))
//				  ->where_in("product_id", $IDS_dig_hosp_to_update)
//				  ->update($this->model->t_product_price);
//			}

			// Pricing (1)


			$ret = array(
			  'product_id' => $product_id,
			  'product_type' => $product_type,
			  'p_hosp_cut' => empty($this->input->post('p_hosp_cut')) ? null : $this->input->post('p_hosp_cut'),
			  'p_hosp_roll' => empty($this->input->post('p_hosp_roll')) ? null : $this->input->post('p_hosp_roll'),
			  'p_res_cut' => empty($this->input->post('p_res_cut')) ? null : $this->input->post('p_res_cut'),
			  'p_dig_res' => empty($this->input->post('p_dig_res')) ? null : $this->input->post('p_dig_res'),
			  'p_dig_hosp' => empty($this->input->post('p_dig_hosp')) ? null : $this->input->post('p_dig_hosp'),
			  'user_id' => $user_id
			);
			// print_r($ret);
			// die;
			$this->model->save_price($ret, $product_id, $this->data['product_type']);
		}

		/*
			  if( $this->input->post('change_shelf') === '1' ){ // Save only if a modification was logged via JS
					  // Uses (many)
					  $arr = $this->input->post('shelf');
					  $arr = ( is_null($arr) ? array() : $arr );
					  $ret = array();
					  foreach($arr as $specid){
						  array_push($ret, array(
							  'product_id' => $product_id,
							  'product_type' => $this->data['product_type'],
							  'shelf_id' => $specid,
							  'user_id' => $this->data['user_id']
						  ));
					  }
					  $this->model->save_shelf($ret, $product_id);
			  }
		*/

		if ($this->input->post('change_uses') === '1') { // Save only if a modification was logged via JS
			// Uses (many)
			$arr = $this->input->post('uses');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'use_id' => $specid,
				  'user_id' => $this->data['user_id']
				));
			}
			$this->model->save_uses($ret, $product_id);
		}

		if ($this->input->post('change_various') === '1') { // Save only if a modification was logged via JS
			// Various (1)
			$railroaded = $this->input->post('railroaded');
			if (!is_null($railroaded)) {
				$railroaded = 'Y';
			} else {
				$railroaded = 'N';
			}
			// Needs to be fixed using NULL values

			// Special cases

			// 1- If Min_Order_qty changes we must change it in all related item with same value
			$items_to_update = $this->db
			  ->query("
							SELECT T_ITEM.id
							FROM T_ITEM
							LEFT OUTER JOIN T_PRODUCT_VARIOUS ON T_ITEM.product_id = T_PRODUCT_VARIOUS.product_id
							WHERE T_ITEM.product_id = ?
							AND (T_ITEM.min_order_qty = '' OR T_ITEM.min_order_qty = T_PRODUCT_VARIOUS.min_order_qty)", array($product_id))
			  ->result_array();
			$batch = array();
			$new_value = $this->input->post('min_order_qty');
			if (count($items_to_update) > 0) {
				foreach ($items_to_update as $item) {
					$aux = array(
					  'id' => $item['id'],
					  'min_order_qty' => $new_value
					);
					array_push($batch, $aux);
				}

				$this->db->update_batch($this->model->t_item, $batch, 'id');
			}
			$ret = array(
			  'product_id' => $product_id,
			  'vendor_product_name' => $this->input->post('vendor_product_name'),
			  'yards_per_roll' => $this->input->post('yards_per_roll'),
			  'lead_time' => $this->input->post('lead_time'),
			  'min_order_qty' => $this->input->post('min_order_qty'),
			  'tariff_code' => $this->input->post('tariff_code'),
			  'tariff_surcharge' => $this->input->post('tariff_surcharge'),
              'duty_perc' => $this->input->post('duty_perc'),
              'freight_surcharge' => $this->input->post('freight_surcharge'),
              'vendor_notes' => $this->input->post('vendor_notes'),
			  'railroaded' => $railroaded,
			  'prop_65' => $this->input->post('prop_65'),
			  'ab_2998_compliant' => $this->input->post('ab_2998_compliant'),
			  'weight_n' => $this->input->post('weight_n'),
			  'weight_unit_id' => $this->input->post('weight_unit'),
			  'user_id' => $this->data['user_id']
			);
			$this->model->save_various($ret, $product_id);
		}

		if ($this->input->post('change_vendor') === '1') { // Save only if a modification was logged via JS
			// Vendor (1)
			$ret = array(
			  'product_id' => $product_id,
			  'vendor_id' => $this->input->post('vendor'),
			  'user_id' => $this->data['user_id']
			);
			$this->model->save_vendor($ret, $product_id);
		}

		if ($this->input->post('change_weave') === '1') { // Save only if a modification was logged via JS
			// Weave (many)
			$arr = $this->input->post('weave');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'weave_id' => $specid,
				  'user_id' => $this->data['user_id']
				));
			}
			$this->model->save_weave($ret, $product_id);
		}

		if ($this->input->post('change_content_f_encoded') === '1') { // Save only if a modification was logged via JS
			// Content Front
			$arr = $this->input->post('content_f_encoded');
			$arr = (is_null($arr) ? array() : json_decode($arr));
			$ret = array();
			foreach ($arr as $i) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'perc' => $i->perc,
				  'content_id' => $i->id,
				  'user_id' => $this->data['user_id']
				));
			}
			$this->model->save_content_front($ret, $product_id);
		}

		if ($this->input->post('change_content_b_encoded') === '1') { // Save only if a modification was logged via JS
			// Content Back
			$arr = $this->input->post('content_b_encoded');
			$arr = (is_null($arr) ? array() : json_decode($arr));
			$ret = array();
			foreach ($arr as $i) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'perc' => $i->perc,
				  'content_id' => $i->id,
				  'user_id' => $this->data['user_id']
				));
			}
			$this->model->save_content_back($ret, $product_id);
		}

		if ($this->input->post('change_abrasion_encoded') === '1') { // Save only if a modification was logged via JS

			// Abrasion
			$arr = $this->input->post('abrasion_encoded');
			$arr = (is_null($arr) ? array() : json_decode($arr));

			// Specs will be all re processed again
			// Delete existing ones, and reenter the modifications the user has done
			// First clean files!!
			$this->model->clean_abrasion_files_logic($product_id);
			$this->model->clean_abrasion_logic($product_id);
			foreach ($arr as $i) { // Loop through each of the abrasion entered by user

				$ret = array(
				  'product_id' => $product_id,
				  'n_rubs' => $i->rubs,
				  'abrasion_test_id' => $i->abrasion_test_id,
				  'abrasion_limit_id' => $i->abrasion_limit_id,
				  'visible' => $i->visible,
				  'data_in_vendor_specsheet' => $i->data_in_vendor_specsheet,
				  'user_id' => $this->data['user_id']
				);

				if (strpos($i->id, 'new-') === FALSE) {
					// Not 'new' in the id. Its an existing one, to reinsert with the same abrasion_id
					$ret['id'] = $i->id;
					$this->model->save_abrasion($ret);
					$new_spec_id = $i->id;
				} else {
					// New firecode
					$this->model->save_abrasion($ret);
					$new_spec_id = $this->db->insert_id();
				}

				if (count($i->files) > 0) { // It at least one file needs to be entered
					$ret = array();
					$n = 0;
					foreach ($i->files as $f) { // Each $f is a URL to the document
						$n++;
						if (strpos($f, 'temp')) {
							// Is a new file!
							$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever)
							$filename = $product_id . '-' . $new_spec_id . '-' . url_title($i->abrasion_limit_name . '-' . $i->rubs . '-' . $i->abrasion_test_name, '-', true) . '-' . $n . '.' . end($extension);
                            //echo "<pre> CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " </pre>";
							//print_r($filename);
							//echo "<pre>";

							$location_request = [
							  'status' => 'local_save',
							  'product_type' => $product_type,
							  'product_id' => $product_id,
							  'img_type' => 'abrasions',
							  'include_filename' => false,
							  'file_format' => end($extension)
							];

							$new_location = $this->file_directory->image_src_path($location_request) . $filename;

							// Save new location
							rename(str_replace(site_url(), '', $f), $new_location);
						} else {
							// Existing file, don't rename the file location
							$new_location = str_replace(site_url(), '', $f);
						}

						array_push($ret, array(
						  'abrasion_id' => $new_spec_id,
						  'url_dir' => $new_location,
						  'user_id' => $user_id
						));

					} // end foreach file
					$this->model->save_abrasion_files($ret);
				}

			} // end foreach abrasion entered

		}

		if ($this->input->post('change_firecode_encoded') === '1') { // Save only if a modification was logged via JS
			// Firecode
			$arr = $this->input->post('firecode_encoded');
			$arr = (is_null($arr) ? array() : json_decode($arr));

			// Specs will be all re processed again
			// Delete existing ones, and reenter the modifications the user has done
			// First, clean files!!
			$this->model->clean_firecodes_files_logic($product_id);
			$this->model->clean_firecodes_logic($product_id);
			foreach ($arr as $i) { // Loop through each of the firecodes entered by user

				$ret = array(
				  'product_id' => $product_id,
				  'firecode_test_id' => $i->firecode_test_id,
				  'visible' => $i->visible,
				  'data_in_vendor_specsheet' => $i->data_in_vendor_specsheet,
				  'user_id' => $this->data['user_id']
				);

				if (strpos($i->id, 'new-') === FALSE) {
					// Not 'new' in the id. Its an existing one, to reinsert with the same firecode_id
					$ret['id'] = $i->id;
					$this->model->save_firecode($ret);
					$new_spec_id = $i->id;
				} else {
					// New firecode
					$this->model->save_firecode($ret);
					$new_spec_id = $this->db->insert_id();
				}

				if (count($i->files) > 0) { // It at least one file needs to be entered
					$ret = array();
					$n = 0;
					foreach ($i->files as $f) {
						$n++;
						if (strpos($f, 'temp')) {
							// Is a new file!
							$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever)
							$filename = $product_id . '-' . $new_spec_id . '-' . url_title($i->firecode_test_name, '-', true) . '-' . $n . '.' . end($extension);
                            //echo "<pre> CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " </pre>";
							//print_r($filename);
							//echo "<pre>";
							$location_request = [
							  'status' => 'local_save',
							  'product_type' => $product_type,
							  'product_id' => $product_id,
							  'img_type' => 'firecodes',
							  'include_filename' => false,
							  'file_format' => end($extension)
							];

							$new_location = $this->file_directory->image_src_path($location_request) . $filename;

							// Save new location
							rename(str_replace(site_url(), '', $f), $new_location);
						} else {
							// Existing file, don't relocate the file
							$new_location = str_replace(site_url(), '', $f);
						}

						array_push($ret, array(
						  'firecode_id' => $new_spec_id,
						  'url_dir' => $new_location,
						  'user_id' => $user_id
						));

					} // end foreach file
					$this->model->save_firecode_files($ret);
				}

			} // end foreach firecode entered
		}

		if ($this->input->post('change_files_encoded') === '1') { // Save only if a modification was logged via JS
			// Product filling
			$arr = $this->input->post('files_encoded');
			$arr = (is_null($arr) ? array() : json_decode($arr));
			$batch = array();
			$n = 0;

			$this->model->clean_product_files_logic($product_id);
			foreach ($arr as $i) { // Loop through each of the files
				$n++;
				if ($i->category_id === $this->category_files_ids['memotags_picture']) {

					$f = $i->url_dir; // $f becomes the Url
					if (strpos($f, 'temp')) {
						$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever)
						$filename = $product_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . end($extension);
						// echo "<pre> CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " </pre>";
						// print_r($filename);
						// echo "<pre>";
						$location_request = [
						  'status' => 'local_save',
						  'product_type' => $product_type,
						  'product_id' => $product_id,
						  'img_type' => 'memotags_picture',
						  'include_filename' => false,
						  'file_format' => end($extension)
						];

						$_new_location = $this->file_directory->image_src_path($location_request);
						$new_location = $_new_location . $filename;

						// Is a new file!
						while (file_exists($new_location)) {
							$n++;
							$filename = $product_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . end($extension);
							$new_location = $_new_location . $filename;
						}
						// Save new location
						rename(str_replace(site_url(), '', $f), $new_location);
					} else {
						// Existing file, don't relocate the file, get just the url address
						$new_location = str_replace(site_url(), '', $f);
					}

					if ($i->category_id === $this->category_files_ids['other']) {
						// Has a name given by user
						$descr = $i->category_name;
					} else {
						$descr = null;
					}

					$ret = array(
					  'product_id' => $product_id,
					  'product_type' => $this->data['product_type'],
					  'category_id' => $i->category_id,
					  'url_dir' => $new_location,
					  'descr' => $descr,
					  'date_add' => date('Y-m-d', strtotime(str_replace('-', '/', $i->date))),
					  'user_id' => $user_id
					);
					// Here we override the last memotag entered
					$last_memotag_data = $ret;
				} else {
					// Regular files

					$f = $i->url_dir; // $f becomes the Url
					if (strpos($f, 'temp')) {
						$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever)
						$filename = $product_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . end($extension);
						// echo "<pre> CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " </pre>";
						// print_r($filename);
						// echo "<pre>";
						$location_request = [
						  'status' => 'local_save',
						  'product_type' => $product_type,
						  'product_id' => $product_id,
						  'img_type' => 'files',
						  'include_filename' => false,
						  'file_format' => end($extension)
						];

						$_new_location = $this->file_directory->image_src_path($location_request);
						$new_location = $_new_location . $filename;

						// Is a new file!
						while (file_exists($new_location)) {
							$n++;
							$filename = $product_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . end($extension);
							$new_location = $_new_location . $filename;
						}
						// Save new location
						rename(str_replace(site_url(), '', $f), $new_location);
					} else {
						// Existing file, don't relocate the file
						$new_location = str_replace(site_url(), '', $f);
					}

					if ($i->category_id === $this->category_files_ids['other']) {
						// Has a name given by user
						$descr = $i->category_name;
					} else {
						$descr = null;
					}

					$ret = array(
					  'product_id' => $product_id,
					  'product_type' => $this->data['product_type'],
					  'category_id' => $i->category_id,
					  'url_dir' => $new_location,
					  'descr' => $descr,
					  'date_add' => date('Y-m-d', strtotime(str_replace('-', '/', $i->date))),
					  'user_id' => $user_id
					);
					array_push($batch, $ret);
				}
			}
			if (isset($last_memotag_data)) array_push($batch, $last_memotag_data);
			//var_dump($batch); exit;
			if (count($batch) > 0) $this->model->save_product_files($batch, $product_id);
		}

		if ($this->input->post('change_product_messages_encoded') === '1') { // Save only if a modification was logged via JS
			// Firecode
			$arr = $this->input->post('product_messages_encoded');
			$arr = (is_null($arr) ? array() : json_decode($arr));

			foreach ($arr as $i) { // Loop through each

				$ret = array(
				  'product_id' => $product_id,
				  'product_type' => $this->data['product_type'],
				  'message' => $i->message_note,
				  'user_id' => $this->data['user_id']
				);

				if (strpos($i->id, 'new-') === FALSE) {
					// Not 'new' in the id. Its an existing one, to reinsert with the same firecode_id
					$this->model->save_message($ret, $i->id);
				} else {
					// New
					$this->model->save_message($ret);
				}

			} // end foreach
		}

		if ($this->input->post('change_showcase') === '1') { // Save only if a modification was logged via JS

			$f = $this->input->post('pic_big_url');
			if (strpos($f, 'temp')) {
				// Is a new file!
				$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever extension is)
				//$extension = array_reverse($extension);
				$location_request = [
				  'status' => 'save',
				  'product_type' => $product_type,
				  'product_id' => $product_id,
				  'img_type' => 'beauty_shot',
				  'include_filename' => true,
				  'file_format' => end($extension)
				];
				$new_location = $this->file_directory->image_src_path($location_request);
				//echo "<pre> CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
				//print_r($extension); echo "<br>";
				//print_r($location_request); echo "<br>";
				//print_r($new_location); echo "<br>";
				//echo "<pre>";

				if (file_exists($new_location)) {
					// Move existing file
					$_destination_for_replacement = str_replace('.' . end($extension), '-' . url_title(date("Y-m-d h-i-sa")) . '.' . end($extension), $new_location);

					//echo "<pre> _destination_for_replacement CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
					//print_r($_destination_for_replacement); echo "<br>";
					////print_r($new_location); echo "<br>";
					//echo "<pre>";

					rename($new_location, $_destination_for_replacement);
				}
			$location_request['status'] = 'load';
			$new_location_db = $this->file_directory->image_src_path($location_request);

			
			$tmp_file_pic_big = $this->input->post('pic_big_url');
			/*  ------------------------
			 Create S3 objects from uploads with the new fileuploadtos3 class
			 fileuploadtos3::SendUploadedTempFileToS3($tmp_file, $new_location);
			 Both Params should be web relative paths
			 ------------------------ */
			 $new_location    = str_replace($_SERVER['DOCUMENT_ROOT'],'',$new_location);
			 $this->fileuploadtos3->SendUploadedTempFileToS3($tmp_file_pic_big, $new_location); 
			 // echo "<pre> " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
			 // echo '$tmp_file_pic_big: '. $tmp_file_pic_big ."<br>";
			 // echo '$new_location: '. $new_location ."<br>";
			 // echo "<pre>";

			// Legacy Save current uploaded file
			// rename(str_replace(site_url(), '', $f), $new_location);

			// PKL Convert the new file location ($new_location_db) to S3 URL for Database insertion
			$S3_location = $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_db);
			$new_location_db = $S3_location;

		} else {
			// Existing file, don't relocate the file
			$new_location_db = $f;
			// PKL convertLegacyImgSrcToS3() will not convert if $new_location_db 
			// is already an S3 Asset URL
			$S3_location = $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_db);
			$new_location_db = $S3_location;
		}

		// ============================================================================
		// BEAUTY SHOT DEPENDENCY: Web visibility requires beauty shot
		// ============================================================================
		$showcase_visible = $this->input->post('showcase_visible');
		// Handle unchecked checkbox (not submitted) - default to '0'
		if (is_null($showcase_visible)) {
			$showcase_visible = '0';
		}
		$has_beauty_shot = !is_null($this->input->post('pic_big_delete')) || strlen($new_location_db) > 0;
		
		// Force web visibility to FALSE if no beauty shot exists
		if (!$has_beauty_shot) {
			$showcase_visible = '0'; // This will result in 'N' in the array below
		}
		
		$ret = array(
		  'product_id' => $product_id,
		  'product_type' => $this->data['product_type'],
		  'url_title' => $url_title,
		  'descr' => $this->input->post('showcase_descr'),
		  'visible' => ($showcase_visible === '1' ? 'Y' : 'N'),
		  'pic_big_url' => (is_null($this->input->post('pic_big_delete')) && strlen($new_location_db) > 0 ? $new_location_db : null),
		  'user_id' => $user_id
		);
			if (!is_null($this->input->post('pic_big_delete'))) {
				$ret['pic_big'] = null;
			}
		$this->model->save_showcase_basic($ret, $product_id, $this->data['product_type']);

			// ============================================================================
			// UPDATE CHILD ITEMS WEB VISIBILITY WHEN PARENT PRODUCT CHANGES
			// ============================================================================
			$this->update_child_items_web_visibility($product_id);

			// Collection
			$arr = $this->input->post('showcase_collection');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'collection_id' => $specid
				));
			}
			$this->model->save_showcase_collection($ret, $product_id);

			// Contents Web
			$arr = $this->input->post('showcase_contents_web');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'content_web_id' => $specid
				));
			}
			$this->model->save_showcase_contents_web($ret, $product_id);

			// Pattern
			$arr = $this->input->post('showcase_patterns');
			$arr = (is_null($arr) ? array() : $arr);
			$ret = array();
			foreach ($arr as $specid) {
				array_push($ret, array(
				  'product_id' => $product_id,
				  'pattern_id' => $specid
				));
			}
			$this->model->save_showcase_patterns($ret, $product_id);

		} else if ($this->input->post('change_product') === '1' && !is_null($this->input->post('showcase_visible'))) {
			// In case where the product name changed but no showcase change was made
			$data = array(
			  'url_title' => $url_title
			);
			$this->model->save_showcase_basic($data, $this->data['product_id'], $this->data['product_type']);
// 				$this->db
// 					->set('url_title', $url_title)
// 					->where("product_id", $this->data['product_id'])
// 					->update($this->model->t_showcase_product);
		}

		// Refresh cache for this product since data may have changed
		if (isset($product_id) && !empty($product_id)) {
			$this->model->refresh_cached_product_row($product_id, $product_type);
		}

	} // End Function Save Regular Product

	function save_combined_product()
	{
		//var_dump($_POST); exit;
		$errors = array();

		$this->db->trans_begin();

		$style_id = $this->input->post('style');
		$ground_id = $this->input->post('ground');
		$reverse_ground = $this->input->post('reverse_ground');
		$reverse_ground = strlen($reverse_ground) === 0 ? 'N' : $reverse_ground;
		$in_master = $this->input->post("in_master");
//		$product_status = $this->input->post('product_status');
//		$stock_status = $this->input->post('stock_status');
		$user_id = $this->session->userdata('user_id');

		$in_master = ($in_master === 'on');

		if ($this->isNew) {
			// New Product
			if ($this->model->valid_combination($this->data['product_type'], $style_id, $ground_id, $reverse_ground)) {
//				var_dump(true); exit;
//				$product_name = $this->model->get_style_name($this->data['product_type'], $style_id) . ' on ' . $this->model->get_product_name_by_item_id($ground_id);

				$data = array(
//				  'url_title' => url_title( strtolower($product_name) ),
				  'item_id' => $ground_id,
				  'reverse_ground' => $reverse_ground,
				  'style_id' => $style_id,
					//'name' => $product_name,
				  'user_id' => $user_id
				);

				$this->data['product_id'] = $this->model->save_product($this->data['product_type'], $data);
			} else {
				$e = "Product combination already exists.";
				array_push($errors, $e);
			}
		} else if ($this->input->post('change_product') === '1') { // Save only if a modification was logged via JS
			$data = [
			  'in_master' => ($in_master ? 1 : 0)
			];
			$this->model->save_product($this->data['product_type'], $data, $this->data['product_id']);
		}

		/*
			Save specs!
		*/

		if (empty($errors)) {

			$product_id = $this->data['product_id'];

			/*
				  if( $this->input->post('change_shelf') === '1' ){ // Save only if a modification was logged via JS
						  $arr = $this->input->post('shelf');
						  $arr = ( is_null($arr) ? array() : $arr );
						  $ret = array();
						  foreach($arr as $specid){
							  array_push($ret, array(
								  'product_id' => $product_id,
								  'product_type' => $this->data['product_type'],
								  'shelf_id' => $specid
							  ));
						  }
						  $this->model->save_shelf($ret, $product_id);
				  }
			*/

			if ($this->input->post('change_prices') === '1') { // Save only if a modification was logged via JS

				/* NOT IN USED SINCE 04-18

				// Special Cases
				/*
				// 1- Update the price of the digital items present in a list
				$to_update = $this->db
					->query("
						SELECT LI.*
						FROM Q_LIST_ITEMS LI, T_ITEM I, T_PRODUCT_PRICE PP

						WHERE LI.item_id = I.id
						AND I.product_type = 'D'
						AND I.product_id = ?
						AND PP.product_id = I.product_id
						AND LI.p_res_cut = PP.p_res_cut
						", array($product_id))->result_array()
					;
				if( count($to_update) > 0 ){
					foreach($to_update as $item){
						$this->db
							->set('p_res_cut', $this->input->post('p_res_cut') )
							->where("list_id", $item['list_id'])
							->where("item_id", $item['item_id'])
							->update($this->model->p_list_items)
							;
					}
				}

				$to_update = $this->db
					->query("
						SELECT LI.*
						FROM Q_LIST_ITEMS LI, T_ITEM I, T_PRODUCT_PRICE PP

						WHERE LI.item_id = I.id
						AND I.product_type = 'D'
						AND I.product_id = ?
						AND PP.product_id = I.product_id
						AND LI.p_hosp_cut = PP.p_hosp_cut
						", array($product_id))->result_array()
					;
				if( count($to_update) > 0 ){
					foreach($to_update as $item){
						$this->db
							->set('p_hosp_cut', $this->input->post('p_hosp_cut') )
							->where("list_id", $item['list_id'])
							->where("item_id", $item['item_id'])
							->update($this->model->p_list_items)
							;
					}
				}
				*/

				// Pricing (1)
				$ret = array(
				  'product_id' => $product_id,
				  'product_type' => $this->data['product_type'],
				  'p_hosp_cut' => empty($this->input->post('p_hosp_cut')) ? null : $this->input->post('p_hosp_cut'),
				  'p_hosp_roll' => empty($this->input->post('p_hosp_roll')) ? null : $this->input->post('p_hosp_roll'),
				  'p_res_cut' => empty($this->input->post('p_res_cut')) ? null : $this->input->post('p_res_cut'),
				  'p_dig_res' => empty($this->input->post('p_dig_res')) ? null : $this->input->post('p_dig_res'),
				  'p_dig_hosp' => empty($this->input->post('p_dig_hosp')) ? null : $this->input->post('p_dig_hosp'),
				  'user_id' => $user_id
				);
				$this->model->save_price($ret, $product_id, $this->data['product_type']);
			}

		}
		$this->errors = $errors;
		
		// Refresh cache for this product since data may have changed
		if (empty($errors) && isset($product_id) && !empty($product_id)) {
			$this->model->refresh_cached_product_row($product_id, $this->data['product_type']);
		}
		
	} // End Function Save Combined Product

	public function archive_product()
	{
		$product_id = $this->input->post('product_id');
		$product_type = $this->input->post('product_type');
		$is_valid = in_array($product_type, array(Regular, Digital, ScreenPrint));
		if ($this->input->is_ajax_request() && $is_valid) {
			$result = $this->model->archive_product($product_id, $product_type);
			$ret = array(
			  'id' => $product_id,
			  'product_type' => $product_type,
			  'continueUrl' => site_url()
			);
			echo json_encode($ret);
		}
	}

	public function retrieve_product()
	{
		$product_id = $this->input->post('product_id');
		$product_type = $this->input->post('product_type');
		$is_valid = in_array($product_type, array(Regular, Digital, ScreenPrint));
		if ($this->input->is_ajax_request() && $is_valid) {
			$result = $this->model->retrieve_product($product_id, $product_type);
			$ret = array(
			  'id' => $product_id,
			  'product_type' => $product_type,
			  'continueUrl' => site_url('item/index/' . $product_id . '-' . $product_type)
			);
			echo json_encode($ret);
		}
	}

	public function checklist()
	{
		$this->load->library('table');
		$this->load->model('Specs_model', 'specs');

		$product_id = $this->input->post('product_id');
		$product_type = $this->input->post('product_type');
		$this->data['product_name'] = $this->model->get_product_name_by_id($product_id, $product_type);

		// Retrieve current state of checklist
		$this->data['product_tasks'] = $this->model->get_product_tasks_all($product_id, $product_type);
//		var_dump($this->data['product_tasks']); return;
		$table_data = [];
//		$table_head = "<thead><th>" . implode("</th><th>", ['', 'Task', 'Who/When', 'Notes', '']) . "</th></thead>";

		$possible_authors = [0=>'P0', 1=>'P1', 2=>'P2', 3=>'P3', 4=>'P4', 5=>'P5', 6=>'P6'];

		$sales_admin_id = 9;
		$l = $this->specs->get_users_name_by_group_id([$sales_admin_id]);
		$options = $this->decode_array($l, 'id', 'first_name');

		foreach($this->data['product_tasks'] as $task){
			$task_id = $task['id'];
			$who_selected = explode(',', $task['task_who']);
			$html_date = date_mysql2php($task['task_when']);
			$completed = $html_date !== '';
			$row = [
//			    "<span id='status_icon_".$task_id."' class='".($completed?'':'hide')."'><i class='fal fa-check-circle'></i></span><span id='status_".$task_id."' class='".($completed?'task-completed':'')."'>".$task['n_order']."</span>",
			    "<span id='status_icon_".$task_id."' class='task-completed ".($completed?'':'hide')."'><i class='fas fa-check-circle'></i></span>",
			    $task['n_order'].') '.$task['descr'],
			    form_multiselect("task_who_".$task_id."[]", $options, $who_selected, " class='multi-dropdown' tabindex='-1' placeholder='' onchange='task_change_event($task_id)' "),
				form_input(['id'=>"task_date_$task_id", 'name'=>"task_date_$task_id", 'type'=>'date', 'class'=>'form-control', 'value'=>$html_date, 'style'=>'width:100%;', 'onchange'=>"task_change_event($task_id)"]),
			    form_textarea(['id'=>"task_notes_$task_id", 'name'=>"task_notes_$task_id", 'cols'=>30, 'rows'=>2, 'value'=>$task['task_notes'], 'class'=>'form-control', 'onchange'=>"task_change_event($task_id)"]),
				"<a href='#' data-url='".site_url('task/update/'.$task_id)."' data-id='".$task_id."' class='btn no-border btn-outline-outline btnUpdateTask'>Update</a>"
			];
			$table_data[] = "<tr name='task_row_".$task_id."'><td>" . implode("</td><td>", $row) . "</td></tr>";
		}
		$this->data['table_body_rows'] = implode('', $table_data);
//		$this->data['table_html'] = "<table id='table-checklist' class='table'>" . $table_head . "<tbody>" . implode('', $table_data) . "</tbody></table>";

		$ret = [
		  'html' => $this->load->view('product/form/form_product_checklist', $this->data, true)
		];
		echo json_encode($ret);
	}

	public function task_update($task_id){
		// Save changes
		$task_data = [
		  'product_id' => $this->input->post('product_id'),
		  'product_type' => $this->input->post('product_type'),
		  'task_id' => $this->input->post('task_id'),
		  'task_who' => implode(',', set_value('task_who', [])),
		  'task_when' => date_php2mysql($this->input->post('task_when')),
		  'task_notes' => $this->input->post('task_notes'),
		  'user_id' => $this->data['user_id']
		];
//		var_dump($task_data); var_dump($this->input->post('date_when')); return;
		$this->model->update_task($task_data);

		$ret = [
		  'task_id' => $task_data['task_id'],
		  'completed' => !is_null($task_data['task_when'])
		];

		echo json_encode($ret);
	}

	/*


	*/

	public function typeahead_products_list()
	{
		$q = $this->input->post('query');
		$itemsOnly = $this->input->post('itemsOnly');
		$includeDigital = $this->input->post('includeDigital');
//		var_dump($_POST);
		$is_valid_request = strlen(trim($q)) > 0;
		$ret = [];
		if ($this->input->is_ajax_request() && $is_valid_request) {
			$query = $this->model->search_by_name($q);
			$ret = [];
			$pids = [];

			$subtitle = array(
			  Regular => ' - Fabric colorline',
			  Digital => ' - Digital colorline'
			);

			foreach ($query as $row) {
				if(!$itemsOnly) {
					$iden = $row['product_id'] . '-' . $row['product_type'];
					if (!in_array($iden, $pids)) {
						$pids[] = $iden;
						$row['vendor_name'] = ($row['product_type'] == Digital ? 'Opuzen' : $row['vendor_name']);
						$ret[] = array(
							'description' => $row['vendor_name'] . $subtitle[$row['product_type']],
							'id' => $row['product_id'] . '-' . $row['product_type'],
							'label' => $row['product_name']
						);
					}
				}
				if(!$includeDigital){
					if ($row['product_type'] == Digital) continue;
				}
				

				// Items will be added (both regular and digital based on includeDigital parameter)
				$ret[] = array(
				  'description' => $row['vendor_name'] . '- SKU',
				  'id' => $row['item_id'] . '-item_id',
				  'label' => $row['product_name'] . ' / ' . ($row['code'] != '' ? $row['code'] . ' / ' : '') . $row['color']
				);
			}

// 			$fil = $this->input->post('filters');
// 			$filters = array(
// 				'includeRegular' => $fil["includeRegular"] === 'true',
// 				'includeDigital' => $fil["includeDigital"] === 'true',
// 				'includeSKU' => $fil["includeSKU"] === 'true',
// 			);
// 			$ret = $this->model->search_by_name($q, $filters);
// 			echo $this->db->last_query();
		}

//		$ret['debug'] = $this->db->last_query();
		echo json_encode($ret);
	}

	public function calculator()
	{
		$this->view('product/calculator');
	}

	/**
	 * Rebuild the entire cached_product_spec_view table
	 * This should be called after major data changes or when vendors_name search is not working
	 */
	public function rebuild_cache()
	{
		// Check if user has permission (you may want to add proper permission checking)
		if (!$this->hasPermission('product', 'edit')) {
			show_error('Access denied', 403);
			return;
		}

		try {
			// Rebuild the entire cache
			$result = $this->model->build_cached_product_spec_view();
			
			if ($result) {
				$message = "Cache rebuilt successfully. All product data including vendor names is now searchable.";
				log_message('info', 'Product cache rebuilt by user: ' . $this->session->userdata('user_id'));
			} else {
				$message = "Cache rebuild failed. Please check the logs.";
				log_message('error', 'Product cache rebuild failed');
			}
			
			// Return JSON response for AJAX calls or redirect for direct access
			if ($this->input->is_ajax_request()) {
				echo json_encode(['success' => (bool)$result, 'message' => $message]);
			} else {
				$this->session->set_flashdata('message', $message);
				redirect('product');
			}
		} catch (Exception $e) {
			log_message('error', 'Product cache rebuild error: ' . $e->getMessage());
			
			if ($this->input->is_ajax_request()) {
				echo json_encode(['success' => false, 'message' => 'Error rebuilding cache: ' . $e->getMessage()]);
			} else {
				$this->session->set_flashdata('error', 'Error rebuilding cache: ' . $e->getMessage());
				redirect('product');
			}
		}
	}

	// ============================================================================
	// WEB VISIBILITY UPDATE METHODS
	// ============================================================================
	
	/**
	 * Update child items web visibility when parent product visibility changes
	 * This ensures all child items are recalculated based on the new parent state
	 * 
	 * @param int $product_id The product ID to update child items for
	 */
	private function update_child_items_web_visibility($product_id)
	{
		// Get all child items for this product
		$this->db->select('i.id as item_id, i.product_id, i.status_id, s.name as status, sp.visible as parent_product_visibility');
		$this->db->from('T_ITEM i');
		$this->db->join('P_PRODUCT_STATUS s', 'i.status_id = s.id', 'left');
		$this->db->join('SHOWCASE_PRODUCT sp', 'i.product_id = sp.product_id', 'left');
		$this->db->where('i.product_id', $product_id);
		$this->db->where('i.archived', 'N');
		
		$items = $this->db->get()->result_array();
		
		if (empty($items)) {
			return;
		}
		
		$items_to_update = [];
		
		foreach ($items as $item) {
			// Calculate new web visibility based on current parent state
			$calculated_visibility = $this->calculate_web_visibility_for_item($item);
			
			$items_to_update[] = [
				'id' => $item['item_id'],
				'web_vis' => $calculated_visibility ? 1 : 0,
				'date_modif' => date('Y-m-d H:i:s')
			];
		}
		
		// Batch update all child items
		if (!empty($items_to_update)) {
			$this->db->update_batch('T_ITEM', $items_to_update, 'id');
			log_message('info', 'Updated ' . count($items_to_update) . ' child items web visibility for product_id: ' . $product_id);
		}
	}
	
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
		
		// Check manual override state (if web_vis_toggle column exists)
		$manual_override = false;
		if (isset($item['web_vis_toggle'])) {
			$manual_override = !empty($item['web_vis_toggle']) ? (bool)$item['web_vis_toggle'] : false;
		}
		
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

	/**
	 * Fix S3 URLs for products with broken paths
	 * Web-accessible method to fix existing database URLs
	 * 
	 * Usage: https://localhost:8443/product/fix_s3_urls
	 */
	public function fix_s3_urls()
	{
		// Admin only
		if (!$this->ion_auth->is_admin()) {
			show_error('Admin access required', 403);
			return;
		}
		
		$this->load->library('FileUploadToS3', null, 'fileuploadtos3');
		
		echo "<h2>S3 URL Fix Utility</h2>";
		echo "<pre>";
		
		// Get all products with pic_big_url
		$query = $this->db->select('product_id, product_type, pic_big_url, url_title')
			->from('SHOWCASE_PRODUCT')
			->where('pic_big_url IS NOT NULL')
			->get();
		
		$products = $query->result_array();
		$fixed = 0;
		$skipped = 0;
		
		echo "Found " . count($products) . " products with beauty shots\n";
		echo str_repeat('-', 80) . "\n\n";
		
		foreach ($products as $product) {
			$old_url = $product['pic_big_url'];
			
			// Skip if already a proper S3 URL
			if (strpos($old_url, 'opuzen-web-assets-public.s3.us-west-1.amazonaws.com') !== false) {
				$skipped++;
				echo "SKIP: {$product['url_title']} - Already S3 URL\n";
				continue;
			}
			
			// Convert to S3 URL
			$new_url = $this->fileuploadtos3->convertLegacyImgSrcToS3($old_url);
			
			// Update database
			$this->db->where('product_id', $product['product_id'])
				->where('product_type', $product['product_type'])
				->update('SHOWCASE_PRODUCT', array('pic_big_url' => $new_url));
			
			if ($this->db->affected_rows() > 0) {
				$fixed++;
				echo "<strong style='color:green'>FIXED: {$product['url_title']}</strong>\n";
				echo "  Old: {$old_url}\n";
				echo "  New: {$new_url}\n\n";
			} else {
				$skipped++;
				echo "SKIP: {$product['url_title']} - No change\n";
			}
		}
		
		echo "\n" . str_repeat('=', 80) . "\n";
		echo "SUMMARY: Fixed {$fixed}, Skipped {$skipped}\n";
		echo str_repeat('=', 80) . "\n";
		echo "</pre>";
	}

}
?>