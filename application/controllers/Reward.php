<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Reward extends MY_Controller
{
    protected $id;
    protected $userRole;
    protected $roleRank = [
        'Utpadak' => 3,
        'Sangrakshak' => 2,
        'Sevak' => 1,
    ];

    protected $roleMap = [
        3 => 'Utpadak',
        2 => 'Sangrakshak',
        1 => 'Sevak',
    ];

    protected $monthlyBank = [
        'Utpadak' => 0,
        'Sangrakshak' => 2000,
        'Sevak' => 5000,
    ];
    protected $rewardLimits = [
        'Sangrakshak' => [
            'Utpadak' => [300, 800],
        ],
        'Sevak' => [
            'Utpadak' => [300, 1200],
            'Sangrakshak' => [600, 2000],
        ],
    ];

    protected $recipients = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model(['user_model', 'transaction_model', 'employee_points_model']);
        
        $this->id = $this->session->userdata('emp_id');
        $empCat = $this->session->userdata('emp_cat');

        $this->userRole = $this->roleMap[(int)$empCat] ?? 'Utpadak';
    }

    public function index()
    {
        if ($this->session->userdata('emp_cat') != 1 && $this->session->userdata('emp_cat') != 2) {
            redirect('/');
        }

        $userData = $this->db->get_where('tbl_employee', ['emp_id' => $this->id])->row_array();

        $transactions = [];
        $tasks = [];
        $leaderboard = [];

        $this->recipients = $this->employee_points_model->getEmployeesWithPoints();

        if ($this->input->is_ajax_request()) {
            $result = $this->processReward();
            echo json_encode($result);
            return;
        }

        $data = [
            'userId'       => $this->id,
            'user'         => $userData,
            'userRole'     => $this->userRole,
            'userBalance' => $this->getRemainingBalance(),
            'rewardLimits' => $this->rewardLimits,
            'recipients'   => $this->recipients,
            'transactions' => $transactions,
            'tasks'        => $tasks,
            'leaderboard'  => $leaderboard,
            'alert'        => null,
        ];

        $this->load->view('reward', $data);
    }

    protected function processReward()
    {
        try {
            $recipientId = (int) $this->input->post('recipient');
            $points = (int) $this->input->post('points');
            $userBalance = $this->getRemainingBalance();

            $recipient = $this->employee_points_model->getEmployeeById($recipientId);

            if (!$recipient) {
                return ['status' => 'error', 'message' => 'Invalid recipient.', 'newBalance' => $userBalance];
            }

            $recipientRole = $this->roleMap[(int)$recipient['emp_cat']] ?? null;
            if (!$recipientRole) {
                return ['status' => 'error', 'message' => 'Invalid recipient role.', 'newBalance' => $userBalance];
            }

            $senderRole = $this->userRole;
            if (is_numeric($senderRole)) {
                $senderRole = $this->roleMap[(int)$senderRole] ?? null;
            } else {
                $senderRole = ucfirst(strtolower(trim($senderRole)));
            }

            if (!$senderRole) {
                return ['status' => 'error', 'message' => 'Invalid sender role.', 'newBalance' => $userBalance];
            }

            $columnToUpdate = '';
            if ($senderRole === 'Sevak') {
                $columnToUpdate = 'pointsfromSevak';
            } elseif ($senderRole === 'Sangrakshak') {
                $columnToUpdate = 'pointsfromSangrakshak';
            }

            if (empty($columnToUpdate)) {
                return ['status' => 'error', 'message' => 'Your role does not have a specific reward allocation.'];
            }

            $currentPointsFromThisRole = (int)($recipient[$columnToUpdate] ?? 0);
            $limits = $this->rewardLimits[$senderRole][$recipientRole] ?? null;

            if (!$limits) {
                return [
                    'status' => 'error',
                    'message' => 'You are not allowed to reward this role.',
                    'newBalance' => $userBalance
                ];
            }

            [$min, $max] = $limits;

            if (($currentPointsFromThisRole + $points) > $max) {
                $remainingAllowed = $max - $currentPointsFromThisRole;
                if ($remainingAllowed < 0)
                    return [
                        'status' => 'error',
                        'message' => "Recipient has already received the maximum points from $senderRole.",
                        'newBalance' => $userBalance
                    ];
                return [
                    'status' => 'error',
                    'message' => "Recipient cap reached. They can only receive $remainingAllowed more points from $senderRole (Max: $max)."
                ];
            }

            $reason = $this->input->post('reason');

            if ($points < $min || $points > $max) {
                return ['status' => 'error', 'message' => "Limit: $min - $max pts.", 'newBalance' => $userBalance];
            }

            if ($points > $userBalance) {
                return ['status' => 'error', 'message' => "Insufficient balance ($userBalance left)."];
            }

            $this->db->trans_start();

            // Update points
            $this->employee_points_model->updateSpecificPoints(
                $recipientId,
                $points,
                $columnToUpdate
            );

            $this->employee_points_model->addRewardedPoints($this->id, $points, $senderRole);

            // Insert transaction (recipient)
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            $time = date('H:i:s');

            $this->db->insert('transactions', [
                'emp_id'           => $recipientId,
                'points'           => $points,
                'transaction_type' => 'earned',
                'description'      => "Reward from $senderRole for $reason",
                'transaction_date' => $today,
                'transaction_time' => $time,
                'icon'             => 'volunteer_activism',
                'created_at'       => $now
            ]);

            $this->db->trans_complete();

            return [
                'status' => 'success',
                'message' => "Sent $points points to {$recipient['name']}!",
                'newBalance' => $this->getRemainingBalance()
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function getRemainingBalance()
    {
        $rewarded = $this->employee_points_model->getRewardedPoints($this->id);
        $monthlyLimit = $this->monthlyBank[$this->userRole];

        return max(0, $monthlyLimit - $rewarded);
    }
}

