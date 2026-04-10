<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Management extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security']);
        $this->load->model(['user_model', 'transaction_model', 'task_model', 'employee_points_model']);
        $this->load->database();
    }

    public function index()
    {
        $managementId = $this->session->userdata('emp_code');
        
        if ($this->session->userdata('emp_cat') != 1 && $this->session->userdata('emp_cat') != 2) {
            redirect('/');
        }

        if (!$managementId) {
            redirect('/login');
        }

        $managementInfo = $this->db->get_where('tbl_employee', ['emp_id' => $managementId])->row_array();
        if (!$managementInfo) {
            echo "User record not found.";
            return;
        }

        $currentCat = (int)$managementInfo['emp_cat'];
        $managementSanghCode = $managementInfo['sangh_code'] ?? '';

        $this->db->select('*');
        $this->db->from('tbl_employee');

        // --- HIERARCHY LOGIC ---
        if ($currentCat === 1) {
            $this->db->where('emp_cat', 2);
        } elseif ($currentCat === 2) {
            $this->db->where('emp_cat', 3)
                     ->where('sangh_code', $managementSanghCode);
        } else {
            $this->db->where('emp_id', 0);
        }

        $data['employees'] = $this->db->get()->result_array();
        $data['management_cat'] = $currentCat;

        $this->load->view('management', $data);
    }

    public function addTask()
    {
        try {
            $managementId = $this->session->userdata('emp_code');
            $managementSanghCode = $this->session->userdata('sangh_code');

            $managementInfo = $this->db->get_where('tbl_employee', ['emp_id' => $managementId])->row_array();
            $currentCat = (int)$managementInfo['emp_cat'];

            $targetEmpId = $this->input->post('emp_id');
            $points = (int)$this->input->post('points');

            // Start building security check
            $this->db->where('emp_id', $targetEmpId);

            if ($currentCat === 1) {
                $this->db->where('emp_cat', 2);
            } elseif ($currentCat === 2) {
                $this->db->where('emp_cat', 3)
                         ->where('sangh_code', $managementSanghCode);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
                return;
            }

            $isAllowed = $this->db->get('tbl_employee')->row_array();

            if (!$isAllowed) {
                echo json_encode(['status' => 'error', 'message' => 'Security Error: Unauthorized management level']);
                return;
            }

            $totalAllowedPoints = 3000;
            $remaining = $this->task_model->getRemainingPoints($targetEmpId, $totalAllowedPoints);

            if ($points > $remaining) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Points exceed remaining allocatable limit.',
                    'csrf_token' => $this->security->get_csrf_hash()
                ]);
                return;
            }

            $insertData = [
                'emp_id'       => $targetEmpId,
                'title'        => $this->input->post('task_title'),
                'points'       => $points,
                'is_recurring' => $this->input->post('is_recurring') ?? 0,
                'is_completed' => 0,
                'created_at'   => date('Y-m-d H:i:s')
            ];

            if ($this->db->insert('tasks', $insertData)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Task added successfully',
                    'csrf_token' => $this->security->get_csrf_hash()
                ]);
                return;
            }

            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
        }
    }

    public function getEmployeeTasks()
    {
        try {
            $managementId = $this->session->userdata('emp_code');
            $managementInfo = $this->db->get_where('tbl_employee', ['emp_id' => $managementId])->row_array();
            $currentCat = (int)$managementInfo['emp_cat'];

            $targetEmpId = $this->input->post('emp_id');

            if (!$targetEmpId) {
                echo json_encode(["status"=>"error", "message" => 'No Employee ID provided']);
                return;
            }

            $this->db->select('tasks.*, tbl_employee.name as employee_name');
            $this->db->from('tasks');
            $this->db->join('tbl_employee', 'tbl_employee.emp_id = tasks.emp_id');
            $this->db->where('tasks.emp_id', $targetEmpId);

            if ($currentCat === 2) {
                $this->db->where('tbl_employee.sangh_code', $this->session->userdata('sangh_code'));
            }

            $tasks = $this->db->order_by('tasks.is_completed', 'ASC')
                ->order_by('tasks.id', 'DESC')
                ->get()
                ->result_array();

            echo json_encode([
                'tasks' => $tasks,
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        } catch (\Exception $e) {
            echo json_encode(["status"=>"error", "message" => 'Server error']);
        }
    }

    public function markTaskComplete()
    {
        try {
            $taskId = $this->input->post('id');
            if (!$taskId) {
                echo json_encode(["status"=>"error", "message" => 'Missing task ID']);
                return;
            }

            $task = $this->db->get_where('tasks', ['id' => $taskId])->row_array();
            if (!$task || $task['is_completed'] == 1) {
                echo json_encode(["status"=>"error", "message" => 'Task invalid or already done']);
                return;
            }

            $this->db->trans_start();

            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            $time = date('H:i:s');

            $this->db->where('id', $taskId)->update('tasks', ['is_completed' => 1]);

            $this->db->insert('transactions', [
                'emp_id'           => $task['emp_id'],
                'points'           => $task['points'],
                'transaction_type' => 'earned',
                'description'      => 'Task completed: ' . $task['title'],
                'transaction_date' => $today,
                'transaction_time' => $time,
                'icon'             => 'task_alt',
                'created_at'       => $now
            ]);

            $currentPointsRow = $this->db->get_where('tbl_employee_points', ['emp_id' => $task['emp_id']])->row_array();
            if ($currentPointsRow) {
                $newPoints = $currentPointsRow['points_received'] + $task['points'];
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
                'message' => 'Points awarded successfully',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        } catch (\Exception $e) {
            echo json_encode(["status"=>"error", "message" => 'Server error']);
        }
    }

    public function getRemainingPoints()
    {
        $empId = $this->input->post('emp_id');

        if (!$empId) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid employee'
            ]);
            return;
        }

        $totalAllowedPoints = 3000;
        $remaining = $this->task_model->getRemainingPoints($empId, $totalAllowedPoints);

        echo json_encode([
            'success' => true,
            'remaining' => $remaining,
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
    }
}

