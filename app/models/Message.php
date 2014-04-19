<?php

class Message extends Eloquent {
    // 消息场景 chat message, user action, system action
    const TYPE_CHAT             = 1;
    const TYPE_USER_ACTION      = 2;
    const TYPE_SYSTEM_ACTION    = 3;

    const MIME_TYPE_JSON             = 'application/json';
    const MIME_TYPE_TEXT             = 'text/plain';
    const MIME_TYPE_IMAGE            = 'image/*';
    const MIME_TYPE_AUDIO            = 'audio/*';
    const MIME_TYPE_VIDEO            = 'video/*';

    // 应答状态 0: 未应答 1: 应答1, 2: 应答2 ...

    public function send() {
        $apiUrl = Config::get('app.rtm_api_url');
        return $this->http_post($apiUrl, $this->toArray());
    }

    function http_post($url, $data, $debug = false) {
        $cookie_file_path = storage_path().'/sessions/rtm_api_cookiee.txt';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1 );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
        curl_setopt($curl, CURLOPT_COOKIEJAR,  $cookie_file_path);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file_path);
        curl_setopt($curl, CURLOPT_URL, $url);
        //Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data, while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded
        // express body_parser noly support urlencoded
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($curl);
        $result = json_decode($response, true);
        $error = curl_error($curl);
        if($debug) {
            $log = "http_post: url=$url \n request data=".print_r($data, true)." \n response=$response \n result=".print_r($result, true)." \n";
            echo  $log;
        }

        if($error) {
            $result = array('code' => -1, 'message' => $error);
        }

        return $result;
    }
}