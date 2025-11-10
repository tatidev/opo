<?php

class Restock_model extends MY_Model
{
	public $filters = [
	  'ids' => [],
	  'only_completed' => false, // false=pendings, true=completed
	  'include_items_description' => true,
	  'include_stock' => false,
	  'by_restock_status_id' => [],
	  'destination_id' => [],
	  'status_id' => [],
	  'date_from' => null,
	  'date_to' => null,
	  'limit' => null  // Memory protection: limit large datasets to prevent PHP memory exhaustion
	];

	function __construct()
	{
		parent::__construct();
		$this->load->database('opuzen_dev_roadkit_init');
		$this->load->database('opuzen_dev_roadkit');
		$this->load->database('opuzen_dev_sales');
		$this->load->database('opuzen_dev_showroom');
	}

	function get_restocks(array $params)
	{
		$this->filters = array_merge($this->filters, $params);

		if ($this->filters['only_completed']) {
			//echo "MODEL-BEFOR select_restock_completed() <br />";
			$this->select_restock_completed();
			$date_attribute = 'date_completed';  // Fixed: use date_completed instead of date_modif for production compatibility
		} else {
			//echo "MODEL-BEFOR select_restock_pendings()) <br />";
			$this->select_restock_pendings();
			$date_attribute = 'date_add';
		}

		if ( !is_null($this->filters['date_from']) ) $this->db->where("DATE(RO.$date_attribute) >=", $this->filters['date_from']);
		if ( !is_null($this->filters['date_to']) ) $this->db->where("DATE(RO.$date_attribute) <=", $this->filters['date_to']);

		if ($this->filters['include_items_description']) $this->select_item_description();

		if (count($this->filters['ids']) > 0) {
			$this->db->where_in("RO.id", $this->filters['ids']);
		}

		if (count($this->filters['destination_id']) > 0) {
			$this->db->where_in("RO.destination_id", $this->filters['destination_id']);
		}

		if (count($this->filters['status_id']) > 0) {
			$this->db->where_in("RO.restock_status_id", $this->filters['status_id']);
		}

		if ($this->filters['include_stock']) {
			$this->db
			    ->select("Stock.id as sales_id, Stock.yardsAvailable")
				->join("$this->db_sales.op_products_stock Stock", "RO.item_id = Stock.master_item_id", 'left outer')
				;
		        }
        
        // Order by most recent first so newest orders appear at top and aren't cut off by limit
        if ($this->filters['only_completed']) {
            // For completed orders, order by completion date
            $this->db->order_by("RO.date_completed", "DESC");
        } else {
            // For pending orders, order by creation date
            $this->db->order_by("RO.date_add", "DESC");
        }
        
        // Memory protection: apply limit if specified to prevent PHP memory exhaustion
        if (isset($this->filters['limit']) && $this->filters['limit'] > 0) {
            $this->db->limit($this->filters['limit']);
        }
        
        //echo "MODEL-BEFOR ->db->get()->result_array() <br />";
		// Echo the mysql query to the screen inside of pre tags
		return $this->db->get()->result_array();
	}

	function get_duplicates($item_ids, $item_sizes, $destination_id)
	{
		$this->db
		    ->select("RO.id, RO.item_id, COALESCE(RO.date_modif, RO.date_add) as date_add, RO.size, RO.quantity_total, RO.quantity_priority, RO.quantity_ringsets")
		    ->select("RS.name as status")
	        ->select("RO.user_id, COALESCE(U.username, RoadkitUser.user) as username")
			->from("$this->t_restock_order RO")
		    ->join("$this->auth_users U", "RO.user_id = U.id", 'left outer')
		    ->join("$this->db_roadkit.T_USER RoadkitUser", "RO.user_id = RoadkitUser.id", 'left outer')
		    ->join("$this->p_restock_status RS", "RO.restock_status_id = RS.id")
			->where("RO.destination_id", $destination_id)
			->where_not_in("RO.restock_status_id", [5, 6])  // Exclude COMPLETED and CANCELLED only
            ->group_by("RO.destination_id, RO.item_id, RO.size")
		;
		$this->db->group_start();
		for($i = 0; $i < count($item_ids); $i++){
			$this->db
			  ->or_group_start()
				  ->where("RO.item_id", $item_ids[$i])
//				  ->where("RO.size", $item_sizes[$i])
			  ->group_end()
			;
		};
		$this->db->group_end();
		$this->select_item_description();
		return $this->db->get()->result_array();
	}

