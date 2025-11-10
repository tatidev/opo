<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/models/Search_model.php';

class Reports_model extends Search_model
{
	public $report_filters = [
	  'showroom_ids' => [],
	  'roadkit_ids' => [],
	  'min_qty' => 10,
	  'dateRanges' => null, // [ from=>date, to=>date ], dates used for price/costs update
	];

	function __construct()
	{
		parent::__construct();
	}

	function get_popular_items($params)
	{
		$this->filters = array_merge($this->filters, $this->report_filters, $params);

		$this->db
		    ->select("VI.item_id, VI.product_name, VI.code, VI.color")
		    ->select("VI.in_ringset, VI.stock_status, VI.status")
			->select("SUM(OrderItems.QUANTITY) as total_samples_ordered")
			->from("$this->db_roadkit.T_ORDER_ITEMS OrderItems")
			->join("$this->db_roadkit.T_ORDER Order", "OrderItems.ORDER_ID = Order.ID")
			->join("$this->db_thismaster.V_ITEM VI", "OrderItems.ITEM_ID = VI.item_id")
			->group_by("OrderItems.ITEM_ID");

		if (!empty($this->filters['showroom_ids'])) $this->filter_showrooms();

		if (!empty($this->filters['roadkit_ids'])) $this->filter_roadkits();

		if (!empty($this->filters['dateRanges'])) $this->where_date_ranges();

		$this->db->having("total_samples_ordered >=", $this->filters['min_qty']);

		$this->db
		  ->order_by("total_samples_ordered DESC")
		  ->limit(50);


		if( $this->filters['get_compiled_select'] ){
			return $this->db->get_compiled_select();
		} else {
			return $this->db->get()->result_array();
		}
	}

	function filter_showrooms(){
		$this->db->where_in("Order.SHOWROOM_ID", $this->filters['showroom_ids']);
	}

	function filter_roadkits(){
		$this->db->where_in("Order.ROADKIT_ID", $this->filters['roadkit_ids']);
	}

	//function prev_search_discontinues($filters){
	//	$this->db
	//		->select("V.name as vendor, RI.product_id, RI.item_id, RI.product_name, RI.code, RI.color, RI.status, RI.change_date")
	//		->from("PROC_ITEM_STATUS_CHANGE RI")
	//		->join("V_PRODUCT_VENDOR V", "RI.product_id = V.vendor_id", "left outer")
	//	;
//
	//	if(!is_null($filters['date_from'])){
	//		$this->db->where("change_date >", $filters['date_from']);
	//	}
	//	if(!is_null($filters['date_to'])){
	//		$this->db->where("change_date <", $filters['date_to']);
	//	}
	//	if(!is_null($filters['status_id'])){
	//		$this->db->where_in("status_id", $filters['status_id']);
	//	}
	//	$sql = $this->db->get_compiled_select();
	//	//echo '<pre>'; print_r($sql); echo '</pre>'; die();
	//	return $this->db->get()->result_array();
	//}

	//i.code, p.name AS product, c.name as color, i.product_id,  
	// s.name AS prod_status, ss.name AS stock_status, s.id AS statusId, 
	// i.date_modif AS ModifiedOn, v.name AS vendor

	function search_discontinues($filters){
        //pre tags and print_r of filters
		// echo '<pre> Filters: '. __FILE__ ."<br>"; 
		// print_r($filters); 
		// echo '</pre>';
		$selStr  = 'i.code, p.name AS product_name, c.name as color, i.product_id, ';
		$selStr .= 's.name AS status, ss.name AS stock_status, s.id AS statusId, ';
		$selStr .= 'i.date_modif AS ModifiedOn, v.name AS vendor';


		$this->db
		->select($selStr)
		->from('T_ITEM i')
		->join('T_PRODUCT p', 'p.id = i.product_id')
		->join('P_PRODUCT_STATUS s', 'i.status_id = s.id')
		->join('P_STOCK_STATUS ss', 'i.stock_status_id = ss.id')
		->join('V_PRODUCT_VENDOR pv', 'pv.product_id = p.id')
		->join('Z_VENDOR v', 'v.id = pv.vendor_id')
		->join('T_ITEM_COLOR ic', 'ic.item_id = i.id')
		->join('P_COLOR c', 'c.id = ic.color_id')
		->group_start() // Begin grouping the OR conditions
			->where('s.name', 'DISCO')
			->or_where('ss.name', 'DISCO')
		->group_end() // End grouping the OR conditions
		->order_by('i.date_modif', 'DESC');
	
		if(!is_null($filters['date_from'])){
			$this->db->where("i.date_modif > ", $filters['date_from']);
		}
		if(!is_null($filters['date_to'])){
			$this->db->where("i.date_modif < ", $filters['date_to']);
		}
		//$sql = $this->db->get_compiled_select();
		//echo '<pre>'; print_r($sql); echo '</pre>';
	    $query = $this->db->get();
	    return $query->result_array();

	}



