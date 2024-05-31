<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function show(Request $request)
    {

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
            ->select('posts.id as post_id', 'profile.user_id', 'post_image.images_id as images')
            ->orderBy('posts.created_at', 'asc')
            ->get();

            
        for ($i = 0; $i < count($posts); $i++) {
            $check_id = [];
            $post_detail = DB::table('posts')->where('id', '=', $posts[$i]->post_id)->first();
            $posts[$i]->post_detail = $post_detail;
            for ($j = $i + 1; $j < count($posts); $j++) {
                if ($posts[$i]->post_id === $posts[$j]->post_id && $posts[$i]->user_id === $posts[$j]->user_id) {
                    $check_id[] = $j;
                    $image = DB::table('images')->where('id', '=', $posts[$j]->images)->first();
                    if (is_array($posts[$i]->images)) {
                        $posts[$i]->images[] = $image;
                    } else {
                        $image2 = DB::table('images')->where('id', '=', $posts[$i]->images)->first();
                        $posts[$i]->images = [$image2, $image];
                    }
                }
            }
            if (count($check_id) === 0) {
                $image = DB::table('images')->where('id', '=', $posts[$i]->images)->first();
                $posts[$i]->images = [];
                $posts[$i]->images[] = $image;
            } else {
                foreach ($check_id as $key => $value) {
                    // dd($value, $posts[$value]);
                    $posts->forget($value);
                    // unset($posts[$value]);
                }
            }
            // reset key of collection
            $posts = $posts->values();
            $check_like = DB::table('posts_like')
            ->where('user_id','=',auth()->user()->id)
            ->where('post_id','=',$posts[$i]->post_id)
            ->first();
            if($check_like){
                $posts[$i]->is_like = true;
            }else{
                $posts[$i]->is_like = false;
            }

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
        $paginator = new LengthAwarePaginator($currentItems, count($posts), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return response([
            'status' => 200,
            'message' => 'get post of home page is successed',
            'posts' => $paginator,
        ]);
    }

    public function storyPaginate(Request $request)
    {
        try {
            $story = DB::table('story')
                ->join('story_image', 'story.id', '=', 'story_image.story_id')
                ->select('story.id as story_id', 'story.user_id', 'story.content', 'story.expired_at', 'story_image.image_id')
                ->get();

            foreach ($story as $key => $value) {
                $user_infor = DB::table('users')
                    ->join('profile', 'users.id', '=', 'profile.user_id')
                    ->select('users.id as user_id', 'profile.nickname', 'users.fullname', 'users.email')
                    ->where('users.id', '=', $value->user_id)
                    ->first();
                $story[$key]->user_infor = $user_infor;
            }
            for ($i = 0; $i < count($story); $i++) {
                $check_id = [];
                $story_detail = DB::table('story')->where('id', '=', $story[$i]->story_id)->first();
                for ($j = $i + 1; $j < count($story); $j++) {
                    if ($story[$i]->story_id === $story[$j]->story_id && $story[$i]->user_id === $story[$j]->user_id) {
                        $check_id[] = $j;
                        $image = DB::table('images')->where('id', '=', $story[$j]->image_id)->first();
                        if (is_array($story[$i]->image_id)) {
                            $story[$i]->image_id[] = $image;
                        } else {
                            $image2 = DB::table('images')->where('id', '=', $story[$i]->image_id)->first();
                            $story[$i]->image_id = [$image, $image2];
                        }
                    }
                }

                if (count($check_id) === 0) {
                    $image = DB::table('images')->where('id', '=', $story[$i]->image_id)->first();
                    $story[$i]->image_id = [];
                    $story[$i]->image_id[] = $image;
                } else {
                    foreach ($check_id as $key => $value) {
                        // dd($value, $story[$value]);
                        $story->forget($value);
                        // unset($story[$value]);
                    }
                }
                // reset key of collection
                $story = $story->values();
                // dd($story);
            }

            // Số phần tử trên mỗi trang
            $perPage = 2;

            // Lấy trang hiện tại từ query string, mặc định là 1
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            // Tính vị trí bắt đầu của trang hiện tại
            $startIndex = ($currentPage - 1) * $perPage;

            // Lấy các phần tử cho trang hiện tại
            $currentItems = $story->slice($startIndex, $perPage)->values();

            // Tạo một đối tượng LengthAwarePaginator
            $paginator = new LengthAwarePaginator($currentItems, $story->count(), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);

            return response([
                'status' => 200,
                'message' => 'get story is successed',
                'story' => $paginator
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'get story is failed',
                'error' => $e
            ]);
        }
    }
}
