<?php

class Friend extends Eloquent {

    const STATUS_CREATE = 1;
    const STATUS_CONFIRM = 2;

    public function confirm() {
        $this->status = self::STATUS_CONFIRM;
        $this->save();

        $channel = Channel::findOrCreateBy($this->user_id, array($this->user_id, $this->friend_id));

        $m1 = new Message();
        $m1->sender_id = $this->user_id;
        $m1->channel_id = $channel->id;
        $m1->type = 1;
        $m1->sub_type = 1;
        $m1->mime_type = 'text/plain';
        $m1->content = '我通过了你的好友验证，我们可以开始对话了';
        $m1->status = 0;
        $m1->ack = 0;
        $m1->save();
        $m1->send();

        $m2 = new Message();
        $m2->sender_id = $this->friend_id;
        $m2->channel_id = $channel->id;
        $m2->type = 1;
        $m2->sub_type = 1;
        $m2->mime_type = 'text/plain';
        $m2->content = '你通过了我的好友验证，我们可以开始对话了';
        $m2->status = 0;
        $m2->ack = 0;
        $m2->save();
        $m2->send();
    }

    public function add($user_id, $friend_id) {
        $this->user_id = $user_id;
        $this->friend_id = $friend_id;
        $this->status = Friend::STATUS_CREATE;

        $this->save();

        $m1 = new Message();
        $m1->sender_id = $this->user_id;
        $m1->channel_id = Channel::ID_CONFIRMATION;
        $m1->recipients = strval($this->friend_id);
        $m1->type = Message::TYPE_REQUEST;
        $m1->sub_type = Message::ST_R_ADD_FIREND;
        $m1->mime_type = 'text/plain';
        $m1->content = '请求加为好友';
        $m1->status = 0;
        $m1->object_id = $this->id;
        $m1->ack = 0;
        $m1->save();
        $m1->send();
    }
}