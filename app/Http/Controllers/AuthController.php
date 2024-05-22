<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function test(Request $request)
    {
        return response([
            'message' => 'demo route',
            'user' => auth()->user()
        ]);
    }

    public function login(Request $request)
    {
        try {
            if (auth()->attempt(['account_name' => $request->username, 'password' => $request->password]) || auth()->attempt(['email' => $request->username, 'password' => $request->password]) || auth()->attempt(['phone' => $request->username, 'password' => $request->password])) {
                $token = auth()->user()->createToken('InstagramToken')->accessToken;

                $cookie = cookie('laravel_session', session()->getId(), config('session.lifetime'));
                return response([
                    'status' => 200,
                    'message' => 'login successed',
                    'data' => auth()->user(),
                    'token' => $token,
                    'cookie' => $cookie,
                    'id' => session()->getId()
                ]);
            }
            return response([
                'status' => 404,
                'message' => 'login failed',
                'data' => $request->all(),
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'login failed',
                'data' => $request->all(),
                'error' => $e
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = auth()->user();
            $user->tokens()->delete();
            return response([
                'status' => 200,
                'message' => 'Logout successed',
                'user' => ''
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 401,
                'message' => 'Unauthenticate',
                'user' => $request->user()
            ]);
        }
    }

    public function register(Request $request)
    {

        $validateValue = 'required | unique:users,phone | regex:/^0[0-9]{10}$/';
        if (!ctype_digit($request->account_name)) {
            $validateValue = 'required | email | unique:users,email';
        }
        $validator = Validator::make($request->all(), [
            'account_name' => $validateValue,
            'fullname' => 'required | min:6',
            'nickname' => 'required | min:6 | max:100 | unique:users,account_name',
            'password' => 'required | min:6 | max:20'
        ], [
            'account_name.unique' => 'Email hoặc số điện thoại đã được sử dụng.',
            'account_name.required' => 'Bạn cần nhập địa chỉ email hoặc số điện thoại.',
            'account_name.email' => 'Địa chỉ email không hợp lệ.',
            'account_name.regex' => 'Số điện thoại không hợp lệ',
            'password.required' => 'Bạn cần nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.max' => 'Mật khẩu chỉ được tối đa :max ký tự.',
            'nickname.require' => 'Bạn cần nhập tên người dùng.',
            'nickname.unique' => 'Tên người dùng đã được sử dụng',
            'nickname.min' => 'Tên người dùng phải có ít nhất :min ký tự',
            'nickname.max' => 'Tên người dùng chỉ được tối đa :max ký tự',
            'fullname.required' => 'Hãy nhập họ tên đầy đủ của bạn',
            'fullname.min' => 'Tên đầy đủ phải có ít nhất :min ký tự'
        ]);
        try {
            $user = [];
            if (!ctype_digit($request->account_name)) {
                $user = DB::table('users')->insertGetId([
                    'email' => $request->account_name,
                    'fullname' => $request->fullname,
                    'account_name' => $request->nickname,
                    'password' => Hash::make($request->password)
                ]);
            } else {
                $user = DB::table('users')->insertGetId([
                    'phone' => $request->account_name,
                    'fullname' => $request->fullname,
                    'account_name' => $request->nickname,
                    'password' => Hash::make($request->password)
                ]);
            }

            DB::table('profile')->insert([
                'user_id' => $user,
                'nickname' => DB::table('users')->where('id', '=', $user)->first()->account_name,
                'avatar_url' => 'https://placehold.it/100x100'
            ]);

            return response([
                'status' => 200,
                'message' => 'resgiter successed',
                'req' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'account is already exist',
                'error' => $validator->errors()
            ]);
        }

    }

    public function googlePage(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    public function loginGoogle(Request $request)
    {
        $user = Socialite::driver('google')->user();
        return response([
            'status' => 200,
            'message' => 'get user of google is successed',
            'data' => $user
        ]);
    }
}
