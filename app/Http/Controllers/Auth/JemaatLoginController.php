<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class JemaatLoginController extends Controller
{
    /**
     * Show the jemaat login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.jemaat-login');
    }

    /**
     * Handle a login request for jemaat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'fullname' => 'required',
            'dateofbirth' => 'required|date'
        ]);

        // Find the user with the given fullname and dateofbirth
        $user = User::where('fullname', $request->fullname)
                    ->where('dateofbirth', $request->dateofbirth)
                    ->where('role', 'jemaat')
                    ->first();

        if ($user) {
            // Log the user in
            Auth::login($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Nama atau tanggal lahir tidak cocok'
        ], 401);
    }
}
