<?php

/**
 * Description of Job
 *
 * @author hp
 */
class Job extends CI_Controller {
    public function index() {
        $this->data['page_title'] = 'Search Jobs';
        $skill = $this->input->post('skill');
        $location = $this->input->post('location');
        $pagination = $this->input->post('pagination');
        if(!empty($skill) && !empty($location) && !empty($pagination)) {
            $params = array(
                'publisher' =>  PUBLISHER,
                'v'         =>  2,
                'format'    =>  FORMAT,
                'q'         =>  $skill,
                'l'         =>  $location,
                'sort'      =>  '',
                'radius'    =>  '',
                'st'        =>  '',
                'jt'        =>  '',
                'limit'     =>  $pagination*10,
                'start'     =>  ($pagination*10)-9  ,
                'fromage'   =>  1,
                'highlight' =>  1,
                'filter'    =>  1,
                'latlong'   =>  0,
                'co'        =>  'in',
                'chnl'      =>  '',
                'userip'    =>  $_SERVER['REMOTE_ADDR'],
                'useragent' =>  $_SERVER['HTTP_USER_AGENT']
            );
            $url = INDEED_API_URL.'?'.  http_build_query($params);
            $result = file_get_contents($url);
            $xml = simplexml_load_string($result);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            if(isset($array['results']['result']) && count($array['results']['result'])) {
               $job_result =  $array['results']['result'];
               $final_result = array();
               $this->load->model('CompanyModel');
               $this->load->model('Linkedin');
               $not_found_comapnies = array();
               foreach($job_result as &$job) {
                   $company_details = $this->CompanyModel->getCompanyDetailsByName($job['company']);
                   if(!$company_details->num_rows()) {
                       $not_found_comapnies[] = $job['company'];
                   } else {
                       $job['company_details'] = $company_details;
                   }
               }
               
               $this->session->set_userdata('not_found_comapnies', $not_found_comapnies);
               $this->getCompanyFromLinkedInAPI($not_found_comapnies);
            }
        }
        $body = $this->load->view('job/search', $this->data, true);
        $this->data['body'] = $body;
        $this->load->view('default', $this->data);
    }
    
    public function getCompanyFromLinkedInAPI($not_found_comapnies=array(), $counter=0) {
        if(empty($not_found_comapnies)) {
            $not_found_comapnies = $this->session->userdata('not_found_comapnies');
        }
        if(!empty($not_found_comapnies)) {
            $limit = count($not_found_comapnies);
            for($i=$counter; $i < $limit; $i++) {
                $this->session->set_userdata('counter', $i);
                $this->searchCompany($not_found_comapnies[$i]);
            }
        }
    }

    public function searchCompany($src) {
        $search_txt = $src;
        if(!empty($search_txt)) {
            $this->load->model('CompanyModel');
            $this->session->set_userdata('search_text', $search_txt);
            //get result set from linkedin API
            $this->load->model('Linkedin');
            $this->Linkedin->getAuthCode();
        }
    }
    
    public function authCode() {
        $code = $this->input->get('code');
        $state = $this->input->get('state');
        //check state value
        $session_state = $this->session->userdata($state);
        if(!empty($code)) {
            if($state == $session_state) {
                $this->load->model('Linkedin');
                $this->Linkedin->getAccessToken($code);
                $result_set = $this->fetch();
                $this->load->model('CompanyModel');
                $this->CompanyModel->save($result_set);
                $this->getCompanyFromLinkedInAPI(array(), $this->session->userdata('counter'));
            } else {
                show_404();
            }
        } else {
            
        }
    }
    public function fetch() {
        $opts = array(
            'http'=>array(
                'method' => 'GET',
                'header' => "Authorization: Bearer " . $this->session->userdata('access_token') . "\r\n" . "x-li-format: json\r\n"
            )
        );
        
        $search_text = $this->session->userdata('search_text');
        // Need to use HTTPS
        $url = LINKEDIN_COMPANY_API_URL.$search_text;

        // Tell streams to make a (GET, POST, PUT, or DELETE) request
        // And use OAuth 2 access token as Authorization
        $context = stream_context_create($opts);

        // Hocus Pocus
        $response = file_get_contents($url, false, $context);
        // Native PHP object, please
        return json_decode($response);
    }
}

?>
