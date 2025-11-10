<?php
require APPPATH . '/core/Logs_model.php';

class MY_Model extends Logs_model
{
    public $_STATUS = [
        'TBD' => 1,
        'NOTRUN' => 2,
        'DISCO' => 3,
        'RUN' => 4,
        'LEFTOVER' => 5,
        'FISHING' => 6,
        'FABRICSEEN' => 15,
        'MSO' => 18,
        'LIMITED_QUANTITY' => 20
    ];

    function __construct()
    {
        parent::__construct();

        // Lists
        $this->digital_grounds_list_id = 1;
        $this->screenprint_grounds_list_id = 2;
        $this->under30_list_id = 3;
        $this->fabricseen_list_id = 401;

        // Statuses
        $this->product_status_running = [$this->_STATUS['RUN']];
        $this->product_status_discontinued = [$this->_STATUS['DISCO']];

        $this->product_status_to_dont_print_in_specsheet = [
            //          $this->_STATUS['TBD'],        # TBD
            $this->_STATUS['NOTRUN'],    # Not Running
            $this->_STATUS['DISCO'],      # Discontinued
            $this->_STATUS['MSO'],        # Mill Special Order
            //	      $this->_STATUS['LIMITED_QUANTITY']
        ];
        $this->product_status_to_dont_print_in_pricelists = [
            $this->_STATUS['NOTRUN'],
            $this->_STATUS['DISCO'],
            $this->_STATUS['MSO'],
            $this->_STATUS['LIMITED_QUANTITY']
        ];
        $this->product_status_to_dont_count_in_memotags = [
            $this->_STATUS['NOTRUN'],
            $this->_STATUS['DISCO'],
            $this->_STATUS['MSO'],
            $this->_STATUS['LIMITED_QUANTITY']
        ]; // Used for the 'AVAILABLE IN X COLORWAYS' counter in the memotags
        $this->product_status_to_dont_print_in_website = $this->product_status_to_dont_print_in_pricelists;
        $this->define_dtables();
    }

