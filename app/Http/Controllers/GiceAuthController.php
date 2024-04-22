<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GiceAuthController extends Controller
{
    /**
     * Start the GICE login flow
     */
    public function login(): RedirectResponse
    {
        return Socialite::driver('gice')->redirect();
    }

    /**
     * Process the GICE OIDC callback request.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Get the user from GICE
        $giceUser = Socialite::driver('gice')->user();

        // Attempt to find the user in the database
        $user = User::updateOrCreate([
            'id' => $giceUser->id
        ], [
            'name' => $giceUser->name,
            'username' => $giceUser->username
        ]);

        // Login the user and regenerate the session
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
