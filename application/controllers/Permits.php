<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Permits extends MY_Controller{

    function __construct()
    {
        parent::__construct();
        $this->thisC = 'permits';
    }

    function get(){
        echo json_encode($this->data['permissionsList']);
    }
}