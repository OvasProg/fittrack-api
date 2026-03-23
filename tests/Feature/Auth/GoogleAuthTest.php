<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('the /google/url endpoint returns a valid Google OAuth redirect URL', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/google/url');

    $response->assertStatus(200)
        ->assertJsonStructure(['url']);

    expect($response->json('url'))->toContain('accounts.google.com');
});

test('it handles google callback and logs in the user', function () {
    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-id-123');
    $googleUser->shouldReceive('getEmail')->andReturn('jane@example.com');
    $googleUser->shouldReceive('getName')->andReturn('Jane Doe');

    Socialite::shouldReceive('driver->stateless->user')->andReturn($googleUser);

    /** @var \Tests\TestCase $this */
    $response = $this->withoutMiddleware()
        ->postJson('/google/callback', ['code' => 'fake-code']);

    $response->assertStatus(204);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'google_id' => 'google-id-123',
        'name' => 'Jane Doe',
    ]);

    $this->assertAuthenticatedAs(User::where('email', 'jane@example.com')->first());
});
