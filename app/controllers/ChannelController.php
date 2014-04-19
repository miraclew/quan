<?php
class ChannelController extends BaseController {

    // create channel
    public function store()
    {
        $members = Input::get('members');
        $creator_id = Auth::user()->id;

        $uids = Channel::getMembers($creator_id, $members);
        if (count($uids) < 2) {
            return Response::json(array('error'=>array('code'=>-1)));
        }

        $channel = Channel::findOrCreateBy($creator_id, $uids);

        return Response::json(array('object'=>$channel->toArray()));
    }
}