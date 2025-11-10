<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Etc extends MY_Controller
{

	public function index()
	{
		echo 'nothing';
		exit;

		$found = 0;
		$results = array();
		$recorridos = array();
		$this->load->library('table');

		$this->load->model('File_directory_model', 'directory');
		$files = scandir($this->directory->index('product', 'memotags_picture') . '/imported/');

		$q = $this->db
		  ->distinct()
		  ->select('code')
		  ->from("T_ITEM")
		  ->get()->result_array();
		$codes = array_column($q, 'code');

		foreach ($files as $filename) {
			$name_arr = str_split($filename);
			$n = 1;
			$item_num = '';
			foreach ($name_arr as $l) {
				if (is_numeric($l)) {
					$item_num .= $l;
					if ($n === 4) {
						$item_num .= '-';
					}
					$n++;
					if ($n === 9) {
						break;
					}
				}
			}
			if (in_array($item_num, $codes) && strlen($item_num) === 9) {
				$found++;
				//$this->table->add_row( $filename, $item_num );
				$product = $this->db
				  ->select("P.id, P.name")
				  ->from("T_PRODUCT P")
				  ->join("T_ITEM I", "P.id = I.product_id")
				  ->where("I.code", $item_num)
				  ->get()->row_array();
				if (empty($recorridos) || !in_array($product['id'], $recorridos)) {
					array_push($results, array('filename' => $filename,
						'product_id' => $product['id'])
					);
					$this->table->add_row($product);
					array_push($recorridos, $product['id']);
				}
			}

		}

		// Start processing each file
		foreach ($results as $p) {

			$new_name = $p['product_id'] . '-' . url_title('4-memotag-picture-06-20-18-', true) . '-1';
			$new_location = $this->directory->index('product', 'memotags_picture') . '/' . $new_name . '.jpeg';
			// Save new location
			rename(str_replace(site_url(), '', $this->directory->index('product', 'memotags_picture') . '/imported/' . $p['filename']), $new_location);

			$ret = array(
			  'product_id' => $p['product_id'],
			  'product_type' => 'R',
			  'category_id' => '4',
			  'url_dir' => $new_location,
			  'user_id' => '1'
			);
			$this->db
			  ->set($ret)
			  ->insert("T_PRODUCT_FILES");
		}

		echo $this->table->generate();
	}

	public function import_sales_m_product()
	{
		$this->load->library('table');
		echo 'nothing';
		exit;
		$this->db->trans_begin();

		$query = $this->db
		  ->query("SELECT P.id as sales_id, C.master_catalogue_id, V.master_vendor_id, P.name, P.code, P.color,
			P.width, P.repeatVertical, P.repeatHorizontal

FROM db199249_sales.op_products P
LEFT OUTER JOIN db199249_sales.op_catalogue C ON P.idCatalogue = C.id
LEFT OUTER JOIN db199249_sales.op_products_vendors PV ON P.id = PV.idProduct
LEFT OUTER JOIN db199249_sales.op_vendors V ON PV.idVendor = V.id

WHERE
P.master_item_id IS NULL
AND NOT ( master_catalogue_id IS NULL AND master_vendor_id IS NULL )

ORDER BY P.name;");
		$this->table->set_heading('catalogue_id', 'vendor_id', 'name', 'code', 'color', 'width', 'V', 'H');

		$res = $query->result_array();


		foreach ($res as $r) {

			$dataP = array(
			  'name' => $r['name'],
			  'width' => str_replace('"', '', $r['width']),
			  'vrepeat' => $r['repeatVertical'],
			  'hrepeat' => $r['repeatHorizontal'],
			  'user_id' => 1
			);
			$this->db->set($dataP)->insert("T_PRODUCT");
			$product_id = $this->db->insert_id();

			// Look for color
			$qc = $this->db->select('id, name')->from('P_COLOR')->where('name', $r['color'])->get()->result_array();
			//echo "<pre>"; var_dump($qc);
			if (count($qc) === 1) {
				$color_id = $qc[0]['id'];
			} else {
				// Insert new color
				$dataC = array(
				  'name' => $r['color'],
				  'user_id' => 1
				);
				$this->db->set($dataC)->insert("P_COLOR");
				$color_id = $this->db->insert_id();
			}

			$dataI = array(
			  'product_id' => $product_id,
			  'product_type' => 'R',
			  'code' => $r['code'],
			  'status_id' => 1,
			  'stock_status_id' => 1,
			  'user_id' => 1
			);

			$this->db->set($dataI)->insert("T_ITEM");
			$item_id = $this->db->insert_id();

			$this->db
			  ->set('master_item_id', $item_id)
			  ->where('id', $r['sales_id'])
			  ->update("db199249_sales.op_products");

			$dataIC = array(
			  'item_id' => $item_id,
			  'color_id' => $color_id,
			  'user_id' => 1
			);
			$this->db->set($dataIC)->insert("T_ITEM_COLOR");

			$dataPW = array(
			  'product_id' => $product_id,
			  'weave_id' => $r['master_catalogue_id'],
			  'user_id' => 1
			);
			if (!is_null($r['master_catalogue_id'])) $this->db->set($dataPW)->insert("T_PRODUCT_WEAVE");

			$dataPV = array(
			  'product_id' => $product_id,
			  'vendor_id' => $r['master_vendor_id'],
			  'user_id' => 1
			);
			if (!is_null($r['master_vendor_id'])) $this->db->set($dataPV)->insert("T_PRODUCT_VENDOR");

		}


		//echo "<pre>"; var_dump($res); exit;
		echo "Total: " . count($res);
		echo $this->table->generate($res);
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function check_images_sizes()
	{
		$this->load->model('Search_model', 'search');
		$this->load->library('table');

		$this->table->set_heading('Product', 'Code', 'Color', 'Image Size');

		$result = $this->search->do_search(
		  array(
			'group_by' => item,
			'select' => ['pic_big_url', 'pic_hd_url'],
			'showcase' => ['web_visible' => 'Y'],
			'includeDiscontinued' => false,
			'includeArchived' => false, // archived items!
//			'order_by' => 'product_name'
		  )
		);
//		echo "<pre>"; var_dump($result);
//		exit;

		foreach ($result as $r) {
			$is_bad = false;
			
			
			$k = null;
			if(!empty($r['pic_big_url'])){
				$k = 'pic_big_url';
			} else if(!empty($r['pic_hd_url'])){
				$k = 'pic_hd_url';
			} else {
				$is_bad = true;
				$size = "no scan";
			}
			
			if(!is_null($k)){
				list($width, $height, $type, $attr) = getimagesize($r[$k]);
				if($width != $height){
					$size = $width . 'x' . $height;
					$is_bad = true;
				}
			}
			

//			if (!empty($r['pic_hd_url'])) {
//				list($width, $height, $type, $attr) = getimagesize($r['pic_hd_url']);
//				$size = $width . 'x' . $height;
//				if ($width < 600 || $height < 600) {
//					$is_bad = true;
//				}
//			} else if (!empty($r['pic_big_url'])) {
//				list($width, $height, $type, $attr) = getimagesize($r['pic_big_url']);
//				$size = $width . 'x' . $height;
//				if ($width < 600 || $height < 600) {
//					$is_bad = true;
//				}
//			} else {
//				$size = 'no scan available';
//				$is_bad = true;
//			}

			if ($is_bad) {
				$size = "<span style='color:red'>" . $size . "</span>";
				$this->table->add_row([$r['product_name'], $r['code'], $r['color'], $size]);
			}


//       break;
		}

//     echo "<pre>"; var_dump($result);
		echo $this->table->generate();

	}

    public function test()
    {
        $dir = './pyzen/run.py';

        $command = 'python3.6 ' . $dir;
        $output = shell_exec($command);
//     echo $output;

		// Convert to array
		$output = json_decode($output, true);
		echo $output['html'];
	}


	public function fix_filesystem(){
//		Before running, do
//			- Files back up (OPMS files and showcase/images)
//			- Database backup

		$this->load->model('File_directory_model', 'file_directory');

//		$this->fix_beauty_shots();
//		$this->fix_regular_thumbnails();
//		$this->fix_styles_beauty_shots();
//		$this->fix_styles_thumbnails();
		$this->fix_separate_files();
	}

	function fix_beauty_shots(){
		$num_mv = 0;
		$q = $this->db
		  ->select("product_id, product_type, pic_big_url")
		  ->from("SHOWCASE_PRODUCT")
		  ->where("pic_big_url IS NOT NULL", null, false)
		  ->get()->result_array();

		$request = [
		  'img_type' => 'beauty_shot',
		  'file_format' => 'jpg',
		  'include_filename' => true
		];

		foreach($q as $showcase_product){
			$old_dir = str_replace($this->file_directory->load_showcase_path, $this->file_directory->save_showcase_path, $showcase_product['pic_big_url']);
			if( strpos($old_dir, 'placeholder') !== false ) continue;
			$request['status'] = 'save';
			$request['product_type'] = $showcase_product['product_type'];
			$request['product_id'] = $showcase_product['product_id'];
			$new_dir = $this->file_directory->image_src_path($request);
			echo "cp $old_dir $new_dir <br>";
			$num_mv += 1;
		}

		echo "$num_mv Regular beauty shots moved";
	}

	function fix_regular_thumbnails(){
		$num_mv = 0;
//    	Items
		$q = $this->db
		  ->select("S.item_id, I.product_id, I.product_type, S.pic_big_url, S.pic_hd_url")
		  ->from("SHOWCASE_ITEM S")
		  ->join("T_ITEM I", "S.item_id = I.id")
		  ->group_start()
			  ->where("S.pic_big_url IS NOT NULL", null, false)
			  ->or_where("S.pic_hd_url IS NOT NULL", null, false)
		  ->group_end()
		  ->where("I.product_id >", "2500")
		  ->order_by("I.product_id")
		  ->get()->result_array();

		$request = [
//		  'img_type' => 'beauty_shot',
		  'file_format' => 'jpg',
		  'include_filename' => true
		];

		foreach($q as $showcase_item){
//			Big
			if( !is_null($showcase_item['pic_big_url']) ){
				$old_dir = str_replace($this->file_directory->load_showcase_path, $this->file_directory->save_showcase_path, $showcase_item['pic_big_url']);
				$request['status'] = 'save';
				$request['img_type'] = 'big';
				$request['product_type'] = $showcase_item['product_type'];
				$request['product_id'] = $showcase_item['product_id'];
				$request['item_id'] = $showcase_item['item_id'];
				$new_dir = $this->file_directory->image_src_path($request);
				echo "cp $old_dir $new_dir <br>";
				$num_mv += 1;
			}

//			HD
			if( !is_null($showcase_item['pic_hd_url']) ){
				$old_dir = str_replace($this->file_directory->load_showcase_path, $this->file_directory->save_showcase_path, $showcase_item['pic_hd_url']);
				$request['status'] = 'save';
				$request['img_type'] = 'hd';
				$request['product_type'] = $showcase_item['product_type'];
				$request['product_id'] = $showcase_item['product_id'];
				$request['item_id'] = $showcase_item['item_id'];
				$new_dir = $this->file_directory->image_src_path($request);
				echo "cp $old_dir $new_dir <br>";
				$num_mv += 1;
			}
		}

		echo "$num_mv Regular item thumbnails moved";
	}

	function fix_styles_beauty_shots(){
		$num_mv = 0;

		$q = $this->db
		  ->select("style_id, pic_big_url")
		  ->from("SHOWCASE_DIGITAL_STYLE")
		  ->where("pic_big_url IS NOT NULL", null, false)
		  ->order_by("style_id")
		  ->get()->result_array();

		$request = [
		  'img_type' => 'beauty_shot',
		  'file_format' => 'jpg',
		  'include_filename' => true
		];

		foreach($q as $showcase_style){
			$old_dir = str_replace($this->file_directory->load_showcase_path, $this->file_directory->save_showcase_path, $showcase_style['pic_big_url']);
			if( strpos($old_dir, 'placeholder') !== false ) continue;
			$request['status'] = 'save';
			$request['product_type'] = Digital;
			$request['product_id'] = $showcase_style['style_id'];
			$new_dir = $this->file_directory->image_src_path($request);
			echo "cp $old_dir $new_dir <br>";
			$num_mv += 1;
		}

		echo "$num_mv Digital beauty shots moved";
	}

	function fix_styles_thumbnails(){
		$num_mv = 0;

//    	Items
		$q = $this->db
		  ->select("id as item_id, style_id, pic_big_url")
		  ->from("SHOWCASE_DIGITAL_STYLE_ITEMS S")
		  ->where("pic_big_url IS NOT NULL", null, false)
		  ->where("pic_big_url !=", '')
		  ->order_by("style_id")
		  ->get()->result_array();

		$request = [
//		  'img_type' => 'beauty_shot',
		  'file_format' => 'jpg',
		  'include_filename' => true
		];

		foreach($q as $showcase_style_item){
			$old_dir = str_replace($this->file_directory->load_showcase_path, $this->file_directory->save_showcase_path, $showcase_style_item['pic_big_url']);
			$request['status'] = 'save';
			$request['img_type'] = 'big';
			$request['product_type'] = Digital;
			$request['product_id'] = $showcase_style_item['style_id'];
			$request['item_id'] = $showcase_style_item['item_id'];
			$new_dir = $this->file_directory->image_src_path($request);
			echo "cp $old_dir $new_dir <br>";
			$num_mv += 1;
		}

		echo "$num_mv Digital item thumbnails moved";
	}

	function fix_separate_files()
	{
		$this->fix_firecode_files();
		$this->fix_abrasion_files();
		$this->fix_product_files();
		$this->fix_memotag_files();
	}

	function fix_firecode_files()
	{
//    	Firecodes
		$num_firecode_files = 0;

		$q = $this->db
		  ->select("PF.product_id, PFF.url_dir")
		  ->from("T_PRODUCT_FIRECODE_FILES PFF")
		  ->join("T_PRODUCT_FIRECODE PF", "PFF.firecode_id = PF.id")
		  ->order_by("PF.product_id")
		  ->group_by("PFF.firecode_id")
		  ->get()->result_array();

		$request = [
		  'status' => 'local_save',
		  'img_type' => 'firecodes',
		  'product_type' => Regular
		];

		foreach ($q as $row) {
			$filename = str_replace("files/products/firecodes/", "", $row['url_dir']);
			$old_dir = '/home/opuzen/app.opuzen.com/pms/' . $row['url_dir'];
			$request['product_id'] = $row['product_id'];
			$new_dir = $this->file_directory->image_src_path($request) . $filename;
			echo "cp $old_dir $new_dir <br>";
			$num_firecode_files += 1;
		}

		echo "$num_firecode_files Firecode files moved";
	}

	function fix_abrasion_files()
	{

//	    Abrasions
		$num_abrasion_files = 0;

		$q = $this->db
		  ->select("PF.product_id, PFF.url_dir")
		  ->from("T_PRODUCT_ABRASION_FILES PFF")
		  ->join("T_PRODUCT_ABRASION PF", "PFF.abrasion_id = PF.id")
		  ->order_by("PF.product_id")
		  ->group_by("PFF.abrasion_id")
		  ->get()->result_array();

		$request = [
		  'status' => 'local_save',
		  'img_type' => 'abrasions',
		  'product_type' => Regular
		];

		foreach ($q as $row) {
//			$file_format = explode('.', $firecode['url_dir'])[1];
			$filename = str_replace("files/products/abrasion/", "", $row['url_dir']);
			$old_dir = '/home/opuzen/app.opuzen.com/pms/' . $row['url_dir'];
			$request['product_id'] = $row['product_id'];
			$new_dir = $this->file_directory->image_src_path($request) . $filename;
			echo "cp $old_dir $new_dir <br>";
			$num_abrasion_files += 1;
		}

		echo "$num_abrasion_files Abrasion files moved";
	}

	function fix_product_files()
	{
//	    Product files

	}

	function fix_memotag_files(){
//	    Memotag pictures

	}


//    public function fix_filesystem(){
//	    $this->load->model('File_directory_model', 'file_directory');
//
//	    $this->fix_beauty_shots();
//	    $this->fix_thumbnails();
//	    $this->fix_separate_files();
//    }
//
//    function fix_beauty_shots(){
//	    $num_mv = 0;
//	    $q = $this->db
//	      ->select("product_id, product_type, pic_big_url")
//	      ->from("SHOWCASE_PRODUCT")
//	      ->where("pic_big_url IS NOT NULL", NULL, FALSE)
//	      ->get()->result_array();
//
//	    $request = [
//	        'img_type' => 'beauty_shot',
//		    'file_format' => 'jpg',
//		    'include_filename' => true
//	    ];
//
//	    foreach($q as $showcase_product){
//	    	$old_dir = str_replace($this->file_directory->load_showcase_path, $this->file_directory->save_showcase_path, $showcase_product['pic_big_url']);
//		    if( strpos($old_dir, 'placeholder') !== false ) continue;
//	    	$request['status'] = 'save';
//	    	$request['product_type'] = $showcase_product['product_type'];
//	    	$request['product_id'] = $showcase_product['product_id'];
//			$new_dir = $this->file_directory->image_src_path($request);
//			echo "cp $old_dir $new_dir <br>";
//		    $num_mv += 1;
//	    }
//
//	    echo "$num_mv beauty shots moved";
//    }
//
//    function fix_separate_files(){
////    	Abrasion
////	    Firecode
////	    Product files
////	    Memotag pictures
//
//    }

}