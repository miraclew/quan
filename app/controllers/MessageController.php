<?php
class MessageController extends BaseController {

    public function store()
    {
        $channel_id = Input::get('channel_id');
        $type = Input::get('type');
        $sub_type = Input::get('sub_type');
        $mime_type = Input::get('mime_type');
        $content = Input::get('content');
        $status = 0;

        if (!Channel::find($channel_id)) {
            return JR::fail(Code::DATA_NOT_FOUND);
        }

        $message = new Message();
        $message->sender_id = Auth::user()->id;
        $message->channel_id = $channel_id;
        $message->type = $type;
        $message->sub_type = $sub_type;
        $message->mime_type = $mime_type;
        $message->content = $content;
        $message->status = $status;
        $message->ack = 0;
        $message->save();

        // send to rtm
        $result = $message->send();
        if ($result['code'] != 0) {
            return JR::fail(Code::FAIL, $result['message']);
        }

        return JR::ok(array('object'=>$message->toArray()));
    }

    public function update($id) {
        $ack = Input::get('ack');
        $message = Message::find($id);
        if (!$message) {
            return JR::fail(Code::DATA_NOT_FOUND);
        }

        if ($ack <= 0) {
            return JR::fail(Code::PARAMS_INVALID);
        }

        if ($message->channel_id >= 0) {
            return JR::fail(Code::FAIL, 'not request message');
        }

        // if ($message->ack == $ack) {
        //     return JR::fail(Code::FAIL, 'ack not changed');
        // }

        // if ($message->type != Message::TYPE_USER_MSG) {
        //     return JR::fail(Code::FAIL, 'message type incorrect');
        // }

        $message->ack = $ack;
        $message->save();

        if ($message->sub_type == Message::ST_UM_FIREND_ADD) {
            $friend = Friend::find($message->ass_object_id);
            $friend->confirm();
            return JR::ok();
        } else {
            return JR::fail(Code::FAIL, 'message type can not handle');
        }
    }

}