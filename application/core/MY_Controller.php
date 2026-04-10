<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');

        // Logic from CI4 BaseController for cross-site session bridging
        $this->_bridge_session();
    }

    private function _bridge_session() {
        //  Allow login route FIRST (avoid infinite loop)
        $currentPath = $_SERVER['REQUEST_URI'];
        if (strpos($currentPath, '/login') !== false) {
            return;
        }

        //  Get CI3 session cookie from the main site
        $sessionId = $_COOKIE['ci_session'] ?? null;

        if (!$sessionId) {
            // Redirect to main site login if session cookie is missing
            // header("Location: https://team.pakka.com/login");
            // exit;
            return; // For now, let local session logic handle it or the controller decide
        }

        //  CI3 session file path (standard loc on Linux usually)
        $sessionFile = '/tmp/ci_session' . $sessionId;

        if (file_exists($sessionFile)) {
            $data = file_get_contents($sessionFile);

            //  Extract emp_code
            preg_match('/emp_code\|s:\d+:"([^"]+)"/', $data, $codeMatch);
            if (!empty($codeMatch[1])) {
                $this->session->set_userdata('emp_code', $codeMatch[1]);
            }

            // Extract emp_name
            preg_match('/emp_name\|s:\d+:"([^"]+)"/', $data, $nameMatch);
            if (!empty($nameMatch[1])) {
                $this->session->set_userdata('emp_name', $nameMatch[1]);
            }

            // Extract emp_id
            preg_match('/emp_id\|i:(\d+)/', $data, $idMatch);
            if (!empty($idMatch[1])) {
                $this->session->set_userdata('emp_id', $idMatch[1]);
            }
        }
    }
}
