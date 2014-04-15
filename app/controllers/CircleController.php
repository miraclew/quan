<?php
class CircleController extends BaseController {
    public function index()
    {
        $limit = intval(Input::get('limit', 20));
        $skip = intval(Input::get('skip', 2));
        $circles = DB::table('circles')->skip($skip)->take($limit)->get();

        return Response::json(array('objects' => $circles));
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
            return Response::json(array('error'=>array('code'=>-1,'message'=>'params invalid')));
        }

        $name = Input::get('name');
        $location = Input::get('location');

        $user_id = Auth::user()->id;

        $circle = new Circle();
        $circle->name = $name;
        $circle->location = $location;
        $circle->creator_id = $user_id;
        $circle->is_locked = false;
        $circle->posts_count = 0;
        $circle->members_count = 0;
        $circle->save();

        return Response::json(array('object' => $circle->toArray()));
    }
}