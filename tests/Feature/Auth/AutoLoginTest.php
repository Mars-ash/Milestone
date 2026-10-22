<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_login_authenticates_admin_when_enabled(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'admin_slot' => true]);
        config(['auth.auto_login' => true]);

        // Request ke root '/' otomatis login dan dialihkan ke /app
        $response = $this->get('/');
        $response->assertRedirect(route('app'));
        $this->assertAuthenticatedAs($admin);

        // Request langsung ke /app otomatis diautentikasi (200 OK)
        auth()->logout();
        $this->assertGuest();
        $response = $this->get('/app');
        $response->assertOk();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_auto_login_does_not_authenticate_when_disabled(): void
    {
        User::factory()->create(['email' => 'admin@example.test', 'admin_slot' => true]);
        config(['auth.auto_login' => false]);

        $response = $this->get('/app');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_manual_logout_stays_on_login_page_even_when_auto_login_enabled(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'admin_slot' => true]);
        config(['auth.auto_login' => true]);

        $this->actingAs($admin);
        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();

        // Mengikuti redirect ke /login tidak langsung auto-login kembali
        $loginPageResponse = $this->get('/login');
        $loginPageResponse->assertOk();
        $this->assertGuest();
    }
}
