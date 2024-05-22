<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {

        try {
            event(new MessageSent($request->content));
            DB::table('messages')->insert([
                'user_id' => auth()->user()->id,
                'room_id' => $request->room_id,
                'content' => $request->content
            ]);

            return response([
                'status' => 200,
                'message' => 'sent message is successed'
            ]);
        } catch (Exception $e) {

            return response([
                'status' => 200,
                'message' => 'sent message is failed'
            ]);
        }
    }

    public function getMessage(Request $request)
    {
        try {
            $data = DB::table('messages')
                ->join('rooms', 'rooms.id', '=', 'messages.room_id')
                ->where('rooms.id', '=', $request->room_id)
                ->where('messages.user_id', '=', auth()->user()->id)
                ->select('messages.user_id', 'messages.room_id', 'messages.content', 'rooms.way_message', 'rooms.type', 'messages.created_at', 'messages.updated_at')
                ->get();
            return response([
                'status' => 200,
                'message' => 'get message in room is successed',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => 'get message in room is failed',
                'error' => $e
            ]);
        }
    }

    public function newRoom(Request $request)
    {

        try {
            $room_id = DB::table('rooms')->insertGetId([
                'way_message' => $request->way_message,
                'type' => "group"
            ]);

            DB::table('room_user')->insert([
                'user_id' => auth()->user()->id,
                'room_id' => $room_id,
                'role' => 'host'
            ]);

            foreach ($request->members as $key => $value) {
                DB::table('room_user')->insert([
                    'user_id' => $value['user_id'],
                    'room_id' => $room_id
                ]);
            }

            return response([
                'status' => 200,
                'message' => 'create new room is successed'
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => "create new room is failed",
                'error' => $e,
                'user' => auth()->user()->id
            ]);
        }


    }

    public function newMessage(Request $request)
    {
        try {

            $id1 = DB::table('members')->where('user_id', '=', $request->user_id)->first()->id;
            $id2 = DB::table('members')->where('user_id', '=', auth()->user()->id)->first()->id;
            $room_id1 = DB::table('join')->where('member_id', '=', $id1)->first()->room_id;
            $room_id2 = DB::table('join')->where('member_id', '=', $id2)->first()->room_id;

            if ($room_id1 === $room_id2) {
                return response([
                    'status' => 201,
                    'message' => 'create new chat single is failed'
                ]);
            } else {
                $member_id1 = DB::table('members')->insertGetId([
                    'user_id' => auth()->user()->id,
                    'role' => 'member'
                ]);

                $member_id2 = DB::table('members')->insertGetId([
                    'user_id' => $request->user_id,
                    'role' => 'member'
                ]);

                $room_id = DB::table('rooms')->insertGetId([
                    'way_message' => 2,
                    'type' => 'single',
                ]);

                DB::table('join')->insert([
                    [
                        'room_id' => $room_id,
                        'member_id' => $member_id1
                    ],
                    [
                        'room_id' => $room_id,
                        'member_id' => $member_id2
                    ]
                ]);

                return response([
                    'status' => 200,
                    'message' => 'create new room is successed'
                ]);
            }


        } catch (Exception $e) {
            return response([
                'status' => 200,
                'message' => 'create new message is failed',
                'error' => $e
            ]);
        }

    }

    public function showMyChat(Request $request)
    {

        $user_id = auth()->user()->id;

        $data = DB::table('rooms')
            ->join('room_user', 'rooms.id', '=', 'room_user.room_id')
            ->join('users', 'room_user.user_id', '=', 'users.id')
            ->where('users.id', '=', $user_id)
            ->get();

        return response([
            'status' => 200,
            'message' => 'get my room is successed',
            'data' => $data
        ]);
    }
}
