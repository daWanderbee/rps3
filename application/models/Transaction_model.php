<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Transaction_model extends CI_Model
{
        
    
    public function getTransactionsByEmployee($empId)
    {
        return $this->db->where('emp_id', $empId)
            ->order_by('transaction_date', 'DESC')
            ->order_by('transaction_time', 'DESC')
            ->get('transactions')->result_array();
    }

    public function getNotificationsByEmployee($empId)
    {
        return $this->db->where('emp_id', $empId)
            ->where('isDiscarded', 0)
            ->order_by('transaction_date', 'DESC')
            ->order_by('transaction_time', 'DESC')
            ->get('transactions')->result_array();
    }

    public function markAsRead($id)
    {
        return $this->db->where('id', $id)->update('transactions', ['isUnread' => 0]);
    }

    public function markAllAsRead($empId)
    {
        return $this->db->where('emp_id', $empId)
            ->set(['isUnread' => 0])
            ->update('transactions');
    }

    public function discardAll($empId)
    {
        return $this->db->where('emp_id', $empId)
            ->set(['isDiscarded' => 1])
            ->update('transactions');
    }
}

