<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lists extends MY_Controller
{

    private $altfield_master_list_id = 370;

    function __construct()
    {
        parent::__construct();
        $this->thisC = 'lists';
        $this->load->library('table');
        $this->load->model('Lists_model', 'model');
        $this->load->model('Search_model', 'search');
        array_push($this->data['crumbs'], 'Lists');
        $this->data['printUrl'] = site_url('pricelist');
        $this->data['hasEditPermission'] = $this->hasPermission('lists', 'edit');

        // Tolerance when inserting batch items
        $this->max_tolerance = 1500;
    }

    public function index()
    {
        array_push($this->data['crumbs'], 'All');
        $this->data['ajaxUrl'] = site_url('lists/get_lists');
        //$this->load_datatables();
        $this->view('lists/list_all');
    }

    public function get_lists()
    {

        $table = array();
        $table['arr'] = array();
        $search = $this->input->post("search");
        
         
        if (strlen($search['value']) > 0) {
            $table = $this->model->get_available_lists(
                array(
                    'datatable' => true,
                    //                    'exclude_id' => [0], # exclude old master price list
                    'category_id' => ($this->hasPermission('lists', 'view') ? array() : array($this->category_lists_ids['ess'], $this->category_lists_ids['b&w'], $this->category_lists_ids['coll']))
                ),
                $search['value']
            );
            // echo "<pre>";
            // print_r($table);
            // die;
        }
        echo json_encode($this->return_datatables_data($table['arr'], $table));
    }

    public function master()
    {
        array_push($this->data['crumbs'], 'Master Price List');
        /*
		 * Prepare filter for the MPL generation
		 */
        $this->data['filters'] = $this->search->get_filter_options();
        foreach ($this->data['filters']['multiselect'] as $multiselect_name => $multiselect_values) {
            $this->data['filters']['multiselect'][$multiselect_name] = form_multiselect($multiselect_name . '[]', $multiselect_values, [0], " class='multi-dropdown w-filtering' tabindex='-1' ");
        }

        $this->view('lists/master_price_list_view');
    }

    public function edit($list_id = null)
    {
        $this->load->model('item_model', 'item_model');
        $this->data['isNew'] = is_null($this->input->post('lid')) && is_null($list_id);
        $this->data['lid'] = ($this->data['isNew'] ? $this->model->get_next_list_id() : (is_null($this->input->post('lid')) ? $list_id : intval($this->input->post('lid'))));
        $new_crumb = $this->data['isNew'] ? 'Create New' : 'Edit';
        array_push($this->data['crumbs'], $new_crumb);
        //$btnDelete = "<i class='fas fa-trash-alt btn-action btnDelete' aria-hidden='true' data-lid='".$this->data['lid']."' ></i>";
        //$this->data['btnDelete'] = ( $this->hasPermission('price_list', 'edit') ? $btnDelete : '' );
        //echo "============== LID ==================";
        //echo $this->data['lid'];
        //echo "============ FETCHED ==============";
        $this->data['info'] = $this->model->get_list_edit($this->data['lid']);
        //echo "<pre LINE: " . __LINE__ . " FILE: " . __FILE__ . ">";
        //print_r( $this->data['info'] ); 
        //echo "</pre>";


        $this->data['items'] = array();
        $this->data['products'] = array();

        if (!$this->data['isNew']) {
            $this->load->model('search_model', 'search');
            $this->data['items'] = $this->search->do_search(
                array(
                    'group_by' => item,
                    'select' => ['shelf', 'status', 'stock_status', 'width', 'content_front', 'outdoor', 'price', 'costs', 'stock', 'showcase', 'url_title', 'in_ringset'],
                    'list' => array(
                        'id' => $this->data['lid'],
                        'archived' => $this->data['info']['archived'] === 'Y',
                        'item_info' => true
                    ),
                    //                    'includeVendorAbrev' => true,
                    //                    'includeDiscontinued' => true,
                    //                    'includeArchived' => true // archived items!
                )
            );
        }

        //echo "<pre LINE: " . __LINE__ . ">";
        //print_r( $this->data['items'] ); 
        //echo "</pre>";

        // Set dropdowns data
        $options = array();
        $selected = array();
        // Common dropdowns
        $this->load->model('Specs_model', 'specs');

        $l = $this->specs->get_lists_category();
        $options = $this->decode_array($l, 'id', 'descr');
        //$options[0] = 'None selected';
        $selected = explode(constant('delimiter'), $this->data['info']['category_id']);
        $this->data['dropdown_category'] = form_multiselect('category[]', $options, set_value('category[]', $selected), " class='multi-dropdown' ");

        $l = $this->specs->get_lists_showrooms();
        $options = $this->decode_array($l, 'id', 'name');
        //$options[0] = 'None selected';
        $selected = explode(constant('delimiter'), $this->data['info']['showroom_id']);
        $this->data['dropdown_showroom'] = form_multiselect('showroom[]', $options, set_value('showroom[]', $selected), " class='multi-dropdown' ");

        $this->data['archived'] = false;
        $this->view('lists/form/view');
    }

    public function save_list()
    {
        //    	var_dump($_POST); exit;
        $errors = array();
        $lid = $this->input->post('lid');
        $list_name = $this->input->post('list_name');
        //$short_name = $this->input->post('s_list_name');
        $categories = $this->input->post('category');
        $active = $this->input->post('active') === null ? 'N' : ($this->input->post('active') === 'on' ? 'Y' : 'N');
        $showrooms = $this->input->post('showroom');
        $items = json_decode($this->input->post('items'), true);
        //$products = json_decode($this->input->post('products'));

        //var_dump($_POST); var_dump($active); exit;

        if (!strlen($list_name) > 0) {
            $e = "List name is invalid.";
            array_push($errors, $e);
        }

        if (empty($errors)) {
            $this->db->trans_begin();

            $data = array(
                'name' => $list_name,
                //'abrev' => $short_name,
                'notes' => '',
                'user_id' => $this->data['user_id'],
                'active' => $active
                // 				,'archived' => 'N'
            );

            if ($lid == '0') {
                $lid = $this->model->save_list($data);
            } else {
                $this->model->save_list($data, $lid);
            }

            $deletes = 0;
            $counter = 1;
            $update_data = [];
            # Assuming $items is sorted by the n_order value
            foreach ($items as $i) {
                if (isset($i['deleted']) && $i['deleted'] === '1') {
                    // Delete
                    $this->model->delete_item($lid, $i['item_id']);
                    $deletes++;
                } else {
                    // Replace in database - there might be some changes such as the n_order, active, ringset, big_piece
                    unset($i->deleted);
                    $i['list_id'] = $lid;
                    $i['n_order'] = $counter; // intval($i->n_order) - $deletes;
                    $i['date_modif'] = parse_str(date('Y-m-d H:i:s'));
                    if (!isset($i['p_res_cut'])) $i['p_res_cut'] = null;
                    //	                if(!isset($i['p_hosp_cut'])) $i['p_hosp_cut'] = null;
                    if (!isset($i['p_hosp_roll'])) $i['p_hosp_roll'] = null;
                    //                    $this->model->add_one_item_to_list($i);
                    $update_data[] = $i;
                    $counter++;
                }
            }
            $this->model->update_list_item($update_data);
            //            var_dump("update_list_item");

            /*
            $deletes = 0;
            foreach( $products as $i ){
              if( isset($i->deleted) && $i->deleted === '1' ){
                // Delete
                $this->model->delete_product($lid, $i->product_id, $i->product_type);
                $deletes++;
              }
              else {
                // Replace in database - there might be some changes such as the n_order, active, ringset, big_piece
                $i->list_id = $lid;
                if( $deletes > 0 ) $i->n_order = intval($i->n_order) - $deletes;
                $this->model->add_one_product_to_list($i);
              }
            }
            */

            $this->model->clean_list_categories($lid);
            if (is_array($categories) && count($categories) > 0) {
                $data = array();
                foreach ($categories as $c) {
                    $aux = array(
                        'list_id' => $lid,
                        'category_id' => $c
                    );
                    array_push($data, $aux);
                }
                $this->model->save_list_category($data, $lid);
            }
            //            var_dump("clean_list_categories");

            $this->model->clean_list_showrooms($lid);
            if (is_array($showrooms) && count($showrooms) > 0) {
                $data = array();
                foreach ($showrooms as $c) {
                    $aux = array(
                        'list_id' => $lid,
                        'showroom_id' => $c
                    );
                    array_push($data, $aux);
                }
                $this->model->save_list_showrooms($data, $lid);
            }
            //            var_dump("save_list_showrooms");

            /*
                Clean some data!
            */

            $this->load->model('maintenance_model', 'maintenance');
            $this->maintenance->clean_data_from_innexisting_lists();
            //            var_dump("clean_data_from_innexisting_lists");

        }

        if ($this->db->trans_status() === FALSE) {
            $ret['status'] = 'error';
            $ret['message'] = 'Some error during the saving ocurred. ' . implode("\n", $this->db->error());
            $this->db->trans_rollback();
        } else if (!empty($errors)) {
            $this->db->trans_rollback();
            $ret['status'] = 'error';
            $ret['message'] = ul($errors, $this->error_ul_attr);
        } else {
            $this->db->trans_commit();
            $ret['status'] = 'OK';
            $ret['continueUrl'] = site_url('lists');
        }
        echo json_encode($ret);
    }

    public function archive_list()
    {
        $list_id = $this->input->post('list_id');
        if (is_numeric($list_id)) {
            $success = $this->model->archive_list($list_id);
            $ret = array(
                'success' => $success, //$this->db->affected_rows() > 0,
                'continueUrl' => site_url('lists')
            );
            echo json_encode($ret);
        }
    }

    public function retrieve_list()
    {
        $list_id = $this->input->post('list_id');
        if (is_numeric($list_id)) {
            $success = $this->model->retrieve_list($list_id);
            $ret = array(
                'success' => $success, //$this->db->affected_rows() > 0,
                'continueUrl' => site_url('lists')
            );
            echo json_encode($ret);
        }
    }

    public function preview()
    {
        /*
            For modal that appears after selecting items!!
            1. show lists
            2. user will selecte one
            3. items that had been pre-selected will be inserted in the selected list
        */
        $l = $this->model->get_available_lists();
        $table = array();
        foreach ($l as $row) {
            $n = $row;
            $n['action'] = "<i class='btn far fa-plus-square btn-action btnSelectList' aria-hidden='true' data-list-id='" . $row['id'] . "'></i>";

            array_push($table, $n);
        }

        $data['tableData'] = $table;
        echo json_encode($data);
    }

    // Print
    // 	public function price_list($list_id, $group_by=product, $order_by=null, $load_view=true){
    // 		return $this->price_list_lib($list_id, $group_by, $order_by, $load_view);
    // 	}

    // Special price lists
    //   public function altfield(){
    //     $this->get_query = true;
    //     $this->price_list(0, product, null, false);
    //   }


    public function edit_item_in_list()
    {
        $batch = $this->input->post('batch');
    }

    public function toggle_status()
    {
        $forwhat = $this->input->post('forwhat');
        $list_id = $this->input->post('list_id');
        $item_id = $this->input->post('item_id');
        $new_status = ($this->input->post('new_status') === 'true' ? 1 : 0);

        switch ($forwhat) {
            case 'active':
                $data['success'] = $this->model->toggle_item_status($list_id, $item_id, $new_status);
                break;
            case 'big-piece':
                $data['success'] = $this->model->toggle_big_piece_status($list_id, $item_id, $new_status);
                break;
        }
        echo json_encode($data);
    }

    /*
      public function delete_item(){
          $list_id = $this->input->post('list_id');
          $item_id = $this->input->post('item_id');

          $data['success'] = $this->model->delete_item($list_id, $item_id);

          echo json_encode($data);
      }
      */

    public function preview_add_items()
    {
        $list = array();
        $list['arr'] = array();
        $search = $this->input->post('search');
        $list_id = $this->input->post('list_id');

        if (strlen($search['value']) > 0) {
            $list = $this->search->do_search([
                'select' => ['shelf', 'status', 'stock_status', 'price', 'costs', 'in_ringset'],
                'list' => ['exclude_id' => [$list_id]],
                'includeDiscontinued' => true,
                'model_table' => $this->model->t_item,
                'datatable' => true
            ]);
        }
        echo json_encode($this->return_datatables_data($list['arr'], $list));
    }

    public function preview_add_lists()
    {
        $list = array();
        $list['arr'] = array();
        $search = $this->input->post('search');
        $this_list_id = $this->input->post('list_id');

        if (strlen($search['value']) > 0) {
            $list = $this->model->get_available_lists([
                'exclude_id' => [$this_list_id],
                'datatable' => true
            ]);
        }
        echo json_encode($this->return_datatables_data($list['arr'], $list));
    }

    public function add_item_to_list()
    {
        $this->load->model('Item_model', 'item_model');
        $item_id = $this->input->post('item_id'); // may be a batch of items
        $list_id = $this->input->post('list_id');
        $batch = array();

        //        $item = $this->input->post('item');

        if ($this->hasPermission('lists', 'edit')) {

            $this_list_items = $this->search->do_search([
                'list' => ['id' => $list_id],
                'includeDiscontinued' => true
            ]);
            $item_ids_in_list = array_column($this_list_items, 'item_id');
            $n_order = intval($this->model->get_next_order($list_id));

            if (is_array($item_id) && count($item_id) <= $this->max_tolerance) {

                foreach ($item_id as $id) {
                    if (!in_array($id, $item_ids_in_list)) {
                        $aux = array(
                            'list_id' => $list_id,
                            'item_id' => $id,
                            'n_order' => $n_order,
                            'user_id' => $this->data['user_id']
                        );
                        array_push($batch, $aux);
                        $n_order++;
                    }
                }
                if (!empty($batch)) {
                    $this->model->add_batch_item_to_list($batch);
                }
            } else if (!is_array($item_id) && !in_array($item_id, $item_ids_in_list)) {
                $arr = array(
                    'list_id' => $list_id,
                    'item_id' => $item_id,
                    'n_order' => $n_order,
                    'user_id' => $this->data['user_id']
                );
                $this->model->add_one_item_to_list($arr);
            }
        }
        $data['batch'] = $batch;
        $data['n_order'] = intval($this->model->get_next_order($list_id));;
        $data['success'] = ($this->hasPermission('lists', 'edit'));
        echo json_encode($data);
    }

    public function get_ringset()
    {
        //$this->load->model('Item_model', 'item_model');
        $product_id = $this->input->post('product_id');
        $product_type = $this->input->post('product_type');
        //$to_list_id = $this->input->post('list_id');
        $valid = false;

        if ($this->hasPermission('lists', 'edit')) {
            if (!is_null($product_id) && !is_null($product_type)) {
                $valid = true;
                $data['items'] = $this->search->do_search(
                    [
                        'product_ids' => array($product_type => array($product_id)),
                        'restrictType' => array($product_type),
                        'select' => ['shelf', 'status', 'stock_status', 'price', 'costs', 'in_ringset'],
                        'in_ringset' => true
                        //                   'includePrice' => true,
                        //                   'includeCosts' => true 
                    ]
                );
                /*
                //$to_list_arr = $this->search->do_search(array( 'list_id'=>array('id'=>$to_list_id, 'active'=>true) ) );
                //$to_list_item_ids = array_column($to_list_arr, 'item_id');
                //var_dump($from_list_arr);exit;
                $data['items'] = array();
                $batch = array();

        //$n_order = intval( $this->model->get_next_order($to_list_id) );
        $counter = 0;
                foreach($from_list_arr as $row){
                    if( !in_array($row['item_id'], $to_list_item_ids) ){
                        $aux = array(
                            'list_id' => $to_list_id,
                            'item_id' => $row['item_id'],
              'n_order' => $n_order,
                            'user_id' => $this->data['user_id']
                        );
                        array_push($batch, $aux);
                        $counter++;
                        if($counter > $this->max_tolerance){
                            break;
                        }
                        $row['n_order'] = $n_order;
                        array_push($data['items'], $row);

                        $n_order++;
                    }
                }
                if( !empty($batch) && count($batch)<=$this->max_tolerance ){
                    $this->model->add_batch_item_to_list($batch);
                }
        */
            }
        }
        $data['success'] = $valid;
        echo json_encode($data);
    }

    public function add_list_to_list()
    {
        $this->load->model('Item_model', 'item_model');
        $from_list_id = $this->input->post('from_list_id');
        $to_list_id = $this->input->post('to_list_id');
        $valid = false;

        if ($this->hasPermission('lists', 'edit')) {
            if (!is_null($from_list_id) && !is_null($to_list_id)) {
                $data['item'] = array();
                //         $data['product'] = array();
                // 				$batch = array();
                $valid = true;

                // Process Items
                $from_list_arr['item'] = $this->search->do_search([
                    'select' => ['status', 'stock_status', 'price', 'costs'],
                    'list' => array('id' => $from_list_id, 'active' => true, 'item_info' => true),
                    'group_by' => item,
                    //           'order_by'=>'ListItems.n_order ASC',
                    //           'select' => ['price', 'costs']
                    //           'includePrice' => true, 'includeCosts'=>true, 
                ]);
                $to_list_arr['item'] = $this->search->do_search([
                    'list' => array('id' => $to_list_id, 'active' => true),
                    'group_by' => item
                ]);
                $to_list_item_ids = array_column($to_list_arr['item'], 'item_id');
                //$n_order = intval( $this->model->get_next_order($to_list_id) );
                foreach ($from_list_arr['item'] as $row) {
                    if (!in_array($row['item_id'], $to_list_item_ids)) {
                        array_push($data['item'], $row);
                    }
                }
            }
        }
        $data['success'] = $valid;
        echo json_encode($data);
    }

    public function get_lists_items_for_memotag_printer()
    {
        $list_id = $this->input->post('list_id');
        $this->data['items'] = $this->search->do_search([
            'list' => array('id' => $list_id, 'active' => true, 'item_info' => true),
            'select' => false,
            'group_by' => item,
            'order_by' => 'n_order'
            //            'order_by' => 'product_name'
        ]);
        //     var_dump( $this->data['items'] ); exit;
        echo json_encode($this->data['items']);
    }

    public function sourcebook()
    {
        $this->load_jquery();
        $this->load_bootstrap();
        $this->load->view('lists/print/sourcebook', $this->data);
    }

    public function get_sourcebook()
    {
        $sourcebook_list_ids = [716, 715, 713, 709, 704, 703, 700];
        //	    $sourcebook_list_ids = [715, 716];
        //	    $sourcebook_list_ids = [700, 703];
        $ret = [
            'data' => $this->prepare_sourcebook_data($sourcebook_list_ids)
        ];
        echo json_encode($ret);
    }

    public function analyze_sourcebook()
    {
        $this->load->library('table');
        //	    $sourcebook_list_ids = [716, 715, 713, 709, 704, 703, 700];
        $sourcebook_list_ids = [703, 700];
        $data = $this->prepare_sourcebook_data($sourcebook_list_ids);

        //	    echo "<pre>"; var_dump($data); return;
        $output_keys = [
            'product_name',
            'item_id',
            'color',
            'v_code',
            'code',
            'vendor_color',
            'vendor_code',
            'graffiti_free',
            'content',
            'status',
            'n_order',
            'pic_url'
        ];

        $html = "";
        foreach ($data as $list_name => &$_list_data) {
            $list_data = $this->grab_desired_keys_from_2d_arr($_list_data, $output_keys);

            $this->table->set_heading(array_keys($list_data[0]));
            $html .= "<h1>$list_name</h1>";
            $html .= $this->table->generate($list_data);
        }
        echo $html;
    }

    public function sourcebook_missing_image()
    {
        $this->load->library('table');
        $sourcebook_list_ids = [716, 715, 713, 709, 704, 703, 700];
        $data = $this->prepare_sourcebook_data($sourcebook_list_ids);

        //	    echo "<pre>"; var_dump($data); return;
        $output_keys = ['pic_url', 'product_name', 'item_id', 'color', 'code',];
        //		    'v_code', 'vendor_color', 'vendor_code',
        //		    'graffiti_free', 'content', 'status', 'n_order', ];

        $ret = [];
        foreach ($data as $list_name => &$_list_data) {
            $list_data = $this->grab_desired_keys_from_2d_arr($_list_data, $output_keys);
            $list_data = $this->search_word_in_key($list_data, 'pic_url', 'placeholder');
            $ret[] = $list_data;
        }
        $ret = flatten_array($ret);
        $ret = sort_by_key($ret, 'product_name');

        $this->table->set_heading(array_keys($ret[0]));
        $html = $this->table->generate($ret);

        echo "<h1>Missing Image</h1>" . $html;
    }

    public function sourcebook_missing_code()
    {
        $this->load->library('table');
        $sourcebook_list_ids = [716, 715, 713, 709, 704, 703, 700];
        $data = $this->prepare_sourcebook_data($sourcebook_list_ids);

        $output_keys = [
            'pic_url',
            'product_name',
            'item_id',
            'color',
            'code',
            'v_code',
            'vendor_color',
            'vendor_code',
        ];
        //		    'graffiti_free', 'content', 'status', 'n_order', ];

        $ret = [];
        foreach ($data as $list_name => &$_list_data) {
            $list_data = $this->grab_desired_keys_from_2d_arr($_list_data, $output_keys);
            $list_data = $this->search_word_in_key($list_data, 'v_code', '(missing)');
            $ret[] = $list_data;
        }
        $ret = flatten_array($ret);
        $ret = sort_by_key($ret, 'product_name');

        $this->table->set_heading(array_keys($ret[0]));
        $html = $this->table->generate($ret);

        echo "<h1>Missing Code</h1>" . $html;
    }

    private function search_word_in_key(&$arr, $key, $needle)
    {
        $ret = [];
        foreach ($arr as $row) {
            if (strpos($row[$key], $needle) !== false) {
                $ret[] = $row;
            }
        }
        return $ret;
    }

    private function evaluate_sourcebook_content_in_place(&$array, $new_field_name = 'content')
    {
        foreach ($array as &$a) {
            if ($a['content_front'] == '100% Polyurethane') {
                $a[$new_field_name] = "Polyurethane";
            } else if (strpos($a['product_name'], "Texuede") !== false) {
                $a[$new_field_name] = "Microfiber";
            } else {
                $a[$new_field_name] = "Vinyl";
            }
        }
    }

    private function evaluate_sourcebook_graffiti_free_in_place(&$array, $new_field_name = 'graffiti_free')
    {
        foreach ($array as &$a) {
            if (strpos($a['finish'], 'Graffiti Free') !== false) {
                if (strpos($a['finish'], 'optional') !== false) {
                    $a[$new_field_name] = 0;
                } else {
                    $a[$new_field_name] = 1;
                }
            } else {
                $a[$new_field_name] = 0;
            }
        }
    }

    private function evaluate_sourcebook_code(&$array, $new_field_name = 'v_code')
    {
        $field_to_test_agains = ['code', 'vendor_code', 'vendor_color'];
        $regex = '/[A-Z]{2}-[0-9]{3}/';
        foreach ($array as &$a) {
            foreach ($field_to_test_agains as $k) {
                $match = preg_match($regex, $a[$k]);
                //    			var_dump($k, $a[$k], $match); echo "<br>";
                if ($match == 1) {
                    $a[$new_field_name] = $a[$k];
                    continue;
                }
            }
            if (!array_key_exists($new_field_name, $a)) {
                $a[$new_field_name] = "(missing)";
            }
        }
    }

    private function grab_desired_keys_from_2d_arr(&$array, &$desired_keys)
    {
        $ret = [];
        foreach ($array as &$a) {
            $ret[] = $this->grab_desired_keys_from_1d_arr($a, $desired_keys);
        }
        return $ret;
    }

    private function grab_desired_keys_from_1d_arr(&$array, &$desired_keys)
    {
        $aux = [];
        foreach ($array as $key => &$val) {
            if (in_array($key, $desired_keys)) {
                $aux[$key] = $val;
            }
        }
        return $aux;
    }

    private function prepare_sourcebook_data($sourcebook_list_ids)
    {
        $square_placeholder = asset_url() . "/images/placeholder_square.jpg";
        $data = $this->search->do_search([
            'list' => array('id' => $sourcebook_list_ids, 'active' => true, 'item_info' => true),
            'select' => ['showcase', 'vendor_item_name', 'finish', 'content_front'],
            'group_by' => item,
            'order_by' => 'list_id'
        ]);
        $lists_data = $this->model->get_list($sourcebook_list_ids);
        $lists_id = array_column($lists_data, 'id');
        $lists_names = array_column($lists_data, 'name');

        $clean_naming_sourcebook = function ($name) {
            //	    	$words = explode('-', $name);
            //	    	return $words[count(-1];
            return $name;
        };
        $lists_names = array_map($clean_naming_sourcebook, $lists_names);
        $list_id_2_name = array_combine($lists_id, $lists_names);

        $ret_data = [];

        $clean_pic_url = function ($url, $item_id, $type = "images_items/big/") {
            if ($url == 'N' or is_null($url)) {
                return asset_url() . "/images/placeholder_square.jpg";
            } else if ($url == 'Y') {
                return "https://www.opuzen.com/showcase/images/" . $type . $item_id . ".jpg";
            } else {
                return $url;
            }
        };

        foreach ($data as $i) {
            //			$i['pic_big'] = $i['pic_big'] == 'N' ? $square_placeholder : $i['pic_big'];
            //			$i['pic_hd'] = $i['pic_hd'] == 'N' ? $square_placeholder : $i['pic_hd'];
            //			$i['pic_big'] = $clean_pic_url($i['pic_big'], $i['item_id'], "images_items/big/");
            //		    $i['pic_hd'] = $clean_pic_url($i['pic_hd'], $i['item_id'], "images_items_hd/");
            $i['pic_url'] = $clean_pic_url($i['pic_big'], $i['item_id'], "images_items/big/");
            $list_name = $list_id_2_name[intval($i['list_id'])];

            if (!array_key_exists($list_name, $ret_data)) {
                $ret_data[$list_name] = [];
            }
            $ret_data[$list_name][] = $i;
        }

        foreach ($ret_data as &$data) {
            $data = sort_by_key($data, 'n_order');
            $this->evaluate_sourcebook_content_in_place($data, 'content');
            $this->evaluate_sourcebook_graffiti_free_in_place($data, 'graffiti_free');
            $this->evaluate_sourcebook_code($data, 'v_code');
        }

        return $ret_data;
    }
}
