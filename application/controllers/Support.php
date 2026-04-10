<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Support extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model('support_model');
    }

    public function index()
    {
        $data['faqs'] = $this->db->get('faqs')->result_array();

        $this->load->view('support', $data);
    }
}

