<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

test('FREE role users are forbidden from accessing pro and admin endpoints', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create(['role' => UserRole::FREE]);

    $this->actingAs($user)
        ->getJson('/api/analytics/charts/muscle-distribution')
        ->assertStatus(403);

    $this->actingAs($user)
        ->getJson('/api/admin/dashboard')
        ->assertStatus(403);
});

test('PRO role users can access pro features but not admin dashboards', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create(['role' => UserRole::PRO]);

    // Mock the service to avoid database issues during the test
    $this->mock(AnalyticsService::class, function ($mock) use ($user) {
        $mock->shouldReceive('getMuscleDistribution')->once()->with($user)->andReturn(collect(['Chest' => 10]));
    });

    $this->actingAs($user)
        ->getJson('/api/analytics/charts/muscle-distribution')
        ->assertStatus(200);

    $this->actingAs($user)
        ->getJson('/api/admin/dashboard')
        ->assertStatus(403);
});

test('ADMIN users can access admin dashboards', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create(['role' => UserRole::ADMIN]);

    // Mock the service to avoid database issues during the test
    $this->mock(AdminDashboardService::class, function ($mock) {
        $mock->shouldReceive('getMetrics')->once()->andReturn(['users' => 100]);
    });

    $this->actingAs($user)
        ->getJson('/api/admin/dashboard')
        ->assertStatus(200);
});
