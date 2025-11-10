<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class History extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->thisC = 'history';
        $this->load->library('table');
        $this->load->model('Product_model', 'model'); // Product_model extends Logs_model !!!

        $this->model->init_date_filter($this->input->post('date_from'), $this->input->post('date_to'));
        $this->data['date_from'] = $this->input->post('date_from');
        $this->data['date_to'] = $this->input->post('date_to');

    }

    public function product($product_id)
    {
        $this->data['product_id'] = ($this->input->post('product_id') === null ? $product_id : $this->input->post('product_id'));
        $data = $this->model->history_product($this->data['product_id']);
        $this->data['table'] = $this->table->generate($data);
        $this->load->view('history/product_price', $this->data);
    }

    public function prices($product_type = '', $product_id = '')
    {

        $this->data['product_id'] = ($this->input->post('product_id') === null ? $product_id : $this->input->post('product_id'));
        $this->data['product_type'] = ($this->input->post('product_type') === null ? $product_type : $this->input->post('product_type'));

        $this->data['pinfo'] = $this->model->get_product_data($this->data['product_type'], $this->data['product_id']);
        //var_dump($this->data['pinfo']);
        $this->data['product_name'] = $this->data['pinfo']['product_name'];
        $this->data['vendors_name'] = $this->data['pinfo']['vendors_name'];
        $this->data['vendor_product_name'] = $this->data['pinfo']['vendor_product_name'];

        $data = $this->model->history_product_price($this->data['product_type'], $this->data['product_id'], $this->data['product_name']);
        $this->table->set_template($this->table_template("", " class='table table-sm' "));
        $this->table->set_heading('Product', 'Res/Cut', 'Hosp/Cut', 'Hosp/Roll', 'Dig. Print/Res', 'Dig. Print/Hosp', 'Date', 'User');
        $this->data['tablePrices'] = $this->table->generate($data);

        $data = $this->model->history_product_cost($this->data['product_type'], $this->data['product_id']);
        $this->table->set_template($this->table_template("", " class='table table-sm' "));
        $this->table->set_heading('Product', 'FOB', 'Cut', 'Half Roll', 'Roll', 'Roll Landed', 'Roll Ex Mill', 'Date', 'User');
        $this->data['tableCosts'] = $this->table->generate($data);

        $this->load_print_libraries();
        $this->load->view('history/product_price', $this->data);
    }

}
