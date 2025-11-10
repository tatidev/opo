<?php defined('BASEPATH') OR exit('No direct script access allowed');

class REST_Auth
{
    public function login($username, $password) {
        if($username == 'admin' && $password == '1234')
        {
            return true;            
        }
        else
        {
            return false;           
        }           
    }
}