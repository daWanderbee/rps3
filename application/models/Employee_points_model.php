<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Employee_points_model extends CI_Model
{
        
    
    public function updatePoints($empId, $pointsToAdd, $senderRole)
    {
        $record = $this->db->get_where('tbl_employee_points', ['emp_id' => $empId])->row_array();

        if ($record) {
            $newPoints = min(50000, $record['points_received'] + $pointsToAdd);
            return $this->db->where('emp_id', $empId)->update('tbl_employee_points', ['points_received' => $newPoints]);
        }

        return $this->db->insert('tbl_employee_points', [
            'emp_id'         => $empId,
            'points_received' => min(50000, $pointsToAdd),
        ]);
    }

    public function getEmployeeById($empId)
    {
        return $this->db->select('e.emp_id, e.name, e.emp_cat, COALESCE(p.pointsfromSevak, 0) as pointsfromSevak, COALESCE(p.pointsfromSangrakshak, 0) as pointsfromSangrakshak')
            ->from('tbl_employee e')
            ->join('tbl_employee_points p', 'p.emp_id = e.emp_id', 'left')
            ->where('e.emp_id', $empId)
            ->get()
            ->row_array();
    }

    public function getEmployeesWithPoints()
    {
        return $this->db->select('e.emp_id as id, e.name, e.emp_cat, COALESCE(p.points_received, 0) AS points_received')
            ->from('tbl_employee e')
            ->join('tbl_employee_points p', 'p.emp_id = e.emp_id', 'left')
            ->where('e.name !=', "")
            ->where_in('e.emp_cat', ['1', '2', '3'])
            ->order_by('e.name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getEmployeePoints($empId)
    {
        return $this->db->select('e.emp_id, e.name, COALESCE(p.points_received, 0) AS points_received')
            ->from('tbl_employee e')
            ->join('tbl_employee_points p', 'p.emp_id = e.emp_id', 'left')
            ->where('e.emp_id', $empId)
            ->get()
            ->row_array();
    }

    public function getMonthlyLeaderboard()
    {
        return $this->db->select('e.emp_id, e.name, SUM(l.points) as points')
            ->from('transactions l')
            ->join('tbl_employee e', 'e.emp_id = l.emp_id')
            ->where('MONTH(l.transaction_date)', date('m'))
            ->where('YEAR(l.transaction_date)', date('Y'))
            ->where('l.points >', 0)
            ->group_by('e.emp_id')
            ->order_by('points', 'DESC')
            ->limit(10)
            ->get()
            ->result_array();
    }

    public function getQuarterlyLeaderboard()
    {
        $currentQuarter = ceil(date('n') / 3);

        return $this->db->select('e.emp_id, e.name, SUM(l.points) as points')
            ->from('transactions l')
            ->join('tbl_employee e', 'e.emp_id = l.emp_id')
            ->where('QUARTER(l.transaction_date)', $currentQuarter)
            ->where('YEAR(l.transaction_date)', date('Y'))
            ->where('l.points >', 0)
            ->group_by('e.emp_id')
            ->order_by('points', 'DESC')
            ->limit(10)
            ->get()
            ->result_array();
    }

    public function deductPoints($empId, $points)
    {
        $employeePoints = $this->db->get_where('tbl_employee_points', ['emp_id' => $empId])->row_array();

        if ($employeePoints) {
            $newPoints = $employeePoints['points_received'] - $points;
            if ($newPoints < 0) return false;

            return $this->db->where('emp_id', $empId)->update('tbl_employee_points', ['points_received' => $newPoints]);
        }
        return false;
    }

    public function getActiveStreaks()
    {
        $streaks = [];
        $currentDate = date('Y-m-d');
        $employees = $this->db->select('emp_id')->distinct()->from('transactions')->get()->result_array();

        foreach ($employees as $emp) {
            $count = 0;
            for ($i = 0; $i < 12; $i++) {
                $checkDate = strtotime("-$i month", strtotime($currentDate));
                $month = date('n', $checkDate);
                $year = date('Y', $checkDate);

                $topUser = $this->db->select('emp_id')
                    ->from('transactions')
                    ->where('MONTH(transaction_date)', $month)
                    ->where('YEAR(transaction_date)', $year)
                    ->where('points >', 0)
                    ->group_by('emp_id')
                    ->order_by('SUM(points)', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row_array();

                if ($topUser && $topUser['emp_id'] == $emp['emp_id']) {
                    $count++;
                } else {
                    break;
                }
            }

            if ($count >= 3) {
                $streaks[$emp['emp_id']] = $count;
            }
        }
        return $streaks;
    }

    public function addRewardedPoints($empId, $points)
    {
        $record = $this->db->get_where('tbl_employee_points', ['emp_id' => $empId])->row_array();

        if ($record) {
            $newPoints = min(50000, $record['points_rewarded'] + $points);
            return $this->db->where('emp_id', $empId)->update('tbl_employee_points', ['points_rewarded' => $newPoints]);
        }

        return $this->db->insert('tbl_employee_points', [
            'emp_id' => $empId,
            'points_received' => 0,
            'points_rewarded' => min(50000, $points)
        ]);
    }

    public function getRewardedPoints($empId)
    {
        $record = $this->db->get_where('tbl_employee_points', ['emp_id' => $empId])->row_array();
        return $record ? (int)$record['points_rewarded'] : 0;
    }

    public function updateSpecificPoints($recipientId, $points, $column)
    {
        $allowedColumns = ['pointsfromSevak', 'pointsfromSangrakshak'];
        if (!in_array($column, $allowedColumns)) {
            return false;
        }

        $record = $this->db->get_where('tbl_employee_points', ['emp_id' => $recipientId])->row_array();

        if ($record) {
            $this->db->where('emp_id', $recipientId);
            $this->db->set($column, "$column + $points", false);
            $this->db->set('points_received', "LEAST(points_received + $points, 50000)", false);
            return $this->db->update('tbl_employee_points');
        }

        return $this->db->insert('tbl_employee_points', [
            'emp_id' => $recipientId,
            'points_received' => min(50000, $points),
            $column => $points,
            'points_rewarded' => 0
        ]);
    }
}

