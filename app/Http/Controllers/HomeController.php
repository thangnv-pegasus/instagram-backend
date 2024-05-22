<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function show(Request $request)
    {
        // $posts = DB::table('posts')
        //     ->join('profile', 'profile.user_id', '=', 'posts.user_id')
        //     ->join('post_image', 'post_image.post_id', '=', 'posts.id')
        //     ->select('posts.id as post_id', 'profile.user_id', 'post_image.images_id')
        //     ->orderBy('posts.created_at', 'asc')
        //     ->paginate(1);


        // for ($i = 0; $i < count($posts); $i++) {
        //     $check_id = [];
        //     $post_detail = DB::table('posts')->where('id', '=', $posts[$i]->post_id)->first();
        //     $posts[$i]->post_detail = $post_detail;
        //     for ($j = $i + 1; $j < count($posts); $j++) {
        //         if ($posts[$i]->post_id === $posts[$j]->post_id && $posts[$i]->user_id === $posts[$j]->user_id) {
        //             $check_id[] = $j;
        //             $image = DB::table('images')->where('id', '=', $posts[$j]->images_id)->first();
        //             if (is_array($posts[$i]->images_id)) {
        //                 $posts[$i]->images_id[] = $image;
        //             } else {
        //                 $image2 = DB::table('images')->where('id', '=', $posts[$i]->images_id)->first();
        //                 $posts[$i]->images_id = [$image2, $image];
        //             }
        //         }
        //     }
        //     foreach ($check_id as $key => $value) {
        //         // dd($value, $posts[$value]);
        //         $posts->forget($value);
        //         // unset($posts[$value]);
        //     }
        //     // reset key of collection
        //     $posts = $posts->values();
        //     // dd($posts);
        // }

        // for ($i = 0; $i < count($posts); $i++) {
        //     $profile = DB::table('profile')->where('user_id', '=', $posts[$i]->user_id)->first();
        //     $posts[$i]->user_profile = $profile;
        // }

        $user = DB::table('users')
            ->join('profile', 'users.id', '=', 'profile.user_id')
            ->where('users.id', '=', auth()->user()->id)
            ->select('profile.*', 'users.fullname', 'users.role', 'users.sex', 'users.email')
            ->first();

        $recommendUsers = DB::table('users')
            ->join('profile', 'users.id', '=', 'profile.user_id')
            ->where('users.id', '!=', auth()->user()->id)
            ->limit(4)
            ->select('profile.*', 'users.fullname', 'users.role', 'users.sex', 'users.email')
            ->get();

        return response([
            'status' => 200,
            'message' => 'get post of home page is successed',
            // 'posts' => $posts,
            'user' => $user,
            'recommnedUsers' => $recommendUsers
        ]);
    }

    public function postsHome(Request $request)
    {
        $posts = DB::table('posts')
            ->join('profile', 'profile.user_id', '=', 'posts.user_id')
            ->join('post_image', 'post_image.post_id', '=', 'posts.id')
            ->select('posts.id as post_id', 'profile.user_id', 'post_image.images_id')
            ->orderBy('posts.created_at', 'asc')
            ->get();


        for ($i = 0; $i < count($posts); $i++) {
            $check_id = [];
            $post_detail = DB::table('posts')->where('id', '=', $posts[$i]->post_id)->first();
            $posts[$i]->post_detail = $post_detail;
            for ($j = $i + 1; $j < count($posts); $j++) {
                if ($posts[$i]->post_id === $posts[$j]->post_id && $posts[$i]->user_id === $posts[$j]->user_id) {
                    $check_id[] = $j;
                    $image = DB::table('images')->where('id', '=', $posts[$j]->images_id)->first();
                    if (is_array($posts[$i]->images_id)) {
                        $posts[$i]->images_id[] = $image;
                    } else {
                        $image2 = DB::table('images')->where('id', '=', $posts[$i]->images_id)->first();
                        $posts[$i]->images_id = [$image2, $image];
                    }
                }
            }
            if (count($check_id) === 0) {
                $image = DB::table('images')->where('id', '=', $posts[$i]->images_id)->first();
                $posts[$i]->images_id = [];
                $posts[$i]->images_id[] = $image;
            } else {
                foreach ($check_id as $key => $value) {
                    // dd($value, $posts[$value]);
                    $posts->forget($value);
                    // unset($posts[$value]);
                }
            }
            // reset key of collection
            $posts = $posts->values();
            // dd($posts);
        }

        for ($i = 0; $i < count($posts); $i++) {
            $profile = DB::table('profile')->where('user_id', '=', $posts[$i]->user_id)->first();
            $posts[$i]->user_profile = $profile;
        }

        // Số phần tử trên mỗi trang
        $perPage = 2;

        // Lấy trang hiện tại từ query string, mặc định là 1
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Tính vị trí bắt đầu của trang hiện tại
        $startIndex = ($currentPage - 1) * $perPage;

        // Lấy các phần tử cho trang hiện tại
        $currentItems = $posts->slice($startIndex, $perPage)->values();

        // Tạo một đối tượng LengthAwarePaginator
        $paginator = new LengthAwarePaginator($currentItems, $posts->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        // $user = DB::table('users')
        //     ->join('profile', 'users.id', '=', 'profile.user_id')
        //     ->where('users.id', '=', auth()->user()->id)
        //     ->select('profile.*', 'users.fullname', 'users.role', 'users.sex', 'users.email')
        //     ->first();

        // $recommendUsers = DB::table('users')
        //     ->join('profile', 'users.id', '=', 'profile.user_id')
        //     ->where('users.id', '!=', auth()->user()->id)
        //     ->limit(4)
        //     ->select('profile.*', 'users.fullname', 'users.role', 'users.sex', 'users.email')
        //     ->get();

        return response([
            'status' => 200,
            'message' => 'get post of home page is successed',
            'posts' => $paginator,
            // 'pagination' => $paginator
        ]);
    }
}
