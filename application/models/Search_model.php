<?php

class Search_model extends MY_Model
{

	public $filters = array(
		// Product filtering utility
	  'product_ids' => array(Regular => array(), Digital => array()),
	  'item_ids' => array(),
	  'restrictType' => array(Regular, Digital),
	  'group_by' => item, // 'product' or 'item'
	  'order_by' => null,
	  'limit' => null,
		// Column selector
	  'select' => true, // see function select_columns() for options
		// Columns individual filters
	  'status_id' => null, // g.e. when [1,3], items with status_id 1 or 3 ONLY will be included
	  'stock_status_id' => null, // g.e. when [1,3], items with stock_status_id 1 or 3 ONLY will be included
	  'shelf_id' => null, // [int]
	  'vendor_id' => null, // [int]
	  'weave_id' => null, // [int]
        'cleaning_id' => null,
        'finish_id' => null,
	  'showcase' => null, // see function filter_showcase()
	  'in_ringset' => null, // true/false, adds clause WHERE is_ringset = 1/0
	  'in_master' => null, // null/true/false, if not null: adds clause WHERE in_master = 1/0
	  'stock' => null, // [ min=>int, max=>int ]—
	  'discount' => 1, // range of 0 to 1
	  'dateRanges' => null, // [ from=>date, to=>date ], dates used for price/costs update
	  'list' => null, // integer or array( id=>[int], exclude_id=>[int], active=>boolean, showroom=>boolean, item_info=>boolean)
		// 			'filterByListId' => null, // replaced by [list][exclude_id]
      'where_literal' => null,
	  'includeVendorAbrev' => true,
	  'includeDiscontinued' => false,
	  'includeArchived' => false,   // true to include deleted items
	  'includeCombinedProducts' => true,
	  'isPrinting' => false, // true if printing for master price list!
	  'add_columns' => [], // g.e. [ 'format'=>'roll' ] will add the clause: SELECT 'roll' as format
	  'model_table' => null, // where it counts all rows from / for datatables only
	  'datatable' => false,
	  'debug' => false,
	  'get_compiled_select' => false,
	  'run_compatibility' => true,
	  'filter_unnecessary_status_for_print' => false,
	  'date_format' => "%Y-%m-%d"
	);
	public $filter_options = [
	  'single_checkboxes' => [
		[
		  'name' => 'Vendor Name',
		  'value' => 'vendor',
		  'checked' => false,
		],
		[
		  'name' => 'Vendor Product Name',
		  'value' => 'vendor_product_name',
		  'checked' => false,
		],
		[
		  'name' => 'Status',
		  'value' => 'status',
		  'checked' => true,
		],
		[
		  'name' => 'Stock Status',
		  'value' => 'stock_status',
		  'checked' => true,
		],
		[
		  'name' => 'Width',
		  'value' => 'width',
		  'checked' => true,
		],
		[
		  'name' => 'Content Front',
		  'value' => 'content_front',
		  'checked' => true,
		],
		[
		  'name' => 'Outdoor',
		  'value' => 'outdoor_text',
		  'checked' => true,
		],
		[
		  'name' => 'Price',
		  'value' => 'price',
		  'checked' => true,
		  'output_values' => [
              'p_res_cut',
              'p_hosp_cut',
              'p_hosp_roll',
              'p_dig_res', 'p_dig_hosp',
              'price_date'
          ]
		],
		[
		  'name' => 'Costs',
		  'value' => 'costs',
		  'checked' => false,
		  'output_values' => ['cost_cut', 'cost_half_roll', 'cost_roll', 'cost_roll_landed', 'cost_roll_ex_mill', 'cost_date']
		],
	    [
		  'name' => 'Shelfs',
		  'value' => 'shelf',
		  'checked' => false,
	    ],
	    [
		  'name' => 'Stock',
		  'value' => 'stock',
		  'checked' => false,
	    ],
	    [
	    'name' => 'Weave',
	    'value' => 'weave',
	    'checked' => false,
	  ]
	  ],
	  'multiselect' => [
	    'list' => []
	  ]
	];

	protected $query = array('stack' => [], 'except_clause' => [], 'string' => '');
	protected $results = array();

	function __construct()
	{
		parent::__construct();
	}

