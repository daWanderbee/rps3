<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Task_model extends CI_Model
{
        
    
    public function getTopTasks($limit = 4, $emp_id)
    {
        return $this->db->where('emp_id', $emp_id)
            ->order_by('points', 'DESC')
            ->limit($limit)
            ->get('tasks')->result_array();
    }

    public function refreshTasks()
    {
        // Delete non-recurring tasks that are finished
        $this->db->where('is_recurring', 0)
            ->where('is_completed', 1)
            ->delete('tasks');

        // Reset recurring tasks so they can be done again
        return $this->db->where('is_recurring', 1)
            ->set(['is_completed' => 0])
            ->update('tasks');
    }

    public function getTasksByEmployee($emp_id)
    {
        return $this->db->where('emp_id', $emp_id)->get('tasks')->result_array();
    }

    public function getRemainingPoints($empId, $totalAllowedPoints = 3000)
    {
        $this->db->select_sum('points');
        $this->db->where('emp_id', $empId);
        $this->db->where('is_admin_task', 0);
        $query = $this->db->get('tasks');
        $row = $query->row();
        
        $totalAssigned = $row ? $row->points : 0;

        return max(0, $totalAllowedPoints - $totalAssigned);
    }

    public function getRemainingAdminPointsForMonth($empId, $month, $year, $limit = 500) 
    {
        $this->db->select_sum('points');
        $this->db->where('emp_id', $empId);
        $this->db->where('is_admin_task', 1);
        $this->db->where('MONTH(created_at)', $month);
        $this->db->where('YEAR(created_at)', $year);
        $query = $this->db->get('tasks');
        $row = $query->row();
        
        $totalAssigned = $row ? $row->points : 0;

        return max(0, $limit - $totalAssigned);
    }
}

