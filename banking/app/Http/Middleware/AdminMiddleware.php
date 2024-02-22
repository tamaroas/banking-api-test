<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
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

        $apiKey = $request->bearerToken(); // header Authorization

        if (!$apiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = User::where('remember_token', $apiKey)->first(); // Recherche l'utilisateur associé à la clé d'API

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

            return $next($request);

    }
}
