<?php

namespace Tests\Feature;

use App\Http\Middleware\RedirectAdminToDashboard;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AdminRouteIsolationTest extends TestCase
{
    public function test_admin_is_redirected_from_public_pages_to_admin_dashboard(): void
    {
        foreach (['/', '/classes', '/teachers', '/account'] as $path) {
            $response = $this->runMiddleware($path, 'admin');

            $this->assertSame(302, $response->getStatusCode());
            $this->assertSame(route('admin.dashboard'), $response->headers->get('Location'));
        }
    }

    public function test_admin_can_still_access_admin_routes(): void
    {
        $response = $this->runMiddleware('/admin/dashboard', 'admin');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('request continued', $response->getContent());
    }

    public function test_customer_is_not_redirected_to_admin_dashboard(): void
    {
        $response = $this->runMiddleware('/classes', 'customer');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('request continued', $response->getContent());
    }

    private function runMiddleware(string $path, string $role): Response
    {
        $request = Request::create($path);
        $user = new User();
        $user->role = $role;
        $request->setUserResolver(fn () => $user);

        return (new RedirectAdminToDashboard())->handle(
            $request,
            fn () => new Response('request continued'),
        );
    }
}
