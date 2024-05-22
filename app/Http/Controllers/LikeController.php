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
                event(new LikeSent(['comment_id' => $request->post_id]));
            }
            if ($request->type === 'parent') {
                DB::table('comments')->where('id', '=', $request->comment_id)->increment('like_total', 1);
                event(new LikeSent(['comment_id' => $request->comment_id]));
            }
            if ($request->type === 'child') {
                DB::table('child_comment')->where('id', '=', $request->comment_id)->increment('like_total', 1);
                event(new LikeSent(['comment_id' => $request->comment_id]));
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
}
