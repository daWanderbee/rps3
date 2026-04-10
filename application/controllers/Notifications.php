<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Notifications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model('transaction_model');
    }

    public function index()
    {
        $empId = $this->session->userdata('emp_code');
        $transactions = $this->transaction_model->getNotificationsByEmployee($empId);

        $this->load->view('notifications', [
            'notifications' => $transactions,
        ]);
    }

    public function markRead()
    {
        $id = $this->input->post('id');
        $this->transaction_model->markAsRead($id);
        echo json_encode(['status' => 'ok']);
    }

    public function markAllRead()
    {
        $empId = $this->session->userdata('emp_code');
        $this->transaction_model->markAllAsRead($empId);
        echo json_encode(['status' => 'ok']);
    }

    public function discard()
    {
        $id = $this->input->post('id');
        $this->db->where('id', $id)->update('transactions', ['isDiscarded' => 1]);
        echo json_encode(['status' => 'ok']);
    }

    public function discardAll()
    {
        $empId = $this->session->userdata('emp_code');
        $this->transaction_model->discardAll($empId);
        echo json_encode(['status' => 'ok']);
    }
}