	function do_search($params)
	{
		$this->filters = array_merge($this->filters, $params);
		$this->date_format = $this->filters['date_format'];
		$this->query = array('stack' => array(), 'string' => '');

		// Deprecation compatibility
		$this->discount = $this->filters['discount'];
		if($this->filters['run_compatibility']) $this->_for_compatibility();

		// Start generating the query for each product type
		foreach ($this->filters['restrictType'] as $this->product_type) {
			if (!$this->filters['includeCombinedProducts'] && in_array($this->product_type, array(Digital, ScreenPrint))) continue; // Skip combined product if so

			// Selections
			$this->select_product_basics();
			if (is_array($this->filters['select'])) $this->select_columns();

			// Apply filters
			if (!is_null($this->filters['status_id'])) $this->db->where_in("$this->t_item.status_id", $this->filters['status_id']);
			if (!is_null($this->filters['stock_status_id'])) $this->db->where_in("$this->t_item.stock_status_id", $this->filters['stock_status_id']);
			if (!is_null($this->filters['shelf_id'])) $this->filter_shelfs();
			if (!is_null($this->filters['vendor_id'])) $this->filter_vendor();
			if (!is_null($this->filters['weave_id'])) $this->filter_weave();
            if (!is_null($this->filters['cleaning_id'])) $this->filter_cleaning();
            if (!is_null($this->filters['finish_id'])) $this->filter_finish();
			if (!is_null($this->filters['showcase'])) $this->filter_showcase();
			if (!is_null($this->filters['list_front_contents_id'])) $this->front_contents();
			if (!is_null($this->filters['list_firecodes_id'])) $this->filter_firecodes();
			if (!is_null($this->filters['in_ringset'])) $this->db->where("$this->t_item.in_ringset", ($this->filters['in_ringset'] ? 1 : 0));
			if (!is_null($this->filters['in_master'])){
				if($this->filters['in_master']) {
					// Select 'in_master' == '1'
					if ($this->product_type == Regular) {
						if ($this->filters['group_by'] === item) {
							$this->db
							  ->where("$this->t_product.in_master", '1')
							  ->where("$this->t_item.in_master", '1');
						} else if ($this->filters['group_by'] === product) {
							$this->db->where("$this->t_product.in_master", '1');
						}
					} else if ($this->product_type == Digital) {
						if ($this->filters['group_by'] === item) {
							$this->db
							  ->where("$this->product_digital.in_master", '1')
							  ->where("$this->t_item.in_master", '1');
						} else if ($this->filters['group_by'] === product) {
							$this->db->where("$this->product_digital.in_master", '1');
						}
					}
				}
				else {
					// Select 'in_master' == '0'
					if ($this->product_type == Regular) {
						if ($this->filters['group_by'] === item) {
							$this->db
							  ->where("$this->t_product.in_master", '0')
							  ->or_group_start()
							      ->where("$this->t_product.in_master", '1')
								  ->where("$this->t_item.in_master", '0')
							  ->group_end()
							;
						} else if ($this->filters['group_by'] === product) {
							$this->db->where("$this->t_product.in_master", '0');
						}
					} else if ($this->product_type == Digital) {
						if ($this->filters['group_by'] === item) {
							$this->db
							  ->where("$this->product_digital.in_master", '0')
							  ->or_group_start()
								  ->where("$this->product_digital.in_master", '1')
								  ->where("$this->t_item.in_master", '0')
							  ->group_end()
							;
						} else if ($this->filters['group_by'] === product) {
							$this->db->where("$this->product_digital.in_master", '0');
						}
					}
				}

			}
			if (!is_null($this->filters['stock'])) $this->filter_stock();
			if (!is_null($this->filters['dateRanges'])) $this->where_date_ranges();
			if (!is_null($this->filters['list'])) $this->filter_lists();

			if (!empty($this->filters['product_ids'][$this->product_type])) $this->db->where_in("$this->t_item.product_id", $this->filters['product_ids'][$this->product_type]);
			if (!empty($this->filters['item_ids'])) $this->db->where_in("$this->t_item.id", $this->filters['item_ids']);


			// Filter product status
			if ($this->filters['isPrinting']) {
				$this->db->where_not_in("$this->t_item.status_id", $this->product_status_to_dont_print_in_pricelists);
			} else if (!$this->filters['includeDiscontinued']) {
				$this->where_item_is_not_discontinued();
			}
			if (!$this->filters['includeArchived']) {
				$this->where_item_is_not_archived();
				$this->where_product_is_not_archived();
			}

			if ($this->filters['datatable']) $this->set_datatables_variables();

            if (!is_null($this->filters['where_literal'])){
                $this->db->where($this->filters['where_literal']);
            }

			if (count($this->filters['add_columns']) > 0) $this->add_columns();

			array_push($this->query['stack'], $this->db->get_compiled_select());
		}

		// Wrap up the query

		$this->query['string'] = implode(" UNION ALL ", $this->query['stack']);

		if ($this->filters['debug']) {
            var_dump($this->query["string"]);
            exit();
//			return [
//			  'query' => $this->query['string'],
//			  'filters' => $this->filters
//				//         'result' => $this->db->query( $this->query['string'] )->result_array()
//			];
		} else if ($this->filters['get_compiled_select']) {
			return $this->query['string'];
		} else if ($this->filters['datatable']) {
			return $this->apply_datatables_processing($this->query['string']);
		} else {
			if ($this->filters['group_by'] === item) {
				if (!is_null($this->filters['order_by'])) {
					$this->query['string'] .= " ORDER BY " . $this->filters['order_by'];
				} else if (strpos($this->query['string'], 'UNION ALL')) {
					$this->query['string'] .= " ORDER BY if(code = '' or code is null,1,0), code, product_name, color ASC ";
				} else {
					$this->query['string'] .= " ORDER BY if($this->t_item.code = '' or $this->t_item.code is null,1,0), $this->t_item.code, product_name, color ASC ";
				}
			} else if ($this->filters['group_by'] === product) {
				if (!is_null($this->filters['order_by'])) {
					$this->query['string'] .= " ORDER BY " . $this->filters['order_by'];
				} else {
					$this->query['string'] .= " ORDER BY product_name ";
				}
			}
			if (!is_null($this->filters['limit'])){
				if(is_array($this->filters['limit'])){
					$this->query['string'] .= " LIMIT " . implode(',', $this->filters['limit']);
				} else {
					$this->query['string'] .= " LIMIT " . $this->filters['limit'];
				}
			}
			if( $this->db->query($this->query['string']) ){
				// PKL: remove this line after testing
				// This is an "Oh my God" query!!
				// echo $this->db->get_compiled_select($this->query['string']);
				return $this->db->query($this->query['string'])->result_array();
			}
			
		}
	}

	// Selects

	function _for_compatibility()
	{
		if (is_bool($this->filters['select']) && $this->filters['select']) {
			$this->filters['select'] = ['width', 'content_front', 'outdoor'];
		}
		if (
		  is_array($this->filters['select']) && !in_array('status', $this->filters['select'])
		  ||
		  !is_array($this->filters['select'])
		) {
			$this->filters['select'][] = 'status';
		}
	}

