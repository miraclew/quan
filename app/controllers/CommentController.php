<?php
class CommentController extends BaseController {
    public function index() {
        $post_id = Input::get('post_id');
        $limit = intval(Input::get('limit', 20));
        $skip = intval(Input::get('skip', 0));

        $query = DB::table('comments')
            ->leftJoin('users', 'comments.user_id','=','users.id')
            ->select('comments.*', 'users.nickname','users.avatar')
            ->where('post_id','=',$post_id)
            ->orderBy('id', 'desc')
            ->skip($skip)->take($limit);

        $comments = $query->get();
        $floor = count($comments);
        foreach ($comments as &$value) {
            $value->floor = $floor;
            $floor--;
        }

        return JR::ok(['objects' => $comments]);
    }

    public function store() {
        $post_id = Input::get('post_id');
        $text = Input::get('text');
        $user_id = Auth::user()->id;

        $post = Post::find($post_id);
        if (!$post) {
            return JR::fail(Code::DATA_NOT_FOUND);
        }

        // $is_member = Member::is_member($post->circle_id, $user_id);
        // if (!$is_member) {
        //     return JR::fail(Code::NOT_ALLOW);
        // }

        if (strlen($text) <= 0) {
            return JR::fail(Code::PARAMS_INVALID, "请输入评论");
        }
        $comment = new Comment();
        $comment->post_id = $post_id;
        $comment->text = $text;
        $comment->user_id = $user_id;
        $comment->save();

        // update post count
        DB::table('posts')->where('id','=',$post_id)->increment('comments_count');

        return JR::ok(['object'=>$comment->toArray()]);
    }
}