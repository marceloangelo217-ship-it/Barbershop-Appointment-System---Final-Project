<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('user');
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            return redirect()->route('login')->with('error', 'Administrators only.');
        }
        return $next($request);
    }
}
