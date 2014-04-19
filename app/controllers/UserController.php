<?php
class UserController extends BaseController {

    public function postLogin() {
        $email = Input::get('email');
        $password = Input::get('password');

        if (Auth::attempt(array('email' => $email, 'password' => $password)))
        {
            $user = Auth::user();
            $ttl = 86400;
            $token = Token::newTokenForUser($user->id, $ttl);

            return Response::json(array(
                'user'=> array('id'=>$user->id, 'email'=> $user->email),
                'auth_token' => $token,
                'auth_token_expires_at' => time()+$ttl));
        } else {
            return Response::json(array('error'=>array('code'=>-1, 'message'=>'登录失败, 用户名或密码错误')));
        }
    }

    public function postLogout() {
        Auth::logout();
        return Response::json(array('success'=>true));
    }

    public function postRegister() {
        $email = Input::get('email');
        $password = Input::get('password');
        $password_digest = Hash::make($password);

        $count = User::where('email', '=', $email)->count();
        if ($count > 0) {
            return Response::json(array('error_code'=>-1, 'error_msg'=>'user exists'));
        }

        $user = new User();
        $user->email = $email;
        $user->password = $password_digest;
        $user->gender = 1;
        $user->is_locked = false;
        $user->save();

        // login
        Auth::logout();
        Auth::loginUsingId($user->id);

        return Response::json(array('user'=> array('id'=>$user->id, 'email'=> $user->email)));
    }

    public function getProfile()
    {
        $user = Auth::user();
        return Response::json(array('object' => $user->toArray()));
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
            return Response::json(array('object' => $object));
        }

        return Response::json(array('object' => null));
    }

}