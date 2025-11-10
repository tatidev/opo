<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends MY_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->thisC = 'reports';
		$this->load->model('Reports_model', 'model');
		$this->load->model('Specs_model', 'specs');
		$this->load->model('Search_model', 'search');
//		$this->model = $this->search;
		array_push($this->data['crumbs'], 'Reports');
		$this->load->library('table');
	}

	function index()
	{

	}

	/*************************
	 *
	 * Products Reports
	 *
	 **************************/
	function load_reports_libraries()
	{
		// Load libraries
		$this->load_jquery();
		$this->load_fontawesome();
		$this->load_datatables();
		$this->load_bootstrap();
		$this->load_multiselect_dropdown();
        $this->add_lib('head', 'js', asset_url() . 'others/notifyjs/dist/notify.js');
		$this->add_lib('head', '', '<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>');
		$this->add_lib('foot', 'js', asset_url() . 'js/commons.js?v=' . rand());
		$this->add_lib('foot', 'css', asset_url() . 'css/loader.css?v=' . rand());
		$this->add_lib('foot', 'css', asset_url() . 'css/style.css?v=' . rand());

		$this->data['stamps'] = $this->get_stamps(item);
	}

	function load_reports_filters()
	{
		switch ($this->report_type) {
			case 'items':
				// Defaults
				$this->data['title'] = 'Reports Generator';

				// Filters
				$this->data['options']['status'] = $this->decode_array($this->specs->get_product_status(), 'id', 'descr');
				$this->data['options']['stock_status'] = $this->decode_array($this->specs->get_stock_status(), 'id', 'descr');
				$this->data['options']['shelf'] = $this->decode_array($this->specs->get_shelfs(), 'id', 'name') + array('none' => 'No shelf');
				$this->data['options']['vendor'] = $this->decode_array($this->specs->get_vendors(), 'id', 'name');
				$all_lists = $this->decode_array($this->specs->get_lists(), 'id', 'name');
			
				$list_firecodes = $this->decode_array($this->specs->get_firecodes(), 'id', 'name');
				$list_front_contents = $this->decode_array($this->specs->get_p_front_contents(), 'id', 'name');
				
				$this->data['options']['list_include'] = $all_lists;
				$this->data['options']['list_exclude'] = $all_lists;
				$this->data['options']['list_firecodes'] = $list_firecodes??[];
				$this->data['options']['list_front_contents'] = $list_front_contents??[];
				$this->data['options']['showroom'] = $this->decode_array($this->specs->get_showrooms(), 'id', 'name');
				$this->data['options']['weave'] = $this->decode_array($this->specs->get_weaves(), 'id', 'name');
                $this->data['options']['cleaning'] = $this->decode_array($this->specs->get_cleanings(), 'id', 'name');
                $this->data['options']['finish'] = $this->decode_array($this->specs->get_finishs(), 'id', 'name');

				$this->data['filters'] =
				  array(
					array(
					  'field_name' => 'Status',
					  'input' => form_multiselect(
						'status_id[]', $this->data['options']['status'], set_value('status_id[]', array()), " class='multi-dropdown input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Stock Status',
					  'input' => form_multiselect(
						'stock_status_id[]', $this->data['options']['stock_status'], set_value('stock_status_id[]', array()), " class='multi-dropdown input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Shelfs',
					  'input' => form_multiselect(
						'shelf_id[]', $this->data['options']['shelf'], set_value('shelf_id[]', array()), " class='multi-dropdown input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Stock Quantities',
					  'input' => form_input(array('type' => 'number', 'name' => 'stock_min', 'min' => '0', 'max' => '999999', 'class' => ' input-filter')) . "< Yards Available < " .
						form_input(array('type' => 'number', 'name' => 'stock_max', 'min' => '0', 'max' => '999999', 'class' => ' input-filter')) . " "
					),
					array(
					  'field_name' => 'Weaves',
					  'input' => form_multiselect(
						'weave_id[]', $this->data['options']['weave'], set_value('weave_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
					  )
					),
                      array(
                          'field_name' => 'Cleanings',
                          'input' => form_multiselect(
                              'cleaning_id[]', $this->data['options']['cleaning'], set_value('cleaning_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
                          )
                      ),
                      array(
                          'field_name' => 'Finish',
                          'input' => form_multiselect(
                              'finish_id[]', $this->data['options']['finish'], set_value('finish_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
                          )
                      ),
					array(
					  'field_name' => 'Vendors',
					  'input' => form_multiselect(
						'vendor_id[]', $this->data['options']['vendor'], set_value('vendor_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Website',
					  'input' => form_multiselect(
						'website[]', array(
						'web_visible_yes' => 'Visible: Yes',
						'web_visible_no' => 'Visible: No',
						'web_image_yes' => 'Image available: Yes',
						'web_image_no' => 'Image available: No',
						'missing_description' => 'Missing description',
					  ), set_value('website[]', array()), " class='multi-dropdown input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Group by',
					  'input' => form_multiselect(
						'group_by[]', array(
						item => 'by Item',
						product => 'by Product',
						'include_digital' => 'incl Digital Products'
					  ), set_value('group_by[]', array(item)), " class='multi-dropdown input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Showrooms',
					  'input' => form_multiselect(
						'showroom_id[]', $this->data['options']['showroom'], set_value('showroom_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Lists Include',
					  'input' => form_multiselect(
						'list_include_id[]', $this->data['options']['list_include'], set_value('list_include_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Lists Exclude',
					  'input' => form_multiselect(
						'list_exclude_id[]', $this->data['options']['list_exclude'], set_value('list_exclude_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'FireCodes',
					  'input' => form_multiselect(
						'list_firecodes_id[]', $this->data['options']['list_firecodes'], set_value('list_firecodes_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
					  )
					),
					array(
					  'field_name' => 'Front Content',
					  'input' => form_multiselect(
						'list_front_contents_id[]', $this->data['options']['list_front_contents'], set_value('list_front_contents_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
					  )
					),

				  );
				if (ENVIRONMENT === 'development' || ENVIRONMENT === 'dev') {
					$this->data['filters'][] = [
					  'field_name' => 'SQL View',
					  'input' => form_input(array('type' => 'checkbox', 'name' => 'get_compiled_select', 'value' => 'yes'))
					];
				}
				break;

			case 'pricing':

				// Defaults
				$this->data['title'] = 'Price Changes';

				$this->data['options']['status'] = $this->decode_array($this->specs->get_product_status(), 'id', 'descr');
				$this->data['options']['stock_status'] = $this->decode_array($this->specs->get_stock_status(), 'id', 'descr');
				//       $this->data['options']['shelf'] = $this->decode_array( $this->specs->get_shelfs(), 'id', 'name' ) + array('none'=>'No shelf');
				$this->data['options']['vendor'] = $this->decode_array($this->specs->get_vendors(), 'id', 'name');
				//       $this->data['options']['list'] = $this->decode_array( $this->specs->get_lists(), 'id', 'name');
				//       $this->data['options']['showroom'] = $this->decode_array( $this->specs->get_showrooms(), 'id', 'name');
				$this->data['options']['weave'] = $this->decode_array($this->specs->get_weaves(), 'id', 'name');

				$this->data['filters'] = array(
//           array(
//             'field_name' => 'Status',
//             'input' => form_multiselect(
//               'status_id[]', $this->data['options']['status'], set_value('status_id[]', array()), " class='multi-dropdown' tabindex='-1' "
//             )
//           ),
//           array(
//             'field_name' => 'Stock Status',
//             'input' => form_multiselect(
//               'stock_status_id[]', $this->data['options']['stock_status'], set_value('stock_status_id[]', array()), " class='multi-dropdown' tabindex='-1' "
//             )
//           ),
					//         array(
					//           'field_name' => 'Shelfs',
					//           'input' => form_multiselect(
					//             'shelf_id[]', $this->data['options']['shelf'], set_value('shelf_id[]', array()), " class='multi-dropdown' tabindex='-1' "
					//           )
					//         ),
//           array(
//             'field_name' => 'Stock Quantities',
//             'input' => form_input(array('type'=>'number','name'=>'stock_min', 'min'=>'0', 'max'=>'999999'))."< Yards Available < ".
//                        form_input(array('type'=>'number','name'=>'stock_max', 'min'=>'0', 'max'=>'999999'))." "
//           ),
//           array(
//             'field_name' => 'Weaves',
//             'input' => form_multiselect(
//               'weave_id[]', $this->data['options']['weave'], set_value('weave_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
//             )
//           ),
				  array(
					'field_name' => 'Vendors',
					'input' => form_multiselect(
					  'vendor_id[]', $this->data['options']['vendor'], set_value('vendor_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
					)
				  ),
				  array(
					'row_class' => "w-50",
					'field_name' => 'By Dates',
					'field_class' => 'col-xs-12 col-sm-2',
					'input' => "<input type='date' name='date_from'> < Update < <input type='date' name='date_to'>",
					'input_class' => 'col-xs-12 col-sm-10 px-4'
				  ),
					//         array(
					//           'field_name' => 'Website',
					//           'input' => form_multiselect(
					//             'website[]', array(
					//               'web_visible_yes' => 'Visible: Yes',
					//               'web_visible_no' => 'Visible: No',
					//               'web_image_yes' => 'Image available: Yes',
					//               'web_image_no' => 'Image available: No',
					//               'missing_description' => 'Missing description',
					//             ), set_value('website[]', array()), " class='multi-dropdown' tabindex='-1' "
					//           )
					//         ),
					//         array(
					//           'field_name' => 'Group by',
					//           'input' => form_multiselect(
					//             'group_by[]', array(
					//               item => 'by Item',
					//               product => 'by Product',
					//               'include_digital' => 'incl Digital Products'
					//             ), set_value('group_by[]', array(item)), " class='multi-dropdown' tabindex='-1' "
					//           )
					//         ),
					//         array(
					//           'field_name' => 'Lists',
					//           'input' => form_multiselect(
					//             'list_id[]', $this->data['options']['list'], set_value('list_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
					//           )
					//         ),
					//         array(
					//           'field_name' => 'Showrooms',
					//           'input' => form_multiselect(
					//             'showroom_id[]', $this->data['options']['showroom'], set_value('showroom_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
					//           )
					//         )
				);
				if (ENVIRONMENT === 'development') {
					array_push($this->data['filters'],
					  array(
						'field_name' => 'SQL View',
						'input' => form_input(array('type' => 'checkbox', 'name' => 'get_compiled_select', 'value' => 'yes'))
					  )
					);
				}
				break;

            case 'shelfs':
                // Defaults
                $this->data['title'] = 'Shelfs Changes';

//                $this->data['options']['status'] = $this->decode_array($this->specs->get_product_status(), 'id', 'descr');
//                $this->data['options']['stock_status'] = $this->decode_array($this->specs->get_stock_status(), 'id', 'descr');
               $this->data['options']['shelf'] = $this->decode_array( $this->specs->get_shelfs(), 'id', 'name' ) + array('none'=>'No shelf');
                $this->data['options']['vendor'] = $this->decode_array($this->specs->get_vendors(), 'id', 'name');
                //       $this->data['options']['list'] = $this->decode_array( $this->specs->get_lists(), 'id', 'name');
                //       $this->data['options']['showroom'] = $this->decode_array( $this->specs->get_showrooms(), 'id', 'name');
//                $this->data['options']['weave'] = $this->decode_array($this->specs->get_weaves(), 'id', 'name');

                $this->data['filters'] = array(
//           array(
//             'field_name' => 'Status',
//             'input' => form_multiselect(
//               'status_id[]', $this->data['options']['status'], set_value('status_id[]', array()), " class='multi-dropdown' tabindex='-1' "
//             )
//           ),
//           array(
//             'field_name' => 'Stock Status',
//             'input' => form_multiselect(
//               'stock_status_id[]', $this->data['options']['stock_status'], set_value('stock_status_id[]', array()), " class='multi-dropdown' tabindex='-1' "
//             )
//           ),
                     array(
                       'field_name' => 'Shelfs',
                       'input' => form_multiselect(
                         'shelf_id[]', $this->data['options']['shelf'], set_value('shelf_id[]', array()), " class='multi-dropdown' tabindex='-1' "
                       )
                     ),
//           array(
//             'field_name' => 'Stock Quantities',
//             'input' => form_input(array('type'=>'number','name'=>'stock_min', 'min'=>'0', 'max'=>'999999'))."< Yards Available < ".
//                        form_input(array('type'=>'number','name'=>'stock_max', 'min'=>'0', 'max'=>'999999'))." "
//           ),
//           array(
//             'field_name' => 'Weaves',
//             'input' => form_multiselect(
//               'weave_id[]', $this->data['options']['weave'], set_value('weave_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
//             )
//           ),
                    array(
                        'field_name' => 'Vendors',
                        'input' => form_multiselect(
                            'vendor_id[]', $this->data['options']['vendor'], set_value('vendor_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
                        )
                    ),
                    array(
                        'row_class' => "",
                        'field_name' => 'By Dates',
                        'field_class' => 'col-xs-12 col-sm-2',
                        'input' => "<input type='date' name='date_from' value='".date("Y-m-d", strtotime("-1 months"))."'> < Update < <input type='date' name='date_to' value='".date("Y-m-d")."'>",
                        'input_class' => 'col-xs-12 col-sm-10 px-4'
                    ),
                    //         array(
                    //           'field_name' => 'Website',
                    //           'input' => form_multiselect(
                    //             'website[]', array(
                    //               'web_visible_yes' => 'Visible: Yes',
                    //               'web_visible_no' => 'Visible: No',
                    //               'web_image_yes' => 'Image available: Yes',
                    //               'web_image_no' => 'Image available: No',
                    //               'missing_description' => 'Missing description',
                    //             ), set_value('website[]', array()), " class='multi-dropdown' tabindex='-1' "
                    //           )
                    //         ),
                     array(
                       'field_name' => 'Group by',
                       'input' => form_multiselect(
                         'group_by[]', array(
                           item => 'by Item',
                           product => 'by Product',
                         ), set_value('group_by[]', array(item)), " class='multi-dropdown' tabindex='-1' "
                       )
                     ),
                    array(
                        'field_name' => 'Change type',
                        'input' => implode("<br/>", [
                            "Current " . form_radio("change_type", "add", true),
                            "Removals " . form_radio("change_type", "removals", false)
                        ])
                    ),
                    //         array(
                    //           'field_name' => 'Lists',
                    //           'input' => form_multiselect(
                    //             'list_id[]', $this->data['options']['list'], set_value('list_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
                    //           )
                    //         ),
                    //         array(
                    //           'field_name' => 'Showrooms',
                    //           'input' => form_multiselect(
                    //             'showroom_id[]', $this->data['options']['showroom'], set_value('showroom_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
                    //           )
                    //         )
                );
                if (ENVIRONMENT === 'development') {
                    array_push($this->data['filters'],
                        array(
                            'field_name' => 'SQL View',
                            'input' => form_input(array('type' => 'checkbox', 'name' => 'get_compiled_select', 'value' => 'yes'))
                        )
                    );
                }
                break;

			case 'disco':

				// Defaults
				$this->data['title'] = 'Discontinued';
                $this->data['options']['vendor'] = $this->decode_array($this->specs->get_vendors(), 'id', 'name');
				$this->data['options']['status'] = $this->decode_array($this->specs->get_product_status(), 'id', 'descr');
				$this->data['options']['stock_status'] = $this->decode_array($this->specs->get_stock_status(), 'id', 'descr');
		        $this->data['options']['list_include'] = $this->decode_array( $this->specs->get_lists(), 'id', 'name');
		        $this->data['options']['showroom'] = $this->decode_array( $this->specs->get_showrooms(), 'id', 'name');

				$today = date('Y-m-d');
				$a_month_ago = date('Y-m-d', strtotime($today . " -1 months"));
//				echo $a_month_ago . " to ". $today;

				$this->data['filters'] = array(
				  array(
					'row_class' => "w-100",
					'field_name' => 'By Dates',
					'field_class' => 'col-xs-12 col-sm-2',
					'input' => form_input(array('type' => 'date', 'name' => 'date_from', 'class' => ' input-filter ', 'value' => $a_month_ago)) . "< Date Status Changed < " .
					  form_input(array('type' => 'date', 'name' => 'date_to', 'class' => ' input-filter ', 'value' => $today)) . " ",
					'input_class' => 'col-xs-12 col-sm-10 px-4'
				  ),
		           array(
		             'field_name' => 'Status changed to',
		             'input' => form_multiselect(
		               'status_id[]', $this->data['options']['status'], set_value('status_id[]', array()), " class='multi-dropdown input-filter' tabindex='-1' "
		             )
		           ),
//		           array(
//		             'field_name' => 'Stock Status',
//		             'input' => form_multiselect(
//		               'stock_status_id[]', $this->data['options']['stock_status'], set_value('stock_status_id[]', array()), " class='multi-dropdown' tabindex='-1' "
//		             )
//		           ),
//				  array(
//					'field_name' => 'Vendors',
//					'input' => form_multiselect(
//					  'vendor_id[]', $this->data['options']['vendor'], set_value('vendor_id[]', array()), " class='multi-dropdown w-filtering' tabindex='-1' "
//					)
//				  ),
//				  array(
//					'field_name' => 'Showrooms',
//					'input' => form_multiselect(
//					  'showroom_id[]', $this->data['options']['showroom'], set_value('showroom_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
//					)
//				  ),
//				  array(
//					'field_name' => 'Lists Include',
//					'input' => form_multiselect(
//					  'list_include_id[]', $this->data['options']['list_include'], set_value('list_include_id[]', array()), " class='multi-dropdown w-filtering input-filter' tabindex='-1' "
//					)
//				  ),

				);
				if (ENVIRONMENT === 'development') {
					array_push($this->data['filters'],
					  array(
						'field_name' => 'SQL View',
						'input' => form_input(array('type' => 'checkbox', 'name' => 'get_compiled_select', 'value' => 'yes'))
					  )
					);
				}
				break;
     
				case 'msowto':

					// Defaults
					$this->data['title'] = 'MSO & WTO';
					$this->data['options']['vendor'] = $this->decode_array($this->specs->get_vendors(), 'id', 'name');
					$this->data['options']['status'] = $this->decode_array($this->specs->get_product_status(), 'id', 'descr');
					$this->data['options']['stock_status'] = $this->decode_array($this->specs->get_stock_status(), 'id', 'descr');
					$this->data['options']['list_include'] = $this->decode_array( $this->specs->get_lists(), 'id', 'name');
					$this->data['options']['showroom'] = $this->decode_array( $this->specs->get_showrooms(), 'id', 'name');
	
					$today = date('Y-m-d');
					$a_month_ago = date('Y-m-d', strtotime($today . " -1 months"));
	
					$this->data['filters'] = array(
					  array(
						'row_class' => "w-100",
						'field_name' => 'By Dates',
						'field_class' => 'col-xs-12 col-sm-2',
						'input' => form_input(array('type' => 'date', 'name' => 'date_from', 'class' => ' input-filter ', 'value' => $a_month_ago)) . "< Date Status Changed < " .
						  form_input(array('type' => 'date', 'name' => 'date_to', 'class' => ' input-filter ', 'value' => $today)) . " ",
						'input_class' => 'col-xs-12 col-sm-10 px-4'
					  ),
					   array(
						 'field_name' => 'Status changed to',
						 'input' => form_multiselect(
						   'status_id[]', $this->data['options']['status'], set_value('status_id[]', array()), " class='multi-dropdown input-filter' tabindex='-1' "
						 )
					   ),
					);
					break;




			case 'popular_items':
				$this->data['title'] = 'Popular Items';

				$this->data['options']['showrooms'] = $this->decode_array($this->specs->get_showrooms(), 'id', 'name');
				$this->data['options']['roadkits'] = $this->decode_array($this->specs->get_lists('Y'), 'id', 'name'); // 'Y': get only active ones in Roadkit App

				$this->data['filters'] = [
				  array(
					'field_name' => 'Min. Qty.',
					'input' => form_input(array(
						'name' => 'min_qty',
						'id' => 'min_qty',
						'type' => 'number',
						'min' => '1',
						'max' => '99',
						'maxlength' => '2',
						'value' => set_value('min_qty', '10'),
						'class' => 'form-control w-100'
					  )
					)
				  ),
				  array(
					'field_name' => 'Showrooms',
					'input' => form_multiselect('showroom_ids[]', $this->data['options']['showrooms'], set_value('showroom_ids[]', []), " class='multi-dropdown w-filtering' tabindex='-1' ")
				  ),
				  array(
					'field_name' => 'Roadkits',
					'input' => form_multiselect('roadkit_ids[]', $this->data['options']['roadkits'], set_value('roadkit_ids[]', []), " class='multi-dropdown w-filtering' tabindex='-1' ")
				  ),
				  array(
					'field_name' => 'Date From',
					'input' => form_input(array(
						'name' => 'date_from',
						'id' => 'date_from',
						'type' => 'date',
						'value' => set_value('date_from', null),
						'class' => 'form-control w-100'
					  )
					)
				  ),
				  array(
					'field_name' => 'Date To',
					'input' => form_input(array(
						'name' => 'date_to',
						'id' => 'date_to',
						'type' => 'date',
						'value' => set_value('date_to', null),
						'class' => 'form-control w-100'
					  )
					)
				  ),
				  array(
				    'field_name' => 'SQL View',
				    'input' => form_input(array('type' => 'checkbox', 'name' => 'get_compiled_select', 'value' => 'yes'))
				  )

				];
		}
	}

	function items($return = false)
	{
		$this->report_type = 'items';
		if ($this->input->is_ajax_request()) {
			$data = [];
			// Response
			if (count($_POST) > 0) {

				$website = $this->input->post('website');
				if (is_null($website)) $website = array();
				$web_visible = in_array('web_visible_yes', $website) ? 'Y' : (in_array('web_visible_no', $website) ? 'N' : null);
				$web_image = in_array('web_image_yes', $website) ? 'Y' : (in_array('web_image_no', $website) ? 'N' : null);
				$web_missing_description = in_array('missing_description', $website);
				$restrictRegulars = !is_null($web_visible) || !is_null($web_image) || $web_missing_description;
				// $list_firecodes_id = $this->input->post('list_firecodes_id[]');
				// $list_front_contents_id = $this->input->post('list_front_contents_id[]');
				$group_by = $this->input->post('group_by');
				if (is_null($group_by)) $group_by = array(item);
				$includeDigital = in_array('include_digital', $group_by);

				$query_group_by = in_array(item, $group_by) ? item : (in_array(product, $group_by) ? product : item);
				$query_group_by = $web_missing_description ? product : $query_group_by; // Force by product when DESCRIPTION is needed
//	            $query_group_by = count($this->input->post('list_exclude_id[]')) > 0 ? item : $query_group_by; // Force ITEM if exclusion is being used

				$list_include_id = set_value('list_include_id[]', null);
				$in_master = !is_null($list_include_id) ? in_array('0', $list_include_id) : null;
				$list_include_id = !is_null($list_include_id) ? array_diff($list_include_id, ["0"]) : null; # take out LIST_ID 0

				$searchfilters = array(
				  'group_by' => $query_group_by,
				  'select' => ['status', 'stock_status', 'shelf', 'price', 'stock', 'showcase', 'url_title', 'vendor'],
				  'restrictType' => (!$restrictRegulars && $includeDigital ? array(Regular, Digital) : array(Regular)),
				  'includeDiscontinued' => true,
				  'status_id' => set_value('status_id[]', null),
				  'stock_status_id' => set_value('stock_status_id[]', null),
				  'list_front_contents_id' => set_value('list_front_contents_id[]', null),
				  'list_firecodes_id' => set_value('list_firecodes_id[]', null),
				  'shelf_id' => set_value('shelf_id[]', null),
				  'stock' => [
					'min' => strlen($this->input->post('stock_min')) > 0 ? $this->input->post('stock_min') : null,
					'max' => strlen($this->input->post('stock_max')) > 0 ? $this->input->post('stock_max') : null
				  ],
				  'showcase' => (
				  $restrictRegulars ? [
					'web_visible' => $web_visible,
					'web_image' => $web_image,
					'missing_description' => $web_missing_description
				  ] : true
				  ),
                    'vendor_id' => set_value('vendor_id[]', true),
                    'finish_id' => set_value('finish_id[]', true),
				  'weave_id' => set_value('weave_id[]', null),
                    'cleaning_id' => set_value('cleaning_id[]', null),
				  'in_master' => $in_master,
				  'list' => [
					'id' => $list_include_id,
					'active' => ($list_include_id !== null),
					'showroom' => set_value('showroom_id[]', null),
					'exclude_id' => set_value('list_exclude_id[]', null)
				  ],
				  'get_compiled_select' => !is_null($this->input->post('get_compiled_select'))
//	                'debug' => true
				);

				$data['tableData'] = $this->search->do_search($searchfilters);
//                echo $this->db->last_query();
//                exit();
				if ($this->search->filters['get_compiled_select']) {
					echo $data['tableData'];
					return;
				}
			} else {
				$data['tableData'] = [];
			}
			$data['stamps'] = $this->get_stamps($query_group_by);
			echo json_encode($data);
		} else {
			$this->load_reports_libraries();
			$this->load_reports_filters();

			$this->data['ajaxUrl'] = site_url('reports/items');
			$this->load->view('reports/items_filterer_view', $this->data);
		}
	}

	function disco($return = false)
	{
		$this->report_type = 'disco';
		if ($this->input->is_ajax_request()) {
//			var_dump($_POST); exit;
			// Response
			$data = [];
			if (count($_POST) > 0) {
				$data['tableData'] = $this->model->search_discontinues([
				    'date_from' => set_value('date_from', null),
					'date_to' => set_value('date_to', null),
					'status_id' => set_value('status_id', null)
				]);
				if ($this->input->post('get_compiled_select')) {
					echo $this->model->db->last_query();
					echo $data;
					return;
				}
			} else {
				$data = array('tableData' => array());
			}
			echo json_encode($data);
		} else {
			$this->load_reports_libraries();
			$this->load_reports_filters();

			$this->data['ajaxUrl'] = site_url('reports/disco');
			$this->load->view('reports/items_status_change_view', $this->data);
		}
	}

	function msowto($return = false)
	{
		$this->report_type = 'msowto';
		if ($this->input->is_ajax_request()) {
//			var_dump($_POST); exit;
			// Response
			$data = [];
			if (count($_POST) > 0) {
				$data['tableData'] = $this->model->search_mso_wto([
				    'date_from' => set_value('date_from', null),
					'date_to' => set_value('date_to', null),
					'status_id' => set_value('status_id', null)
				]);
				if ($this->input->post('get_compiled_select')) {
					echo $this->model->db->last_query();
					echo $data;
					return;
				}
			} else {
				$data = array('tableData' => array());
			}
			echo json_encode($data);
		} else {
			$this->load_reports_libraries();
			$this->load_reports_filters();

			$this->data['ajaxUrl'] = site_url('reports/msowto');
			$this->load->view('reports/items_status_change_view', $this->data);
		}
	}

	function pricing($return = false)
	{
		$this->report_type = 'pricing';
		if ($this->input->is_ajax_request()) {
			// Response
			if (count($_POST) > 0) {

				$searchfilters = array(
				  'group_by' => product,
				  'select' => ['status', 'stock_status', 'shelf', 'price', 'costs', 'vendor', 'weave'],
				  'restrictType' => array(Regular), //( !$restrictRegulars && $includeDigital ? array(Regular, Digital) : array(Regular) ),
				  'includeDiscontinued' => true,
				  'status_id' => set_value('status_id[]', null),
				  'stock_status_id' => set_value('stock_status_id[]', null),
				  'shelf_id' => set_value('shelf_id[]', true),
//             'includePrice' => true,
//             'includeCosts' => true,
				  'dateRanges' => array(
					'from' => strlen($this->input->post('date_from')) > 0 ? $this->input->post('date_from') : null,
					'to' => strlen($this->input->post('date_to')) > 0 ? $this->input->post('date_to') : null
				  ),
//             'includeStock' => array(  'min'=> strlen($this->input->post('stock_min')) > 0 ? $this->input->post('stock_min') : null,
//                                       'max'=> strlen($this->input->post('stock_max')) > 0 ? $this->input->post('stock_max') : null
//                                    ),
//             'includeShowcase' => ( $restrictRegulars ? 
//                                       array( 'web_visible' => $web_visible,
//                                              'web_image' => $web_image,
//                                              'missing_description' => $web_missing_description
//                                            ) : 
//                                       true 
//                                  ),
				  'vendor_id' => set_value('vendor_id[]', true),
				  'weave_id' => set_value('weave_id[]', null),
//             'list' => array(
//               'id' => set_value('list_id[]', null),
//               'active' => ( set_value('list_id[]', null) !== null ),
//               'showroom' => set_value('showroom_id[]', null)
//             ),
				  'get_compiled_select' => !is_null($this->input->post('get_compiled_select'))
				);
//         var_dump($searchfilters);exit;
				$data = $this->search->do_search($searchfilters);
				if ($this->search->filters['get_compiled_select']) {
					echo $data;
					return;
				}
			} else {
				$data = array('tableData' => array());
			}
			echo json_encode($data);
		} else {
			$this->load_reports_libraries();
			$this->load_reports_filters();

			$this->data['ajaxUrl'] = site_url('reports/pricing');
			$this->load->view('reports/price_changes_view', $this->data);
		}
	}

    function shelfs($return = false)
    {
//        var_dump($_POST); exit();

        $this->report_type = 'shelfs';
        if ($this->input->is_ajax_request()) {
            // Response
            if (count($_POST) > 0) {

                $group_by = $this->input->post('group_by');
                if (is_null($group_by)) $group_by = array(item);
                $query_group_by = (in_array(product, $group_by) ? product : item);
//
//                $searchfilters = array(
//                    'group_by' => $query_group_by,
////                    'select' => ['status', 'stock_status', 'shelf', 'price', 'costs', 'vendor', 'weave'],
//                    'select' => ['status', 'stock_status', 'shelf', 'vendor', 'weave'],
//                    'restrictType' => array(Regular), //( !$restrictRegulars && $includeDigital ? array(Regular, Digital) : array(Regular) ),
//                    'includeDiscontinued' => true,
//                    'status_id' => set_value('status_id[]', null),
//                    'stock_status_id' => set_value('stock_status_id[]', null),
//                    'shelf_id' => set_value('shelf_id[]', true),
//                    'dateRanges' => array(
//                        'table_attr' => "{$this->search->t_item_shelf}.date_add",
//                        'from' => strlen($this->input->post('date_from')) > 0 ? $this->input->post('date_from') : null,
//                        'to' => strlen($this->input->post('date_to')) > 0 ? $this->input->post('date_to') : null
//                    ),
//                    'vendor_id' => set_value('vendor_id[]', true),
//                    'weave_id' => set_value('weave_id[]', null),
////             'list' => array(
////               'id' => set_value('list_id[]', null),
////               'active' => ( set_value('list_id[]', null) !== null ),
////               'showroom' => set_value('showroom_id[]', null)
////             ),
//                    'get_compiled_select' => !is_null($this->input->post('get_compiled_select')),
//                    'calculateHistory' => true
//                );
//                var_dump($searchfilters);exit;
//
//                $data = $this->search->do_search($searchfilters);
//                if ($this->search->filters['get_compiled_select']) {
//                    echo $data;
//                    return;
//                }
//                echo $this->model->db->last_query();

                $this->filters = [
                    "date_from" => $this->input->post("date_from"),
                    "date_to" => $this->input->post("date_to"),
                    "group_by" => $query_group_by,
                    "vendor_id" => $this->input->post("vendor_id"),
                    "shelf_id" => $this->input->post("shelf_id")
                ];

                if($this->input->post("change_type") == "add"){
                    $data = $this->model->get_shelf_additions($this->filters);
                }
                else if ($this->input->post("change_type") == "removals"){
                    $data = $this->model->get_shelf_removals($this->filters);
                }

                $shelfs_data = $this->specs->get_shelfs();
                $shelf_id_to_name = [];
                foreach($shelfs_data as $s){
                    $shelf_id_to_name[$s["id"]] = $s["name"];
                }

                // Inject shelf names
                foreach($data as &$d){
                    $shelfsIds = explode(",", $d["shelf_id"]);
                    $aux = [];
                    foreach($shelfsIds as $sId){
                        $aux[] = $shelf_id_to_name[$sId];
                    }
                    unset($d["shelf_id"]);
                    $d["shelf"] = implode(", ", $aux);
                }

            } else {
                $data = array('tableData' => array());
            }
            echo json_encode($data);
            return;
        } else {
            $this->load_reports_libraries();
            $this->load_reports_filters();

            $this->data['ajaxUrl'] = site_url('reports/shelfs');
            $this->load->view('reports/shelfs_changes_view', $this->data);
        }
    }

    /*************************
	 *
	 * Roadkits Reports
	 *
	 **************************/

	function popular_items()
	{
		$this->report_type = 'popular_items';
		if ($this->input->is_ajax_request()) {
			$data = [];
			if (count($_POST) > 0) {

				// Process query
				$filters = [
				  'showroom_ids' => (is_array($this->input->post('showroom_ids')) ? $this->input->post('showroom_ids') : []),
				  'roadkit_ids' => (is_array($this->input->post('roadkit_ids')) ? $this->input->post('roadkit_ids') : []),
				  'min_qty' => ($this->input->post('min_qty') !== null ? $this->input->post('min_qty') : 10),
				  'dateRanges' => [
				    'from' => ($this->input->post('date_from') !== null && $this->input->post('date_from') !== '' ? $this->input->post('date_from') : null),
				    'to' => ($this->input->post('date_to') !== null && $this->input->post('date_to') !== '' ? $this->input->post('date_to') : null)
				  ],
				  'get_compiled_select' => !is_null($this->input->post('get_compiled_select'))
				];

				$data['tableData'] = $this->model->get_popular_items($filters);
			} else {
				$data['tableData'] = [];
			}

			if( $this->model->filters['get_compiled_select'] ){
				echo $data['tableData'];
			} else {
				echo json_encode($data);
			}

		} else {
			$this->load_reports_libraries();
			$this->load_reports_filters();

			$this->data['ajaxUrl'] = site_url('reports/popular_items');
			$this->load->view('reports/popular_items_view', $this->data);
		}
	}

	function image_sizes(){
	
	}

	/**
	 * Web Visibility Dashboard
	 * Displays metrics and statistics for product and item web visibility
	 */
	function web_visibility_dashboard()
	{
		// Load libraries
		$this->load_reports_libraries();

		// Set page title
		$this->data['title'] = 'Web Visibility Dashboard';

		// Get product visibility metrics
		$this->data['product_metrics'] = $this->get_product_visibility_metrics();

		// Get item visibility metrics
		$this->data['item_metrics'] = $this->get_item_visibility_metrics();

		// Get data quality issues
		$this->data['data_quality'] = $this->get_data_quality_issues();

		// Load the view directly (not using template wrapper like other reports)
		$this->load->view('reports/web_visibility_dashboard', $this->data);
	}

	/**
	 * Get product visibility metrics
	 */
	private function get_product_visibility_metrics()
	{
		$query = $this->db->query("
			SELECT 
				COUNT(*) as total_products,
				SUM(CASE WHEN sp.visible = 'Y' THEN 1 ELSE 0 END) as visible_on_web,
				SUM(CASE WHEN sp.visible = 'N' THEN 1 ELSE 0 END) as hidden,
				SUM(CASE WHEN sp.visible IS NULL THEN 1 ELSE 0 END) as not_configured,
				SUM(CASE WHEN sp.visible = 'Y' AND sp.pic_big_url IS NOT NULL AND sp.pic_big_url != '' THEN 1 ELSE 0 END) as visible_with_beauty_shot,
				SUM(CASE WHEN sp.visible = 'Y' AND (sp.pic_big_url IS NULL OR sp.pic_big_url = '') THEN 1 ELSE 0 END) as visible_without_beauty_shot,
				SUM(CASE WHEN sp.pic_big_url IS NOT NULL AND sp.pic_big_url != '' THEN 1 ELSE 0 END) as total_with_beauty_shot
			FROM T_PRODUCT p
			LEFT JOIN SHOWCASE_PRODUCT sp ON p.id = sp.product_id
			WHERE p.archived = 'N'
		");

		return $query->row_array();
	}

	/**
	 * Get item visibility metrics
	 */
	private function get_item_visibility_metrics()
	{
		// Overall metrics
		$overall_query = $this->db->query("
			SELECT 
				COUNT(*) as total_items,
				SUM(CASE WHEN i.web_vis = 1 THEN 1 ELSE 0 END) as visible_on_web,
				SUM(CASE WHEN i.web_vis = 0 THEN 1 ELSE 0 END) as hidden,
				SUM(CASE WHEN i.web_vis IS NULL THEN 1 ELSE 0 END) as not_calculated,
				SUM(CASE WHEN i.web_vis_toggle = 1 THEN 1 ELSE 0 END) as manual_override_active
			FROM T_ITEM i
			WHERE i.archived = 'N'
		");

		// Status breakdown of visible items
		$status_query = $this->db->query("
			SELECT 
				ps.name as status,
				COUNT(*) as count
			FROM T_ITEM i
			JOIN P_PRODUCT_STATUS ps ON i.status_id = ps.id
			WHERE i.archived = 'N' AND i.web_vis = 1
			GROUP BY ps.name
			ORDER BY count DESC
		");

		// Image compliance
		$image_query = $this->db->query("
			SELECT 
				SUM(CASE WHEN i.web_vis = 1 AND (si.pic_big_url IS NOT NULL OR si.pic_hd_url IS NOT NULL) THEN 1 ELSE 0 END) as visible_with_images,
				SUM(CASE WHEN i.web_vis = 1 AND si.pic_big_url IS NULL AND si.pic_hd_url IS NULL THEN 1 ELSE 0 END) as visible_without_images
			FROM T_ITEM i
			LEFT JOIN SHOWCASE_ITEM si ON i.id = si.item_id
			WHERE i.archived = 'N' AND i.web_vis = 1
		");

		return [
			'overall' => $overall_query->row_array(),
			'by_status' => $status_query->result_array(),
			'image_compliance' => $image_query->row_array()
		];
	}

	/**
	 * Get data quality issues
	 */
	private function get_data_quality_issues()
	{
		// Products visible without beauty shot
		$products_query = $this->db->query("
			SELECT COUNT(*) as count
			FROM SHOWCASE_PRODUCT sp
			WHERE sp.visible = 'Y'
			  AND (sp.pic_big_url IS NULL OR sp.pic_big_url = '')
		");

		// Items visible but parent not visible
		$items_parent_query = $this->db->query("
			SELECT COUNT(*) as count
			FROM T_ITEM i
			JOIN SHOWCASE_PRODUCT sp ON i.product_id = sp.product_id
			WHERE i.web_vis = 1 
			  AND sp.visible = 'N'
			  AND i.web_vis_toggle = 0
			  AND i.archived = 'N'
		");

		// Items visible without images
		$items_images_query = $this->db->query("
			SELECT COUNT(*) as count
			FROM T_ITEM i
			LEFT JOIN SHOWCASE_ITEM si ON i.id = si.item_id
			WHERE i.web_vis = 1
			  AND si.pic_big_url IS NULL
			  AND si.pic_hd_url IS NULL
			  AND i.archived = 'N'
		");

		return [
			'products_no_beauty_shot' => $products_query->row()->count,
			'items_parent_not_visible' => $items_parent_query->row()->count,
			'items_no_images' => $items_images_query->row()->count
		];
	}
}