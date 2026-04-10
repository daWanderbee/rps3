<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Redeem extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation', 'email']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model(['reward_redemptions_model', 'employee_points_model', 'redeem_model']);
        $this->load->database();
    }

    public function index()
    {
        // Get the category from the URL (e.g., ?category=wellbeing)
        $selectedCategory = $this->input->get('category') ? $this->input->get('category') : 'all';

        // Fetch filtered rewards from SQL
        if ($selectedCategory === 'all') {
            $redeem = $this->db->get('redeemable_rewards')->result_array();
        } else {
            $redeem = $this->db->where('category', $selectedCategory)->get('redeemable_rewards')->result_array();
        }

        $empId = $this->session->userdata('emp_code');

        foreach ($redeem as &$reward) {
            $status = $this->db->where('emp_id', $empId)
                ->where('reward_id', $reward['id'])
                ->get('reward_redemptions')
                ->row_array();

            if ($status && strtotime($status['redeemable_after']) > time()) {
                $remaining = strtotime($status['redeemable_after']) - time();
                $reward['lock_days'] = ceil($remaining / (60 * 60 * 24));
            } else {
                $reward['lock_days'] = 0;
            }
        }

        $data = [
            'rewards'          => $redeem,
            'selectedCategory' => $selectedCategory,
            'categories'       => [
                'all' => 'All',
                'everyday_rewards' => 'Everyday',
                'lifestyle_essentials' => 'Lifestyle Essentials',
                'premium_merchandise' => 'Premium Merchandise',
                'tech_and_vouchers' => 'Tech & Vouchers',
                'wellbeing' => 'Mindful Living & Wellbeing',
                'lifestyle_experiences' => 'Lifestyle & Experiences'
            ]
        ];

        $result = $this->employee_points_model->getEmployeePoints($empId);
        $data['userPoints'] = $result['points_received'] ?? 0;

        $this->load->view('redeem', $data);
    }

    public function processRedemption()
    {
        $json = json_decode(file_get_contents("php://input"), true);

        if (!$json) {
            echo json_encode(['success' => false, 'message' => 'No data received']);
            return;
        }

        $cost = abs($json['points']);
        $rewardId = $json['reward_id'];
        $empId = $this->session->userdata('emp_code');

        $lastRedemption = $this->db->where('emp_id', $empId)
            ->where('reward_id', $rewardId)
            ->order_by('redeemed_at', 'DESC')
            ->get('reward_redemptions')
            ->row_array();

        if ($lastRedemption) {
            $today = time();
            $unlockDate = strtotime($lastRedemption['redeemable_after']);

            if ($today < $unlockDate) {
                $diff_days = ceil(($unlockDate - $today) / (60 * 60 * 24));
                echo json_encode([
                    'success' => false,
                    'message' => "This reward is locked. Available in {$diff_days} days."
                ]);
                return;
            }
        }

        $userPointsData = $this->employee_points_model->getEmployeePoints($empId);
        $currentPoints = $userPointsData['points_received'];
        if ($currentPoints < $cost) {
            echo json_encode(['success' => false, 'message' => 'Insufficient points']);
            return;
        }

        // Deduct points
        $this->employee_points_model->deductPoints($empId, $cost);

        $now = date('Y-m-d H:i:s');
        $today_date = date('Y-m-d');
        $time_now = date('H:i:s');
        $unlockAfter = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Log to redemptions
        $this->db->insert('reward_redemptions', [
            'emp_id'           => $empId,
            'reward_id'        => $rewardId,
            'redeemed_at'      => $now,
            'redeemable_after' => $unlockAfter
        ]);

        $transactionData = [
            'emp_id'           => $empId,
            'transaction_date' => $today_date,
            'transaction_time' => $time_now,
            'transaction_type' => 'debit',
            'description'      => 'Redeemed ' . $json['reward_name'],
            'points'           => -$cost,
            'icon'             => 'shopping_bag',
            'created_at'       => $now
        ];

        if ($this->db->insert('transactions', $transactionData)) {

            // 🔔 SEND REDEMPTION EMAIL
            $employeeRow = $this->db->select('email')->where('emp_id', $empId)->get('tbl_employee')->row();
            $employeeEmail = $employeeRow ? $employeeRow->email : '';

            if ($employeeEmail) {
                $this->email->to($employeeEmail);
                $this->email->subject("{$json['reward_name']} Redeemed Successfully");
                $message = "
                <!DOCTYPE html>
                <html>
                <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
                    <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color: #f4f4f4; padding: 20px 0;'>
                        <tr>
                            <td align='center'>
                                <table width='600' cellpadding='0' cellspacing='0' border='0' style='background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                                    <tr>
                                        <td style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; color: white;'>
                                            <h1 style='margin: 0;'>RPS Rewards</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 40px 30px;'>
                                            <h2 style='color: #333333;'>Redemption Successful! 🎉</h2>
                                            <p style='color: #666666;'>Your reward has been successfully redeemed.</p>
                                            <table width='100%' style='background-color: #f8f9fa; border-radius: 6px; border-left: 4px solid #667eea; padding: 20px;'>
                                                <tr><td><strong>Reward:</strong></td><td align='right'>{$json['reward_name']}</td></tr>
                                                <tr><td><strong>Points Used:</strong></td><td align='right'>{$cost}</td></tr>
                                                <tr><td><strong>Date:</strong></td><td align='right'>" . date('d M Y') . "</td></tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>";
                $this->email->message($message);
                $this->email->send();
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
}

}
