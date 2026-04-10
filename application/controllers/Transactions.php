<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Transactions extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model('transaction_model');
    }

    public function index()
    {
        $empId = $this->session->userdata('emp_code');
        $data['transactions'] = $this->transaction_model->getTransactionsByEmployee($empId);

        $this->load->view('transactions', $data);
    }
}

