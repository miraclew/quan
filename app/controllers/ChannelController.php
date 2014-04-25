<?php
class ChannelController extends BaseController {

    // create channel
    public function store()
    {
        $members = Input::get('members');
        $creator_id = Auth::user()->id;

        $uids = Channel::getMembers($creator_id, $members);
        if (count($uids) < 2) {
            return JR::fail(Code::PARAMS_INVALID);
        }

        $channel = Channel::findOrCreateBy($creator_id, $uids);

        return JR::ok(array('object'=>$channel->info($creator_id)));
    }

    // update title, avatar etc.
    public function update($id) {
        $user_id = Auth::user()->id;
        $title = Input::get('title');
        $avatar = Input::get('avatar');

        $channel = Channel::find($id);
        if (!$channel) {
            return JR::fail(Code::FAIL);
        }
        if ($user_id != $channel->creator_id) {
            return JR::fail(Code::NOT_ALLOW);
        }

        if (!$title) {
            $channel->title = $title;
        }

        if (!$avatar) {
            $channel->avatar = $avatar;
        }

        $channel->save();
        return JR::ok();
    }

    public function show($id) {
        $user_id = Auth::user()->id;
        $channel = Channel::find($id);
        $info = $channel->info($user_id);
        return JR::ok(array('object'=>$info));
    }
}