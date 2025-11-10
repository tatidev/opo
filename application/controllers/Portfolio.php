<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Portfolio extends MY_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->thisC = 'portfolio';
		
		error_reporting(E_ALL | E_STRICT);
		$this->load->model('File_directory_model', 'files_directory');
		$this->load->model('Portfolio_model', 'model');
//		$this->load->model('Search_model', 'search');
		array_push($this->data['crumbs'], 'Portfolio');
	}

	function index(){
		$this->view('portfolio/view');
	}

	private function _reformat_result(&$data, $keys_as_list, $delimiter){
		foreach($keys_as_list as $key){
			foreach($data as &$row){
				if(!is_null($row[$key])){
					$row[$key] = explode($delimiter, $row[$key]);
				}
				else {
					$row[$key] = [];
				}
			}
		}
	}
	
	private function _reformat_project_data(&$data){
		$keys_as_list = ['picture_ids'];
		$delimiter = ' / ';
		$this->_reformat_result($data, $keys_as_list, $delimiter);
	}
	
	private function _reformat_picture_data(&$data){
		$keys_as_list = ['products_assoc', 'products_assoc_id'];
		$delimiter = ' / ';
		$this->_reformat_result($data, $keys_as_list, $delimiter);
		
		foreach($data as &$row){
			$src_frag = str_replace("files/portfolio/", "", $row['url']);
			// $row['url'] = base_url() . $row['url'];
			$row['url'] = 'https://opuzen-portfolio.s3.us-west-1.amazonaws.com/' . $src_frag;
		}
	}
	
	/*
	 * Projects
	 */
	
	function get(){
		$search = $this->input->get('search');
		$page = intval($this->input->get('page'));
		$size_per_page = intval($this->input->get('size_per_page'));
		$table_size = $this->model->get_project_count();
		$data = $this->model->get_all_projects($page, $size_per_page, $search);
//		$this->_reformat_project_data($data);
		echo json_encode([
			'page' => $page,
			'size_per_page' => $size_per_page,
			'total_count' => $table_size,
			'result' => array_column($data, 'id')
		]);
	}
	
	function get_project(){
		$project_id = $this->input->get('project_id');
		$project_data = $this->model->get_projects($project_id);
		$this->_reformat_project_data($project_data);
		$project_pictures = $this->model->get_pictures_from_project($project_id);
		$this->_reformat_picture_data($project_pictures);
		
		// Assuming the query is for 1 project_id
		$project_data[0]['pictures'] = $project_pictures;
		echo json_encode($project_data[0]);
	}

	function add_project(){
		$id = $this->model->upsert_project([
			"name" => $this->input->post("name"),
			"notes" => $this->input->post("notes"),
			"user_id" => $this->data['user_id']
		]);
		echo json_encode(['success'=>true, 'id'=>$id]);
	}
	
	function update_project(){
		$id = $this->input->post("project_id");
		if(is_null($id)){
			echo json_encode(["success"=>false, "error"=>"Missing project_id"]); return;
		}
		$_project_attrs = ["name", "notes", "active"];
		$data = [];
		$y = false;
		foreach($_project_attrs as $attr){
			$val = $this->input->post($attr);
			if(!is_null($val)){
				$data[$attr] = $val;
				$y = true;
			}
		}
		if($y){
			$data['user_id_modif'] = $this->data['user_id'];
			$this->model->upsert_project($data, $id);
			echo json_encode(['success' => true]); return;
		}
		echo json_encode(['success' => false, "error"=>"Invalid attributes"]); return;
	}
	
	function delete_project(){
		$id = $this->input->post("project_id");
		if(is_null($id)){
			echo json_encode(['success'=>false, "error"=>"Invalid id"]); return;
		}
		$id = $this->model->upsert_project([
			"user_id_modif" => $this->data['user_id']
		], $id);
		$pictures_data = $this->model->get_pictures_from_project($id);
		foreach($pictures_data as $pic_data){
			$this->_delete_picture($pic_data);
		}
		$this->model->db->delete($this->model->t_portfolio_project, ['id' => $id]);
		echo json_encode(['success'=>true]); return;
	}
	
	/*
	 * Pictures
	 */
	
	private function _receive_files($id){
		$dir = $this->files_directory->image_src_path([
			'status' => 'local_save',
//			'product_type' => $product_type,
			'product_id' => $id,
			'img_type' => 'portfolio',
			'include_filename' => false,
//			'file_format' => $extension[1]
		]);
		
		// Receive many files
		$fileParameters = array(
			'dir' => $dir,
			'filename' => mt_rand(10, 20000000),
			'print_response' => false
		);
		$this->load->library("UploadHandler", $fileParameters, 'UploadHandler');
		return $this->UploadHandler->get_response();
	}
	
	function get_pictures(){
		$project_id = $this->input->get('project_id');
		$data = $this->model->get_pictures_from_project($project_id);
		$this->_reformat_picture_data($data);
		echo json_encode([
			'total_count' => count($data),
			'result' => $data
		]);
	}
	
	function add_picture(){
		$id = $this->input->post("project_id");
		
		$img_data = $this->_receive_files($id);
		if(property_exists($img_data['files'][0], 'error')){
			echo json_encode(['success'=>false, 'error'=>$img_data['files'][0]->error]); return;
		}
		
		$data = [
			"project_id" => $id,
			"url" => str_replace(base_url(), '', $img_data['files'][0]->url),
			"user_id" => $this->data['user_id']
		];
		$id = $this->model->upsert_picture($data);
		$ret = $this->model->get_pictures($id);
		$this->_reformat_picture_data($ret);
		echo json_encode(['success'=>true, 'data'=>$ret[0]]);
	}
	
	function delete_picture(){
//		var_dump("Delete", $_POST, APPPATH); exit;
		$id = $this->input->post('picture_id');
		$picture_data = $this->model->get_pictures($id);
		$this->_delete_picture($picture_data);
		echo json_encode(["success" => true]);
	}
	
	private function _delete_picture($picture_data){
		$id = $picture_data['id'];
		
		// Update table
		$data = [
			'user_id_modif' => $this->data['user_id'],
		];
		$this->model->upsert_picture($data, $id);
		$this->model->db->delete($this->model->t_portfolio_picture, ['id' => $id]); # MySQL Trigger records history
		
		// Move the PORTFOLIO_PRODUCTS to HISTORY table
		$target = $this->model->th_portfolio_product;
		$source = $this->model->t_portfolio_product;
		$q = "INSERT INTO $target
			  (picture_id, item_id, date_add, user_id, user_id_archive)
			  SELECT S.picture_id, S.item_id, S.date_add, S.user_id, ?
			  FROM $source S WHERE S.picture_id = ?;";
		$this->model->db->query($q, [$this->data['user_id'], $id]);
		$this->model->db->delete($source, ['picture_id' => $id]);
	}
	
	function update_picture(){
		$id = $this->input->post("picture_id");
		if(is_null($id)){
			echo json_encode(["success"=>false, "error"=>"Missing picture_id"]); return;
		}
		$_picture_attrs = ["notes", "active"];
		$data = [];
		$y = false;
		foreach($_picture_attrs as $attr){
			$val = $this->input->post($attr);
			if(!is_null($val)){
				$data[$attr] = $val;
				$y = true;
			}
		}
		if($y){
			$data['user_id_modif'] = $this->data['user_id'];
			$this->model->upsert_picture($data, $id);
			echo json_encode(['success' => true]); return;
		}
		echo json_encode(['success' => false, "error"=>"Invalid attributes"]); return;
	}
	
	/*
	 * PictureProduct
	 */
	
	function add_product(){
//		var_dump("Add product to picture:", $_POST); exit;
		$item_id = $this->input->post('item_id');
		$picture_id = $this->input->post('picture_id');
		$data = [
			"picture_id" => $picture_id,
			"item_id" => $item_id,
			"user_id" => $this->data['user_id']
		];
		$this->model->db->set($data)->insert($this->model->t_portfolio_product);
		echo json_encode(["success" => true]);
	}
	
	function delete_product(){
//		var_dump("Delete product to picture:", $_POST); exit;
		$item_id = $this->input->post('item_id');
		$picture_id = $this->input->post('picture_id');
		$this->model->db->delete($this->model->t_portfolio_product, ['picture_id' => $picture_id, 'item_id' => $item_id]);
		echo json_encode(['success' => true]);
	}

}