	function where_date_ranges(){
		if( !is_null($this->filters['dateRanges']['from']) ){
			$this->db->where("Order.DATE_CREATED >=", $this->filters['dateRanges']['from']);
		}
		if( !is_null($this->filters['dateRanges']['to']) ){
			$this->db->where("Order.DATE_CREATED <=", $this->filters['dateRanges']['from']);
		}
	}

    function get_shelf_additions($filters){
//        var_dump($filters);
        $queryDir = getcwd() . "/application/models/queries/report_shelf_additions.sql";
        $queryStr = file_get_contents($queryDir);
        $queryStr = str_replace("{date_to}", $filters["date_to"], $queryStr);
        $queryStr = str_replace("{date_from}", $filters["date_from"], $queryStr);

        return $this->get_shelf_apply_filters($queryStr, $filters);
    }

    function get_shelf_removals($filters){
//        var_dump($filters);
        $queryDir = getcwd() . "/application/models/queries/report_shelf_removals.sql";
        $queryStr = file_get_contents($queryDir);
        $queryStr = str_replace("{date_to}", $filters["date_to"], $queryStr);
        $queryStr = str_replace("{date_from}", $filters["date_from"], $queryStr);

        return $this->get_shelf_apply_filters($queryStr, $filters);
    }

    function get_shelf_apply_filters($queryStr, $filters){
        $where_vendor_in = "";
        if(!is_null($filters["vendor_id"]) and count($filters["vendor_id"]) > 0){
            $where_vendor_in = "WHERE V.vendor_id IN (".implode(',', $filters["vendor_id"]).")";
        }
        $queryStr = str_replace("{optional_where}", $where_vendor_in, $queryStr);

        $filter_shelf_id = "";
        if(!is_null($filters["shelf_id"]) and count($filters["shelf_id"]) > 0){
            $filter_shelf_id = "AND A.shelf_id IN (".implode(',', $filters["shelf_id"]).")";
        }
        $queryStr = str_replace("{filter_shelf_id}", $filter_shelf_id, $queryStr);

        $group_by = "";
        if($filters["group_by"] == product){
            // Make changes to query
            $group_by = "GROUP BY I.product_id";
            $queryStr = str_replace("I.status,", "GROUP_CONCAT(DISTINCT I.status) as status,", $queryStr);
            $queryStr = str_replace("I.stock_status,", "GROUP_CONCAT(DISTINCT I.stock_status) as stock_status,", $queryStr);
            $queryStr = str_replace("I.code,", "'' as code,", $queryStr);
            $queryStr = str_replace("I.color,", "'' as color,", $queryStr);
            $queryStr = str_replace("A.shelf_id,", "GROUP_CONCAT(DISTINCT A.shelf_id) as shelf_id,", $queryStr);
        }

        $queryStr = str_replace("{optional_group_by}", $group_by, $queryStr);

        return $this->db->query($queryStr)->result_array();
    }

	function search_mso_wto($filters){
        //pre tags and print_r of filters
		// echo '<pre> Filters: '. __FILE__ ."<br>"; 
		// print_r($filters); 
		// echo '</pre>';
		$selStr  = 'i.code, p.name AS product_name, c.name as color, i.product_id, ';
		$selStr .= 's.name AS status, ss.name AS stock_status, s.id AS statusId, ';
		$selStr .= 'i.date_modif AS ModifiedOn, v.name AS vendor';


		$this->db
		->select($selStr)
		->from('T_ITEM i')
		->join('T_PRODUCT p', 'p.id = i.product_id')
		->join('P_PRODUCT_STATUS s', 'i.status_id = s.id')
		->join('P_STOCK_STATUS ss', 'i.stock_status_id = ss.id')
		->join('V_PRODUCT_VENDOR pv', 'pv.product_id = p.id')
		->join('Z_VENDOR v', 'v.id = pv.vendor_id')
		->join('T_ITEM_COLOR ic', 'ic.item_id = i.id')
		->join('P_COLOR c', 'c.id = ic.color_id')
		->group_start() // Begin grouping the OR conditions
			->where('s.name', 'MSO')
			->or_where('ss.name', 'WTO')
		->group_end() // End grouping the OR conditions
		->order_by('i.date_modif', 'DESC');
	
		if(!is_null($filters['date_from'])){
			$this->db->where("i.date_modif > ", $filters['date_from']);
		}
		if(!is_null($filters['date_to'])){
			$this->db->where("i.date_modif < ", $filters['date_to']);
		}
		//$sql = $this->db->get_compiled_select();
		//echo '<pre>'; print_r($sql); echo '</pre>';
	    $query = $this->db->get();
	    return $query->result_array();
	}


}

?>