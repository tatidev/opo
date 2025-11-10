<?php

class Item_model extends MY_Model
{

	protected $t;

	function __construct()
	{
		parent::__construct();
		$this->model_table = $this->t_item;
		$this->defaults = array(
			// Default filters
		  'product_type' => null,
		  'product_id' => null,
		  'item_id' => null,
		  'datatable' => false,
		  'show_discontinued' => false,
		  'show_archived' => false,
		  'includeCosts' => false
		);
		$this->filters = array();
	}

	function get_product_name($type, $id)
	{
		switch ($type) {

			case constant('Regular'):
				$this->db->select("$this->t_product.name")
				  ->from($this->t_product)
				  ->where("$this->t_product.id", $id);
				$q = $this->db->get()->row_array();
				break;

			case constant('Digital'):
				$this->db->select("
					CONCAT(
						$this->t_digital_style.name, ' on ', 
						CASE WHEN $this->product_digital.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, 
						COALESCE($this->t_product.dig_product_name, $this->t_product.name), ' / ',
						GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ')
					) as name")
				  ->from($this->product_digital)
				  ->join($this->t_digital_style, "$this->product_digital.style_id = $this->t_digital_style.id")
				  ->join($this->t_item, "$this->t_item.id = $this->product_digital.item_id", 'left outer')
				  ->join($this->t_product, "$this->t_product.id = $this->t_item.product_id")
				  ->join($this->t_item_color, "$this->t_item_color.item_id = $this->t_item.id", 'left outer')
				  ->join($this->p_color, "$this->p_color.id = $this->t_item_color.color_id", 'left outer')
				  ->where("$this->product_digital.id", $id);
				$q = $this->db->get()->row_array();
				break;

			case constant('ScreenPrint'):
				$this->db->select("CONCAT($this->t_screenprint_style.name, ' on ', CASE WHEN $this->product_screenprint.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, $this->t_product.name, ' / ', GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ') ) as name")
				  ->from($this->product_screenprint)
				  ->join($this->t_screenprint_style, "$this->product_screenprint.style_id = $this->t_screenprint_style.id")
				  ->join($this->t_item, "$this->t_item.id = $this->product_screenprint.item_id", 'left outer')
				  ->join($this->t_product, "$this->t_product.id = $this->t_item.product_id")
				  ->join($this->t_item_color, "$this->t_item_color.item_id = $this->t_item.id", 'left outer')
				  ->join($this->p_color, "$this->p_color.id = $this->t_item_color.color_id", 'left outer')
				  ->where("$this->product_screenprint.id", $id);
				$q = $this->db->get()->row_array();
				break;
		}

		return (isset($q['name']) ? $q['name'] : '');
	}

	function get_item_info_for_tag($product_type, $item_id)
	{
		$this->filters = array_merge($this->defaults,
		  array(
			'product_type' => $product_type,
			'show_discontinued' => true,
			'show_archived' => true
		  )
		);
		//$this->selectors($product_type);
		$this->product_type = $product_type;
		$this->select_item_basics();
		$this->select_item_colors();
		$this->db->where("$this->t_item.id", $item_id);

		$query = $this->db->get();
		return $this->_return_if_non_empty($query, false);
// 		return $this->db->get()->row_array();
	}

	function get_item_price($item_id)
	{
		$t = $this->t_item;

		$this->select_item_prices();
		$this->db
		  ->from($t)
		  ->where("$t.id", $item_id);
		return $this->db->get()->row_array();
	}

	function selectors($product_type)
	{
		$this->product_type = $product_type;
		$this->select_item_basics();
		$this->set_datatables_variables();

		$this->select_item_colors();
		$this->select_item_prices();

		if ($this->filters['includeCosts']) $this->select_item_costs();
	}

	function select_item_basics()
	{
		switch ($this->product_type) {

			case constant('Regular'):
				$this->x2 = $this->t_product;
				$this->db
				  ->select("$this->t_item.id as item_id, $this->t_item.code, $this->t_item.in_ringset, $this->t_item.in_master, $this->t_product.in_master as product_in_master")
				  ->select("$this->t_product.id as product_id")
				  ->select("IF($this->t_item.code IS NULL, CONCAT_WS(' ', $this->p_vendor.abrev, $this->t_product.name), $this->t_product.name) as product_name")
				  ->select(
					"$this->p_product_status.name as status, 
					 $this->p_product_status.descr as status_abrev, 
					 $this->p_product_status.web_vis as web_vis, 
					 $this->p_product_status.id as status_id"
				  )
				  ->select("$this->p_stock_status.name as stock_status, $this->p_stock_status.descr as stock_status_abrev, $this->p_stock_status.id as stock_status_id")
				  ->select("$this->t_product.width as width")
				  ->select("$this->t_item.archived")
				  ->from("$this->t_item")
				  ->join($this->t_product, "$this->t_item.product_id = $this->t_product.id", 'left outer')
				  ->join($this->p_product_status, "$this->p_product_status.id = $this->t_item.status_id", "left outer")
				  ->join($this->p_stock_status, "$this->p_stock_status.id = $this->t_item.stock_status_id", 'left outer')
				  ->join($this->t_product_vendor, "$this->t_product.id = $this->t_product_vendor.product_id")
				  ->join($this->p_vendor, "$this->t_product_vendor.vendor_id = $this->p_vendor.id")
				  ->group_by("$this->t_item.id");
				break;

			case constant('Digital'):
				$this->x2 = $this->t_digital_style;
				$this->db
				  ->select("$this->t_item.id as item_id, $this->t_item.code, $this->t_item.in_ringset, $this->t_item.in_master, $this->product_digital.in_master as product_in_master")
				  ->select("$this->product_digital.id as product_id")
				  ->select("
						CONCAT(
							$this->t_digital_style.name, ' on ', 
							CASE WHEN $this->product_digital.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, 
							COALESCE($this->t_product.dig_product_name, $this->t_product.name), 
							' ', 
							GROUP_CONCAT(DISTINCT PC.name SEPARATOR ' / ')
						) as product_name")
				  ->select("$this->p_product_status.name as status, $this->p_product_status.descr as status_abrev, $this->p_product_status.id as status_id")
				  ->select("$this->p_stock_status.name as stock_status, $this->p_stock_status.descr as stock_status_abrev, $this->p_stock_status.id as stock_status_id")
				  ->select("COALESCE($this->t_product.dig_width, $this->t_product.width) as width")
				  ->select("$this->t_item.archived")
				  ->from("$this->t_item")
				  ->join($this->product_digital, "$this->product_digital.id = $this->t_item.product_id")
				  ->join("$this->t_item TT", "$this->product_digital.item_id = TT.id")
				  ->join("$this->t_product", "TT.product_id = $this->t_product.id")
				  ->join("$this->t_item_color IC", "TT.id = IC.item_id", 'left outer')
				  ->join("$this->p_color PC", "PC.id = IC.color_id", 'left outer')
				  ->join($this->t_digital_style, "$this->t_digital_style.id = $this->product_digital.style_id")
				  ->join($this->p_product_status, "$this->p_product_status.id = $this->t_item.status_id", "left outer")
				  ->join($this->p_stock_status, "$this->p_stock_status.id = $this->t_item.stock_status_id", 'left outer')
				  ->group_by("$this->t_item.id");
				break;

			case constant('ScreenPrint'):
				$this->x2 = $this->t_screenprint_style;
				$this->db
				  ->select("$this->t_item.id as item_id, $this->t_item.code, $this->t_item.in_ringset")
				  ->select("$this->product_screenprint.id as product_id, CONCAT($this->t_screenprint_style.name, ' on ', CASE WHEN $this->product_screenprint.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, $this->t_product.name, ' ', GROUP_CONCAT(DISTINCT PC.name SEPARATOR ' / ')) as product_name")
				  ->select("$this->p_product_status.name as status, $this->p_product_status.descr as status_abrev, $this->p_product_status.id as status_id")
				  ->select("$this->p_stock_status.name as stock_status, $this->p_stock_status.descr as stock_status_abrev, $this->p_stock_status.id as stock_status_id")
				  ->select("$this->t_product.width as width")
				  ->select("$this->t_item.archived")
				  ->from("$this->t_item")
				  ->join($this->product_screenprint, "$this->product_screenprint.id = $this->t_item.product_id")
				  ->join("$this->t_item TT", "$this->product_screenprint.item_id = TT.id")
				  ->join("$this->t_product", "TT.product_id = $this->t_product.id")
				  ->join("$this->t_item_color IC", "TT.id = IC.item_id", 'left outer')
				  ->join("$this->p_color PC", "PC.id = IC.color_id", 'left outer')
				  ->join($this->t_screenprint_style, "$this->t_screenprint_style.id = $this->product_screenprint.style_id")
				  ->join($this->p_product_status, "$this->p_product_status.id = $this->t_item.status_id", "left outer")
				  ->join($this->p_stock_status, "$this->p_stock_status.id = $this->t_item.stock_status_id", 'left outer')
				  ->group_by("$this->t_item.id");
				break;

			default:

				break;
		}
		$this->db->select("$this->t_item.product_type as product_type");
		//$this->db->where("$this->t_item.product_type", $this->product_type);

		if (isset($this->filters['show_discontinued']) && !$this->filters['show_discontinued']) {
			$this->where_item_is_not_discontinued();
		}
		if (isset($this->filters['show_archived']) && !$this->filters['show_archived']) {
			$this->where_product_is_not_archived();
			$this->where_item_is_not_archived();
		}
	}

	function select_item_shelfs()
	{
		$this->db
		  ->select("GROUP_CONCAT(DISTINCT $this->p_shelf.name ORDER BY $this->p_shelf.name SEPARATOR ' / ') as shelf")
		  ->select("GROUP_CONCAT(DISTINCT $this->t_item_shelf.shelf_id ORDER BY $this->t_item_shelf.shelf_id SEPARATOR ' / ') as shelf_id")
		  ->join($this->t_item_shelf, "$this->t_item.id = $this->t_item_shelf.item_id", 'left outer')
		  ->join($this->p_shelf, "$this->t_item_shelf.shelf_id = $this->p_shelf.id", 'left outer')
			;
	}

	function select_sampling_locations()
	{
		$this->db
		  ->select("RollLocation.name as roll_location")
		  ->select("BinLocation.name as bin_location")
		  ->select("$this->t_item.roll_location_id")
		  ->select("$this->t_item.bin_location_id")
		  ->select("$this->t_item.roll_yardage")
		  ->select("$this->t_item.bin_quantity")
//		  ->join($this->t_item_sampling, "$this->t_item.id = $this->t_item_sampling.item_id", 'left outer')
		  ->join("$this->p_sampling_locations RollLocation", "$this->t_item.roll_location_id = RollLocation.id", 'left outer')
		  ->join("$this->p_sampling_locations BinLocation", "$this->t_item.bin_location_id = BinLocation.id", 'left outer')
		;
	}

	function select_item_stock()
	{
		$t = $this->model->t_product_stock;
		$this->db
			->select("Stock.id as sales_id, CONCAT(Stock.name, ' / ', COALESCE(Stock.code, ''), ' / ', Stock.color) sales_name")
		    ->select("Stock.yardsInStock, Stock.yardsOnHold, Stock.yardsAvailable, Stock.yardsOnOrder, Stock.yardsBackorder")
			->join("$t Stock", "$this->t_item.id = Stock.master_item_id", "left outer")
			;

//		$sales_products = $this->db_sales . ".op_products";
//		$sales_bolts = $this->db_sales . ".op_products_bolts";
//
//		$this->db
//		  ->select("$sales_products.id as sales_id")
//		  ->join("$sales_products", "$this->t_item.id = $sales_products.master_item_id", 'left outer')
//		  ->select("
//				(
//					SELECT SUM($sales_bolts.yardsInStock)
//					FROM $sales_bolts
//					WHERE $sales_products.id = $sales_bolts.idProduct
//					GROUP BY $sales_products.master_item_id
//				) as yardsInStock,
//
//				(
//					SELECT SUM($sales_bolts.yardsOnHold)
//					FROM $sales_bolts
//					WHERE $sales_products.id = $sales_bolts.idProduct
//					GROUP BY $sales_products.master_item_id
//				) as yardsOnHold,
//
//				(
//					SELECT SUM($sales_bolts.yardsAvailable)
//					FROM $sales_bolts
//					WHERE $sales_products.id = $sales_bolts.idProduct
//					GROUP BY $sales_products.master_item_id
//				) as yardsAvailable,
//
//				(
//					SELECT SUM(PO.yardsOrdered)
//          FROM $this->db_sales.op_purchase_order PO
//					WHERE $sales_products.id = PO.idProduct
//					AND PO.lastStage IN (1, 2)
//					GROUP BY $sales_products.master_item_id
//				) as yardsOnOrder,
//
//				(
//					SELECT SUM(OPB.yards)
//          FROM $this->db_sales.op_orders_products_bolts OPB
//          JOIN $this->db_sales.op_orders_header OH ON OPB.idOrder = OH.id
//					WHERE $sales_products.id = OPB.idProduct
//					AND OH.stage = 'BACKORDER'
//					GROUP BY $sales_products.master_item_id
//				) as yardsBackorder
//		");
	}

	function select_item_colors()
	{
		$t = $this->t_item;
		$c = $this->t_item_color;
		$p = $this->p_color;
		$this->db
		  ->select("GROUP_CONCAT(DISTINCT $p.name ORDER BY $c.n_order SEPARATOR ' / ') as color, GROUP_CONCAT(DISTINCT $p.id ORDER BY $c.n_order SEPARATOR ' / ') as color_ids")
		  ->join($c, "$t.id = $c.item_id")
		  ->join($p, "$c.color_id = $p.id");
	}

    function select_item_reselections()
    {
        $t = $this->t_item;
        $r = $this->t_item_reselection;
        $this->db
            ->select("GROUP_CONCAT(DISTINCT L.item_id_1 SEPARATOR ',') as reselections_ids")
            ->join("$r L", "$t.id = L.item_id_0", "left");
    }

    function select_item_reselections_of()
    {
        $t = $this->t_item;
        $r = $this->t_item_reselection;
        $this->db
            ->select("GROUP_CONCAT(DISTINCT R.item_id_0 SEPARATOR ',') as reselections_ids_of")
            ->join("$r R", "$t.id = R.item_id_1", "left");
	}

	function select_item_showcase()
	{
		$this->db
		  ->select("$this->t_showcase_item.visible as showcase_visible")
		  ->select("$this->t_showcase_product.url_title")
		  ->select("$this->t_showcase_item.n_order")
		  ->select("$this->t_showcase_item.pic_big")
		  ->select("$this->t_showcase_item.pic_big_url")
		  ->select("$this->t_showcase_item.pic_hd")
		  ->select("$this->t_showcase_item.pic_hd_url")
		  ->join("$this->t_showcase_item", "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer')
		  ->join("$this->t_showcase_product", "$this->t_item.product_id = $this->t_showcase_product.product_id", 'left outer')
		  ->select("GROUP_CONCAT( $this->t_showcase_item_coord_color.coord_color_id SEPARATOR ' / ' ) as showcase_coord_color_id")
		  ->join("$this->t_showcase_item_coord_color", "$this->t_item.id = $this->t_showcase_item_coord_color.item_id", 'left outer');
	}

	function select_item_messages($item_id)
	{
		$m = $this->t_item_messages;
		$this->db
		  ->select("$m.id, $m.message as message_note, COALESCE($m.date_modif, $m.date_add) as date_modif, $this->auth_users.username, $m.user_id")
		  ->from($m)
		  ->join($this->t_item, "$m.item_id = $this->t_item.id")
		  ->join($this->auth_users, "$m.user_id = $this->auth_users.id")
		  ->where("$m.item_id", $item_id)
		  ->order_by("$m.date_modif DESC")
		  ->limit(10);
		return $this->db->get()->result_array();
	}

	function select_item_prices($product_id = null, $product_type = null)
	{
		$t = $this->t_item;
		$t_prices = $this->t_product_price;
		if (!is_null($product_id) && !is_null($product_type)) {
			$this->db
                ->select("$t_prices.p_res_cut")
                ->select("$t_prices.p_hosp_roll")
			    ->select("$t_prices.p_hosp_cut")
			  ->from($t_prices)
			  ->where("product_id", $product_id)
			  ->where("product_type", $product_type);
			return $this->db->get()->row_array();
			//->join($t_prices, "$t.product_id = $t_prices.product_id AND $t.product_type = $t_prices.product_type", 'left outer');
		} else {
			$this->db
                ->select("$t_prices.p_res_cut")
                ->select("$t_prices.p_hosp_roll")
			    ->select("$t_prices.p_hosp_cut")
			  ->join($t_prices, "$t.product_id = $t_prices.product_id AND $t.product_type = $t_prices.product_type", 'left outer');
		}
	}

	function select_item_content_front($id = null)
	{
		if (is_null($id)) {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT $this->t_product_content_front.perc, '% ', PC1.name ORDER BY $this->t_product_content_front.perc DESC SEPARATOR ' / ' ) as content_front")
			  ->join($this->t_product_content_front, "$this->t_product_content_front.product_id = $this->t_product.id", 'left outer')
			  ->join($this->p_content . " PC1 ", "$this->t_product_content_front.content_id = PC1.id", "left outer");
		} else {
			$this->db->select("PC1.id, $this->t_product_content_front.perc, PC1.name")
			  ->from($this->t_product_content_front)
			  ->join($this->p_content . " PC1 ", "$this->t_product_content_front.content_id = PC1.id", 'left outer')
			  ->where("$this->t_product_content_front.product_id", $id)
			  ->order_by("$this->t_product_content_front.perc DESC");
			return $this->db->get()->result_array();
		}
	}

    function get_item($item_id, $product_type = null){

		
		$this->db->select([
			'T_ITEM.product_type AS product_type',
			'T_ITEM.archived',
			'T_ITEM.code AS code',
			"GROUP_CONCAT(DISTINCT P_SHELF.name ORDER BY P_SHELF.name SEPARATOR ' / ') AS shelf",
			"GROUP_CONCAT(DISTINCT T_ITEM_SHELF.shelf_id ORDER BY T_ITEM_SHELF.shelf_id SEPARATOR ' / ') AS shelf_id",
			'RollLocation.name AS roll_location',
			'BinLocation.name AS bin_location',
			'T_ITEM.roll_location_id',
			'T_ITEM.bin_location_id',
			'T_ITEM.roll_yardage',
			'T_ITEM.bin_quantity',
			'Stock.id AS sales_id',
			"CONCAT(Stock.name, ' / ', COALESCE(Stock.code, ''), ' / ', Stock.color) AS sales_name",
			'Stock.yardsInStock',
			'Stock.yardsOnHold',
			'Stock.yardsAvailable',
			'Stock.yardsOnOrder',
			'Stock.yardsBackorder',
			"GROUP_CONCAT(DISTINCT P_COLOR.name ORDER BY T_ITEM_COLOR.n_order SEPARATOR ' / ') AS color",
			"GROUP_CONCAT(DISTINCT P_COLOR.id ORDER BY T_ITEM_COLOR.n_order SEPARATOR ' / ') AS color_ids",
			'SHOWCASE_ITEM.visible AS showcase_visible',
			'SHOWCASE_PRODUCT.url_title',
			'SHOWCASE_PRODUCT.visible AS parent_product_visibility',
			'SHOWCASE_ITEM.n_order',
			'SHOWCASE_ITEM.pic_big',
			'SHOWCASE_ITEM.pic_big_url',
			'SHOWCASE_ITEM.pic_hd',
			'SHOWCASE_ITEM.pic_hd_url',
			"GROUP_CONCAT(SHOWCASE_ITEM_COORD_COLOR.coord_color_id SEPARATOR ' / ') AS showcase_coord_color_id",
			"GROUP_CONCAT(DISTINCT L.item_id_1 SEPARATOR ', ') AS reselections_ids",
			"GROUP_CONCAT(DISTINCT R.item_id_0 SEPARATOR ', ') AS reselections_ids_of",
			'T_PRODUCT_VARIOUS.vendor_product_name',
			'T_ITEM.vendor_color',
			'T_ITEM.vendor_code',
			'T_ITEM.min_order_qty',
			'T_ITEM.web_vis',
			'T_PRODUCT_VARIOUS.min_order_qty AS min_order_qty_p'
		]);
		
		$this->db->from('T_ITEM');
		
		// LEFT JOINS
		$this->db->join('T_ITEM_SHELF', 'T_ITEM.id = T_ITEM_SHELF.item_id', 'left');
		$this->db->join('P_SHELF', 'T_ITEM_SHELF.shelf_id = P_SHELF.id', 'left');
		$this->db->join('P_SAMPLING_LOCATIONS RollLocation', 'T_ITEM.roll_location_id = RollLocation.id', 'left');
		$this->db->join('P_SAMPLING_LOCATIONS BinLocation', 'T_ITEM.bin_location_id = BinLocation.id', 'left');
		$this->db->join($this->db_sales.'.op_products_stock Stock', 'T_ITEM.id = Stock.master_item_id', 'left');
		$this->db->join('SHOWCASE_ITEM', 'T_ITEM.id = SHOWCASE_ITEM.item_id', 'left');
		$this->db->join('SHOWCASE_PRODUCT', 'T_ITEM.product_id = SHOWCASE_PRODUCT.product_id', 'left');
		$this->db->join('SHOWCASE_ITEM_COORD_COLOR', 'T_ITEM.id = SHOWCASE_ITEM_COORD_COLOR.item_id', 'left');
		$this->db->join('T_ITEM_RESELECTION L', 'T_ITEM.id = L.item_id_0', 'left');
		$this->db->join('T_ITEM_RESELECTION R', 'T_ITEM.id = R.item_id_1', 'left');
		$this->db->join('T_PRODUCT_VARIOUS', 'T_ITEM.product_id = T_PRODUCT_VARIOUS.product_id', 'left');
		


		
		// INNER JOINS
		$this->db->join('T_ITEM_COLOR', 'T_ITEM.id = T_ITEM_COLOR.item_id', 'inner');
		$this->db->join('P_COLOR', 'T_ITEM_COLOR.color_id = P_COLOR.id', 'inner');
		
		// WHERE CLAUSES
		$this->db->where('T_ITEM.id', $item_id);
		$this->db->where('T_ITEM.product_type', $product_type);
		//$this->db->where("T_ITEM.archived", 'NO');
		
		$query = $this->db->get();
		return $query->result_array(); // or ->row_array() if expecting a single record

	}


	/*function get_item($item_id, $product_type = null)
	{
		
		$this->product_type = $product_type;
		if (is_null($product_type) || $product_type == 'item_id') {
			$this->product_type = $this->get_item_type($item_id);
		}

		$this->select_item_basics();
		$this->select_item_shelfs();
		$this->select_sampling_locations();
		$this->select_item_stock();
		$this->select_item_colors();
		$this->select_item_showcase();
        $this->select_item_reselections();
        $this->select_item_reselections_of();

		$this->db
		  ->select("$this->t_product_various.vendor_product_name")
		  ->select("$this->t_item.vendor_color, $this->t_item.vendor_code")
		  ->select("$this->t_item.min_order_qty")
		  ->select("$this->t_product_various.min_order_qty as min_order_qty_p")
		  ->join($this->t_product_various, "$this->t_item.product_id = $this->t_product_various.product_id", 'left outer')
		;

		$this->db->where("$this->t_item.id", $item_id);

		if (!is_null($this->product_type)) {
			$this->db->where("$this->t_item.product_type", $this->product_type);
		}

		return $this->db->get()->row_array();
	} */

	function get_item_parent_and_type($item_id)
	{
		$this->db
		  ->select('product_id, product_type')
		  ->from($this->t_item)
		  ->where("id", $item_id);
		return $this->db->get()->row_array();
	}

	function get_item_type($item_id)
	{
		$this->db
		  ->select('product_type')
		  ->from($this->t_item)
		  ->where("id", $item_id);
		return $this->db->get()->row()->product_type;
	}

    function get_item_reselections($item_id){
        $this->db
            ->select('item_id_1')
            ->from($this->t_item_reselection)
            ->where("item_id_0", $item_id);
        return $this->db->get()->row_array();
    }

    function get_item_status_id($item_id){
        $this->db
            ->select('status_id')
            ->from($this->t_item)
            ->where("id", $item_id);
        return $this->db->get()->row_array();
	}

	function save_item($data, $item_id = null)
	{
        
		// echo "<pre> CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " </pre>";
		// echo "<br> data: <br>";
		// print_r($data); echo "<br>";
		// echo "<br> item_id: $item_id <br>";
		// echo "</pre>";

		$t = $this->t_item;
		if ($item_id === null) {
			// NOTE: Msy need to run $sql> SET SESSION sql_mode = '';
			//       Strict mode = OFF in the database due to legacy code
			// Create new
			$this->db
			->set('date_add', 'NOW()', false)
			->set($data)
			->insert($t);
		
		    $insert_id = $this->db->insert_id();
		    
		    if ($insert_id) {
		    	//echo "Inserted ID: " . $insert_id;
				return $insert_id;
		    } else {
		    	echo "ERROR: No ID returned. CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ ;
		    }
		} else {
		 
			$this->db
			  ->set($data)
			  ->where('id', $item_id)
			  ->update($t);
		}
	}

    function save_reselections($items, $item_id)
    {
        $this->clean_logics($item_id, $this->t_item_reselection, 'item_id_0');
        $this->db->trans_start();
        foreach ($items as $item) {
            $insert_query = $this->db->insert_string($this->t_item_reselection, $item);
            $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
            $this->db->query($insert_query);
        }
        $this->db->trans_complete();
	}

	function save_item_colors($data, $id)
	{
		// To implement with new database
		//$this->log_item_color($id);
		$this->clean_logics($id, $this->t_item_color, 'item_id');
		$this->db->insert_batch($this->t_item_color, $data);
	}

	function save_item_shelfs($data, $id)
	{
		$this->clean_logics($id, $this->t_item_shelf, 'item_id');
		(count($data) > 0 ? $this->db->insert_batch($this->t_item_shelf, $data) : '');
	}

//	function save_item_sampling_location($data, $id)
//	{
//		$this->clean_logics($id, $this->t_item_sampling, 'item_id');
//		$this->db->insert($this->t_item_sampling, $data);
//	}
	
//	function update_item_sampling_location($data, $id)
//	{
//		$this->db
//			->set('date_modif', 'NOW()', FALSE)
//			->set($data)
//			->where("item_id", $id)
//			->update($this->t_item_sampling);
//	}

	function save_item_messages($data, $id = null)
	{
		$t = $this->t_item_messages;
		if (!is_null($id)) {
			$this->db
			  ->set($data)
			  ->where("id", $id)
			  ->update($t);
		} else {
			$this->db
			  ->set($data)
			  ->set("date_add", "NOW()", false)
			  ->insert($t);
		}

	}

	function save_showcase_basic($data, $item_id)
	{
		$q = $this->db
		  ->where('item_id', $item_id)
		  ->get($this->t_showcase_item);
 
		if ($q->num_rows() > 0) {
			 
			$this->db
			  ->set($data)
			  ->where('item_id', $item_id)
			  ->update($this->t_showcase_item);
		} else {
			$this->db
			  ->set($data)
			  ->set('item_id', $item_id)
			  ->insert($this->t_showcase_item);
		}
	}

	function save_showcase_coord_color($data, $product_id)
	{
		$t = $this->t_showcase_item_coord_color;
		//$this->log_product_weave($product_id);
		$this->clean_logics($product_id, $t, 'item_id');
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_item_sales_id($item_id, $sales_id){
		$t = "$this->db_sales.op_products";

		# Clean previous connection
		$this->db
			->set("master_item_id", NULL)
			->where("master_item_id", $item_id)
			->update($t);

		# Do next connection
		$this->db
			->set("master_item_id", $item_id)
			->where("id", $sales_id)
			->update($t);

		$this->db->query("call $this->db_sales.proc_update_products_stock();");
	}

	function archive_item($item_id)
	{
		$this->db
		  ->set('archived', 'Y')
		  ->where('id', $item_id)
		  ->update($this->t_item);
	}

	function retrieve_item($item_id)
	{
		$this->db
		  ->set('archived', 'N')
		  ->where('id', $item_id)
		  ->update($this->t_item);
		return $this->get_item($item_id);
	}

	function is_unique_code($item_id, $code)
	{
		// Check against ALL items (including archived) since archived items may be reused in future
		$this->db->select('I.id')
		  ->from("$this->t_item I")
		  ->where("I.id !=", $item_id)  // Exclude current item (for edits)
		  ->where('I.code', $code);     // Check for this specific code
		  // Removed archived filters - now checks ALL items regardless of archive status
		$num = $this->db->get()->num_rows();
		return ($num === 0);  // Returns true if code is unique (0 matches found)
	}

	/**
	 * Generate a unique random item code in nnnn-nnnn format
	 * 
	 * SECURITY: Uses random_int() for cryptographically secure randomness
	 * VALIDATION: Ensures uniqueness against existing non-archived items
	 * RATE LIMITING: Limited attempts to prevent infinite loops
	 * 
	 * @return string Unique item code in format nnnn-nnnn
	 * @throws Exception If unable to generate unique code after max attempts
	 */
	function generate_unique_item_code()
	{
		// Security: Limit attempts to prevent infinite loops/DoS
		$max_attempts = 100;
		$attempt = 0;
		
		do {
			try {
				// Security: Use cryptographically secure random number generation
				$first_part = random_int(1000, 9999);
				$second_part = random_int(1000, 9999);
				
				// Format: nnnn-nnnn (exactly 9 characters)
				$code = sprintf('%04d-%04d', $first_part, $second_part);
				
			} catch (Exception $e) {
				// Fallback to mt_rand if random_int fails (though it shouldn't in PHP 7+)
				$first_part = mt_rand(1000, 9999);
				$second_part = mt_rand(1000, 9999);
				$code = sprintf('%04d-%04d', $first_part, $second_part);
				
				// Log the fallback for monitoring
				log_message('warning', 'Item code generation fell back to mt_rand: ' . $e->getMessage());
			}
			
			$attempt++;
			
		} while (!$this->is_unique_code(0, $code) && $attempt < $max_attempts);
		
		// Security: Prevent infinite loops, throw exception if unable to generate
		if ($attempt >= $max_attempts) {
			log_message('error', 'Unable to generate unique item code after ' . $max_attempts . ' attempts');
			throw new Exception('Unable to generate unique item code. Please try again or contact administrator.');
		}
		
		// Log successful generation for audit trail
		log_message('info', 'Generated unique item code: ' . $code . ' (attempt ' . $attempt . ')');
		
		return $code;
	}

	/**
	 * Validate item code format (letters allowed for all users)
	 * 
	 * SECURITY: Server-side validation of code format
	 * VALIDATION: Strict pattern matching for nnnn-nnnn[A-Za-z]? format
	 * 
	 * @param string $code The item code to validate
	 * @param bool $is_admin Whether the current user is an admin (deprecated parameter, kept for compatibility)
	 * @return array Validation result with 'valid' boolean and 'message' string
	 */
	function validate_item_code_format($code, $is_admin = false)
	{
		// Input sanitization: trim whitespace
		$code = trim($code);
		
		// Check if empty
		if (empty($code)) {
			return array('valid' => false, 'message' => 'Item code is required.');
		}
		
		// Security: Validate length to prevent buffer overflow attempts
		if (strlen($code) > 10) {
			return array('valid' => false, 'message' => 'Item code is too long. Maximum 10 characters.');
		}
		
		// Base pattern: nnnn-nnnn (exactly 4 digits, dash, 4 digits)
		$base_pattern = '/^[0-9]{4}-[0-9]{4}$/';
		
		// Extended pattern: nnnn-nnnnX (base pattern + single letter) - now allowed for all users
		$extended_pattern = '/^[0-9]{4}-[0-9]{4}[A-Za-z]$/';
		
		// Validate base format
		if (preg_match($base_pattern, $code)) {
			return array('valid' => true, 'message' => 'Valid item code format.');
		}
		
		// Check if it matches extended pattern (now allowed for all users)
		if (preg_match($extended_pattern, $code)) {
			return array('valid' => true, 'message' => 'Valid item code format with letter suffix.');
		}
		
		// Invalid format
		return array('valid' => false, 'message' => 'Invalid format. Use: nnnn-nnnn or nnnn-nnnnX (where X is a letter)');
	}

	function total_items()
	{
		return $this->db->count_all($this->t_item);
	}

	function typeahead_colors($search, $filter = array(), $item_id = null)
	{
		$t = $this->p_color;
		$this->db->select("$t.id, $t.name as label")
		  ->from($t)
		  ->like("$t.name", $search)
		  ->order_by("$t.name");
		if (!empty($filter)) {
			$this->db->where_not_in("$t.id", $filter);
		}
		return $this->db->get()->result_array();
	}

	function select_item_costs($id = null, $return = false)
	{
		$t = $this->t_product_cost;

		if (is_null($id)) {
			$this->db
			  ->select("$t.fob")
			  ->select("CASE WHEN $t.cost_cut != '0.00' THEN GROUP_CONCAT(DISTINCT P1.name, ' ', $t.cost_cut) ELSE '-' END as cost_cut")
			  ->select("CASE WHEN $t.cost_half_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P2.name, ' ', $t.cost_half_roll) ELSE '-' END as cost_half_roll")
			  ->select("CASE WHEN $t.cost_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P3.name, ' ', $t.cost_roll) ELSE '-' END as cost_roll")
			  ->select("CASE WHEN $t.cost_roll_landed != '0.00' THEN GROUP_CONCAT(DISTINCT P4.name, ' ', $t.cost_roll_landed) ELSE '-' END as cost_roll_landed")
			  ->select("CASE WHEN $t.cost_roll_ex_mill != '0.00' THEN GROUP_CONCAT(DISTINCT P5.name, ' ', $t.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill")
//			  ->select("$t.cost_roll_ex_mill_text")
			  ->select("SUBSTRING($t.date, 1, 10) as cost_date")
			  ->join($t, "$this->t_product.id = $t.product_id", 'left outer')
			  ->join("$this->p_price_type P1", "$t.cost_cut_type_id = P1.id", "left outer")
			  ->join("$this->p_price_type P2", "$t.cost_half_roll_type_id = P2.id", "left outer")
			  ->join("$this->p_price_type P3", "$t.cost_roll_type_id = P3.id", "left outer")
			  ->join("$this->p_price_type P4", "$t.cost_roll_landed_type_id = P4.id", "left outer")
			  ->join("$this->p_price_type P5", "$t.cost_roll_ex_mill_type_id = P5.id", "left outer");
		} else {
			$this->db->select("$t.fob")
			  ->select("$t.cost_cut_type_id, $t.cost_cut")
			  ->select("$t.cost_half_roll_type_id, $t.cost_half_roll")
			  ->select("$t.cost_roll_type_id, $t.cost_roll")
			  ->select("$t.cost_roll_landed_type_id, $t.cost_roll_landed")
			  ->select("$t.cost_roll_ex_mill_type_id, $t.cost_roll_ex_mill")
//			  ->select("$t.cost_roll_ex_mill_text")
			;
			if ($return) {
				$this->db
				  ->from($t)
				  ->where("$t.product_id", $id);
				return $this->db->get()->result_array();
			} else {
				$this->db
				  ->join($t, "$t.product_id = $this->t_product.id", "left outer");
			}

		}

		//array_push($this->colSearch, "$t.cost_cut", "$t.cost_half_roll", "$t.cost_roll", "$t.cost_roll_landed", "$t.cost_roll_ex_mill");
	}

	function get_item_presence($item_id)
	{
		return $this->db
		  ->distinct()
		  ->select("L.name, LI.date_add, IF(LI.date_modif='0000-00-00 00:00:00','-',LI.date_modif), U.username")
		  ->from("$this->p_list_items LI")
		  ->join("$this->p_list L", "LI.list_id = L.id")
		  ->join("V_USER U", "LI.user_id = U.id", 'left outer')
		  ->where("LI.item_id", $item_id)
		  ->group_by("LI.list_id")
		  ->order_by("L.date_add DESC")
		  ->get()->result_array();
	}

    function save_in_sales($data){

    }

    function get_items($item_ids)
    {
        $t = $this->t_item;
        $p = $this->t_product;
        $this->select_item_colors();
        $this->db->select("$t.id, $t.product_id, $t.code, $t.archived");
        $this->db->from($t);
        $this->db->select("$p.name");
        $this->db->join($p, "$t.product_id = $p.id", 'left outer');
        $this->db->where_in("$t.id", $item_ids);
        $this->db->group_by("$t.id");
        return $this->db->get()->result_array();
    }
}

?>