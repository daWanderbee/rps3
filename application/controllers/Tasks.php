<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Tasks extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model('task_model');
        $this->load->model('employee_points_model');
    }

    public function index()
    {
        $empId = $this->session->userdata('emp_code');
        $data['tasks'] = $this->task_model->getTasksByEmployee($empId);
        $result = $this->employee_points_model->getEmployeePoints($empId);
        $data['userPoints'] = $result['points_received'] ?? 0;
        
        $this->load->view('tasks', $data);
    }
}