    private function define_dtables()
    {
        /*
            Main app database - used in ion_auth_model to bring the showroom
            Can be fixed using a VIEW
        */
        $this->db_thismaster = $this->db->database;

        if (ENVIRONMENT === 'production') {
            $this->site_urls   = ['website' => "https://www.opuzen.com/"];
            $this->db_roadkit  = "opuzen_prod_roadkit_int";
            $this->db_showcase = "opuzen_prod_showcase";
            $this->db_sales    = "opuzen_prod_sales";
            $this->db_users    = "opuzen_prod_users";
        } elseif (ENVIRONMENT === 'prod') {
            $this->site_urls   = ['website' => "https://www.opuzen.com/"];
            $this->db_roadkit  = "opuzen_prod_roadkit_int";
            $this->db_showcase = "opuzen_prod_showcase";
            $this->db_sales    = "opuzen_prod_sales";
            $this->db_users    = "opuzen_prod_users";
        } elseif (ENVIRONMENT === 'development') {
            $this->site_urls   = ['website' => "https://dev.opuzen.com/dev/public_html/"];
            $this->db_roadkit  = "opuzen_prod_roadkit_int";
            $this->db_showcase = "opuzen_prod_showcase";
            $this->db_sales    = "opuzen_dev_sales";
            $this->db_users    = "opuzen_prod_users";
        } elseif (ENVIRONMENT === 'dev') {
            $this->site_urls   = ['website' => "https://opms-dev.opuzen-service.com/"];
            // $this->load->database('authentication', TRUE);
            $this->db_roadkit  = "opuzen_dev_roadkit_int";
            $this->db_showcase = "opuzen_dev_showcase";
            $this->db_sales    = "opuzen_dev_sales";
            $this->db_users    = "opuzen_dev_users";
        } elseif (ENVIRONMENT === 'tnd_dev') {
            $this->site_urls   = ['website' => "https://opms-dev.opuzen-service.com/"];
            // $this->load->database('authentication', TRUE);
            $this->db_roadkit  = "opuzen_dev_roadkit_int";
            $this->db_showcase = "opuzen_dev_showcase";
            $this->db_sales    = "opuzen_dev_sales";
            $this->db_users    = "opuzen_dev_users";
        } elseif (ENVIRONMENT === 'qa' || ENVIRONMENT === 'stage') {
            $this->site_urls   = ['website' => "https://stage.opuzen-service.com/"];
            $this->db_roadkit  = "opuzen_qa_roadkit_int";
            $this->db_showcase = "opuzen_qa_showcase";
            $this->db_sales    = "opuzen_qa_sales";
            $this->db_users    = "opuzen_qa_users";
        } else {
            $this->site_urls   = ['website' => "https://localhost:8444/"];
            $this->db_roadkit  = "opuzen_loc_roadkit_int";
            $this->db_showcase = "opuzen_loc_showcase";
            $this->db_sales    = "opuzen_loc_sales";
            $this->db_users    = "opuzen_loc_users";
        }

        // Used for data collection of user_ids
        $this->auth_groups = "$this->db_users.auth_groups";
        $this->auth_groups_permissions = "$this->db_users.auth_groups_permissions";
        $this->auth_login_attempts = "$this->db_users.auth_login_attempts";
        $this->auth_permissions = "$this->db_users.auth_permissions";
        $this->auth_users = "$this->db_users.auth_users";
        $this->auth_users_groups = "$this->db_users.auth_users_groups";
        $this->auth_users_showrooms = "$this->db_users.auth_users_showrooms";
        $this->auth_users_destinations = "$this->db_users.auth_users_destinations";

        // Table for: Products
        $this->t_product = "T_PRODUCT";
        $this->t_product_files = "T_PRODUCT_FILES";
        $this->t_product_abrasion = "T_PRODUCT_ABRASION";
        $this->t_product_abrasion_files = "T_PRODUCT_ABRASION_FILES";
        $this->t_product_cleaning = "T_PRODUCT_CLEANING";
        $this->t_product_cleaning_specials = "T_PRODUCT_CLEANING_SPECIAL";
        $this->t_product_cleaning_instructions = "T_PRODUCT_CLEANING_INSTRUCTIONS";
        $this->t_product_warranty = "T_PRODUCT_WARRANTY";
        $this->t_product_content_back = "T_PRODUCT_CONTENT_BACK";
        $this->t_product_content_front = "T_PRODUCT_CONTENT_FRONT";
        $this->t_product_finish = "T_PRODUCT_FINISH";
        $this->t_product_finish_specials = "T_PRODUCT_FINISH_SPECIAL";
        $this->t_product_firecode = "T_PRODUCT_FIRECODE";
        $this->t_product_firecode_files = "T_PRODUCT_FIRECODE_FILES";
        $this->t_product_messages = "T_PRODUCT_MESSAGES";
        $this->t_product_origin = "T_PRODUCT_ORIGIN";
        $this->t_product_price = "T_PRODUCT_PRICE";
        $this->t_product_cost = "T_PRODUCT_PRICE_COST";
        //$this->t_product_shelf = "T_PRODUCT_SHELF";
        $this->t_product_use = "T_PRODUCT_USE";
        $this->t_product_various = "T_PRODUCT_VARIOUS";
        $this->t_product_vendor = "T_PRODUCT_VENDOR";
        $this->t_product_weave = "T_PRODUCT_WEAVE";
        $this->t_product_stock = $this->db_sales . ".op_products_stock";
        $this->t_product_stock_bolts = $this->db_sales . ".op_products_bolts";
        $this->t_product_task = "T_PRODUCT_TASK";

        $this->v_product_cost = "V_PRODUCT_COST";
        $this->v_product_content_front = "V_PRODUCT_CONTENT_FRONT";
        $this->v_product_content_back = "V_PRODUCT_CONTENT_BACK";
        $this->v_product_vendor = "V_PRODUCT_VENDOR";
        $this->v_item = "V_ITEM";
        $this->v_item_shelf = "V_ITEM_SHELF";

        $this->t_item = "T_ITEM";
        $this->t_item_color = "T_ITEM_COLOR";
        $this->t_item_messages = "T_ITEM_MESSAGES";
        $this->t_item_shelf = "T_ITEM_SHELF";
        $this->t_item_reselection = "T_ITEM_RESELECTION";
        //        $this->t_item_sampling = "T_ITEM_SAMPLING";

        $this->product_digital = "T_PRODUCT_X_DIGITAL";
        $this->product_screenprint = "T_PRODUCT_X_SCREENPRINT";

        $this->t_digital_style = "U_DIGITAL_STYLE";
        $this->t_digital_style_files = "U_DIGITAL_STYLE_FILES";
        $this->t_screenprint_style = "U_SCREENPRINT_STYLE";

        // Table for: Website data
        $this->t_showcase_product = "SHOWCASE_PRODUCT";
        $this->t_showcase_product_collection = "SHOWCASE_PRODUCT_COLLECTION";
        $this->t_showcase_product_contents_web = "SHOWCASE_PRODUCT_CONTENTS_WEB";
        $this->t_showcase_product_patterns = "SHOWCASE_PRODUCT_PATTERNS";
        $this->t_showcase_item = "SHOWCASE_ITEM";
        $this->t_showcase_item_coord_color = "SHOWCASE_ITEM_COORD_COLOR";
        $this->t_showcase_style = "SHOWCASE_DIGITAL_STYLE";
        $this->t_showcase_style_items = "SHOWCASE_DIGITAL_STYLE_ITEMS";
        $this->t_showcase_style_items_color = "SHOWCASE_DIGITAL_STYLE_ITEMS_COLOR";
        $this->t_showcase_style_items_coord_color = "SHOWCASE_DIGITAL_STYLE_ITEMS_COORD_COLOR";
        $this->t_showcase_styles_patterns = "SHOWCASE_DIGITAL_STYLE_PATTERNS";
        $this->t_showcase_screenprint = "SHOWCASE_SCREENPRINT_STYLE";
        $this->t_showcase_contents_web = "SHOWCASE_P_CONTENTS_WEB";
        $this->t_showcase_coord_colors = "SHOWCASE_P_COORD_COLORS";
        $this->t_showcase_patterns = "SHOWCASE_P_PATTERNS";
        $this->t_showcase_collection = "SHOWCASE_P_COLLECTION";
        $this->t_showcase_press = "SHOWCASE_PRESS";

        // Table for: Specs constants
        $this->p_abrasion_limit = "P_ABRASION_LIMIT";
        $this->p_abrasion_test = "P_ABRASION_TEST";
        $this->p_cleaning = "P_CLEANING";
        $this->p_cleaning_instructions = "P_CLEANING_INSTRUCTIONS";
        $this->p_warranty = "P_WARRANTY";
        $this->p_terms = "P_TERMS";
        $this->p_general_cleaning_instructions = "P_GENERAL_CLEANING_INSTRUCTIONS";
        $this->p_general_warranty = "P_GENERAL_WARRANTY";
        $this->p_docs_fqs = "P_FQS";
        $this->p_color = "P_COLOR";
        $this->p_content = "P_CONTENT";
        $this->p_finish = "P_FINISH";
        $this->p_firecode = "P_FIRECODE_TEST";
        $this->p_origin = "P_ORIGIN";
        $this->p_product_status = "P_PRODUCT_STATUS";
        $this->p_stock_status = "P_STOCK_STATUS";
        $this->p_restock_status = "P_RESTOCK_STATUS";
        $this->p_restock_destination = "P_RESTOCK_DESTINATION";
        $this->p_shelf = "P_SHELF";
        $this->p_sampling_locations = "P_SAMPLING_LOCATIONS";
        $this->p_use = "P_USE";
        $this->p_weave = "P_WEAVE";
        $this->p_category_lists = "P_CATEGORY_LISTS";
        $this->p_category_files = "P_CATEGORY_FILES";
        $this->p_price_type = "P_PRICE_TYPE";
        $this->p_weight_unit = "P_WEIGHT_UNIT";
        $this->p_product_task = "P_PRODUCT_TASK";

        // Table for: Contacts / Vendors / Showrooms
        $this->p_contact = "Z_CONTACT";
        $this->p_vendor = "Z_VENDOR";
        $this->p_vendor_files = "Z_VENDOR_FILES";
        $this->p_vendor_contact = "Z_VENDOR_CONTACT";
        $this->p_showroom = "Z_SHOWROOM";
        $this->p_showroom_files = "Z_SHOWROOM_FILES";
        $this->p_showroom_contact = "Z_SHOWROOM_CONTACT";

        // Table for: Active lists
        $this->p_list = "Q_LIST";
        $this->p_list_items = "Q_LIST_ITEMS";
        //     $this->p_list_products = "Q_LIST_PRODUCTS";
        $this->p_list_showrooms = "Q_LIST_SHOWROOMS";
        $this->p_list_category = "Q_LIST_CATEGORY";
        // Table for: Archived lists
        $this->th_list = "S_HISTORY_Q_LIST";
        $this->th_list_items = "S_HISTORY_Q_LIST_ITEMS";
        $this->th_list_showrooms = "S_HISTORY_Q_LIST_SHOWROOMS";
        $this->th_list_category = "S_HISTORY_Q_LIST_CATEGORY";

        // Table for: History
        $this->th_product = "S_HISTORY_PRODUCT";
        $this->th_product_digital = "S_HISTORY_PRODUCT_X_DIGITAL";
        $this->th_product_screenprint = "S_HISTORY_PRODUCT_X_SCREENPRINT";
        $this->th_product_price = "S_HISTORY_PRODUCT_PRICE";
        $this->th_product_cost = "S_HISTORY_PRODUCT_PRICE_COST";
        $this->th_product_content_back = "S_HISTORY_PRODUCT_CONTENT_BACK";
        $this->th_product_content_front = "S_HISTORY_PRODUCT_CONTENT_FRONT";
        $this->th_product_various = "S_HISTORY_PRODUCT_VARIOUS";
        $this->th_product_vendor = "S_HISTORY_PRODUCT_VENDOR";
        $this->th_product_weave = "S_HISTORY_PRODUCT_WEAVE";
        $this->th_item = "S_HISTORY_ITEM";
        $this->th_item_color = "S_HISTORY_ITEM_COLOR";
        $this->th_product_task = "S_HISTORY_PRODUCT_TASK";
        $this->th_portfolio_picture = "S_HISTORY_PORTFOLIO_PICTURE";
        $this->th_portfolio_product = "S_HISTORY_PORTFOLIO_PRODUCT";

        $this->t_restock_order = "RESTOCK_ORDER";
        $this->t_restock_ship = "RESTOCK_SHIP";
        $this->t_restock_order_completed = "RESTOCK_ORDER_COMPLETED";
        $this->t_restock_ship_completed = "RESTOCK_SHIP_COMPLETED";

        $this->t_portfolio_project = "PORTFOLIO_PROJECT";
        $this->t_portfolio_picture = "PORTFOLIO_PICTURE";
        $this->t_portfolio_product = "PORTFOLIO_PRODUCT";
    }

