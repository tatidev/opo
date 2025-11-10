<?php

function get_item_web_visiblity($db, $item_id = false, $item_code = false) {
    if ($item_id === false) {
        return null; 
    }
    if (strtolower($item_code) == 'd' || strtolower($item_code) == 'dgital') {
        return "is_digital";
    }

    $db
      ->select("status.web_vis as prod_status_website_visibility")
      ->from("P_PRODUCT_STATUS status")
      ->join("T_ITEM item", "status.id = item.status_id")
      ->where('item.id', $item_id);

    $q = $db->get();

    // Check for SQL errors
    if ($q === false) {
        // Log the error or display it
        $error = $db->error(); // Get the error message
        log_message('error', 'Database error: ' . $error['message']); // Adjust to your logging method
        //$qrystr =  $this->db->get_compiled_select();
        //echo "<pre>get_item_web_visiblity(id)  ";
        //print_r( $error['message']);
        //print_r( $error);
        //echo "</pre>";
        exit;
        return null;
    }

    if ($q->num_rows() > 0) {
        $row = $q->row();
        if(isset($row->prod_status_website_visibility)){
            return $row->prod_status_website_visibility;
        }
        return null;
    } else {
        return null;
    }
}


function sort_by_key(&$array, $key, $ascending = true)
{

    $build_sorter = function ($key, $ascending) {
        $order_f = function ($item1, $item2) {
            if ($item1 == $item2) return 0;
            return $item1 > $item2 ? -1 : 1;
        };
        if ($ascending) {
            $order_f = function ($item1, $item2) {
                if ($item1 == $item2) return 0;
                return $item1 < $item2 ? -1 : 1;
            };
        }
        return function ($a, $b) use ($key, $order_f) {
            return $order_f($a[$key], $b[$key]);
        };
    };

    usort($array, $build_sorter($key, $ascending));
    return $array;
}


function flatten_array($arr)
{
    $ret = [];
    foreach ($arr as $a) {
        $ret = array_merge($ret, $a);
    }
    return $ret;
}


function array_get($array, $key, $default = null)
{
    return isset($array[$key]) ? $array[$key] : $default;
}

function date_php2mysql($date)
{
    if (is_null($date) or $date == '') {
        return null;
    }
    return date('Y-m-d H:i:s', strtotime($date));
}

function date_mysql2php($date)
{
    if (is_null($date)) {
        return '';
    }
    return date('Y-m-d', strtotime($date));
}

//function datetime_mysql2php($date)
//{
//	return date('m/d/Y H:i:s', strtotime($date));
//}

function asset_url()
{
    return base_url() . 'assets/';
}

function file_url()
{
    return base_url() . 'files/';
}

function view_url($view_name)
{
//     $target_file= APPPATH.'views/'.$view_name;
    $target_file = base_url() . 'application/views/' . $view_name;
    return $target_file;
//     if( file_exists($target_file) ) return $target_file;
}

function asset_links($library_arr)
{
    // Libraries loading
    // -- This function is used in the main HEADER and FOOTER of the site
    // Common libraries are set up on Core/MY_Controller
    // Then invidivual libraries are set up by each controller
    $links = '';
    foreach ($library_arr as $lib) {
        switch ($lib['type']) {
            case 'css':
                $links .= "<link type='text/css' rel='stylesheet' href='" . $lib['url'] . "'  />";
                break;

            case 'js':
                $links .= "<script type='text/javascript' src='" . $lib['url'] . "'></script>";
                break;

            case 'less':
                $links .= "<link rel='stylesheet/less' type='text/css' href='" . $lib['url'] . "' />";
                break;

            default:
                $links .= $lib['url'];
                break;
        }
    }
    echo $links;
}

function include_pdf_library()
{
    require_once('pdf/fpdf.php');
    require_once('pdf/fpdf-custom.php');
}

function hasPermissions($permissionsList, $arr, $user_id = null)
{
    if ($user_id === '1') {
        return true;
    }
    if (array_key_exists('module', $arr)) {
        if ($arr['module'] === '' && $arr['action'] === '') {
            return true;
        }
        foreach ($permissionsList as $row) {
            if ($row['module'] == $arr['module'] && $row['action'] == $arr['action'])
                return true;
        }
        return false;
    }
    return true;
}

