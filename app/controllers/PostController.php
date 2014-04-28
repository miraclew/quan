<?php
class PostController extends BaseController {
    const POST_SCOPE_LATEST = 0;
    const POST_SCOPE_HOTEST = 1;

    public function index()
    {
        $limit = intval(Input::get('limit', 20));
        $skip = intval(Input::get('skip', 0));
        $scope = intval(Input::get('scope'));
        $query = DB::table('posts')
            ->leftJoin('users', 'posts.user_id','=','users.id')
            ->select('posts.*', 'users.nickname','users.avatar')
            ->skip($skip)->take($limit);
        if ($scope == self::POST_SCOPE_HOTEST) {
            $query->orderBy('likes_count', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }
        $posts = $query->get();
        return JR::ok(array('objects' => $posts));
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
            return JR::fail(Code::PARAMS_INVALID);
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

        return JR::ok(array('object' => $post->toArray()));
    }
}