<?php
class PostController extends BaseController {
    public function index()
    {
        $limit = intval(Input::get('limit', 20));
        $skip = intval(Input::get('skip', 2));
        $posts = DB::table('posts')->skip($skip)->take($limit)->get();
        return Response::json(array('objects' => $posts));
    }

    public function store()
    {
        $validator = Validator::make(
            Input::all(),
            array(
                'circle_id' => 'required',
                'text' => 'required',
            )
        );
        if ($validator->fails())
        {
            return Response::json(array('error'=>array('code'=>-1,'message'=>'params invalid')));
        }

        $circle_id = Input::get('circle_id');
        $text = Input::get('text');
        $images = Input::get('images');

        $user_id = Auth::user()->id;

        $post = new Post();
        $post->circle_id = $circle_id;
        $post->topic_id = 0;
        $post->user_id = $user_id;
        $post->text = $text;
        $post->images = $images;
        $post->save();

        return Response::json(array('object' => $post->toArray()));
    }
}