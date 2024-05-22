<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function follow(Request $request)
    {

        $user = auth()->user();
        try {
            DB::table('followers')->insert([
                'user_id' => $user->id,
                'user_myfollow_id' => $request->id
            ]);
            return response([
                'status' => 200,
                'message' => 'follow user success'
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'follow user success',
                'error' => $e
            ]);
        }
    }

    public function unfollow(Request $request)
    {
        $user = auth()->user();
        try {
            DB::table('followers')->where('user_id', '=', $user->id)->where('user_myfollow_id', '=', $request->id)->delete();
            return response([
                'status' => 200,
                'message' => 'unfollow user success',
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'unfollow user success',
                'error' => $e
            ]);
        }
    }
}
