<?php
class File_directory_model extends MY_Model
{
    
    protected $files_dir ;
    protected $root ;

    function __construct()
    {
        parent::__construct();
        $this->root = $_SERVER['DOCUMENT_ROOT'] ;
        $this->host = $_SERVER['HTTP_HOST'] ;
        $this->load_showcase_path = $this->root . '/showcase/';
        $this->save_showcase_path =   $this->root . '/showcase/';
        $this->files_dir =  $this->root . '/files';
        $this->files_temp =  $this->files_dir . '/temp/';
        
        // PHP get path to showcase folder
        $this->files_folder = $this->files_dir;
        $this->temp_folder = $this->files_folder . 'temp/';
        $this->images_folder = 'images/';
        $this->placeholder_image_url = 'https://www.opuzen.com/assets/images/placeholder.jpg';
    }

    function index($p1 = '', $p2 = '')
    {

        $folder1 = '';
        $folder2 = '';

        switch ($p1) {

            case 'temp':
                $folder1 = 'temp/';
                break;
                
	        case 'portfolio':
	        	$folder1 = "portfolio/";
	        	break;
            
            case 'product':
            case constant('Regular'):
            case constant('ScreenPrint'):
                $folder1 = 'products';
                switch ($p2) {
                    case 'abrasion':
                        $folder2 = 'abrasion';
                        break;
                    case 'firecodes':
                        $folder2 = 'firecodes';
                        break;
                    case 'specsheet_opuzen':
                        $folder2 = 'ssop';
                        break;
                    case 'memotags_picture':
                        $folder2 = 'memotags';
                        break;
                    case '':
                        $folder2 = 'product_files'; // vendor spec sheet for example!
                        break;
                    default:
                        break;
                }
                break;


            case constant('Digital'):
                $folder1 = 'digital';
                switch ($p2) {
                    case 'memotags_picture':
                        $folder2 = 'memotags';
                        break;
                    case '':
                        $folder2 = 'product_files';
                        break;
                    default:
                        break;
                }
                break;


            case constant('vendor'):
                $folder1 = 'vendors';
                break;

            case constant('showroom'):
                $folder1 = 'showroom';
                break;

            default:
                break;
        }

        return $this->files_folder . $folder1 . ($folder2 !== '' ? '/' . $folder2 : '');
    }

