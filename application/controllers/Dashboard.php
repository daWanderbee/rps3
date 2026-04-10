<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model('leaderboard_model');
        $this->load->model('reward_redemptions_model');
        $this->load->model('employee_points_model');
        $this->load->model('transaction_model');
        $this->load->model('task_model');
        $this->load->model('redeem_model');
    }

    public function index()
    {
        $empId = $this->session->userdata('emp_code');
        

        $empModel = $this->employee_points_model;
        $leaderboardModel = $this->leaderboard_model;
        $redeemModel = $this->redeem_model;
        $taskModel = $this->task_model;
        $redemptionsModel = $this->reward_redemptions_model;

        $user = $empModel->getEmployeePoints($empId);

        $points = $user['points_received'] ?? 0; // fallback if null

        // Fetch top 5 from SQL
        $rows = $empModel->getMonthlyLeaderboard();
        $tasks = $taskModel->getTopTasks(4, $empId);

        $affordableRewards = $redeemModel->getRedeemable($points);
        if (empty($affordableRewards)) {
            $affordableRewards = $redeemModel->getCheapestRewards(4);
        }

        // NEW: Inject Lock Status
        foreach ($affordableRewards as &$reward) {
            $status = $this->db->where('emp_id', $empId)
                ->where('reward_id', $reward['id'])
                ->order_by('redeemed_at', 'DESC')
                ->get('reward_redemptions')
                ->row_array();

            if ($status && strtotime($status['redeemable_after']) > time()) {
                $remaining = strtotime($status['redeemable_after']) - time();
                $reward['lock_days'] = ceil($remaining / (60 * 60 * 24));
            } else {
                $reward['lock_days'] = 0;
            }
        }

        // Transform SQL rows → leaderboard format
        $leaderboard = [];
        foreach ($rows as $index => $row) {
            $leaderboard[] = [
                'rank'   => $index + 1,
                'name'   => $row['name'],
                'points' => $row['points'],
            ];
        }

        $transactionModel = $this->transaction_model;
        $transactions = $transactionModel->getTransactionsByEmployee($empId);

        $this->load->view('dashboard', [
            'user'        => $user,
            'leaderboard' => $leaderboard,
            'tasks'       => $tasks,
            'transactions' => $transactions,
            'featuredRewards' => $affordableRewards,
        ]);
    }
}
