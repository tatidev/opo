<?php
defined('BASEPATH') or exit('No direct script access allowed');

error_reporting(E_ALL | E_STRICT);

class FileuploadToS3 extends MY_Controller
{
    private $bucket_name = '';
    private $s3_key_prefix = '';
    private $region = '';
    private $s3_key = '';
    private $response_msg_prefix = '';
    private $response_data = [];
    
    public function __construct()
    {
        parent::__construct();
        $this->bucket_name = 'opuzen-web-assets-public';
        $this->s3_key_prefix = 'showcase/images/';
        $this->region = 'us-west-1';
        $this->load->model('File_directory_model', 'files_directory');
        $this->response_data = ['status' => 'error', 'message' =>  ""];
    }

    public function uploadToTemp()
    {
        $this->response_msg_prefix = get_class($this).'::'.__FUNCTION__ . '  ';
        try {
            // Retrieve POST parameters
            $category_id   = (null !== ($this->input->post('category_id')))?  $this->input->post('category_id') : null;
            $category_name = (null !== ($this->input->post('category_name')))? $this->input->post('category_id') : null;
            // Temp file information
            $temp_directory = $this->files_directory->temp_folder;
            // $_FILES['files'] is the file input field name in the HTML form
            $filename = mt_rand(1000, 20000) . '_' . $_FILES['files']['name'];
            $temp_file_path = $temp_directory . $filename;
            // echo "<pre> FileuploadToS3 L:". __lINE__ . " ";
            // print_r($_FILES);
            // echo "</pre>";
            // Check if a file is uploaded
            if (!isset($_FILES['files']) || $_FILES['files']['error'] !== UPLOAD_ERR_OK) {
                $this->response_data['message'] = "ERROR: ". $this->response_msg_prefix ."Line[".__LINE__."] "  .' No file uploaded or there was an upload error.';
                echo json_encode($this->response_data);
                return;
            }
            // Move the uploaded file to a temporary location
            if (!move_uploaded_file($_FILES['files']['tmp_name'], $temp_file_path)) {
                $this->response_data['message'] =  "ERROR: ". $this->response_msg_prefix ."Line[".__LINE__."] "  .' Failed to move the uploaded file.';
                echo json_encode($this->response_data);
                return;
            }
            
            $this->response_data['status'] = 'success';
            $this->response_data['message'] = $this->response_msg_prefix . 'File uploaded successfully.';
            $this->response_data['file_url'] = $temp_file_path;
            
        } catch (Exception $e) {
            $this->response_data['message'] =  "ERROR: ". $this->response_msg_prefix ."Line[".__LINE__."] " . $e->getMessage();
        }
        // Return response as JSON
        echo json_encode($this->response_data);
    }

    public function uploadToS3($origPath, $newPath, $deleteOrigPathAfterChange = FALSE)
    {
        echo "<pre> uploadToS3 </pre>";
        echo "LINE:[" . __LINE__ . "]<br>";
        print_r($newPath);
        echo "</pre>";
        die();
        // Initialize response data
        $this->response_msg_prefix = get_class($this).'::'. __FUNCTION__ . '  ';
        $temp_file_path = FALSE;
        // if $origPath contains "temp" then it is a temp uplaoded file
        if (strpos($origPath, 'temp') !== false) { 
            $temp_file_path = $origPath; 
            // Upload to S3 from temp filepath
            try {
                // AWS CLI command to upload file to S3
                $upload_command = sprintf(
                    'aws s3 cp %s s3://%s/%s --region %s ',
                    $temp_file_path, $this->bucket_name, $this->s3_key_prefix.$this->s3_key, $this->region);
                // echo "<pre> FileuploadToS3 L:". __lINE__ . "<br />";
                // echo ' $upload_command: '. "<br />";
                // echo $upload_command . "<br />";
                // echo "</pre>";         
                // Execute the upload command
                exec($upload_command, $output, $return_var);
                if ($return_var === 0) {
                    $response_data['status'] = 'success';
                    $response_data['message'] = 'File uploaded successfully.';
                    $response_data['file_url'] = sprintf(
                        'https://%s.s3.%s.amazonaws.com/%s',
                        $bucket_name,
                        $region,
                        $s3_key
                    );
                } else {
                    $response_data['message'] = 'Failed to upload file to S3.';
                    $response_data['error_details'] = implode("\n", $output);
                }
                if($temp_file_path) {
                    // Remove the temporary file
                    unlink($temp_file_path);
                }
            } catch (Exception $e) {
                $response_data['message'] = $e->getMessage();
            }
        // Return response as JSON
        echo json_encode($response_data);    
        }   
    }
}