    function image_src_path($request){
    	$default = [
    		'status'=>'load',
		    'product_type'=>Regular,
		    'product_id'=>null,
		    'item_id'=>null,
		    'include_filename'=>false,
		    'file_format'=>''
	    ];
    	$request = array_merge($default, $request);

    	$status = $request['status']; # load or save
	    $product_type = $request['product_type']; # Regular or Digital
	    $product_id = $request['product_id'];
	    $item_id = $request['item_id'];
	    $img_type = $request['img_type']; # beauty_shot, item hd , item big, placeholder, portfolio, portfolio_thumb
	    $include_filename = $request['include_filename'];
	    $file_format = $request['file_format'];

	    # /R/5000/
		$_range_folder = strval(intval($product_id / 1000) * 1000);
	    # /R/5000/5123/
	    $_product_folder = strval($product_id);

	    $product_base_folder = implode("/", [$product_type, $_range_folder, $_product_folder]) . '/';

	    $image_location = '';
	    $filename = '';
	    switch($img_type){
		    case 'temp':
		    	return $this->temp_folder;
		    case 'beauty_shot':
			    $image_location = ''; # stored in $product_base_folder
			    $filename = 'bs_' . $product_id . '.' . $file_format;
		    	break;
		    case 'hd':
		    case 'big':
			    $image_location = $img_type . '/';
			    $filename = $item_id . '.' . $file_format;
		    	break;
		    case 'abrasions':
			    $image_location = 'abrasions/';
			    break;
		    case 'firecodes':
			    $image_location = 'firecodes/';
			    break;
		    case 'memotags_picture':
			    $image_location = 'memotags/';
			    break;
		    case 'files':
			    $image_location = 'product_files/'; // vendor spec sheet for example!
			    break;
		    case 'placeholder':
		    	# FORCE RETURN
		    	return $this->placeholder_image_url;
		    	break;
		    case 'portfolio':
			    $product_base_folder = implode("/", ['portfolio', $_range_folder, $_product_folder]) . '/';
			    $image_location = '';
		    	break;
		    case 'portfolio_thumb':
			    $product_base_folder = 'portfolio/thumb/';
			    $image_location = '';
		    	break;
	    }

	    if($status === 'load') {
            // echo '===================== <br />';
            // echo 'this->save_showcase_path: '. $this->save_showcase_path . '<br />';
            // echo 'this->images_folder: ' . $this->images_folder . '<br />';
            // echo 'product_base_folder: ' . $product_base_folder . '<br />';
            // echo 'image_location: ' . $image_location . '<br />';
            // echo '===================== <br />';
            //$return_path = $this->save_showcase_path . $this->images_folder . $product_base_folder . $image_location;
		    $return_path = $this->load_showcase_path . $this->images_folder . $product_base_folder . $image_location;
            
	    } else if ($status === 'save'){
		    $return_path = $this->save_showcase_path . $this->images_folder . $product_base_folder . $image_location;
            //$return_path = $this->save_showcase_path . $this->images_folder . $product_base_folder . $image_location;
            ///  opuzen-efs/prod/website/showcase/ . images/ . $product_base_folder . 
            // echo '===================== <br>';
            // echo 'RETURN PATH for MKDIR(): ' . $return_path . '<br>';
            // echo '===================== <br>';
		    create_dir($return_path);
            # Create a index.html file
            // copy($this->files_folder."index.html", $return_path."index.html");
            //copy_on_all_subfolders($this->files_folder."/index.html", rtrim($return_path,'/'), "/" );

	    } else if ($status === 'local_save'){
	    	$return_path = $this->files_folder ."/" . $product_base_folder . $image_location;
            $return_path = str_replace("//", "/", $return_path);
		    create_dir($return_path);
            # Create a index.html file
            // copy($this->files_folder."index.html", $return_path."index.html");
            //copy_on_all_subfolders($this->files_folder."/index.html",  rtrim($return_path,'/'), "/");
	    }

	    if($include_filename){
		    $return_path .= $filename;
	    }

	    return $return_path;
    }

    function old_image_src_path($status, $purpose, $size = '')
    {
        switch ($purpose) {
            case Regular:
            case 'fabrics':
            case 'sc_grounds':
                $folder = 'images_pattern/';
                break;

            case Regular . '_items':
            case 'digital_grounds':
            case 'fabrics_items':
                switch ($size) {
                    case 'thumb':
                        $folder = 'images_items/big/';
                        break;
                    case 'big':
                        $folder = 'images_items/big/';
                        break;
                    case 'hd':
                        $folder = 'images_items_hd/';
                        break;
                    default:
                        $folder = 'images_items/big/';
                        break;
                }
                break;

            case Digital:
            case 'digital_styles':
                $folder = 'images_dp_styles/';
                break;

            case Digital . "_items":
            case 'digital_styles_items':
                $folder = 'images_dp_items/big/';
                break;

            case 'portfolio':
                $folder = 'press/';
                break;

            case 'portfolio_thumb':
                $folder = 'press/thumb/';
                break;

            case 'screenprints':
                switch ($size) {
                    case 'full_repeat':
                        $folder = 'images_print/full_repeat/';
                        break;

                    case 'actual_scale':
                        $folder = 'images_print/actual_scale/';
                        break;

                    case 'additional1':
                        $folder = 'images_print/additional1/';
                        break;

                    case 'additional2':
                        $folder = 'images_print/additional2/';
                        break;

                    default:
                        $folder = 'images_print/thumbnail/';
                        break;
                }
                break;

            case 'replaced_images':
                $folder = 'replaced_images/';
                break;

            case 'placeholder':
                return $this->placeholder_image_url;

            default:
                $folder = '';
                break;
        }

        switch ($status) {
            case 'load':
                return $this->load_showcase_path . $this->images_folder . $folder;
                break;
            case 'save':
                return $this->save_showcase_path . $this->images_folder . $folder;
                break;
        }

    }


}

?>