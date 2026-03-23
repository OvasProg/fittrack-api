<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
  public function getGoogleAuthUrl()
  {
    /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
    $driver = Socialite::driver('google');
    return response()->json([
      'url' => $driver->stateless()->redirect()->getTargetUrl(),
    ]);
  }

  public function handleGoogleCallback()
  {
    /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
    $driver = Socialite::driver('google');
    $googleUser = $driver->stateless()->user();

    $user = User::firstOrCreate(
      ['email' => $googleUser->getEmail()],
      [
        'name' => $googleUser->getName(),
        'google_id' => $googleUser->getId(),
        'password' => bcrypt(Str::random(24)),
      ]
    );

    // Log user into the stateful session
    Auth::login($user);

    return response()->noContent();
  }
}
