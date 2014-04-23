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

        return JR::ok(array('object'=>$channel->toArray()));
    }
}