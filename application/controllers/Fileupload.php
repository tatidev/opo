<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fileupload extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        error_reporting(E_ALL | E_STRICT);
        $this->load->model('File_directory_model', 'files_directory');

        $filename = mt_rand(1000, 20000);
        $directory = $this->files_directory->temp_folder;

        $parameters = array(
            // File destination
            'dir' => $directory,
            // File name
            'filename' => $filename,
            'print_response' => false
        );

        $this->load->library("UploadHandler", $parameters, 'UploadHandler');
	    $category_id = $this->input->post('category_id');
	    $category_name = $this->input->post('category_name');
	    $data = $this->UploadHandler->get_response();

        // echo "<pre> FileuploadToS3 L:". __lINE__ . " ";
        // echo '$_FILES <br />';
        // print_r($_FILES);
        // echo '$data[files] <br />';
        // print_r($data);
        // echo "</pre>";

	    foreach ($data['files'] as $file) {
		    $file->category_id = $category_id;
		    $file->category_name = $category_name;
	    }

        echo json_encode($data);
    }

}	

