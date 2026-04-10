<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Redeem_model extends CI_Model
{
            
    public function getRedeemable($userPoints)
    {
        return $this->db->where('points <=', $userPoints)
            ->order_by('points', 'DESC')
            ->get('reward_inventory')
            ->result_array();
    }
    public function getCheapestRewards($limit = 4)
    {
        return $this->db->order_by('points', 'ASC')
            ->limit($limit)
            ->get('reward_inventory')
            ->result_array();
    }
}

