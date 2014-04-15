<?php
class UserController extends BaseController {

    public function postLogin() {
        $email = Input::get('email');
        $password = Input::get('password');

        if (Auth::attempt(array('email' => $email, 'password' => $password)))
        {
            $user = Auth::user();
            $token = Token::authToken($user->id, 600);

            return Response::json(array(
                'user'=> array('id'=>$user->id, 'email'=> $user->email),
                'auth_token' => $token->token,
                'auth_token_expires_at' => $token->expires_at));
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

        return Response::json(array('id'=>$user->id));
    }

    public function getProfile()
    {
        return Response::json(array('name' => 'Steve', 'state' => 'CA'));
    }

}