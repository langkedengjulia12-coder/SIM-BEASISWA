<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

class RoutesTest extends TestCase
{
    public function test_public_routes_can_resolve_without_missing_controllers(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_homepage_uses_simk_branding(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('SIMK')
            ->assertDontSee('Fleet Management');
    }

    public function test_login_page_uses_simk_branding(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee('SIMK')
            ->assertDontSee('Fleet Management');
    }

    public function test_login_form_posts_to_authentication_route(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee('method="POST"', false)
            ->assertSee('action="' . route('login.store') . '"', false);
    }
}
