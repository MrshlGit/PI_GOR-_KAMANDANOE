<?php

namespace App\Http\Controllers;

use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Menampilkan halaman login
    public function index()
    {
        return view('login_view');
    }

    // Memproses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function cekDataUSer(Request $request)
    {

        $user = User::where('username', $username)->first();
        // $username = $request->input('username');
        // $password = $request->input('password');

        if($user){
            return response()->json($user);
        }

        return response()->json(['message' => 'User tidak ditemukan'], 404);

        // Cek apakah username dan password cocok dengan data di database
        // if (Auth::attempt(['username' => $username, 'password' => $password])) {
        //     // Jika cocok, redirect ke halaman dashboard atau halaman yang diinginkan
        //     return redirect()->intended('/dashboard');
        // } else {
        //     // Jika tidak cocok, redirect kembali ke halaman login dengan pesan error
        //     return redirect()->back()->withErrors(['login' => 'Username atau password salah.']);
        // }
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}