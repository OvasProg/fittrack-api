<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Checkout;
use Tests\TestCase;

test('an unauthenticated user cannot create a checkout session', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/subscribe');

    $response->assertStatus(401);
});

test('it returns a checkout url for authenticated users', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    // 1. Create a REAL Stripe Session data object and set our fake URL
    $stripeSession = new \Stripe\Checkout\Session('cs_test_fake123');
    $stripeSession->url = 'https://checkout.stripe.com/fake-url';

    // 2. Create a REAL Cashier Checkout object using the fake Stripe session
    $checkout = new \Laravel\Cashier\Checkout($user, $stripeSession);

    // 3. Mock the subscription builder chain to return our real Checkout object
    $builder = Mockery::mock(\Laravel\Cashier\SubscriptionBuilder::class);
    $builder->shouldReceive('checkout')->once()->andReturn($checkout);

    /** @var \App\Models\User|\Mockery\MockInterface $mockUser */
    $mockUser = Mockery::mock($user)->makePartial();
    $mockUser->shouldReceive('newSubscription')
        ->with('pro', 'price_1TDm42Gfu8sQwcLAH219IDnM')
        ->once()
        ->andReturn($builder);

    \Laravel\Sanctum\Sanctum::actingAs($mockUser);

    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/subscribe');

    $response->assertStatus(200)
        ->assertJson(['url' => 'https://checkout.stripe.com/fake-url']);
});
