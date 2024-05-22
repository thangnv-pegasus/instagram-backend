<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenWithCookieToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasCookie('cookie_user')) {
            // Nếu cookie tồn tại, sử dụng session để xác thực
            if (auth()->check()) {
                // Người dùng đã đăng nhập bằng cookie
                return $next($request);
            }
        }
        // Kiểm tra xem header Authorization có chứa token không
        if ($request->hasHeader('Authorization')) {
            $authorizationHeader = $request->header('Authorization');
            // Phân tích token từ header và thực hiện xác thực
            $token = substr($authorizationHeader, strlen('Bearer '));

            // Thực hiện xác thực bằng token
            if (auth()->onceUsingId($token)) {
                // Người dùng đã xác thực thành công bằng token
                return $next($request);

            }
        }
        return response()->json(['status' => 401, 'message' => 'unAuthentication', 'cookie' => $request->cookie('cookie_user'), 'header' => $request->header()]);
    }
}
