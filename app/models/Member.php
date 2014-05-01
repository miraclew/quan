<?php

class Member extends Eloquent {
    const TYPE_MEMBER = 1;
    const TYPE_MODERATOR = 2;
    const TYPE_ADMIN = 3;

    public static function is_member($circle_id, $user_id) {
        return self::whereRaw('circle_id=? and user_id=?')->count() > 0;
    }
}