    /*
        Common methods
    */

    function save_new_color($data)
    {
        $this->db
            ->set($data)
            ->insert($this->p_color);
        return $this->db->insert_id();
    }

    function clean_logics($id, $t, $field = 'product_id')
    {
        // Eventually this function will save the modifications in the log table!!!
        $this->db->where($field, $id)
            ->delete($t);
    }

    // Searches

    function set_datatables_variables()
    {
        $this->whereColumns = array();
        $this->whereClause = '';

        // Columns of data!
        $columns = ($this->input->post('columns') === null ? array() : $this->input->post('columns'));

        // How will the table order??
        $order = $this->input->post('order');
        if ($order !== null) {
            $this->order_by = [$columns[$order[0]['column']]['data'] . " " . $order[0]['dir']];
        }

        // Is there any input to search by user?
        $search = $this->input->post('search'); // still need to use $search['value']
        //$searchtype = ( is_null($this->input->post('searchtype')) ? constant('product') : $this->input->post('searchtype') );
        $search_value = $search['value'];

        $this->words_to_search = explode(' ', $search_value);
        $c = 0;
        foreach ($this->words_to_search as $w) { // Clean up empty spaces!!!!
            if (strlen($w) === 0) {
                unset($this->words_to_search[$c]);
            }
            $c++;
        }
        $c = 0;
        foreach ($columns as $col) { // Clean columns that are not searchable
            if ($col['searchable'] === 'false' || strpos($col['data'], 'btn') !== false) {
                unset($columns[$c]);
            } else /*if( $searchtype === constant('product') )*/ {
                $column_schema = $this->column_schema($col['data']);

                if (isset($column_schema['clause']) && $column_schema['clause'] === 'where') {

                    if (is_array($column_schema['name']) && count($column_schema['name']) > 0) {
                        foreach ($column_schema['name'] as $name) {
                            array_push($this->whereColumns, $name);
                        }
                    } else {
                        array_push($this->whereColumns, $column_schema['name']);
                    }
                }
            }/* else if( $searchtype === constant('item') ) {
        array_push($this->whereColumns, "$this->t_item.code");
      }*/
            $c++;
        }

        $sql = '';

        if (strlen($search_value) > 0) {
            $w = 1;

            foreach ($this->words_to_search as $word) {

                $c = 1;
                $totalColumns = count($this->whereColumns);
                if ($totalColumns > 0) {
                    foreach ($this->whereColumns as $colName) {
                        //$this->whereClause .= $this->createCondition($word, $colName, $totalColumns, $c);
                        $this->whereClause .= '';
                        $this->whereClause .= ($c === 1 ? ' ( ' : ''); // First column
                        $this->whereClause .= " $colName LIKE '%$word%' ";
                        $this->whereClause .= ($c !== $totalColumns ? ' OR ' : ' ) '); // Last column?
                        $c++;
                    }
                    $this->whereClause .= ($w !== count($this->words_to_search) ? ' AND ' : '');
                }
                $w++;
            }

            //if ($this->whereClause !== '') $this->db->where($this->whereClause);
        }
    }

