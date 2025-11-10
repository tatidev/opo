<?php

class Specs_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->set_rules(); // Init in case is not called
		$this->load->model('Search_model', 'search');
	}

	// Has to be a function because $table changes when editing specs
	function set_rules($table = '', $id = '')
	{
		$this->rules = array(

		  'styles' => array(
			Digital => array(
			  array(
				'field' => 'info_name',
				'label' => 'Name',
				'rules' => "required" . (empty($id) ? "|is_unique[$this->t_digital_style.name]" : ''),
			  )
			),
			ScreenPrint => array(
			  array(
				'field' => 'info_name',
				'label' => 'Name',
				'rules' => "required" . (empty($id) ? "|is_unique[$this->t_screenprint_style.name]" : ''),
			  )
			)
		  ),

		  'specs' => array(
//			array(
//			  'field' => 'info_name',
//			  'label' => 'Name',
////			  'rules' => "required" . (empty($id) ? "|is_unique[$table.name]" : ''),
//			  'rules' => (empty($id) ? "is_unique[$table.name]" : ''),
//			)
		  )

		);
	}

	public function get_abrasion_limits()
	{
		$this->select_name_id_for_table($this->p_abrasion_limit);
		$this->db->select('descr');
		return $this->db->get()->result_array();
	}

	public function get_abrasion_tests()
	{
		$this->select_name_id_for_table($this->p_abrasion_test);
		$this->db->select('descr');
		return $this->db->get()->result_array();
	}

	public function get_cleanings()
	{
		$this->select_name_id_for_table($this->p_cleaning);
		$this->db->select('descr');
		return $this->db->get()->result_array();
	}

	public function get_cleanings_instructions()
	{
		$this->select_name_id_for_table($this->p_cleaning_instructions);
		$this->db->select('descr');
		return $this->db->get()->result_array();
	}
	
	public function get_warranties()
	{
		$this->select_name_id_for_table($this->p_warranty);
		$this->db->select('descr');
		return $this->db->get()->result_array();
	}
	
	public function get_product_tasks(){
		$order_by = 'n_order';
		$this->select_name_id_for_table($this->p_product_task, $order_by);
		$this->db
			->select("n_order, descr, active")
			;
		return $this->db->get()->result_array();
	}

	public function get_contents()
	{
		$this->select_name_id_for_table($this->p_content);
		return $this->db->get()->result_array();
	}

	public function get_finishs()
	{
		$this->select_name_id_for_table($this->p_finish);
		$this->db->select('descr');
		return $this->db->get()->result_array();
	}

	public function get_firecodes()
	{
		$this->select_name_id_for_table($this->p_firecode);
		$this->db->select('descr');
		return $this->db->get()->result_array();
	}

	public function get_origins()
	{
		$this->select_name_id_for_table($this->p_origin);
		return $this->db->get()->result_array();
	}

	public function get_product_status($id = null)
	{
		$this->select_name_id_for_table($this->p_product_status, 'descr');
		$this->db->select('descr');
		if (!is_null($id)) {
			$this->db->where('id', $id);
			return $this->db->get()->row_array();
		} else {
			return $this->db->get()->result_array();
		}
	}

	public function get_stock_status($id = null)
	{
		$this->select_name_id_for_table($this->p_stock_status, 'descr');
		$this->db->select('descr');
		if (!is_null($id)) {
			$this->db->where('id', $id);
			return $this->db->get()->row_array();
		} else {
			return $this->db->get()->result_array();
		}
	}

	public function get_uses()
	{
		$this->select_name_id_for_table($this->p_use);
		return $this->db->get()->result_array();
	}

	public function get_shelfs($id = null)
	{
		$this->select_name_id_for_table($this->p_shelf);
		$this->db->select('descr');
		if (!is_null($id)) {
			if(is_array($id)){
				$this->db->where_in('id', $id);
				return $this->db->get()->result_array();
			}
			else{
				$this->db->where('id', $id);
				return $this->db->get()->row_array();
			}
		} else {
			return $this->db->get()->result_array();
		}
	}

	public function get_sampling_locations($id = null)
	{
		$this->select_name_id_for_table($this->p_sampling_locations);
		$this->db->select('descr');
		if (!is_null($id)) {
			$this->db->where('id', $id);
			return $this->db->get()->row_array();
		} else {
			return $this->db->get()->result_array();
		}
	}

	public function get_restock_status($id = null)
	{
		$this->select_name_id_for_table($this->p_restock_status);
		if (!is_null($id)) {
			$this->db->where('id', $id);
			return $this->db->get()->row_array();
		} else {
			return $this->db->get()->result_array();
		}
	}

	public function get_restock_destinations($id = null)
	{
		$this->select_name_id_for_table($this->p_showroom);
		if (!is_null($id)) {
			$this->db->where('id', $id);
			return $this->db->get()->row_array();
		} else {
			return $this->db->get()->result_array();
		}
	}

	public function get_user_restock_destination_id($user_id){
		$r = $this->db
		  ->select("restock_destination_id")
		  ->from("$this->auth_users_destinations")
		  ->where("user_id", $user_id)
		  ->get()->row_array();
		if(count($r) > 0){
			return $r['restock_destination_id'];
		}
		else {
			return 0;
		}
	}

	public function get_showrooms()
	{
		$this->select_name_id_for_table($this->p_showroom, 'name');
		$this->db->select('abrev');
		return $this->db->get()->result_array();
	}

	public function get_vendors()
	{
		$this->select_name_id_for_table($this->p_vendor, 'name');
		$this->db->select('abrev');
		return $this->db->get()->result_array();
	}

	public function get_weaves()
	{
		$this->select_name_id_for_table($this->p_weave);
		return $this->db->get()->result_array();
	}

	public function get_categories_files()
	{
		$this->select_name_id_for_table($this->p_category_files, 'id');
		return $this->db->get()->result_array();
	}

	public function get_prices_types()
	{
		$this->select_name_id_for_table($this->p_price_type);
		return $this->db->get()->result_array();
	}

	public function get_weight_units($id = null)
	{
		$this->select_name_id_for_table($this->p_weight_unit);
		if (!is_null($id)) {
			$this->db->where('id', $id);
			return $this->db->get()->row_array();
		} else {
			return $this->db->get()->result_array();
		}
	}


	public function get_showcase_collections()
	{
		$this->select_name_id_for_table($this->t_showcase_collection);
		return $this->db->get()->result_array();
	}

	public function get_showcase_contents_web()
	{
		$this->select_name_id_for_table($this->t_showcase_contents_web);
		return $this->db->get()->result_array();
	}

	public function get_showcase_patterns()
	{
		$this->select_name_id_for_table($this->t_showcase_patterns);
		return $this->db->get()->result_array();
	}

	public function get_showcase_coords_colors()
	{
		$this->select_name_id_for_table($this->t_showcase_coord_colors);
		return $this->db->get()->result_array();
	}


	private function select_name_id_for_table($table, $orderby = 'name', $active = 'Y')
	{
		$this->db->select("id, name")
		  ->from($table)
		  ->order_by($orderby);

		if (!is_null($active)) {
			$this->db->where("active", $active);
		}
	}

	/*
	  Digital Data
	*/

	public function get_digital_styles($filters = array())
	{

		$filters = array_merge(
		  array(
			'onlyActive' => false,
			'includeArchived' => false,
			'count' => false
		  ),
		  $filters
		);

		$this->db
		  ->select("id, name, vrepeat, hrepeat, active")
		  ->from($this->t_digital_style)
		  ->order_by('name');

		if (!$filters['includeArchived']) $this->db->where('archived', 'N');

		if ($filters['onlyActive']) $this->db->where('active', 'Y');

		if ($filters['count']) {
			$this->db->select(" ( SELECT COUNT(*) FROM $this->product_digital WHERE $this->product_digital.style_id = $this->t_digital_style.id ) as count_relations ");
			$this->db->select(" ( SELECT COUNT(*) FROM $this->t_showcase_style_items SSI WHERE SSI.style_id = $this->t_digital_style.id AND SSI.visible = 'Y' AND SSI.archived = 'N' ) as count_active_items");
			$this->db->select(" ( SELECT COUNT(*) FROM $this->t_showcase_style_items SSI WHERE SSI.style_id = $this->t_digital_style.id AND SSI.archived = 'N' ) as count_items");
		}

		return $this->db->get()->result_array();
	}

	public function get_digital_grounds()
	{
//     $this->load->model('lists_model', 'lists_model');
		return $this->search->do_search(
		  array(
			'select' => ['price'],
			'list' => array('id' => $this->digital_grounds_list_id),
			'includeCombinedProducts' => false,
			'includeDiscontinued' => true,
			'order_by' => 'product_name'
		  )
		);
	}

	/*
	  Screen Print Data
	*/

	public function get_screenprint_styles($filters = array())
	{

		$filters = array_merge(
		  array(
			'onlyActive' => false,
			'includeArchived' => false,
			'count' => false
		  ),
		  $filters
		);

		$this->db
		  ->select("id, name, vrepeat, hrepeat, active")
		  ->from($this->t_screenprint_style)
		  ->order_by('name');

		if (!$filters['includeArchived']) $this->db->where('archived', 'N');

		if ($filters['onlyActive']) $this->db->where('active', 'Y');

		if ($filters['count']) {
			$this->db->select(" ( SELECT COUNT(*) FROM $this->product_screenprint WHERE $this->product_screenprint.style_id = $this->t_screenprint_style.id ) as count_relations ");
		}

		return $this->db->get()->result_array();
	}

	public function get_screenprint_grounds()
	{
		$this->load->model('lists_model', 'lists_model');
		return $this->search->do_search(
		  array(
			'select' => ['price'],
			'list' => array('id' => $this->screenprint_grounds_list_id),
			'includeCombinedProducts' => false,
			'order_by' => 'product_name'
		  )
		);
	}

	/*
	  Lists info
	*/

	public function get_lists($active=null)
	{
		$this->select_name_id_for_table($this->p_list, 'name', $active);
		return $this->db->get()->result_array();
	}

	public function get_p_front_contents($active=null)
	{
		$this->select_name_id_for_table($this->p_content, 'name', $active);
		return $this->db->get()->result_array();
	}

	
	 

	public function get_lists_category()
	{
		$this->select_name_id_for_table($this->p_category_lists);
		$this->db->select("descr");
		return $this->db->get()->result_array();
	}

	public function get_lists_showrooms()
	{
		$this->select_name_id_for_table($this->p_showroom);
		return $this->db->get()->result_array();
	}

	/*


	*/

	function get_rows($selectedrow)
	{

		// Subquery for active relations
		if (isset($selectedrow['active_relations'])) {
			$n = 0;
			foreach ($selectedrow['active_relations'] as $related_table => $attribute_in_table) {

				if (is_array($attribute_in_table)) {
					$relationsSubquery = [];
					foreach($attribute_in_table as $attribute_name){
						$n++;
						$relationsSubquery[] = " ( SELECT COUNT(*) FROM $related_table WHERE $attribute_name = " . $selectedrow['table'] . ".id ) ";
					}
					$relationsSubquery = implode(' + ', $relationsSubquery);

				} else {
					$n++;
					$relationsSubquery = " ( SELECT COUNT(*) FROM $related_table WHERE $attribute_in_table = " . $selectedrow['table'] . ".id ) ";
				}

				if (count($selectedrow['active_relations']) > $n) {
					$relationsSubquery .= " + ";
				}
			}

            $this->db->select("*")
                ->select("( $relationsSubquery ) as relations")
                ->from($selectedrow['table']);
		}
        else {
            $this->db->select("*")
                ->from($selectedrow['table']);
        }


		if(isset($selectedrow['exclude_ids'])){
			$this->db->where_not_in("id", $selectedrow['exclude_ids']);
		}
		return $this->db->get()->result_array();
	}

	function get_spec_data($selectedrow, $spec_id)
	{
		$table_name = $selectedrow['table'];
		if ($selectedrow['n_columns'] === 2) {
			$this->db->select("$table_name.id, $table_name.name, $table_name.active");
		} else if ($selectedrow['n_columns'] === 3) {
			$this->db->select("$table_name.id, $table_name.name, $table_name.descr, $table_name.active");
		}
		if(array_key_exists('shared_files', $selectedrow) and $selectedrow['shared_files']){
			$files_table_name = $table_name . '_FILES';
			$this->db
				->select("GROUP_CONCAT(DISTINCT $files_table_name.url_dir, '#', $files_table_name.date_add ORDER BY $files_table_name.date_add DESC SEPARATOR '**') as files")
				->join($files_table_name, "$table_name.id = $files_table_name.related_id")
				;
		}
		if(array_key_exists('n_order', $selectedrow) and $selectedrow['n_order']){
			$this->db->select("$table_name.n_order");
		}
		$this->db
		  ->from($table_name)
		  ->where("$table_name.id", $spec_id)
		;
		return $this->db->get()->row_array();
	}

	function save_spec($table, $data, $spec_id = null)
	{
		if ($spec_id === null) {
			$this->db
			  ->set('date_add', 'NOW()', false)
			  ->set($data)
			  ->insert($table);
			return $this->db->insert_id();
		} else {
			$this->db
			  ->set($data)
			  ->where('id', $spec_id)
			  ->update($table);
		}
	}

	function clean_spec_files_logic($selectedRow, $spec_id=null){
		if(empty($spec_id)){
			return;
		} else {
			$files_table = $selectedRow['table'] . '_FILES';
			$sql = "DELETE
            FROM $files_table
            WHERE related_id = ?;";
			$bind = array($spec_id);
			$this->db->query($sql, $bind);
		}
	}

	function save_spec_files($selectedRow, $batch){
		$t = $selectedRow['table'] . '_FILES';
		$this->db->insert_batch($t, $batch);
	}

	function get_style_data($type, $id)
	{
		$this->db
		  ->select("S.id, S.name, S.hrepeat, S.vrepeat, S.active, S.archived")
		  ->where("S.id", $id);
		switch ($type) {
			case constant('Digital'):
				$this->db
				  ->select("SS.url_title, SS.visible as showcase_visible, SS.pic_big, SS.pic_big_url")
				  ->select("GROUP_CONCAT(DISTINCT SP.pattern_id SEPARATOR ' / ') as showcase_pattern_id")
				  ->from("$this->t_digital_style S")
				  ->join("$this->t_showcase_style SS", "S.id = SS.style_id", 'left outer')
				  ->join("$this->t_showcase_styles_patterns SP", "S.id = SP.style_id", 'left outer')
				  ->group_by("S.id");
				break;

			case constant('ScreenPrint'):
				$this->db->from("$this->t_screenprint_style S");
				break;
		}
		return $this->db->get()->row_array();
	}

	function select_style_files($id = null)
	{
		$t = $this->t_digital_style_files;
		$p = $this->p_category_files;
		if (is_null($id)) {
			$this->db->select("GROUP_CONCAT(DISTINCT $t.url_dir, '*', $t.date_add, '*', $t.user_id, '*', $p.id, '*', $p.name, '*', IFNULL($t.descr, ' ') ORDER BY $t.date_add DESC SEPARATOR '**' ) as files")
			  ->join($t, "$this->t_digital_style.id = $t.style_id", 'left outer')
			  ->join($p, "$t.category_id = $p.id", 'left outer');
		} else {
			$this->db
			  ->select("$t.url_dir,  $t.date_add, $t.user_id")
			  ->select("$t.category_id, $p.name as category_name, COALESCE($t.descr, '-') as descr")
			  ->from($t)
			  ->join($p, "$t.category_id = $p.id", 'left outer')
			  ->where("$t.style_id", $id)
			  ->order_by("$t.date_add");
			return $this->db->get()->result_array();
		}
	}

	function get_style_items($type, $id)
	{
		switch ($type) {
			case constant('Digital'):
				$this->db
				  ->select("SI.id, SI.style_id, SI.visible as showcase_visible, SI.n_order, SI.pic_big, SI.pic_big_url")
				  ->select("GROUP_CONCAT(DISTINCT C.id SEPARATOR ' / ') as color_ids")
				  ->select("GROUP_CONCAT(DISTINCT C.name ORDER BY SIC.n_order SEPARATOR ' / ') as color_names")
				  ->select("GROUP_CONCAT(DISTINCT SICC.coord_color_id SEPARATOR ' / ') as coord_color_ids")
				  ->from("$this->t_showcase_style_items SI")
				  ->join("$this->t_showcase_style_items_color SIC", "SI.id = SIC.item_id")
				  ->join("$this->p_color C", "SIC.color_id = C.id")
				  ->join("$this->t_showcase_style_items_coord_color SICC", "SI.id = SICC.item_id", 'left outer')
				  ->where("SI.style_id", $id)
				  ->group_by("SI.id");
				$this->where_item_is_not_archived("SI");
				return $this->db->get()->result_array();
				break;

			case constant('ScreenPrint'):
				return array();
				break;
		}
	}

	function save_style($type, $data, $spec_id = null)
	{
		switch ($type) {
			case constant('Digital'):
				$table = $this->t_digital_style;
				break;

			case constant('ScreenPrint'):
				$table = $this->t_screenprint_style;
				break;
		}
		if ($spec_id === null) {
			$this->db
			  ->set('date_add', 'NOW()', false)
			  ->set($data)
			  ->insert($table);
			return $this->db->insert_id();
		} else {
			$this->db
			  ->set($data)
			  ->where('id', $spec_id)
			  ->update($table);
		}
	}

	function clean_style_files_logic($style_id)
	{
		$sql = "DELETE
            FROM $this->t_digital_style_files
            WHERE style_id = ?;";
		$bind = array($style_id);
		$this->db->query($sql, $bind);
	}

	function save_style_files($data, $style_id)
	{
		$t = $this->t_digital_style_files;
		$this->db->insert_batch($t, $data);
	}

	function save_style_item($data, $item_id = null)
	{
		$t = $this->t_showcase_style_items;
		if (is_null($item_id)) {
			// Create new
			$this->db
			  ->set('date_add', 'NOW()', false)
			  ->set($data)
			  ->insert($t);
			return $this->db->insert_id();
		} else {
			// To implement with new database
			//$this->log_item($item_id);
			$this->db->set($data)
			  ->where('id', $item_id)
			  ->update($t);
		}
	}

	function save_style_item_colors($data, $id)
	{
		// To implement with new database
		//$this->log_item_color($id);
		$this->clean_logics($id, $this->t_showcase_style_items_color, 'item_id');
		$this->db->insert_batch($this->t_showcase_style_items_color, $data);
	}

	function save_style_showcase($type, $data, $id)
	{
		switch ($type) {
			case constant('Digital'):
				$table = $this->t_showcase_style;
				break;

			case constant('ScreenPrint'):
				//$table = $this->t_screenprint_style;
				break;
		}

		$q = $this->db
		  ->where('style_id', $id)
		  ->get($table);

		if ($q->num_rows() > 0) {
			$this->db
			  ->set($data)
			  ->where('style_id', $id)
			  ->update($table);
		} else {
			$this->db
			  ->set($data)
			  ->set('style_id', $id)
			  ->insert($table);
		}
	}

	function save_style_showcase_patterns($type, $data, $id)
	{
		switch ($type) {
			case constant('Digital'):
				$table = $this->t_showcase_styles_patterns;
				break;

			case constant('ScreenPrint'):
				//$table = $this->t_screenprint_style;
				break;
		}
		//$this->log_product_weave($product_id);
		$this->clean_logics($id, $table, 'style_id');
		(count($data) > 0 ? $this->db->insert_batch($table, $data) : '');
	}

	function archive_style_item($type, $id)
	{
		switch ($type) {
			case constant('Digital'):
				$table = $this->t_showcase_style_items;
				break;

			case constant('ScreenPrint'):
				//$table = $this->t_screenprint_style;
				break;
		}
		$this->db
		  ->set("archived", "Y");
		if (is_array($id)) {
			$this->db
			  ->where_in("id", $id);
		} else {
			$this->db
			  ->where("id", $id);
		}
		$this->db
		  ->update($table);
	}

	function archive_style($type, $style_id)
	{
		switch ($type) {
			case constant('Digital'):
				$table = $this->t_digital_style;
				break;

			case constant('ScreenPrint'):
				$table = $this->t_screenprint_style;
				break;
		}
		$this->db
		  ->set('archived', 'Y')
		  ->where('id', $style_id)
		  ->update($table);
	}

	function retrieve_style($type, $style_id)
	{
		switch ($type) {
			case constant('Digital'):
				$table = $this->t_digital_style;
				break;

			case constant('ScreenPrint'):
				$table = $this->t_screenprint_style;
				break;
		}
		$this->db
		  ->set('archived', 'N')
		  ->where('id', $style_id)
		  ->update($table);
		return $this->get_style_data($type, $style_id);
	}

	function get_users_name_by_id($ids){
		$this->db
			->select("U.id, CONCAT(U.first_name, ' ', U.last_name) as name, U.username")
			->from("$this->auth_users U")
			->where_in("U.id", $ids)
			;
		return $this->db->get()->result_array();
	}

	function get_users_name_by_group_id($group_ids){
		$this->db
		  ->select("U.id, U.first_name, U.username")
		  ->from("$this->auth_users U")
		  ->join("$this->auth_users_groups UG", "U.id = UG.user_id")
		  ->where_in("UG.group_id", $group_ids)
		;
		return $this->db->get()->result_array();
	}
}

?>