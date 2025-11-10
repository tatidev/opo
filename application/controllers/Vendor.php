<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendor extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->thisC = 'contact';
        $this->load->model('Vendor_model', 'model');
    }

    public function vendor()
    {
        array_push($this->data['crumbs'], 'Contacts', 'Vendors');
        $this->data['contactsUrl'] = site_url('contact/vendor');
        $this->data['ctype'] = constant('vendor');
        $this->view('vendor/list');
    }

    public function showroom()
    {
        array_push($this->data['crumbs'], 'Contacts', 'Showrooms');
        $this->data['contactsUrl'] = site_url('contact/showroom');
        $this->data['ctype'] = constant('showroom');
        $this->view('vendor/list');
    }

    public function get()
    {
        $this->filters = array(
            'ctype' => $this->input->post('ctype'),
            'show_discontinued' => ($this->input->post('show_discontinued') === 'Y' ? true : false),
            'datatable' => true
        );
        $list = $this->model->get_vendors($this->filters);
        echo json_encode($this->return_datatables_data($list['arr'], $list));
    }

    public function edit()
    {
        $data['vid'] = $this->input->post('vid');
        $data['isNew'] = $data['vid'] === '0';
        $data['ctype'] = $this->input->post('ctype');

        switch ($data['ctype']) {
            case constant('vendor'):
                $data['formTitle'] = 'Vendor Edit';
                break;

            case constant('showroom'):
                $data['formTitle'] = 'Showroom Edit';
                break;
        }

        $this->filters = array(
            'vendor_id' => $this->input->post('vid'),
            'ctype' => $data['ctype']
        );

        $data['info'] = $this->model->get_vendors($this->filters, false);
        $data['hasPermission'] = $this->hasPermission('contact', 'edit');
        //var_dump($data['info']);exit;
        $data['tbody_files'] = '';
        $files_to_encoded = array();
        if (!is_null($data['info']['files'])) {
            $files = explode(constant('delimiter2'), $data['info']['files']);
            foreach ($files as $f) {
                $this_file = explode(constant('delimiterFiles'), $f);
                $aux = array(
                    'url_dir' => $this_file[0],
                    'descr' => $this_file[1],
                    'date_add' => $this_file[2],
                    'user_id' => $this_file[3]
                );
                $data['tbody_files'] .= "
				<tr>
					<td><a href='" . $aux['url_dir'] . "' target='_blank'><i class='fa fa-file btnViewFile' aria-hidden='true'></i></a> " . $aux['descr'] . "</td>
					<td>" . nice_date($aux['date_add'], 'm-d-Y') . "</td>
					<td>
						" . ($this->data['user_id'] === $aux['user_id'] || $this->data['is_admin'] ? "<i class='fa fa-times-circle delete_temp_url' aria-hidden='true'></i>" : '') . "
					</td>
				</tr>";
                array_push($files_to_encoded, $aux);
            }
        }
        $data['files_encoded'] = json_encode($files_to_encoded);

        $ret['html'] = $this->load->view('vendor/form/view', $data, true);
        echo json_encode($ret);
    }

    public function save()
    {
        //var_dump($_POST);exit;

        $this->load->library('form_validation');
        $this->form_validation->set_rules($this->model->rules);

        if ($this->form_validation->run() == TRUE) {
            $this->load->model('File_directory_model', 'file_directory');
            $this->db->trans_begin();

            $data['ctype'] = $this->input->post('ctype'); // Vendor or Showroom constants
            $data['vid'] = $this->input->post('vid');
            $active = $this->input->post('active');

            $insert = array(
                'name' => $this->input->post('vendors_name'),
                'abrev' => strlen($this->input->post('vendors_abrev')) > 0 ? $this->input->post('vendors_abrev') : null,
                'active' => (!is_null($active) && $active === 'on' ? 'Y' : 'N'),
                'user_id' => $this->data['user_id']
            );

            if ($data['vid'] === '0') {
                // New
                $data['vid'] = $this->model->save($data['ctype'], $insert);
            } else {
                $this->model->save($data['ctype'], $insert, $data['vid']);
            }
            $insert['id'] = $data['vid'];

            // Save files
            $this->model->clean_vendor_files_logic($data['ctype'], $data['vid']);
            $arr = $this->input->post('files_encoded');
            $arr = (is_null($arr) ? array() : json_decode($arr));

            $batch = array();
            $n = 0;
            if (count($arr) > 0) {
                foreach ($arr as $i) { // Loop through each of the files
                    $n++;
                    // $i [x]
                    //	0: url_dir / 1: descr

                    // Change location of the file if necessary
                    $f = $i->url_dir; // $f becomes the Url
                    if (strpos($f, 'temp')) {
	                    $extension = explode('.', str_replace(site_url() . $this->file_directory->temp_folder, '', $f));
	                    $filename = $data['vid'] . '-' . url_title($i->descr . '-' . $i->date_add, '-', true) . '-' . $n . '.' . $extension[1];

	                    $location_request = [
	                      'status'=>'save',
	                      'product_type'=>$data['ctype'],
	                      'product_id'=>$data['vid'],
//	                      'item_id'=>$item_id,
	                      'img_type'=>'files',
	                      'include_filename'=>false,
	                      'file_format'=>$extension[1]
	                    ];
	                    $_new_location = $this->file_directory->image_src_path($location_request);
	                    $new_location = $_new_location . $filename;

	                    if (file_exists($new_location)) {
		                    // Move existing file
		                    $n++;
		                    $filename = $data['vid'] . '-' . url_title($i->descr . '-' . $i->date_add, '-', true) . '-' . $n . '.' . $extension[1];
		                    $new_location = $_new_location . $filename;
	                    }
	                    $location_request['status'] = 'load';
	                    $new_location_big_db = $this->file_directory->image_src_path($location_request);

	                    // Save current uploaded file
	                    rename(str_replace(site_url(), '', $f), $new_location);

//                    	**
//                        // Is a new file!
//                        $extension = explode('.', str_replace(site_url() . $this->file_directory->index('temp'), '', $f)); // [0] name / [1] extension .jpg or .pdf (whatever extension is)
//                        $new_name = $data['vid'] . '-' . url_title($i->descr . '-' . $i->date_add, '-', true) . '-' . $n;
//
//                        $new_location = $this->file_directory->index($data['ctype']) . '/' . $new_name . '.' . $extension[1];
//                        // Save new location
//                        rename(str_replace(site_url(), '', $f), $new_location);
                    } else {
                        // Existing file, don't relocate the file
                        $new_location = str_replace(site_url(), '', $f);
                    }

                    switch ($data['ctype']) {
                        case constant('vendor'):
                            $ret = array(
                                'vendor_id' => $data['vid'],
                                'url_dir' => $new_location,
                                'descr' => $i->descr,
                                'user_id' => $this->data['user_id']
                            );
                            break;

                        case constant('showroom'):
                            $ret = array(
                                'showroom_id' => $data['vid'],
                                'url_dir' => $new_location,
                                'descr' => $i->descr,
                                'user_id' => $this->data['user_id']
                            );
                            break;
                    }

                    array_push($batch, $ret);
                }
                $this->model->save_vendor_files($batch, $data['ctype'], $data['vid']);
            }
            $insert['count_files'] = $n;

            // Wrap up
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $ret['success'] = false;
                $ret['message'] = 'Some error ocurred during transaction.';
            } else {
                $this->db->trans_commit();
                $ret['item'] = $this->model->get_vendors(array('ctype' => $data['ctype'], 'vendor_id' => $data['vid']));
                $ret['success'] = true;
            }
        } else {
            $ret['success'] = false;
            $ret['message'] = validation_errors();
        }

        echo json_encode($ret);
    }

    public function archive()
    {
        $vid = $this->input->post('vid');
        $ctype = $this->input->post('ctype');
        if ($this->input->is_ajax_request()) {
            $result = $this->model->archive_vendor($ctype, $vid);
            $ret = array(
                'success' => true,
                'vid' => $vid
            );
            echo json_encode($ret);
        }
    }

    public function retrieve()
    {
        $vid = $this->input->post('vid');
        $ctype = $this->input->post('ctype');
        if ($this->input->is_ajax_request()) {
            $data = $this->model->retrieve_vendor($ctype, $vid);
            $ret = array(
                'success' => true,
                'vendor' => $data
            );
            echo json_encode($ret);
        }
    }

    public function typeahead_vendor_list()
    {
        $q = $this->input->get('query');
        $ctype = $this->input->get('ctype');
        $ret = $this->model->search_by_name($ctype, $q);
        if ($this->input->is_ajax_request()) {
            echo json_encode($ret);
        }
    }

}
