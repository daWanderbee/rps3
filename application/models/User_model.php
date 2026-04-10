<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class User_model extends CI_Model
{
    public function get_user($id)
    {
        return $this->db->get_where('tbl_employee', ['emp_id' => $id])->row_array();
    }

    public function get_all_users()
    {
        return $this->db->get('tbl_employee')->result_array();
    }

    public function getEmployeesByDepartment($deptId)
    {
        return $this->db->where('department_id', $deptId)
                    ->get('tbl_employee')->result_array();
    }
}