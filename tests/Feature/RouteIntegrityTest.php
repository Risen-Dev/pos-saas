<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteIntegrityTest extends TestCase
{
    public function test_application_controller_routes_have_public_actions(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (! str_starts_with($action, 'App\\Http\\Controllers\\')) {
                continue;
            }
            [$controller, $method] = array_pad(explode('@', $action, 2), 2, '__invoke');
            $this->assertTrue(method_exists($controller, $method), "Missing action: {$action}");
            $this->assertTrue((new \ReflectionMethod($controller, $method))->isPublic(), "Non-public action: {$action}");
        }
    }
}
