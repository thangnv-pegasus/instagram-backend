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

    public function likeComment(Request $request){
        try{
            if($request->type === 'parent'){
                DB::table('comments')->where('id','=',$request->comment_id)->increment('like_total',1);
            }
            if($request->type === 'child'){
                DB::table('child_comment')->where('id','=',$request->comment_id)->increment('like_total',1);

            }
            return response([
                'status' => 200,
                'message' => 'like comment is successed',
                "data" => $request->all()
            ]);
        }catch(Exception $e){
            return response([
                'status' => 200,
                'message' => 'like comment is failed',
                'error' => $e
            ]);
        }
    }
}