	function add_batch_on_order($data)
	{
		$this->db->insert_batch($this->t_restock_order, $data);
	}

	function update_batch_on_order($data){
		$this->db->update_batch($this->t_restock_order, $data, 'id');
	}

	function add_restock_shipments($data)
	{
		// DEBUG: Log what we're trying to insert
		error_log("ADD_RESTOCK_SHIPMENTS DEBUG: Attempting to insert " . count($data) . " shipment records:");
		error_log("Shipment data: " . json_encode($data));
		
		if (empty($data)) {
			error_log("ADD_RESTOCK_SHIPMENTS ERROR: Empty data array passed");
			$this->debug_info['shipment_insert_error'] = 'Empty data array';
			return;
		}
		
		$this->db->insert_batch($this->t_restock_ship, $data);
		
		$affected = $this->db->affected_rows();
		$last_query = $this->db->last_query();
		$db_error = $this->db->error();
		
		// Store debug info for JSON response
		$this->debug_info['shipment_insert'] = [
			'data_sent' => $data,
			'affected_rows' => $affected,
			'last_query' => $last_query,
			'db_error' => $db_error
		];
		
		error_log("ADD_RESTOCK_SHIPMENTS DEBUG: Inserted $affected rows");
		
		if ($affected == 0) {
			error_log("ADD_RESTOCK_SHIPMENTS ERROR: No rows inserted! Query: $last_query");
			error_log("Database error: " . $db_error['message']);
		}
	}

	function update_orders($data)
	{
		error_log("UPDATE_ORDERS DEBUG: About to update " . count($data) . " orders:");
		foreach ($data as $update) {
			error_log("- Order {$update['id']}: Setting status to {$update['restock_status_id']}");
		}
		
		$this->db->update_batch($this->t_restock_order, $data, 'id');
		$affected = $this->db->affected_rows();
		error_log("UPDATE_ORDERS DEBUG: Actually updated $affected rows");
	}

	public $debug_info = []; // Store debug info for output

