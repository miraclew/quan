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

        $circles = [];
        if ($scope == self::CIRCLE_SCOPE_MY) {
            $query->select(DB::raw('circles.*'));
            $query->join('members', function($join){
                $join->on('members.circle_id','=','circles.id')->where('members.user_id','=', Auth::user()->id);
            });
            $circles = $query->get();
        } else if ($scope == self::CIRCLE_SCOPE_NEAR) {
            $latitude = Input::get('latitude');
            $longitude = Input::get('longitude');
            $lbs = new LBS();
            $circle_ids = $lbs->getCirclesAround($longitude, $latitude, $skip, $limit);
            foreach ($circle_ids as $v) {
                $circle = Circle::find($v['circle_id']);
                if (!$circle) {
                    continue;
                }
                $cc = $circle->toArray();
                $cc['distance'] = $v['distance'];
                $cc['distance_text'] = $v['distance_text'];

                $circles[] = $cc;
            }
        } else if ($scope == self::CIRCLE_SCOPE_SEARCH) {
            $keywords = Input::get('keywords');
            $query->whereRaw('name like ?', ["%$keywords%"]);
            $circles = $query->get();
        }

        foreach ($circles as &$value) {
            if (is_object($value)) {
                $value->members_count = strval(Member::whereRaw('circle_id=?',[$value->id])->count());
            } else if (is_array($value)) {
                $value['members_count'] = strval(Member::whereRaw('circle_id=?',[$value['id']])->count());
            }
        }

        return JR::ok(array('objects' => $circles));
    }

    public function store()
    {
        $validator = Validator::make(
            Input::all(),
            array(
                'name' => 'required|min:2',
                'address' => 'required|min:2',
            )
        );
        if ($validator->fails())
        {
            return JR::fail(Code::PARAMS_INVALID);
        }

        $user_id = Auth::user()->id;
        $name = Input::get('name');
        $address = Input::get('address');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $place_uid = Input::get('place_uid');

        if ($place_uid) {
            $circle = Circle::where('place_uid','=',$place_uid)->first();
        }

        if ($circle) {
            $object = $circle->toArray();
            $object['member_id'] = $this->getMemberId($circle->id);
        } else {
            $circle = new Circle();
            $circle->name = $name;
            $circle->address = $address;
            $circle->creator_id = $user_id;
            if ($latitude && $longitude) {
                $circle->lat = $latitude;
                $circle->lng = $longitude;
            }
            if ($place_uid) {
                $circle->place_uid = $place_uid;
            }
            $circle->is_locked = false;
            $circle->posts_count = 0;
            $circle->members_count = 0;
            $circle->save();

            if ($latitude && $longitude) {
                $lbs = new LBS();
                $lbs->setCircleLocation($circle->id, $circle->lng, $circle->lat);
            }
            $object = $circle->toArray();
            $object['member_id'] = 0;
        }

        return JR::ok(array('object' => $object));
    }

    public function show($id) {
        if (stripos($id, 'place_uid') !== false) {
            $place_uid = str_replace('place_uid', '', $id);
            $circle = Circle::where('place_uid','=',$place_uid)->first();
        }
        else
            $circle = Circle::find($id);

        if (!$circle) {
            return JR::fail(Code::DATA_NOT_FOUND);
        }

        $object = $circle->toArray();
        $object['member_id'] = $this->getMemberId($circle->id);

        return JR::ok(['object'=>$object]);
    }

    private function getMemberId($circle_id) {
        $member = Member::whereRaw('user_id=? and circle_id=?', [Auth::user()->id, $circle_id])->first();
        if ($member) {
            return $member->id;
        }
        return 0;
    }
}