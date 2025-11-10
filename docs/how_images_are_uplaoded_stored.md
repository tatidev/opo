# how Product & Item images are uploaded / staored

### Product Image urls are saved to database as follows
```
Productmodel::save_showcase_basic($data, $product_id, $product_type);
```

A convertion from legacy file saves to S3 objects has been coded as follows:
```
# Product_controller::submit_form() updated 
    ...
    circa line 1610

		$new_location_db = $this->file_directory->image_src_path($location_request);
		// PKL Convert the new file location ($new_location_db) to S3 URL for Database insertion
		$S3_location = $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_db);
              $new_location_db = $S3_location;
		
		$tmp_file_pic_big = $this->input->post('pic_big_url');
		/*  ------------------------
		 Create S3 objects from uploads with the new fileuploadtos3 class
		 fileuploadtos3::SendUploadedTempFileToS3($tmp_file, $new_location);
		 Both Params should be web relative paths
		 ------------------------ */
		 $new_location    = str_replace($_SERVER['DOCUMENT_ROOT'],'',$new_location);
		 $this->fileuploadtos3->SendUploadedTempFileToS3($tmp_file_pic_big, $new_location); 

		// Legacy Save current uploaded file
		// rename(str_replace(site_url(), '', $f), $new_location);
		} else {
			// Existing file, don't relocate the file
			$new_location_db = $f;
			echo "EXISTING PRODUCT FILE: " . $new_location_db . "\n";
            // PKL convertLegacyImgSrcToS3() will not convert if $new_location_db 
            // is already an S3 Asset URL
			$S3_location = $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_db);
               $new_location_db = $S3_location;
		}
	...
```

### Item Image urls are saved to database with this model function
```
   Item_model::save_showcase_basic($data, $item_id, );
```

```
 # Item_controller:
    ...
    circa line 902

		$new_location_big_db = (strlen($new_location_big_db) > 0 ? $new_location_big_db : null);
		$new_location_hd_db = (strlen($new_location_hd_db) > 0 ? $new_location_hd_db : null);
					
		// PKL Convert the new file location ($new_location_big_db) to S3 URL for Database insertion
		   $S3_big_db_location = 
		       $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_big_db);
		// PKL Convert the new file location ($new_location_hd_db ) to S3 URL for Database insertion
		   $S3_hd_db_location = 
		       $this->fileuploadtos3->convertLegacyImgSrcToS3($new_location_hd_db );
		$ret = array(
		  'url_title' => strtolower(url_title($product_name) . 
		                            '/' . url_title(implode('-', lor_names))),
		  'visible' => $web_visible,
		  'pic_big_url' => $S3_big_db_location,
		  'pic_hd_url' => $S3_hd_db_location,
		  'user_id' => $this->data['user_id']
		);

		$this->model->save_showcase_basic($ret, $item_id);
	...
```