<?php

class Portfolio_model extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_project_count(){
		return $this->db->count_all_results($this->t_portfolio_project);
	}
	
	function get_picture_count(){
		return $this->db->count_all_results($this->t_portfolio_picture);
	}
	
	function _select_projects(){
		$Projects = $this->t_portfolio_project;
		$Pics = $this->t_portfolio_picture;
		
		$this->db
			->select("Proj.id, Proj.name, Proj.notes, Proj.active")
			->select("Proj.date_add, Proj.user_id, Proj.date_modif, Proj.user_id_modif")
			->select("GROUP_CONCAT(DISTINCT Pics.id) as picture_ids")
			->from("$Projects Proj")
			->join("$Pics Pics", "Proj.id = Pics.project_id", 'left outer')
		;
	}
	
	function _select_pictures(){
		$Pics = $this->t_portfolio_picture;
		$PicProducts = $this->t_portfolio_product;
		$items = $this->v_item;
		$separator = delimiter;
		$this->db
			->select("Pics.id, Pics.url, Pics.notes, Pics.active")
			->select("Pics.date_add, Pics.user_id, Pics.date_modif, Pics.user_id_modif")
			->select("GROUP_CONCAT(DISTINCT PicProducts.item_id SEPARATOR '$separator') as products_assoc_id")
			->select("GROUP_CONCAT(DISTINCT CONCAT(I.product_name,'-',I.color) SEPARATOR '$separator') as products_assoc")
			->from("$Pics Pics")
			->join("$PicProducts PicProducts", "Pics.id = PicProducts.picture_id", "left outer")
			->join("$items I", "PicProducts.item_id = I.item_id", "left outer")
		;
	}
	
	function get_all_projects($page, $size_per_page, $search=''){
//		$this->_select_projects();
		$Projects = $this->t_portfolio_project;
		$this->db
			->select("Proj.id")
			->from("$Projects Proj")
			->group_by("Proj.id")
			->order_by("Proj.date_add", "desc")
			->limit($size_per_page, $page*$size_per_page)
		;
		if(strlen($search) > 0){
			$this->db
				->join("V_PORTFOLIO_PICTURE VPic", "Proj.id = VPic.project_id", "left outer")
				->like("Proj.name", $search)
				->or_like("Proj.notes", $search)
				->or_like("VPic.notes", $search)
				->or_like("VPic.products", $search)
			;
		}
		return $this->db->get()->result_array();
	}
	
	function get_projects($id){
		$this->_select_projects();
		$this->db
			->group_by("Proj.id")
			->order_by("Proj.date_modif", "desc")
		;
		if(is_array($id)){
			$this->db->where_in("Proj.id", $id);
		}
		else {
			$this->db->where("Proj.id", $id);
		}
		return $this->db->get()->result_array();
	}

	function get_pictures_from_project($id){
		$this->_select_pictures();
		$this->db
			->where("Pics.project_id", $id)
			->group_by("Pics.id")
			->order_by("Pics.date_modif", "desc")
			;
		return $this->db->get()->result_array();
	}
	
	function get_pictures($id){
		$this->_select_pictures();
		$this->db
			->group_by("Pics.id")
			->order_by("Pics.date_modif", "desc")
		;
		if(is_array($id)){
			$this->db->where_in("Pics.id", $id);
		} else {
			$this->db->where("Pics.id", $id);
		}
		return $this->db->get()->result_array();
	}
	
	function _upsert($T, $data, $id=null){
		if(is_null($id)){
			$this->db
				->set($data)
				->insert($T);
			return $this->db->insert_id();
		}
		else {
			$this->db
				->set($data)
				->where("id", $id)
				->set('date_modif', 'NOW()', false)
				->update($T);
			return $id;
		}
	}
	
	function upsert_project($data, $id=null){
		$T = $this->t_portfolio_project;
		return $this->_upsert($T, $data, $id);
	}
	
	function upsert_picture($data, $id=null){
		$T = $this->t_portfolio_picture;
		return $this->_upsert($T, $data, $id);
	}

}