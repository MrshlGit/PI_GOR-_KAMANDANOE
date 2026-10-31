<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|min:4|unique:user,username',
            'noHp'     => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'email'    => 'required|email|unique:user,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'username.unique'    => 'Username sudah digunakan',
            'noHp.regex'         => 'Format nomor HP tidak valid (contoh: 082299998888)',
            'email.unique'       => 'Email sudah terdaftar',
            'password.min'       => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sama',
        ]);

        User::create([
            'username' => $data['username'],
            'noHp'     => $data['noHp'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'user',
        ]);

        return redirect('/')->with('success', 'Registrasi berhasil, silakan login');
    }
}