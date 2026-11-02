<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class AkunController extends Controller
{
    public function index()
    {
        return view('admin.akun.index', [
            'users'     => User::orderBy('id')->get(),
            'ringkasan' => User::ringkasan(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('admin.akun.index')
            ->with('sukses', 'Akun berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $akun): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($akun->is($request->user()) && $data['role'] !== 'admin') {
            return redirect()->route('admin.akun.index')
                ->with('galat', 'Anda tidak dapat menurunkan role akun Anda sendiri.');
        }

        $akun->update($data);

        return redirect()->route('admin.akun.index')
            ->with('sukses', 'Perubahan akun berhasil disimpan.');
    }

    public function destroy(User $akun): RedirectResponse
    {
        if ($akun->is(auth()->user())) {
            return redirect()->route('admin.akun.index')
                ->with('galat', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        try {
            $akun->delete();
        } catch (QueryException) {
            return redirect()->route('admin.akun.index')
                ->with('galat', 'Akun tidak dapat dihapus karena masih memiliki data terkait.');
        }

        return redirect()->route('admin.akun.index')
            ->with('sukses', 'Akun berhasil dihapus.');
    }
}
