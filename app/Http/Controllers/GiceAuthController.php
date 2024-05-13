<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class GiceAuthController extends Controller
{
    /**
     * Start the GICE login flow
     */
    public function login(): Response
    {
        $redirect = Socialite::driver('gice')->redirect();

        return Inertia::location($redirect);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): Response
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return Inertia::location('https://goonfleet.com/');
    }
}
