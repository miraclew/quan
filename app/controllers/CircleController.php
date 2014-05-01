<?php
class CircleController extends BaseController {
    const CIRCLE_SCOPE_MY = 0;
    const CIRCLE_SCOPE_NEAR = 1;
    const CIRCLE_SCOPE_SEARCH = 2;

    public function index()
    {
        $limit = intval(Input::get('limit', 20));
        $skip = intval(Input::get('skip', 0));
        $scope = intval(Input::get('scope'));
        $query = DB::table('circles')->skip($skip)->take($limit);

        if ($scope == self::CIRCLE_SCOPE_MY) {
            $query->select(DB::raw('circles.*'));
            $query->join('members', function($join){
                $join->on('members.circle_id','=','circles.id')->where('members.user_id','=', Auth::user()->id);
            });
        } else if ($scope == self::CIRCLE_SCOPE_NEAR) {

        } else if ($scope == self::CIRCLE_SCOPE_SEARCH) {
            $keywords = Input::get('keywords');
            $query->whereRaw('name like ?', ["%$keywords%"]);
        }

        $circles = $query->get();

        return JR::ok(array('objects' => $circles));
    }

    public function store()
    {
        $validator = Validator::make(
            Input::all(),
            array(
                'name' => 'required|min:2',
                'location' => 'required|min:2',
            )
        );
        if ($validator->fails())
        {
            return JR::fail(Code::PARAMS_INVALID);
        }

        $name = Input::get('name');
        $location = Input::get('location');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');

        $user_id = Auth::user()->id;

        $circle = new Circle();
        $circle->name = $name;
        $circle->location = $location;
        $circle->creator_id = $user_id;
        if ($latitude && $longitude) {
            $circle->lat = $latitude;
            $circle->lng = $longitude;
        }
        $circle->is_locked = false;
        $circle->posts_count = 0;
        $circle->members_count = 0;
        $circle->save();

        return JR::ok(array('object' => $circle->toArray()));
    }

    public function show($id) {
        $circle = Circle::find($id);
        if (!$circle) {
            return JR::fail(Code::DATA_NOT_FOUND);
        }

        $object = $circle->toArray();
        $object['member_id'] = 0;
        $member = Member::whereRaw('user_id=? and circle_id=?', [Auth::user()->id, $id])->first();
        if ($member) {
            $object['member_id'] = $member->id;
        }

        return JR::ok(['object'=>$object]);
    }
}