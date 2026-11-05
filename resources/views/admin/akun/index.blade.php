@extends('layouts.admin')
@section('title', 'Manajemen Akun')

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Manajemen Akun</h1>
        </div>
        <label class="search">
            <svg class="ic"><use href="#i-search"/></svg>
            <input id="cari" type="text" placeholder="Cari pengguna...">
        </label>
    </div>

    <section class="box">
        <div class="box-h">
            <h2>Daftar Pengguna Terdaftar</h2>
            <button type="button" class="btn kuning" data-modal="m-tambah">+ Tambah Akun</button>
        </div>

        <div class="scroll">
            <table class="tbl akun">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>USERNAME</th>
                        <th>ROLE</th>
                        <th class="kanan">AKSI</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    @forelse ($users as $u)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="avatar a{{ $u->id % 5 }}">{{ $u->inisial }}</span>
                                {{ $u->username ?? '-' }}
                            </td>
                            <td><span class="badge {{ $u->role }}">{{ ucfirst((string) $u->role) }}</span></td>
                            <td class="aksi">
                                <button type="button" class="btn"
                                        data-modal="m-edit"
                                        data-action="{{ route('admin.akun.update', $u) }}"
                                        data-id="{{ $u->id }}"
                                        data-username="{{ $u->username }}"
                                        data-role="{{ $u->role }}">Edit</button>
                                @unless ($u->is(auth()->user()))
                                    <button type="button" class="btn bahaya"
                                            data-modal="m-hapus"
                                            data-action="{{ route('admin.akun.destroy', $u) }}"
                                            data-username="{{ $u->username }}">Hapus</button>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div @class(['modal', 'open' => $errors->tambah->any()]) id="m-tambah">
        <div class="modal-box">
            <h3>+ Tambah User Baru</h3>
            <form method="POST" action="{{ route('admin.akun.store') }}">
                @csrf
                <label class="fld">Username
                    <input type="text" name="username" placeholder="Masukkan username"
                           value="{{ $errors->tambah->any() ? old('username') : '' }}" required>
                    @error('username', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Password
                    <input type="password" name="password" placeholder="Masukkan password" required>
                    @error('password', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Role
                    <select name="role">
                        <option value="user" @selected(! $errors->tambah->any() || old('role') !== 'admin')>User</option>
                        <option value="admin" @selected($errors->tambah->any() && old('role') === 'admin')>Admin</option>
                    </select>
                    @error('role', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <div class="modal-aksi">
                    <button type="button" class="btn" data-tutup>Batal</button>
                    <button type="submit" class="btn oren">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div @class(['modal', 'open' => $errors->edit->any()]) id="m-edit">
        <div class="modal-box">
            <h3>Edit User</h3>
            <form method="POST" action="{{ $errors->edit->any() ? route('admin.akun.update', old('_id')) : '' }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_id" value="{{ $errors->edit->any() ? old('_id') : '' }}" data-isi="id">
                <label class="fld">Username
                    <input type="text" name="username" data-isi="username"
                           value="{{ $errors->edit->any() ? old('username') : '' }}" required>
                    @error('username', 'edit') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Password Baru (opsional)
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">
                    @error('password', 'edit') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Role
                    <select name="role" data-isi="role">
                        <option value="user" @selected(! $errors->edit->any() || old('role') !== 'admin')>User</option>
                        <option value="admin" @selected($errors->edit->any() && old('role') === 'admin')>Admin</option>
                    </select>
                    @error('role', 'edit') <span class="err">{{ $message }}</span> @enderror
                </label>
                <div class="modal-aksi">
                    <button type="button" class="btn" data-tutup>Batal</button>
                    <button type="submit" class="btn oren">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal hapus" id="m-hapus">
        <div class="modal-box">
            <span class="warn"><svg class="ic"><use href="#i-warn"/></svg></span>
            <h3>Hapus User?</h3>
            <p class="muted">Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="target" data-isi="username"></div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-aksi">
                    <button type="button" class="btn" data-tutup>Batal</button>
                    <button type="submit" class="btn merah">Hapus</button>
                </div>
            </form>
        </div>
    </div>
@endsection
