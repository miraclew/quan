<?php
class MemberController extends BaseController {

    public function index() {
        $circle_id = Input::get('circle_id');


        $query = DB::table('members')
            ->leftJoin('users', 'members.user_id','=','users.id')
            ->select('members.id', 'members.user_id', 'users.nickname','users.avatar')
            ->whereRaw('circle_id=?',[$circle_id]);

        $members = $query->get();
        return JR::ok(['objects' => $members, 'circle_id'=>$circle_id]);
    }

    public function store() {
        $circle_id = Input::get('circle_id');
        $user_id = Auth::user()->id;

        $count = Member::whereRaw('circle_id=? and user_id=?', [$circle_id, $user_id])->count();
        if ($count > 0) {
            return JR::fail(Code::DATA_DUPLICATE);
        }

        $member = new Member();
        $member->circle_id = $circle_id;
        $member->user_id = $user_id;
        $member->save();

        return JR::ok(['object'=>$member->toArray()]);
    }

    public function destroy($id) {
        $member = Member::find($id);
        if (!$member) {
            return JR::fail(Code::FAIL);
        }
        if ($member->user_id != Auth::user()->id) {
            return JR::fail(Code::NOT_ALLOW);
        }

        $member->delete();
        return JR::ok();
    }
}