<?php

class WoocommerceAPIInterface {
    private static $api_key = 'ck_37032f6d70c9af7a8599daeedb80561300623f27';
    private static $api_secret = 'cs_57b4dd7d1b8211a180e5933ac42741d190244671';
    private static $base_url = '/wp-json/wc/v3';
    
    public function get($endpoint) {
        $curl = curl_init();

        $options = array(
            CURLOPT_URL => get_site_url().$this::$base_url.$endpoint,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($this::$api_key . ":" . $this::$api_secret)
            ]
        );

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($curl);

        // Convert the $headers string to an indexed array
        $headers_indexed_arr = explode("\r\n", $headers);
        $headers_arr = array();
        // $status_message = array_shift($headers_indexed_arr);

        // Create an associative array containing the response headers
        foreach ($headers_indexed_arr as $value) {
            if(false !== ($matches = explode(':', $value, 2))) {
                $headers_arr["{$matches[0]}"] = trim($matches[1]);
            }                
        }

        return array(
            'headers'   => $headers_arr,
            'body'      => json_decode($body)
        );
    }
}