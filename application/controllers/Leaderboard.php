<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Leaderboard extends MY_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library(['session']);
        $this->load->helper(['url']);
        $this->load->model('employee_points_model');
    }

    public function index()
    {
        $type = $this->input->get('type') ? $this->input->get('type') : 'monthly';

        // 1. Fetch Leaderboard Data based on type
        if ($type === 'quarterly') {
            $leaderboard = $this->employee_points_model->getQuarterlyLeaderboard();
        } else {
            $leaderboard = $this->employee_points_model->getMonthlyLeaderboard();
        }

        $data = [
            'leaderboard' => $leaderboard,
            'streaks' => $this->employee_points_model->getActiveStreaks(),
            'type'        => $type,
            'currentUser' => $this->session->userdata('emp_code'),
        ];
        
        $this->load->view('leaderboard', $data);
    }
}

