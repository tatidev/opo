<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roadkit extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->thisC = 'roadkit';
    }

    public function index()
    {
        $this->view('roadkit/roadkit_list');
    }
}
