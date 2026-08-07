<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $userRole): Response
    {
        $currentUser = $request->user();

        if (!$currentUser || $currentUser->role !== $userRole){
            return response()->json([
                'status'    => 'forbidden',
                'message'   => 'Anda tidak memiliki akses ke route ini.'
            ], 403);
        }

        return $next($request);
    }
}
