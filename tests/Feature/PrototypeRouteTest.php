<?php

namespace Tests\Feature;

use Tests\PrototypeTestCase as TestCase;

class PrototypeRouteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }
    public function test_prototype_route_is_accessible_in_testing_environment(): void
    {
        config(['prototype.enabled' => true]);

        $response = $this->get('/prototype');
        $response->assertStatus(200);
        $response->assertSee('PROTOTYPE');
        $response->assertSee('Progress New Model');
    }

    public function test_root_redirects_to_prototype_when_enabled(): void
    {
        config(['prototype.enabled' => true]);

        $response = $this->get('/');
        $response->assertRedirect(route('app'));
    }

    public function test_prototype_route_aborts_404_when_prototype_is_disabled(): void
    {
        config(['prototype.enabled' => false]);

        $response = $this->get('/prototype');
        $response->assertStatus(404);
    }

    public function test_prototype_route_aborts_404_in_production_environment(): void
    {
        $this->app['env'] = 'production';
        config(['prototype.enabled' => true]);

        $response = $this->get('/prototype');
        $response->assertStatus(404);
    }

    public function test_root_renders_welcome_page_when_prototype_disabled(): void
    {
        config(['prototype.enabled' => false]);

        $response = $this->get('/');
        $response->assertRedirect(route('app'));
    }
}
