<?php

namespace Tests\Unit\Middleware;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureStockOwnershipMiddlewareTest extends TestCase
{
    public function test_allows_owner_and_aborts_non_owner(): void
    {
        $middleware = new EnsureStockOwnership();
        $owner = new User();
        $owner->id = 10;

        $stock = new Stock(['user_id' => 10]);

        $request = Request::create('/stocks/1', 'GET');
        $request->setUserResolver(fn () => $owner);
        $route = new Route('GET', '/stocks/{stock}', []);
        $route->bind($request);
        $route->setParameter('stock', $stock);
        $request->setRouteResolver(fn () => $route);

        $response = $middleware->handle($request, fn () => new Response('OK'));
        $this->assertSame(200, $response->getStatusCode());

        $otherStock = new Stock(['user_id' => 99]);
        $route->setParameter('stock', $otherStock);

        $this->expectException(HttpException::class);
        $middleware->handle($request, fn () => new Response('OK'));
    }
}