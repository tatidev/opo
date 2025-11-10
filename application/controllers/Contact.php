<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->thisC = 'contact';
        $this->load->model('Contact_model', 'model');
        array_push($this->data['crumbs'], 'Contacts');
    }

    public function showroom()
    {
        array_push($this->data['crumbs'], 'Showrooms');
        $this->data['column_name'] = 'Showroom';
        $this->data['vid'] = $this->input->post('vid');
        $this->data['datatable_title'] = $this->model->get_name(constant('showroom'), $this->data['vid']);
        $this->data['ctype'] = constant('showroom');
        $this->data['editContactUrl'] = site_url('contact/edit');
        $this->data['ajaxUrl'] = site_url('contact/get_showroom_contacts');
        $this->data['hasPermission'] = $this->hasPermission('showroom', 'transaction');
        $this->view('contacts/list');
    }

    public function vendor()
    {
        array_push($this->data['crumbs'], 'Vendors');
        $this->data['column_name'] = 'Vendor';
        $this->data['vid'] = $this->input->post('vid');
        $this->data['datatable_title'] = $this->model->get_name(constant('vendor'), $this->data['vid']);
        $this->data['ctype'] = constant('vendor');
        $this->data['editContactUrl'] = site_url('contact/edit');
        $this->data['ajaxUrl'] = site_url('contact/get_vendor_contacts');
        $this->data['hasPermission'] = $this->hasPermission('vendor', 'transaction');
        $this->view('contacts/list');
    }

    public function get_showroom_contacts()
    {
        $this->filters = array(
            'vid' => $this->input->post('vid'),
            'showroom_only' => true,
            'show_archived' => false
        );
        $this->get_contacts();
    }

    public function get_vendor_contacts()
    {
        $this->filters = array(
            'vid' => $this->input->post('vid'),
            'vendor_only' => true,
            'show_archived' => false
        );
        $this->get_contacts();
    }

    private function get_contacts()
    {
        $list = $this->model->get_contacts($this->filters, true);
        echo json_encode($this->return_datatables_data($list['arr'], $list));
    }

    function edit()
    {
        $this->load->model('Specs_model', 'specs');
        $data['cid'] = $this->input->post('cid');
        $data['ctype'] = $this->input->post('ctype'); // Vendor or Showroom constants
        $data['vid'] = $this->input->post('vid');

        switch ($data['ctype']) {
            case constant('vendor'):
                $data['formTitle'] = 'Vendor Contact Edit';
                $data['dropdown_name'] = 'Vendor';
                $this->filters = array(
                    'vendor_only' => true,
                    'contact_id' => $data['cid']
                );
                $l = $this->specs->get_vendors();
                $options = $this->decode_array($l, 'id', 'name');
                break;

            case constant('showroom'):
                $data['formTitle'] = 'Showroom Contact Edit';
                $data['dropdown_name'] = 'Showroom';
                $this->filters = array(
                    'showroom_only' => true,
                    'contact_id' => $data['cid']
                );
                $l = $this->specs->get_showrooms();
                $options = $this->decode_array($l, 'id', 'name');
                break;
        }

        $data['info'] = $this->model->get_contacts($this->filters, false);
        $data['dropdown'] = form_dropdown('owner[]', $options, set_value('owner[]', $data['vid']), " class='single-dropdown w-filtering' tabindex='-1' ");
        $data['hasPermission'] = $this->hasPermission('contact', 'edit');

        $ret['html'] = $this->load->view('contacts/form/view', $data, true);
        echo json_encode($ret);
    }

    public function save()
    {
        //var_dump($_POST);exit;
        $this->load->library('form_validation');
        $this->form_validation->set_rules($this->model->rules);

        if ($this->form_validation->run() == TRUE) {

            $this->db->trans_begin();

            $data['cid'] = $this->input->post('cid');
            $data['ctype'] = $this->input->post('ctype'); // Vendor or Showroom constants
            $data['vid'] = $this->input->post('vid');

            $insert = array(
                'name' => $this->input->post('c_name'),
                'company' => $this->input->post('c_company'),
                'position' => $this->input->post('c_position'),
                'address_1' => $this->input->post('c_address_1'),
                'address_2' => $this->input->post('c_address_2'),
                'city' => $this->input->post('c_city'),
                'state' => $this->input->post('c_state'),
                'zipcode' => $this->input->post('c_zipcode'),
                'country' => $this->input->post('c_country'),
                'tel_1' => $this->input->post('c_tel_1'),
                'tel_2' => $this->input->post('c_tel_2'),
                'email_1' => $this->input->post('c_email_1'),
                'email_2' => $this->input->post('c_email_2'),
                'user_id' => $this->data['user_id']
            );

            if ($data['cid'] === '0') {
                // New
                $data['cid'] = $this->model->save_contact($insert);
            } else {
                $this->model->save_contact($insert, $data['cid']);
            }
            $insert['id'] = $data['cid'];

            // Update owner relationship
            // Either a 'Vendor' or a 'Showroom'

            $owner = $this->input->post('owner');
            $owner_id = $owner[0];
            $this->model->save_contact_relation($data['ctype'], $data['cid'], $owner_id, $data['vid']);

            // Get for view only
            $c_owner = $this->model->get_name($data['ctype'], $owner_id);
            $insert['c_owner_abrev'] = $c_owner['abrev'];

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $ret['success'] = false;
                $ret['message'] = 'Some error ocurred during transaction.';
            } else {
                $this->db->trans_commit();
                $ret['item'] = $insert;
                $ret['success'] = true;
            }
        } else {
            $ret['success'] = false;
            $ret['message'] = validation_errors();
        }

        echo json_encode($ret);
    }


}