    function apply_datatables_processing($query)
    {

        $this->db->from("($query) as t");

        if (isset($this->order_by)) {
            if (is_array($this->order_by)) {
                foreach ($this->order_by as $ord) {
                    $this->db->order_by($ord);
                }
                //                $this->db->order_by($this->order_by[0], $this->order_by[1]);
            } else {
                $this->db->order_by($this->order_by);
            }
        }


        // How many results where found????? Used for pagination of the results
        $arr['recordsFiltered'] = $this->db->count_all_results(null, false); // False: dont empty the active query
        // Limit the query
        if ($this->input->post('length') !== '-1') {
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        }
        // Bring it on!!
        $arr['arr'] = $this->db->get()->result_array();
        $arr['query'] = $this->db->last_query();

        // DEV Feedback 
        // echo $arr['query'];
        // exit;

        // This needs to be fixed..
        if (isset($this->model_table)) {
            if (is_array($this->model_table)) {
                $arr['recordsTotal'] = 0;
                foreach ($this->model_table as $t) {
                    $arr['recordsTotal'] += $this->db->count_all($t);
                }
            } else {
                $arr['recordsTotal'] = $this->db->count_all($this->model_table);
            }
        } else if (is_null($this->filters['model_table'])) {
            $arr['recordsTotal'] = 0;
        } else if (is_array($this->filters['model_table'])) {
            $arr['recordsTotal'] = 0;
            foreach ($this->filters['model_table'] as $t) {
                $arr['recordsTotal'] += $this->db->count_all($t);
            }
        } else {
            $arr['recordsTotal'] = $this->db->count_all($this->filters['model_table']);
        }

        return $arr;
    }

