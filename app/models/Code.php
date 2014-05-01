<?php

class Code {
    const OK = 0;

    const FAIL              = -1;
    const PARAMS_INVALID    = -2;
    const DATA_NOT_FOUND    = -3;
    const NOT_ALLOW         = -4;

    const RES_TAKEN         = -5;
    const DATA_DUPLICATE    = -6;

    const AUTH_NEED         = -10;
    const AUTH_FAIL         = -11;

    private static $messages = array(
        0 => 'OK',
        -1 => 'FAIL',
        -2 => 'PARAMS_INVALID',
        -3 => 'DATA_NOT_FOUND',
        -4 => 'NOT_ALLOW',
        -5 => 'RES_TAKEN',
        -6 => 'DATA_DUPLICATE',
        -10 => 'AUTH_NEED',
        -11 => 'AUTH_FAIL'
        );

    public static function message($code) {
        return self::$messages[$code];
    }
}