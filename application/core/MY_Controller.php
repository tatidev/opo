<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	require APPPATH . '/core/MY_Constants.php';
	require APPPATH . '/libraries/REST_Controller.php';
	
	class MY_Controller extends CI_Controller
	{
		// Common properties
		protected $data;
		protected $thisC;
		
		function __construct()
		{
			parent::__construct();
			//PKL Added for AWS Setup
			//$this->load->database('default');

			date_default_timezone_set("America/Los_Angeles");
			$this->data['crumbs'] = array();
			$this->data['library_head'] = array();
			$this->data['library_foot'] = array();
			//     $this->access_log();
			$guestUrls = [
				'reps/product/specsheet/',
				# Required endpoints for Sourcebook
				'lists/sourcebook', 'lists/get_sourcebook', 'reps/item/get/'
			];
			$isGuest = false;
			foreach($guestUrls as $gUrl){
				if (strpos($this->uri->uri_string(), $gUrl) !== false) {
					$isGuest = true;
				}
			}
			
			if ($isGuest) {
				$this->init_guest();
			} else if ($this->ion_auth->logged_in() === FALSE) {
				redirect('/auth/');
			} else {
				$this->init_logged();
//            // Logged in !
//            if ($this->ion_auth->in_group(constant('Showroom_Group_id'))) {
//                // Showroom logged in!
//                  redirect('https://dev.opuzen.com/dev_roadkit2');
//            } else {
//                $this->init_logged();
//            }
			}
		}
		
		/*
		 * Initialization functions for all
		 */
		
		private function init()
		{
			$this->no_price_sql_output = '$ -';
			$this->not_valid_specs = array('0', '0.00', "N/A", 'Not Officially Tested', 'Ask Vendor', 'Unknown');
			
			$this->special_cases = array(
				'firecodes' => array(38),
				'abrasion' => array(1, 4, 5),
				// Used for
				'uses_ids' => array(
					'drapery' => 6,
					'upholstery' => 2
				)
			);
			
			$this->category_lists_ids = array(
				'ess' => 1, // essentials
				'b&w' => 2,
				'coll' => 9
			);
			
			$this->category_files_ids = array(
				'other' => '2',
				'memotags_picture' => '4'
			);
			
			$this->error_ul_attr = array(
				'class' => 'list-unstyled'
			);
		}
		
		private function init_guest()
		{
			// For Reps Specsheet view purposes
			$this->init();
			$this->data['is_admin'] = false;
			$this->data['permissionsList'] = [];
			$this->data['user_id'] = 999;
			$this->data['username'] = 'guest';
			$this->data['user_ip'] = $this->input->ip_address();
		}
		
	private function init_logged()
	{
		$this->init();
		$this->data['user_id'] = $this->session->userdata('user_id');
		$this->data['username'] = $this->session->userdata('username');
		// Set admin status: user_id '1' is always admin, OR user is in admin group
		// Use explicit boolean conversion to ensure strict type checking
		$is_user_id_admin = ($this->data['user_id'] === '1' || $this->data['user_id'] === 1);
		$is_group_admin = (bool) $this->ion_auth->is_admin();
		$this->data['is_admin'] = (bool) ($is_user_id_admin || $is_group_admin);
		$this->data['is_showroom'] = $this->ion_auth->in_group(constant('Showroom_Group_id'));
		$this->data['permissionsList'] = $this->ion_auth->getPermissionsArr();
		$this->data['user_ip'] = $this->input->ip_address();
		
		$this->data['editProductUrl'] = site_url('product/edit');
		$this->data['viewColorlinesUrl'] = site_url('item/index');
		$this->data['editListUrl'] = site_url('lists/edit');
		
		$this->data['btnBack'] = "<a class='btn btn-outline-info no-border btnBack'><i class='far fa-arrow-alt-circle-left'></i> Back</a>";
	}
		
		protected function get_stamps($level)
		{
			if ($level == item) {
				$identifier = 'item_id';
			} else if ($level == product) {
				$identifier = 'product_id';
			} else {
				# no stamps...
				return array();
			}
			return array(
				'under30_ids' => array_column($this->search->do_search(['list' => ['id' => $this->model->under30_list_id], 'group_by' => $level, 'select' => false]), $identifier),
				'digital_ground_ids' => array_column($this->search->do_search(['list' => ['id' => $this->model->digital_grounds_list_id], 'group_by' => $level, 'select' => false]), $identifier),
				'fabricseen_ids' => array_column($this->search->do_search(['list' => ['id' => $this->model->fabricseen_list_id], 'group_by' => $level, 'select' => false]), $identifier),
			);
		}
		
		/*
		 * Loading functions for all
		 */
		
		protected function load_libraries()
		{
			/*
			Javascript/CSS (external) Libraries For All Pages
			*/
			
			//$this->data['library_ajax'] = array();
			// Head
			$this->add_lib('head', '', '<meta charset="utf-8">');
			$this->add_lib('head', '', '<meta name="author" content="Ezequiel Donovan">');
			$this->add_lib('head', '', '<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height, shrink-to-fit=no">');
			$this->add_lib('head', '', "<link rel='icon' type='image/ico' href='" . asset_url() . "images/favicon_b_32x32.png'>");
			$this->add_lib('head', 'css', 'https://fonts.googleapis.com/css?family=Karla');
			
			// JQuery
			$this->load_jquery();
			
			// Fontawesome
			$this->load_fontawesome();
			//$this->add_lib('head', '', '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">');
			//$this->add_lib('head', 'css', asset_url().'others/Font-Awesome/web-fonts-with-css/css/fontawesome-all.min.css');
			
			// Desktop Menu
			$this->add_lib('head', 'js', asset_url() . 'others/smartmenus-1.0.1/jquery.smartmenus.js');
			$this->add_lib('head', 'css', asset_url() . 'others/smartmenus-1.0.1/css/sm-core-css.css');
			$this->add_lib('head', 'css', asset_url() . 'css/menu.css?q=' . rand());
			// Mobile Menu
			//$this->add_lib('head', 'js', asset_url().'others/mobile-menu/js/modernizr.custom.js');
			//$this->add_lib('head', 'js', asset_url().'others/mobile-menu/js/jquery.dlmenu.js');
			//$this->add_lib('head', 'css', asset_url().'others/mobile-menu/css/component.css');
			$this->add_lib('head', '', "<link rel='stylesheet' type='text/css' media='screen and (max-device-width: 1200px)' href='" . asset_url() . "css/mobile.css?q=" . rand() . "' />");
			$this->add_lib('head', 'css', asset_url() . "css/responsive.css?q=" . rand());
			
			$this->add_lib('foot', 'js', asset_url() . 'js/jquery.history.js');
			// Bootstrap
			$this->load_bootstrap();
			
			// Less
			$this->add_lib('head', 'js', asset_url() . 'others/less-min.js');
			// Google Recaptcha
			$this->add_lib('foot', 'js', 'https://www.google.com/recaptcha/api.js');
			
			// Sweet Alert / Notifications
			$this->add_lib('head', '', '<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>');
			$this->add_lib('head', 'js', asset_url() . 'others/notifyjs/dist/notify.js');
			
			// Multiselect dropdown
			$this->load_multiselect_dropdown();
			
			$this->add_lib('head', 'js', asset_url() . 'others/jquery.ui.widget.js');
			$this->add_lib('head', 'js', asset_url() . 'others/jquery.fileupload.js');
			$this->add_lib('head', 'js', asset_url() . 'others/jquery.today.js');
			
			// Typeahead
			//$this->add_lib('head', 'css', asset_url() . 'others/bootcomplete/bootcomplete.css');
			//$this->add_lib('head', 'js', asset_url() . 'others/bootcomplete/jquery.bootcomplete.js');
			$this->add_lib('head', 'js', asset_url() . 'others/EasyAutocomplete/dist/jquery.easy-autocomplete.min.js');
			$this->add_lib('head', 'css', asset_url() . 'others/EasyAutocomplete/dist/easy-autocomplete.min.css');
			
			// Own Styles
			$this->add_lib('foot', 'js', asset_url() . 'js/commons.js?v=' . rand());
			$this->add_lib('foot', 'css', asset_url() . 'css/loader.css?v=' . rand());
			$this->add_lib('foot', 'css', asset_url() . 'css/style.css?v=' . rand());
			$this->add_lib('foot', 'css', asset_url() . 'css/sticky_footer.css');
			$this->add_lib('foot', 'js', asset_url() . 'js/debugger.js?v=' . rand());
			$this->add_lib('foot', 'js', asset_url() . 'js/form_validation.js?v=' . rand());
			$this->add_lib('foot', 'js', asset_url() . 'js/my_cart.js?v=' . rand());
			
			$this->load_datatables();
		}
		
		protected function load_jquery()
		{
			$this->add_lib('head', '', '<script
		src="https://code.jquery.com/jquery-3.3.1.min.js"
		integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
		crossorigin="anonymous"></script>');
		}
		
		protected function load_datatables()
		{
			$this->add_lib('head', '', '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/af-2.2.2/b-1.5.1/b-colvis-1.5.1/b-flash-1.5.1/b-html5-1.5.1/b-print-1.5.1/cr-1.4.1/fc-3.2.4/fh-3.1.3/kt-2.3.2/r-2.2.1/rg-1.0.2/rr-1.2.3/sc-1.4.4/sl-1.2.5/datatables.min.css"/>');
			$this->add_lib('head', '', '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>');
			$this->add_lib('head', '', '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>');
			$this->add_lib('head', '', '<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/af-2.2.2/b-1.5.1/b-colvis-1.5.1/b-flash-1.5.1/b-html5-1.5.1/b-print-1.5.1/cr-1.4.1/fc-3.2.4/fh-3.1.3/kt-2.3.2/r-2.2.1/rg-1.0.2/rr-1.2.3/sc-1.4.4/sl-1.2.5/datatables.min.js"></script>');
			// Customs
			$this->add_lib('head', 'js', asset_url() . 'js/init_datatables.js?v=' . rand());
			$this->add_lib('head', 'css', asset_url() . 'css/my_datatables.css?v=' . rand());
		}
		
		protected function load_fontawesome()
		{
//        $this->add_lib('head', 'css', asset_url() . 'others/Font-Awesome-Pro/web-fonts-with-css/css/fontawesome-all.min.css');
			$this->add_lib('head', '', '<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.13.1/css/all.css" integrity="sha384-B9BoFFAuBaCfqw6lxWBZrhg/z4NkwqdBci+E+Sc2XlK/Rz25RYn8Fetb+Aw5irxa" crossorigin="anonymous">');
		}
		
		protected function load_print_libraries($datatables = false)
		{
			$this->add_lib('head', '', "<link rel='icon' type='image/ico' href='" . asset_url() . "images/favicon_b_32x32.png'>");
// 		$this->add_lib('head', 'js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js');
			$this->load_jquery();
			$this->load_bootstrap();
			$this->load_fontawesome();
			$this->add_lib('foot', 'css', asset_url() . 'css/style.css?v=' . rand());
			//$this->add_lib('head', '', '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">');
			//$this->add_lib('head', '', '<script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js" integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous"></script>');
			$this->add_lib('foot', 'css', asset_url() . 'css/price_list_print.css?v=' . rand());
			if ($datatables) $this->load_datatables();
		}
		
		protected function load_bootstrap()
		{
			$this->add_lib('foot', '', '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">');
			$this->add_lib('foot', '', '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>');
			$this->add_lib('foot', '', '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>');
		}
		
		protected function load_multiselect_dropdown()
		{
			$this->add_lib('foot', 'css', asset_url() . 'others/bootstrap-multiselect-0.9.13/css/bootstrap-multiselect.css');
			$this->add_lib('foot', 'js', asset_url() . 'others/bootstrap-multiselect-0.9.13/js/bootstrap-multiselect.js');
			$this->add_lib('foot', 'less', asset_url() . 'others/bootstrap-multiselect-0.9.13/less/bootstrap-multiselect.less');
			$this->add_lib('foot', 'js', asset_url() . 'js/init_dropdowns.js?v=' . rand());
		}
		
		protected function load_specsheet_libraries()
		{
			$this->add_lib('head', 'css', 'https://fonts.googleapis.com/css?family=Karla');
			$this->add_lib('foot', 'css', asset_url() . 'css/sticky_footer.css');

// 		$this->load_libraries();
			$this->load_jquery();
			$this->load_bootstrap();
			$this->add_lib('foot', 'css', asset_url() . 'css/style.css?v=' . rand());
		}
		
		function hasPermission($paramModule, $paramAction)
		{
			if ($this->data['user_id'] === '1') {
				// Is Admin
				return true;
			}
			
			if (!is_array($paramModule)) {
				$moduleArr = array($paramModule);
			}
			if (!is_array($paramAction)) {
				$actionArr = array($paramAction);
			}
			
			foreach ($this->data['permissionsList'] as $row) {
				foreach ($moduleArr as $module) {
					if ($row['module'] == $module) {
						foreach ($actionArr as $action) {
							if ($row['action'] == $action) {
								return true;
							}
						}
					}
				}
			}
			return false;
		}
		
		/*
		  Custom function to integrate the header, menu and footer
		*/
		
		function view($template_name, $data = null, $return = FALSE)
		{
			$this->data = (empty($data)) ? $this->data : $data;
			
			$this->load_libraries();
			$this->load->model('menu_model');
			$this->data['menu'] = $this->menu_model->get_menu($this->thisC, $this->data['is_showroom']);
			$this->data['submenu'] = array(); // $this->menu_model->get_submenu($this->thisC);
			$this->data['header_menu'] = $this->load->view('_header_menu', $this->data, true);
			$this->data['header_menu_mobile'] = $this->load->view('_header_menu_mobile', $this->data, true);
			$this->data['hasEditPermission'] = $this->hasPermission($this->thisC, 'edit');
			//$this->setRawData(); // View on top of the page, only for admin
			
			if ($return || $this->input->is_ajax_request()) {
				$header_crumb = $this->load->view('_header_crumb', $this->data, true);
				
				if (is_array($template_name)) {
					$content = '';
					foreach ($template_name as $t) {
						$content .= $this->load->view($t, $this->data, true);
					}
				} else {
					$content = $this->load->view($template_name, $this->data, true);
				}
				
				if ($this->input->is_ajax_request()) {
					$ret['html'] = $header_crumb . $content;
					echo json_encode($ret);
				} else {
					$header = $this->load->view('_header', $this->data, true);
					$footer = $this->load->view('_footer', $this->data, true);
					return $header . $header_crumb . $content . $footer;
				}
			} else {
				$this->load->view('_header', $this->data);
				$this->load->view('_header_crumb', $this->data);
				if (is_array($template_name)) {
					foreach ($template_name as $t) {
						$this->load->view($t, $this->data);
					}
				} else {
					$this->load->view($template_name, $this->data);
				}
				$this->load->view('_footer', $this->data);
			}
		}
		
		function table_template($tableId, $attr = " width='100%' ", $override = [])
		{
			$default_template = array(
				'table_open' => "<table id='$tableId' $attr  >",
				
				'thead_open' => '<thead>',
				'thead_close' => '</thead>',
				
				'heading_row_start' => '<tr>',
				'heading_row_end' => '</tr>',
				'heading_cell_start' => '<th>',
				'heading_cell_end' => '</th>',
				
				'tbody_open' => '<tbody>',
				'tbody_close' => '</tbody>',
				
				'row_start' => '<tr>',
				'row_end' => '</tr>',
				'cell_start' => '<td>',
				'cell_end' => '</td>',
				
				'row_alt_start' => '<tr>',
				'row_alt_end' => '</tr>',
				'cell_alt_start' => '<td>',
				'cell_alt_end' => '</td>',
				
				'table_close' => '</table>'
			);
			return array_merge($default_template, $override);
		}
		
		function add_lib($where, $type, $url)
		{
			array_push($this->data['library_' . $where], array('type' => $type, 'url' => $url));
		}
		
		function setRawData()
		{
			$this->data2 = $this->data;
			unset($this->data2['menu']);
			unset($this->data2['submenu']);
			unset($this->data2['header_menu']);
			unset($this->data2['header_menu_mobile']);
			unset($this->data2['library_foot']);
			unset($this->data2['library_head']);
			unset($this->data2['permissionsList']);
			
			switch ($this->thisC) {
				case 'product':
					unset($this->data2['form']);
					unset($this->data2['dropdown_product_status']);
					unset($this->data2['dropdown_stock_status']);
					unset($this->data2['dropdown_uses']);
					unset($this->data2['dropdown_weave']);
					unset($this->data2['dropdown_finish']);
					unset($this->data2['dropdown_cleaning']);
					unset($this->data2['dropdown_origin']);
					unset($this->data2['dropdown_vendor']);
			}
			$this->data['rawdata'] = $this->data2;
		}
		
		
		/*
		  For data validation before sending it to the view
		*/
		
		protected function return_datatables_data($table, $q, $extra = array())
		{
			return array(
				'draw' => $this->input->post('draw'),
				'recordsTotal' => (isset($q['recordsTotal']) ? $q['recordsTotal'] : 0),
				'recordsFiltered' => (isset($q['recordsFiltered']) ? $q['recordsFiltered'] : 0),
				'tableData' => $table,
				'sql' => (in_array(constant('ENVIRONMENT'), ['development', 'dev', 'local']) && isset($q['query']) ? $q['query'] : ''),
				'extra' => $extra
			);
		}
		
		function is_valid_spec_arr($data, $field = null)
		{
			$ret = true;
			if (count($data) > 0) {
				if (!is_null($field)) {
					foreach ($data as $d) {
						if (strlen($d[$field]) === 0 || !$this->is_valid_spec($d[$field])) {
							$ret = false; // Found one not permitted for the front end
						}
					}
				} else {
					foreach ($data as $d) {
						$ret = $this->is_valid_spec_str($d);
					}
				}
			} else {
				// No elements in array
				$ret = false;
			}
			return $ret;
		}
		
		function is_valid_spec_str($data)
		{
			if (strlen($data) > 0 && $this->is_valid_spec($data)) {
				return true;
			} else {
				return false;
			}
		}
		
		protected function is_valid_spec($val)
		{
			foreach ($this->not_valid_specs as $nv) {
				if ($nv === $val) {
					return false;
				}
			}
			return true;
		}
		
		protected function decode_array($arr, $field, $value)
		{
			// Changed destination of this function to the helpers/ folder
			return decode_array($arr, $field, $value);
		}
		
		protected function format_for_sql_idplus1($array = array(), $field = '')
		{
			$return = array();
			foreach ($array as $i) {
				array_push($return,
					array(
						'product_id' => $this->data['product_id'],
						$field => $i
					)
				);
			}
			return $return;
		}
		
		/*
		 * Image helper functions for all!
		 */
		
		function _save_beauty_shot($product_type, $product_id)
		{
		
		}
		
		function _save_item_hd($product_type, $product_id, $item_id)
		{
		
		}
		
		function _save_item_big($product_type, $product_id, $item_id)
		{
		
		}
		
		//public function convertLegacyImgSrcToS3($legacy_src){
		//	$s3_uri = 'https://opuzen-web-assets-public.s3.us-west-1.amazonaws.com/';
		//	$new_src = str_replace('https://www.opuzen.com/', $s3_uri, $legacy_src);
		//	return $new_src;
		//}
		
		/*
		  Log Error
		*/
		public function my_error_logging()
		{
			var_dump($_POST);
		}
		
	}

?>