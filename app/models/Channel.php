<?php

class Channel extends Eloquent {

    const ID_CONFIRMATION   = -1;
    const ID_SYS_RECOMMEND  = -2;
    const ID_AD_1           = -11;

    public static function findOrCreateBy($creator_id, $uids) {
        sort($uids);
        $hash = md5(implode(',', $uids));

        $channel = Channel::where('hash', $hash)->first();
        if (!$channel) {
            $channel = new Channel();
            $channel->hash = $hash;
            $channel->creator_id = $creator_id;
            $channel->save();

            $redis = LRedis::connection();
            $key = Consts::CK_S_CHANNEL_MEMBERS.':'.$channel->id;
            foreach ($uids as $value) {
                $redis->sadd($key, $value);
            }
        }

        return $channel;
    }

    public static function getMembers($creator_id, $members) {
        $uids = explode(',', $members);
        $uids[] = $creator_id; // add creatorid
        $uids = array_unique($uids);

        $result = array();
        foreach ($uids as $value) {
            if ($value) {
                $result[] = $value;
            }
        }

        sort($result);
        return $result;
    }


}