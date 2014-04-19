<?php
class ChannelMemberController extends BaseController {
    // add members
    public function store()
    {
        $channel_id = Input::get('channel_id');
        $add_uids = Channel::getMembers(Input::get('members'));

        // check permission
        $user_id = Auth::user()->id;
        $redis = LRedis::connection();
        $key = Consts::CK_S_CHANNEL_MEMBERS.':'.$channel_id;
        $uids = $redis->smembers($key);
        if (!in_array($user_id, $uids)) {
            return Response::json(array('error'=>array('code'=>-1,'message'=>'denied')));
        }

        $udis_toadd = array_diff($add_uids, $uids);
        foreach ($udis_toadd as $value) {
            $redis->sadd($key, $value);
        }

        // rehash channel ?

        return Response::json(array('objects'=>$udis_toadd));
    }

    public function destroy() {
        $channel_id = Input::get('channel_id');
        $remove_uids = Channel::getMembers(Input::get('members'));
    }
}