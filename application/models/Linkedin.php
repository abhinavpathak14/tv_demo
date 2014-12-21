<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Linkedin
 *
 * @author hp
 */
class Linkedin extends CI_Model {
    public function getAuthCode() {
        $random = random_string();
        $this->session->set_userdata($random, $random);
        redirect(LINKEDIN_AUTH_CODE_URL.'?response_type=code&client_id='.LINKEDIN_API_KEY.'&state='.$random.'&redirect_uri='.  site_url('company/authCode'));
    }
    function getAccessToken() {
        $params = array(
            'grant_type' => 'authorization_code',
            'client_id' => LINKEDIN_API_KEY,
            'client_secret' => LINKEDIN_SECRET_KEY,
            'code' => $_GET['code'],
            'redirect_uri' => site_url('company/authCode'),
        );


        // Access Token request
        $url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);


        // Tell streams to make a POST request
        $context = stream_context_create(
                array('http' =>
                    array('method' => 'POST',
                    )
                )
        );


        // Retrieve access token information
        $response = file_get_contents($url, false, $context);


        // Native PHP object, please
        $token = json_decode($response);
        
        // Store access token and expiration time
        $this->session->set_userdata('access_token', $token->access_token);
        $this->session->set_userdata('expires_in', $token->expires_in);
        $this->session->set_userdata('expires_at', $token->expires_in+time());
        return true;
    }
}

?>
