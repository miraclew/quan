<?php

class DebugController extends BaseController {

    public function getTest()
    {
        //var_dump(Token::first());
        //echo phpinfo();
        $lbs = new LBS();
        $a = $lbs->setCircleLocation(1,22,33);
        var_dump($a);
    }

}