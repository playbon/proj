<?php

namespace Tests\Feature;

use App\Enums\BuildStatus;
use App\Models\Build;
use App\Models\BuildReport;
use App\Models\Package;
use App\Models\PackageReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ── Build reports ─────────────────────────────────────────

    public function test_publisher_can_report_a_build(): void
    {
        $reporter = User::factory()->publisher()->create();
        $owner    = User::factory()->publisher()->create();
        $build    = Build::factory()->completed()->create(['user_id' => $owner->id]);

        $this->actingAs($reporter)
            ->post(route('builds.report', $build), ['reason' => 'Malicious content'])
            ->assertRedirect();

        $this->assertDatabaseHas('build_reports', [
            'build_id'    => $build->id,
            'reporter_id' => $reporter->id,
            'status'      => 'pending',
        ]);
    }

    public function test_user_cannot_report_same_build_twice(): void
    {
        $reporter = User::factory()->publisher()->create();
        $build    = Build::factory()->completed()->create();

        BuildReport::create([
            'build_id'    => $build->id,
            'reporter_id' => $reporter->id,
            'reason'      => 'First report',
            'status'      => 'pending',
        ]);

        $this->actingAs($reporter)
            ->post(route('builds.report', $build), ['reason' => 'Duplicate'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_ghost_cannot_report_a_build(): void
    {
        $ghost = User::factory()->ghost()->create();
        $build = Build::factory()->completed()->create();

        $this->actingAs($ghost)
            ->post(route('builds.report', $build), ['reason' => 'Bad build'])
            ->assertRedirect(route('ghost.pending'));
    }

    public function test_cannot_report_already_blocked_build(): void
    {
        $reporter = User::factory()->publisher()->create();
        $build    = Build::factory()->blocked()->create();

        $this->actingAs($reporter)
            ->post(route('builds.report', $build), ['reason' => 'Blocked already'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_cannot_report_creator_build(): void
    {
        $reporter = User::factory()->publisher()->create();
        $creator  = User::factory()->creator()->create();
        $build    = Build::factory()->completed()->create(['user_id' => $creator->id]);

        $this->actingAs($reporter)
            ->post(route('builds.report', $build), ['reason' => 'Trying to report creator'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_report_requires_reason(): void
    {
        $reporter = User::factory()->publisher()->create();
        $build    = Build::factory()->completed()->create();

        $this->actingAs($reporter)
            ->post(route('builds.report', $build), ['reason' => ''])
            ->assertSessionHasErrors('reason');
    }

    // ── Package reports ───────────────────────────────────────

    public function test_publisher_can_report_a_package(): void
    {
        $reporter = User::factory()->publisher()->create();
        $owner    = User::factory()->publisher()->create();
        $package  = Package::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($reporter)
            ->post(route('packages.report', $package), ['reason' => 'Spam package'])
            ->assertRedirect();

        $this->assertDatabaseHas('package_reports', [
            'package_id'  => $package->id,
            'reporter_id' => $reporter->id,
            'status'      => 'pending',
        ]);
    }

    public function test_user_cannot_report_same_package_twice(): void
    {
        $reporter = User::factory()->publisher()->create();
        $package  = Package::factory()->create();

        PackageReport::create([
            'package_id'  => $package->id,
            'reporter_id' => $reporter->id,
            'reason'      => 'First',
            'status'      => 'pending',
        ]);

        $this->actingAs($reporter)
            ->post(route('packages.report', $package), ['reason' => 'Duplicate'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_cannot_report_inactive_package(): void
    {
        $reporter = User::factory()->publisher()->create();
        $package  = Package::factory()->inactive()->create();

        $this->actingAs($reporter)
            ->post(route('packages.report', $package), ['reason' => 'Already inactive'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_cannot_report_creator_package(): void
    {
        $reporter = User::factory()->publisher()->create();
        $creator  = User::factory()->creator()->create();
        $package  = Package::factory()->create(['user_id' => $creator->id]);

        $this->actingAs($reporter)
            ->post(route('packages.report', $package), ['reason' => 'Creator package'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── Admin: accept / reject build report ───────────────────

    public function test_admin_can_accept_build_report(): void
    {
        $admin    = User::factory()->admin()->create();
        $owner    = User::factory()->publisher()->create();
        $build    = Build::factory()->completed()->create(['user_id' => $owner->id]);
        $report   = BuildReport::create([
            'build_id'    => $build->id,
            'reporter_id' => User::factory()->publisher()->create()->id,
            'reason'      => 'Bad',
            'status'      => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reports.accept', $report))
            ->assertRedirect();

        $this->assertEquals(BuildStatus::Blocked, $build->fresh()->status);
        $this->assertEquals('accepted', $report->fresh()->status);
        $this->assertTrue($owner->fresh()->isBanned());
    }

    public function test_admin_can_reject_build_report(): void
    {
        $admin  = User::factory()->admin()->create();
        $build  = Build::factory()->completed()->create();
        $report = BuildReport::create([
            'build_id'    => $build->id,
            'reporter_id' => User::factory()->publisher()->create()->id,
            'reason'      => 'Questionable',
            'status'      => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reports.reject', $report))
            ->assertRedirect();

        $this->assertEquals('rejected', $report->fresh()->status);
        $this->assertEquals(BuildStatus::Completed, $build->fresh()->status);
    }

    // ── Admin: accept / reject package report ─────────────────

    public function test_admin_can_accept_package_report(): void
    {
        $admin   = User::factory()->admin()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);
        $report  = PackageReport::create([
            'package_id'  => $package->id,
            'reporter_id' => User::factory()->publisher()->create()->id,
            'reason'      => 'Malware',
            'status'      => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.package-reports.accept', $report))
            ->assertRedirect();

        $this->assertFalse($package->fresh()->is_active);
        $this->assertEquals('accepted', $report->fresh()->status);
        $this->assertTrue($owner->fresh()->isBanned());
    }

    public function test_admin_can_reject_package_report(): void
    {
        $admin   = User::factory()->admin()->create();
        $package = Package::factory()->create();
        $report  = PackageReport::create([
            'package_id'  => $package->id,
            'reporter_id' => User::factory()->publisher()->create()->id,
            'reason'      => 'Questionable',
            'status'      => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.package-reports.reject', $report))
            ->assertRedirect();

        $this->assertEquals('rejected', $report->fresh()->status);
        $this->assertTrue($package->fresh()->is_active);
    }

    public function test_non_admin_cannot_access_report_management(): void
    {
        $publisher = User::factory()->publisher()->create();

        $this->actingAs($publisher)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }
}
