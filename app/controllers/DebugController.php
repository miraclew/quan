<?php

class DebugController extends BaseController {

    public function getTest()
    {
        var_dump(Token::first());
    }

}