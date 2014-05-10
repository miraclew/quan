<?php

class Channel extends Eloquent {

    const ID_CONFIRMATION   = -1;
    const ID_SYS_RECOMMEND  = -2;
    const ID_AD_1           = -11;

    public static function p2pChanel($p1, $p2) {
        return self::findOrCreateBy($p1, [$p1, $p2]);
    }

    // uids should include creator_id
    public static function findOrCreateBy($creator_id, $uids) {
        sort($uids);
        $hash = md5(implode(',', $uids));

        $channel = Channel::where('hash', $hash)->first();
        if (!$channel) {
            $channel = new Channel();
            $channel->hash = $hash;
            $channel->creator_id = $creator_id;
            $channel->members_count = count($uids);
            if ($channel->members_count > 2) {
                $channel->title = "群聊";
            }

            $channel->save();

            $redis = LRedis::connection();
            $key = Consts::CK_S_CHANNEL_MEMBERS.':'.$channel->id;
            foreach ($uids as $value) {
                $redis->sadd($key, $value);
            }

            if ($channel->members_count > 2) {
                // send channel created event to all members
                $m1 = new Message();
                $m1->sender_id = -1;
                $m1->channel_id = $channel->id;
                $m1->type = Message::TYPE_CHANNEL_EVENT;
                $m1->sub_type = Message::ST_CE_CREATED;
                $m1->mime_type = 'application/json';
                $m1->content = json_encode(array(
                    'id'=>$channel->id,
                    'title'=>$channel->title,
                    'avatar'=>'',
                    'members_count'=>$channel->members_count));
                $m1->save();
                $m1->send();
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

    public function info($user_id) {
        $title = $this->title;
        $avatar = $this->avatar;

        $redis = LRedis::connection();
        $key = Consts::CK_S_CHANNEL_MEMBERS.':'.$this->id;
        $uids = $redis->smembers($key);
        if (!in_array($user_id, $uids)) {
            return null;
        }

        $peers = array_diff($uids, array($user_id));

        if (!$title) {
            $us = DB::table('users')->whereIn('id', array_slice($peers, 0, 5))->lists('nickname');
            $title = join($us, ',');
        }

        return array('id'=>$this->id, 'title'=>$title, 'avatar'=>$avatar, 'members'=>$uids);
    }

}