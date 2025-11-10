<?php
class Contact_model extends MY_Model {
	
	public $rules = array(
						array(
										'field' => 'c_name',
										'label' => 'Name',
										'rules' => 'required'
						),
						array(
										'field' => 'c_email_1',
										'label' => 'Email 1',
										'rules' => 'valid_email'
						),
						array(
										'field' => 'c_email_2',
										'label' => 'Email 2',
										'rules' => 'valid_email'
						),
	);
            
  function __construct(){
    parent::__construct();
  }
	
	function select_contact_basics(){
		$this->db
			->select("C.id")
			->select("C.name as c_name")
			->select("C.company as c_company")
			->select("C.position as c_position")
			->select("C.address_1 as c_address_1")
			->select("C.address_2 as c_address_2")
			->select("C.city as c_city")
			->select("C.state as c_state")
			->select("C.zipcode as c_zipcode")
			->select("C.country as c_country")
			->select("C.tel_1 as c_tel_1")
			->select("C.tel_2 as c_tel_2")
			->select("C.email_1 as c_email_1")
			->select("C.email_2 as c_email_2")
			->select("C.date_add")
			->select("C.date_modif")
			->select("U.username")
			->from("$this->p_contact C")
			->join("$this->auth_users U", "C.user_id = U.id");
	}
	
	function get_contacts($filters, $isDatatable=true){
		$this->select_contact_basics();
		
		if( isset($filters['showroom_only']) ){
			$this->model_table = $this->p_showroom_contact;
			$this->db
				->select("V.id as c_owner_id")
				->select("V.name as c_owner_name")
				->select("V.abrev as c_owner_abrev")
				->from("$this->p_showroom_contact SC")
				->where("C.id = SC.contact_id")
				->join("$this->p_showroom V", "SC.showroom_id = V.id")
				;
		}
		if( isset($filters['vendor_only']) ){
			$this->model_table = $this->p_vendor_contact;
			$this->db
				->select("V.id as c_owner_id")
				->select("V.name as c_owner_name")
				->select("V.abrev as c_owner_abrev")
				->from("$this->p_vendor_contact SC")
				->where("C.id = SC.contact_id")
				->join("$this->p_vendor V", "SC.vendor_id = V.id")
				;
		}
		
		if( isset($this->filters['vid']) && !is_null($this->filters['vid']) ){
			$this->db->where('V.id', $filters['vid']);
		}
		
		if( isset($filters['show_archived']) && $filters['show_archived'] ){
			// No filter necessary
		} else {
			$this->where_contact_not_archived('C');
		}
		
		if( isset($filters['contact_id']) ){
			$this->db->where('C.id', $filters['contact_id']);
		}
		
		if( $isDatatable ){
			$this->set_datatables_variables();
			$q = $this->db->get_compiled_select();
			return $this->apply_datatables_processing($q);
		} else {
			return $this->db->get()->row_array();
		}
		
	}
	
	function save_contact($data, $id=null){
		if( is_null($id) ){
			// New
			$this->db->insert($this->p_contact, $data);
			return $this->db->insert_id();
		} else {
			// Edit existing contact
      $this->db
        ->set($data)
        //->set('log_vers_id', 'log_vers_id+1', FALSE)
        ->where('id', $id)
        ->update($this->p_contact);
		}
	}
	
	function save_contact_relation($ctype, $cid, $owner_id, $old_owner_id){
		
		switch( $ctype ){
			case constant('showroom'):
				// Delete old relationship
				$this->db
					->where('showroom_id', $old_owner_id)
					->where('contact_id', $cid)
					->delete($this->p_showroom_contact)
					;
				// Insert new one (maybe the owner_id changed)
				$this->db
					->set( array(
						'showroom_id' => $owner_id,
						'contact_id' => $cid
					) )
					->insert($this->p_showroom_contact)
					;
				
				break;
				
			case constant('vendor'):
				// Delete old relationship
				$this->db
					->where('vendor_id', $old_owner_id)
					->where('contact_id', $cid)
					->delete($this->p_vendor_contact)
					;
				// Insert new one (maybe the owner_id changed)
				$this->db
					->set( array(
						'vendor_id' => $owner_id,
						'contact_id' => $cid
					) )
					->insert($this->p_vendor_contact)
					;
				
				break;
		}
	}
	
	function get_name($ctype, $id){
		switch( $ctype ){
			case constant('showroom'):
				$this->db
					->select("name, abrev")
					->from($this->p_showroom)
					->where("id", $id)
					;
				break;
				
			case constant('vendor'):
				$this->db
					->select("name, abrev")
					->from($this->p_vendor)
					->where("id", $id)
					;
				break;
		}
		return $this->db->get()->row_array();
	}
	
	function where_contact_not_archived($tableName){
		$this->db->where("$tableName.archived", 'N');
	}
	
}

?>