	function move_completed_orders($order_ids, $user_id_completed, $quantity_completed_ids = [])
	{
		$order_ids_comma_sep = implode(',', $order_ids);
		$quantity_completed_comma_sep = empty($quantity_completed_ids) ? '' : implode(',', $quantity_completed_ids);

		// DEBUG: Check what statuses these orders actually have RIGHT NOW
		$current_statuses = $this->db->query("
			SELECT id, restock_status_id 
			FROM $this->t_restock_order 
			WHERE id IN ($order_ids_comma_sep)
		")->result_array();
		
		$this->debug_info['current_statuses_before_move'] = $current_statuses;

		// Simplified approach - use basic status preservation, then fix quantity-completed orders separately
		$status_case = "CASE 
		           WHEN RO.restock_status_id = 6 THEN 6 
		           ELSE RO.restock_status_id 
		       END";

		// Use INSERT IGNORE to skip duplicates that may already exist from previous fixes
		$this->db->query("
			INSERT IGNORE INTO $this->t_restock_order_completed
			(order_id, item_id, destination_id, size, quantity_total, quantity_priority, quantity_ringsets, restock_status_id, date_requested, user_id_requested, date_modif, user_id_modif, user_id_completed)
			SELECT RO.id, RO.item_id, RO.destination_id, RO.size, RO.quantity_total, RO.quantity_priority, RO.quantity_ringsets, 
			       $status_case, 
			       RO.date_add, RO.user_id, RO.date_modif, RO.user_id_modif, ?
			FROM $this->t_restock_order RO
			WHERE RO.id IN ($order_ids_comma_sep)
		", [$user_id_completed]);

		// FORCE quantity-completed orders to COMPLETED status with a separate update
		if (!empty($quantity_completed_ids)) {
			$quantity_completed_comma_sep = implode(',', $quantity_completed_ids);
			$this->debug_info['quantity_completed_update'] = [
				'quantity_completed_ids' => $quantity_completed_ids,
				'attempting_to_force_to_completed' => true
			];
			
			$this->db->query("
				UPDATE $this->t_restock_order_completed 
				SET restock_status_id = 5 
				WHERE order_id IN ($quantity_completed_comma_sep)
			");
			
			$affected = $this->db->affected_rows();
			$this->debug_info['quantity_completed_update']['rows_affected'] = $affected;
			
			// Check final status after update
			$final_statuses = $this->db->query("
				SELECT order_id, restock_status_id 
				FROM $this->t_restock_order_completed 
				WHERE order_id IN ($quantity_completed_comma_sep)
			")->result_array();
			$this->debug_info['final_statuses_after_update'] = $final_statuses;
		}
//		var_dump($this->db->last_query());

		// DEBUG: Check what's in RESTOCK_SHIP before copy
		$pre_copy_check = $this->db->query("SELECT * FROM $this->t_restock_ship WHERE order_id IN ($order_ids_comma_sep)")->result_array();
		$this->debug_info['pre_copy_shipments'] = [
			'order_ids_to_copy' => $order_ids_comma_sep,
			'shipments_found' => $pre_copy_check,
			'count' => count($pre_copy_check)
		];

		// Copy shipments from RESTOCK_SHIP to RESTOCK_SHIP_COMPLETED
		// Use ON DUPLICATE KEY UPDATE to handle ID conflicts instead of INSERT IGNORE
		$copy_query = "
			INSERT INTO $this->t_restock_ship_completed
			(id, order_id, quantity, quantity_ringsets, date_add, user_id)
			SELECT RS.id, RS.order_id, RS.quantity, RS.quantity_ringsets, RS.date_add, RS.user_id
			FROM $this->t_restock_ship RS
			WHERE RS.order_id IN ($order_ids_comma_sep)
			ON DUPLICATE KEY UPDATE
			order_id = VALUES(order_id),
			quantity = VALUES(quantity),
			quantity_ringsets = VALUES(quantity_ringsets),
			date_add = VALUES(date_add),
			user_id = VALUES(user_id)
		";
		
		$this->db->query($copy_query);
		$copy_affected = $this->db->affected_rows();
		$copy_error = $this->db->error();
		
		// Debug shipment copying
		$this->debug_info['shipment_copy'] = [
			'query' => $copy_query,
			'affected_rows' => $copy_affected,
			'error' => $copy_error
		];

		// Update completion date to current timestamp AFTER successful insert
		$this->db->query("
			UPDATE $this->t_restock_order_completed 
			SET date_modif = NOW() 
			WHERE order_id IN ($order_ids_comma_sep)
		");

		// Delete from pending tables
		$this->db->query("DELETE FROM $this->t_restock_order WHERE id IN ($order_ids_comma_sep)");
		$order_delete_affected = $this->db->affected_rows();
		
		$this->db->query("DELETE FROM $this->t_restock_ship WHERE order_id IN ($order_ids_comma_sep)");
		$shipment_delete_affected = $this->db->affected_rows();
		
		// Debug deletions
		$this->debug_info['deletions'] = [
			'orders_deleted' => $order_delete_affected,
			'shipments_deleted' => $shipment_delete_affected
		];
	}

	public function select_restock_pendings()  // TEMPORARY: Made public for debugging
	{
		$this->db
		  ->select("RO.id, RO.item_id, RO.size, RO.quantity_total, RO.quantity_priority, RO.quantity_ringsets")
		  ->select("RO.date_add, COALESCE(RO.date_modif, '') as date_modif")
		  ->select("COALESCE(SUM(RS.quantity), 0) as quantity_shipped, COALESCE(SUM(RS.quantity_ringsets), 0) as quantity_ringsets_shipped")
		  ->select("RO.user_id, COALESCE(U.username, RoadkitUser.user) as username")
		  ->select("RO.restock_status_id, RO.destination_id, COALESCE(RD.abrev, RD.name) as destination")
		  ->from("$this->t_restock_order RO")
		  ->join("$this->auth_users U", "RO.user_id = U.id", 'left outer')
		  ->join("$this->db_roadkit.T_USER RoadkitUser", "RO.user_id = RoadkitUser.id", 'left outer')
		  ->join("$this->t_restock_ship RS", "RO.id = RS.order_id", 'left outer')
		  ->join("$this->p_showroom RD", "RO.destination_id = RD.id", 'left outer')
		  ->where_not_in("RO.restock_status_id", [5, 6])  // Exclude COMPLETED (5) and CANCEL (6) from pendings
		  ->group_by("RO.id")
		;
	}

	public function select_restock_completed()  // TEMPORARY: Made public for debugging
	{
		$this->db
		  ->select("RO.order_id as id, RO.item_id, RO.size, RO.quantity_total, RO.quantity_priority, RO.quantity_ringsets")
		  ->select("RO.date_requested as date_add, RO.date_modif, RO.date_completed")  // Added date_completed for production compatibility
		  ->select("COALESCE(SUM(RS.quantity), 0) as quantity_shipped, COALESCE(SUM(RS.quantity_ringsets), 0) as quantity_ringsets_shipped")
		  ->select("RO.user_id_requested as user_id, U.username")
//		  ->select("IF(RO.restock_status_id=6,6,5) as restock_status_id, RO.destination_id, COALESCE(RD.abrev, RD.name) as destination")
          ->select("RO.restock_status_id, RO.destination_id, COALESCE(RD.abrev, RD.name) as destination")
		  ->from("$this->t_restock_order_completed RO")
		  ->join("$this->auth_users U", "RO.user_id_requested = U.id", 'left outer')
		  ->join("$this->t_restock_ship_completed RS", "RO.order_id = RS.order_id", 'left outer')
		  ->join("$this->p_showroom RD", "RO.destination_id = RD.id", 'left outer')
		  ->where("RO.restock_status_id !=", 7)  // Exclude BACKORDER from completed tab
		  ->group_by("RO.order_id")
		;
	}

	// TEMPORARY: One-time function to fix historical completed order statuses
	public function fix_historical_completed_statuses() 
	{
		// Update all completed orders to COMPLETED (5), but preserve CANCEL (6) and exclude BACKORDER (7)
		$this->db->query("
			UPDATE $this->t_restock_order_completed 
			SET restock_status_id = 5 
			WHERE restock_status_id NOT IN (5, 6, 7)
		");
		return $this->db->affected_rows();
	}

	// TEMPORARY: One-time function to move backorder orders back to pending table
	public function move_backorders_to_pending() 
	{
		// First, insert backorder orders back into pending table
		$this->db->query("
			INSERT INTO $this->t_restock_order
			(item_id, destination_id, size, quantity_total, quantity_priority, quantity_ringsets, restock_status_id, date_add, user_id, date_modif, user_id_modif)
			SELECT RO.item_id, RO.destination_id, RO.size, RO.quantity_total, RO.quantity_priority, RO.quantity_ringsets, RO.restock_status_id, RO.date_requested, RO.user_id_requested, RO.date_modif, RO.user_id_modif
			FROM $this->t_restock_order_completed RO
			WHERE RO.restock_status_id = 7
		");
		
		$moved_count = $this->db->affected_rows();
		
		// Then, delete them from completed table
		if ($moved_count > 0) {
			$this->db->query("
				DELETE FROM $this->t_restock_order_completed 
				WHERE restock_status_id = 7
			");
		}
		
		return $moved_count;
	}

	// TEMPORARY: One-time function to move completed orders from pending to completed table
	public function move_misplaced_completed_orders() 
	{
		// Find all orders in pending table that have COMPLETED status (5)
		$completed_orders = $this->db
			->select('id')
			->from($this->t_restock_order)
			->where('restock_status_id', 5)
			->get()
			->result_array();
			
		if (empty($completed_orders)) {
			return 0; // No misplaced completed orders found
		}
		
		$order_ids = array_column($completed_orders, 'id');
		$order_ids_comma_sep = implode(',', $order_ids);
		
		// Move these orders to completed table using the same logic as move_completed_orders
		// but with a generic user_id since we don't know who completed them historically
		$generic_user_id = 1; // Use admin user ID or system user ID
		
		// Insert into completed table
		$this->db->query("
			INSERT INTO $this->t_restock_order_completed
			(order_id, item_id, destination_id, size, quantity_total, quantity_priority, quantity_ringsets, restock_status_id, date_requested, user_id_requested, date_modif, user_id_modif, user_id_completed)
			SELECT RO.id, RO.item_id, RO.destination_id, RO.size, RO.quantity_total, RO.quantity_priority, RO.quantity_ringsets, 5, RO.date_add, RO.user_id, RO.date_modif, RO.user_id_modif, ?
			FROM $this->t_restock_order RO
			WHERE RO.id IN ($order_ids_comma_sep)
		", [$generic_user_id]);
		
		// Insert shipments to completed table
		$this->db->query("
			INSERT INTO $this->t_restock_ship_completed
			(id, order_id, quantity, quantity_ringsets, date_add, user_id)
			SELECT RS.id, RS.order_id, RS.quantity, RS.quantity_ringsets, RS.date_add, RS.user_id
			FROM $this->t_restock_ship RS
			WHERE RS.order_id IN ($order_ids_comma_sep)
		");
		
		// Delete from pending tables
		$this->db->query("DELETE FROM $this->t_restock_order WHERE id IN ($order_ids_comma_sep)");
		$this->db->query("DELETE FROM $this->t_restock_ship WHERE order_id IN ($order_ids_comma_sep)");
		
		return count($order_ids);
	}

	private function select_item_description()
	{
		$this->db
		    // Direct join to T_ITEM and T_PRODUCT tables (more reliable than V_ITEM view)
		    ->select("I.product_id, I.product_type, P.name as product_name, I.code")
            ->join("$this->t_item I", "RO.item_id = I.id", "left outer")
            ->join("$this->t_product P", "I.product_id = P.id", "left outer")

            // Get color from T_ITEM_COLOR table
            ->select("GROUP_CONCAT(DISTINCT PC.name ORDER BY PC.name SEPARATOR '/') as color")
            ->join("$this->t_item_color IC", "I.id = IC.item_id", "left outer")
            ->join("$this->p_color PC", "IC.color_id = PC.id", "left outer")

            // Keep shelf and vendor info if needed (fallback to empty if tables don't exist)
            ->select("GROUP_CONCAT(DISTINCT VIS.name ORDER BY VIS.name SEPARATOR ', ') as shelfs")
            ->join("$this->v_item_shelf VIS", "RO.item_id = VIS.id", "left outer")

            ->select("VIV.name as vendor_name")
            ->join("$this->v_product_vendor VIV", "I.product_id = VIV.product_id AND I.product_type = 'R'", 'left outer')
            
            // Group by restock order ID to ensure one row per order with concatenated colors
            ->group_by("RO.id")
        ;
	}

}

?>