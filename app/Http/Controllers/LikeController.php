<?php

namespace App\Http\Controllers;

use App\Events\LikeSent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function like(Request $request)
    {
        try {
            if ($request->type === 'post') {
                DB::table('posts')->where('id', '=', $request->post_id)->increment('like_total', 1);
                DB::table('posts_like')->insert(['user_id' => auth()->user()->id, 'post_id' => $request->post_id]);
                event(new LikeSent($request->post_id, 'post', DB::table('posts')->where('id', '=', $request->post_id)->first()->like_total));
            }
            if ($request->type === 'parent') {
                DB::table('comments')->where('id', '=', $request->comment_id)->increment('like_total', 1);
                DB::table('comment_like')->insert(['user_id' => auth()->user()->id, 'comment_id' => $request->comment_id]);
                event(new LikeSent($request->comment_id, 'parent', DB::table('comments')->where('id', '=', $request->comment_id)->first()->like_total));
            }
            if ($request->type === 'child') {
                DB::table('child_comment')->where('id', '=', $request->comment_id)->increment('like_total', 1);
                DB::table('childcomment_like')->insert(['user_id' => auth()->user()->id, 'comment_id' => $request->comment_id]);
                event(new LikeSent($request->comment_id, 'child', DB::table('child_comment')->where('id', '=', $request->comment_id)->first()->like_total));
            }

            return response([
                'status' => 200,
                'message' => 'like event is successed'
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'like event failed',
                'error' => $e
            ]);
        }
    }

    public function unlike(Request $request)
    {
        try {
            if ($request->type === 'post') {
                DB::table('posts')->where('id', '=', $request->post_id)->decrement('like_total', 1);
                DB::table('posts_like')->where('post_id', '=', $request->post_id)->where('user_id', '=', auth()->user()->id)->delete();
                event(new LikeSent($request->post_id, 'post', DB::table('posts')->where('id', '=', $request->post_id)->first()->like_total));
            }
            if ($request->type === 'parent') {
                DB::table('comments')->where('id', '=', $request->comment_id)->decrement('like_total', 1);
                DB::table('comment_like')->where('comment_id', '=', $request->comment_id)->where('user_id', '=', auth()->user()->id)->delete();
                event(new LikeSent($request->comment_id, 'parent', DB::table('comments')->where('id', '=', $request->comment_id)->first()->like_total));
            }
            if ($request->type === 'child') {
                DB::table('child_comment')->where('id', '=', $request->comment_id)->decrement('like_total', 1);
                DB::table('childcomment_like')->where('comment_id', '=', $request->comment_id)->where('user_id', '=', auth()->user()->id)->delete();
                event(new LikeSent($request->comment_id, 'child', DB::table('child_comment')->where('id', '=', $request->comment_id)->first()->like_total));
            }

            return response([
                'status' => 200,
                'message' => 'unlike event is successed'
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'unlike is failed'
            ]);
        }
    }
}
