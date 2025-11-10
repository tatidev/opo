<?php

class Product_model extends MY_Model
{

	protected $t;

	function __construct()
	{
		parent::__construct();
		//$this->colSearch = array();
        // $this->model_table = array( $this->t_product, $this->product_digital, $this->product_screenprint );
		$this->model_table = array($this->t_product, $this->product_digital);
	}

	function rules($product_type, $id)
	{
		$arr = array(
		  constant('Regular') => array(
			array(
			  'field' => 'product_name',
			  'label' => 'Name',
			  'rules' => "required"// . ( $id === '0' ? "|is_unique[$this->t_product.name]" : '')
			),
			array(
			  'field' => 'vendor',
			  'label' => 'Vendor',
			  'rules' => 'required|is_natural_no_zero'
			)
		  ),
		  constant('Digital') => array(
			array(
			  'field' => 'style',
			  'label' => 'Style',
			  'rules' => "required|is_natural_no_zero"
			),
			array(
			  'field' => 'ground',
			  'label' => 'Ground',
			  'rules' => 'required|is_natural_no_zero'
			)
		  )
// 			constant('ScreenPrint') => array(
// 								array(
// 											'field' => 'style',
// 											'label' => 'Style',
// 											'rules' => "required|is_natural_no_zero"
// 								),
// 								array(
// 											'field' => 'ground',
// 											'label' => 'Ground',
// 											'rules' => 'required|is_natural_no_zero'
// 								)
// 			)
		);
		return $arr[$product_type];
	}

	function get_product_name_vendor_combination($product_name)
	{
		return $this->db
		  ->select("P.name, PV.vendor_id")
		  ->from("$this->t_product P")
		  ->join("$this->t_product_vendor PV", "P.id = PV.product_id")
		  ->where("P.name", $product_name)
		  ->get()->result_array();
	}

	/*

	Product information selection

	*/
	function get_product_info_for_tag($type, $product_id)
	{
		/*
			Subselect
		*/
		$this->db
		  ->select("COUNT(DISTINCT I.id)")
		  ->from("$this->t_item I")
		  ->where("I.product_type", $type)
		  ->where("I.product_id", $product_id)
		  ->where('I.archived', 'N')
		  ->where_not_in("I.status_id", $this->product_status_to_dont_count_in_memotags)
		  ->group_by("I.product_id");
		$subselect = $this->db->get_compiled_select();
		//var_dump($subselect);exit;

		$this->product_type = $type;
		$this->db->select(" ( $subselect ) as total_aval_colors "); // Checks if there are more available colors
		$this->select_product_basics($product_id);
		//$this->select_product_counters();
		$this->select_product_abrasion(null, array('visibleOnly' => true, 'showLimit' => true));
		$this->select_product_cleanings();
		$this->select_product_content_front();
		$this->select_product_content_back();
		$this->select_product_finishs();
		$this->select_product_firecodes(null, array('visibleOnly' => true));
		//$this->select_product_origins($id);
		//$this->select_product_shelfs($id);
		$this->select_product_uses($product_id);
		//$this->select_product_various();
		$this->select_product_vendors($product_id);
		$this->select_product_weaves($product_id);
		//$this->select_product_prices();
		//$this->select_product_costs($id);
		$this->select_product_files(); // memotag picture!

		$query = $this->db->get();
		return $this->_return_if_non_empty($query, false);
// 		return $query->row_array();
	}


	public function refresh_cached_product_row($product_id, $product_type)
	{
		// Sanitize input
		$product_id = (int) $product_id;
		$product_type = ($product_type === 'D') ? 'D' : 'R';
	
		// Build SELECT query that returns 1 row for the given product
		$sql = $this->db->query("
			SELECT *, vendors_abrev AS searchable_vendors_abrev FROM (
				" . $this->get_products_spec_full_unfiltered_query(false) . "
			) AS full
			WHERE full.product_id = {$product_id}
			AND full.product_type = '{$product_type}'
			LIMIT 1
		");
	
		$row = $sql->row_array();
		if (!$row) {
			log_message('error', "Cache refresh failed: no product found for ID $product_id type $product_type");
			return false;
		}
	
		// Upsert into cache table
		$this->db->replace('cached_product_spec_view', $row);
	
		log_message('info', "Cache row refreshed for product_id {$product_id}, type {$product_type}");
		return true;
	}

	public function get_products_spec_view()
	{
		// Optional: rebuild cache only if needed
		//$this->build_cached_product_spec_view();
	
		// Skip DataTables column schema processing for cached view
		// We'll handle search manually since we're using a pre-built cached table
		$search = $this->searchText;
		
		// Get pagination and ordering from DataTables
		$order = $this->input->post('order');
		if ($order !== null) {
			$columns = $this->input->post('columns');
			$order_column = $columns[$order[0]['column']]['data'] ?? 'product_name';
			$order_dir = $order[0]['dir'] ?? 'ASC';
		} else {
			$order_column = 'product_name';
			$order_dir = 'ASC';
		}
	
		$sql = "SELECT * FROM cached_product_spec_view";
	
		$whereParts = [];
	
		if (!empty($search)) {
			$search = $this->db->escape_str($search);
			
			// Comprehensive search across all relevant fields
			$whereParts[] = "(
				vendors_abrev LIKE '%" . $search . "%'
				OR searchable_vendors_abrev LIKE '%" . $search . "%'
				OR product_name LIKE '%" . $search . "%'
				OR vendors_name LIKE '%" . $search . "%'
				OR vendor_product_name LIKE '%" . $search . "%'
				OR vendor_business_name LIKE '%" . $search . "%'
				OR searchable_colors LIKE '%" . $search . "%'
				OR searchable_uses LIKE '%" . $search . "%'
				OR searchable_firecodes LIKE '%" . $search . "%'
				OR searchable_content_front LIKE '%" . $search . "%'
				OR weaves LIKE '%" . $search . "%'
				OR colors LIKE '%" . $search . "%'
				OR uses LIKE '%" . $search . "%'
				OR firecodes LIKE '%" . $search . "%'
				OR content_front LIKE '%" . $search . "%'
			)";
		}
	
		if (!empty($whereParts)) {
			$sql .= " WHERE " . implode(" AND ", $whereParts);
		}
	
		// ✅ De-duplicate results based on primary identifiers
		$sql .= " GROUP BY product_id, product_type";
		$sql .= " ORDER BY " . $order_column . " " . $order_dir;

		// Apply pagination manually
		$start = $this->input->post('start') ?? 0;
		$length = $this->input->post('length') ?? 50;
		
		// Get total count
		$count_sql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
		$total_query = $this->db->query($count_sql);
		$total_count = $total_query->row_array()['total'];
		
		// Add pagination
		if ($length != -1) {
			$sql .= " LIMIT $start, $length";
		}
		
		$query = $this->db->query($sql);
		$results = $query->result_array();
		
		return [
			'arr' => $results,
			'recordsTotal' => $this->db->count_all('cached_product_spec_view'),
			'recordsFiltered' => $total_count,
			'query' => $sql
		];
	}

    /**
     * Translates legacy WHERE clause references to match the cached_product_spec_view schema.
     */
    private function flatten_cached_spec_where_clause($clause)
    {
        $replacements = [
            'P.name' => 'product_name',
            'P.dig_product_name' => 'product_name', // dig names were merged into product_name
            'PV.vendor_product_name' => 'vendor_product_name',
            'V.abrev' => 'vendors_abrev', // Add mapping for vendor abbreviation
            'C.name' => 'searchable_colors',
            'U.name' => 'searchable_uses',
            'FT.name' => 'searchable_firecodes',
            'PC.name' => 'searchable_content_front',
        ];
    
        return str_replace(array_keys($replacements), array_values($replacements), $clause);
    }

	public function build_cached_product_spec_view()
	{
		// First, truncate the table to ensure a completely fresh rebuild
		$this->db->query("TRUNCATE TABLE cached_product_spec_view");
		
		$sql = "INSERT INTO cached_product_spec_view (
			product_name,
			vrepeat,
			hrepeat,
			width,
			product_id,
			outdoor,
			product_type,
			archived,
			in_master,
			abrasions,
			count_abrasion_files,
			content_front,
			firecodes,
			count_firecode_files,
			uses,
			uses_id,
			vendor_product_name,
			tariff_surcharge,
			freight_surcharge,
			p_hosp_cut,
			p_hosp_roll,
			p_res_cut,
			p_dig_res,
			p_dig_hosp,
			price_date,
			fob,
			cost_cut,
			cost_half_roll,
			cost_roll,
			cost_roll_landed,
			cost_roll_ex_mill,
			cost_date,
			vendors_name,
			vendors_abrev,
			vendor_business_name,
			weaves,
			weaves_id,
			colors,
			color_ids,
			searchable_colors,
			searchable_uses,
			searchable_firecodes,
			searchable_content_front,
			searchable_vendors_abrev
		)
		" . $this->get_products_spec_full_unfiltered_query(false);
	
