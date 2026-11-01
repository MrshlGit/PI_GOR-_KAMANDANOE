<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('login_view');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user());
    }

    public function cekDataUser(Request $request)
    {
        $username = $request->input('username');
        $user = User::where('username', $username)->first();

        if ($user) {
            return response()->json($user);
        }

        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectByRole(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('dashboard');
        }

        return redirect()->route('user.dashboard');
    }
}