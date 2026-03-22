<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
  public function createCheckoutSession(Request $request)
  {
    $user = $request->user();
    $priceId = 'price_1TDm42Gfu8sQwcLAH219IDnM';

    // Generate the Stripe Checkout Session
    $checkout = $user->newSubscription('pro', $priceId)
      ->checkout([
        'success_url' => 'http://localhost:5500/dashboard?success=true',
        'cancel_url' => 'http://localhost:5500/dashboard?canceled=true',
      ]);

    // Return the URL so the frontend can redirect the user
    return response()->json(['url' => $checkout->url]);
  }
}
