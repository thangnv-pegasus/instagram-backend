<?php

namespace App\Http\Controllers;

use App\Events\LikeSent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoryController extends Controller
{
    public function create(Request $request)
    {
        try {
            $story_id = DB::table('story')->insertGetId([
                'user_id' => auth()->user()->id,
                'content' => $request->content ?? null,
                'expired_at' => now()->addDay(1)
            ]);
            $image_id = DB::table('images')->insertGetId([
                'image_url' => $request->image_url,
                'image_id' => $request->image_id
            ]);
            DB::table('story_image')->insert([
                'story_id' => $story_id,
                'image_id' => $image_id
            ]);
            return response([
                'status' => 200,
                'message' => 'create story is successed',
                'data' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'create story is failed',
                'error' => $e
            ]);
        }
    }

    public function show(Request $request)
    {

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
    }

    public function like(Request $request)
    {
        try {

            DB::table('story_interact')->insert([
                'user_id' => auth()->user()->id,
                'story_id' => $request->story_id,
                'emotion_id' => $request->emotion_id
            ]);

            event(new LikeSent([
                'user_id' => auth()->user()->id,
                'story_id' => $request->story_id,
                'emotion_id' => $request->emotion_id
            ]));

            return response([
                'status' => 200,
                'message' => 'like story is successed',
                'data' => $request->all()
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'like story is failed',
                'error' => $e
            ]);
        }

    }

    public function getUserInteract(Request $request)
    {
        try {
            $users = DB::table('story_interact')
                ->join('story', 'story_interact.story_id', '=', 'story.id')
                ->join('emotion', 'emotion.id', '=', 'story_interact.emotion_id')
                ->join('users', 'users.id', '=', 'story_interact.user_id')
                ->select('story_interact.story_id', 'story_interact.user_id', 'emotion.id as emotion_id', 'emotion.name as emotion_name', 'users.account_name', 'users.fullname')
                ->where('story.user_id', '=', auth()->user()->id)
                ->get();
            for ($i = 0; $i < count($users); $i++) {
                $list_index = [];
                for ($j = $i + 1; $j < count($users); $j++) {
                    if ($users[$i]->story_id === $users[$j]->story_id && $users[$i]->user_id === $users[$j]->user_id) {
                        $list_index[] = $j;
                        if (is_array($users[$i]->emotion_id)) {
                            $users[$i]->emotion_id[] = [
                                'emotion_id' => $users[$j]->emotion_id,
                                'emotion_name' => $users[$j]->emotion_name
                            ];
                        } else {
                            $users[$i]->emotion_id = [
                                [
                                    'emotion_id' => $users[$i]->emotion_id,
                                    'emotion_name' => $users[$i]->emotion_name
                                ],
                                [
                                    'emotion_id' => $users[$j]->emotion_id,
                                    'emotion_name' => $users[$j]->emotion_name
                                ]
                            ];
                        }
                    }
                }
                // dd($users);
                foreach ($list_index as $key => $value) {
                    unset($users[$value]);
                }
            }

            return response([
                'status' => 200,
                'message' => 'show user is successed',
                'data' => $users,
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'show user is failed',
                'error' => $e
            ]);
        }
    }
}
