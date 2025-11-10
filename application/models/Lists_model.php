<?php

class Lists_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->model_table = $this->p_list;
	}

	function get_available_lists($filters = array(), $search = "")
	{

		$defaults = array(
			'datatable' => false,
			'category_id' => array(),
			'exclude_id' => null // To don't select the ones numbered here
		);
		$this->filters = array_merge($defaults, $filters);

		$cat = $this->p_category_lists;

		$this->db->select("$this->p_list.id, $this->p_list.name as list_name, $this->p_list.date_add, $this->p_list.date_modif")
			->select("GROUP_CONCAT(DISTINCT $this->p_category_lists.name SEPARATOR ' / ') as category")
			->select("GROUP_CONCAT(DISTINCT $this->p_showroom.abrev SEPARATOR ' / ') as showrooms")
			->select("COUNT(DISTINCT $this->t_item.product_id) as total_products")
			->select("COUNT(DISTINCT $this->t_item.id) as total_items")
			->from($this->p_list)
			->join($this->p_list_category, "$this->p_list.id = $this->p_list_category.list_id", 'left outer')
			->join($this->p_category_lists, "$this->p_list_category.category_id = $this->p_category_lists.id", 'left outer')
			->join($this->p_list_showrooms, "$this->p_list.id = $this->p_list_showrooms.list_id", 'left outer')
			->join($this->p_showroom, "$this->p_list_showrooms.showroom_id = $this->p_showroom.id", 'left outer')
			->join($this->p_list_items, "$this->p_list.id = $this->p_list_items.list_id", 'left outer')
			->join($this->t_item, " $this->p_list_items.item_id = $this->t_item.id ", 'left outer')
			//->join($this->t_item, " $this->p_list_items.item_id = $this->t_item.id AND $this->t_item.archived = 'N' ", 'left outer')
			//       ->where("$this->p_list.archived", 'N')
			->where("$this->p_list.user_id != 0") // This will bring up draft list_ids that were not finally saved
			->group_by("$this->p_list.id")
		;

		if (!is_null($this->filters['exclude_id'])) $this->db->where_not_in("$this->p_list.id", $this->filters['exclude_id']);

		if (!empty($this->filters['category_id'])) $this->db->where_in("$this->p_list_category.category_id", $this->filters['category_id']);
		if (!empty($search)) {

			$this->db->group_start();
			$this->db->like($this->p_list . '.name',  $search);
			$this->db->or_like($this->p_category_lists . '.name',  $search);
			$this->db->or_like($this->p_showroom . '.abrev',  $search);
			$this->db->group_end();
		}

		if ($this->filters['datatable']) {
			$this->set_datatables_variables();
			return $this->apply_datatables_processing($this->db->get_compiled_select());
		} else {
			return $this->db->get()->result_array();
		}
	}

	function get_list($id = [])
	{
		$this->db
			->select("*")
			->from("V_LIST")
			->where_in("V_LIST.id", $id);
		return $this->db->get()->result_array();
	}

	function get_next_list_id()
	{
		$t = $this->p_list;
		$data = array(
			'user_id' => 0
		);
		$this->db
			->set('date_add', 'NOW()', false)
			->set($data)
			->insert($t);
		return $this->db->insert_id();
	}

	function get_list_edit($id)
	{
		$isArchived = $this->db->where_in('id', $id)->count_all_results($this->p_list) === 0;

		if (!$isArchived) {
			$t = $this->p_list;
			$ti = $this->t_item;
			$pp = $this->t_product_price;
			$ps = $this->p_product_status;
			$ss = $this->p_stock_status;
			$c = $this->p_list_category;
			$i = $this->p_list_items;
			$s = $this->p_list_showrooms;
			$this->db->select(" 'N' as archived ");
		} else {
			$t = $this->th_list;
			$c = $this->th_list_category;
			$i = $this->th_list_items;
			$s = $this->th_list_showrooms;
			$this->db->select(" 'Y' as archived ");
		}
		$cat = $this->p_category_lists;
		$srs = $this->p_showroom;
		$u = $this->auth_users;


		$this->db->select("$t.id, $t.name, $t.date_add, $t.date_modif, $t.active")
			//       ->select("$t.archived")
			->select($pp . ".p_res_cut as price, " . $pp . ".p_hosp_roll as volume_price, " . $ps . ".name as status, " . $ss . ".name as stock_status")
			->select("GROUP_CONCAT(DISTINCT $cat.id SEPARATOR ' / ') as category_id")
			->select("GROUP_CONCAT(DISTINCT $s.showroom_id SEPARATOR ' / ') as showroom_id")
			->select('initial_discount')
			->select("$u.username")
			->from($t)
			->join($c, "$t.id = $c.list_id", 'left outer')
			->join($cat, "$c.category_id = $cat.id", 'left outer')
			->join($i, "$t.id = $i.list_id", 'left outer')
			->join($ti, "$i.item_id = $ti.id", 'left outer')
			->join($pp, "$ti.product_id = $pp.product_id", 'left outer')
			->join($ps, "$ti.status_id = $ps.id", 'left outer')
			->join($ss, "$ti.stock_status_id = $ss.id", 'left outer')
			->join($u, "$t.user_id = $u.id", 'left outer')
			->join($s, "$t.id = $s.list_id", 'left outer')
			->where_in("$t.id", $id)
			->group_by("$t.id");
		return $this->db->get()->row_array();
		//$sql = $this->db->get_compiled_select();
		// echo '<pre> ('. __LINE__ .') at: ' . __FILE__ . "<br /."; 
		// print_r($sql); 
		// echo '</pre>';
		//die();
		//return $this->db->get()->row_array();
	}

	/*

	  Saves

	*/

	function save_list($data, $id = null)
	{
		$t = $this->p_list;
		if ($id == null) {
			// New
			$this->db->insert($t, $data);
			return $this->db->insert_id();
		} else {
			// Edit
			// log, then save
			$this->db
				->set($data)
				->where('id', $id)
				->update($t);
		}
	}

	function save_list_category($data, $id)
	{
		$t = $this->p_list_category;
		$this->db->insert_batch($t, $data);
	}

	function clean_list_categories($id)
	{
		$t = $this->p_list_category;
		$this->clean_logics($id, $t, 'list_id');
	}

	function save_list_showrooms($data, $id)
	{
		$t = $this->p_list_showrooms;
		$this->db->insert_batch($t, $data);
	}

	function clean_list_showrooms($id)
	{
		$t = $this->p_list_showrooms;
		$this->clean_logics($id, $t, 'list_id');
	}

	function archive_list($list_id)
	{
		//     $this->db
		//       ->set('archived', 'Y')
		//       ->where('id', $list_id)
		//       ->update($this->p_list);
		$this->db->trans_begin();

		$this->db->query("
	      INSERT INTO $this->th_list
	      SELECT *
	      FROM $this->p_list
	      WHERE id = ? ;
	    ", array($list_id));
		$this->db
			->where('id', $list_id)
			->delete($this->p_list);

		$this->db->query("
	      INSERT INTO $this->th_list_items
	      SELECT *
	      FROM $this->p_list_items
	      WHERE list_id = ? ;
	    ", array($list_id));
		$this->db
			->where('list_id', $list_id)
			->delete($this->p_list_items);

		$this->db->query("
	      INSERT INTO $this->th_list_showrooms
	      SELECT *
	      FROM $this->p_list_showrooms
	      WHERE list_id = ? ;
	    ", array($list_id));
		$this->db
			->where('list_id', $list_id)
			->delete($this->p_list_showrooms);

		$this->db->query("
	      INSERT INTO $this->th_list_category
	      SELECT *
	      FROM $this->p_list_category
	      WHERE list_id = ?
	    ", array($list_id));
		$this->db
			->where('list_id', $list_id)
			->delete($this->p_list_category);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return $this->db->error();
		} else {
			$this->db->trans_commit();
			return true;
		}
	}

	function retrieve_list($list_id)
	{
		//     $this->db
		//       ->set('archived', 'N')
		//       ->where('id', $list_id)
		//       ->update($this->p_list);
		$this->db->trans_begin();

		$this->db->query("
      INSERT INTO $this->p_list
      SELECT *
      FROM $this->th_list
      WHERE id = ? ;
    ", array($list_id));
		$this->db
			->where('id', $list_id)
			->delete($this->th_list);

		$this->db->query("
      INSERT INTO $this->p_list_items
      SELECT *
      FROM $this->th_list_items
      WHERE list_id = ? ;
    ", array($list_id));
		$this->db
			->where('list_id', $list_id)
			->delete($this->th_list_items);

		$this->db->query("
      INSERT INTO $this->p_list_showrooms
      SELECT *
      FROM $this->th_list_showrooms
      WHERE list_id = ? ;
    ", array($list_id));
		$this->db
			->where('list_id', $list_id)
			->delete($this->th_list_showrooms);

		$this->db->query("
      INSERT INTO $this->p_list_category
      SELECT *
      FROM $this->th_list_category
      WHERE list_id = ?
    ", array($list_id));
		$this->db
			->where('list_id', $list_id)
			->delete($this->th_list_category);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return false;
		} else {
			$this->db->trans_commit();
			return true;
		}
	}

	//

	function add_one_item_to_list($data)
	{
		$t = $this->p_list_items;
		$this->db->replace($t, $data);
	}

	function update_list_item($data)
	{
		if (count($data) == 0)
			return;
		//		var_dump($data);
		$t = $this->p_list_items;
		$cols = ["list_id", "item_id"];
		$update_cols = [
			"n_order",
			//            "p_hosp_cut",
			"p_hosp_roll",
			"p_res_cut",
			"active",
			"big_piece",
			"user_id",
			"date_modif"
		];
		$values = [];
		foreach ($data as $row) {
			$row_values = [];
			foreach ($cols as $c) {
				$row_values[] = $row[$c];
			}
			foreach ($update_cols as $c) {
				$row_values[] = (is_null($row[$c]) ? "NULL" : $row[$c]);
			}
			$values[] = "(" . implode(",", $row_values) . ")";
		}
		$values = implode(",", $values);
		$sql_str = "INSERT INTO $t (" . implode(",", array_merge($cols, $update_cols)) . ") VALUES " . $values;

		$on_duplicate_key_update = [];
		foreach ($update_cols as $c) {
			$on_duplicate_key_update[] = $c . "=VALUES(" . $c . ")";
		}
		$sql_str = $sql_str . " ON DUPLICATE KEY UPDATE " . implode(",", $on_duplicate_key_update) . ";";
		//		var_dump($sql_str); exit;
		$this->db->query($sql_str);
	}

	//   function add_one_product_to_list($data){
	//     $t = $this->p_list_products;
	//     $this->db->replace($t, $data);
	//   }

	function add_batch_item_to_list($data)
	{
		$this->db->insert_batch($this->p_list_items, $data);
	}

	function toggle_item_status($list_id, $item_id, $status)
	{
		$t = $this->p_list_items;
		$this->db
			->set('date_modif', 'NOW()', false)
			->set("$t.active", $status)
			->where("$t.list_id", $list_id)
			->where("$t.item_id", $item_id)
			->update($t);
		return ($this->db->affected_rows() === 1);
	}

	function toggle_big_piece_status($list_id, $item_id, $status)
	{
		$t = $this->p_list_items;
		$this->db
			->set('date_modif', 'NOW()', false)
			->set("$t.big_piece", $status)
			->where("$t.list_id", $list_id)
			->where("$t.item_id", $item_id)
			->update($t);
		return ($this->db->affected_rows() === 1);
	}

	function delete_item($list_id, $item_id)
	{
		$t = $this->p_list_items;

		/*
		$this->db
		  ->select("n_order")
		  ->from($t)
		  ->where('list_id', $list_id)
		  ->where('item_id', $item_id)
		  ;
		$row = $this->db->get()->row();
		$this_item_n_order = intval( $row->n_order );
		*/

		$this->db
			->where("$t.list_id", $list_id)
			->where("$t.item_id", $item_id)
			->delete($t);
		$valid = ($this->db->affected_rows() === 1);
		//return ( $this->db->affected_rows() === 1 );

		/*
		// Change order of all other items
		$this->db
		  ->set('n_order', 'n_order-1', false)
		  ->where('list_id', $list_id)
		  ->where('n_order >', $this_item_n_order)
		  ->update( $this->p_list_items );
		$valid = $valid && ( $this->db->affected_rows() === 1 );
		*/
		return $valid;
	}

	//   function delete_product($list_id, $product_id, $product_type){
	//     $t = $this->p_list_products;
	//     $this->db
	//       ->where("$t.list_id", $list_id)
	//       ->where("$t.product_id", $product_id)
	//       ->where("$t.product_type", $product_type)
	//       ->delete($t);
	//     return $this->db->affected_rows() === 1;
	//   }

	function get_next_order($list_id)
	{
		$this->db
			->select("MAX(n_order)+1 as num")
			->from($this->p_list_items)
			->where('list_id', $list_id);
		$row = $this->db->get()->row();
		return is_null($row->num) ? 1 : $row->num;
	}

	function get_30_under_product_ids()
	{
		$q = $this->db
			->distinct()
			->select("I.product_id as product_id")
			->from("Q_LIST_ITEMS LI")
			->join("T_ITEM I", "LI.item_id = I.id")
			->where("LI.list_id", $this->under30_list_id)
			->get()->result_array();
		return array_column($q, 'product_id');
	}

	function get_showroom_address($showroom_id)
	{
		$this->db
			->select("Contact.address_1, Contact.address_2, Contact.city, Contact.state, Contact.zipcode")
			->select("COALESCE(Contact.tel_1, Contact.tel_2) as tel, COALESCE(Contact.email_1, Contact.email_2) as email")
			->from("$this->p_contact Contact")
			->join("$this->p_showroom_contact SC", "Contact.id = SC.contact_id")
			->where("SC.showroom_id", $showroom_id);
		return $this->db->get()->result_array();
	}
}
