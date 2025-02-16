<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // التأكد من أن المستخدم مسجل دخوله كـ admin
        if (!Auth::guard('admin')->check()) {
            return redirect('/admin/login'); // اعادة توجيه للمسار الخاص بتسجيل الدخول
        }

        return $next($request);
    }
}
