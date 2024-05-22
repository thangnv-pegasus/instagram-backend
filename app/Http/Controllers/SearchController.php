<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {

            return response([
                'status' => 200,
                'message' => 'search ' . $request->value . ' successed',
                'result' => DB::table('users')
                    ->where('')
                    ->paginate(15)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => $e
            ]);
        }
    }
}