	function select_columns()
	{
		if (count($this->filters['select']) > 0) {
			foreach ($this->filters['select'] as $col) {
				switch ($col) {
					case 'status':
					case 'status_descr':
					case 'status_id':
					case 'stock_status':
					case 'stock_status_descr':
					case 'stock_status_id':
						if ($this->product_type === Digital) {
							// Digital
							if ($this->filters['group_by'] === item) {
								switch ($col) {
									case 'status':
										$this->db->select("$this->p_product_status.name as $col");
										break;
									case 'status_descr':
										$this->db->select("$this->p_product_status.descr as $col");
										break;
									case 'status_id':
										$this->db->select("$this->p_product_status.id as $col");
										break;
									case 'stock_status':
										$this->db->select("'MBO' as $col");
										break;
									case 'stock_status_descr':
										$this->db->select("'Manuf by Opuzen' as $col");
										break;
									case 'stock_status_id':
										$this->db->select("0 as $col");
										break;
									default:
										break;
								}
								if (!$this->db->table_is_included($this->p_product_status)) $this->db->join("$this->p_product_status", "$this->t_item.status_id = $this->p_product_status.id");
							} else {
								switch ($col) {
									case 'status':
										$this->db->select("GROUP_CONCAT(DISTINCT $this->p_product_status.name SEPARATOR '-') as $col");
										break;
									case 'status_descr':
										$this->db->select("$this->p_product_status.descr as $col");
										break;
									case 'status_id':
										$this->db->select("GROUP_CONCAT(DISTINCT $this->p_product_status.id SEPARATOR '-') as $col");
										break;
									case 'stock_status':
										$this->db->select("'MBO' as $col");
										break;
									case 'stock_status_descr':
										$this->db->select("'Manuf by Opuzen' as $col");
										break;
									case 'stock_status_id':
										$this->db->select("0 as $col");
										break;
									default:
										break;
								}
								if (!$this->db->table_is_included($this->p_product_status)) $this->db->join("$this->p_product_status", "$this->t_item.status_id = $this->p_product_status.id");
							}
						} else {
							// Regular
							if ($this->filters['group_by'] === item) {
								// Group by Item
								switch ($col) {
									case 'status':
										$this->db->select("$this->p_product_status.name as $col");
										break;
									case 'status_descr':
										$this->db->select("$this->p_product_status.descr as $col");
										break;
									case 'status_id':
										$this->db->select("$this->p_product_status.id as $col");
										break;
									case 'stock_status':
										$this->db->select("$this->p_stock_status.name as $col");
										break;
									case 'stock_status_descr':
										$this->db->select("$this->p_stock_status.descr as $col");
										break;
									case 'stock_status_id':
										$this->db->select("$this->p_stock_status.id as $col");
										break;
									default:
										break;
								}
								if (!$this->db->table_is_included($this->p_product_status)) $this->db->join("$this->p_product_status", "$this->t_item.status_id = $this->p_product_status.id");
								if (!$this->db->table_is_included($this->p_stock_status)) $this->db->join("$this->p_stock_status", "$this->t_item.stock_status_id = $this->p_stock_status.id");
							} else {
								// Group by Product
								switch ($col) {
									case 'status':
										if($this->filters['filter_unnecessary_status_for_print']){
											$this->db->select("GROUP_CONCAT(DISTINCT if($this->p_product_status.id != 1 AND $this->p_product_status.id != 2 AND $this->p_product_status.id != 3, $this->p_product_status.name, null) SEPARATOR '-') as $col");
										}
										else {
											$this->db->select("GROUP_CONCAT(DISTINCT $this->p_product_status.name SEPARATOR '-') as $col");
										}

										break;
									case 'status_descr':
										$this->db->select("$this->p_product_status.descr as $col");
										break;
									case 'status_id':
										$this->db->select("GROUP_CONCAT(DISTINCT $this->p_product_status.id SEPARATOR '-') as $col");
										break;
									case 'stock_status':
										$this->db->select("GROUP_CONCAT(DISTINCT $this->p_stock_status.name SEPARATOR '-') as $col");
										break;
									case 'stock_status_descr':
										$this->db->select("$this->p_stock_status.descr as $col");
										break;
									case 'stock_status_id':
										$this->db->select("GROUP_CONCAT(DISTINCT $this->p_stock_status.id SEPARATOR '-') as $col");
										break;
									default:
										break;
								}
								if (!$this->db->table_is_included($this->p_product_status)) $this->db->join("$this->p_product_status", "$this->t_item.status_id = $this->p_product_status.id");
								if (!$this->db->table_is_included($this->p_stock_status)) $this->db->join("$this->p_stock_status", "$this->t_item.stock_status_id = $this->p_stock_status.id");
							}
						}
						break;

					case 'shelf':
						$this->db->select("GROUP_CONCAT(DISTINCT $this->p_shelf.name ORDER BY $this->p_shelf.name SEPARATOR ' / ') as $col");
						if (!$this->db->table_is_included($this->t_item_shelf)) $this->db->join($this->t_item_shelf, "$this->t_item.id = $this->t_item_shelf.item_id", 'left outer');
						if (!$this->db->table_is_included($this->p_shelf)) $this->db->join($this->p_shelf, "$this->t_item_shelf.shelf_id = $this->p_shelf.id", 'left outer');
						break;

					case 'shelf_id':
						$this->db->select("GROUP_CONCAT(DISTINCT $this->t_item_shelf.shelf_id ORDER BY $this->t_item_shelf.shelf_id SEPARATOR ' / ') as $col");
						if (!$this->db->table_is_included($this->t_item_shelf)) $this->db->join($this->t_item_shelf, "$this->t_item.id = $this->t_item_shelf.item_id", 'left outer');
						//         if( ! $this->db->table_is_included( $this->p_shelf ) ) $this->db->join($this->p_shelf, "$this->t_item_shelf.shelf_id = $this->p_shelf.id", 'left outer');
						break;

					case 'sampling_location':
						$t = "RollLoc";
						$this->db->select("$t.name as roll_location")
						  ->join("$this->p_sampling_locations $t", "$this->t_item.roll_location_id = $t.id", 'left outer');

						$t = "BinLoc";
						$this->db->select("$t.name as bin_location")
						  ->join("$this->p_sampling_locations $t", "$this->t_item.bin_location_id = $t.id", 'left outer');
						break;

					case 'sampling_stock':
						$this->db->select("$this->t_item.roll_yardage as roll_yardage");
						$this->db->select("$this->t_item.bin_quantity as bin_quantity");
						break;

					case 'showcase':
						if ($this->product_type === Regular) {
							if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
							if ($this->filters['group_by'] === item) {
								if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
								$this->db
								  ->select("CASE WHEN $this->t_item.status_id IN (" . implode(',', $this->product_status_to_dont_print_in_website) . ") THEN 'N' ELSE $this->t_showcase_item.visible END AS web_visible")
								  ->select("COALESCE($this->t_showcase_item.pic_big_url, $this->t_showcase_item.pic_big) as pic_big")
								  ->select("COALESCE($this->t_showcase_item.pic_hd_url, $this->t_showcase_item.pic_hd) as pic_hd");
							} else if ($this->filters['group_by'] === product) {
								$this->db
								  ->select("$this->t_showcase_product.visible as web_visible")
								  ->select("COALESCE($this->t_showcase_product.pic_big_url, $this->t_showcase_product.pic_big) as pic_big")
								  ->select("'N' as pic_hd");
							}
						} else {
							$this->db->select("'N' as web_visible, 'N' as pic_big, 'N' as pic_hd");
						}
						break;

					case 'pic_big_url':
                        if($this->filters['group_by'] == product){
                            if($this->product_type === Regular){
                                if (isset($this->filters['list']['item_info']) && $this->filters['list']['item_info']) {
                                    $this->db->select("SUBSTRING_INDEX(GROUP_CONCAT($this->t_showcase_item.pic_big_url ORDER BY ListItems.big_piece DESC SEPARATOR ','), ',', 1) as pic_big_url");
                                }
                                else {
                                    $this->db->select("$this->t_showcase_item.pic_big_url as pic_big_url");
                                }
                                if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
                            }
                            else if($this->product_type === Digital) {
                                if (isset($this->filters['list']['item_info']) && $this->filters['list']['item_info']) {
                                    $this->db->select("SUBSTRING_INDEX(GROUP_CONCAT($this->t_showcase_style_items.pic_big_url ORDER BY ListItems.big_piece DESC SEPARATOR ','), ',', 1) as pic_big_url");
                                }
                                else {
                                    $this->db->select("$this->t_showcase_style_items.pic_big_url");
                                }
                                if (!$this->db->table_is_included($this->t_showcase_style_items)) $this->db->join($this->t_showcase_style_items, "$this->product_digital.style_id = $this->t_showcase_style_items.style_id AND $this->t_showcase_style_items.archived = 'N'", 'left outer');
                            }
                        }
                        else {
                            if($this->product_type === Regular){
                                $this->db->select("$this->t_showcase_item.pic_big_url");
                                if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
                            }
                            else if($this->product_type === Digital) {
                                $this->db->select("$this->t_showcase_style_items.pic_big_url");
                                if (!$this->db->table_is_included($this->t_showcase_style_items)) $this->db->join($this->t_showcase_style_items, "$this->product_digital.style_id = $this->t_showcase_style_items.style_id AND $this->t_showcase_style_items.archived = 'N'", 'left outer');
                            }
                        }
						break;

					case 'pic_hd_url':
						$this->db->select("$this->t_showcase_item.pic_hd_url");
						if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
						break;

					case 'beauty_shot_url':
						$this->db->select("COALESCE($this->t_showcase_product.pic_big_url, $this->t_showcase_product.pic_big) as beauty_shot_url");
						if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
						break;

					case 'url_title':
						if ($this->product_type === Regular) {
							if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
							$this->db->select("IF( $this->t_showcase_product.visible = 'Y', $this->t_showcase_product.url_title, '' ) as url_title");
						} else {
							if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
							$this->db->select("IF( $this->t_showcase_product.visible = 'Y', $this->t_showcase_product.url_title, '' ) as url_title");
//							$this->db->select("'' as url_title");
						}
						break;

					case 'in_ringset':
						if ($this->filters['group_by'] === item) $this->db->select("$this->t_item.in_ringset");
						break;

					case 'in_master':
						if ($this->filters['group_by'] === item) $this->db->select("$this->t_item.in_master, $this->t_product.in_master as in_master_product");
						if ($this->filters['group_by'] === product) $this->db->select("$this->t_product.in_master");
						break;

					case 'width':
						$this->db->select("CONCAT($this->t_product.width, '\"') as $col");
						break;

					case 'repeats':
					case 'repeat':
						$this->db->select("CONCAT_WS(' / ', CONCAT('V: ', IF($this->t_product.vrepeat != '0.00' AND $this->t_product.vrepeat IS NOT NULL, $this->t_product.vrepeat, NULL)), CONCAT('H: ', IF($this->t_product.hrepeat != '0.00' AND $this->t_product.hrepeat IS NOT NULL, $this->t_product.hrepeat, NULL)) ) as repeats");
						break;

					case 'content_front':
						if($this->product_type === Regular){
							$this->db
								->select("PCF.content_front")
								->join("$this->v_product_content_front PCF", "$this->t_item.product_id = PCF.product_id", "left outer");
						}
						else if($this->product_type === Digital) {
							$this->db
								->select("PCF.content_front")
								->join("$this->v_product_content_front PCF", "TT.product_id = PCF.product_id", "left outer");
						}
						break;

					case 'content_back':
						if($this->product_type === Regular){
							$this->db
								->select("PCB.content_back")
								->join("$this->v_product_content_back PCB", "$this->t_item.product_id = PCB.product_id", "left outer");
						}
						else if($this->product_type === Digital) {
							$this->db
								->select("PCB.content_back")
								->join("$this->v_product_content_back PCB", "TT.product_id = PCB.product_id", "left outer");
						}
						break;

					case 'outdoor':
						$this->db->select("$this->t_product.outdoor");
						break;

					case 'outdoor_text':
						$this->db->select("IF($this->t_product.outdoor = 'N', 'No', 'Yes') as outdoor");
						break;

					case 'origin':
						$this->db
						  ->select("$this->p_origin.name as origin")
						  ->join($this->t_product_origin, "$this->t_product.id = $this->t_product_origin.product_id", 'left outer')
						  ->join($this->p_origin, "$this->t_product_origin.origin_id = $this->p_origin.id", 'left outer');
						break;

					case 'description':
						$this->db->select("$this->t_showcase_product.descr as description");
						if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
						break;

					case 'finish':
						$this->db
						  ->select("GROUP_CONCAT(DISTINCT $this->p_finish.name SEPARATOR ', ') as finish")
						  ->join($this->t_product_finish, "$this->t_product.id = $this->t_product_finish.product_id", 'left outer')
						  ->join($this->p_finish, "$this->t_product_finish.finish_id = $this->p_finish.id", 'left outer');
						break;

					case 'firecode':
						$this->db
						  ->select("GROUP_CONCAT(DISTINCT $this->p_firecode.name SEPARATOR ', ') as firecode")
						  ->join($this->t_product_firecode, "$this->t_product.id = $this->t_product_firecode.product_id", 'left outer')
						  ->join($this->p_firecode, "$this->t_product_firecode.firecode_test_id = $this->p_firecode.id", 'left outer');
						break;

					case 'abrasion':
						$this->db
						  ->select("GROUP_CONCAT(DISTINCT $this->t_product_abrasion.n_rubs, ' ', $this->p_abrasion_test.name SEPARATOR ', ') as abrasion")
						  ->join($this->t_product_abrasion, "$this->t_product.id = $this->t_product_abrasion.product_id", 'left outer')
						  ->join($this->p_abrasion_test, "$this->t_product_abrasion.abrasion_test_id = $this->p_abrasion_test.id", 'left outer');
						break;

					case 'yards_per_roll':
						$this->db->select("$this->t_product_various.yards_per_roll");
						if (!$this->db->table_is_included($this->t_product_various)) {
							$this->db->join($this->t_product_various, "$this->t_product.id = $this->t_product_various.product_id", 'left outer');
						}
						break;

					case 'lead_time':
						$this->db->select("$this->t_product_various.lead_time");
						if (!$this->db->table_is_included($this->t_product_various)) {
							$this->db->join($this->t_product_various, "$this->t_product.id = $this->t_product_various.product_id", 'left outer');
						}
						break;

					case 'railroaded':
						$this->db->select("$this->t_product_various.railroaded");
						if (!$this->db->table_is_included($this->t_product_various)) {
							$this->db->join($this->t_product_various, "$this->t_product.id = $this->t_product_various.product_id", 'left outer');
						}
						break;

					case 'coord_colors':
						if ($this->filters['group_by'] === item) {
							$this->db
							  ->select("GROUP_CONCAT(DISTINCT $this->t_showcase_coord_colors.name SEPARATOR ', ') as coord_colors")
							  ->join($this->t_showcase_item_coord_color, "$this->t_item.id = $this->t_showcase_item_coord_color.item_id", 'left outer')
							  ->join($this->t_showcase_coord_colors, "$this->t_showcase_item_coord_color.coord_color_id = $this->t_showcase_coord_colors.id", 'left outer');
						}
						break;

					case 'pattern':
						$this->db
						  ->select("GROUP_CONCAT(DISTINCT $this->t_showcase_patterns.name SEPARATOR ', ') as pattern")
						  ->join($this->t_showcase_product_patterns, "$this->t_product.id = $this->t_showcase_product_patterns.product_id", 'left outer')
						  ->join($this->t_showcase_patterns, "$this->t_showcase_product_patterns.pattern_id = $this->t_showcase_patterns.id", 'left outer');
						break;

					case 'price':
						switch ($this->product_type) {
							case Regular:
								$main_table = $this->t_product;
								break;
							case Digital:
								$main_table = $this->product_digital;
								break;
							case ScreenPrint:
								$main_table = $this->product_screenprint;
								break;
						}
						if (
						  isset($this->filters['list']['id']) &&
						  !is_null($this->filters['list']['id']) &&
						  $this->filters['list']['id'] !== 0 &&
						  $this->filters['group_by'] === item)
						{
							if(isset($this->filters['list']['list_price']) && $this->filters['list']['list_price']){

								$this->db
								  ->select("COALESCE(ListItems.p_res_cut, $this->t_product_price.p_res_cut, '-') as p_res_cut")
								  ->select("COALESCE(ListItems.p_hosp_cut, $this->t_product_price.p_hosp_cut, '-') as p_hosp_cut")
								  ->select("COALESCE(ListItems.p_hosp_roll, $this->t_product_price.p_hosp_roll, '-') as p_hosp_roll")
								  ->select("COALESCE($this->t_product_price.p_dig_res, '-') as p_dig_res")
								  ->select("COALESCE($this->t_product_price.p_dig_hosp, '-') as p_dig_hosp")
								  ->select("DATE_FORMAT($this->t_product_price.date, '$this->date_format') as price_date")
								  ->join("$this->t_product_price", "$main_table.id = $this->t_product_price.product_id AND $this->t_product_price.product_type = '$this->product_type' ", 'left outer');
							}
							else {
								$this->db
								  ->select("ListItems.p_res_cut as list_p_res_cut")
								  ->select("ListItems.p_hosp_cut as list_p_hosp_cut")
								  ->select("ListItems.p_hosp_roll as list_p_hosp_roll")
								  ->select("COALESCE($this->t_product_price.p_res_cut, '-') as p_res_cut")
								  ->select("COALESCE($this->t_product_price.p_hosp_cut, '-') as p_hosp_cut")
								  ->select("COALESCE($this->t_product_price.p_hosp_roll, '-') as p_hosp_roll")
								  ->select("COALESCE($this->t_product_price.p_dig_res, '-') as p_dig_res")
								  ->select("COALESCE($this->t_product_price.p_dig_hosp, '-') as p_dig_hosp")
								  ->select("DATE_FORMAT($this->t_product_price.date, '$this->date_format') as price_date")
								  ->join("$this->t_product_price", "$main_table.id = $this->t_product_price.product_id AND $this->t_product_price.product_type = '$this->product_type' ", 'left outer');
							}
						} else {
							$this->db
							  ->select("COALESCE($this->t_product_price.p_res_cut, '-') as p_res_cut")
							  ->select("COALESCE($this->t_product_price.p_hosp_cut, '-') as p_hosp_cut")
							  ->select("COALESCE($this->t_product_price.p_hosp_roll, '-') as p_hosp_roll")
							  ->select("COALESCE($this->t_product_price.p_dig_res, '-') as p_dig_res")
							  ->select("COALESCE($this->t_product_price.p_dig_hosp, '-') as p_dig_hosp")
							  ->select("DATE_FORMAT($this->t_product_price.date, '$this->date_format') as price_date")
							  ->join("$this->t_product_price", "$main_table.id = $this->t_product_price.product_id AND $this->t_product_price.product_type = '$this->product_type' ", 'left outer');
						}
						break;

					case 'costs':
//						$t = $this->t_product_cost;
//						$this->db
//						  ->select("IF( $t.cost_cut IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_cut_type_id = PT.id, PT.name, null), ' ', $t.cost_cut) ) as cost_cut")
//						  ->select("IF( $t.cost_half_roll IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_half_roll_type_id = PT.id, PT.name, null), ' ', $t.cost_half_roll) ) as cost_half_roll")
//						  ->select("IF( $t.cost_roll IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_roll_type_id = PT.id, PT.name, null), ' ', $t.cost_roll) ) as cost_roll")
//						  ->select("IF( $t.cost_roll_landed IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_roll_landed_type_id = PT.id, PT.name, null), ' ', $t.cost_roll_landed) ) as cost_roll_landed")
//						  ->select("IF( $t.cost_roll_ex_mill IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_roll_ex_mill_type_id = PT.id, PT.name, null), ' ', $t.cost_roll_ex_mill) ) as cost_roll_ex_mill")
////						  ->select("$t.cost_roll_ex_mill_text")
//						  ->select("DATE_FORMAT($t.date, '%m/%d/%Y') as cost_date")
//						  ->join($t, "$this->t_product.id = $t.product_id", 'left outer')
//						  ->from("$this->p_price_type PT");
						$this->db
							->select("$this->v_product_cost.cost_cut")
						    ->select("$this->v_product_cost.cost_half_roll")
						    ->select("$this->v_product_cost.cost_roll")
						    ->select("$this->v_product_cost.cost_roll_landed")
						    ->select("$this->v_product_cost.cost_roll_ex_mill")
//						    ->select("$this->v_product_cost.cost_date")
						    ->select("DATE_FORMAT($this->v_product_cost.cost_date, '$this->date_format') as cost_date")
//                            ->select("DATE_FORMAT($this->v_product_cost.cost_date, '%Y-%m-%d %H:%i:%s  %m/%d/%Y') as cost_date")
//						    ->select("DATE_FORMAT($this->v_product_cost.cost_date, $this->date_format) as cost_date", FALSE)
						;
						if (!$this->db->table_is_included($this->v_product_cost)) $this->db->join($this->v_product_cost, "$this->t_item.product_id = $this->v_product_cost.product_id", 'left outer');

						break;

					case 'vendor':
						if ($this->product_type === Digital) {
							$this->db->select("'OZ' as vendor");
						} else if ($this->product_type === Regular) {
							$this->db->select("IF( $this->p_vendor.abrev IS NULL, $this->p_vendor.name, $this->p_vendor.abrev ) as vendor");
							if (!$this->db->table_is_included($this->t_product_vendor)) $this->db->join($this->t_product_vendor, "$this->t_item.product_id = $this->t_product_vendor.product_id");
							if (!$this->db->table_is_included($this->p_vendor)) $this->db->join($this->p_vendor, "$this->t_product_vendor.vendor_id = $this->p_vendor.id");

						}
						break;

					case 'vendor_product_name':
						if($this->product_type === Digital){
							$this->db->select("'' as vendor_product_name");
						} else {
							$this->db->select("COALESCE($this->t_product_various.vendor_product_name, '') as vendor_product_name");
							if (!$this->db->table_is_included($this->t_product_various)) $this->db->join($this->t_product_various, "$this->t_product.id = $this->t_product_various.product_id", 'left outer');
						}
						break;

                    case 'tariff_surcharge':
                        $this->db->select("COALESCE($this->t_product_various.tariff_surcharge, '') as tariff_surcharge");
                        if (!$this->db->table_is_included($this->t_product_various)) $this->db->join($this->t_product_various, "$this->t_product.id = $this->t_product_various.product_id", 'left outer');
                        break;

                    case 'freight_surcharge':
                        $this->db->select("COALESCE($this->t_product_various.freight_surcharge, '') as freight_surcharge");
                        if (!$this->db->table_is_included($this->t_product_various)) $this->db->join($this->t_product_various, "$this->t_product.id = $this->t_product_various.product_id", 'left outer');
                        break;
						
					case 'vendor_item_name':
						if($this->filters['group_by'] === item){
							$this->db->select("$this->t_item.vendor_color, $this->t_item.vendor_code");
						}
						break;

					case 'weave':
						$this->db->select("GROUP_CONCAT(DISTINCT $this->p_weave.name SEPARATOR ', ') as weave");
						if (!$this->db->table_is_included($this->t_product_weave)) $this->db->join($this->t_product_weave, "$this->t_product.id = $this->t_product_weave.product_id", 'left outer');
						if (!$this->db->table_is_included($this->p_weave)) $this->db->join($this->p_weave, "$this->t_product_weave.weave_id = $this->p_weave.id", 'left outer');
						break;

					case 'uses':
						$this->db
						  ->select("GROUP_CONCAT(DISTINCT $this->p_use.name ORDER BY $this->p_use.name ASC SEPARATOR ' / ') as uses")
						  ->join($this->t_product_use, "$this->t_product.id = $this->t_product_use.product_id", 'left outer')
						  ->join($this->p_use, "$this->t_product_use.use_id = $this->p_use.id", 'left outer');
						break;

					case 'cleaning':
						$this->db
						  ->select("GROUP_CONCAT(DISTINCT $this->p_cleaning.name SEPARATOR ' / ') as cleaning")
						  ->join($this->t_product_cleaning, "$this->t_product.id = $this->t_product_cleaning.product_id", 'left outer')
						  ->join($this->p_cleaning, "$this->t_product_cleaning.cleaning_id = $this->p_cleaning.id", "left outer");
						break;

					case 'stock':
						if ($this->filters['group_by'] === item) {
							if (!$this->db->table_is_included($this->t_product_stock)) $this->db->join($this->t_product_stock, "$this->t_item.id = $this->t_product_stock.master_item_id", 'left outer');
							$this->db->select("$this->t_product_stock.yardsInStock, $this->t_product_stock.yardsOnHold, $this->t_product_stock.yardsAvailable, $this->t_product_stock.yardsOnOrder, $this->t_product_stock.yardsBackorder");
						} else if ($this->filters['group_by'] === product) {
							if (!$this->db->table_is_included($this->t_product_stock)) $this->db->join($this->t_product_stock, "$this->t_item.id = $this->t_product_stock.master_item_id", 'left outer');
							$this->db->select("SUM($this->t_product_stock.yardsInStock) as yardsInStock, SUM($this->t_product_stock.yardsOnHold) as yardsOnHold, SUM($this->t_product_stock.yardsAvailable) as yardsAvailable, SUM($this->t_product_stock.yardsOnOrder) as yardsOnOrder, SUM($this->t_product_stock.yardsBackorder) as yardsBackorder");
                        }
                        break;

					case 'stock_link':
						if ($this->filters['group_by'] === item) {
							if (!$this->db->table_is_included($this->t_product_stock)) $this->db->join($this->t_product_stock, "$this->t_item.id = $this->t_product_stock.master_item_id", 'left outer');
							$this->db->select("$this->t_product_stock.id as sales_id");
						}
						break;

					case 'count_items':
						if ($this->product_type === Regular) {
							if ($this->filters['group_by'] === product) {
								$this->db
								  ->select("COUNT(DISTINCT $this->t_item.id) as count_items")
								  ->select("COUNT(CASE WHEN $this->t_item.code IS NOT NULL THEN 1 END) as count_items_with_code");
							}
// 							if( ! $this->db->table_is_included($this->t_item) ) $this->db->join("$this->t_item", "$this->t_product.id = $this->t_item.product_id", 'left outer');
							break;
						} else if ($this->product_type === Digital) {
							if ($this->filters['group_by'] === product) {
								$this->db
								  ->select("COUNT(DISTINCT $this->t_item.id) as count_items")
								  ->select("COUNT(DISTINCT $this->t_item.id) as count_items_with_code");
							}
// 							if( ! $this->db->table_is_included($this->t_item) ) $this->db->join("$this->t_item", "$this->product_digital.id = $this->t_item.product_id", 'left outer');
							break;
						}

				}
			}
		}
	}

