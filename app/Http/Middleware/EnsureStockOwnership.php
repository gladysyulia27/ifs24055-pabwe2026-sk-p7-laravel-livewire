<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStockOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $stock = $request->route('stock');
        if ($stock && (int) $stock->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        return $next($request);
    }
}