		return $this->db->query($sql);
	}

	
	public function get_products_spec_full_unfiltered_query($include_digital = false)
	{
		$where_regular = "P.archived = 'N'";
		$where_digital = "X.archived = 'N'";
	
		$query = "
		(SELECT DISTINCT
			P.name AS product_name,
			P.vrepeat,
			P.hrepeat,
			P.width,
			P.id AS product_id,
			P.outdoor,
			'R' AS product_type,
			P.archived,
			P.in_master,
			GROUP_CONCAT(DISTINCT A.abrasion_test_id, '*', AL.name, '-', A.n_rubs, '-', AT.name SEPARATOR ' / ') AS abrasions,
			COUNT(DISTINCT AF.abrasion_id) AS count_abrasion_files,
			GROUP_CONCAT(DISTINCT REPLACE(CF.perc, '.00', ''), '% ', PC.name ORDER BY CF.perc DESC SEPARATOR ' / ') AS content_front,
			GROUP_CONCAT(DISTINCT FT.name SEPARATOR ' / ') AS firecodes,
			COUNT(DISTINCT FF.firecode_id) AS count_firecode_files,
			GROUP_CONCAT(DISTINCT U.name ORDER BY U.name ASC SEPARATOR ' / ') AS uses,
			GROUP_CONCAT(DISTINCT U.id SEPARATOR ' / ') AS uses_id,
			PV.vendor_product_name,
			PV.tariff_surcharge,
			PV.freight_surcharge,
			PR.p_hosp_cut,
			PR.p_hosp_roll,
			PR.p_res_cut,
			PR.p_dig_res,
			PR.p_dig_hosp,
			DATE_FORMAT(PR.date, '%m/%d/%Y') AS price_date,
			PCOST.fob,
			IFNULL(PCOST.cost_cut, '-') AS cost_cut,
			IFNULL(PCOST.cost_half_roll, '-') AS cost_half_roll,
			IFNULL(PCOST.cost_roll, '-') AS cost_roll,
			IFNULL(PCOST.cost_roll_landed, '-') AS cost_roll_landed,
			IFNULL(PCOST.cost_roll_ex_mill, '-') AS cost_roll_ex_mill,
			DATE_FORMAT(PCOST.date, '%m/%d/%Y') AS cost_date,
			NULLIF(PV.vendor_product_name, '') AS vendors_name,
			V.abrev AS vendors_abrev,
			V.name AS vendor_business_name,
			GROUP_CONCAT(DISTINCT W.name ORDER BY W.name ASC SEPARATOR ' / ') AS weaves,
			GROUP_CONCAT(DISTINCT W.id SEPARATOR ' / ') AS weaves_id,
			GROUP_CONCAT(DISTINCT C.name ORDER BY C.name ASC SEPARATOR ' / ') AS colors,
			GROUP_CONCAT(DISTINCT C.id SEPARATOR ' / ') AS color_ids,
			GROUP_CONCAT(DISTINCT C.name ORDER BY C.name ASC SEPARATOR ' ') AS searchable_colors,
			GROUP_CONCAT(DISTINCT U.name ORDER BY U.name ASC SEPARATOR ' ') AS searchable_uses,
			GROUP_CONCAT(DISTINCT FT.name SEPARATOR ' ') AS searchable_firecodes,
			GROUP_CONCAT(DISTINCT PC.name ORDER BY CF.perc DESC SEPARATOR ' ') AS searchable_content_front,
			V.abrev AS searchable_vendors_abrev
		FROM T_PRODUCT P
		LEFT JOIN T_PRODUCT_ABRASION A ON P.id = A.product_id
		LEFT JOIN P_ABRASION_TEST AT ON A.abrasion_test_id = AT.id
		LEFT JOIN P_ABRASION_LIMIT AL ON A.abrasion_limit_id = AL.id
		LEFT JOIN T_PRODUCT_ABRASION_FILES AF ON A.id = AF.abrasion_id
		LEFT JOIN T_PRODUCT_CONTENT_FRONT CF ON P.id = CF.product_id
		LEFT JOIN P_CONTENT PC ON CF.content_id = PC.id
		LEFT JOIN T_PRODUCT_FIRECODE F ON P.id = F.product_id
		LEFT JOIN P_FIRECODE_TEST FT ON F.firecode_test_id = FT.id
		LEFT JOIN T_PRODUCT_FIRECODE_FILES FF ON F.id = FF.firecode_id
		LEFT JOIN T_PRODUCT_USE PU ON P.id = PU.product_id
		LEFT JOIN P_USE U ON PU.use_id = U.id
		LEFT JOIN T_PRODUCT_VARIOUS PV ON P.id = PV.product_id
		LEFT JOIN T_PRODUCT_PRICE PR ON PR.product_id = P.id AND PR.product_type = 'R'
		LEFT JOIN T_PRODUCT_PRICE_COST PCOST ON P.id = PCOST.product_id
		LEFT JOIN T_PRODUCT_VENDOR TV ON P.id = TV.product_id
		LEFT JOIN Z_VENDOR V ON TV.vendor_id = V.id
		LEFT JOIN T_PRODUCT_WEAVE PW ON P.id = PW.product_id
		LEFT JOIN P_WEAVE W ON PW.weave_id = W.id
		LEFT JOIN T_ITEM I ON I.product_id = P.id
		LEFT JOIN T_ITEM_COLOR IC ON IC.item_id = I.id
		LEFT JOIN P_COLOR C ON C.id = IC.color_id,
		P_PRICE_TYPE PT
		WHERE $where_regular
		GROUP BY product_id, product_type)";

		// Conditionally add digital products UNION
		if ($include_digital) {
			$query .= "
		
		UNION ALL
	
		(SELECT DISTINCT
			CONCAT(DS.name, ' on ', CASE WHEN X.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, COALESCE(P.dig_product_name, P.name), ' / ', GROUP_CONCAT(DISTINCT C.name SEPARATOR ' / ')) AS product_name,
			DS.vrepeat,
			DS.hrepeat,
			COALESCE(P.dig_width, P.width),
			X.id AS product_id,
			P.outdoor,
			'D' AS product_type,
			X.archived,
			X.in_master,
			GROUP_CONCAT(DISTINCT A.abrasion_test_id, '*', AL.name, '-', A.n_rubs, '-', AT.name SEPARATOR ' / ') AS abrasions,
			COUNT(DISTINCT AF.abrasion_id) AS count_abrasion_files,
			GROUP_CONCAT(DISTINCT REPLACE(CF.perc, '.00', ''), '% ', PC.name ORDER BY CF.perc DESC SEPARATOR ' / ') AS content_front,
			GROUP_CONCAT(DISTINCT FT.name SEPARATOR ' / ') AS firecodes,
			COUNT(DISTINCT FF.firecode_id) AS count_firecode_files,
			GROUP_CONCAT(DISTINCT U.name ORDER BY U.name ASC SEPARATOR ' / ') AS uses,
			GROUP_CONCAT(DISTINCT U.id SEPARATOR ' / ') AS uses_id,
			PV.vendor_product_name,
			PV.tariff_surcharge,
			PV.freight_surcharge,
			PR.p_hosp_cut,
			PR.p_hosp_roll,
			PR.p_res_cut,
			PR.p_dig_res,
			PR.p_dig_hosp,
			DATE_FORMAT(PR.date, '%m/%d/%Y') AS price_date,
			PCOST.fob,
			IFNULL(PCOST.cost_cut, '-') AS cost_cut,
			IFNULL(PCOST.cost_half_roll, '-') AS cost_half_roll,
			IFNULL(PCOST.cost_roll, '-') AS cost_roll,
			IFNULL(PCOST.cost_roll_landed, '-') AS cost_roll_landed,
			IFNULL(PCOST.cost_roll_ex_mill, '-') AS cost_roll_ex_mill,
			DATE_FORMAT(PCOST.date, '%m/%d/%Y') AS cost_date,
			NULLIF(PV.vendor_product_name, '') AS vendors_name,
			V.abrev AS vendors_abrev,
			V.name AS vendor_business_name,
			GROUP_CONCAT(DISTINCT W.name ORDER BY W.name ASC SEPARATOR ' / ') AS weaves,
			GROUP_CONCAT(DISTINCT W.id SEPARATOR ' / ') AS weaves_id,
			GROUP_CONCAT(DISTINCT C.name ORDER BY C.name ASC SEPARATOR ' / ') AS colors,
			GROUP_CONCAT(DISTINCT C.id SEPARATOR ' / ') AS color_ids,
			GROUP_CONCAT(DISTINCT C.name ORDER BY C.name ASC SEPARATOR ' ') AS searchable_colors,
			GROUP_CONCAT(DISTINCT U.name ORDER BY U.name ASC SEPARATOR ' ') AS searchable_uses,
			GROUP_CONCAT(DISTINCT FT.name SEPARATOR ' ') AS searchable_firecodes,
			GROUP_CONCAT(DISTINCT PC.name ORDER BY CF.perc DESC SEPARATOR ' ') AS searchable_content_front,
			V.abrev AS searchable_vendors_abrev
		FROM T_PRODUCT_X_DIGITAL X
		JOIN U_DIGITAL_STYLE DS ON X.style_id = DS.id
		JOIN T_ITEM I ON X.item_id = I.id
		JOIN T_PRODUCT P ON I.product_id = P.id
		LEFT JOIN T_ITEM_COLOR IC ON IC.item_id = I.id
		LEFT JOIN P_COLOR C ON C.id = IC.color_id
		LEFT JOIN T_PRODUCT_ABRASION A ON P.id = A.product_id
		LEFT JOIN P_ABRASION_TEST AT ON A.abrasion_test_id = AT.id
		LEFT JOIN P_ABRASION_LIMIT AL ON A.abrasion_limit_id = AL.id
		LEFT JOIN T_PRODUCT_ABRASION_FILES AF ON A.id = AF.abrasion_id
		LEFT JOIN T_PRODUCT_CONTENT_FRONT CF ON P.id = CF.product_id
		LEFT JOIN P_CONTENT PC ON CF.content_id = PC.id
		LEFT JOIN T_PRODUCT_FIRECODE F ON P.id = F.product_id
		LEFT JOIN P_FIRECODE_TEST FT ON F.firecode_test_id = FT.id
		LEFT JOIN T_PRODUCT_FIRECODE_FILES FF ON F.id = FF.firecode_id
		LEFT JOIN T_PRODUCT_USE PU ON P.id = PU.product_id
		LEFT JOIN P_USE U ON PU.use_id = U.id
		LEFT JOIN T_PRODUCT_VARIOUS PV ON P.id = PV.product_id
		LEFT JOIN T_PRODUCT_PRICE PR ON PR.product_id = X.id AND PR.product_type = 'D'
		LEFT JOIN T_PRODUCT_PRICE_COST PCOST ON P.id = PCOST.product_id
		LEFT JOIN T_PRODUCT_VENDOR TV ON P.id = TV.product_id
		LEFT JOIN Z_VENDOR V ON TV.vendor_id = V.id
		LEFT JOIN T_PRODUCT_WEAVE PW ON P.id = PW.product_id
		LEFT JOIN P_WEAVE W ON PW.weave_id = W.id,
		P_PRICE_TYPE PT
		WHERE $where_digital
		GROUP BY product_id, product_type)";
		}

		return $query;
	}
	

	private function remap_where_clause_aliases($clause)
	{
		// Map full table names to their aliases
		$table_aliases = [
			'T_PRODUCT' => 'P',
			'T_PRODUCT_X_DIGITAL' => 'X',
			'T_ITEM' => 'I',
			'U_DIGITAL_STYLE' => 'DS',
			'T_ITEM_COLOR' => 'IC',
			'P_COLOR' => 'C',
			'T_PRODUCT_VARIOUS' => 'PV',
			'T_PRODUCT_PRICE' => 'PR',
			'T_PRODUCT_PRICE_COST' => 'PCOST',
			'Z_VENDOR' => 'V',
			'T_PRODUCT_VENDOR' => 'TV',
			'T_PRODUCT_WEAVE' => 'PW',
			'P_WEAVE' => 'W',
			'T_PRODUCT_ABRASION' => 'A',
			'P_ABRASION_TEST' => 'AT',
			'P_ABRASION_LIMIT' => 'AL',
			'T_PRODUCT_ABRASION_FILES' => 'AF',
			'T_PRODUCT_CONTENT_FRONT' => 'CF',
			'P_CONTENT' => 'PC',
			'T_PRODUCT_FIRECODE' => 'F',
			'P_FIRECODE_TEST' => 'FT',
			'T_PRODUCT_FIRECODE_FILES' => 'FF',
			'T_PRODUCT_USE' => 'PU',
			'P_USE' => 'U',
			'P_PRICE_TYPE' => 'PT'
		];
	
		// Sort by longest first to prevent partial replacements
		uksort($table_aliases, function ($a, $b) {
			return strlen($b) - strlen($a);
		});
	
		foreach ($table_aliases as $full => $alias) {
			$clause = preg_replace('/\b' . preg_quote($full, '/') . '\./', $alias . '.', $clause);
		}
	
		return $clause;
	}


	function selectors($for, $type)
	{
		$this->product_type = $type;
		$this->select_product_basics();
		$this->set_datatables_variables();

		switch ($for) {
			case 'specs':
				// For Specifications List table
				$this->select_product_abrasion();
				//$this->select_product_cleanings();
				$this->select_product_content_front();
				//$this->select_product_content_back();
				//$this->select_product_finishs();
				$this->select_product_firecodes();
				//$this->select_product_shelfs();
				//$this->select_product_origins();
				$this->select_product_uses();
				$this->select_product_various(null, false, ['vendor_product_name', 'tariff_surcharge', 'freight_surcharge']);
                // $this->select_product_various();
				$this->select_product_prices();
				if(!$this->is_showroom){
					$this->select_product_costs();
					$this->select_product_vendors();
				}
				$this->select_product_weaves();
				break;

			case 'prices':
				// For Price List table
				//$this->select_product_shelfs();
				//$this->select_product_various();
				$this->select_product_prices();
				if(!$this->is_showroom){
					$this->select_product_costs();
					$this->select_product_vendors();
				}

				break;

			default:
				break;
		}

		$this->where_product_is_not_archived();
	}

	function get_product_edit($type, $id, $someParams = array())
	{
		$this->product_type = $type;
	
		// Main optimized query (fast version)
		$main_sql = "SELECT
			T_PRODUCT.id AS product_id,
			T_PRODUCT.name AS product_name,
			T_PRODUCT.width,
			T_PRODUCT.vrepeat,
			T_PRODUCT.hrepeat,
			T_PRODUCT.lightfastness,
			T_PRODUCT.seam_slippage,
			T_PRODUCT.outdoor,
			T_PRODUCT.archived,
			T_PRODUCT.in_master,
			T_PRODUCT.dig_product_name,
			T_PRODUCT.dig_width,
			'R' AS product_type,
			COUNT(DISTINCT S_HISTORY_PRODUCT.id) AS cant_status_update,
			COUNT(DISTINCT T_PRODUCT_MESSAGES.id) AS cant_messages,
			COUNT(DISTINCT S_HISTORY_PRODUCT_PRICE.id) AS cant_price_updates,
			COUNT(DISTINCT S_HISTORY_PRODUCT_PRICE_COST.id) AS cant_cost_updates,
			GROUP_CONCAT(DISTINCT P_CLEANING.id SEPARATOR ' / ') AS cleanings_ids,
			GROUP_CONCAT(DISTINCT P_CLEANING.name SEPARATOR ' / ') AS cleanings,
			T_PRODUCT_CLEANING_SPECIAL.special_instruction AS special_cleaning_instr,
			GROUP_CONCAT(DISTINCT P_CLEANING_INSTRUCTIONS.id ORDER BY P_CLEANING_INSTRUCTIONS.id SEPARATOR ' / ') AS cleaning_instructions_ids,
			GROUP_CONCAT(DISTINCT P_CLEANING_INSTRUCTIONS.name ORDER BY P_CLEANING_INSTRUCTIONS.id SEPARATOR ' / ') AS cleaning_instructions,
			GROUP_CONCAT(DISTINCT P_WARRANTY.id ORDER BY P_WARRANTY.id SEPARATOR ' / ') AS warranty_ids,
			GROUP_CONCAT(DISTINCT P_WARRANTY.name ORDER BY P_WARRANTY.id SEPARATOR ' / ') AS warranty,
			GROUP_CONCAT(DISTINCT REPLACE(T_PRODUCT_CONTENT_FRONT.perc, '.00', ''), '% ', PC1.name ORDER BY T_PRODUCT_CONTENT_FRONT.perc DESC SEPARATOR ' / ') AS content_front,
			GROUP_CONCAT(DISTINCT REPLACE(T_PRODUCT_CONTENT_BACK.perc, '.00', ''), '% ', PC2.name ORDER BY T_PRODUCT_CONTENT_BACK.perc DESC SEPARATOR ' / ') AS content_back,
			GROUP_CONCAT(DISTINCT P_FINISH.name SEPARATOR ' / ') AS finishs,
			GROUP_CONCAT(DISTINCT P_FINISH.id SEPARATOR ' / ') AS finishs_id,
			T_PRODUCT_FINISH_SPECIAL.special_instruction AS special_finish_instr,
			GROUP_CONCAT(DISTINCT P_ORIGIN.name SEPARATOR ' / ') AS origin,
			GROUP_CONCAT(DISTINCT P_ORIGIN.id SEPARATOR ' / ') AS origin_id,
			GROUP_CONCAT(DISTINCT P_USE.name ORDER BY P_USE.name ASC SEPARATOR ' / ') AS uses,
			GROUP_CONCAT(DISTINCT P_USE.id SEPARATOR ' / ') AS uses_id,
			T_PRODUCT_VARIOUS.vendor_product_name,
			T_PRODUCT_VARIOUS.yards_per_roll,
			T_PRODUCT_VARIOUS.lead_time,
			T_PRODUCT_VARIOUS.min_order_qty,
			T_PRODUCT_VARIOUS.tariff_code,
			T_PRODUCT_VARIOUS.tariff_surcharge,
			T_PRODUCT_VARIOUS.duty_perc,
			T_PRODUCT_VARIOUS.freight_surcharge,
			T_PRODUCT_VARIOUS.vendor_notes,
			T_PRODUCT_VARIOUS.railroaded,
			T_PRODUCT_VARIOUS.prop_65,
			T_PRODUCT_VARIOUS.ab_2998_compliant,
			T_PRODUCT_VARIOUS.dyed_options,
			T_PRODUCT_VARIOUS.weight_n,
			T_PRODUCT_VARIOUS.weight_unit_id,
			GROUP_CONCAT(DISTINCT V.id SEPARATOR ' / ') AS vendors_id,
			V.name AS vendors_name,
			V.abrev AS vendors_abrev,
			T_PRODUCT_PRICE.p_hosp_cut,
			T_PRODUCT_PRICE.p_hosp_roll,
			T_PRODUCT_PRICE.p_res_cut,
			T_PRODUCT_PRICE.p_dig_res,
			T_PRODUCT_PRICE.p_dig_hosp,
			DATE_FORMAT(T_PRODUCT_PRICE.date, '%m/%d/%Y') AS price_date,
			T_PRODUCT_PRICE_COST.fob,
			T_PRODUCT_PRICE_COST.cost_cut_type_id,
			T_PRODUCT_PRICE_COST.cost_cut,
			T_PRODUCT_PRICE_COST.cost_half_roll_type_id,
			T_PRODUCT_PRICE_COST.cost_half_roll,
			T_PRODUCT_PRICE_COST.cost_roll_type_id,
			T_PRODUCT_PRICE_COST.cost_roll,
			T_PRODUCT_PRICE_COST.cost_roll_landed_type_id,
			T_PRODUCT_PRICE_COST.cost_roll_landed,
			T_PRODUCT_PRICE_COST.cost_roll_ex_mill_type_id,
			T_PRODUCT_PRICE_COST.cost_roll_ex_mill,
			DATE_FORMAT(T_PRODUCT_PRICE_COST.date, '%m/%d/%Y') AS cost_date,
			GROUP_CONCAT(DISTINCT P_WEAVE.name ORDER BY P_WEAVE.name ASC SEPARATOR ' / ') AS weaves,
			GROUP_CONCAT(DISTINCT P_WEAVE.id SEPARATOR ' / ') AS weaves_id,
			GROUP_CONCAT(DISTINCT T_PRODUCT_FILES.url_dir, '#', T_PRODUCT_FILES.date_add, '#', T_PRODUCT_FILES.user_id, '#', P_CATEGORY_FILES.id, '#', P_CATEGORY_FILES.name, '#', IFNULL(T_PRODUCT_FILES.descr, ' ') ORDER BY T_PRODUCT_FILES.date_add DESC SEPARATOR '**') AS files,
			SHOWCASE_PRODUCT.url_title,
			SHOWCASE_PRODUCT.descr AS showcase_descr,
			SHOWCASE_PRODUCT.visible AS showcase_visible,
			SHOWCASE_PRODUCT.pic_big,
			SHOWCASE_PRODUCT.pic_big_url
		FROM T_PRODUCT
		LEFT JOIN S_HISTORY_PRODUCT ON T_PRODUCT.id = S_HISTORY_PRODUCT.product_id
		LEFT JOIN T_PRODUCT_MESSAGES ON T_PRODUCT_MESSAGES.product_id = T_PRODUCT.id AND T_PRODUCT_MESSAGES.product_type = 'R'
		LEFT JOIN S_HISTORY_PRODUCT_PRICE ON T_PRODUCT.id = S_HISTORY_PRODUCT_PRICE.product_id AND S_HISTORY_PRODUCT_PRICE.product_type = 'R'
		LEFT JOIN S_HISTORY_PRODUCT_PRICE_COST ON T_PRODUCT.id = S_HISTORY_PRODUCT_PRICE_COST.product_id
		LEFT JOIN T_PRODUCT_CLEANING ON T_PRODUCT.id = T_PRODUCT_CLEANING.product_id
		LEFT JOIN P_CLEANING ON T_PRODUCT_CLEANING.cleaning_id = P_CLEANING.id
		LEFT JOIN T_PRODUCT_CLEANING_SPECIAL ON T_PRODUCT.id = T_PRODUCT_CLEANING_SPECIAL.product_id
		LEFT JOIN T_PRODUCT_CLEANING_INSTRUCTIONS ON T_PRODUCT.id = T_PRODUCT_CLEANING_INSTRUCTIONS.product_id
		LEFT JOIN P_CLEANING_INSTRUCTIONS ON T_PRODUCT_CLEANING_INSTRUCTIONS.cleaning_instructions_id = P_CLEANING_INSTRUCTIONS.id
		LEFT JOIN T_PRODUCT_WARRANTY ON T_PRODUCT.id = T_PRODUCT_WARRANTY.product_id
		LEFT JOIN P_WARRANTY ON T_PRODUCT_WARRANTY.warranty_id = P_WARRANTY.id
		LEFT JOIN T_PRODUCT_CONTENT_FRONT ON T_PRODUCT.id = T_PRODUCT_CONTENT_FRONT.product_id
		LEFT JOIN P_CONTENT PC1 ON T_PRODUCT_CONTENT_FRONT.content_id = PC1.id
		LEFT JOIN T_PRODUCT_CONTENT_BACK ON T_PRODUCT.id = T_PRODUCT_CONTENT_BACK.product_id
		LEFT JOIN P_CONTENT PC2 ON T_PRODUCT_CONTENT_BACK.content_id = PC2.id
		LEFT JOIN T_PRODUCT_FINISH ON T_PRODUCT.id = T_PRODUCT_FINISH.product_id
		LEFT JOIN P_FINISH ON T_PRODUCT_FINISH.finish_id = P_FINISH.id AND P_FINISH.active = 'Y'
		LEFT JOIN T_PRODUCT_FINISH_SPECIAL ON T_PRODUCT.id = T_PRODUCT_FINISH_SPECIAL.product_id
		LEFT JOIN T_PRODUCT_ORIGIN ON T_PRODUCT.id = T_PRODUCT_ORIGIN.product_id
		LEFT JOIN P_ORIGIN ON T_PRODUCT_ORIGIN.origin_id = P_ORIGIN.id
		LEFT JOIN T_PRODUCT_USE ON T_PRODUCT.id = T_PRODUCT_USE.product_id
		LEFT JOIN P_USE ON T_PRODUCT_USE.use_id = P_USE.id
		LEFT JOIN T_PRODUCT_VARIOUS ON T_PRODUCT.id = T_PRODUCT_VARIOUS.product_id
		LEFT JOIN T_PRODUCT_VENDOR ON T_PRODUCT.id = T_PRODUCT_VENDOR.product_id
		LEFT JOIN Z_VENDOR V ON T_PRODUCT_VENDOR.vendor_id = V.id
		LEFT JOIN T_PRODUCT_PRICE ON T_PRODUCT_PRICE.product_id = T_PRODUCT.id AND T_PRODUCT_PRICE.product_type = 'R'
		LEFT JOIN T_PRODUCT_PRICE_COST ON T_PRODUCT_PRICE_COST.product_id = T_PRODUCT.id
		LEFT JOIN T_PRODUCT_WEAVE ON T_PRODUCT.id = T_PRODUCT_WEAVE.product_id
		LEFT JOIN P_WEAVE ON T_PRODUCT_WEAVE.weave_id = P_WEAVE.id
		LEFT JOIN T_PRODUCT_FILES ON T_PRODUCT.id = T_PRODUCT_FILES.product_id
		LEFT JOIN P_CATEGORY_FILES ON T_PRODUCT_FILES.category_id = P_CATEGORY_FILES.id
		LEFT JOIN SHOWCASE_PRODUCT ON T_PRODUCT.id = SHOWCASE_PRODUCT.product_id AND SHOWCASE_PRODUCT.product_type = 'R'
		WHERE T_PRODUCT.id = ?
		GROUP BY T_PRODUCT.id";
	
		$main = $this->db->query($main_sql, [$id])->row_array();
	
		// Supplementary queries with proper aliases
		$main['showcase_pattern_id'] = $this->db
			->query("SELECT GROUP_CONCAT(DISTINCT pattern_id SEPARATOR ' / ') AS showcase_pattern_id FROM SHOWCASE_PRODUCT_PATTERNS WHERE product_id = ?", [$id])
			->row_array()['showcase_pattern_id'];
	
		$main['showcase_collection_id'] = $this->db
			->query("SELECT GROUP_CONCAT(DISTINCT collection_id SEPARATOR ' / ') AS showcase_collection_id FROM SHOWCASE_PRODUCT_COLLECTION WHERE product_id = ?", [$id])
			->row_array()['showcase_collection_id'];
	
		$main['showcase_contents_web_id'] = $this->db
			->query("SELECT GROUP_CONCAT(DISTINCT content_web_id SEPARATOR ' / ') AS showcase_contents_web_id FROM SHOWCASE_PRODUCT_CONTENTS_WEB WHERE product_id = ?", [$id])
			->row_array()['showcase_contents_web_id'];
 
		$main['portfolio_urls'] = $this->db
			->query("SELECT GROUP_CONCAT(DISTINCT url SEPARATOR '**') AS portfolio_urls FROM V_PRODUCT_PORTFOLIO_PICTURE WHERE product_id = ? AND product_type = 'R'", [$id])
			->row_array()['portfolio_urls'];
	
		return $main;
	}

	
	/*
	function get_product_edit($type, $id, $someParams = array())
	{
		$this->product_type = $type;
        if($this->product_type == Regular){
            $t = $this->t_product;
            $this->db->select("$t.lightfastness");
            $this->db->select("$t.seam_slippage");
        }
		$this->select_product_basics($id);
		$this->select_product_counters();
		//$this->select_product_abrasion(null, array('visibleOnly'=>false) );
		$this->select_product_cleanings($id);
		$this->select_product_cleaning_instructions($id);
		$this->select_product_warranty($id);
		$this->select_product_content_front();
		$this->select_product_content_back();
		$this->select_product_finishs($id);
		//$this->select_product_firecodes(null, array('visibleOnly'=>false));
		$this->select_product_origins($id);
		//$this->select_product_shelfs($id);
		$this->select_product_uses($id);
		$this->select_product_various();
		$this->select_product_vendors($id);
		$this->select_product_weaves($id);
		$this->select_product_prices();
		$this->select_product_costs($id);
		$this->select_product_files();
		$this->select_product_showcase();
		$this->select_product_portfolio();
        
		// $compiled_select = $this->db->get_compiled_select();
        // echo "<pre> POST: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
		// print_r($compiled_select );
		// echo "</pre>";
		// exit();
   		// $query = $this->db->get();
   		// return $this->_return_if_non_empty($query, false);
		return $this->db->get()->row_array();
	} */

	function get_product_specsheet($type, $id, $someParams = array())
	{
		$this->product_type = $type;
        if($this->product_type == Regular){
            $t = $this->t_product;
            $this->db->select("$t.lightfastness");
        }
		$this->select_product_basics($id);
		$this->select_product_abrasion(null, $someParams);
		$this->select_product_cleanings($id);
		$this->select_product_cleaning_instructions($id, true);
		$this->select_product_content_front();
		$this->select_product_content_back();
		$this->select_product_finishs($id);
		$this->select_product_firecodes();
		$this->select_product_origins($id);
		$this->select_product_various();
		$this->select_product_vendors($id);
		$this->select_product_uses($id);
		$this->select_product_weaves($id);
		$this->select_product_showcase();
		return $this->db->get()->row_array();
	}

	function select_product_basics($id = null)
	{
		$lib_dig = $this->t_digital_style;
		$lib_sp = $this->t_screenprint_style;
		$map_dig = $this->product_digital;
		$map_sp = $this->product_screenprint;

		if (is_null($id)) {
			// For the big table under PRODUCTS / SPECS & PRICES
			$this->db->distinct();
			switch ($this->product_type) {
				case constant('Regular'):
					$this->t = $this->t_product;
					$this->x2 = $this->t;
					$this->db
					  ->select("$this->t.name as product_name")
					  ->select("$this->t.vrepeat, $this->t.hrepeat")
					  ->select("$this->t.width as width");
					break;

				case constant('Digital'):
					$this->t = $map_dig;
					$this->x2 = $lib_dig;
					$this->db
					  ->select("
					CONCAT(
					$this->x2.name, ' on ', 
					CASE WHEN $this->t.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END,
					COALESCE($this->t_product.dig_product_name, $this->t_product.name),
					' / ', 
					GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ') 
					) as product_name")
					  ->select("$this->x2.vrepeat, $this->x2.hrepeat")
					  ->select("COALESCE($this->t_product.dig_width, $this->t_product.width) as width")
					  ->from($this->t_item)
					  ->from($this->t_product)
					  ->from($lib_dig)
					  ->join($this->t_item_color, "$this->t_item_color.item_id = $this->t_item.id", 'left outer')
					  ->join($this->p_color, "$this->p_color.id = $this->t_item_color.color_id", 'left outer')
					  ->where("$this->t.item_id = $this->t_item.id")
					  ->where("$this->t_product.id = $this->t_item.product_id")
					  ->where("$this->t.style_id = $lib_dig.id");
					break;
			}
		} else {
			switch ($this->product_type) {
				case constant('Regular'):
					$this->t = $this->t_product;
					$this->x2 = $this->t;
					$this->db
					  ->select("$this->t.name as product_name")
					  ->select("$this->t.vrepeat, $this->t.hrepeat")
					  ->select("$this->t.width as width")
					  ->select("$this->t.dig_product_name")
					  ->select("$this->t.dig_width")
					  //->select("COUNT(LI1.item_id) as is_30under")
					  //->select("COUNT(LI2.item_id) as is_digitalground")
					  //->join("$this->t_item", "$this->t_item.product_id = $this->t.id", 'left outer')
					  //->join("$this->p_list_items LI1", "$this->t_item.id = LI1.item_id AND LI1.list_id = '$this->under30_list_id' ", 'left outer')
					  //->join("$this->p_list_items LI2", "$this->t_item.id = LI2.item_id AND LI2.list_id = '$this->digital_grounds_list_id' ", 'left outer')

					  ->where("$this->t.id", $id)
					  ->select("COUNT(DISTINCT $this->th_product.id) as cant_status_update")
					  ->join($this->th_product, "$this->t_product.id = $this->th_product.product_id", 'left outer');
					break;

				case constant('Digital'):
					$this->t = $map_dig;
					$this->x2 = $lib_dig;
					$this->db
					  ->select("
						CONCAT(
						$this->x2.name, ' on ', 
						CASE WHEN $this->t.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END,
						COALESCE($this->t_product.dig_product_name, $this->t_product.name),
						' / ', 
						GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ') 
						) as product_name")
					  ->select("$this->x2.vrepeat, $this->x2.hrepeat")
					  ->select("COALESCE($this->t_product.dig_width, $this->t_product.width) as width")
					  ->select("$this->t.reverse_ground")
					  ->select("$this->t.style_id")
					  //->select("'0' as is_30under")
					  //->select("'0' as is_digitalground")
					  ->from($this->t_item)
					  ->from($this->t_product)
					  ->from($lib_dig)
					  ->join($this->t_item_color, "$this->t_item_color.item_id = $this->t_item.id", 'left outer')
					  ->join($this->p_color, "$this->p_color.id = $this->t_item_color.color_id", 'left outer')
					  ->where("$this->t.id", $id)
					  ->where("$this->t.item_id = $this->t_item.id")
					  ->where("$this->t_product.id = $this->t_item.product_id")
					  ->where("$this->t.style_id = $lib_dig.id")
					  ->select("COUNT(DISTINCT $this->th_product_digital.id) as cant_status_update")
					  ->join($this->th_product_digital, "$this->t.id = $this->th_product_digital.product_id", 'left outer');
					break;
			}
		}

		$this->db
		  ->select("$this->t.id as product_id")
		  ->select("$this->t_product.outdoor")
		  ->select(" '$this->product_type' as product_type ", false)
		  ->select("$this->t.archived, $this->t.in_master")
		  ->from($this->t)
		  ->group_by("$this->t.id");
	}

	function select_product_counters()
	{
		$this->db
		  ->select("COUNT(DISTINCT $this->t_product_messages.id) as cant_messages")
		  ->join($this->t_product_messages, "$this->t_product_messages.product_id = $this->t.id AND $this->t_product_messages.product_type = '$this->product_type' ", 'left outer')
		  ->select("COUNT(DISTINCT $this->th_product_price.id) as cant_price_updates")
		  ->select("COUNT(DISTINCT $this->th_product_cost.id) as cant_cost_updates")
		  ->join($this->th_product_price, "$this->t.id = $this->th_product_price.product_id AND '$this->product_type' = $this->th_product_price.product_type ", 'left outer')
		  ->join($this->th_product_cost, "$this->t_product.id = $this->th_product_cost.product_id", 'left outer');
	}

	function select_product_abrasion($id = null, $params = array())
	{

		$default = array(
		  'visibleOnly' => false,
		  'showLimit' => true
		);
		$params = array_merge($default, $params);

		$abrasion_test = $this->p_abrasion_test;
		$abrasion_limit = $this->p_abrasion_limit;
		$files = $this->t_product_abrasion_files;
		$extraParam = '';

		if (is_null($id)) {
			if (isset($params['visibleOnly']) && $params['visibleOnly']) {
				$extraParam = " AND $this->t_product_abrasion.visible = 'Y' ";
			}

			if ($params['showLimit']) {
				$this->db->select("GROUP_CONCAT(DISTINCT $this->t_product_abrasion.abrasion_test_id, '*', $abrasion_limit.name, '-', $this->t_product_abrasion.n_rubs, '-', $abrasion_test.name SEPARATOR ' / ') as abrasions");
			} else {
				$this->db->select("GROUP_CONCAT(DISTINCT $this->t_product_abrasion.abrasion_test_id, '*', $this->t_product_abrasion.n_rubs, '-', $abrasion_test.name SEPARATOR ' / ') as abrasions");
			}

			$this->db
			  ->select("COUNT(DISTINCT $files.abrasion_id) as count_abrasion_files")
			  ->join($this->t_product_abrasion, "$this->t_product.id = $this->t_product_abrasion.product_id $extraParam", 'left outer')
			  ->join($abrasion_test, "$this->t_product_abrasion.abrasion_test_id = $abrasion_test.id", 'left outer')
			  ->join($abrasion_limit, "$this->t_product_abrasion.abrasion_limit_id = $abrasion_limit.id", 'left outer')
			  ->join($files, "$this->t_product_abrasion.id = $files.abrasion_id", 'left outer');

		} else {
			$t = $this->t_product_abrasion;
			$this->db
			  ->select("$t.id as id, $abrasion_test.id as abrasion_test_id, $abrasion_test.name as abrasion_test_name")
			  ->select("$abrasion_limit.id as abrasion_limit_id, $abrasion_limit.name as abrasion_limit_name")
			  ->select("$t.data_in_vendor_specsheet")
			  ->select("$t.n_rubs as rubs")
			  ->select("GROUP_CONCAT($files.url_dir SEPARATOR '**') as files")
			  ->select("$t.date_add")
			  ->select("$t.visible")
			  ->from($t)
			  ->join($abrasion_test, "$t.abrasion_test_id = $abrasion_test.id", 'left outer')
			  ->join($abrasion_limit, "$t.abrasion_limit_id = $abrasion_limit.id", 'left outer')
			  ->join($files, "$t.id = $files.abrasion_id", 'left outer')
			  ->where("$t.product_id", $id)
			  ->group_by("$t.id");
			if (isset($params['visibleOnly']) && $params['visibleOnly']) {
				$this->db->where("$t.visible", 'Y');
				//$extraParam = " AND $this->t_product_abrasion.visible = 'Y' ";
			}
			return $this->db->get()->result_array();
		}
	}

	function select_product_cleanings($id = null)
	{
		if (is_null($id)) {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT $this->p_cleaning.name SEPARATOR ' / ') as cleanings")
			  ->join($this->t_product_cleaning, "$this->t_product.id = $this->t_product_cleaning.product_id", 'left outer')
			  ->join($this->p_cleaning, "$this->t_product_cleaning.cleaning_id = $this->p_cleaning.id", "left outer");
		} else {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT $this->p_cleaning.id SEPARATOR ' / ') as cleanings_ids")
			  ->select("GROUP_CONCAT(DISTINCT $this->p_cleaning.name SEPARATOR ' / ') as cleanings")
			  ->select("$this->t_product_cleaning_specials.special_instruction as special_cleaning_instr")
			  ->join($this->t_product_cleaning, "$this->t.id = $this->t_product_cleaning.product_id", 'left outer')
			  ->join($this->p_cleaning, "$this->t_product_cleaning.cleaning_id = $this->p_cleaning.id", 'left outer')
			  ->join($this->t_product_cleaning_specials, "$this->t.id = $this->t_product_cleaning_specials.product_id", 'left outer');
		}
	}

	function select_product_cleaning_instructions($id = null, $include_files=false)
	{
		if (is_null($id)) {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT $this->p_cleaning_instructions.name ORDER BY $this->p_cleaning_instructions.id SEPARATOR ' / ') as cleaning_instructions")
			  ->join($this->t_product_cleaning_instructions, "$this->t_product.id = $this->t_product_cleaning_instructions.product_id", 'left outer')
			  ->join($this->p_cleaning_instructions, "$this->t_product_cleaning_instructions.cleaning_instructions_id = $this->p_cleaning_instructions.id", "left outer");
		} else {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT $this->p_cleaning_instructions.id ORDER BY $this->p_cleaning_instructions.id SEPARATOR ' / ') as cleaning_instructions_ids")
			  ->select("GROUP_CONCAT(DISTINCT $this->p_cleaning_instructions.name ORDER BY $this->p_cleaning_instructions.id SEPARATOR ' / ') as cleaning_instructions")
			  ->join($this->t_product_cleaning_instructions, "$this->t.id = $this->t_product_cleaning_instructions.product_id", 'left outer')
			  ->join($this->p_cleaning_instructions, "$this->t_product_cleaning_instructions.cleaning_instructions_id = $this->p_cleaning_instructions.id", 'left outer')
			;
		}
		if ($include_files){
			$table_files = $this->p_cleaning_instructions . '_FILES';
			$this->db
				->select("GROUP_CONCAT(DISTINCT $table_files.url_dir ORDER BY $this->p_cleaning_instructions.id SEPARATOR '**') as cleaning_instructions_files")
				->join($table_files, "$this->p_cleaning_instructions.id = $table_files.related_id", "left outer")
			;
		}
	}
	
	function select_product_warranty($id = null, $include_files=false){
		$attr_name = 'warranty';
		$consts_table = $this->p_warranty;
		$product_table = $this->t_product_warranty;
		
		if (is_null($id)) {
			$this->db
				->select("GROUP_CONCAT(DISTINCT $consts_table.name ORDER BY $consts_table.id SEPARATOR ' / ') as $attr_name")
				->join($product_table, "$this->t_product.id = $product_table.product_id", 'left outer')
				->join($consts_table, "$product_table.".$attr_name."_id = $consts_table.id", "left outer");
		} else {
			$this->db
				->select("GROUP_CONCAT(DISTINCT $consts_table.id ORDER BY $consts_table.id SEPARATOR ' / ') as ".$attr_name."_ids")
				->select("GROUP_CONCAT(DISTINCT $consts_table.name ORDER BY $consts_table.id SEPARATOR ' / ') as $attr_name")
				->join($product_table, "$this->t.id = $product_table.product_id", 'left outer')
				->join($consts_table, "$product_table.".$attr_name."_id = $consts_table.id", 'left outer')
			;
		}
		if ($include_files){
			$table_files = $product_table . '_FILES';
			$this->db
				->select("GROUP_CONCAT(DISTINCT $table_files.url_dir ORDER BY $consts_table.id SEPARATOR '**') as ".$attr_name."_files")
				->join($table_files, "$consts_table.id = $table_files.related_id", "left outer")
			;
		}
	}

	function select_product_content_front($id = null)
	{
		if (is_null($id)) {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT REPLACE($this->t_product_content_front.perc, '.00', ''), '% ', PC1.name ORDER BY $this->t_product_content_front.perc DESC SEPARATOR ' / ' ) as content_front")
			  ->join($this->t_product_content_front, "$this->t_product.id = $this->t_product_content_front.product_id", 'left outer')
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

	function select_product_content_back($id = null)
	{
		if (is_null($id)) {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT REPLACE($this->t_product_content_back.perc, '.00', ''), '% ', PC2.name ORDER BY $this->t_product_content_back.perc DESC SEPARATOR ' / ' ) as content_back")
			  ->join($this->t_product_content_back, "$this->t_product.id = $this->t_product_content_back.product_id", 'left outer')
			  ->join($this->p_content . " PC2 ", "$this->t_product_content_back.content_id = PC2.id", "left outer");
		} else {
			$this->db->select("PC2.id, $this->t_product_content_back.perc, PC2.name")
			  ->from($this->t_product_content_back)
			  ->join($this->p_content . " PC2 ", "$this->t_product_content_back.content_id = PC2.id", 'left outer')
			  ->where("$this->t_product_content_back.product_id", $id)
			  ->order_by("$this->t_product_content_back.perc DESC");
			return $this->db->get()->result_array();
		}
	}

	function select_product_finishs($id = null)
	{
		$this->db
		  ->select("GROUP_CONCAT(DISTINCT $this->p_finish.name SEPARATOR ' / ') as finishs")
		  ->select("GROUP_CONCAT(DISTINCT $this->p_finish.id SEPARATOR ' / ') as finishs_id")
		  ->select("$this->t_product_finish_specials.special_instruction as special_finish_instr")
		  ->join($this->t_product_finish, "$this->t_product.id = $this->t_product_finish.product_id", 'left outer')
		  ->join($this->p_finish, $this->t_product_finish . ".finish_id = $this->p_finish.id AND $this->p_finish.active = 'Y'", 'left outer')
		  ->join($this->t_product_finish_specials, "$this->t_product.id = $this->t_product_finish_specials.product_id", 'left outer');
	}

	function select_product_firecodes($id = null, $params = array())
	{
		$files = $this->t_product_firecode_files;
		$extraParam = '';
		$default = array(
		  'visibleOnly' => false,
		  'showLimit' => true
		);
		$params = array_merge($default, $params);

		if (is_null($id)) {
			if (isset($params['visibleOnly']) && $params['visibleOnly']) {
				$extraParam = " AND $this->t_product_firecode.visible = 'Y' ";
			}
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT $this->p_firecode.name SEPARATOR ' / ') as firecodes")
			  ->select("COUNT(DISTINCT $files.firecode_id) as count_firecode_files")
			  ->join($this->t_product_firecode, "$this->t_product.id = $this->t_product_firecode.product_id $extraParam", 'left outer')
			  ->join($this->p_firecode, "$this->t_product_firecode.firecode_test_id = $this->p_firecode.id", 'left outer')
			  ->join($files, "$this->t_product_firecode.id = $files.firecode_id", 'left outer');
		} else {
			$this->db
			  ->select("$this->t_product_firecode.id, $this->p_firecode.id as firecode_test_id, $this->p_firecode.name as firecode_test_name")
			  ->select("$this->t_product_firecode.data_in_vendor_specsheet")
			  ->select("GROUP_CONCAT($files.url_dir SEPARATOR '**') as files")
			  ->select("$this->t_product_firecode.date_add")
			  ->select("$this->t_product_firecode.visible")
			  ->from($this->t_product_firecode)
			  ->join($this->p_firecode, $this->t_product_firecode . ".firecode_test_id = $this->p_firecode.id", 'left outer')
			  ->join($files, "$this->t_product_firecode.id = $files.firecode_id", 'left outer')
			  ->where("$this->t_product_firecode.product_id", $id)
			  ->group_by("$this->t_product_firecode.id");
			if (isset($params['visibleOnly']) && $params['visibleOnly']) {
				$this->db->where("$this->t_product_firecode.visible", 'Y');
				//$extraParam = " AND $this->t_product_abrasion.visible = 'Y' ";
			}
			return $this->db->get()->result_array();
		}
	}

	function select_product_origins($id = null)
	{
		$this->db
		  ->select("GROUP_CONCAT(DISTINCT $this->p_origin.name SEPARATOR ' / ') as origin")
		  ->select("GROUP_CONCAT(DISTINCT $this->p_origin.id SEPARATOR ' / ') as origin_id")
		  ->join($this->t_product_origin, "$this->t_product.id = $this->t_product_origin.product_id", 'left outer')
		  ->join($this->p_origin, "$this->t_product_origin.origin_id = $this->p_origin.id", 'left outer');
	}

	function select_product_prices($id = null)
	{
		$t = $this->t_product_price;

		if (is_null($id)) {
			switch ($this->product_type) {
				case constant('Regular'):
					$main_table = $this->t_product;
					break;
				case constant('Digital'):
					$main_table = $this->product_digital;
					break;
//         case constant('ScreenPrint'):
//           $main_table = $this->product_screenprint;
//           break;
			}
			$this->db
			  ->select("$t.p_hosp_cut")
			  ->select("$t.p_hosp_roll")
			//   ->select("$t.roll_price")
			  ->select("$t.p_res_cut")
			  ->select("$t.p_dig_res, $t.p_dig_hosp")
			  ->select("DATE_FORMAT($t.date, '%m/%d/%Y') as price_date", false)
			  ->join($t, " $t.product_id = $main_table.id AND $t.product_type = '$this->product_type' ", 'left outer');
		} else {
			$this->db
                ->select("$t.p_res_cut")
                ->select("$t.p_hosp_roll")
			  ->select("$t.p_hosp_cut")
			//   ->select("$t.roll_price")
			  ->select("$t.p_dig_res, $t.p_dig_hosp")
			  ->select("DATE_FORMAT($t.date, '%m/%d/%Y') as price_date", false)
			  ->from($t)
			  ->where("$t.product_id", $id)
			  ->where("$t.product_type", $this->product_type);
			return $this->db->get()->result_array();
		}

		//array_push($this->colSearch, "$t.p_hosp_cut", "$t.p_hosp_roll", "$t.p_res_cut");
	}

	function select_product_costs($id = null, $return = false)
	{
		$t = $this->t_product_cost;

		if (is_null($id)) {
			$this->db
			  /*
			  ->select("  ")
			  ->join($t, "$this->t_product.id = $t.product_id", 'left outer')
			  ->join("$this->p_price_type PT", "$t.price_type = PT.id")
			  ->join("P_COST_TYPE CT", "$t.cost_type = CT.id")
			  */
			  ->select("$t.fob")
			  ->select("IF( $t.cost_cut IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_cut_type_id = PT.id, PT.name, null), ' ', $t.cost_cut) ) as cost_cut")
			  //->select("CASE WHEN $t.cost_cut != '0.00' THEN GROUP_CONCAT(DISTINCT P1.name, ' ', $t.cost_cut) ELSE '-' END as cost_cut")
			  ->select("IF( $t.cost_half_roll IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_half_roll_type_id = PT.id, PT.name, null), ' ', $t.cost_half_roll) ) as cost_half_roll")
			  //->select("CASE WHEN $t.cost_half_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P2.name, ' ', $t.cost_half_roll) ELSE '-' END as cost_half_roll")
			  ->select("IF( $t.cost_roll IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_roll_type_id = PT.id, PT.name, null), ' ', $t.cost_roll) ) as cost_roll")
			  //->select("CASE WHEN $t.cost_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P3.name, ' ', $t.cost_roll) ELSE '-' END as cost_roll")
			  ->select("IF( $t.cost_roll_landed IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_roll_landed_type_id = PT.id, PT.name, null), ' ', $t.cost_roll_landed) ) as cost_roll_landed")
			  //->select("CASE WHEN $t.cost_roll_landed != '0.00' THEN GROUP_CONCAT(DISTINCT P4.name, ' ', $t.cost_roll_landed) ELSE '-' END as cost_roll_landed")
			  ->select("IF( $t.cost_roll_ex_mill IS NULL, '-', GROUP_CONCAT(DISTINCT IF($t.cost_roll_ex_mill_type_id = PT.id, PT.name, null), ' ', $t.cost_roll_ex_mill) ) as cost_roll_ex_mill")
			  //->select("CASE WHEN $t.cost_roll_ex_mill != '0.00' THEN GROUP_CONCAT(DISTINCT P5.name, ' ', $t.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill")
//			  ->select("$t.cost_roll_ex_mill_text")
			  ->select("DATE_FORMAT($t.date, '%m/%d/%Y') as cost_date", false)
			  ->join($t, "$this->t_product.id = $t.product_id", 'left outer')
			  ->from("$this->p_price_type PT");
			//->join("$this->p_price_type P1", "$t.cost_cut_type_id = P1.id");
			//->join("$this->p_price_type P2", "$t.cost_half_roll_type_id = P2.id")
			//->join("$this->p_price_type P3", "$t.cost_roll_type_id = P3.id")
			//->join("$this->p_price_type P4", "$t.cost_roll_landed_type_id = P4.id")
			//->join("$this->p_price_type P5", "$t.cost_roll_ex_mill_type_id = P5.id");
		} else {
			$this->db->select("$t.fob")
			  ->select("$t.cost_cut_type_id, $t.cost_cut")
			  ->select("$t.cost_half_roll_type_id, $t.cost_half_roll")
			  ->select("$t.cost_roll_type_id, $t.cost_roll")
			  ->select("$t.cost_roll_landed_type_id, $t.cost_roll_landed")
			  ->select("$t.cost_roll_ex_mill_type_id, $t.cost_roll_ex_mill")
//			  ->select("$t.cost_roll_ex_mill_text")
			  ->select("DATE_FORMAT($t.date, '%m/%d/%Y') as cost_date", false);
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

	/*
	function select_product_costs($id=null, $return=false){
	  $t = $this->t_product_cost;

	  if( is_null($id) ){
		$this->db
		  ->select("$t.fob")
				  ->select("CASE WHEN $t.cost_cut != '0.00' THEN GROUP_CONCAT(P1.name, ' ', $t.cost_cut) ELSE '-' END as cost_cut")
				  ->select("CASE WHEN $t.cost_half_roll != '0.00' THEN GROUP_CONCAT(P2.name, ' ', $t.cost_half_roll) ELSE '-' END as cost_half_roll")
				  ->select("CASE WHEN $t.cost_roll != '0.00' THEN GROUP_CONCAT(P3.name, ' ', $t.cost_roll) ELSE '-' END as cost_roll")
				  ->select("CASE WHEN $t.cost_roll_landed != '0.00' THEN GROUP_CONCAT(P4.name, ' ', $t.cost_roll_landed) ELSE '-' END as cost_roll_landed")
				  ->select("CASE WHEN $t.cost_roll_ex_mill != '0.00' THEN GROUP_CONCAT(P5.name, ' ', $t.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill")
			 ->select("$t.cost_roll_ex_mill_text")
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
			->select("$t.cost_roll_ex_mill_text");
		if($return){
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
	*/

	/*
	function select_product_shelfs($id=null){
		  switch($this->product_type){
			  case constant('Regular'):
				  $main_table = $this->t_product;
		break;
		case constant('Digital'):
		  $main_table = $this->product_digital;
		break;
		case constant('ScreenPrint'):
		  $main_table = $this->product_screenprint;
		break;
	  }
	  $this->db
			  ->select("GROUP_CONCAT(DISTINCT $this->p_shelf.name ORDER BY $this->p_shelf.name ASC SEPARATOR ' / ') as shelf")
			  ->select("GROUP_CONCAT(DISTINCT $this->p_shelf.id SEPARATOR ' / ') as shelf_id")
		->join($this->t_product_shelf, "$main_table.id = $this->t_product_shelf.product_id AND $this->t_product_shelf.product_type = '$this->product_type' ", 'left outer')
		->join($this->p_shelf, "$this->t_product_shelf.shelf_id = $this->p_shelf.id", 'left outer');
	}
	  */

	function select_product_uses($id = null)
	{
		$this->db
		  ->select("GROUP_CONCAT(DISTINCT $this->p_use.name ORDER BY $this->p_use.name ASC SEPARATOR ' / ') as uses")
		  ->select("GROUP_CONCAT(DISTINCT $this->p_use.id SEPARATOR ' / ') as uses_id")
		  ->join($this->t_product_use, "$this->t_product.id = $this->t_product_use.product_id", 'left outer')
		  ->join($this->p_use, "$this->t_product_use.use_id = $this->p_use.id", 'left outer');
	}

	function select_product_various($id = null, $return = false, $selectors = [])
	{
		$t = $this->t_product_various;
		if (count($selectors) > 0) {
			foreach ($selectors as $s) {
				$this->db->select("$t.$s");
			}
		} else {
			$this->db
			  ->select("$t.vendor_product_name")
			  ->select("$t.yards_per_roll")
			  ->select("$t.lead_time")
			  ->select("$t.min_order_qty")
			  ->select("$t.tariff_code")
			  ->select("$t.tariff_surcharge")
                ->select("$t.duty_perc")
                ->select("$t.freight_surcharge")
                ->select("$t.vendor_notes")
			  ->select("$t.railroaded")
			  ->select("$t.prop_65")
			  ->select("$t.ab_2998_compliant")
                ->select("$t.dyed_options")
			  ->select("$t.weight_n")
			  ->select("$t.weight_unit_id");
		}

		if (is_null($id)) {
			$this->db
			  ->join($t, "$this->t_product.id = $t.product_id", 'left outer');
		} else {
			$this->db
			  ->from($t)
			  ->where("$t.product_id", $id);
			if ($return) {
				return $this->db->get()->row_array();
			}
		}

		//array_push($this->colSearch, "$t.vendor_product_name", "$t.yards_per_roll", "$t.lead_time", "$t.min_order_qty", "$t.tariff_code");
	}

	function select_product_vendors($id = null, $return = false)
	{
		$t = $this->t_product_vendor;
		if (is_null($id)) {
			$this->db
			  //->select("CASE WHEN ($p.descr is null or $p.descr = '') THEN $p.name ELSE $p.descr END AS vendors_name")
			  ->select("V.name as vendors_name")
			  ->select("V.abrev as vendors_abrev")
			  ->join($t, "$this->t_product.id = $t.product_id", 'left outer')
			  ->join("$this->p_vendor V", "$t.vendor_id = V.id", 'left outer');
		} else {
			$this->db
			  ->select("GROUP_CONCAT(DISTINCT V.id SEPARATOR ' / ') as vendors_id")
			  //->select("CASE WHEN ($p.descr is null or $p.descr = '') THEN $p.name ELSE $p.descr END AS vendors_name")
			  ->select("V.name as vendors_name")
			  ->select("V.abrev as vendors_abrev")
			  ->join($t, "$this->t_product.id = $t.product_id", 'left outer')
			  ->join("$this->p_vendor V", "$t.vendor_id = V.id", 'left outer');
			if ($return) {
				$this->db
				  ->from($this->t_product)
				  ->where("$t.product_id", $id);
				return $this->db->get()->row_array();
			}
		}

		//array_push($this->colSearch, "$p.name", "$p.descr");
	}

	function select_product_weaves($id = null)
	{
		$this->db
		  ->select("GROUP_CONCAT(DISTINCT $this->p_weave.name ORDER BY $this->p_weave.name ASC SEPARATOR ' / ') as weaves")
		  ->select("GROUP_CONCAT(DISTINCT $this->p_weave.id SEPARATOR ' / ') as weaves_id")
		  ->join($this->t_product_weave, "$this->t_product.id = $this->t_product_weave.product_id", 'left outer')
		  ->join($this->p_weave, "$this->t_product_weave.weave_id = $this->p_weave.id", 'left outer');
	}

	function select_product_files($id = null)
	{
		$p = $this->p_category_files;
		switch ($this->product_type) {
			case Digital:
				$product_table = $this->t_digital_style;
				$prefix = 'style_id';
				$t = $this->t_digital_style_files;
				break;
			case Regular:
			default:
				$product_table = $this->t_product;
				$prefix = 'product_id';
				$t = $this->t_product_files;
				break;
		}
		if (is_null($id)) {
			$this->db->select("GROUP_CONCAT(DISTINCT $t.url_dir, '#', $t.date_add, '#', $t.user_id, '#', $p.id, '#', $p.name, '#', IFNULL($t.descr, ' ') ORDER BY $t.date_add DESC SEPARATOR '**' ) as files")
			  ->join($t, "$product_table.id = $t.$prefix", 'left outer')
			  ->join($p, "$t.category_id = $p.id", 'left outer');
		} else {
			$this->db->select("$t.url_dir, $p.name")
			  ->from($t)
			  ->join($p, "$t.category_id = $p.id", 'left outer')
			  ->where("$t.$prefix", $id);
			return $this->db->get()->result_array();
		}
	}

	function select_product_showcase()
	{
		switch ($this->product_type) {
			case Regular:
				$this->db
				  ->select("$this->t_showcase_product.url_title")
				  ->select("$this->t_showcase_product.descr as showcase_descr")
				  ->select("$this->t_showcase_product.visible as showcase_visible")
				  ->select("$this->t_showcase_product.pic_big")
				  ->select("$this->t_showcase_product.pic_big_url")
				  ->join($this->t_showcase_product, " ($this->t_product.id = $this->t_showcase_product.product_id AND $this->t_showcase_product.product_type = '$this->product_type' ) ", "left outer", false)
				  ->select("GROUP_CONCAT(DISTINCT SPC.collection_id SEPARATOR ' / ') as showcase_collection_id")
				  ->join("$this->t_showcase_product_collection SPC", "$this->t_product.id = SPC.product_id", 'left outer')
				  ->select("GROUP_CONCAT(DISTINCT SPCW.content_web_id SEPARATOR ' / ') as showcase_contents_web_id")
				  ->join("$this->t_showcase_product_contents_web SPCW", "$this->t_product.id = SPCW.product_id", 'left outer')
				  ->select("GROUP_CONCAT(DISTINCT SPP.pattern_id SEPARATOR ' / ') as showcase_pattern_id")
				  ->join("$this->t_showcase_product_patterns SPP", "$this->t_product.id = SPP.product_id", 'left outer');
				break;

			case Digital:
				$this->db
				  ->select("$this->t_showcase_style.url_title")
				  ->select("'' as showcase_descr")
				  ->select("$this->t_showcase_style.visible as showcase_visible")
				  ->select("$this->t_showcase_style.pic_big")
				  ->select("$this->t_showcase_style.pic_big_url")
				  ->join($this->t_showcase_style, "$this->t_digital_style.id = $this->t_showcase_style.style_id", "left outer")
				  ->select("'' as showcase_collection_id")
				  ->select("'' as showcase_contents_web_id")
				  ->select("GROUP_CONCAT(DISTINCT SSP.pattern_id SEPARATOR ' / ') as showcase_pattern_id")
				  ->join("$this->t_showcase_styles_patterns SSP", "$this->t_showcase_style.style_id = SSP.style_id", 'left outer');
				break;
			default:
				$this->db
				  ->select("'' as url_title")
				  ->select("'' as showcase_descr")
				  ->select("'' as showcase_visible")
				  ->select("'' as pic_big")
				  ->select("'' as pic_big_url")
				  ->select("'' as showcase_collection_id")
				  ->select("'' as showcase_content_web_id")
				  ->select("'' as showcase_pattern_id");
				break;
		}
	}
	
	function select_product_portfolio()
	{
		switch ($this->product_type) {
			case Regular:
				$this->t = $this->t_product;
				$this->db
					->select("GROUP_CONCAT(DISTINCT Pic.url SEPARATOR '**') as portfolio_urls")
					->join("V_PRODUCT_PORTFOLIO_PICTURE Pic", "$this->t.id = Pic.product_id AND Pic.product_type = 'R'", 'left outer')
				;
				break;
			
			case Digital:
				$this->t = $this->product_digital;
				$this->db
					->select("GROUP_CONCAT(DISTINCT Pic.url SEPARATOR '**') as portfolio_urls")
					->join("V_PRODUCT_PORTFOLIO_PICTURE Pic", "$this->t.id = Pic.product_id AND Pic.product_type = 'D'", 'left outer')
				;
				break;
			default:
				break;
		}
	}
	
	
	function select_product_messages($product_type, $product_id)
	{
		$this->db
		  ->select("PM.id, PM.message as message_note, PM.date_add, PM.date_modif, U.username, PM.user_id")
		  ->from("$this->t_product_messages PM")
		  ->join("$this->auth_users U", "PM.user_id = U.id", 'left outer')
		  ->where("PM.product_id", $product_id)
		  ->where("PM.product_type", $product_type)
		  ->order_by("PM.date_modif DESC");
		return $this->db->get()->result_array();
	}

	function get_product_name_by_id($product_id, $product_type=Regular){
		if($product_type == Regular){
			$this->db
			  ->select("name")
			  ->from($this->t_product)
			  ->where("id", $product_id);
			$q = $this->db->get()->row_array();
			return $q['name'];
		} else if($product_type == Digital){
			$this->db
			  ->select("name")
			  ->from($this->t_product)
			  ->where("id", $product_id);
			$q = $this->db->get()->row_array();
			return $q['name'];
		}
	}

	function get_product_name_by_item_id($item_id)
	{
		$this->db->select("CONCAT($this->t_product.name, '-', GROUP_CONCAT($this->p_color.name SEPARATOR '-') ) as name")
		  ->from($this->t_product)
		  ->join($this->t_item, "$this->t_item.product_id = $this->t_product.id")
		  ->join($this->t_item_color, "$this->t_item.id = $this->t_item_color.item_id")
		  ->join($this->p_color, "$this->t_item_color.color_id = $this->p_color.id")
		  ->where("$this->t_item.id", $item_id)
		  ->group_by("$this->t_item.id");
		$q = $this->db->get()->row_array();
		return $q['name'];
	}

	function get_product_data($product_type, $product_id)
	{
		$this->product_type = $product_type;
		$this->select_product_basics($product_id);
		$this->select_product_various();
		$this->select_product_vendors();
		return $this->db->get()->row_array();
	}

	function get_style_name($product_type, $style_id)
	{
		switch ($product_type) {
			case constant('Digital'):
				$t = $this->t_digital_style;
				break;
//       case constant('ScreenPrint'):
//         $t = $this->t_screenprint_style;
//         break;
		}

		$this->db->select("$t.name")
		  ->from($t)
		  ->where("$t.id", $style_id);
		$q = $this->db->get()->row_array();
		return $q['name'];
	}


	// Checklist/Tasks functions

	function get_product_tasks($product_id, $product_type){
		$this->db
		  ->select("task_id, task_who, task_when, task_notes")
		  ->from($this->t_product_task)
		  ->where("$this->t_product_task.product_id", $product_id)
		  ->where("$this->t_product_task.product_type", $product_type)
		;
		return $this->db->get()->result_array();
	}

	function get_product_tasks_all($product_id, $product_type){
		$this->db
			->select("T.id, T.n_order, T.name, T.descr, PT.task_who, PT.task_when, PT.task_notes")
			->from("$this->p_product_task T")
			->join("$this->t_product_task PT", "T.id = PT.task_id AND PT.product_id = $product_id AND PT.product_type = '$product_type'", 'left outer')
//			->where("PT.product_id", $product_id)
//			->where("PT.product_type", $product_type)
			->where("T.active", 'Y')
		    ->order_by("T.n_order ASC")
		;
		return $this->db->get()->result_array();
	}

	function update_task($task_data){
		$_SQL_VALUES = [];
		$_SQL_KEY_UPDATE = [];
		$_SQL_Q = [];
		foreach($task_data as $key => $val){
			$_SQL_VALUES[] = $key;
			$_SQL_KEY_UPDATE[] = $key."=VALUES(".$key.")";
			$_SQL_Q[] = '?';
		}

		$sql = "INSERT INTO $this->t_product_task (".implode(',', $_SQL_VALUES).")
        VALUES (".implode(',', $_SQL_Q).")
        ON DUPLICATE KEY UPDATE ".implode(',', $_SQL_KEY_UPDATE)."
        ;";

		$this->db->query($sql, array_values($task_data));
	}


	// Bootcomplete usage

	function search_by_name($q, $filters = array())
	{

//		$this->query = array('stack' => array());

		if (strpos(strtolower($q), 'digital') !== false) {
			// $q is a substring of 'digital'
			return [];
		}

		$this->db
		  ->select("I.*")
		  ->select("V.name as vendor_name")
		  ->from("$this->v_item I")
		  ->join("$this->t_product_vendor PV", "I.product_id = PV.product_id", "left outer")
		  ->join("$this->p_vendor V", "PV.vendor_id = V.id", "left outer")
		  ->group_start()
			  ->like("I.product_name", $q)
			  ->or_like("I.color", $q)
			  ->or_like("I.code", $q)
		  ->group_end()
		  ->order_by("I.product_type DESC, I.product_name");

		$this->db
		  ->group_start()
			->where("I.archived_product", "N")
			->where("I.archived", "N")
		  ->group_end()
		;

//		$this->db
//		  ->not_group_start()
//			  ->where("I.archived_product", "Y")
//			  ->or_where("I.archived", "Y")
//		  ->group_end()
//		;

		return $this->db->get()->result_array();
	}


//		// Regular Products
//
//		if ($this->filters['includeRegular']) {
//			$this->product_type = constant('Regular');
//			$this->pcode = constant('product') . '-' . $this->product_type;
//			$this->db
//			  ->select("CONCAT(V.name, ' - Fabric colorline') as description")
//			  ->select("CONCAT(P.id, '-', '$this->product_type') as id")
//			  ->select("P.name as label")
//			  ->from("$this->t_product P")
//			  ->join("$this->t_product_vendor PV", "P.id = PV.product_id", 'left outer')
//			  ->join("$this->p_vendor V", "PV.vendor_id = V.id", 'left outer')
//			  ->like('P.name', $q)
//			  ->limit(100);
//
//			$this->where_product_is_not_archived('P');
//
//			array_push($this->query['stack'], $this->db->get_compiled_select());
//		}
//
//		// Digital Products
//
//		if ($this->filters['includeDigital']) {
//			$this->product_type = constant('Digital');
//			$this->pcode = constant('product') . '-' . $this->product_type;
//			$this->db
//			  ->select("CONCAT('Opuzen', ' - ', 'Digital colorline') as description")
//			  ->select("CONCAT(PD.id, '-', '$this->product_type') as id")
//			  ->select("
//				CONCAT(
//				$this->t_digital_style.name, ' on ',
//				CASE WHEN PD.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END,
//				COALESCE($this->t_product.dig_product_name, $this->t_product.name),
//				' / ',
//				GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ')
//				) as label")
//			  ->from("$this->product_digital PD")
//			  ->join($this->t_digital_style, "PD.style_id = $this->t_digital_style.id")
//			  ->join($this->t_item, "$this->t_item.id = PD.item_id", 'left outer')
//			  ->join($this->t_product, "$this->t_product.id = $this->t_item.product_id")
//			  ->join($this->t_item_color, "$this->t_item_color.item_id = $this->t_item.id", 'left outer')
//			  ->join($this->p_color, "$this->p_color.id = $this->t_item_color.color_id", 'left outer')
//			  ->or_like("$this->t_digital_style.name", $q)
//			  ->or_like("$this->t_product.name", $q)
//			  ->or_like("$this->t_product.dig_product_name", $q)
//			  ->group_by("PD.id");
//
//			$this->where_product_is_not_archived('PD');
//
//			array_push($this->query['stack'], $this->db->get_compiled_select());
//		}
//
//		// Regular Items
//
//		if ($this->filters['includeSKU']) {
//			$this->product_type = constant('Regular');
//			$this->pcode = constant('item') . '-' . $this->product_type;
//			// Regular items
//			$this->db
//			  ->select("CONCAT(V.name, ' - ', 'SKU') as description")
//			  ->select("GROUP_CONCAT(DISTINCT I.id, '-', 'item_id') as id")
//			  ->select("CONCAT( IF(I.code IS NULL, CONCAT_WS(' ', V.abrev, P.name), CONCAT(P.name, ' / ', I.code) ), ' / ', GROUP_CONCAT(DISTINCT C.name ORDER BY IC.n_order SEPARATOR ' ') ) as label")
//			  ->from("$this->t_item I")
//			  ->join("$this->t_product P", "I.product_id = P.id AND P.archived = 'N' ")
//			  ->join("$this->t_item_color IC", "I.id = IC.item_id")
//			  ->join("$this->p_color C", "IC.color_id = C.id")
//			  ->join("$this->t_product_vendor PV", "I.product_id = PV.product_id", 'left outer')
//			  ->join("$this->p_vendor V", "PV.vendor_id = V.id", 'left outer')
//			  ->where("I.product_type", $this->product_type)
//			  ->where("I.archived", 'N')
//			  ->or_like('I.code', $q)
//			  //->or_like("P.name", $q)
//			  ->group_by("I.id");
//
//			//$this->where_product_is_not_archived('P');
//			$this->where_product_is_not_archived('I');
//			$this->where_item_is_not_discontinued('I');
//
//			array_push($this->query['stack'], $this->db->get_compiled_select());
//		}
//
//		$query = implode(" UNION ALL ", $this->query['stack']);
//
//		$this->db
//		  ->from("( $query ) as t")
//		  //->like('label', $q)
//		  ->order_by("label");
//
//		return $this->db->get()->result_array();
//	}




	function search_by_name2($q, $filters = array())
	{

		$this->query = array('stack' => array());

		$this->filters = array_merge(array(
		  'includeRegular' => true,
		  'includeDigital' => true,
		  'includeSKU' => true,
		  'showcase_permit' => false
		), $filters);

		// Regular Products

		if ($this->filters['includeRegular']) {
			$this->product_type = constant('Regular');
			$this->pcode = constant('product') . '-' . $this->product_type;
			$this->db
			  ->select("CONCAT(V.name, ' - Fabric colorline') as description")
			  ->select("CONCAT(P.id, '-', '$this->product_type') as id")
			  ->select("P.name as label")
			  ->from("$this->t_product P")
			  ->join("$this->t_product_vendor PV", "P.id = PV.product_id", 'left outer')
			  ->join("$this->p_vendor V", "PV.vendor_id = V.id", 'left outer')
			  ->like('P.name', $q);

			$this->where_product_is_not_archived('P');

			array_push($this->query['stack'], $this->db->get_compiled_select());
		}

		// Digital Products

		if ($this->filters['includeDigital']) {
			$this->product_type = constant('Digital');
			$this->pcode = constant('product') . '-' . $this->product_type;
			$this->db
			  ->select("CONCAT('Opuzen', ' - ', 'Digital colorline') as description")
			  ->select("CONCAT(PD.id, '-', '$this->product_type') as id")
			  ->select("
				CONCAT(
				$this->t_digital_style.name, ' on ', 
				CASE WHEN PD.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, 
				COALESCE($this->t_product.dig_product_name, $this->t_product.name), 
				' / ', 
				GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ') 
				) as label")
			  ->from("$this->product_digital PD")
			  ->join($this->t_digital_style, "PD.style_id = $this->t_digital_style.id")
			  ->join($this->t_item, "$this->t_item.id = PD.item_id", 'left outer')
			  ->join($this->t_product, "$this->t_product.id = $this->t_item.product_id")
			  ->join($this->t_item_color, "$this->t_item_color.item_id = $this->t_item.id", 'left outer')
			  ->join($this->p_color, "$this->p_color.id = $this->t_item_color.color_id", 'left outer')
			  ->or_like("$this->t_digital_style.name", $q)
			  ->or_like("$this->t_product.name", $q)
			  ->or_like("$this->t_product.dig_product_name", $q)
			  ->group_by("PD.id");

			$this->where_product_is_not_archived('PD');

			array_push($this->query['stack'], $this->db->get_compiled_select());
		}

		// Screenprint Products

// 		if( $this->filters['includeDigital'] ){
// 			$this->product_type = constant('ScreenPrint');
// 			$this->pcode = constant('product') . '-' . $this->product_type;
// 			$this->db
// 				->select("CONCAT('') as description")
// 				->select("CONCAT(PS.id, '-', '$this->product_type') as id")
// 				->select("CONCAT($this->t_screenprint_style.name, ' on ', CASE WHEN PS.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, $this->t_product.name, ' / ', GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ') ) as label")
// 				->from("$this->product_screenprint PS")
// 				->join($this->t_screenprint_style, "PS.style_id = $this->t_screenprint_style.id")
// 				->join($this->t_item, "$this->t_item.id = PS.item_id", 'left outer')
// 				->join($this->t_product, "$this->t_product.id = $this->t_item.product_id")
// 				->join($this->t_item_color, "$this->t_item_color.item_id = $this->t_item.id", 'left outer')
// 				->join($this->p_color, "$this->p_color.id = $this->t_item_color.color_id", 'left outer')

// 				->or_like("$this->t_screenprint_style.name", $q)
// 				->or_like("$this->t_product.name", $q)
// 				->group_by("PS.id");

// 			$this->where_product_is_not_archived("PS");

// 			array_push( $this->query['stack'], $this->db->get_compiled_select() );
// 		}

		// Regular Items

		if ($this->filters['includeSKU']) {
			$this->product_type = constant('Regular');
			$this->pcode = constant('item') . '-' . $this->product_type;
			// Regular items
			$this->db
			  ->select("CONCAT(V.name, ' - ', 'SKU') as description")
			  ->select("GROUP_CONCAT(DISTINCT I.id, '-', 'item_id') as id")
			  ->select("CONCAT( IF(I.code IS NULL, CONCAT_WS(' ', V.abrev, P.name), CONCAT(P.name, ' / ', I.code) ), ' / ', GROUP_CONCAT(DISTINCT C.name ORDER BY IC.n_order SEPARATOR ' ') ) as label")
			  ->from("$this->t_item I")
			  ->join("$this->t_product P", "I.product_id = P.id AND P.archived = 'N' ")
			  ->join("$this->t_item_color IC", "I.id = IC.item_id")
			  ->join("$this->p_color C", "IC.color_id = C.id")
			  ->join("$this->t_product_vendor PV", "I.product_id = PV.product_id", 'left outer')
			  ->join("$this->p_vendor V", "PV.vendor_id = V.id", 'left outer')
			  ->where("I.product_type", $this->product_type)
			  ->where("I.archived", 'N')
			  ->or_like('I.code', $q)
			  //->or_like("P.name", $q)
			  ->group_by("I.id");

			//$this->where_product_is_not_archived('P');
			$this->where_product_is_not_archived('I');
			$this->where_item_is_not_discontinued('I');

			array_push($this->query['stack'], $this->db->get_compiled_select());
		}

		$query = implode(" UNION ALL ", $this->query['stack']);

		$this->db
		  ->from("( $query ) as t")
		  //->like('label', $q)
		  ->order_by("label");

		return $this->db->get()->result_array();

// 		$query = $this->db->get();

// 		return $this->_return_if_non_empty($query);
// 		$data = array();
// 		if($query !== FALSE && $query->num_rows() > 0){
// 			$data = $query->result_array();
// 		}
// 		return $data;
	}

	// Specsheet get data

	function is_valid_product_id($type, $id)
	{
		switch ($type) {
			case constant('Regular'):
				$table = $this->t_product;
				break;
			case constant('Digital'):
				$table = $this->product_digital;
				break;
// 			case constant('ScreenPrint'):
// 				$table = $this->product_screenprint;
// 				break;
		}
		$this->db->select('*')
		  ->from($table)
		  ->where('id', $id);
		$q = $this->db->get();
		return ($q->num_rows() > 0);
	}

	function get_products_items_for_specsheet($type, $product_id, $given_filters = array())
	{
		$default = array(
		  'get_all' => false
		);
		$filters = array_merge($default, $given_filters);

		if ($type === Regular) {
			$this->db
			  ->select("$this->t_item.id, $this->t_item.code, GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ') as color")
			  ->select("$this->t_showcase_item.pic_big")
			  ->select("$this->t_showcase_item.pic_big_url")
			  ->select("$this->t_showcase_item.pic_hd")
			  ->select("$this->t_showcase_item.pic_hd_url")
			  ->select(
				"$this->p_product_status.name as status, 
				 $this->p_product_status.descr as status_abrev, 
				 $this->p_product_status.web_vis as web_vis, 
				 $this->p_product_status.id as status_id"
			  )
			  ->from($this->t_item)
			  ->join($this->t_item_color, "$this->t_item.id = $this->t_item_color.item_id", 'left outer')
			  ->join($this->p_product_status, "$this->p_product_status.id = $this->t_item.status_id", "left outer")
			  ->join($this->p_color, "$this->t_item_color.color_id = $this->p_color.id", 'left outer')
			  ->join($this->t_showcase_item, "$this->t_item.id = $this->t_showcase_item.item_id", 'left outer')
			  ->join("$this->t_product_stock S", "$this->t_item.id = S.master_item_id", 'left outer')
			  ->where("$this->t_item.product_type", $type)
			  ->where("$this->t_item.product_id", $product_id)
              ->where("$this->t_item.archived", "N")
			  ->group_by("$this->t_item.id");
		} else if ($type === Digital) {
			$this->db
			  ->select("$this->t_showcase_style_items.id as style_item_id")
			  ->select("$this->t_item.id, $this->t_item.code, GROUP_CONCAT(DISTINCT $this->p_color.name SEPARATOR ' / ') as color")
			  ->select("$this->t_showcase_style_items.pic_big")
			  ->select("$this->t_showcase_style_items.pic_big_url")
			  // ->select("$this->t_showcase_item.pic_hd")
			  // ->select("$this->t_showcase_item.pic_hd_url")
			  ->select(
				"$this->p_product_status.name as status, 
				 $this->p_product_status.descr as status_abrev, 
				 $this->p_product_status.web_vis as web_vis, 
				 $this->p_product_status.id as status_id"
			  )
              // ->select("'' as pic_hd")
              // ->select("$this->t_showcase_item.pic_hd_url")
			  ->from($this->t_item)
			  ->join($this->product_digital, "$this->t_item.product_id = $this->product_digital.id")
			  ->join($this->p_product_status, "$this->p_product_status.id = $this->t_item.status_id", "left outer")
			  ->join($this->t_showcase_style_items, "$this->product_digital.style_id = $this->t_showcase_style_items.style_id")
			  ->join($this->t_showcase_style_items_color, "$this->t_showcase_style_items.id = $this->t_showcase_style_items_color.item_id", 'left outer')
			  ->join($this->p_color, "$this->t_showcase_style_items_color.color_id = $this->p_color.id", 'left outer')
			  ->where("$this->t_item.product_type", $type)
			  ->where("$this->t_item.product_id", $product_id)
			  ->where("$this->t_showcase_style_items.archived", "N")
			  ->where_not_in("$this->t_item.status_id", $this->product_status_to_dont_print_in_specsheet)
			  ->group_by("$this->t_showcase_style_items.id");
		}

		if ((!$filters['get_all']) AND ($type === Regular)) {
			$this->db
			->group_start()
				->where_not_in("$this->t_item.status_id", $this->product_status_to_dont_print_in_specsheet)
				->or_where("S.yardsAvailable >=", 10)
			->group_end();
		}

		if (isset($filters['limit'])) {
			$this->db->limit($filters['limit']);
		}

//        var_dump($this->db->get_compiled_select());
		$q = $this->db->get();
        return $q->result_array();
	}

	function get_product_id($item_id)
	{
		$this->db
		  ->select("product_id, product_type")
		  ->from($this->t_item)
		  ->where("id", $item_id);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
			return $q->row_array();
		} else {
			return null;
		}
	}

	function search_for_id($str, $category)
	{
		switch ($category) {
			case Regular:
				$this->db
				  ->select("A.product_id as id")
				  ->from("$this->t_showcase_product A")
				  ->join("$this->t_product P", "A.product_id = P.id AND A.product_type = '" . Regular . "'")
				  ->where('A.url_title', $str)
				  ->where('P.archived', 'N');
				break;
 			case Digital:
// 				$t = "$this->t_showcase_style";
// 				$c_id = "style_id";
// 				break;
				return null;
		}

		$q = $this->db->get();

		if ($q->num_rows() > 0) {
			$row = $q->row();
			return $row->id;
		} else {
			return null;
		}
	}

	/*********************************************************************************
	 *
	 * Product creation
	 *Editing
	 *Saving
	 *Deleting
	 **********************************************************************************/

	function valid_name($name)
	{
		$this->db->select('id')
		  ->from($this->t_product)
		  ->like('name', $name);
		$q = $this->db->get()->result_array();
		return (count($q) == 0);
	}

	function valid_combination($product_type, $style_id, $ground_item_id, $reverse_ground)
	{
		switch ($product_type) {
			case constant('Digital'):
				$map = $this->product_digital;
				break;

//       case constant('ScreenPrint'):
//         $map = $this->product_screenprint;
//         break;
		}
		$this->db->select('id')
		  ->from($map)
		  ->where('style_id', $style_id)
		  ->where('item_id', $ground_item_id)
		  ->where('reverse_ground', $reverse_ground)
		  ->where('archived', 'N');
		$q = $this->db->get()->result_array();
		return (count($q) === 0);
	}

	function save_product($product_type, $data, $id = null)
	{
		switch ($product_type) {
			case constant('Regular'):
				$t = $this->t_product;
				break;

			case constant('Digital'):
				$t = $this->product_digital;
				break;

//       case constant('ScreenPrint'):
//         $t = $this->product_screenprint;
//         break;
		}

		if ($id == null) {
			// New
			$this->db
			  ->set($data)
			  ->set('date_add', 'NOW()', false)
			  ->insert($t);
			return $this->db->insert_id();
		} else {
			// Edit
			$this->log_product($product_type, $id);
			$this->db
			  ->set($data)
			  ->set('log_vers_id', 'log_vers_id+1', FALSE)
			  ->where('id', $id)
			  ->update($t);
		}
	}

	function save_cleaning($data, $product_id)
	{
		$t = $this->t_product_cleaning;
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_cleaning_special($data, $product_id)
	{
		$t = $this->t_product_cleaning_specials;
		$this->clean_logics($product_id, $t);
		$this->db->insert($t, $data);
	}

	function save_cleaning_instructions($data, $product_id)
	{
		$t = $this->t_product_cleaning_instructions;
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}
	
	function save_warranty($data, $product_id)
	{
		$t = $this->t_product_warranty;
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_content_front($data, $product_id)
	{
		$t = $this->t_product_content_front;
		$this->log_product_content_front($product_id);
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_content_back($data, $product_id)
	{
		$t = $this->t_product_content_back;
		$this->log_product_content_back($product_id);
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_finish($data, $product_id)
	{
		$t = $this->t_product_finish;
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_finish_special($data, $product_id)
	{
		$t = $this->t_product_finish_specials;
		$this->clean_logics($product_id, $t);
		$this->db->insert($t, $data);
	}

	function save_origin($data, $product_id)
	{
		$t = $this->t_product_origin;
		$this->clean_logics($product_id, $t);
		$this->db->insert($t, $data);
	}

	function save_cost($data, $product_id)
	{
		$t = $this->t_product_cost;
		$this->log_product_cost($product_id);
		$this->clean_logics($product_id, $t);
		$this->db->insert($t, $data);
	}

	function save_price($data, $product_id, $product_type)
	{
		$t = $this->t_product_price;
		$this->log_product_price($product_type, $product_id);
		// Clean logic
		$this->db
		  ->where('product_id', $product_id)
		  ->where('product_type', $product_type)
		  ->delete($t);
		$this->db->insert($t, $data);
	}

	/*
	function save_shelf($data, $product_id){
	  $t = $this->t_product_shelf;
	  $this->clean_logics($product_id, $t);
	  ( count($data) > 0 ? $this->db->insert_batch($t, $data) : '' );
	}
	*/

	function save_uses($data, $product_id)
	{
		$t = $this->t_product_use;
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_various($data, $product_id)
	{
		$t = $this->t_product_various;
		$this->log_product_various($product_id);
		$this->clean_logics($product_id, $t);
		$this->db->insert($t, $data);
	}

	function save_vendor($data, $product_id)
	{
		$t = $this->t_product_vendor;
		$this->log_product_vendor($product_id);
		$this->clean_logics($product_id, $t);
		$this->db->insert($t, $data);
		
		// Refresh cache for this product since vendor data affects search
		$product_type = 'R'; // Default to Regular, you may need to determine this dynamically
		$this->refresh_cached_product_row($product_id, $product_type);
	}

	function save_weave($data, $product_id)
	{
		$t = $this->t_product_weave;
		$this->log_product_weave($product_id);
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_abrasion($data)
	{
		$t = $this->t_product_abrasion;
		$this->db->insert($t, $data);
	}

	function save_abrasion_files($data)
	{
		$t = $this->t_product_abrasion_files;
		$this->db->insert_batch($t, $data);
	}

	function save_message($data, $id = null)
	{
		$t = $this->t_product_messages;
		if (is_null($id)) {
			$this->db
			  ->set('date_add', 'NOW()', false)
			  ->set($data)
			  ->insert($t);
		} else {
			$this->db
			  ->set($data)
			  ->where('id', $id)
			  ->update($t);
		}
	}

	function clean_abrasion_logic($product_id)
	{
		$this->clean_logics($product_id, $this->t_product_abrasion);
	}

	function clean_abrasion_files_logic($product_id)
	{
		$e = $this->t_product_abrasion;
		$t = $this->t_product_abrasion_files;
		$sql = "DELETE
            FROM $t
            WHERE abrasion_id IN (
              SELECT id
              FROM $e
              WHERE product_id = ?
            );";
		$bind = array($product_id);
		$this->db->query($sql, $bind);
	}

	function save_firecode($data)
	{
		$t = $this->t_product_firecode;
		$this->db->insert($t, $data);
	}

	function save_firecode_files($data)
	{
		$t = $this->t_product_firecode_files;
		$this->db->insert_batch($t, $data);
	}

	function clean_firecodes_logic($product_id)
	{
		$this->clean_logics($product_id, $this->t_product_firecode);
	}

	function clean_firecodes_files_logic($product_id)
	{
		$e = $this->t_product_firecode;
		$t = $this->t_product_firecode_files;
		$sql = "DELETE
            FROM $t
            WHERE firecode_id IN (
              SELECT id
              FROM $e
              WHERE product_id = ?
            );";
		$bind = array($product_id);
		$this->db->query($sql, $bind);
	}

	function save_product_files($data, $product_id)
	{
		$t = $this->t_product_files;
		$this->db->insert_batch($t, $data);
	}

	function clean_product_files_logic($product_id)
	{
		$sql = "DELETE
            FROM $this->t_product_files
            WHERE product_id = ?;";
		$bind = array($product_id);
		$this->db->query($sql, $bind);
	}

	function save_showcase_basic($data, $product_id, $product_type)
	{
		//echo '<pre> ->save_showcase_basic(data) <br /> line: ' . __LINE__. 'file: ' . __FILE__  . '</pre>';
		//print_r($data);
		//echo '</pre>';
		//die();
		$q = $this->db
		  ->where('product_id', $product_id)
		  ->where('product_type', $product_type)
		  ->get($this->t_showcase_product);

		if ($q->num_rows() > 0) {
			$this->db
			  ->set($data)
			  ->where('product_id', $product_id)
			  ->where('product_type', $product_type)
			  ->update($this->t_showcase_product);
		} else {
			$this->db
			  ->set($data)
			  ->set('product_id', $product_id)
			  ->set('product_type', $product_type)
			  ->set('date_add', date('Y-m-d H:i:s'))
			  ->insert($this->t_showcase_product);
		}
	}

	function save_showcase_collection($data, $product_id)
	{
		//echo '<pre> ->save_showcase_collection(data) <br /> line: ' . __LINE__. 'file: ' . __FILE__  . '</pre>';
		//print_r($data);
		//echo '</pre>';
		//die();
		$t = $this->t_showcase_product_collection;
		//$this->log_product_weave($product_id);
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_showcase_contents_web($data, $product_id)
	{
		//echo '<pre> ->save_showcase_contents_web(data) <br /> line: ' . __LINE__. 'file: ' . __FILE__  . '</pre>';
		//print_r($data);
		//echo '</pre>';
		//die();
		$t = $this->t_showcase_product_contents_web;
		//$this->log_product_weave($product_id);
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}

	function save_showcase_patterns($data, $product_id)
	{
		//echo '<pre> ->save_showcase_patterns(data) <br /> line: ' . __LINE__. 'file: ' . __FILE__  . '</pre>';
		//print_r($data);
		//echo '</pre>';
		//die();
		$t = $this->t_showcase_product_patterns;
		//$this->log_product_weave($product_id);
		$this->clean_logics($product_id, $t);
		(count($data) > 0 ? $this->db->insert_batch($t, $data) : '');
	}


	function get_mapping_style_id($type, $product_id)
	{
		switch ($type) {
			case constant('Digital'):
				$t = $this->product_digital;
				$tstyles = $this->t_digital_style;
				break;
//       case constant('ScreenPrint'):
//         $t = $this->product_screenprint;
// 				$tstyles = $this->t_screenprint_style;
//         break;
		}
		$this->db->select("$tstyles.id, $tstyles.name")
		  ->from($t)
		  ->join($tstyles, "$t.style_id = $tstyles.id")
		  ->where("$t.id", $product_id);
		$q = $this->db->get()->row_array();
		return $q;
	}

	function get_mapping_item_id($type, $product_id)
	{
		switch ($type) {
			case constant('Digital'):
				$t = $this->product_digital;
				break;
//       case constant('ScreenPrint'):
//         $t = $this->product_screenprint;
//         break;
		}
		$this->db->select("I.id, CONCAT(P.name, ' / ', GROUP_CONCAT(C.name ORDER BY IC.n_order SEPARATOR ' / ') ) as name")
		  ->from($t)
		  ->join("$this->t_item I", "$t.item_id = I.id")
		  ->join("$this->t_product P", "I.product_id = P.id")
		  ->join("$this->t_item_color IC", "I.id = IC.item_id")
		  ->join("$this->p_color C", "IC.color_id = C.id")
		  ->where("$t.id", $product_id)
		  ->group_by("I.id");
		$q = $this->db->get()->row_array();
		return $q;
	}

	function get_related_products($product_id)
	{
		// Returns all products that are a combined of the $product_id parameter
		$this->db->distinct()
		  ->select("$this->product_digital.id")
		  ->from("$this->t_item, $this->product_digital")
		  ->where("$this->product_digital.item_id IN ( SELECT id FROM $this->t_item WHERE $this->t_item.product_id = $product_id )");
		return $this->db->get()->result_array();

//     $this->db->distinct()
//       ->select("$this->product_screenprint.id")
//       ->from("$this->t_item, $this->product_screenprint")
//       ->where("$this->product_screenprint.item_id IN ( SELECT id FROM $this->t_item WHERE $this->t_item.product_id = $product_id )");
//     $screenprints = $this->db->get()->result_array();

//     return array_merge($digitals, $screenprints);
	}


	function archive_product($product_id, $product_type)
	{
		switch ($product_type) {
			case constant('Regular'):
				$t = $this->t_product;
				break;
			case constant('Digital'):
				$t = $this->product_digital;
				break;
//       case constant('ScreenPrint'):
//         $t = $this->product_screenprint;
//         break;
		}
		$this->db
		  ->set('archived', 'Y')
		  ->where('id', $product_id)
		  ->update($t);
	}

	function retrieve_product($product_id, $product_type)
	{
		switch ($product_type) {
			case constant('Regular'):
				$t = $this->t_product;
				break;
			case constant('Digital'):
				$t = $this->product_digital;
				break;
//       case constant('ScreenPrint'):
//         $t = $this->product_screenprint;
//         break;
		}
		$this->db
		  ->set('archived', 'N')
		  ->where('id', $product_id)
		  ->update($t);
	}

    // Returns the web visibility of an item based on the status
    // Returns a boolean value
    // eg.  $is_web_visible = get_item_web_visiblity($item_id = 4653);
    // eg.  $is_web_visible = get_item_web_visiblity($item_code = '4400-1235');
    //
    function get_item_web_visiblity($item_id = false) {
    	if ($item_id === false) {
    		return null; 
    	}
        $this->db
          ->select("status.web_vis as prod_status_website_visibility")
          ->from("$this->p_product_status status")
          ->join("$this->t_item item", "status.id = item.status_id")
          ->where('item.id', $item_id);
        $q = $this->db->get();
        // Check for SQL errors
        if ($q === false) {
            // Log the error or display it
            $error = $this->db->error(); // Get the error message
            log_message('error', 'Database error: ' . $error['message']); // Adjust to your logging method
    		//$qrystr =  $this->db->get_compiled_select();
    		//echo "<pre>get_item_web_visiblity(id)  ";
            //print_r( $error['message']);
    		//print_r( $error);
            //echo "</pre>";
    		exit;
            return null;
        }
        if ($q->num_rows() > 0) {
            $row = $q->row();
            if(isset($row->prod_status_website_visibility)){
                return $row->prod_status_website_visibility;
            }
            return null;
        } else {
            return null;
        }
    }




}

?>