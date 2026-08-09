<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                // Google sign-up is the tenant path only; landlords apply via /landlord/apply.
                'role_id' => Role::where('name', 'tenant')->value('id'),
                // Password is required by default, so we set a random one for OAuth users if creating new
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                 'email_verified_at' => now(), // Auto verify email
            ]);

            Auth::login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google Login failed. Please try again.');
        }
    }
}
