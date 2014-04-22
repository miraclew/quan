<?php

class JR {
    public static function ok($data=array()) {
        $data['code'] = Code::OK;
        return Response::json($data);
    }

    public static function fail($code, $message='') {
        if ($message == '') {
            $message = Code::message($code);
        }
        return Response::json(array('code'=>$code,'message'=>$message));
    }
}