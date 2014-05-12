<?php
class UserController extends BaseController {

    public function postLogin() {
        $username = Input::get('username');
        $password = Input::get('password');

        if (Auth::attempt(array('username' => $username, 'password' => $password)))
        {
            return $this->loginResponse();
        } else {
            return JR::fail(Code::AUTH_FAIL);
        }
    }

    public function postLogout() {
        Auth::logout();
        return JR::ok();
    }

    public function postRegister() {
        $username = Input::get('username');
        $nickname = Input::get('nickname');
        $password = Input::get('password');
        $password_digest = Hash::make($password);

        $validator = Validator::make(
            Input::all(),
            array(
                'username' => 'required|min:6',
                'nickname' => 'required|min:3',
                'password' => 'required|min:6',
            )
        );
        if ($validator->fails())
        {
            return JR::fail(Code::PARAMS_INVALID);
        }

        $count = User::where('username', '=', $username)->count();
        if ($count > 0) {
            return JR::fail(Code::RES_TAKEN, '该用户名已被使用');
        }

        $user = new User();
        $user->username = $username;
        $user->nickname = $nickname;
        $user->password = $password_digest;
        if (stripos($username, '@') !== false) {
            $user->email = $username;
        }
        $user->gender = 1;
        $user->is_locked = false;
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        // login
        Auth::logout();
        Auth::loginUsingId($user->id);

        return $this->loginResponse();
    }

    public function postFeedback() {
        $user = Auth::user();
        $text = Input::get('text');
        if (!$text) {
            return JR::fail(Code::PARAMS_INVALID);
        }

        $feedback = new Feedback();
        $feedback->user_id = $user->id;
        $feedback->text = $text;
        $feedback->save();

        return JR::ok();
    }

    public function postRegisterdevice() {
        $user = Auth::user();
        $deviceToken = Input::get('device_token');

        $redis = LRedis::connection();
        $key = Consts::CK_S_USER_DEVICE_TOKEN.':'.$user->id;
        $redis->set($key, $deviceToken);
        $key = Consts::CK_S_DEVICE_TOKEN_USER.':'.$deviceToken;
        $redis->set($key, $user->id);
        return JR::ok();
    }

    private function loginResponse() {
        $user = Auth::user();
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        $ttl = 86400;
        $token = Token::newTokenForUser($user->id, $ttl);

        $circles = DB::table('circles')->select(DB::raw('circles.id, circles.name'))->join('members', function($join){
                $join->on('members.circle_id','=','circles.id')->where('members.user_id','=', Auth::user()->id);
            })->get();

        return JR::ok(array(
            'user'=> array('id'=>$user->id,
                'username'=> $user->username,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'email'=>$user->email),
            'circles' => $circles,
            'auth_token' => $token,
            'auth_token_expires_at' => time()+$ttl));
    }

    public function index() {
        $ids = Input::get('ids');
        $keywords = Input::get('keywords');

        $fields = Input::get('fields');
        $fields[] = 'id';
        $fields = array_unique($fields);
        $allow_fields = array('id', 'nickname','avatar','gender');
        $fields = array_intersect($fields, $allow_fields);

        if ($ids) {
            $users = DB::table('users')->select($fields)->whereIn('id', $ids)->get();
        } else if ($keywords) {
            $users = DB::table('users')->select($fields)->whereRaw("nickname like ?", ["%$keywords%"])->get();
        }

        return JR::ok(array('objects' => $users));
    }

    public function show($id) {
        $me = Auth::user();
        $user = User::find($id);
        if ($user) {
            $object = $user->toArray();
            $object['friend_id'] = 0;

            $friend = Friend::whereRaw('(user_id=? and friend_id=?) or (user_id=? and friend_id=?)', array($me->id, $user->id,$user->id,$me->id))->first();
            if ($friend && $friend->status == Friend::STATUS_CONFIRM) {
                $object['friend_id'] = $friend->id;
            }

            $object['channel_id'] = Channel::p2pChanel($me->id, $id)->id;

            return JR::ok(array('object' => $object));
        }

        return JR::ok(array('object' => null));
    }

    public function update($id) {
        $me = Auth::user();
        if ($id != $me->id) {
            return JR::fail(Code::NOT_ALLOW);
        }

        $avatar = Input::get('avatar');
        $gender = Input::get('gender');
        $me->avatar = $avatar;
        $me->gender = $gender;
        $me->save();

        return JR::ok();
    }

}