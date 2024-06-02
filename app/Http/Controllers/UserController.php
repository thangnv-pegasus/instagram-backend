<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function show(Request $request)
    {
        try {
            $user = DB::table('users')
                ->join('profile', 'users.id', '=', 'profile.user_id')
                ->where('users.account_name', '=', $request->nickname)
                ->select(
                    'profile.nickname',
                    'profile.avatar_url',
                    'profile.bio',
                    'users.created_at',
                    'users.date_of_birth',
                    'users.email',
                    'users.fullname',
                    'profile.user_id',
                    'profile.is_company',
                    'profile.is_real',
                    'users.phone',
                    'profile.priority',
                    'users.role',
                    'users.sex',
                    'users.updated_at'
                )
                ->first();
            if ($request->nickname === auth()->user()->account_name) {
                $user->type = 'me';
            } else {
                $user->type = 'user';
            }
            $user->post_length = DB::table('posts')->where('user_id', '=', $user->user_id)->count();
            $follower = DB::table('followers')->where('user_myfollow_id', '=', $user->user_id)->select('followers.user_id')->get();
            $mefollow = DB::table('followers')->where('user_id', '=', $user->user_id)->select('followers.user_myfollow_id as user_follow')->get();
            return response([
                'status' => 200,
                'message' => 'get profile user successed',
                'user' => $user,
                'followers' => $follower,
                'mefollow' => $mefollow
            ]);


        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'get profile user failed',
                'error' => $e,
                'request' => $request->header()
            ]);
        }
    }

    public function update(Request $request)
    {

        try {
            // Định nghĩa các quy tắc xác thực
            $rules = [
                'nickname' => 'required|max:255',
                'fullname' => 'required|max:255',
                'email' => 'email',
                'phone' => 'required | regex:/^(\+84|0)(3|5|7|8|9)[0-9]{8}$/',
                'dob' => 'required | date'
            ];

            // Định nghĩa các thông báo lỗi tùy chỉnh
            $messages = [
                'nickname.required' => 'Tên tài khoản là bắt buộc.',
                'nickname.max' => 'Tên tài khoản không được vượt quá :max ký tự.',
                'fullname.required' => 'Họ tên là bắt buộc.',
                'fullname.max' => 'Họ tên không được vượt quá :max ký tự.',
                'email.email' => 'Hãy nhập đúng địa chỉ email',
                'phone.required' => 'Hãy nhập số điện thoại',
                'phone.regex' => 'Hãy nhập số điện thoại hợp lệ',
                'dob.required' => 'Hãy nhập ngày sinh',
                'dob.date' => 'Hãy nhập đúng định dạng ngày/tháng/năm'
            ];
            // Tạo validator với các quy tắc và thông báo lỗi tùy chỉnh
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response([
                    'status' => 500,
                    'message' => 'update profile user is failed',
                    'error' => $validator->errors()
                ]);
            }
            DB::table('users')
                ->where('id', '=', auth()->user()->id)
                ->update([
                    'account_name' => $request->nickname,
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'sex' => $request->sex,
                    'date_of_birth' => $request->dob
                ]);
            DB::table('profile')
                ->where('user_id', '=', auth()->user()->id)
                ->update([
                    'nickname' => $request->nickname,
                    'bio' => $request->bio,
                    'avatar_url' => $request->avatar_url,
                ]);

            return response([
                'status' => 200,
                'message' => 'update profile user is successed',
                'user' => DB::table('users')
                    ->join('profile', 'users.id', '=', 'profile.user_id')
                    ->where('users.id', '=', auth()->user()->id)
                    ->first()
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'update profile user is failed',
                'error' => $e
            ]);
        }
    }

    public function myFriend(Request $request)
    {
        try {
            $friends = DB::table('followers as f1')
                ->join('followers as f2', function ($join) {
                    $join->on('f1.user_id', '=', 'f2.user_id')
                        ->on('f1.user_id', '=', 'f2.user_id');
                })
                ->select('f1.user_id as me_id', 'f1.user_id')
                ->where('f1.user_id', '=', auth()->user()->id)
                ->get();

            return response([
                'status' => 200,
                'message' => 'show story to your friend is successed',
                'data' => $friends
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'get friend is failed',
                'error' => $e
            ]);
        }
    }

    public function follower(Request $request)
    {
        try {
            $users = [];
            if ($request->way === 'follow-me') {
                $users = DB::table('followers')
                    ->join('users', 'followers.user_id', '=', 'users.id')
                    ->where('user_id', '=', auth()->user()->id)
                    ->select('users.*')
                    ->get();
            }
            if ($request->way === 'my-follow') {
                $users = DB::table('followers')
                    ->join('users', 'followers.user_myfollow_id', '=', 'users.id')
                    ->where('user_myfollow_id', '=', auth()->user()->id)
                    ->select('users.*')
                    ->get();
            }
            return response([
                'status' => 200,
                'message' => 'get followers is successed',
                'data' => $users
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'show followers is failed',
                'error' => $e
            ]);
        }
    }

    public function updateRole(Request $request)
    {
        try {
            if ($request->type === 'company') {
                DB::table('profile')->where('user_id', '=', auth()->user()->id)->update(['is_company' => $request->status]);
            }
            if ($request->type === 'real-account') {
                DB::table('profile')->where('user_id', '=', auth()->user()->id)->update(['is_real' => $request->status]);
            }
            $profileUser = DB::table('profile')->where('user_id', '=', auth()->user()->id)->first();
            $checkPriority = 0;
            if ($profileUser->is_company === 1) {
                $checkPriority++;
            }
            if ($profileUser->is_real === 1) {
                $checkPriority++;
            }
            DB::table('profile')->where('user_id', '=', auth()->user()->id)->update(['priority' => $checkPriority]);
            return response([
                'status' => 200,
                'message' => 'update is successed',
                'data' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'update is failed',
                'error' => $e
            ]);
        }
    }

}
