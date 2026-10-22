<?php

namespace Tests\Feature\Auth;

use App\Livewire\Prototype\ProgressForm;
use App\Models\Attachment;
use App\Models\CorrectiveAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\ViewException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_anonymous_cannot_access_data_file_or_direct_livewire_action(): void
    {
        foreach (['/app', '/prototype', '/attachments/'.Str::uuid().'/download'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        try {
            Livewire::test(ProgressForm::class);
            $this->fail('Anonymous Livewire access accepted');
        } catch (ViewException $e) {
            $cause = $e;
            while ($cause->getPrevious()) {
                $cause = $cause->getPrevious();
            }
            $this->assertInstanceOf(AuthenticationException::class, $cause);
        }
        $this->post('/register', [])->assertNotFound();
        $this->get('/register')->assertNotFound();
    }

    public function test_login_failure_success_logout_and_no_fixture_in_operational_route(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'password' => 'test-password-private']);
        $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $admin->email, 'password' => 'test-password-private'])->assertRedirect(route('app'));
        $this->assertAuthenticatedAs($admin);
        $this->get('/app')->assertOk()->assertDontSee('Ahmad Fauzi');
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/prototype')->assertRedirect(route('login'));
    }

    public function test_login_is_rate_limited(): void
    {
        $admin = User::factory()->create(['email' => 'limited@example.test']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $admin->email, 'password' => 'wrong']);
        }
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_command_creates_one_hashed_admin_and_refuses_second(): void
    {
        $this->artisan('admin:create', ['email' => 'local@example.test', '--name' => 'Admin Fiktif'])
            ->expectsQuestion('Password admin (minimal 12 karakter)', 'test-secret-1234')
            ->expectsQuestion('Ulangi password', 'test-secret-1234')->assertSuccessful();
        $admin = User::sole();
        $this->assertTrue(Hash::check('test-secret-1234', $admin->password));
        $this->artisan('admin:create', ['email' => 'second@example.test'])->assertFailed();
        $this->assertSame(1, User::count());
    }

    public function test_draft_and_file_policy_and_closed_update_guard(): void
    {
        $admin = User::factory()->create();
        $p = Progress::factory()->create(['status' => 'Closed']);
        $draft = ProgressDraft::factory()->create(['admin_id' => $admin->id]);
        $otherIdentity = new User;
        $otherIdentity->id = $admin->id + 1;
        $this->assertFalse(Gate::forUser($otherIdentity)->allows('view', $draft));
        $this->assertFalse(Gate::forUser($admin)->allows('update', $p));
        $problem = Problem::factory()->create(['progress_id' => $p->id]);
        $action = CorrectiveAction::factory()->create(['problem_id' => $problem->id]);
        $this->assertFalse(Gate::forUser($admin)->allows('update', $problem));
        $this->assertFalse(Gate::forUser($admin)->allows('update', $action));
        Storage::fake('private');
        $key = 'attachments/'.Str::uuid();
        Storage::disk('private')->put($key, 'private');
        $a = Attachment::create(['uploaded_by' => $admin->id, 'progress_id' => $p->id, 'owner_kind' => 'progress', 'state' => 'attached', 'kind' => 'document', 'storage_key' => $key, 'original_name' => 'private.txt', 'mime_type' => 'text/plain', 'size_bytes' => 7, 'checksum' => hash('sha256', 'private')]);
        $this->actingAs($admin)->get(route('attachments.download', $a))->assertDownload('private.txt');
        $this->assertFalse(Gate::forUser($admin)->allows('update', $a));
        $this->assertFalse(Gate::forUser($otherIdentity)->allows('download', $a));
        $a->update(['progress_id' => null, 'draft_id' => $draft->id, 'state' => 'staging']);
        $this->get(route('attachments.download', $a))->assertForbidden();
        config(['prototype.enabled' => false]);
        Livewire::test(ProgressForm::class)->assertNotFound();
    }

    public function test_mutating_login_request_requires_csrf_token(): void
    {
        // Enable the real CSRF check, normally bypassed by the testing environment.
        $this->app['env'] = 'local';
        $this->post('/login', ['email' => 'csrf@example.test', 'password' => 'invalid'])->assertStatus(419);
        $this->assertGuest();
    }

    public function test_livewire_rechecks_session_before_a_later_action(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $component = Livewire::test(ProgressForm::class);
        auth()->logout();
        try {
            $component->call('addProblem')->assertUnauthorized();
        } catch (AuthenticationException $e) {
            $this->assertSame('Unauthenticated.', $e->getMessage());
        } catch (ViewException $e) {
            $cause = $e;
            while ($cause->getPrevious()) {
                $cause = $cause->getPrevious();
            }
            $this->assertInstanceOf(AuthenticationException::class, $cause);
        }
    }
}
