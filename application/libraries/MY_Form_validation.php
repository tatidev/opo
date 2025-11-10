<?php

class MY_Form_validation extends CI_Form_validation {

	public function __construct($config = array()) 
	{
  	parent::__construct($config);
    // Your own constructor code
		
	}
	
	public function is_unique($str, $field)
	{
		if( substr_count($field, '.') === 2 ){
			// Full directory is given
			sscanf($field, '%[^.].%[^.].%[^.]', $db, $table, $field);
			return isset($this->CI->db)
				? ($this->CI->db->limit(1)->get_where($db.'.'.$table, array($field => $str))->num_rows() === 0)
				: FALSE;
		} else {
			sscanf($field, '%[^.].%[^.]', $table, $field);
			return isset($this->CI->db)
				? ($this->CI->db->limit(1)->get_where($table, array($field => $str))->num_rows() === 0)
				: FALSE;
		}
	}

}

?>