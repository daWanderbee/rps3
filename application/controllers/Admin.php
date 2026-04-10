<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Admin extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model('user_model');
        $this->load->model('transaction_model');
        $this->load->model('task_model');
        $this->load->model('employee_points_model');
    }

    public function bulkAssignTask()
    {
        $json = json_decode(file_get_contents("php://input"), true);

        if (!$json) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
            return;
        }

        $type = $json['type'];
        $targetId = $json['target_id'];
        $employeesToAssign = [];

        if ($type === 'individual') {
            $employeesToAssign[] = $targetId;
        } elseif ($type === 'individual_bulk') {
            $employeesToAssign = explode(',', $targetId);
        } elseif ($type === 'sangh') {
            $list = $this->db->where('sangh_code', $targetId)->get('tbl_employee')->result_array();
            foreach ($list as $emp) {
                $employeesToAssign[] = $emp['emp_id'];
            }
        } elseif ($type === 'all') {
            $list = $this->db->get('tbl_employee')->result_array();
            foreach ($list as $emp) {
                $employeesToAssign[] = $emp['emp_id'];
            }
        }

        if (empty($employeesToAssign)) {
            echo json_encode(['status' => 'error', 'message' => 'No employees found']);
            return;
        }

        $MAX_MONTHLY_ADMIN_POINTS = 500;
        $taskPoints = (int)$json['points'];
        $currentMonth = date('m');
        $currentYear  = date('Y');

        $batchData = [];
        $skipped = 0;
        $partiallyFilled = 0;

        foreach ($employeesToAssign as $empId) {
            $monthlyAdminPoints = $this->db->select_sum('points')
                ->where('emp_id', $empId)
                ->where('is_admin_task', 1)
                ->where('MONTH(created_at)', $currentMonth)
                ->where('YEAR(created_at)', $currentYear)
                ->get('tasks')
                ->row()->points ?? 0;

            $remaining = $MAX_MONTHLY_ADMIN_POINTS - $monthlyAdminPoints;
            if ($remaining <= 0) {
                $skipped++;
                continue;
            }

            $pointsToAssign = min($taskPoints, $remaining);
            if ($pointsToAssign < $taskPoints) {
                $partiallyFilled++;
            }

            $batchData[] = [
                'emp_id'       => $empId,
                'title'        => $json['task_title'],
                'description'  => $json['task_description'],
                'points'       => $pointsToAssign,
                'is_recurring' => $json['is_recurring'] ?? 0,
                'is_completed' => 0,
                'is_admin_task' => 1,
                'created_at'   => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($batchData) && $this->db->insert_batch('tasks', $batchData)) {
            echo json_encode([
                'success' => true,
                'message' => 'Assigned to ' . count($batchData) . ' employees. ' .
                    ($skipped ? "$skipped skipped (limit reached). " : '') .
                    ($partiallyFilled ? "$partiallyFilled partially filled." : ''),
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        echo json_encode(['status' => 'error', 'message' => 'No employees eligible for assignment.']);
    }

    public function getEmployeeTasks()
    {
        $json = json_decode(file_get_contents("php://input"), true);
        $targetEmpId = $json['emp_id'] ?? null;

        if (!$targetEmpId) {
            echo json_encode(['status' => 'error', 'message' => 'No Employee ID provided']);
            return;
        }

        $tasks = $this->db->where('emp_id', $targetEmpId)
            ->order_by('is_completed', 'ASC')
            ->order_by('id', 'DESC')
            ->get('tasks')
            ->result_array();

        echo json_encode($tasks);
    }

    public function markTaskComplete()
    {
        $json = json_decode(file_get_contents("php://input"), true);
        if (!$json || !isset($json['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing task ID']);
            return;
        }

        $task = $this->db->get_where('tasks', ['id' => $json['id']])->row_array();
        if (!$task || $task['is_completed'] == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Task invalid or already completed']);
            return;
        }

        $this->db->trans_start();

        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $time = date('H:i:s');

        // 1. Mark Task Done
        $this->db->where('id', $json['id'])->update('tasks', ['is_completed' => 1]);

        // 2. Log Transaction
        $this->db->insert('transactions', [
            'emp_id'           => $task['emp_id'],
            'points'           => $task['points'],
            'transaction_type' => 'earned',
            'description'      => 'Admin override: ' . $task['title'],
            'transaction_date' => $today,
            'transaction_time' => $time,
            'icon'             => 'verified',
            'created_at'       => $now
        ]);

        // 3. Update User Balance
        $currentPoints = $this->db->get_where('tbl_employee_points', ['emp_id' => $task['emp_id']])->row_array();
        if ($currentPoints) {
            $newPoints = $currentPoints['points_received'] + $task['points'];
            $this->db->where('emp_id', $task['emp_id'])->update('tbl_employee_points', ['points_received' => $newPoints]);
        } else {
            $this->db->insert('tbl_employee_points', [
                'emp_id'         => $task['emp_id'],
                'points_received' => $task['points']
            ]);
        }

        $this->db->trans_complete();

        echo json_encode([
            'success' => true,
            'message' => 'Task marked complete and points awarded.',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
    }

    public function index()
    {
        if ($this->session->userdata('emp_cat') != 1 && $this->session->userdata('emp_cat') != 2) {
            redirect('/');
        }

        $data['employees'] = $this->db->select('emp_id, name, sangh_code, status')
            ->where('name !=', '')
            ->order_by('name', 'ASC')
            ->get('tbl_employee')
            ->result_array();

        $data['sanghs'] = $this->db->select('sangh_code, sangh_name')
            ->distinct()
            ->where('sangh_code !=', '')
            ->where('sangh_code IS NOT NULL')
            ->order_by('sangh_name', 'ASC')
            ->get('tbl_employee')
            ->result_array();

        $this->load->view('admin', $data);
    }
}

