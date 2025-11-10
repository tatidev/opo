<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller
{

    private $print_type; // options (null, digital_ground)
    private $basket_items;
    private $basket_ringsets;

    function __construct()
    {
        parent::__construct();
        $this->load_cart();
        //     var_dump($this->print_type); exit;
    }

    function see_session()
    {
        $this->return_ajax_cart_data();
    }

    private function load_cart()
    {
        $this->print_type = (isset($_SESSION['print_type']) && !empty($_SESSION['print_type']) ? $_SESSION['print_type'] : null);
        $this->basket_items = (isset($_SESSION['basket_items']) && !empty($_SESSION['basket_items']) ? $_SESSION['basket_items'] : array());
        $this->basket_ringsets = (isset($_SESSION['basket_ringsets']) && !empty($_SESSION['basket_ringsets']) ? $_SESSION['basket_ringsets'] : array());
    }

    private function save_cart()
    {
        $_SESSION['print_type'] = $this->print_type;
        $_SESSION['basket_items'] = $this->basket_items;
        $_SESSION['basket_ringsets'] = $this->basket_ringsets;
    }

    private function count_cart()
    {
        return count($this->basket_items) + count($this->basket_ringsets);
    }

    public function empty_cart($ajax = true)
    {
        $_SESSION['basket_items'] = array();
        $_SESSION['basket_ringsets'] = array();
        if ($ajax) {
            echo json_encode($_SESSION);
        }
    }

    /*********
     * Ajax Methods
     *********/

    public function set_cart()
    {
        $this->print_type = $this->input->post('print_type');
        $this->basket_items = $this->input->post('basket_items');
        $this->basket_ringsets = $this->input->post('basket_ringsets');
        $this->save_cart();
        $this->return_ajax_cart_data();
    }

    public function add()
    {
        $basket_type = $this->input->post('basket_type');
        switch ($basket_type) {
            case constant('items'):
                $this->add_item();
                break;
            case constant('ringsets'):
                $this->add_ringset();
                break;
        }
        $this->save_cart();
        $this->return_ajax_cart_data();
    }

    /* Ajax call to add item to cart */
    private function add_item()
    {
        $product_type = $this->input->post('product_type');
        $item_id = $this->input->post('item_id');
        $qty = $this->input->post('qty');
        array_push($this->basket_items, array(
                'product_type' => $product_type,
                'item_id' => $item_id,
                'qty' => $qty)
        );
    }

    /* Ajax call to add ringset to cart */
    private function add_ringset()
    {
        $product_type = $this->input->post('product_type');
        $product_id = $this->input->post('product_id');
        $qty = $this->input->post('qty');
        array_push($this->basket_ringsets, array(
                'product_id' => $product_id,
                'product_type' => $product_type,
                'qty' => $qty)
        );
    }

    /* Ajax call to remove one item or ringset */
    public function remove()
    {
        $basket_type = $this->input->post('basket_type');
        $found = false;

        switch ($basket_type) {
            case constant('items'):
                $given_product_type = $this->input->post('product_type');
                $given_item_id = $this->input->post('item_id');
                $n = 0;
                foreach ($this->basket_items as $i) {
                    if ($i['item_id'] === $given_item_id && $i['product_type'] === $given_product_type) {
                        $found = true;
                        if (intval($i['qty']) === 1) {
                            unset($this->basket_items[$n]);
                        } else if (intval($i['qty']) > 1) {
                            $i['qty'] = $i['qty'] - 1;
                        }
                    }
                    $n++;
                    if ($found) {
                        break;
                    }
                }
                break;

            case constant('ringsets'):
                $given_product_type = $this->input->post('product_type');
                $given_product_id = $this->input->post('product_id');
                $n = 0;
                foreach ($this->basket_ringsets as $i) {
                    if ($i['product_id'] === $given_product_id && $i['product_type'] === $given_product_type) {
                        $found = true;
                        if (intval($i['qty']) === 1) {
                            unset($this->basket_ringsets[$n]);
                        } else if (intval($i['qty']) > 1) {
                            $i['qty'] = $i['qty'] - 1;
                        }
                    }
                    $n++;
                    if ($found) {
                        break;
                    }
                }
                break;
        }

        $this->save_cart();
        $this->return_ajax_cart_data();
    }

    /*
        Print items in cart
    */

    private function return_ajax_cart_data()
    {
        $ret['qty'] = $this->count_cart();
        echo json_encode($ret);
    }

    public function checkout()
    {
        $this->load->library('table');
        $this->load->model('product_model', 'product_model');
        $this->load->model('item_model', 'item_model');
        $this->data['collectionRows'] = array();
        $this->data['collectionToShow'] = array();
        $this->patterns_looped = array(); // to avoid extra calls to DB for retrieving technical info for each pattern

        foreach ($this->basket_items as $s) {
            $this->process_item_for_view($s);
        }

        foreach ($this->basket_ringsets as $r) {
            $product_id = $r['product_id'];
            $product_type = $r['product_type'];
            // First, get all the ringsets 'item_id' for this product_id
            $items_assoc = $this->product_model->get_ringset_items($product_type, $product_id);
            // Despues los puedo procesar como items individuales como hice con los anteriores
            foreach ($items_assoc as $s) {
                $s['qty'] = 1;
                $this->process_item_for_view($s);
            }
        }

        //$this->empty_cart(false);
	    if($this->print_type == 'digital_ground'){
	    	// PHP 5.3
		    //usort($this->data['collectionToShow'], function ($item1, $item2) {
			//    if ($item1['Pattern'] == $item2['Pattern']) return 0;
			//    return $item1['Pattern'] < $item2['Pattern'] ? -1 : 1;
		    //});

		    // PHP 7+
		    usort($inventory, function ($item1, $item2) {
			    return $item1['price'] <=> $item2['price'];
		    });
	    }

        $this->load->view('cart/print', $this->data);
    }

    private function process_item_for_view($s)
    {
        if (!isset($s['product_id']) or !isset($s['product_type'])) {
            return;
        }
        $product_id = $s['product_id'];
        $product_type = $s['product_type'];
        $index = $product_type . $product_id;

        if (empty($this->patterns_looped)) {
            //array_push($this->patterns_looped, $q);
            $q = $this->product_model->get_product_info_for_tag($s['product_type'], $s['product_id']);
            $this->patterns_looped[$index] = $q;
        } else {
            $found = isset($this->patterns_looped[$index]);
            if (!$found) {
                $q = $this->product_model->get_product_info_for_tag($s['product_type'], $s['product_id']);
                $this->patterns_looped[$index] = $q;
            } else {
                // We found it in the saved array
                $q = $this->patterns_looped[$index];
            }
        }
// 		echo "<pre>"; var_dump($this->db->last_query()); exit;
// 		echo "<pre>"; var_dump($q, $this->print_type); exit;
        // Process each tech data for the view
        // Get the product image if any
        $url_dir = null;
        $data = explode(constant('delimiterFiles'), $q['files']);
        if ($data[0] !== '') {
            foreach ($data as $d) {
                $aux = explode('#', $d);
                if ($aux[3] === $this->category_files_ids['memotags_picture']) {
                    // Picture found!
                    $url_dir = $aux[0];
                }
            }
        }
        $this->data['print_type'] = $this->print_type;
        // Product name
        if ($this->print_type === 'digital_ground') {
            $q['product_name'] = (!empty($q['dig_product_name']) ? $q['dig_product_name'] : $q['product_name']);
            $q['width'] = (!empty($q['dig_width']) ? $q['dig_width'] : $q['width']);
        }

        // Repeat
        $repeat = '';
        if ($this->is_valid_spec_str($q['hrepeat'])) {
            $repeat = 'H: ' . $q['hrepeat'] . '&quot;';
            if ($this->is_valid_spec_str($q['vrepeat'])) {
                $repeat .= ' / V: ' . $q['vrepeat'] . '&quot;';
            }
        } else if ($this->is_valid_spec_str($q['vrepeat'])) {
            $repeat = 'V: ' . $q['vrepeat'] . '&quot;';
        }

        // Width
        $width = '';
        if ($this->is_valid_spec_str($q['width'])) {
            $width .= str_replace('.00', '', $q['width']) . '&quot;';
        }

        // Outdoor bool
        $outdoor = ($q['outdoor'] === 'Y' ? true : false);

        $contentArr = explode(' / ', $q['content_front']);
        $contentBackArr = explode(' / ', $q['content_back']);
        $firecodeArr = explode('/', $q['firecodes']);

        $finishArr = [];
        $finishAux = explode('/', $q['finishs']);
        foreach ($finishAux as $f) {
            array_push($finishArr, trim($f));
//            if (strpos(strtolower($f), 'stain') !== false || strpos(strtolower($f), 'black out') !== false) {
//                array_push($finishArr, $f);
//            }
        }

        $cleaningArr = [];
        $cleaningAux = explode('/', $q['cleanings']);
        foreach ($cleaningAux as $f) {
            if (strpos(strtolower($f), 'bleach') !== false) {
                array_push($cleaningArr, $f);
            }
        }

        $abrasionArr = array();
        $temp = explode('/', $q['abrasions']);
        //var_dump($temp);exit;
        if (!empty($temp[0])) {
            foreach ($temp as $t) {
                $abrasion = explode('*', $t);
                if (!in_array($abrasion[0], $this->special_cases['abrasion'])) {
                    $pc = explode('-', $abrasion[1]);
                    $limit = $pc[0];
                    $rubs = number_format($pc[1], 0);
                    $test = $pc[2];
                    $str = /*$limit.' '.*/
                        $rubs . ' ' . $test;
                    $str = str_replace('Unknown', ' ', $str);
                    array_push($abrasionArr, $str);
                }
            }
        }



        // Agrego todos los que se solicitaron de este mismo item
        $item = $this->item_model->get_item_info_for_tag($product_type, $s['item_id']);
        //var_dump($q); exit;
        for ($i = 0; $i < $s['qty']; $i++) {
            $data = array(
                'total_aval_colors' => $q['total_aval_colors'], //intval( $q['total_aval_colors'] ) > 1,
                'product_id' => $s['product_id'],
                'product_type' => $product_type,
                'product_name' => $product_type == Digital || !empty($this->print_type) ? $q['product_name'] : $item['product_name'],
                'item_id' => $s['item_id'],
                'color' => $item['color'],
                'code' => $item['code'],
                'status' => array('id' => $item['status_id'], 'isRunning' => in_array($item['status_id'], $this->item_model->product_status_running)),
                'width' => $width,
                'repeat' => $repeat,
                'uses_id' => $q['uses_id'],
                'contentArr' => $contentArr,
                'contentBackArr' => $contentBackArr,
                'abrasionArr' => $abrasionArr,
                'firecodeArr' => $firecodeArr,
                'finishArr' => $finishArr,
                'cleaningArr' => $cleaningArr,
                'outdoor' => $outdoor,
                'url_dir' => $url_dir
            );
            $this->addtoprint($data);
        }
    }

    // Process data so that is manipulated in the view
    private function addtoprint($data)
    {
        $row = array();
        $tag['product_type'] = $data['product_type'];
        $tag['outdoor'] = $data['outdoor'];
        $tag['picture'] = $data['url_dir'];

        /*
        $this->load->library('ciqrcode');
        $qr_name = $data['product_id'] . '-' . $data['product_type'] . '-' . $data['item_id'];
        $QRUrl = 'files/qr/'.$qr_name.'.png';
        if( !file_exists($QRUrl) ){
        $this->ciqrcode->generate(array(
        'data' => $qr_name,
        'level' => 'H',
        'size' => 3,
        'savename' => $QRUrl
        ));
        }
        */
        // Starting here, the order matters!

        //$tag['qr'] = '<img src="'.site_url().$QRUrl.'" />';
        if (!is_null($this->print_type)) {
            if ($_SESSION['print_type'] === 'digital_ground') {
                $tag['Item #'] = 'Digital Ground';
            }
        } else if ($tag['product_type'] === 'D') {
            $data['uses_id'] = explode(' / ', $data['uses_id']);
            if (in_array(strval($this->special_cases['uses_ids']['drapery']), $data['uses_id']) && in_array(strval($this->special_cases['uses_ids']['upholstery']), $data['uses_id'])) {
                $tag['Item #'] = 'Digitally Printed Drapery / Upholstery';
            } else if (in_array(strval($this->special_cases['uses_ids']['drapery']), $data['uses_id'])) {
                $tag['Item #'] = 'Digitally Printed Drapery';
            } else if (in_array(strval($this->special_cases['uses_ids']['upholstery']), $data['uses_id'])) {
                $tag['Item #'] = 'Digitally Printed Upholstery';
            } else if ($this->valid_to_show($data['code'])) {
                $tag['Item #'] = $data['code'];
            }
        } else if ($this->valid_to_show($data['code'])) {
            if (strlen($data['code']) === 9) {
                $tag['Item #'] = '#' . $data['code'];
            } else {
                $tag['Item #'] = $data['code'];
            }
        } else {
            $tag['Item #'] = '&nbsp;';
        }
        array_push($row, (isset($tag['Item #']) ? $tag['Item #'] : ''));

        if ($this->valid_to_show($data['status']) && intval($data['total_aval_colors']) > 1) {
            if ($data['status']['isRunning']) {
                $tag['total_aval_colors'] = "AVAILABLE IN " . $data['total_aval_colors'] . " COLORWAYS";
            } else {
                $tag['total_aval_colors'] = "AVAILABLE IN ADDITIONAL COLORWAYS";
            }
            $tag['status'] = $data['status'];
        }

        if ($this->valid_to_show($data['product_name'])) {
            $tag['Pattern'] = $data['product_name'];
        }
        array_push($row, (isset($tag['Pattern']) ? $tag['Pattern'] : ''));

        if ($this->valid_to_show($data['color'])) {
            $tag['Color'] = $data['color'];
        }
        array_push($row, (isset($tag['Color']) ? $tag['Color'] : ''));

        if ($this->valid_to_show($data['width'])) {
            $tag['Width'] = $data['width'];
        }
        array_push($row, (isset($tag['Width']) ? $tag['Width'] : ''));

        if ($this->valid_to_show($data['repeat'])) {
            $tag['Repeat'] = $data['repeat'];
        }
        array_push($row, (isset($tag['Repeat']) ? $tag['Repeat'] : ''));

        if ($this->valid_to_show($data['contentArr'])) {
            $aux = array();
            foreach ($data['contentArr'] as $c) {
                array_push($aux, $c);
            }
            $tag['Content'] = $aux;
        }
        array_push($row, (isset($tag['Content']) ? implode('/', $aux) : ''));

        if ($this->valid_to_show($data['contentBackArr'])) {
            $aux = array();
            foreach ($data['contentBackArr'] as $c) {
                array_push($aux, $c);
            }
            $tag['Backing'] = $aux;
        }
        array_push($row, (isset($tag['Backing']) ? implode('/', $aux) : ''));

        if ($this->valid_to_show($data['abrasionArr'])) {
            $aux = array();
            foreach ($data['abrasionArr'] as $c) {
                $c = str_replace(',', '&#44;', $c);
                array_push($aux, $c);
            }
            $tag['Abrasion'] = $aux;
        }
        array_push($row, (isset($tag['Abrasion']) ? implode('/', $aux) : ''));

        if ($this->valid_to_show($data['finishArr'])) {
            $aux = array();
            foreach ($data['finishArr'] as $c) {
                array_push($aux, $c);
            }
            $tag['Finish'] = $aux;
        }

        if ($this->valid_to_show($data['cleaningArr'])) {
            $aux = array();
            foreach ($data['cleaningArr'] as $c) {
                array_push($aux, $c);
            }
            $tag['Cleaning'] = $aux;
        }

        if ($this->valid_to_show($data['firecodeArr'])) {
            $aux = array();
            foreach ($data['firecodeArr'] as $c) {
                array_push($aux, $c);
            }
            $tag['Fire Rating'] = $aux;
        }
        array_push($row, (isset($tag['Fire Rating']) ? implode('/', $aux) : ''));

        array_push($row, (isset($tag['total_aval_colors']) ? $tag['total_aval_colors'] : ''));

        array_push($this->data['collectionRows'], $row);
        array_push($this->data['collectionToShow'], $tag);
    }

    // Validates if is needed to show in the memotag
    function valid_to_show($data)
    {
        if (is_array($data)) {
            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($this->is_invalid_text($d)) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            if ($this->is_invalid_text($data)) {
                return false;
            }
        }
        return true;
    }

    function is_invalid_text($str)
    {
        return $str === '0' || $str === '' || $str === 'N/A' || $str === 'Not Officially Tested';
// 		if($str === '0' || $str === '' || $str === 'N/A' || $str === 'Not Officially Tested'){
// 			return true;
// 		} else {
// 			return false;
// 		}
    }

}
