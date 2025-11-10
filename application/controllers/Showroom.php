<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Showroom extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->thisC = 'showroom';
        $this->load->model('Showroom_model', 'model');
    }

    public function index()
    {

    }
}
