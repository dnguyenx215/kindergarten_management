<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleCheck
{
    /**
     * Xác định nếu người dùng có thuộc một trong các vai trò được chỉ định.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles  Danh sách các vai trò cho phép
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user || !in_array(strtolower($user->role), array_map('strtolower', $roles))) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        return $next($request);
    }
}
