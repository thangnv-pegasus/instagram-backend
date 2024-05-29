<?php

namespace App\Http\Controllers;

use App\Events\CommentSent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function create(Request $request)
    {

        try {
            DB::table('comments')->insert([
                'user_id' => auth()->user()->id,
                'post_id' => $request->post_id,
                'content' => $request->content,
                'like_total' => 0
            ]);

            event(new CommentSent($request->content));

            return response([
                'status' => 200,
                'message' => 'create comment is successed',
                'data' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'create comment is failed',
                'error' => $e
            ]);
        }
    }

    public function show(Request $request)
    {
        try {
            $postId = $request->postId;
            $comments = DB::table('comments')
                ->join('profile', 'profile.user_id', '=', 'comments.user_id')
                ->where('post_id', '=', $postId)
                ->orderByDesc('created_at')
                ->select('comments.*', 'profile.nickname', 'profile.avatar_url')
                ->get();
            foreach ($comments as $key => $comment) {
                $childCmt = DB::table('child_comment')
                    ->join('profile', 'profile.user_id', '=', 'child_comment.user_id')
                    ->where('comment_id', '=', $comment->id)
                    ->where('post_id', '=', $postId)
                    ->orderByDesc('created_at')
                    ->select('child_comment.*', 'profile.nickname', 'profile.avatar_url')
                    ->get();
                $comments[$key]->child_comment = $childCmt;
            }
            return response([
                'status' => 200,
                'message' => 'get comments is successed',
                'comments' => $comments
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 400,
                'message' => 'get comments is failed'
            ]);
        }
    }

    public function createChild(Request $request)
    {
        try {
            DB::table('child_comment')->insert([
                'comment_id' => $request->comment_id,
                'content' => $request->content,
                'post_id' => $request->post_id,
                'user_id' => auth()->user()->id,
                'like_total' => 0
            ]);
            event(new CommentSent($request->content));
            return response([
                'status' => 200,
                'message' => 'create child comment is successed',
                'data' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'create child comment is failed',
                'error' => $e
            ]);
        }
    }

    public function likeComment(Request $request)
    {
        try {
            if ($request->type === 'parent') {
                DB::table('comments')->where('id', '=', $request->comment_id)->increment('like_total', 1);
            }
            if ($request->type === 'child') {
                DB::table('child_comment')->where('id', '=', $request->comment_id)->increment('like_total', 1);

            }
            return response([
                'status' => 200,
                'message' => 'like comment is successed',
                "data" => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'like comment is failed',
                'error' => $e
            ]);
        }
    }
}
