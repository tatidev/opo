<?php
class Vendor_model extends MY_Model {
	
	public $rules = array(
						array(
										'field' => 'vendors_name',
										'label' => 'Name',
										'rules' => 'required'
						)
	);
            
  function __construct(){
    parent::__construct();
  }
	
	function set_tables( $type ){
    switch( $type ){
      case constant('vendor'):
				$this->t_primary = $this->p_vendor;
				$this->t_files = $this->p_vendor_files;
				$this->t_contact = $this->p_vendor_contact;
        break;
          
      case constant('showroom'):
				$this->t_primary = $this->p_showroom;
				$this->t_files = $this->p_showroom_files;
				$this->t_contact = $this->p_showroom_contact;
        break;
    }
	}
  
  function get_vendors( $filters=array() ){
		
		$defaults = array(
			'ctype' => null,
			'show_discontinued' => true,
			'show_archived' => false,
			'vendor_id' => null,
			'datatable' => false
		);
		$this->filters = array_merge($defaults, $filters);
		$this->ctype = $this->filters['ctype'];
		
    $this->db
      ->select("V.id, V.abrev as vendors_abrev, V.name as vendors_name, V.date_modif, V.active, V.archived")
			->select("COUNT(DISTINCT VF.url_dir) as count_files")
			->select("GROUP_CONCAT( DISTINCT VF.url_dir, '**', VF.descr, '**', VF.date_add, '**', VF.user_id ORDER BY VF.date_add DESC SEPARATOR '/**/' ) as files")
      ->select("COUNT(DISTINCT VC.contact_id) as count_contacts")
			->group_by("V.id")
      ;
    
    switch( $this->filters['ctype'] ){
      case constant('vendor'):
        $this->db
          ->select("COUNT(DISTINCT PV.product_id) as count_assoc")
          ->from("$this->p_vendor V")
          ->join("$this->p_vendor_files VF", "V.id = VF.vendor_id", 'left outer')
          ->join("$this->p_vendor_contact VC", "V.id = VC.vendor_id", 'left outer')
          ->join("$this->t_product_vendor PV", "V.id = PV.vendor_id", 'left outer')
          ;
				$this->model_table = $this->p_vendor;
        break;
          
      case constant('showroom'):
        $this->db
          ->select("COUNT(DISTINCT LS.list_id) as count_assoc")
          ->from("$this->p_showroom V")
          ->join("$this->p_showroom_files VF", "V.id = VF.showroom_id", 'left outer')
          ->join("$this->p_showroom_contact VC", "V.id = VC.showroom_id", 'left outer')
          ->join("$this->p_list_showrooms LS", "V.id = LS.showroom_id", 'left outer')
          ;
				$this->model_table = $this->p_showroom;
        break;
    }
    
    if( !is_null($this->filters['show_archived']) && $this->filters['show_archived'] ){
      $this->db->where('V.archived', 'Y');
    } else {
      $this->db->where('V.archived', 'N');
    }
		
		if( !is_null($this->filters['vendor_id']) ){
			$this->db->where('V.id', $this->filters['vendor_id']);
		}
    
		if( $this->filters['datatable'] ){
			$this->set_datatables_variables();
			$q = $this->db->get_compiled_select();
			return $this->apply_datatables_processing($q);
		} else {
			return $this->db->get()->row_array();
		}
  }
  
	function save($ctype, $data, $id=null){
		switch($ctype){
			case constant('showroom'):
				$t = $this->p_showroom;
				break;
				
			case constant('vendor'):
				$t = $this->p_vendor;
				break;
		}
		if( is_null($id) ){
			// New
			$this->db
				->set('date_add', 'NOW()', false)
				->insert($t, $data);
			return $this->db->insert_id();
		} else {
			// Edit existing contact
      $this->db
        ->set($data)
        //->set('log_vers_id', 'log_vers_id+1', FALSE)
        ->where('id', $id)
        ->update($t);
		}
	}
	
  function archive_vendor($ctype, $id){
		$this->set_tables($ctype);
    $this->db
      ->set('archived', 'Y')
      ->where('id', $id)
      ->update($this->t_primary);
  }
  
  function retrieve_vendor($ctype, $id){
		$this->set_tables($ctype);
    $this->db
      ->set('archived', 'N')
      ->where('id', $id)
      ->update($this->t_primary);
    return $this->get_vendors( array( 'ctype'=>$ctype ,'vendor_id'=>$id ), false );
  }
	
  function save_vendor_files($data, $ctype, $vid){
		switch($ctype){
			case constant('vendor'):
				$field = 'vendor_id';
				$t = $this->p_vendor_files;
				break;
			case constant('showroom'):
				$field = 'showroom_id';
				$t = $this->p_showroom_files;
				break;
		}
    $this->db->insert_batch($t, $data);
  }
	
  function clean_vendor_files_logic($ctype, $vid){
		
		switch( $ctype ){
			case constant('showroom'):
				$sql = "DELETE
								FROM $this->p_showroom_files
								WHERE showroom_id = ?;";
				break;
			case constant('vendor'):
				$sql = "DELETE
								FROM $this->p_vendor_files
								WHERE vendor_id = ?;";
				break;
		}
		
    $bind = array($vid);
    $this->db->query($sql, $bind);
  }
	
	function search_by_name($ctype, $q){
		switch($ctype){
			case constant('showroom'):
				$t = $this->p_showroom;
				break;
				
			case constant('vendor'):
				$t = $this->p_vendor;
				break;
		}
    $this->db
      ->select("id as id")
      ->select("name as label")
      ->from($t)
      ->like('name', $q)
      ->or_like('abrev', $q)
			->order_by("label");
    return $this->db->get()->result_array();
	}
	
}

?>


