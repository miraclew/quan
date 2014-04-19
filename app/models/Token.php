<?php

class Token extends Eloquent {
    public static function authToken($userId, $ttl=2592000) { // 30 days
        $token = Token::where('user_id', '=', $userId)->first();
        if (!$token) {
            $token = new Token();
            $token->user_id = $userId;
        }
        $token->refresh($ttl);
        $token->save();

        return $token;
    }

    public static function newTokenForUser($userId, $ttl=2592000) { // 30 days
        $token = Str::random(20);
        $redis = LRedis::connection();
        $key = "token:$token";
        $redis->set($key, $userId);
        $redis->expire($key, $ttl);

        return $token;
    }

    public static function getUserOfToken($token) {
        $redis = LRedis::connection();
        $key = "token:$token";
        return $redis->get($key);
    }

    public function refresh($ttl) {
        $this->token = Str::random(20);
        $this->expires_at = date('Y-m-d H:i:s', time() + $ttl);
    }

    public function removeToken($t) {
        $token = Token::where('token', '=', $t)->first();
        if ($token) {
            $token->delete();
        }
    }

    public function isExpired() {
        return strtotime($this->expires_at) < time();
    }

}