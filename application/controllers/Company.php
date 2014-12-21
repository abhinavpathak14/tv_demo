<?php
/**
 * Description of Company
 *
 * @author hp
 */ 
class Company extends CI_Controller {
    var $search='';
    public function index() {
        $this->search();
    }
    public function search($src='') {
        $this->data['page_title'] = 'Search Company';
        $this->data['search_msg'] = 'Enter Company name above';
        $search_txt = $this->input->post('search_txt');
        if(!empty($src)) {
            $search_txt = $src;
        }
        if(!empty($search_txt)) {
            $this->load->model('CompanyModel');
            $companies = $this->CompanyModel->searchCompany($search_txt);
            if(empty($companies) || $companies->num_rows() == 0) {
                $this->session->set_userdata('search_text', $search_txt);
                //get result set from linkedin API
                $this->load->model('Linkedin');
                $this->Linkedin->getAuthCode();
            } else {
                $this->data['search_msg']='';
                $this->data['companies'] = $companies;
            }
        }
        $body = $this->load->view('company/search', $this->data, true);
        $this->data['body'] = $body;
        $this->load->view('default', $this->data);
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
                redirect('company/search/'.$this->session->userdata('search_text'));
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