    function column_schema($data)
    {
        if (isset($this->product_type)) {
            switch ($this->product_type) {
                case constant('Regular'):
                    $this->x2 = $this->t_product;
                    break;
                case constant('Digital'):
                    $this->x2 = $this->t_digital_style;
                    break;
                case constant('ScreenPrint'):
                    $this->x2 = $this->t_screenprint_style;
                    break;
            }
        }

        switch ($data) {
            case 'product_name':
                return array(
                    'clause' => 'where',
                    'name' => (isset($this->product_type) && $this->x2 !== $this->t_product && $this->product_type !== constant('Regular') ?
                        array("$this->t_product.dig_product_name", "$this->t_product.name", "$this->x2.name", "$this->p_color.name") :
                        array("$this->t_product.name", "$this->t_product.dig_product_name"))
                );
                break;

            case 'width':
                return array('clause' => 'where', 'name' => "$this->t_product.width");
                break;

            case 'product_status':
                return array('clause' => 'where', 'name' => "$this->p_product_status.name");
                break;

            case 'stock_status':
                return array('clause' => 'where', 'name' => "$this->p_stock_status.name");
                break;

            case 'p_res_cut':
            case 'p_hosp_cut':
            case 'p_hosp_roll':
                return array('clause' => 'where', 'name' => $this->t_product_price . '.' . $data);
                break;

            case 'cost_cut':
            case 'cost_half_roll':
            case 'cost_roll':
            case 'cost_roll_landed':
            case 'cost_roll_ex_mill':
            case 'fob':
                return array('clause' => 'where', 'name' => $this->t_product_cost . '.' . $data);
                break;

            case 'vendors_name':
            case 'vendors_abrev':
            case "c_owner_abrev":
                if (isset($this->product_type) && $this->product_type === constant('Regular') || isset($this->ctype)) return array('clause' => 'where', 'name' => array("V.name", "V.abrev"));
                break;

            case 'vendor_product_name':
                return array('clause' => 'where', 'name' => $this->t_product_various . '.' . $data);
                break;

            case 'yards_per_roll':
            case 'lead_time':
            case 'min_order_qty':

                break;

            case "uses":
                return array('clause' => 'where', 'name' => "$this->p_use.name");
                break;

            case "weaves":
                return array('clause' => 'where', 'name' => "$this->p_weave.name");
                break;

            case "repeats":
                return array('clause' => 'where', 'name' => array("$this->x2.vrepeat", "$this->x2.hrepeat"));
                break;

            case "content_front":
                return array('clause' => 'where', 'name' => array("PC1.name", "$this->t_product_content_front.perc"));
                break;

            case "content_back":
                //return array('clause'=>'where', 'name'=>array("PC2.name", "$this->t_product_content_back.perc") );
                break;

            case "firecodes":
                return array('clause' => 'where', 'name' => "$this->p_firecode.name");
                break;

            case "abrasions":
                return array('clause' => 'where', 'name' => array("$this->p_abrasion_test.name", "$this->p_abrasion_limit.name", "$this->t_product_abrasion.n_rubs"));
                break;

            case "cleanings":
                return array('clause' => 'where', 'name' => "$this->p_cleaning.name");
                break;

            case "finishs":
                return array('clause' => 'where', 'name' => "$this->p_finish.name");
                break;

            case "list_name":
                return array('clause' => 'where', 'name' => $this->p_list . '.name');
                break;

            case "category":
                return array('clause' => 'where', 'name' => array("$this->p_category_lists.name", "$this->p_category_lists.descr"));
                break;

            case "showrooms":
                return array('clause' => 'where', 'name' => array("$this->p_showroom.name", "$this->p_showroom.abrev"));
                break;

            case "code":
                return array('clause' => 'where', 'name' => "$this->t_item.code");
                break;

            case "color":
                return array('clause' => 'where', 'name' => "$this->p_color.name");
                break;


            /*

                CONTACTS

            */
            case "c_name":
                return array('clause' => 'where', 'name' => "C.name");
                break;
            case "c_company":
                return array('clause' => 'where', 'name' => "C.company");
                break;
            case "c_address_1":
                return array('clause' => 'where', 'name' => "C.address_1");
                break;
            case "c_address_2":
                return array('clause' => 'where', 'name' => "C.address_2");
                break;
            case "city":
                return array('clause' => 'where', 'name' => "C.city");
                break;

            default:
                break;
        }
    }

