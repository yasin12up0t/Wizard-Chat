<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    /**
     * Delete the authenticated user's account.
     */
    public function destroy(): RedirectResponse
    {
        $user = Auth::user();

        // Delete the user account and all related data (be cautious here)
        $user->delete();

        // Invalidate the session and regenerate the CSRF token
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Redirect to homepage or any other page after account deletion
        return redirect('/');
    }
}