	function add_columns()
	{
		foreach ($this->filters['add_columns'] as $key => $val) {
			$this->db->select("'$val' as $key");
		}
	}

	function select_product_basics()
	{
		$this->t_item = $this->t_item;
		$pp = $this->t_product_price;
		$ps = $this->p_product_status;
		$ss = $this->p_stock_status;
		$use = $this->p_use;
		$pu = $this->t_product_use;

		switch ($this->product_type) {
			case Regular:
				$this->db
				  // Select product data
				  ->select("$this->t_product.id as product_id")
				  ->select("$this->t_item.product_type as product_type")
				  ->select("$this->t_item.id as item_id")
				  ->select("$this->t_item.code as item_code")
				  ->select("use.name as use")
				  ->select("prod_price.p_res_cut as price")
				  ->select("prod_price.p_hosp_roll as volume_price")
				  ->select("prod_status.name AS product_status")
				  ->select("stock_status.name AS stock_status")
				  ->from("$this->t_item")
				  ->join("$this->t_product", "$this->t_item.product_id = $this->t_product.id")
				  ->join("$pp as prod_price ", "$this->t_item.product_id = prod_price.product_id AND prod_price.product_type = 'R'", 'left outer')
				  ->join("$ps as prod_status ", "$this->t_item.status_id = prod_status.id", 'left outer')
				  ->join("$ss as stock_status ", "$this->t_item.stock_status_id = stock_status.id", 'left outer')
				  ->join("$pu", "$this->t_item.product_id = $pu.product_id", 'left outer')
				  ->join("$use as use", "$pu.use_id = use.id", 'left outer')
				  ->where("$this->t_item.product_type", $this->product_type);


				if ($this->filters['group_by'] === product) {
					// Select only the main product
					$this->db
					  ->distinct()
					  ->select("$this->t_product.name as product_name")
					  ->group_by("$this->t_item.product_id");

				} else if ($this->filters['group_by'] === item) {
					// Select individual items
					if ($this->filters['includeVendorAbrev']) {
						$this->db->select("IF($this->t_item.code IS NULL, CONCAT_WS(' ', $this->p_vendor.abrev, $this->t_product.name), $this->t_product.name) as product_name");
					} else {
						$this->db->select("$this->t_product.name as product_name");
					}

					$this->db
					  ->select("$this->t_item.id as item_id")
					  ->select("$this->t_item.code as code")
					  ->select("GROUP_CONCAT(DISTINCT $this->p_color.NAME ORDER BY $this->t_item_color.n_order SEPARATOR '/') AS color")
					  ->join("$this->t_item_color", "$this->t_item.id = $this->t_item_color.item_id")
					  ->join("$this->p_color", "$this->t_item_color.color_id = $this->p_color.id")
					  ->join($this->t_product_vendor, "$this->t_item.product_id = $this->t_product_vendor.product_id")
					  ->join($this->p_vendor, "$this->t_product_vendor.vendor_id = $this->p_vendor.id")
					  ->group_by("$this->t_item.id");
				}
				break;

			case Digital:
				$this->db
				  // Select Product data
				  ->select("$this->product_digital.id as product_id")
				  ->select("$this->t_item.product_type as product_type")
				  ->select("$this->t_item.id as item_id")
				  ->select("$this->t_item.code as item_code")
				  ->select("use.name as use")
				  ->select("digit_prod_price.p_res_cut as price")
				  ->select("digit_prod_price.p_hosp_roll as volume_price")
				  ->select("digit_prod_status.name AS product_status")
				  ->select("digit_stock_status.name AS stock_status")
				  ->from("$this->t_item")
				  ->join("$this->product_digital", "$this->t_item.product_id = $this->product_digital.id")
				  ->join("$this->t_item TT", "$this->product_digital.item_id = TT.id")
				  ->join("$ps as digit_prod_status", "$this->t_item.status_id = digit_prod_status.id", 'left outer')
				  ->join("$pp as digit_prod_price", "$this->t_item.product_id = digit_prod_price.product_id AND digit_prod_price.product_type = 'D'", 'left outer')
				  ->join("$ss as digit_stock_status", "$this->t_item.stock_status_id = digit_stock_status.id", 'left outer')
				  ->join("$pu", "$this->t_item.product_id = $pu.product_id", 'left outer')
				  ->join("$use as use", "$pu.use_id = use.id", 'left outer')
				  ->join("$this->t_item_color TC", "TT.id = TC.item_id", 'left outer')
				  ->join("$this->p_color PC", "TC.color_id = PC.id", 'left outer')
				  ->join("$this->t_product", "TT.product_id = $this->t_product.id")
				  ->join($this->t_digital_style, "$this->product_digital.style_id = $this->t_digital_style.id")
				  ->where("$this->t_item.product_type", $this->product_type);

				$this->db->select("
					CONCAT($this->t_digital_style.name, ' on ', 
					CASE WHEN $this->product_digital.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, 
					COALESCE($this->t_product.dig_product_name, $this->t_product.name),
					' ', 
					GROUP_CONCAT(DISTINCT PC.name ORDER BY PC.name SEPARATOR ' / ')
					) as product_name");

				if ($this->filters['group_by'] === product) {
					// Select when grouped by the main product
					$this->db
					  ->distinct()
					  ->group_by("$this->t_item.product_id");
// 						->group_by("$this->product_digital.id");
				} else if ($this->filters['group_by'] === item) {
					// Select for individual items
					$this->db
					  ->select("$this->t_item.id as item_id")
					  ->select("$this->t_item.code as code")
					  ->select("GROUP_CONCAT(DISTINCT $this->p_color.NAME ORDER BY $this->t_item_color.n_order SEPARATOR '/') AS color")
					  ->join("$this->t_item_color", "$this->t_item.id = $this->t_item_color.item_id")
					  ->join("$this->p_color", "$this->t_item_color.color_id = $this->p_color.id")
					  ->group_by("$this->t_item.id");
				}
				break;
		}

	}

	function filter_shelfs()
	{
		if (is_array($this->filters['shelf_id'])) {
			if (!$this->db->table_is_included($this->t_item_shelf)) $this->db->join($this->t_item_shelf, "$this->t_item.id = $this->t_item_shelf.item_id", 'left outer');
//       if( ! $this->db->table_is_included( $this->p_shelf ) ) $this->db->join($this->p_shelf, "$this->t_item_shelf.shelf_id = $this->p_shelf.id", 'left outer');
			if (in_array('none', $this->filters['shelf_id'])) {
				$this->db->where("$this->t_item_shelf.shelf_id IS NULL");
			} else {
				$this->db->where_in("$this->t_item_shelf.shelf_id", $this->filters['shelf_id']);
			}
		}
	}

	function filter_showcase()
	{

		if (isset($this->filters['showcase']['missing_description']) && $this->filters['showcase']['missing_description']) {
			if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
			$this->db
			  ->group_start()
			  ->where("$this->t_showcase_product.descr", '')
			  ->or_where("$this->t_showcase_product.descr IS NULL")
			  ->group_end();
			// Force to look under the visible products
			$this->filters['showcase']['web_visible'] = 'Y';
		}

		if (isset($this->filters['showcase']['web_visible']) && !is_null($this->filters['showcase']['web_visible'])) {
			switch ($this->filters['showcase']['web_visible']) {
				case 'Y':
					if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
					if ($this->filters['group_by'] === item) {
						if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
						$this->db
						  ->group_start()
						  ->where_not_in("$this->t_item.status_id", $this->product_status_to_dont_print_in_website)
						  ->where("$this->t_showcase_product.visible", 'Y')
						  ->where("$this->t_showcase_item.visible", 'Y')
						  ->group_end();
					} else if ($this->filters['group_by'] === product) {
						$this->db->where("$this->t_showcase_product.visible", 'Y');
					}
					break;
				case 'N':
					if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
					if ($this->filters['group_by'] === item) {
						$digitalsubquery = "SELECT item_id FROM $this->p_list_items WHERE list_id = $this->digital_grounds_list_id AND active = 1 AND item_id = $this->t_item.id";
						if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
						$this->db
						  ->group_start()
						  ->group_start()
						  ->where(" EXISTS ($digitalsubquery) ")
						  ->group_start()
						  ->where("$this->t_showcase_item.visible", 'N')
						  ->or_where("$this->t_showcase_item.visible IS NULL")
						  ->group_end()
						  ->group_end()
						  ->or_group_start()
						  ->where(" NOT EXISTS ($digitalsubquery) ")
						  ->group_start()
						  ->where("$this->t_showcase_item.visible", 'N')
						  ->or_where("$this->t_showcase_item.visible IS NULL")
						  ->or_where("$this->t_showcase_product.visible", 'N')
						  ->or_where("$this->t_showcase_product.visible IS NULL")
						  ->group_end()
						  ->group_end()
						  ->group_end();
					} else if ($this->filters['group_by'] === product) {
						$this->db
						  ->group_start()
						  ->where("$this->t_showcase_product.visible", 'N')
						  ->or_where("$this->t_showcase_product.visible IS NULL")
						  ->group_end();
					}
					break;
			}
		}

		if (isset($this->filters['showcase']['web_image']) && !is_null($this->filters['showcase']['web_image'])) {
			switch ($this->filters['showcase']['web_image']) {
				case 'Y':
					if ($this->filters['group_by'] === item) {
						if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
						$this->db
						  ->group_start()
						  ->where("$this->t_showcase_item.pic_big_url IS NOT NULL", null, false)
						  ->or_group_start()
						  ->where("$this->t_showcase_item.pic_big_url IS NULL", null, false)
						  ->where("$this->t_showcase_item.pic_big", 'Y')
						  ->group_end()
						  ->group_end();
					} else if ($this->filters['group_by'] === product) {
						if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
						$this->db
						  ->group_start()
						  ->where("$this->t_showcase_product.pic_big_url IS NOT NULL", null, false)
						  ->or_group_start()
						  ->where("$this->t_showcase_product.pic_big_url IS NULL", null, false)
						  ->where("$this->t_showcase_product.pic_big", 'Y')
						  ->group_end()
						  ->group_end();
					}
					break;
				case 'N':
					if ($this->filters['group_by'] === item) {
						if (!$this->db->table_is_included($this->t_showcase_item)) $this->db->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer');
						$this->db
						  ->where("$this->t_showcase_item.pic_big_url IS NULL", null, false)
						  ->group_start()
						  ->where("$this->t_showcase_item.pic_big IS NULL", null, false)
						  ->or_where("$this->t_showcase_item.pic_big", "N")
						  ->group_end();
					} else if ($this->filters['group_by'] === product) {
						if (!$this->db->table_is_included($this->t_showcase_product)) $this->db->join($this->t_showcase_product, "$this->t_showcase_product.product_type = 'R' AND $this->t_product.id = $this->t_showcase_product.product_id", 'left outer');
						$this->db
						  ->where("$this->t_showcase_product.pic_big_url IS NULL", null, false)
						  ->group_start()
						  ->where("$this->t_showcase_product.pic_big IS NULL", null, false)
						  ->or_where_in("$this->t_showcase_product.pic_big", array('N', 'P'))
						  ->group_end();
					}
					break;
			}
		}
	}

	function filter_stock()
	{
		if ($this->filters['group_by'] === item) {
			if (!$this->db->table_is_included($this->t_product_stock)) $this->db->join($this->t_product_stock, "$this->t_item.id = $this->t_product_stock.master_item_id", 'left outer');
			if (is_array($this->filters['stock'])) {
				if (!is_null($this->filters['stock']['min'])) {
					$this->db->where("$this->t_product_stock.yardsAvailable >", $this->filters['stock']['min']);
				}
				if (!is_null($this->filters['stock']['max'])) {
					$this->db->where("$this->t_product_stock.yardsAvailable <", $this->filters['stock']['max']);
				}
			}
		} else if ($this->filters['group_by'] === product) {
			$this->db->select(" '' as sales_id, 0 as yardsInStock, 0 as yardsOnHold, '-' as yardsAvailable, 0 as yardsOnOrder, 0 as yardsBackorder");
		}
	}

	function filter_vendor()
	{
		if (is_array($this->filters['vendor_id']) && count($this->filters['vendor_id']) > 0) {
			if (!$this->db->table_is_included($this->t_product_vendor)) $this->db->join($this->t_product_vendor, "$this->t_item.product_id = $this->t_product_vendor.product_id");
			$this->db->where_in("$this->t_product_vendor.vendor_id", $this->filters['vendor_id']);
		}
	}

	function filter_weave()
	{
		if (is_array($this->filters['weave_id']) && count($this->filters['weave_id']) > 0) {
			if (!$this->db->table_is_included($this->t_product_weave)) $this->db->join($this->t_product_weave, "$this->t_product.id = $this->t_product_weave.product_id", 'left outer');
			$this->db->where_in("$this->t_product_weave.weave_id", $this->filters['weave_id']);
		}
	}
	function front_contents()
	{
		if (is_array($this->filters['list_front_contents_id']) && count($this->filters['list_front_contents_id']) > 0) {
			if (!$this->db->table_is_included($this->t_product_content_front)) $this->db->join($this->t_product_content_front, "$this->t_product.id = $this->t_product_content_front.product_id", 'left outer');
			$this->db->where_in("$this->t_product_content_front.content_id", $this->filters['list_front_contents_id']);
		}
	}
	function filter_firecodes()
	{
		if (is_array($this->filters['list_firecodes_id']) && count($this->filters['list_firecodes_id']) > 0) {
			if (!$this->db->table_is_included($this->t_product_firecode)) $this->db->join($this->t_product_firecode, "$this->t_product.id = $this->t_product_firecode.product_id", 'left outer');
			$this->db->where_in("$this->t_product_firecode.firecode_test_id", $this->filters['list_firecodes_id']);
		}
	}

    function filter_cleaning()
    {
        if (is_array($this->filters['cleaning_id']) && count($this->filters['cleaning_id']) > 0) {
            if (!$this->db->table_is_included($this->t_product_cleaning)) $this->db->join($this->t_product_cleaning, "$this->t_product.id = $this->t_product_cleaning.product_id", 'left outer');
            $this->db->where_in("$this->t_product_cleaning.cleaning_id", $this->filters['cleaning_id']);
        }
    }

    function filter_finish()
    {
        if (is_array($this->filters['finish_id']) && count($this->filters['finish_id']) > 0) {
            if (!$this->db->table_is_included($this->t_product_finish)) $this->db->join($this->t_product_finish, "$this->t_product.id = $this->t_product_finish.product_id", 'left outer');
            $this->db->where_in("$this->t_product_finish.finish_id", $this->filters['finish_id']);
        }
    }

	function filter_lists()
	{
//		if (!isset($this->filters['list']['id']) && !isset($this->filters['list']['showroom'])) {
//			return;
//		}

		if (isset($this->filters['list']['archived']) and $this->filters['list']['archived']) {
			$i = $this->th_list_items;
			$s = $this->th_list_showrooms;
		} else {
			$i = $this->p_list_items;
			$s = $this->p_list_showrooms;
		}

		if (isset($this->filters['list']['id']) && $this->filters['list']['id'] > 0 && !$this->_is_master_price_list($this->filters['list']['id'])) {
			if (!$this->db->table_is_included($i)) $this->db->join("$i ListItems", "$this->t_item.id = ListItems.item_id", 'left outer');
			$this->db->where_in("ListItems.list_id", $this->filters['list']['id']);
			if (isset($this->filters['list']['active']) && $this->filters['list']['active']) {
				$this->db->where("ListItems.active", "1");
			}
			if (isset($this->filters['list']['item_info']) && $this->filters['list']['item_info']) {
				$this->db
				  ->select("ListItems.list_id")
				  ->select("ListItems.big_piece")
				  ->select("ListItems.active")
				  ->select("MIN(ListItems.n_order) as n_order");
			}
		}
		if (isset($this->filters['list']['showroom']) && is_array($this->filters['list']['showroom'])) {
			if (!$this->db->table_is_included($i)) $this->db->join("$i ListItems", "$this->t_item.id = ListItems.item_id", 'left outer');
			$this->db
			  ->join("$s ListShowrooms", "ListItems.list_id = ListShowrooms.list_id")
			  ->where_in("ListShowrooms.showroom_id", $this->filters['list']['showroom']);
		}

		// Check if we have any exclusion clause to be added
		if (isset($this->filters['list']['exclude_id']) && !is_null($this->filters['list']['exclude_id'])) {
			$_subquery = $this->_exclusion_subquery();
			$this->db->where("$this->t_item.id NOT IN ($_subquery)", NULL, FALSE);
		}
	}

	function _exclusion_subquery(){
		return "
			SELECT `$this->p_list_items`.`item_id`
			FROM `$this->p_list_items`
			WHERE `$this->p_list_items`.`list_id` IN (". implode(',', $this->filters['list']['exclude_id']) .")
		";
	}

	private function _is_master_price_list()
	{
		return (
		  (is_array($this->filters['list']['id']) && (in_array(0, $this->filters['list']['id']) || in_array("0", $this->filters['list']['id'])))
		  ||
		  $this->filters['list']['id'] === 0
		);
	}

	function where_date_ranges()
	{
        $ranges = $this->filters['dateRanges'];

        if(array_key_exists('table_attr', $ranges) and !is_null($ranges['table_attr'])){
            $table_attr = $ranges['table_attr'];
        } else {
            if(in_array('price', $this->filters['select'])){
                $table_attr = "$this->t_product_price.date";
            } else if (in_array('cost', $this->filters['select'])){
                $table_attr = "$this->t_product_cost.date";
            } else {
                $table_attr = "$this->t_product_price.date";
            }
        }
        $this->db->select("DATE_FORMAT($table_attr, '%m/%d/%Y') as _date");

		if (is_array($ranges) && (!is_null($ranges['from']) || !is_null($ranges['to']))) {

            $this->db->group_start();
            if (!is_null($ranges['from'])) {
                $this->db->where("$table_attr >=", $ranges['from']);
            }
            if (!is_null($ranges['to'])) {
                $this->db->where("$table_attr <=", $ranges['to']);
            }
            $this->db->group_end();

		}
	}

	function get_filter_options(){
		// Get available Lists
		$lists_results = $this->db
			->select("id, name")
			->from("$this->p_list")
//		    ->where('active', 'Y')
		    ->order_by("id")
			->get()->result_array()
		;

		$this->filter_options['multiselect']['list'] = decode_array($lists_results, 'id', 'name');

		return $this->filter_options;
    }

}