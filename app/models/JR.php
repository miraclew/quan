<?php

class JR {
    public static function ok($data) {
        $data['code'] = Code::OK;
        return Response::json($data);
    }

    public static function fail($code, $message='') {
        return Response::json(array('code'=>$code,'message'=>$message));
    }
}