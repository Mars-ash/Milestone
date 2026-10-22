<?php

namespace Tests\Feature\Import;

use App\Models\Progress;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportAccessCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_access_command_dry_run_does_not_modify_database(): void
    {
        $this->artisan('import:access', ['--dry-run' => true])
            ->expectsOutputToContain('DRY RUN')
            ->assertSuccessful();

        $this->assertSame(0, Project::count());
        $this->assertSame(0, Progress::count());
    }

    public function test_import_access_command_executes_successfully(): void
    {
        $this->artisan('import:access')
            ->expectsOutputToContain('Import completed successfully')
            ->assertSuccessful();

        $this->assertSame(51, Project::count());
        $this->assertSame(266, Progress::count());
    }
}
