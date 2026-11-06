<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    public function test_allows_matching_role_and_blocks_others(): void
    {
        $middleware = new EnsureRole();
        $admin = new User(['role' => 'admin']);

        $request = Request::create('/admin/users', 'GET');
        $request->setUserResolver(fn () => $admin);

        $response = $middleware->handle($request, fn () => new Response('OK'), 'admin');
        $this->assertSame(200, $response->getStatusCode());

        $this->expectException(HttpException::class);
        $middleware->handle($request, fn () => new Response('OK'), 'member');
    }
}