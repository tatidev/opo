<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH.'/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Stock extends REST_Controller {
  
  function __construct(){
    parent::__construct();
// 		$this->load->model('Lists_model', 'model');
// 		$this->load->model('Search_model', 'search');
  }
  
  
  
}