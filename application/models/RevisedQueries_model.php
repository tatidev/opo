<?php

class RevisedQueries_model extends MY_Model
{
    protected $product_type;

    function __construct()
    {
        parent::__construct();

        $this->model_table = $this->t_item;
        $this->defaults = array(
            // Default filters
            'product_type' => null,
            'product_id' => null,
            'item_id' => null,
            'datatable' => false,
            'show_discontinued' => false,
            'show_archived' => false,
            'includeCosts' => false
        );
        $this->load->model('Item_model');
        $this->filters = array();
    }


    /**
     * Get item details by product id
     * 
     * @param int $product_id
     * @param string $product_type
     * @return array
     * 
     * example usage:   
     * $product_id = 1;
     * $product_type = 'R';
     * $items = $this->revisedqueries->get_item_details_by_product_id($product_id, $product_type);   
     * 
     * 
     */
    public function get_item_details_by_product_id($product_id, $product_type)
    {
        $items = [];
        $items_fetched = $this->get_items_by_product_id($product_id, $product_type);
        foreach ($items_fetched as $item) {
            $item_details = $this->get_item_details($item['item_id'], $product_type);
            if ($product_type === 'D') {
                $item_details['product_name'] = $this->Item_model->get_product_name($product_type, $product_id);
            }
            $items[] = $item_details;
        }
        return $items;
    }

    /**
     * Get items by product id
     * 
     * @param int $product_id
     * @param string $product_type
     * @return array
     * 
     * example usage:   
     * $product_id = 1;
     * $product_type = 'R';
     * $items = $this->revisedqueries->get_items_by_product_id($product_id, $product_type);   
     * (If not archived)
     * 
     */
    public function get_items_by_product_id($product_id, $product_type)
    {
        $this->db->select('i.id AS item_id, i.code, i.archived')
            ->from('T_ITEM AS i')
            ->where('i.product_id', $product_id)
            ->where('i.product_type', $product_type)
            ->where('i.archived', 'N')
            ->order_by('i.code', 'ASC');
        // Execute the query
        $query = $this->db->get();
        // Fetch the result as an array
        $results = $query->result_array();
        $sql = $this->db->get_compiled_select();
        //$sql = $this->db->last_query();
        return $results;
    }

    /**
     * Get items by product id
     * 
     * @param int $product_id
     * @param string $product_type
     * @return array
     * 
     * example usage:   
     * $product_id = 1;
     * $product_type = 'R';
     * $items = $this->revisedqueries->get_items_by_product_id($product_id, $product_type);   
     * (If not archived)
     * 
     */
    public function get_item_details_by_item_id($iid)
    {
        $this->db->select('i.id AS item_id, i.product_type,i.code, i.archived')
            ->from('T_ITEM AS i')
            ->where('i.id', $iid)
            ->where('i.archived', 'N')
            ->order_by('i.code', 'ASC');
        // Execute the query
        $query = $this->db->get();
        // Fetch the result as an array
        $items_fetched = $query->result_array();

        $sql = $this->db->get_compiled_select();
        //$sql = $this->db->last_query();
        foreach ($items_fetched as $item) {
            $item_details = $this->get_item_details($item['item_id'], $item['product_type']);
 
            if ($product_type === 'D') {
                $item_details['product_name'] = $this->Item_model->get_product_name($product_type, $product_id);
            }
            $items[] = $item_details;
        }
        return $items;
    }


