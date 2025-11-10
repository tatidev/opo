<?php
defined('BASEPATH') or exit('No direct script access allowed');

// error_reporting(E_ALL | E_STRICT);
class FileUploadToS3
{
    private $root = '';
    private $host = '';
    private $http_protocol = '';
    private $load_showcase_path = '';
    private $save_showcase_path = '';
    private $files_dir = '';
    private $files_temp = '';
    private $bucket_name = '';
    private $s3_key_prefix = '';
    private $region = '';
    private $s3_key = '';
    private $response_msg_prefix = '';
    private $response_data = [];
    protected $CI; // Store CI instance
    public $category_id = '';
    public $category_name = '';
    public $image_loc_destination_path = '';
    public $image_S3_destination_path = '';
    
    public function __construct()
    {
        // Detect if we're using HTTPS
        $this->http_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                              (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                              (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
                              (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '8443') ||
                              (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '8445') ? 'https://' : 'http://';
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        $this->host = $_SERVER['HTTP_HOST'];
        $this->base_url = $this->host;
        $this->showcase_rel_path = 'showcase/';
        $this->showcase_abs_path =  rtrim($this->root,'/') . '/' . 'showcase/';
        $this->files_rel_dir = 'files/';
        $this->files_abs_dir = rtrim($this->root,'/') . '/' . 'files/';
        $this->rel_temp_dir = $this->files_rel_dir . 'temp/';
        $this->abs_temp_dir = rtrim($this->files_abs_dir,'/') . '/' . 'temp/'; 
        $this->category_id = $_POST['category_id'] ?? null;
        $this->category_name = $_POST['category_name'] ?? null;
        $this->bucket_name = 'opuzen-web-assets-public';
        $this->s3_key_prefix = 'showcase/images/';
        $this->region = 'us-west-1';
        $this->response_data = ['status' => 'error', 'message' =>  ""];
    }

    /*
    *  Upload file to a temporary directory
    *  @param string $uploadDir
    *  @param string $baseUrl
    *  @return array
    *
    * example usage:
    * $uploadDir = '/var/www/html/files/temp';
    * $baseUrl = 'https://localhost:8443';
    * $response = uploadFileHandler($uploadDir, $baseUrl);
    * echo json_encode($response, JSON_PRETTY_PRINT);
    *
    */
    public function uploadFileHandler(string $uploadDir, string $baseUrl ) : array
    {
        $uploadDir = $this->abs_temp_dir;
        $base_url = $baseUrl;
        $response = [];
        $files_flattened = $this->flatten_files_array($_FILES);

        // Ensure the upload directory exists
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
            return ['error' => 'Failed to create upload directory'];
        }

        // Check if files were uploaded
        if (empty($files_flattened['files'])) {
            throw new Exception('No files uploaded');
            return ['error' => 'No files uploaded'];
        }
        if (!empty($files_flattened['files']['name']) && is_array($files_flattened)) {     
            foreach ($files_flattened as $postedFile) {
                //echo "<pre> ARR of ARRS L:". __lINE__ . " -->  <br />";
                //print_r($postedFile);
                //echo "</pre>";
                $filename = (is_array($postedFile['name']))?     $postedFile['name'][0] :     $postedFile['name'];
                $tmpName  = (is_array($postedFile['tmp_name']))? $postedFile['tmp_name'][0] : $postedFile['tmp_name'];
                $size     = (is_array($postedFile['size']))?     $postedFile['size'][0] :     $postedFile['size'];
                $type     = (is_array($postedFile['type']))?     $postedFile['type'][0] :     $postedFile['type'];
                $error    = (is_array($postedFile['error']))?    $postedFile['error'][0] :    $postedFile['error'];
        
                // Generate a unique filename
                $uniqueName = mt_rand(1000, 20000) . '_' . basename($filename);
                $tempFilePath = rtrim($uploadDir, '/') . '/' . $uniqueName;
                $tempFileUrl = rtrim($baseUrl, '/') . '/' . $this->rel_temp_dir. $uniqueName;
    
                // echo "<pre>  _tempFilePath L:". __lINE__ . " -->  <br />";
                // print_r( $tempFilePath);
                // echo "</pre>";
                
                // Default error message
                $fileErrorMsg = null;
        
                // Attempt to move the uploaded file
                if ($error === UPLOAD_ERR_OK) {
                    if (!move_uploaded_file($tmpName, $tempFilePath)) {
                        throw new Exception('Failed to move uploaded file');
                        $fileErrorMsg = 'Failed to move uploaded file';
                    }
                } else {
                    throw new Exception('File upload error code: ' . $error);
                    $fileErrorMsg = 'File upload error code: ' . $error;
                }
        
                // Construct file response object
                $fileInfo = new stdClass();
                $fileInfo->name = $uniqueName;
                $fileInfo->size = $size;
                $fileInfo->type = $type;
                $fileInfo->temp_url = $this->http_protocol . $tempFileUrl;
                $fileInfo->url = $this->http_protocol . $tempFileUrl;
                $fileInfo->temp_path = $tempFilePath;
                $fileInfo->error = $fileErrorMsg ?: 'Failed to resize image (original)';
                $fileInfo->deleteUrl = rtrim($baseUrl, '/') . '/index.php?file=' . urlencode($uniqueName);
                $fileInfo->deleteType = 'DELETE';
                $fileInfo->category_id = $this->category_id; // Static value (update as needed)
                $fileInfo->category_name = $this->category_name; // Static value (update as needed)
        
                // Append to response array
                $response['files'][] = $fileInfo;
            }
        }

        return $response;
    }

    /*  
     *  Move file from one relative web location to another
     *  @param string $source_url
     *  @param string $destination_url
     *  
     *  example usage:
     *  include this library in your controller
     *  $this->load->library('FileUploadToS3');
     * 
     *  $source_url = '/files/temp/1234_filename.jpg';
     *  $destination_url = '/showcase/images/1234_filename.jpg';
     * 
     *  $this->FileUploadToS3->SendUploadedTempFileToS3($source_url, $destination_url);
     *  
     *  confirm the file has been moved to the new location
     *  $file = $_SERVER['DOCUMENT_ROOT'] . $destination_url;
     *  
     */
    public function SendUploadedTempFileToS3(string $source_url, string $destination_url): bool
    {
        
        //echo "<pre> source_url:  ".__CLASS__."::".__METHOD__." [" . __LINE__ . "]<br />";
        //print_r($source_url);
        //echo "</pre>";
        //echo "<pre> destination_url".__CLASS__."::".__METHOD__." [" . __LINE__ . "]<br />";
        //print_r($destination_url);
        //echo "</pre>";

        // remove the http://localhost:8443 with server variable
        $source_url = str_replace($_SERVER['HTTP_HOST'], '', $source_url);  
        $source_url = str_replace('https://', '', $source_url);
        // Convert URLs to absolute file paths
        $source_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($source_url, '/');
        $destination_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($destination_url, '/');
        $this->image_loc_destination_path = $destination_path;
    
        // echo "<pre> source_path ".__CLASS__."::".__METHOD__." [" . __LINE__ . "]<br />";
        // print_r($source_path);
        // echo "</pre>";

        // echo "<pre> destination_path ".__CLASS__."::".__METHOD__." [" . __LINE__ . "]<br />";
        // print_r($destination_path);
        // echo "</pre>";

        // Ensure source file exists
        if (!file_exists($source_path)) {
            throw new Exception("Source file does not exist - $source_path");
            error_log("Error: Source file does not exist - $source_path");
            return false;
        }
    
        // if destination directory does not exist, create it
        $destination_dir = dirname($destination_path);

        if (!is_dir($destination_dir) && !mkdir($destination_dir, 0755, true)) {
            throw new Exception("Failed to create directory - $destination_dir");
            error_log("Error: Failed to create directory - $destination_dir");
            return false;
        }
    
        // Move file
        if (!rename($source_path, $destination_path)) {
            throw new Exception("Failed to move file from $source_path to $destination_path");
            error_log("Error: Failed to move file from $source_path to $destination_path");
            return false;
        }
        
        try {
            $this->createS3fileObject();
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to create S3 file object - $e->getMessage()");
            error_log("Error: Failed to create S3 file object - $e->getMessage()");
            return false;
        }
    }

    /*
    *  Upload file to a temporary directory
    *  @param string $uploadDir
    *  @param string $baseUrl
    *  @return array
    *
    * example usage:
    *   $this->abs_temp_dir = '/var/www/html/files/temp';
    *   $this->base_url = 'https://localhost:8443';
    *   $response = uploadFileHandler($uploadDir, $baseUrl);
    *   echo json_encode($response, JSON_PRETTY_PRINT);
    *
    */
    public function uploadToTemp()
    {
        

        //echo "<pre> FileuploadToS3:uploadToTemp() L:". __lINE__ . " ";
        //echo '$_FILES <br />';
        //print_r($_FILES);

        $files_flattened = $this->flatten_files_array($_FILES);

        //echo "<pre> FileuploadToS3:uploadToTemp() L:". __lINE__ . " ";
        //echo '$files_flattened <br />';
        //print_r($files_flattened);

        $this->response_msg_prefix = get_class($this).'::'.__FUNCTION__ . '  ';
        // $files_flattened['files'] is the file input field name in the HTML form
        $filename = mt_rand(1000, 20000) . '_' . $files_flattened['files']['name'];

        try {

            $data = $this->uploadFileHandler($this->abs_temp_dir, $this->base_url);

            foreach ($data['files'] as $file) {
                $file->category_id = $this->category_id;
                $file->category_name = $this->category_name;
            }

            $temp_file_path = $this->abs_temp_dir . $filename;         
            // echo "<pre> FileuploadToS3:uploadToTemp() L:". __lINE__ . " ";
            // echo '$files_flattened <br />';
            // print_r($files_flattened);
            // echo '$temp_directory <br />';
            // print_r($temp_directory);
            // print_r($temp_file_path);
            // echo '<br /> $data[files] <br />';
            // print_r($data['files']);
            // echo "</pre>";


            
            // Check if a file is uploaded
            if (!isset($files_flattened['files']) || $files_flattened['files']['error'] !== UPLOAD_ERR_OK) {
                $this->response_data['message'] = "ERROR: ". $this->response_msg_prefix ."Line[".__LINE__."] "  .' No file uploaded or there was an upload error.';
                echo json_encode($this->response_data);
                return;
            }

            $this->response_data = $data;
            $this->response_data['status'] = 'success';
            $this->response_data['message'] = $this->response_msg_prefix . 'File uploaded successfully.';
            $this->response_data['file_url'] = $temp_file_path;
            
        } catch (Exception $e) {
            $this->response_data['message'] =  "ERROR: " . $this->response_msg_prefix . "Line[" . __LINE__ . "] " . print_r($e->getMessage(), true);
        }


        // Return response as JSON
        echo json_encode($this->response_data);
    }
    
    /*
    * Create an S3 file object
    * @param string $origPath
    * @param bool $deleteOrigPathAfterChange
    * @return void
    */
    public function createS3fileObject($origPath = '', $deleteOrigPathAfterChange = FALSE)
    {
        
        //echo "DEBUGGINGcreateS3fileObject() line: ".__LINE__." file: ".__FILE__. "<br />";
        //echo '$origPath line: ' . __LINE__ .': ' .$origPath . "<br />";

        $origPath = $this->image_loc_destination_path;

        // echo '$origPath line: ' . __LINE__ .': ' .$origPath . "<br />";

        // Initialize response data
        $this->response_msg_prefix = __CLASS__ .'::'. __METHOD__ . '  ';
        $temp_file_path = FALSE;

        // Upload to S3 from temp filepath
        $response_data = $this->uploadToBucket($origPath);

        // Return response as JSON
        //echo json_encode($response_data);    
    }

    /*
    *  
    *  Upload file to S3 bucket
    *  @param string $local_file_path
    *  @param bool $deleteOrigPathAfterChange
    *  @return array
    *
    *  example usage:
    *  $local_file_path = '/var/www/html/showcase/images/1234_filename.jpg';
    *  $deleteOrigPathAfterChange = TRUE;
    *  $response = uploadToBucket($local_file_path, $deleteOrigPathAfterChange);
    *  echo json_encode($response, JSON_PRETTY_PRINT);
    *
    */
    private function uploadToBucket($local_file_path, $deleteOrigPathAfterChange = FALSE)
    {
        //echo "DEBUGGINGcreateS3fileObject() line: ".__LINE__." file: ".__FILE__. "<br />";
        //echo '$local_file_path line: ' . __LINE__ .': ' . $local_file_path . "<br />";

        try {
            // Get file name by removing doc root from $local_file_path
            $filenamepath = str_replace($this->root . "/" , '', $local_file_path);
            //echo "filenamepath  line: " . __LINE__ . ' : ' .  $filenamepath . "<br />";

            // AWS CLI command to upload file to S3
            $upload_command = sprintf(
                'aws s3 cp %s s3://%s/%s --region %s ',
                $local_file_path, $this->bucket_name, $filenamepath, $this->region);
            exec($upload_command, $output, $return_var);
            if ($return_var === 0) {
                $response_data['status'] = 'success';
                $response_data['message'] = 'File uploaded successfully.';
                // eg.
                // https://opuzen-web-assets-public.s3.us-west-1.amazonaws.com/showcase/images/D/2000/2025/bs_2025.jpg
                $response_data['file_url'] = sprintf(
                    'https://%s.s3.%s.amazonaws.com/%s',
                    $this->bucket_name,
                    $this->region,
                    $filenamepath
                );
            } else {
                $response_data['message'] = 'Failed to upload file to S3.';
                $response_data['error_details'] = implode("\n", $output);
            }
            if($local_file_path && $deleteOrigPathAfterChange) {
                // Remove the temporary file
                unlink($local_file_path);
            }
        } catch (Exception $e) {
            $response_data['message'] = $e->getMessage();
        }

        return $response_data;
    }


    /*
    *  Convert a legacy image src to an S3 URL
    *  @param string $legacyImgSrc
    *  @return string
    *   
    *  example usage:
    *  $legacyImgSrc = $_SERVER['HTTP_HOST'] . '/files/temp/1234_filename.jpg';
    *  $new_src = convertLegacyImgSrcToS3($legacyImgSrc);
    *  echo $new_src;
    */
    public function convertLegacyImgSrcToS3($legacyImgSrc)
    {
        // If already an S3 URL, return as is
        if (strpos($legacyImgSrc, 'opuzen-web-assets-public.s3.us-west-1.amazonaws.com') !== false) {
            return $legacyImgSrc;
        }

        // If the URL already contains a full domain, extract just the path
        if (strpos($legacyImgSrc, 'http') === 0) {
            $parsedUrl = parse_url($legacyImgSrc);
            $legacyImgSrc = $parsedUrl['path'];
        }

        // Remove any server host references
        $legacyImgSrc = str_replace($this->host, '', $legacyImgSrc);
        
        // PKL FIX: Extract only the relative path from 'showcase/' onwards
        // This handles any absolute path structure (e.g., /opuzen-efs/prod/opms/showcase/...)
        $showcasePos = strpos($legacyImgSrc, 'showcase/');
        if ($showcasePos !== false) {
            // Extract from 'showcase/' onwards
            $legacyImgSrc = substr($legacyImgSrc, $showcasePos);
        } else {
            // Fallback: try removing document root and cleaning up
            $legacyImgSrc = str_replace($_SERVER['DOCUMENT_ROOT'], '', $legacyImgSrc);
            $legacyImgSrc = ltrim($legacyImgSrc, '/');
        }
        
        // Remove 'showcase/images/' prefix if present (will be added back via s3_key_prefix)
        $legacyImgSrc = str_replace('showcase/images/', '', $legacyImgSrc);
        
        // Construct new S3 URL with proper prefix
        $s3Url = 'https://' . $this->bucket_name . '.s3.' . $this->region . '.amazonaws.com/' . $this->s3_key_prefix . $legacyImgSrc;
        
        // Remove any duplicated slashes
        $s3Url = preg_replace('#([^:])//+#', '$1/', $s3Url);
        
        return $s3Url;
    }

    function getImageFileExtension($filePath) {
        // Parse the URL if it's a URI and get the path component
        $path = parse_url($filePath, PHP_URL_PATH);
        
        // Get the file extension using pathinfo
        $extension = pathinfo($path, PATHINFO_EXTENSION);
    
        // Ensure it's a valid image extension (case-insensitive check)
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'];
    
        if ($extension && in_array(strtolower($extension), $validExtensions)) {
            return strtolower($extension);
        }
    
        return false; // Return false if it's not a valid image file
    }

    function flatten_files_array(array $files): array {
        // If already flat, return as-is
        if (is_array($files['files']['name'])) {
            $normalized = [];
            foreach ($files['files'] as $key => $value) {
                //print_r($value);
                if (is_array($value)) {
                    $normalized['files'][$key] = $value[0];
                } else {
                    $normalized['files'][$key] = $value;
                }
            }
            return $normalized;
        }
        // If not nested, return as-is
        return $files;
    }

}