function create_dir($filename)
{
    if (!is_dir($filename)) {
        mkdir($filename, 0777, true);
    }
}

// function copy_on_all_subfolders($from, $to){
//     $fromParts = explode("/", $from);
//     $filename = end($fromParts);
//     $pieces = explode("/", $to);
//     $agg = "";
//     foreach($pieces as $piece){
//         $agg .= $piece . "/";
//         if($agg != '/' and $agg != '/home/' and $agg != '/home/opuzen/' and $agg != '/home/opuzen/public_html/'){
//             copy($from, $agg.$filename);
//         }
//     }
// }

// from = /files/temp/index.html
// to = /opuzen-efs/prod/website/showcase/images/...

/*function copy_on_all_subfolders($from, $to){
    echo "copying from: $from to: $to <br>";
    
    $fromParts = explode("/", $from);
    $filename = end($fromParts);
    $pieces = explode("/", $to);
    $agg = "";
    echo "FOR LOOP <br />";
    foreach($pieces as $piece){
        echo "copying piece: $piece <br>";
        $agg .= $piece . "/";
        echo "aggregate: $agg <br>";
        if($agg != '/' and $agg != '/opuzen-efs/' and $agg != '/opuzen-efs/prod/' and $agg != '/opuzen-efs/prod/website/'){
            echo "copying from: $from to: $agg.$filename <br>";
            copy($from, $agg.$filename);
        }
    }
}*/

/**
 * Copy a file to all subdirectories within a given directory.
 *
 * @param string $copy_from_file_path Full path of the source file.
 * @param string $copy_to_file_path   Relative path where the file should be copied.
 * @param string $starting_at_subfolder Root directory where the recursion begins.
 */
function copy_on_all_subfolders(string $copy_from_file_path, string $copy_to_file_path, string $starting_at_subfolder = ''): void
{
    // Ensure the source file exists
    if (!file_exists($copy_from_file_path)) {
        echo "Error: Source file does not exist: $copy_from_file_path\n";
        return;
    }

    // Ensure the root directory exists
    if (!is_dir($starting_at_subfolder)) {
        echo "Error: Starting directory does not exist: $starting_at_subfolder\n";
        return;
    }

    // Get a list of all subdirectories recursively
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($starting_at_subfolder, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            // Build the target path inside the subdirectory
            $target_path = $file->getRealPath() . DIRECTORY_SEPARATOR . basename($copy_to_file_path);

            // Copy the file
            if (copy($copy_from_file_path, $target_path)) {
                echo "Copied to: $target_path\n";
            } else {
                echo "Failed to copy to: $target_path\n";
            }
        }
    }
}




function restock_comparison_sort_by_item_id($a, $b)
{
    $key = 'item_id';
    if ($a[$key] < $b[$key]) {
        return -1;
    } else if ($a[$key] > $b[$key]) {
        return 1;
    }
    return 0;
}

function decode_array($arr, $field, $value)
{
    $ret = array();
    if (is_array($arr) && !empty($arr)) {
        foreach ($arr as $a) {
            $id = $a[$field];
            $name = $a[$value];
            $ret[$id] = $name;
        }
    }
    return $ret;
}

function delete_col(&$array, $key)
{
    if (is_array($key)) {
        // Trying to delete many columns at once
        // Check that the column ($key) to be deleted exists in all rows before attempting delete
        foreach ($array as &$row) {
            foreach ($key as $k) {
                if (!array_key_exists($k, $row)) {
                    return false;
                }
            }
        }
        foreach ($array as &$row) {
            foreach ($key as $k) {
                unset($row[$k]);
            }
        }
        unset($row);
        return true;
    } else {
        // Deleting only 1 column
        // Check that the column ($key) to be deleted exists in all rows before attempting delete
        foreach ($array as &$row) {
            if (!array_key_exists($key, $row)) {
                return false;
            }
        }
        foreach ($array as &$row) {
            unset($row[$key]);
        }

        unset($row);

        return true;
    }

}