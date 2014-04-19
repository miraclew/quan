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
        $message->from_id = Auth::user()->id;
        $message->channel_id = $channel_id;
        $message->type = $type;
        $message->sub_type = $sub_type;
        $message->mime_type = $mime_type;
        $message->content = $content;
        $message->status = $status;
        $message->ack_status = 0;
        $message->save();

        // send to rtm
        $result = $message->send();

        return JR::ok($result);
        //return JR::ok(array('object'=>$message->toArray()));
    }


}