<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (auth()->user() && auth()->user()->role_id == 3) {
            return route('login');
        }
        // return $request->expectsJson() ? null : route('login');
        if (!$request->expectsJson()) {
            return route('login'); // Redirects unauthenticated users to login page
        }
    }
}
