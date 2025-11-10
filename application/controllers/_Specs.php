<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Specs extends MY_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->thisC = 'specs';
		$this->load->model('Specs_model', 'model');
		$this->editing_list = $this->get_editing_list();
		asort($this->editing_list);
	}

	public function index()
	{
		array_push($this->data['crumbs'], 'Products', 'Editing');

		$options[0] = 'None selected';
		$selected = '0';
		$options += $this->decode_array($this->editing_list, 'backname', 'name');
		$this->data['select_editing'] = form_dropdown('select_editing', $options, set_value('select_editing', $selected), " class='single-dropdown select_editing font-title' ");

		$this->data['hasEditPermission'] = $this->hasPermission('specs', 'edit');
		$this->view('specs/specs_list');
	}

	public function get_table_data()
	{
		$this->load->model('Editing_model', 'model');
		$given_spec_name = $this->input->post("spec_name");
		if ($given_spec_name !== '0') {

			foreach ($this->editing_list as $l) {
				if ($l['backname'] === $given_spec_name) {
					$selectedRow = $l;
				}
			}

			// Get specs and send for datatables
			$data['tableData'] = $this->model->get_rows($selectedRow);
		} else {
			$data['tableData'] = array();
		}
		echo json_encode($data);
	}

	public function edit_spec()
	{
		$given_spec_name = $this->input->post('spec_name');
		$given_spec_id = $this->input->post('spec_id');
		if ($given_spec_name !== '0') {
			$selectedRow = $this->get_selected_spec($given_spec_name);
			$this->data['hasPermission'] = $this->hasPermission('specs', 'edit');
			$this->data['spec_id'] = $given_spec_id;
			$this->data['spec_name'] = $given_spec_name;
			$this->data['info'] = $this->model->get_spec_data($selectedRow, $given_spec_id);
			$this->data['n_columns'] = $selectedRow['n_columns'];
			$this->data['shared_files'] = ( array_key_exists('shared_files', $selectedRow) ? $selectedRow['shared_files'] : false);
			$this->data['n_order'] = (array_key_exists('n_order', $selectedRow) ? $selectedRow['n_order'] : false);
			if( $this->data['shared_files'] ){
				$this->data['list_files']['tbody'] = '';
				$data = explode(constant('delimiterFiles'), $this->data['info']['files']);
				$files_encoded = array();
				if ($data[0] !== '') {
					foreach ($data as $d) {
						$aux = explode('#', $d);
						$aux_d = array(
						  'url_dir' => $aux[0],
						  'date' => nice_date($aux[1], 'm-d-Y'),
//					  'user_id' => $aux[2],
						);

						array_push($files_encoded, $aux_d);
						$this->data['list_files']['tbody'] .= "
				<tr>
					<td><a href='" . site_url() . $aux_d['url_dir'] . "' target='_blank'><i class='fa fa-file btnViewFile' style='display:none'></i></a></td>
					<td>" . $aux_d['date'] . "</td>
					" . ($this->hasPermission('specs', 'edit') ? "<td><i class='fa fa-times-circle delete_temp_url' style='display:none'></i></td>" : '') . "
				</tr>";
					}
				}
				$this->data['files_encoded'] = json_encode($files_encoded);
			}


			$ret['html'] = $this->load->view('specs/specs_form', $this->data, true);
			echo json_encode($ret);
		}
	}

	public function save_spec()
	{
		$selectedRow = null;
		$given_spec_name = $this->input->post('spec_name');
		$given_spec_id = $this->input->post('spec_id');

		$selectedRow = $this->get_selected_spec($given_spec_name);
		$this->model->set_rules($selectedRow['table'], $given_spec_id);

		$this->load->library('form_validation');
		$this->form_validation->set_rules($this->model->rules['specs']);

//		if ($this->form_validation->run() == TRUE) {
		if(true){
			$info_name = $this->input->post('info_name');
			$info_descr = $this->input->post('info_descr');
			$user_id = $this->session->userdata('user_id');
			$active = $this->input->post("active");

			if ($selectedRow['n_columns'] === 2) {
				$data = array(
				  'name' => $info_name,
				  'active' => (is_null($active) ? 'N' : 'Y'),
				  'user_id' => $user_id,
				);
			} else if ($selectedRow['n_columns'] === 3) {
				$data = array(
				  'name' => $info_name,
				  'descr' => $info_descr,
				  'active' => (is_null($active) ? 'N' : 'Y'),
				  'user_id' => $user_id
				);
			}

			if(empty($given_spec_id) and array_key_exists('n_order', $selectedRow) and $selectedRow['n_order']){
				// Save new order selected
				$table = $selectedRow['table'];
				$row_array = $this->model->db
				  ->select("MAX($table.n_order) + 1 as next_order")
				  ->from($table)
				  ->get()->row_array();
				$data['n_order'] = $row_array['next_order'];
			}

			// Save principal spec first, so that we can get a new ID in case it's a new spec row
			if (empty($given_spec_id)) {
				// New spec entered
				$given_spec_id = $this->model->save_spec($selectedRow['table'], $data);
			} else {
				$this->model->save_spec($selectedRow['table'], $data, $given_spec_id);
			}
			$data['id'] = $given_spec_id;

			if(array_key_exists('shared_files', $selectedRow) and $selectedRow['shared_files']){
				$this->load->model('File_directory_model', 'file_directory');
				$arr = $this->input->post('files_encoded');
				$arr = (is_null($arr) ? array() : json_decode($arr));
				$this->model->clean_spec_files_logic($selectedRow, $given_spec_id);
				$n = 0;
				$batch = [];

				foreach ($arr as $i) { // Loop through each of the files
					$n++;
					// Regular files

					$f = $i->url_dir; // $f becomes the Url
					if (strpos($f, 'temp')) {
						$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever)
						$filename = $given_spec_id . '-' . url_title($i->date, '-', true) . '-' . $n . '.' . $extension[1];

						$location_request = [
						  'status' => 'local_save',
						  'product_type' => $selectedRow['backname'],
						  'product_id' => $given_spec_id,
						  'img_type' => 'files',
						  'include_filename' => false,
						  'file_format' => $extension[1]
						];

						$_new_location = $this->file_directory->image_src_path($location_request);
						$new_location = $_new_location . $filename;

						// Is a new file!
						while (file_exists($new_location)) {
							$n++;
							$filename = $given_spec_id . '-' . url_title($i->date, '-', true) . '-' . $n . '.' . $extension[1];
							$new_location = $_new_location . $filename;
						}
						// Save new location
						rename(str_replace(site_url(), '', $f), $new_location);
					} else {
						// Existing file, don't relocate the file
						$new_location = str_replace(site_url(), '', $f);
					}

					$ret = array(
					  'related_id' => $given_spec_id,
					  'url_dir' => $new_location,
					  'date_add' => date('Y-m-d', strtotime(str_replace('-', '/', $i->date))),
					  'user_id' => $user_id
					);
					array_push($batch, $ret);
				}
//				var_dump($batch);
				if (count($batch) > 0) $this->model->save_spec_files($selectedRow, $batch);
			}

			$ret['row'] = $data;
			$ret['success'] = true;
		} else {
			$ret['success'] = false;
			$ret['message'] = validation_errors() . '. ' . form_error();
		}

		echo json_encode($ret);
	}

	public function save_spec_reorder(){
		$spec_name = $this->input->post("spec_name");
		$new_reorder = $this->input->post("new_reorder");
		$spec_meta = $this->get_selected_spec($spec_name);

		$this->model->db->update_batch($spec_meta['table'], $new_reorder, 'id');
		echo json_encode(['success'=>true]);
	}

	private function get_selected_spec($spec_name){
		foreach ($this->editing_list as $l) {
			if ($l['backname'] === $spec_name) {
				return $l;
			}
		}
	}

	private function get_editing_list()
	{
		return array(
		  array(
			'backname' => 'product_status',
			'name' => 'Product Status',
			'table' => $this->model->p_product_status,
			'active_relations' => array(
				//$this->model->t_product => 'product_status_id',
			  $this->model->t_item => 'status_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'stock_status',
			'name' => 'Stock Status',
			'table' => $this->model->p_stock_status,
			'active_relations' => array(
				//$this->model->t_product => 'stock_status_id',
			  $this->model->t_item => 'stock_status_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'abrasion_limit',
			'name' => 'Abrasion > Limits',
			'table' => $this->model->p_abrasion_limit,
			'active_relations' => array(
			  $this->model->t_product_abrasion => 'abrasion_limit_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'abrasion_test',
			'name' => 'Abrasion > Tests',
			'table' => $this->model->p_abrasion_test,
			'active_relations' => array(
			  $this->model->t_product_abrasion => 'abrasion_test_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'category_files',
			'name' => 'Files > Categories',
			'table' => $this->model->p_category_files,
			'active_relations' => array(
			  $this->model->t_product_files => 'category_id',
			  $this->model->p_vendor_files => 'file_category_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'category_lists',
			'name' => 'Lists > Categories',
			'table' => $this->model->p_category_lists,
			'active_relations' => array(
			  $this->model->p_list_category => 'category_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'cleanings',
			'name' => 'Cleanings',
			'table' => $this->model->p_cleaning,
			'active_relations' => array(
			  $this->model->t_product_cleaning => 'cleaning_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'cleaning_instructions',
			'name' => 'Care Instructions',
			'table' => $this->model->p_cleaning_instructions,
			'active_relations' => array(
			  $this->model->t_product_cleaning_instructions => 'cleaning_instructions_id'
			),
			'n_columns' => 3,
			'shared_files' => true
		  ),
            array(
                'backname' => 'warranty',
                'name' => 'Warranties',
                'table' => $this->model->p_warranty,
                'active_relations' => array(
                    $this->model->t_product_warranty => 'warranty_id'
                ),
                'n_columns' => 3,
                'shared_files' => true
            ),
            array(
                'backname' => 'general_warranty',
                'name' => 'Website > General Warranties',
                'table' => $this->model->p_general_warranty,
                'n_columns' => 3,
                'shared_files' => true
            ),
            array(
                'backname' => 'terms_and_conditions',
                'name' => 'Website > Terms and Conditions',
                'table' => $this->model->p_terms,
//                'active_relations' => array(),
                'n_columns' => 3,
                'shared_files' => true
            ),
            array(
                'backname' => 'faqs',
                'name' => 'Website > FAQs',
                'table' => $this->model->p_docs_fqs,
//                'active_relations' => array(),
                'n_columns' => 3,
                'shared_files' => true
            ),
            array(
                'backname' => 'general_cleaning_instructions',
                'name' => 'Website > General Cleaning Instructions',
                'table' => $this->model->p_general_cleaning_instructions,
//                'active_relations' => array(),
                'n_columns' => 3,
                'shared_files' => true
            ),
		  array(
			'backname' => 'colors',
			'name' => 'Colors',
			'table' => $this->model->p_color,
			'active_relations' => array(
			  $this->model->t_item_color => 'color_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'contents',
			'name' => 'Contents',
			'table' => $this->model->p_content,
			'active_relations' => array(
			  $this->model->t_product_content_back => 'content_id',
			  $this->model->t_product_content_front => 'content_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'finishs',
			'name' => 'Finish',
			'table' => $this->model->p_finish,
			'active_relations' => array(
			  $this->model->t_product_finish => 'finish_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'uses',
			'name' => 'Uses',
			'table' => $this->model->p_use,
			'active_relations' => array(
			  $this->model->t_product_use => 'use_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'shelfs',
			'name' => 'Shelfs',
			'table' => $this->model->p_shelf,
			'active_relations' => array(
			  $this->model->t_item_shelf => 'shelf_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'sampling_location',
			'name' => 'Sampling Locations',
			'table' => $this->model->p_sampling_locations,
			'active_relations' => array(
			  $this->model->t_item => ['roll_location_id', 'bin_location_id']
			),
			'exclude_ids' => [1],
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'restock_status',
			'name' => 'Restock Status',
			'table' => $this->model->p_restock_status,
			'active_relations' => array(
			  $this->model->t_restock_order => 'restock_status_id',
			),
//			'exclude_ids' => [1],
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'weaves',
			'name' => 'Weaves',
			'table' => $this->model->p_weave,
			'active_relations' => array(
			  $this->model->t_product_weave => 'weave_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'firecode_test',
			'name' => 'Firecode > Tests',
			'table' => $this->model->p_firecode,
			'active_relations' => array(
			  $this->model->t_product_firecode => 'firecode_test_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'origins',
			'name' => 'Origins',
			'table' => $this->model->p_origin,
			'active_relations' => array(
			  $this->model->t_product_origin => 'origin_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'weight_unit',
			'name' => 'Weight Units',
			'table' => $this->model->p_weight_unit,
			'active_relations' => array(
			  $this->model->t_product_various => 'weight_unit_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'price_type',
			'name' => 'Price Type',
			'table' => $this->model->p_price_type,
			'active_relations' => array(
			  $this->model->t_product_cost => 'cost_cut_type_id',
			  $this->model->t_product_cost => 'cost_half_roll_type_id',
			  $this->model->t_product_cost => 'cost_roll_type_id',
			  $this->model->t_product_cost => 'cost_roll_landed_type_id',
			  $this->model->t_product_cost => 'cost_roll_ex_mill_type_id'
			),
			'n_columns' => 3
		  ),
		  array(
			'backname' => 'showcase_collection',
			'name' => 'Website > Collections',
			'table' => $this->model->t_showcase_collection,
			'active_relations' => array(
			  $this->model->t_showcase_product_collection => 'collection_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'showcase_patterns',
			'name' => 'Website > Patterns',
			'table' => $this->model->t_showcase_patterns,
			'active_relations' => array(
			  $this->model->t_showcase_product_patterns => 'pattern_id',
			  $this->model->t_showcase_styles_patterns => 'pattern_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'showcase_coord_colors',
			'name' => 'Website > Coordinate Colors',
			'table' => $this->model->t_showcase_coord_colors,
			'active_relations' => array(
			  $this->model->t_showcase_item_coord_color => 'coord_color_id',
			  $this->model->t_showcase_style_items_coord_color => 'coord_color_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'showcase_contents_web',
			'name' => 'Website > Contents Web',
			'table' => $this->model->t_showcase_contents_web,
			'active_relations' => array(
			  $this->model->t_showcase_product_contents_web => 'content_web_id'
			),
			'n_columns' => 2
		  ),
		  array(
			'backname' => 'product_checklist',
			'name' => 'Product Checklist',
			'table' => $this->model->p_product_task,
			'active_relations' => array(
			  $this->model->t_product_task => 'task_id'
			),
			'n_columns' => 3,
		    'n_order' => True
		  )

		);
	}

	/*

		Digital and Screen Print Styles

	*/

	public function style_libraries()
	{
		array_push($this->data['crumbs'], 'Products', 'Patterns Library');
		//$options[0] = 'None selected';
		$selected = constant('Digital');
		$options = array(
		  constant('Digital') => 'Digital Patterns',
		  constant('ScreenPrint') => 'Screen Print Patterns'
		);
		$this->data['select_editing'] = form_dropdown('select_editing', $options, set_value('select_editing', $selected), " class='single-dropdown select_editing font-title' ");

		$this->view('specs/styles_list');
	}

	public function get_styles()
	{
		$styles_type = $this->input->post('style_type');
		$arr['tableData'] = array();
		if ($styles_type !== '0') {
			switch ($styles_type) {
				case constant('Digital'):
					$arr['tableData'] = $this->model->get_digital_styles(array('count' => true));
					break;

				case constant('ScreenPrint'):
					$arr['tableData'] = $this->model->get_screenprint_styles(array('count' => true));
					break;
			}
		}
		echo json_encode($arr);
	}

	public function edit_style()
	{
		$given_style_type = $this->input->post('style_type');
		$given_style_id = $this->input->post('style_id');
		$data['hasPermission'] = $this->hasPermission('specs', 'edit');
		$data['style_type'] = $given_style_type;
		$data['style_id'] = $given_style_id;
		$data['isNew'] = $data['style_id'] === '0';
		$data['info'] = $this->model->get_style_data($given_style_type, $given_style_id);
		//var_dump($data);exit;
		if ($given_style_type === constant('Digital')) {

			if ($data['isNew']) {
				$data['info']['url_title'] = $this->model->site_urls['website'];
			} else {
				$data['info']['url_title'] = $this->model->site_urls['website'] . 'product/digital/' . $data['info']['url_title'];
			}

			/* Files */
			$data['list_files']['tbody'] = '';
			$data['list_files']['tfoot'] = '';
			$files = $this->model->select_style_files($data['style_id']);
			//var_dump($data['info']['files']);exit;
			//$data = explode(constant('delimiterFiles'), $data['info']['files']);
			$lis = array();
			$files_encoded = array();
			if (!empty($files)) {
				foreach ($files as $d) {
					$isOther = ($d['category_id'] === $this->category_files_ids['other']);
					$aux_d = array(
					  'url_dir' => $d['url_dir'],
					  'date' => nice_date($d['date_add'], 'm-d-Y'),
					  'user_id' => $d['user_id'],
					  'category_id' => $d['category_id'],
					  'category_name' => ($isOther ? $d['descr'] : $d['category_name']),
					  'descr' => $d['descr']
					);

					array_push($files_encoded, $aux_d);
					$data['list_files']['tbody'] .= "
					<tr>
						<td><a href='" . site_url() . $aux_d['url_dir'] . "' target='_blank'><i class='fa fa-file btnViewFile' style='display:none'></i> " . ($isOther ? $aux_d['descr'] : $aux_d['category_name']) . "</a></td>
						<td>" . $aux_d['date'] . "</td>
						" . ($this->hasPermission('specs', 'edit') ? "<td><i class='fa fa-times-circle delete_temp_url' style='display:none'></i></td>" : '') . "
					</tr>";
				}
			}
			$data['files_encoded'] = json_encode($files_encoded);
			//$this->data['list_files'] = ul($lis, " id='list_files' class='list-unstyled d-flex flex-wrap flex-c-50' ");

			//$l = $this->model->get_categories_files();
			//$options = $this->decode_array($l, 'id', 'name');
			$options = array($this->category_files_ids['memotags_picture'] => 'Memotag picture');
			$data['dropdown_category_files'] = form_dropdown('category_files', $options, array(), " id='category_files' class='single-dropdown btn btn-default' ");


			/* Showcase data */
			$this->load->model('File_directory_model', 'file_directory');
			$data['items'] = $this->model->get_style_items($given_style_type, $given_style_id);

			if (!$data['isNew'] && !is_null($data['info']['pic_big_url'])) {
			  $data['info']['pic_big_url'] = $this->convertLegacyImgSrcToS3( $data['info']['pic_big_url']);
//            } else if (!$data['isNew'] && !is_null($data['info']['pic_big']) && $data['info']['pic_big'] !== 'N') {
//                $data['info']['pic_big_url'] = $this->file_directory->image_src_path('load', 'digital_styles') . $given_style_id . '.jpg';
			} else {
				$data['info']['pic_big_url'] = '';
			}

			$l = $this->model->get_showcase_patterns();
			$options = $this->decode_array($l, 'id', 'name');
			$selected = ($data['isNew'] ? array() : explode(constant('delimiter'), $data['info']['showcase_pattern_id']));
			$data['dropdown_showcase_patterns'] = form_multiselect('showcase_patterns[]', $options, set_value('showcase_patterns[]', $selected), " class='multi-dropdown' tabindex='-1' ");

			// Process each web item for this style
			foreach ($data['items'] as $key => $value) {
				if (!is_null($data['items'][$key]['pic_big_url'])) {
				  $data['items'][$key]['pic_big_url'] = $this->convertLegacyImgSrcToS3( $data['items'][$key]['pic_big_url']);
//                } else if (!is_null($data['items'][$key]['pic_big']) && $data['items'][$key]['pic_big'] !== 'N') {
//                    $data['items'][$key]['pic_big_url'] = $this->file_directory->image_src_path('load', 'digital_styles_items') . $data['items'][$key]['id'] . '.jpg';
				} else {
					$data['items'][$key]['pic_big_url'] = '';
				}
			}
			
			// echo "<pre DEBUGGING TEST line: ".__LINE__." file: ".__FILE__.">";
            // print_r($data['items']);
            // echo "</pre>";

			$data['items_list']['ids'] = json_encode(array_column($data['items'], 'id'));
			$data['items_list']['json'] = json_encode($data['items']);

		} else {
			return;
		}

		$ret['html'] = $this->load->view('specs/styles_form', $data, true);
		echo json_encode($ret);
	}

	public function save_style()
	{
		$this->load->model('File_directory_model', 'file_directory');
		$given_style_type = $this->input->post('style_type');
		$given_style_id = $this->input->post('style_id');

		$this->model->set_rules('', $given_style_id);
		$this->load->library('form_validation');
		$this->form_validation->set_rules($this->model->rules['styles'][$given_style_type]);

		if ($this->form_validation->run() == TRUE) {
			$info_name = $this->input->post('info_name');
			$style_url_title = url_title($info_name, '-', true);
			$vrepeat = $this->input->post('vrepeat');
			$hrepeat = $this->input->post('hrepeat');
			$no_repeat = $this->input->post('no_repeat');
			$active = $this->input->post('active');
			$user_id = $this->session->userdata('user_id');

			if (!is_null($no_repeat) && $no_repeat === 'on') {
				$vrepeat = null;
				$hrepeat = null;
			}

			$data = array(
			  'name' => $info_name,
			  'vrepeat' => $vrepeat,
			  'hrepeat' => $hrepeat,
			  'active' => (!is_null($active) && $active === 'on' ? 'Y' : 'N'),
			  'user_id' => $user_id
			);

			if (empty($given_style_id)) {
				// New spec entered
				$given_style_id = $this->model->save_style($given_style_type, $data);
			} else if ($this->input->post('change_product') === '1') {
				$this->model->save_style($given_style_type, $data, $given_style_id);
			}

			$data['id'] = $given_style_id;

			// echo '<pre> '. get_class(). "::" . __FUNCTION__ . '()  ' . __LINE__ .  ' </pre>';
			// print_r($_POST);
			// print_r($_FILES);
			// echo '</pre>';

			if ($this->input->post('change_showcase') === '1') {
				$showcase_visible = $this->input->post('showcase_visible');

				$f = $this->input->post('pic_big_url');
				if (strpos($f, 'temp')) {
					// Is a new file!
					$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever extension is)
					$location_request = [
					  'status' => 'save',
					  'product_type' => Digital,
					  'product_id' => $given_style_id,
					  'item_id' => null,
					  'img_type' => 'beauty_shot',
					  'include_filename' => true,
					  'file_format' => $extension[1]
					];
					$new_location = $this->file_directory->image_src_path($location_request);

					if (file_exists($new_location)) {
						// Move existing file
						$_destination_for_replacement = str_replace('.' . $extension[1], '-' . url_title(date("Y-m-d h-i-sa")) . '.' . $extension[1], $new_location);
						rename($new_location, $_destination_for_replacement);
					}
					$location_request['status'] = 'load';
					$new_location_db = $this->file_directory->image_src_path($location_request);

					// Save current uploaded file
					rename(str_replace(site_url(), '', $f), $new_location);
				} else {
					// Existing file, don't relocate the file
					$new_location_db = $f;
				}
				$showcase_data = array(
				  'url_title' => $style_url_title,
				  'visible' => (!is_null($showcase_visible) && $showcase_visible === 'on' ? 'Y' : 'N'),
				  'pic_big' => null,
				  'pic_big_url' => $new_location_db,
				  'user_id' => $this->data['user_id']
				);
				$this->model->save_style_showcase($given_style_type, $showcase_data, $given_style_id);

				// Pattern
				$arr = $this->input->post('showcase_patterns');
				$arr = (is_null($arr) ? array() : $arr);
				$ret = array();
				foreach ($arr as $specid) {
					array_push($ret, array(
					  'style_id' => $given_style_id,
					  'pattern_id' => $specid
					));
				}
				$this->model->save_style_showcase_patterns($given_style_type, $ret, $given_style_id);
			} else if ($this->input->post('change_product') === '1') {
				// Update url_title in case the product name changed
				$this->db
				  ->set("url_title", $style_url_title)
				  ->where("style_id", $given_style_id)
				  ->update($this->model->t_showcase_style);
			}

			if ($given_style_type === constant('Digital') && $this->input->post('change_files_encoded') === '1') {
				$this->load->model('File_directory_model', 'file_directory');
				$arr = $this->input->post('files_encoded');
				$arr = (is_null($arr) ? array() : json_decode($arr));
				$batch = array();
				$n = 0;

				$this->model->clean_style_files_logic($given_style_id);
				foreach ($arr as $i) { // Loop through each of the files
					$n++;
					if ($i->category_id === $this->category_files_ids['memotags_picture']) {
						// Memotag pictures
						$f = $i->url_dir; // $f becomes the Url
						if (strpos($f, 'temp')) {
							// Is a new file!
							$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever extension is)
							$filename = $given_style_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . $extension[1];

							$location_request = [
							  'status' => 'local_save',
							  'product_type' => Digital,
							  'product_id' => $given_style_id,
							  'img_type' => 'memotags_picture',
							  'include_filename' => false,
							  'file_format' => $extension[1]
							];

							$_new_location = $this->file_directory->image_src_path($location_request);
							$new_location = $_new_location . '/' . $filename;

							// Is a new file!
							while (file_exists($new_location)) {
								$n++;
								$filename = $given_style_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . $extension[1];
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
						  'style_id' => $given_style_id,
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

							$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever extension is)
							$filename = $given_style_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . $extension[1];

							$location_request = [
							  'status' => 'local_save',
							  'product_type' => Digital,
							  'product_id' => $given_style_id,
							  'img_type' => 'files',
							  'include_filename' => false,
							  'file_format' => $extension[1]
							];

							$_new_location = $this->file_directory->image_src_path($location_request);
							$new_location = $_new_location . '/' . $filename;

							// Is a new file!
							while (file_exists($new_location)) {
								$n++;
								$filename = $given_style_id . '-' . url_title($i->category_id . '-' . $i->category_name . '-' . $i->date, '-', true) . '-' . $n . '.' . $extension[1];
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
						  'style_id' => $given_style_id,
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
				if (count($batch) > 0) $this->model->save_style_files($batch, $given_style_id);
			}

			// Process individual Digital items
			$n = 0;
			$count_active_items = 0;
			$item_ids = json_decode($this->input->post('item_ids'));
			$item_ids_edited = json_decode($this->input->post('item_ids_edited'));
			$item_ids_not_edited = array();
			$item_ids_deleted = json_decode($this->input->post('item_ids_deleted'));

			// Delete first
			if (!empty($item_ids_deleted)) $this->model->archive_style_item($given_style_type, $item_ids_deleted);

			// Then updates
			if (!empty($item_ids_edited)) {

				foreach ($item_ids_edited as $id) {
					$item_visible = $this->input->post('showcase_visible_' . $id);
					if (!is_null($item_visible) && $item_visible === 'on') $count_active_items++;

					// Process image location if new
					$f = $this->input->post('pic_big_url_' . $id);
					if (strpos($f, 'temp')) {
						$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f));

						$location_request = [
						  'status' => 'save',
						  'product_type' => Digital,
						  'product_id' => $given_style_id,
						  'item_id' => $id,
						  'img_type' => 'big',
						  'include_filename' => true,
						  'file_format' => $extension[1]
						];
						$new_location = $this->file_directory->image_src_path($location_request);

						if (file_exists($new_location)) {
							// Move existing file
							$location_request['item_id'] = strval($id) . '-' . url_title(date("Y-m-d h-i-sa"));
							rename($new_location, $this->file_directory->image_src_path($location_request));
							$location_request['item_id'] = $id;
						}
						$location_request['status'] = 'load';
						$new_location_db = $this->file_directory->image_src_path($location_request);

						// Save current uploaded file
						rename(str_replace(site_url(), '', $f), $new_location);

//                    	***
//                        // Is a new file!
//	                    $location_request = [
//	                      'status'=>'save',
//	                      'product_type'=>Digital,
//	                      'product_id'=>$given_style_id,
//	                      'item_id'=>$id,
//	                      'img_type'=>'big',
//	                      'include_filename'=>true,
//	                      'file_format'=>$extension[1]
//	                    ];
//	                    $new_location = $this->file_directory->image_src_path($location_request);
//
//	                    if (file_exists($new_location)) {
//		                    // Move existing file
//		                    $_destination_for_replacement = str_replace('.'.$extension[1], '-'.url_title(date("Y-m-d h-i-sa")).'.'.$extension[1], $new_location);
//		                    rename($new_location, $_destination_for_replacement);
//	                    }
//	                    $location_request['status'] = 'load';
//	                    $new_location_db = $this->file_directory->image_src_path($location_request);
//
//	                    // Save current uploaded file
//	                    rename(str_replace(site_url(), '', $f), $new_location);
					} else {
						// Existing file, don't relocate the file
						$new_location_db = $f;
					}

					$item_color_ids = json_decode($this->input->post('color_ids_' . $id));
					$item_color_names = json_decode($this->input->post('color_names_' . $id));
					$batch = array();
					$n = 1;
					foreach ($item_color_ids as $cid) {
						// Check if it's a new one
						$isNew = strpos($cid, 'new-') !== false;
						if ($isNew) {
							$new_color_data = array(
							  'name' => trim($item_color_names[$n - 1])
							);
							$color_id = $this->model->save_new_color($new_color_data);
						} else {
							$color_id = $cid;
						}

						array_push($batch, array('item_id' => $id, 'color_id' => $color_id, 'n_order' => $n, 'user_id' => $this->data['user_id']));
						$n++;
					}
					$this->model->save_style_item_colors($batch, $id);

					// Data
					$showcase_data = array(
					  'url_title' => $style_url_title . '/' . url_title(implode('-', $item_color_names), '-', true),
					  'visible' => (!is_null($item_visible) && $item_visible === 'on' ? 'Y' : 'N'),
					  'pic_big' => null,
					  'pic_big_url' => $new_location_db,
					  'user_id' => $this->data['user_id']
					);
					$this->model->save_style_item($showcase_data, $id);
				}
			}

			// Any new?
			if (!empty($item_ids)) {
				foreach ($item_ids as $id) {
					$item_visible = $this->input->post('showcase_visible_' . $id);
					if (!is_null($item_visible) && $item_visible === 'on') $count_active_items++;

					$isNew = strpos($id, 'new') !== false;
					if ($isNew) {
						// New Item to be saved

						// Process image location if new
						$f = $this->input->post('pic_big_url_' . $id);
						if (strpos($f, 'temp')) {
							$extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f));

							$location_request = [
							  'status' => 'save',
							  'product_type' => Digital,
							  'product_id' => $given_style_id,
							  'item_id' => $id,
							  'img_type' => 'big',
							  'include_filename' => true,
							  'file_format' => $extension[1]
							];
							$new_location = $this->file_directory->image_src_path($location_request);

							if (file_exists($new_location)) {
								// Move existing file
								$location_request['item_id'] = strval($id) . '-' . url_title(date("Y-m-d h-i-sa"));
								rename($new_location, $this->file_directory->image_src_path($location_request));
								$location_request['item_id'] = $id;
							}
							$location_request['status'] = 'load';
							$new_location_db = $this->file_directory->image_src_path($location_request);

							// Save current uploaded file
							rename(str_replace(site_url(), '', $f), $new_location);

//                        	***
//                            // Is a new file!
//                            $extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever extension is)
//	                        $location_request = [
//	                          'status'=>'save',
//	                          'product_type'=>Digital,
//	                          'product_id'=>$given_style_id,
//	                          'item_id'=>$id,
//	                          'img_type'=>'big',
//	                          'include_filename'=>true,
//	                          'file_format'=>$extension[1]
//	                        ];
//	                        $new_location = $this->file_directory->image_src_path($location_request);
//
//	                        if (file_exists($new_location)) {
//		                        // Move existing file
//		                        $_destination_for_replacement = str_replace('.'.$extension[1], '-'.url_title(date("Y-m-d h-i-sa")).'.'.$extension[1], $new_location);
//		                        rename($new_location, $_destination_for_replacement);
//	                        }
//	                        $location_request['status'] = 'load';
//	                        $new_location_db = $this->file_directory->image_src_path($location_request);
//
//	                        // Save current uploaded file
//	                        rename(str_replace(site_url(), '', $f), $new_location);
						} else {
							// Existing file, don't relocate the file
							$new_location_db = $f;
						}

						$item_color_ids = json_decode($this->input->post('color_ids_' . $id));
						$item_color_names = json_decode($this->input->post('color_names_' . $id));
						$showcase_data = array(
						  'style_id' => $given_style_id,
						  'url_title' => $style_url_title . '/' . url_title(implode('-', $item_color_names), '-', true),
						  'visible' => (!is_null($item_visible) && $item_visible === 'on' ? 'Y' : 'N'),
						  'pic_big' => null,
						  'pic_big_url' => $new_location_db,
						  'user_id' => $this->data['user_id']
						);
						$new_item_id = $this->model->save_style_item($showcase_data);

						$batch = array();
						$n = 1;
						foreach ($item_color_ids as $cid) {
							// Check if it's a new one
							$isNew = strpos($cid, 'new-') !== false;

							if ($isNew) {
								$new_color_data = array(
								  'name' => trim($item_color_names[$n - 1])
								);
								$color_id = $this->model->save_new_color($new_color_data);
							} else {
								$color_id = $cid;
							}

							array_push($batch, array('item_id' => $new_item_id, 'color_id' => $color_id, 'n_order' => $n, 'user_id' => $this->data['user_id']));
							$n++;
						}
						$this->model->save_style_item_colors($batch, $id);

					} else {
						array_push($item_ids_not_edited, $id);
					}
				}
			}

			// If showcase_product=1, update url_title for the items that were not tracked as updates
			if ($this->input->post('change_product') === '1' && !empty($item_ids_not_edited)) {
				foreach ($item_ids_not_edited as $id) {
					$item_color_names = json_decode($this->input->post('color_names_' . $id));
					$showcase_data = array(
					  'url_title' => $style_url_title . '/' . url_title(implode('-', $item_color_names), '-', true),
					);
					$this->model->save_style_item($showcase_data, $id);
				}
			}

			$data['count_active_items'] = $count_active_items;
			$data['count_items'] = count($item_ids) + count($item_ids_edited);
			$ret['row'] = $data;
			$ret['success'] = true;
		} else {
			$ret['success'] = false;
			$ret['message'] = validation_errors();
		}
		echo json_encode($ret);
	}

	public function archive_style()
	{
		$style_type = $this->input->post('style_type');
		$style_id = $this->input->post('style_id');
		$result = $this->model->archive_style($style_type, $style_id);
		if ($this->input->is_ajax_request()) {
			$ret = array(
			  'success' => true,
			  'style_id' => $style_id
			);
			echo json_encode($ret);
		}
	}

	public function retrieve_style()
	{
		$style_type = $this->input->post('style_type');
		$style_id = $this->input->post('style_id');
		$style_data = $this->model->retrieve_style($style_type, $style_id);
		if ($this->input->is_ajax_request()) {
			$ret = array(
			  'success' => true,
			  'style_data' => $style_data
			);
			echo json_encode($ret);
		}
	}

	/*

		Function called via Ajax from the product/edit_product to return
		- Form for new submission of data (content, content-back, firecode, abrasion)
		- List of current items information

	*/
	public function get_specs_list()
	{
		//$this->load->library('table');
		$this->load->helper('date');
		//$this->table_template();
		$this->load->model('Product_model', 'product_model');

		$product_id = $this->input->post('product_id');
		$product_type = $this->input->post('product_type');
		$spectype = $this->input->post('spectype');

		$ret = array();
		$tarr = array();
		$form = '';
		$tbody = '';
		$tfoot = '';

		switch ($spectype) {
			case 'content_f':
				$formdata['title'] = 'Front Content';
				$formdata['spectype'] = $spectype;
				$l = $this->model->get_contents();
				$options = $this->decode_array($l, 'id', 'name');
				// Form for when user wants to add new content
				$formdata['dropdown_contents'] = form_dropdown('new_content_id', $options, array(), " id='new_content_id' class='multiselect dropdown-toggle btn btn-default w-100 grey-background' ");
				$formdata['form_button_submit'] = form_button('btnSS', " Add ", " id='btnAddSS' data-spectype='" . $spectype . "' class='btn pull-right' onClick='add_new_spec_data(this)' ");

				// Get this products content front
				$total = 0;
				$jsonarr = json_decode($this->input->post('user_inputed'), true);
				$arr = (!empty($jsonarr) ? $jsonarr : $this->product_model->select_product_content_front($product_id));
				$data['tbody'] = '';
				if (!empty($arr)) {
					foreach ($arr as $r) {
						$total += $r['perc'];
						$data['tbody'] .= "
						<tr data-perc='" . $r['perc'] . "' data-content-id='" . $r['id'] . "' data-content-name='" . $r['name'] . "'>
							<td>" . $r['perc'] . "%</td>
							<td>" . $r['name'] . "</td>
							<td><i class='fa fa-trash btnDeleteRow pull-right' style='display:none'></i></td>
						</tr>
						";
					}
				} else {
					//$this->table->add_row('No results.');
				}
				$data['tfoot'] = "
				<tr>
					<td colspan='3'><small>(<span id='perc_total'>$total</span>%)</small></td>
				</tr>
				";
				$form = $this->load->view('product/form/specs_modal_forms/contents', $formdata, true);
				break;

			case 'content_b':
				$formdata['title'] = 'Back Content';
				$formdata['spectype'] = $spectype;

				$l = $this->model->get_contents();
				$options = $this->decode_array($l, 'id', 'name');
				// Form for when user wants to add new content
				$formdata['dropdown_contents'] = form_dropdown('new_content_id', $options, array(), " id='new_content_id' class='multiselect dropdown-toggle btn btn-default w-100 grey-background' ");

				$form = $this->load->view('product/form/specs_modal_forms/contents', $formdata, true);

				// Get this products content front
				$total = 0;
				$jsonarr = json_decode($this->input->post('user_inputed'), true);
				$arr = (!empty($jsonarr) ? $jsonarr : $this->product_model->select_product_content_back($product_id));
				$data['tbody'] = '';
				if (!empty($arr)) {
					foreach ($arr as $r) {
						$total += $r['perc'];
						$data['tbody'] .= "
						<tr data-perc='" . $r['perc'] . "' data-content-id='" . $r['id'] . "' data-content-name='" . $r['name'] . "'>
							<td>" . $r['perc'] . "%</td>
							<td>" . $r['name'] . "</td>
							<td><i class='fa fa-trash btnDeleteRow pull-right' style='display:none'></i></td>
						</tr>
						";
					}
				} else {
					//$this->table->add_row('No results.');
				}
				$data['tfoot'] = "
				<tr>
					<td colspan='3'><small>(<span id='perc_total'>$total</span>%)</small></td>
				</tr>
				";

				break;

			case 'abrasion':
				$formdata['title'] = 'Abrasion';
				$formdata['product_id'] = $product_id;
				$formdata['spectype'] = $spectype;

				$l = $this->model->get_abrasion_limits();
				$t = $this->model->get_abrasion_tests();
				$options_l = $this->decode_array($l, 'id', 'name');
				$options_t = $this->decode_array($t, 'id', 'name');
				// Form for when user wants to add new content
				$formdata['new_abrasion_limit_options'] = $options_l;
				//$formdata['dropdown_new_abrasion_limit'] = form_dropdown('new_abrasion_limit', $options_l, ( isset($arr[0]) ? array($arr[0]['abrasion_limit_id']) : array(1) ), " id='new_abrasion_limit' class='multiselect dropdown-toggle btn btn-default w-100 form-control grey-background' ");
				$formdata['new_abrasion_test_options'] = $options_t;
				//$formdata['dropdown_new_abrasion_test'] = form_dropdown('new_abrasion_test', $options_t, ( isset($arr[0]) ? array($arr[0]['abrasion_test_id']) : array() ), " id='new_abrasion_test' class='multiselect dropdown-toggle btn btn-default w-100 form-control grey-background' ");

				$form = $this->load->view('product/form/specs_modal_forms/abrasion', $formdata, true);

				// Get this products data
				$total = 0;
				$jsonarr = json_decode($this->input->post('user_inputed'), true);
				$arr = (!empty($jsonarr) ? $jsonarr : $this->product_model->select_product_abrasion($product_id));

				$data['tbody'] = '';
				if (!empty($arr)) {
					foreach ($arr as $r) {
						$total++;
						$filesanchors = '';
						$files = (is_array($r['files']) ?
						  $r['files'] :
						  (!is_null($r['files']) ?
							explode('**', $r['files']) :
							array()
						  )
						);
						if (count($files) > 0) {
							foreach ($files as $f) {
								$filesanchors .= "<a href='" . site_url() . str_replace(site_url(), '', $f) . "' target='_blank'><i class='fa fa-file btnViewFile' style='display:none'></i></a> ";
							}
						}
						$data['tbody'] .= "
						<tr data-id='" . $r['id'] . "' data-rubs='" . $r['rubs'] . "' data-abrasion-limit-id='" . $r['abrasion_limit_id'] . "' data-abrasion-limit-name='" . $r['abrasion_limit_name'] . "' data-abrasion-test-id='" . $r['abrasion_test_id'] . "' data-abrasion-test-name='" . $r['abrasion_test_name'] . "' data-date-add='" . $r['date_add'] . "' data-visible='" . $r['visible'] . "' data-in-vendor-specsheet='" . $r['data_in_vendor_specsheet'] . "'>
							<td> <i class='fa " . ($r['visible'] == 'Y' ? 'fa-eye text-success' : 'fa-eye-slash text-danger') . "' style='display:none'></i> </td>
							<td>" . nice_date($r['date_add'], 'm-d-Y') . "</td>
							<td id='files'>$filesanchors</td>
							<td>" . $r['abrasion_limit_name'] . "</td>
							<td>" . $r['rubs'] . "</td>
							<td>" . $r['abrasion_test_name'] . "</td>
							<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' style='display:none'></i></td>
							<td class='align-middle'><i class='fa fa-trash btnDeleteRow pull-right' style='display:none'></i></td>
						</tr>
						";
					}
				} else if ($product_id == 0) {
					// Is a new item, we must show the table
				} else {

				}
				$data['tfoot'] = '';
				break;

			case 'firecode':
				$formdata['title'] = 'Firecodes';
				$formdata['product_id'] = $product_id;
				$formdata['spectype'] = $spectype;

				$l = $this->model->get_firecodes();
				$options_l = $this->decode_array($l, 'id', 'name');
				// Form for when user wants to add new

				$formdata['dropdown_firecodes'] = form_dropdown('new_firecode', $options_l, array(), " id='new_firecode' class='multiselect dropdown-toggle btn btn-default w-100 form-control grey-background' ");
				$formdata['form_button_submit'] = form_button('btnSS', " Add ", " id='btnAddSS' data-spectype='" . $spectype . "' class='btn pull-right' onClick='add_new_spec_data(this)' ");

				$form = $this->load->view('product/form/specs_modal_forms/firecodes', $formdata, true);

				// Get this products data
				$total = 0;
				$jsonarr = json_decode($this->input->post('user_inputed'), true);
				$arr = (!empty($jsonarr) ? $jsonarr : $this->product_model->select_product_firecodes($product_id));

				$data['tbody'] = '';
				if (!empty($arr)) {
					foreach ($arr as $r) {
						$total++;
						$filesanchors = '';
						$files = (is_array($r['files']) ?
						  $r['files'] :
						  (!is_null($r['files']) ?
							explode('**', $r['files']) :
							array()
						  )
						);
						if (count($files) > 0) {
							foreach ($files as $f) {
								$filesanchors .= "<a href='" . site_url() . str_replace(site_url(), '', $f) . "' target='_blank'><i class='fa fa-file btnViewFile' style='display:none'></i></a> ";
							}
						}
						$data['tbody'] .= "
						<tr data-id='" . $r['id'] . "' data-firecode-test-id='" . $r['firecode_test_id'] . "' data-firecode-test-name='" . $r['firecode_test_name'] . "' data-in-vendor-specsheet='" . $r['data_in_vendor_specsheet'] . "' data-date-add='" . $r['date_add'] . "' data-visible='" . $r['visible'] . "' >
							<td> <i class='fa " . ($r['visible'] == 'Y' ? 'fa-eye text-success' : 'fa-eye-slash text-danger') . "' style='display:none'></i> </td>
							<td>" . nice_date($r['date_add'], 'm-d-Y') . "</td>
							<td id='files'>$filesanchors</td>
							<td>" . $r['firecode_test_name'] . "</td>
							<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' style='display:none'></i></td>
							<td class='align-middle'><i class='fa fa-trash btnDeleteRow pull-right' style='display:none'></i></td>
						</tr>
						";
					}
				} else if ($product_id == 0) {
					// Is a new item, we must show the table
				} else {

				}
				$data['tfoot'] = '';
				break;

			case 'product_messages':
				$formdata['title'] = 'Product Notes';
				$formdata['product_id'] = $product_id;
				$formdata['product_type'] = $product_type;
				$formdata['spectype'] = $spectype;

				//var_dump($l);exit;
				$form = $this->load->view('product/form/specs_modal_forms/messages', $formdata, true);

				// Get this products data
				$total = 0;
				$user_input = $this->input->post('user_inputed');
				if (empty($user_input)) $user_input = "{}";
				$edited_messages = json_decode($user_input, true); // New or Edited messages from user but not saved yet

				$arr = $this->product_model->select_product_messages($product_type, $product_id); // All Messages in DB
				$edited_msgs_ids = array_column($edited_messages, 'id');

				$data['tbody'] = '';

				//if( !empty($arr) ){

				foreach ($arr as $r) {
					if (!in_array($r['id'], $edited_msgs_ids)) {
						$total++;
						$r['date_modif'] = (strpos($r['date_modif'], '0000') !== false ? $r['date_add'] : $r['date_modif']);
						$data['tbody'] .= "
						<tr data-message-id='" . $r['id'] . "' data-date-add='" . $r['date_modif'] . "' data-user-id='" . $r['user_id'] . "' data-edited='N'>
							<td>" . nice_date($r['date_modif'], 'm-d-Y') . "</td>
							<td>" . $r['username'] . "</td>
							<td class='message'>" . $r['message_note'] . "</td>
							" . ($r['user_id'] === $this->data['user_id'] ? "
							<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' style='display:none'></i></td>
							" : '<td></td><td></td>') . "
						</tr>
						";
					}
				}

				// Only the new ones are left
				foreach ($edited_messages as $r) {
					$total++;
					$data['tbody'] = "
					<tr data-message-id='" . $r['id'] . "' data-date-add='" . $r['date_add'] . "' data-user-id='" . $r['user_id'] . "' data-edited='Y'>
						<td>" . $r['date_add'] . "</td>
						<td>" . $this->data['username'] . "</td>
						<td class='message'>" . $r['message_note'] . "</td>
						<td class='align-middle'><i class='fas fa-pen-square btnEditSSRow pull-left' style='display:none'></i></td>
					</tr>
					" . $data['tbody'];
				}
				$data['tfoot'] = '';
				break;

			default:
				break;
		}

		$data['title'] = $formdata['title'];
		$data['spectype'] = $spectype;
		$data['form'] = $form;
		$ret['html'] = $this->load->view('product/form/specs_modal_forms/_specs_modal_wrap', $data, true);

		if ($this->input->is_ajax_request()) {
			echo json_encode($ret);
		} else {

		}
	}

}
