<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisterFleetController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return back(303)->with('status', 'Fleet Registered');
    }
}
