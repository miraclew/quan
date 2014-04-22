<?php
class FriendController extends BaseController {

    public function index()
    {
        $limit = intval(Input::get('limit', 20));
        $skip = intval(Input::get('skip', 0));

        $friends = DB::table('friends')
            ->where('status', Friend::STATUS_CONFIRM)
            ->where(function($query)
                {
                    $user_id = Auth::user()->id;
                    $query->where('user_id', $user_id)
                          ->orWhere('friend_id', $user_id);
                })
            ->skip($skip)->take($limit)->get();

        $objects = array();
        foreach ($friends as $value) {
            $friend_id = Auth::user()->id == $value->user_id ? $value->friend_id : $value->user_id;
            $user = User::find($friend_id);
            $objects[] = array(
                'id' => $value->id,
                'friend_id' => $friend_id,
                'avatar' => $user->avatar,
                'nickname' => $user->nickname
                );
        }

        return Response::json(array('objects' => $objects));
    }

    public function store()
    {
        $friend_id = Input::get('friend_id');
        if ($friend_id == Auth::user()->id) {
            return Response::json(array('error'=>array('denied')));
        }

        $friend = new Friend();
        $friend->user_id = Auth::user()->id;
        $friend->friend_id = $friend_id;
        $friend->status = Friend::STATUS_CREATE;
        $friend->save();

        return Response::json(array('object'=> $friend->toArray()));
    }

    public function update($id) {
        $status = Input::get('status');
        $friend = Friend::find($id);
        if (!$friend) {
            return Response::json(array('error'=>array('message'=>'data not found')));
        }

        if (Auth::user()->id != $friend->friend_id) {
            return JR::fail(Code::NOT_ALLOW);
        }

        $friend->status = $status;
        $friend->save();

        return Response::json(array('object'=>$friend->toArray()));
    }

    public function destroy($id) {
        $friend = Friend::find($id);
        if (!$friend) {
            return Response::json(array('error'=>array('message'=>'data not found')));
        }

        if (Auth::user()->id != $friend->friend_id && Auth::user()->id != $friend->user_id) {
            return Response::json(array('error'=>array('message'=>'denied')));
        }

        $friend->delete();

        return Response::json(array('message'=>"OK"));
    }
}