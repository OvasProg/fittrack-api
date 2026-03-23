<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
  public function createCheckoutSession(Request $request)
  {
    $user = $request->user();
    $priceId = config('services.stripe.pro_price_id');

    $frontendUrl = config('app.frontend_url');

    $checkout = $user->newSubscription('pro', $priceId)
      ->checkout([
        'success_url' => $frontendUrl . '/dashboard.html?success=true',
        'cancel_url' => $frontendUrl . '/dashboard.html?canceled=true',
      ]);

    // @phpstan-ignore-next-line
    return response()->json(['url' => $checkout->url]);
  }
}
