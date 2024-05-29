<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/user2', [AuthController::class, 'test'])->middleware(['auth:api', 'role']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

        Route::get('/profile', [UserController::class, 'show'])->middleware('auth:api');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

        Route::get('auth/google',[AuthController::class,'googlePage']);
        Route::get('/auth/google/callback',[AuthController::class,'loginGoogle']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/follow', [FollowController::class, 'follow']);
        Route::delete('/unfollow', [FollowController::class, 'unfollow']);
        Route::get('/my-friend', [UserController::class, 'myFriend']);
        Route::get('/follower', [UserController::class, 'follower']);

        Route::patch('/update-profile', [UserController::class, 'update']);
        Route::patch('/update-role', [UserController::class, 'updateRole']);

        Route::get('/home', [HomeController::class, 'show']);
        Route::get('posts-paginate',[HomeController::class,'postsHome']);
        Route::get('/story-paginate',[HomeController::class,'storyPaginate']);
    })->name('user');

    Route::middleware('auth:api')->group(function () {
        Route::post('/create-post', [PostController::class, 'create']);
        Route::get('/my-post', [PostController::class, 'showMyPost']);
        Route::post('/update-mypost', [PostController::class, 'updateMyPost']);
        Route::post('/like-post', [LikeController::class, 'like']);
        Route::post('/unlike-post', [LikeController::class, 'unlike']);
        Route::get('/recommend-post', [PostController::class, 'recommend']);
        Route::post('/new-comment', [CommentController::class, 'create']);
        Route::post('/new-child-comment', [CommentController::class, 'createChild']);
        Route::patch('/like-comment', [LikeController::class, 'like']);
        Route::get('/comments',[CommentController::class,'show']);
    })->name('post');

    Route::middleware('auth:api')->group(function () {
        Route::post('/new-story', [StoryController::class, 'create']);
        Route::get('/story', [StoryController::class, 'show']);

        Route::post('/like-story', [StoryController::class, 'like'])->middleware((['auth:api']));
        Route::get('/get-user-interact', [StoryController::class, 'getUserInteract']);
    })->name('story');

    Route::middleware('auth:api')->group(function () {
        Route::post('/new-room', [ChatController::class, 'newRoom']);
        Route::post('/new-single-room', [ChatController::class, 'newMessage']);
        Route::get('/get-my-room', [ChatController::class, 'showMyChat']);
        Route::post('/new-message', [ChatController::class, 'sendMessage']);
        Route::get('/get-message', [ChatController::class, 'getMessage']);
    })->name('chat');
});
