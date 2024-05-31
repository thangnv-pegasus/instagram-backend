<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function showMyPost(Request $request)
    {
        try {
            $posts = DB::table('posts')->where('user_id', '=', $request->user_id)->get();

            foreach ($posts as $key => $post) {
                $images = DB::table('post_image')
                    ->join('images', 'post_image.images_id', '=', 'images.id')
                    ->where('post_id', '=', $post->id)
                    ->select('images.*')
                    ->get();
                $parentCommentCount = DB::table('comments')->where('post_id','=',$post->id)->count();
                $childCommentCount = DB::table('child_comment')->where('post_id','=',$post->id)->count();
                $post->images = $images;
                $post->comment_count = $parentCommentCount + $childCommentCount;
            }

            // Số lượng bản ghi trên mỗi trang
            $perPage = 10;

            // Lấy trang hiện tại từ request (mặc định là 1)
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            // Tính toán vị trí bắt đầu của dữ liệu cho trang hiện tại
            $currentItems = $posts->slice(($currentPage - 1) * $perPage, $perPage)->values();

            // Tạo một instance của LengthAwarePaginator
            $paginatedItems = new LengthAwarePaginator($currentItems, $posts->count(), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]);


            return response([
                'status' => 200,
                'message' => 'show all post',
                'data' => $paginatedItems,
                'user_id' => $request->all()
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'show all post is error',
                'error' => $e
            ]);
        }
    }

    public function create(Request $request)
    {
        try {
            $post_id = DB::table('posts')->insertGetId([
                'user_id' => auth()->user()->id,
                'caption' => $request->caption,
                'like_total' => 0
            ]);

            $post_image_id = DB::table('images')->insertGetId([
                'image_url' => $request->image_url,
                'image_id' => $request->image_id
            ]);

            DB::table('post_image')->insert([
                'post_id' => $post_id,
                'images_id' => $post_image_id
            ]);
            return response([
                'status' => 200,
                'message' => 'create new post is successed'
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'create new post is successed',
                'error' => $e
            ]);
        }

    }

    public function updateMyPost(Request $request)
    {
        try {
            if ($request->caption != null) {
                DB::table('posts')
                    ->where('id', '=', $request->post_id)
                    ->where('user_id', '=', auth()->user()->id)
                    ->update([
                        'caption' => $request->caption
                    ]);
            }
            if ($request->image_url) {
                DB::table('images')
                    ->where('id', '=', $request->post_id)
                    ->update([
                        'image_url' => $request->image_url,
                        'image_id' => $request->image_id
                    ]);
            }

            return response([
                'status' => 200,
                'message' => 'update my post is successed',
                'req' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'update my post is failed',
                'error' => $e
            ]);
        }
    }

    public function likePost(Request $request)
    {
        try {
            DB::table('posts')->where('id', '=', $request->id)->increment('like_total', 1);
            return response([
                'status' => 200,
                'message' => 'like post is successed',
                'data' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'like post is failed',
                'error' => $e
            ]);
        }

    }

    public function recommend(Request $request)
    {
        $posts = DB::table('posts')
            ->join('profile', 'profile.user_id', '=', 'posts.user_id')
            ->join('post_image', 'post_image.post_id', '=', 'posts.id')
            ->select('posts.id as post_id', 'profile.user_id', 'post_image.images_id')
            ->where('profile.user_id', '!=', auth()->user()->id)
            ->orderBy('profile.is_company', 'DESC')
            ->orderBy('posts.like_total', 'DESC')
            ->orderBy('profile.is_real', 'DESC')
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
            foreach ($check_id as $key => $value) {
                // dd($value, $posts[$value]);
                $posts->forget($value);
                // unset($posts[$value]);
            }
            $posts = $posts->values();
            // dd($posts);
        }

        for ($i = 0; $i < count($posts); $i++) {
            $profile = DB::table('profile')->where('user_id', '=', $posts[$i]->user_id)->first();
            $posts[$i]->user_profile = $profile;
        }

        return response([
            'status' => 200,
            'message' => 'recommed post',
            'data' => $posts
        ]);
    }


}
