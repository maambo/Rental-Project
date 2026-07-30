<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_log_creates_audit_log_record(): void
    {
        $user = $this->makeTenant();
        $this->actingAs($user);

        AuditService::log('user.login', 'auth');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event'   => 'user.login',
            'module'  => 'auth',
        ]);
    }

    public function test_log_records_old_and_new_values(): void
    {
        $user = $this->makeTenant();
        $this->actingAs($user);

        AuditService::log(
            'property.updated',
            'property',
            'App\\Models\\Property',
            99,
            ['status' => 'pending'],
            ['status' => 'approved'],
        );

        $record = AuditLog::latest()->first();

        $this->assertEquals('pending', $record->old_values['status'] ?? null);
        $this->assertEquals('approved', $record->new_values['status'] ?? null);
        $this->assertEquals(99, $record->auditable_id);
    }

    public function test_log_works_without_authenticated_user(): void
    {
        AuditService::log('system.boot', 'system');

        $this->assertDatabaseHas('audit_logs', [
            'event'   => 'system.boot',
            'user_id' => null,
        ]);
    }
}