    /**
     * Get items along with details
     * 
     * @param int $item_id
     * @return array
     * 
     * example usage:   
     * $item_id = 1;
     * $item = $this->revisedqueries->get_item_details($item_id);   
     * 
     * 
     */
    public function get_item_details($item_id, $product_type)
    {

        // JSON_ARRAYAGG(resel.item_id_1) AS reselections_ids,
        // CONCAT('[', GROUP_CONCAT(resel.item_id_1), ']') AS reselections_ids,
        $this->db->select("
          i.*, i.code, i.id AS item_id, 
          i.product_id,
          i.product_type AS product_type,
          i.archived,
          i.web_vis,
          i.web_vis_toggle,
          i.exportable,
          sales_stock.id AS sales_id,
          sales_stock.yardsInStock,
		  sales_stock.yardsOnHold,
		  sales_stock.yardsAvailable,
		  sales_stock.yardsOnOrder,
		  sales_stock.yardsBackorder,
          showp.visible as parent_product_visibility,
          pp.p_dig_hosp,
          pp.p_dig_res,
          pp.p_res_cut,
          pp.p_hosp_cut,
          pp.p_hosp_roll,
          pp.p_hosp_roll as volume_price,
          ppc.cost_cut,
          ppc.cost_half_roll,
          ppc.cost_roll,
          ppc.cost_roll_ex_mill,
          ppc.cost_roll_landed,
          mpl.price_date,
          samploc.name AS roll_location,
          GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR '/') AS color,
          GROUP_CONCAT(coorclr.coord_color_id SEPARATOR ' / ') AS showcase_coord_color_id,
          ishelf.shelf_id,
          JSON_ARRAYAGG(resel.item_id_1) AS reselections_ids,
          showi.visible AS showcase_visible,
          showi.url_title AS url_title,
          showi.pic_big_url AS pic_big_url,
          showi.pic_hd_url AS pic_hd_url,
          shelf.name AS shelf,
          ps.id AS status_id,
          ps.name    AS status,
          ps.descr   AS status_descr,
          ss.id AS stock_status_id,
          ss.name    AS stock_status,
          ss.descr   AS stock_status_descr,
          t_various.vendor_product_name
        ");
        $this->db->from("T_ITEM AS i");
        if ($product_type === 'R') {
            $this->db->select("p.name AS product_name");
            $this->db->select("p.in_master AS in_master_product");
            $this->db->join("T_PRODUCT AS p", "p.id = i.product_id", "left");
        }
        if ($product_type === 'D') {
            $this->db->select("digp.reverse_ground AS reverse_ground, digp.style_id AS style_id");
            $this->db->join("T_PRODUCT_X_DIGITAL AS digp", "digp.id = i.product_id", "left");
        }
        // plain string (works in every CI‑3.x version)
        $this->db->join('T_PRODUCT_PRICE AS pp', 'pp.product_id   = i.product_id AND pp.product_type = i.product_type', 'left');
        $this->db->join("T_PRODUCT_PRICE_COST AS ppc", "ppc.product_id = i.product_id", "left");
        $this->db->join("T_PRODUCT_USE AS pu", "pu.product_id = i.product_id", "left");
        $this->db->join("SHOWCASE_PRODUCT AS showp", "showp.product_id = i.product_id", "left");
        $this->db->join("P_USE AS u", "u.id = pu.use_id", "left");
        $this->db->join("PROC_MASTER_PRICE_LIST AS mpl", "mpl.product_id = i.product_id", "left");
        $this->db->join("P_PRODUCT_STATUS AS ps", "ps.id = i.status_id", "left");
        $this->db->join("P_STOCK_STATUS AS ss", "ss.id = i.stock_status_id", "left");
        $this->db->join("T_ITEM_COLOR AS ic", "ic.item_id = i.id", "left");
        $this->db->join("P_COLOR AS c", "c.id = ic.color_id", "left");
        $this->db->join("T_ITEM_SHELF AS ishelf", "ishelf.item_id = i.id", "left");
        $this->db->join("P_SHELF AS shelf", "shelf.id = ishelf.shelf_id", "left");
        $this->db->join("P_SAMPLING_LOCATIONS AS samploc", "samploc.id = i.roll_location_id", "left");
        $this->db->join("T_ITEM_MESSAGES AS mess", "mess.item_id = i.id", "left");
        $this->db->join("T_ITEM_STOCK AS istock", "istock.item_id = i.id", "left");
        $this->db->join("T_ITEM_RESELECTION AS resel", "resel.item_id_0 = i.id", "left");
        $this->db->join("SHOWCASE_ITEM AS showi", "showi.item_id = i.id", "left");
        $this->db->join("SHOWCASE_ITEM_COORD_COLOR AS coorclr", "i.id = coorclr.item_id", "left");
        $this->db->join($this->db_sales . '.op_products_stock AS sales_stock', 'i.id = sales_stock.master_item_id', 'left');
        $this->db->join('T_PRODUCT_VARIOUS as t_various', 'i.product_id = t_various.product_id', 'left');
        $this->db->where("i.id", $item_id);
        //$this->db->where("i.archived", 'N');
        $this->db->where("i.product_type", $product_type);
        //$this->db->where("pp.product_type", $product_type);
        $this->db->group_by("i.id");
        $query = $this->db->get();
        //$sql = $this->db->last_query();
        //return $sql;
        return $query->row_array(); // Return a single row as an associative array
    }


    function get_item_type($item_id)
    {
        $this->db
            ->select('product_type')
            ->from($this->t_item)
            ->where("id", $item_id);
        return $this->db->get()->row()->product_type;
    }


    /**
     * Get product type by product id
     * 
     * @param int $product_id
     * @return string
     * 
     * example usage:   
     * $product_id = 1;
     * $product_type = $this->revisedqueries->get_product_type_by_pid($product_id);   
     * 
     * 
     */
    //  public function get_product_type_by_pid($pid)
    //  {
    //      $this->db->select('type')
    //      ->from('T_PRODUCT')
    //      ->where('id', $pid);
    //      $query = $this->db->get();
    //      return $query->row()->type;
    //  }

    public function get_digital_style_id_by_pid($pid)
    {
        $this->db
            ->select('style_id')
            ->from($this->product_digital)
            ->where("id", $pid);
        return $query->row()->style_id;
    }



    /*
     * Get product type from product id
     * Checks regular product first
     * @param int $pid
     * @return string  ( R  or D  or FALSE )
     * 
     * example usage:
     * $product_id = 1;
     * $product_type = $this->revisedqueries->get_product_type_from_pid($product_id);
     * 
     */
    //function get_product_type_by_pid($pid){
    //    $this->db->select('type')
    //    ->from('T_PRODUCT')
    //    ->where('id', $pid);
    //    $query = $this->db->get();
    //    $pt = $query->row()->type;
    //    echo "<pre>get_product_type_by_pid() CLASS: " . __CLASS__ . " FUNCTION: " . __FUNCTION__ . " LINE: " . __LINE__ . " <br>";
    //    echo "sql: " . $this->db->last_query() . "<br>";
    //    echo "product_id: " . $pid . "<br>";
    //    echo "product_type: " . $pt . "<br>";
    //    echo "</pre>";
    //	if($pt === 'R'){
    //        return $pt;
    //    }else{
    //      $style_id = $this->get_digital_style_id_by_pid($pid);
    //      if( is_numeric($style_id)){
    //        return 'D';
    //      }
    //    }
    //    return FALSE;
    //}





}
