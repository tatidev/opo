<?php

class Logs_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /*

      METHODS
      Save logs methods

    */

    function log_product($product_type, $product_id)
    {

        switch ($product_type) {
            case constant('Regular'):
                $h = $this->th_product;
                $o = $this->t_product;
                $q = "
          INSERT INTO $h ( product_id, name, width, vrepeat, hrepeat, outdoor, dig_product_name, dig_width, in_master, log_vers_id, date, user_id )
          SELECT id, name, width, vrepeat, hrepeat, outdoor, dig_product_name, dig_width, in_master, log_vers_id, date_modif, user_id
          FROM $o A
          WHERE A.id = ?;";
                break;

            case constant('Digital'):
                $h = $this->th_product_digital;
                $o = $this->product_digital;
                $q = "
          INSERT INTO $h ( product_id, item_id, reverse_ground, style_id, in_master, log_vers_id, date, user_id )
          SELECT id, item_id, reverse_ground, style_id, in_master, log_vers_id, date_modif, user_id
          FROM $o A
          WHERE A.id = ?;";
                break;

            case constant('ScreenPrint'):
                $h = $this->th_product_screenprint;
                $o = $this->product_screenprint;
                $q = "
          INSERT INTO $h ( product_id, item_id, style_id, name, log_vers_id, date, user_id )
          SELECT id, item_id, style_id, name, log_vers_id, date_modif, user_id
          FROM $o A
          WHERE A.id = ?;";
                break;

            default:
                break;
        }

        //$user_id = $this->session->userdata('user_id');
        $bind = array($product_id);
        $this->db->query($q, $bind);
    }

    function log_product_price($product_type, $product_id)
    {
        //$user_id = $this->session->userdata('user_id');
        $bind = array($product_type, $product_id);
        $this->db->query("
      INSERT INTO $this->th_product_price ( product_id, product_type, p_hosp_cut, p_hosp_roll, p_res_cut, p_dig_res, p_dig_hosp, date, user_id )
      SELECT product_id, product_type, p_hosp_cut, p_hosp_roll, p_res_cut, p_dig_res, p_dig_hosp, date, user_id
      FROM $this->t_product_price A
      WHERE A.product_type = ? AND A.product_id = ?; 
    ", $bind);
    }

    function log_product_cost($product_id)
    {
        //$user_id = $this->session->userdata('user_id');
        $bind = array($product_id);
        $this->db->query("
      INSERT INTO $this->th_product_cost ( product_id, fob, 
      cost_cut_type_id, cost_cut, 
      cost_half_roll_type_id, cost_half_roll, 
      cost_roll_type_id, cost_roll, 
      cost_roll_landed_type_id, cost_roll_landed, 
     	cost_roll_ex_mill_type_id, cost_roll_ex_mill, cost_roll_ex_mill_text, 
      date, user_id )
      SELECT product_id, fob, 
      cost_cut_type_id, cost_cut, 
      cost_half_roll_type_id, cost_half_roll, 
      cost_roll_type_id, cost_roll, 
      cost_roll_landed_type_id, cost_roll_landed, 
      cost_roll_ex_mill_type_id, cost_roll_ex_mill, cost_roll_ex_mill_text,
      date, user_id
      FROM $this->t_product_cost A
      WHERE A.product_id = ?; 
    ", $bind);
    }

    function log_product_content_front($product_id)
    {
        $bind = array($product_id);
        $this->db->query("
				INSERT INTO $this->th_product_content_front 
				(product_id, perc, content_id, date, user_id)
				SELECT product_id, perc, content_id, date_add, user_id
				FROM $this->t_product_content_front A
				WHERE A.product_id = ?
		", $bind);
    }

    function log_product_content_back($product_id)
    {
        $bind = array($product_id);
        $this->db->query("
				INSERT INTO $this->th_product_content_back
				(product_id, perc, content_id, date, user_id)
				SELECT product_id, perc, content_id, date_add, user_id
				FROM $this->t_product_content_back A
				WHERE A.product_id = ?
		", $bind);
    }

    function log_product_various($product_id)
    {
        $bind = array($product_id);
        $this->db->query("
				INSERT INTO $this->th_product_various 
				(product_id, vendor_product_name, yards_per_roll, lead_time, min_order_qty, tariff_code, tariff_surcharge, railroaded, date, user_id)
				SELECT product_id, vendor_product_name, yards_per_roll, lead_time, min_order_qty, tariff_code, tariff_surcharge, railroaded, date_add, user_id
				FROM $this->t_product_various A
				WHERE A.product_id = ?
		", $bind);
    }

    function log_product_vendor($product_id)
    {
        $bind = array($product_id);
        $this->db->query("
				INSERT INTO $this->th_product_vendor 
				(product_id, vendor_id, date, user_id)
				SELECT product_id, vendor_id, date_add, user_id
				FROM $this->t_product_vendor A
				WHERE A.product_id = ?
		", $bind);
    }

    function log_product_weave($product_id)
    {
        $bind = array($product_id);
        $this->db->query("
				INSERT INTO $this->th_product_weave 
				(product_id, weave_id, date, user_id)
				SELECT product_id, weave_id, date_add, user_id
				FROM $this->t_product_weave A
				WHERE A.product_id = ?
		", $bind);
    }

    function log_item($item_id)
    {
        $bind = array($item_id);
        $this->db->query("
				INSERT INTO $this->th_item 
				(item_id, product_id, product_type, o_id, in_ringset, code, status_id, stock_status_id, vendor_color, vendor_code, min_order_qty, date, archived, user_id)
				SELECT id, product_id, product_type, o_id, in_ringset, code, status_id, stock_status_id, vendor_color, vendor_code, min_order_qty, date_modif, archived, user_id
				FROM $this->t_item A
				WHERE A.id = ?
		", $bind);
    }

    function log_item_color($item_id)
    {
        $bind = array($item_id);
        $this->db->query("
				INSERT INTO $this->th_item_color 
				(item_id, color_id, n_order, date, user_id)
				SELECT item_id, color_id, n_order, date_add, user_id
				FROM $this->t_item_color A
				WHERE A.item_id = ?
		", $bind);
    }

    /*

      METHODS
      Get Products Information

    */

    function init_date_filter($date_from = null, $date_to = null)
    {
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    function filter_by_dates($t, $field = 'date')
    {
        if ($this->date_from !== null) {
            $this->db->where("$t.$field >=", $this->date_from);
        }
        if ($this->date_to !== null) {
            $this->db->where("$t.$field <=", $this->date_to);
        }
    }

    function history_product($product_id)
    {
        $sql = '';
        $h = $this->th_product;
        $this->db->select("log_vers_id, name, width, vrepeat, hrepeat, product_type, outdoor, dig_product_name, dig_width, date, user_id as user")
            ->from("$h A")
            ->where("A.product_id", $product_id);
        $this->filter_by_dates($h);

        $sql .= $this->db->get_compiled_select() . " UNION ALL ";

        $o = $this->t_product;
        $this->db->select("log_vers_id, name, width, vrepeat, hrepeat, product_type, outdoor, dig_product_name, dig_width, date_modif as date, user_id as user")
            ->from("$o A")
            ->where("A.id", $product_id);

        $sql .= $this->db->get_compiled_select() . " ORDER BY log_vers_id DESC;";

        return $this->db->query($sql)->result_array();
    }

    function history_product_price($product_type, $product_id, $pname = '')
    {
        $sql = '';
        $t = $this->t_product;

        // Get old prices
        $th_price = $this->th_product_price;
        $this->db
            ->select(" '$pname' ")
            ->select("CASE WHEN A.p_res_cut != '0.00' THEN CONCAT('$ ', A.p_res_cut) ELSE '-' END as p_res_cut")
            ->select("CASE WHEN A.p_hosp_cut != '0.00' THEN CONCAT('$ ', A.p_hosp_cut) ELSE '-' END as p_hosp_cut")
            ->select("CASE WHEN A.p_hosp_roll != '0.00' THEN CONCAT('$ ', A.p_hosp_roll) ELSE '-' END as p_hosp_roll")
            ->select("CASE WHEN A.p_dig_res != '0.00' THEN CONCAT('$ ', A.p_dig_res) ELSE '-' END as p_dig_res")
            ->select("CASE WHEN A.p_dig_hosp != '0.00' THEN CONCAT('$ ', A.p_dig_hosp) ELSE '-' END as p_dig_hosp")
            ->select("DATE_FORMAT(A.date, '%m/%d/%Y %H:%i:%s') as date", false)
            ->select("$this->auth_users.username as user")
            ->from("$th_price A")
            ->join("$this->auth_users", "$this->auth_users.id = A.user_id")
            ->where("A.product_id", $product_id)
            ->where("A.product_type", $product_type);
        $this->filter_by_dates($th_price);

        $sql .= $this->db->get_compiled_select() . " UNION ALL ";

        // Get current price
        $o = $this->t_product_price;
        $this->db
            ->select(" '$pname' ")
            ->select("CASE WHEN A.p_res_cut != '0.00' THEN CONCAT('$ ', A.p_res_cut) ELSE '-' END as p_res_cut")
            ->select("CASE WHEN A.p_hosp_cut != '0.00' THEN CONCAT('$ ', A.p_hosp_cut) ELSE '-' END as p_hosp_cut")
            ->select("CASE WHEN A.p_hosp_roll != '0.00' THEN CONCAT('$ ', A.p_hosp_roll) ELSE '-' END as p_hosp_roll")
            ->select("CASE WHEN A.p_dig_res != '0.00' THEN CONCAT('$ ', A.p_dig_res) ELSE '-' END as p_dig_res")
            ->select("CASE WHEN A.p_dig_hosp != '0.00' THEN CONCAT('$ ', A.p_dig_hosp) ELSE '-' END as p_dig_hosp")
            ->select("DATE_FORMAT(A.date, '%m/%d/%Y %H:%i:%s') as date", false)
//       ->select(" 'current' ")
            ->select("$this->auth_users.username as user")
            ->from("$o A")
            ->join("$this->auth_users", "$this->auth_users.id = A.user_id")
            ->where("A.product_id", $product_id)
            ->where("A.product_type", $product_type);

        $sql .= $this->db->get_compiled_select() . " ORDER BY date DESC;";

        return $this->db->query($sql)->result_array();
    }

    function history_product_cost($product_type, $product_id)
    {
        $sql = '';
        $t = $this->t_product;
        $t_cost_history = $this->th_product_cost;
        $type = $this->p_price_type;

        // Get old costs for the fabric (or ground if combined)

        if ($product_type === constant('Regular')) {
            $this->db->select("B.name")
                ->select("A.fob")
                ->select("CASE WHEN A.cost_cut != '0.00' THEN CONCAT(T1.name, ' ', A.cost_cut) ELSE '-' END as cost_cut")
                ->select("CASE WHEN A.cost_half_roll != '0.00' THEN CONCAT(T2.name, ' ', A.cost_half_roll) ELSE '-' END as cost_half_roll")
                ->select("CASE WHEN A.cost_roll != '0.00' THEN CONCAT(T3.name, ' ', A.cost_roll) ELSE '-' END as cost_roll")
                ->select("CASE WHEN A.cost_roll_landed != '0.00' THEN CONCAT(T4.name, ' ', A.cost_roll_landed) ELSE '-' END as cost_roll_landed")
                ->select("CASE WHEN A.cost_roll_ex_mill != '0.00' THEN CONCAT(T5.name, ' ', A.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill")
                ->select("DATE_FORMAT(A.date, '%m/%d/%Y %H:%i:%s') as date", false)
                ->select("$this->auth_users.username as user")
                ->from("$t_cost_history A")
                ->join("$t B", "A.product_id = B.id", 'left outer')
                ->join("$type T1", "A.cost_cut_type_id = T1.id", 'left outer')
                ->join("$type T2", "A.cost_half_roll_type_id = T2.id", 'left outer')
                ->join("$type T3", "A.cost_roll_type_id = T3.id", 'left outer')
                ->join("$type T4", "A.cost_roll_landed_type_id = T4.id", 'left outer')
                ->join("$type T5", "A.cost_roll_ex_mill_type_id = T5.id", 'left outer')
                ->join("$this->auth_users", "$this->auth_users.id = A.user_id", 'left outer')
                ->where("A.product_id", $product_id);
        } else {
            if ($product_type === constant('Digital')) {
                $t_x_product = $this->product_digital;
            } else if ($product_type === constant('ScreenPrint')) {
                $t_x_product = $this->product_screenprint;
            }

            $this->db->select("T_PRODUCT.name")
                ->select("A.fob")
                ->select("CASE WHEN A.cost_cut != '0.00' THEN CONCAT(T1.name, ' ', A.cost_cut) ELSE '-' END as cost_cut")
                ->select("CASE WHEN A.cost_half_roll != '0.00' THEN CONCAT(T2.name, ' ', A.cost_half_roll) ELSE '-' END as cost_half_roll")
                ->select("CASE WHEN A.cost_roll != '0.00' THEN CONCAT(T3.name, ' ', A.cost_roll) ELSE '-' END as cost_roll")
                ->select("CASE WHEN A.cost_roll_landed != '0.00' THEN CONCAT(T4.name, ' ', A.cost_roll_landed) ELSE '-' END as cost_roll_landed")
                ->select("CASE WHEN A.cost_roll_ex_mill != '0.00' THEN CONCAT(T5.name, ' ', A.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill")
                ->select("DATE_FORMAT(A.date, '%m/%d/%Y %H:%i:%s') as date", false)
                ->select("$this->auth_users.username as user")
                ->from("$t_x_product PD, $this->t_item T_ITEM, $t T_PRODUCT, $t_cost_history A")
                ->where("PD.id", $product_id)
                ->where("T_ITEM.id = PD.item_id")
                ->where("T_PRODUCT.id = T_ITEM.product_id")
                ->where("A.product_id = T_PRODUCT.id")
                ->join("$type T1", "A.cost_cut_type_id = T1.id", 'left outer')
                ->join("$type T2", "A.cost_half_roll_type_id = T2.id", 'left outer')
                ->join("$type T3", "A.cost_roll_type_id = T3.id", 'left outer')
                ->join("$type T4", "A.cost_roll_landed_type_id = T4.id", 'left outer')
                ->join("$type T5", "A.cost_roll_ex_mill_type_id = T5.id", 'left outer')
                ->join("$this->auth_users", "$this->auth_users.id = A.user_id", 'left outer');

        }

        $this->filter_by_dates($t_cost_history);

        $sql .= $this->db->get_compiled_select() . " UNION ALL ";

        if ($product_type === constant('Regular')) {

            // Get current cost
            $o = $this->t_product_cost;
            $this->db->select("B.name")
                ->select("A.fob")
                ->select("CASE WHEN A.cost_cut != '0.00' THEN CONCAT(T1.name, ' ', A.cost_cut) ELSE '-' END as cost_cut")
                ->select("CASE WHEN A.cost_half_roll != '0.00' THEN CONCAT(T2.name, ' ', A.cost_half_roll) ELSE '-' END as cost_half_roll")
                ->select("CASE WHEN A.cost_roll != '0.00' THEN CONCAT(T3.name, ' ', A.cost_roll) ELSE '-' END as cost_roll")
                ->select("CASE WHEN A.cost_roll_landed != '0.00' THEN CONCAT(T4.name, ' ', A.cost_roll_landed) ELSE '-' END as cost_roll_landed")
                ->select("CASE WHEN A.cost_roll_ex_mill != '0.00' THEN CONCAT(T5.name, ' ', A.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill")
                ->select("DATE_FORMAT(A.date, '%m/%d/%Y %H:%i:%s') as date", false)
                ->select("$this->auth_users.username as user")
                ->from("$o A")
                ->join("$t B", "A.product_id = B.id", 'left outer')
                ->join("$type T1", "A.cost_cut_type_id = T1.id", 'left outer')
                ->join("$type T2", "A.cost_half_roll_type_id = T2.id", 'left outer')
                ->join("$type T3", "A.cost_roll_type_id = T3.id", 'left outer')
                ->join("$type T4", "A.cost_roll_landed_type_id = T4.id", 'left outer')
                ->join("$type T5", "A.cost_roll_ex_mill_type_id = T5.id", 'left outer')
                ->join("$this->auth_users", "$this->auth_users.id = A.user_id", 'left outer')
                ->where("A.product_id", $product_id);

        } else {

            if ($product_type === constant('Digital')) {
                $t_x_product = $this->product_digital;
            } else if ($product_type === constant('ScreenPrint')) {
                $t_x_product = $this->product_screenprint;
            }

            // Get current cost
            $o = $this->t_product_cost;
            $this->db->select("T_PRODUCT.name")
                ->select("A.fob")
                ->select("CASE WHEN A.cost_cut != '0.00' THEN CONCAT(T1.name, ' ', A.cost_cut) ELSE '-' END as cost_cut")
                ->select("CASE WHEN A.cost_half_roll != '0.00' THEN CONCAT(T2.name, ' ', A.cost_half_roll) ELSE '-' END as cost_half_roll")
                ->select("CASE WHEN A.cost_roll != '0.00' THEN CONCAT(T3.name, ' ', A.cost_roll) ELSE '-' END as cost_roll")
                ->select("CASE WHEN A.cost_roll_landed != '0.00' THEN CONCAT(T4.name, ' ', A.cost_roll_landed) ELSE '-' END as cost_roll_landed")
                ->select("CASE WHEN A.cost_roll_ex_mill != '0.00' THEN CONCAT(T5.name, ' ', A.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill")
                ->select("DATE_FORMAT(A.date, '%m/%d/%Y %H:%i:%s') as date", false)
                ->select("$this->auth_users.username as user")
                ->from("$t_x_product PD, $this->t_item T_ITEM, $t T_PRODUCT, $o A")
                ->where("PD.id", $product_id)
                ->where("T_ITEM.id = PD.item_id")
                ->where("T_PRODUCT.id = T_ITEM.product_id")
                ->where("A.product_id = T_PRODUCT.id")
                ->join("$type T1", "A.cost_cut_type_id = T1.id", 'left outer')
                ->join("$type T2", "A.cost_half_roll_type_id = T2.id", 'left outer')
                ->join("$type T3", "A.cost_roll_type_id = T3.id", 'left outer')
                ->join("$type T4", "A.cost_roll_landed_type_id = T4.id", 'left outer')
                ->join("$type T5", "A.cost_roll_ex_mill_type_id = T5.id", 'left outer')
                ->join("$this->auth_users", "$this->auth_users.id = A.user_id", 'left outer');

        }

        $sql .= $this->db->get_compiled_select() . " ORDER BY date DESC;";

        return $this->db->query($sql)->result_array();
    }

    /*

      METHODS
      Additional functions

    */
    function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE)
    {
        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city" => @$ipdat->geoplugin_city,
                            "state" => @$ipdat->geoplugin_regionName,
                            "country" => @$ipdat->geoplugin_countryName,
                            "country_code" => @$ipdat->geoplugin_countryCode,
                            "continent" => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }
        return $output;
    }

    function access_log()
    {

        $this->load->model('logs_model');

        $ip = $this->input->ip_address();
        $arr = $this->ip_info($ip, "location");

        if ($this->uri->uri_string() !== '' && !in_array('search', $this->uri->segment_array()) && !in_array('get_ground_info', $this->uri->segment_array()) && !in_array('get_print', $this->uri->segment_array()) && !in_array('get_print_detail', $this->uri->segment_array())) {

            if (in_array('product', $this->uri->segment_array())) {
                // A Product page was viewed
                $seg_array = $this->uri->segment_array();
                $product = $seg_array[$this->uri->total_segments()];
                if (in_array('digital', $this->uri->segment_array())) {
                    // A Digital Product
                    $category = 'digital';
                } else {
                    // A Fabric Product
                    $category = 'fabric';
                }
                $this->logs_model->add_product_page_log($category, $product);

            } else {
                // Regular URIs
                $this->logs_model->add_access_log($ip, $this->uri->uri_string(), $arr['country'], $arr['state'], $arr['city'], $this->ip_info($ip, "address"), $this->input->user_agent());
            }
        }
    }

    function show_404()
    {
        //$this->load->model('logs_model');

        $ip = $this->input->ip_address();
        $arr = $this->ip_info($ip, "location");
        //$this->logs_model->add_log_404($ip, 'frontend_new', $arr['country'], $arr['state'], $arr['city'], $this->ip_info($ip, "address"), '', $this->input->user_agent(), $this->uri->uri_string() );

        show_404();
    }

}

?>