<?php

class Friend extends Eloquent {

    const STATUS_CREATE = 1;
    const STATUS_CONFIRM = 2;

    public function confirm() {
        $channel = Channel::findOrCreateBy($this->user_id, array($this->user_id, $this->friend_id));

        $m1 = new Message();
        $m1->sender_id = $this->user_id;
        $m1->channel_id = $channel->id;
        $m1->type = Message::TYPE_USER_MSG;
        $m1->sub_type = Message::ST_UM_FIREND_CONFIRM;
        $m1->mime_type = 'application/json';
        $m1->content = json_encode(['friend_id'=> $this->id, 'text'=>'你通过了我的好友验证，我们可以开始对话了']);
        $m1->status = 0;
        $m1->ack = 0;
        $m1->save();
        $m1->send(true);

        $m2 = new Message();
        $m2->sender_id = $this->friend_id;
        $m2->channel_id = $channel->id;
        $m2->type = Message::TYPE_USER_MSG;
        $m2->sub_type = Message::ST_UM_FIREND_CONFIRM;
        $m2->mime_type = 'application/json';
        $m2->content = json_encode(['friend_id'=> $this->id, 'text'=>'我通过了你的好友验证，我们可以开始对话了']);
        $m2->status = 0;
        $m2->ack = 0;
        $m2->save();
        $m2->send(true);

        $this->status = self::STATUS_CONFIRM;
        $this->save();
    }

    public static function add($user_id, $friend_id) {
        $friend = Friend::whereRaw('(user_id=? and friend_id=?) or (user_id=? and friend_id=?)',
            [$user_id, $friend_id, $friend_id, $user_id])->first();
        if (!$friend) {
            $friend = new Friend();
            $friend->user_id = $user_id;
            $friend->friend_id = $friend_id;
            $friend->status = Friend::STATUS_CREATE;
            $friend->save();
        }

        $user = User::find($user_id);

        $m1 = new Message();
        $m1->sender_id = $user_id;
        $m1->channel_id = Channel::ID_CONFIRMATION;
        $m1->recipients = strval($friend_id);
        $m1->type = Message::TYPE_USER_MSG;
        $m1->sub_type = Message::ST_UM_FIREND_ADD;
        $m1->mime_type = 'text/plain';
        $m1->content = $user->nickname .': 请求加为好友';
        $m1->status = 0;
        $m1->ass_object_id = $friend->id;
        $m1->ack = 0;
        $m1->save();
        $m1->send();

        return $friend;
    }
}