    function where_product_is_not_archived($t = null)
    {
        if (is_null($t)) {

            switch ($this->product_type) {
                case constant('Regular'):
                    $t = $this->t_product;
                    break;

                case constant('Digital'):
                    $t = $this->product_digital;
                    break;

                case constant('ScreenPrint'):
                    $t = $this->product_screenprint;
                    break;

                case 'digital_style_id':
                    $t = $this->t_digital_style;

                default:
                    break;
            }
        }

        if (!is_null($t)) $this->db->where("$t.archived", 'N');
    }

    function where_item_is_not_discontinued($t = null)
    {
        if (is_null($t)) {

            switch ($this->product_type) {
                case constant('Regular'):
                case constant('Digital'):
                case constant('ScreenPrint'):
                    $t = $this->t_item;
                    break;

                default:
                    break;
            }
        }
        if (!is_null($t)) $this->db->where_not_in("$t.status_id", $this->product_status_discontinued);
    }

    function where_item_is_not_archived($t = null)
    {
        if (is_null($t)) {
            switch ($this->product_type) {
                case constant('Regular'):
                case constant('Digital'):
                case constant('ScreenPrint'):
                    $t = $this->t_item;
                    break;

                default:
                    break;
            }
        }
        if (!is_null($t)) $this->db->where("$t.archived", "N");
    }

    function _return_if_non_empty($query = null, $array_format = true)
    {
        if (is_null($query)) {
            $query = $this->db->get();
        }

        $data = array();
        if ($query !== FALSE && $query->num_rows() > 0) {
            if ($array_format) {
                $data = $query->result_array();
            } else {
                $data = $query->row_array();
            }
        }
        return $data;
    }
}
