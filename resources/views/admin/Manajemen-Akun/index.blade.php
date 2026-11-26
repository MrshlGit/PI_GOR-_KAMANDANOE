@extends('layouts.admin')
@section('title', 'Manajemen Akun')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-akun.css') }}">
@endpush

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Manajemen Akun</h1>
        </div>
        <label class="search">
            <svg class="ic"><use href="#i-search"/></svg>
            <input id="account-search" type="text" placeholder="Cari pengguna..." aria-label="Cari pengguna">
        </label>
    </div>

    <section class="box account-table-panel">
        <div class="box-h">
            <h2>Daftar Pengguna Terdaftar</h2>
            <button type="button" class="btn kuning" data-modal="m-tambah">+ Tambah Akun</button>
        </div>

        <div class="account-table-toolbar" aria-label="Kontrol tabel akun">
            <details class="account-tool">
                <summary class="account-tool-trigger">
                    <svg class="ic" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h16M4 12h16M4 19h16"/><path d="M8 3v4M15 10v4M11 17v4"/></svg>
                    Fields
                </summary>
                <div class="account-tool-menu account-fields-menu">
                    <strong>Tampilkan kolom</strong>
                    <label><input type="checkbox" data-account-field="username" checked disabled> Username <small>Wajib</small></label>
                    <label><input type="checkbox" data-account-field="role" checked> Role</label>
                    <label><input type="checkbox" data-account-field="email" checked> Email</label>
                    <label><input type="checkbox" data-account-field="created" checked> Terdaftar</label>
                    <label><input type="checkbox" data-account-field="last-online" checked> Terakhir online</label>
                    <label><input type="checkbox" data-account-field="actions" checked disabled> Aksi <small>Wajib</small></label>
                </div>
            </details>

            <details class="account-tool">
                <summary class="account-tool-trigger">
                    <svg class="ic" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4"/></svg>
                    Filter
                </summary>
                <div class="account-tool-menu account-filter-menu">
                    <label>Role
                        <select data-account-role-filter>
                            <option value="">Semua role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </label>
                    <label>Aktivitas akun
                        <select data-account-online-filter>
                            <option value="">Semua akun</option>
                            <option value="online">Pernah online</option>
                            <option value="offline">Belum pernah online</option>
                        </select>
                    </label>
                </div>
            </details>

            <details class="account-tool">
                <summary class="account-tool-trigger">
                    <svg class="ic" aria-hidden="true" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13"/><path d="m3 7 2-2v6m-2 6 2-2v6"/></svg>
                    Sort
                </summary>
                <div class="account-tool-menu account-sort-menu">
                    <label>Urutkan berdasarkan
                        <select data-account-sort>
                            <option value="">Urutan awal</option>
                            <option value="username">Username</option>
                            <option value="role">Role</option>
                            <option value="email">Email</option>
                            <option value="created">Tanggal terdaftar</option>
                            <option value="last-online">Terakhir online</option>
                        </select>
                    </label>
                    <button class="account-sort-direction" type="button" data-account-sort-direction="asc" aria-label="Urutan menaik">Menaik (A–Z)</button>
                </div>
            </details>
        </div>
        <p class="account-table-summary" id="account-results-summary" aria-live="polite"></p>

        <div class="scroll">
            <table class="tbl akun" data-account-table>
                <thead>
                    <tr>
                        <th data-account-column="username">USERNAME</th>
                        <th data-account-column="role">ROLE</th>
                        <th data-account-column="email">EMAIL</th>
                        <th data-account-column="created">TERDAFTAR</th>
                        <th data-account-column="last-online">TERAKHIR ONLINE</th>
                        <th class="kanan" data-account-column="actions">AKSI</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    @forelse ($users as $u)
                        <tr data-account-row
                            data-role="{{ strtolower((string) $u->role) }}"
                            data-search="{{ $u->username }} {{ $u->role }} {{ $u->email }} {{ $u->created_at?->locale('id')->translatedFormat('d M Y') }} {{ $u->last_online_at?->locale('id')->diffForHumans() ?? 'Belum pernah' }}"
                            data-username="{{ $u->username }}"
                            data-email="{{ $u->email }}"
                            data-created="{{ $u->created_at?->timestamp ?? '' }}"
                            data-last-online="{{ $u->last_online_at?->timestamp ?? '' }}">
                            <td data-account-column="username">
                                <span class="avatar a{{ $u->id % 5 }}">{{ $u->inisial }}</span>
                                {{ $u->username ?? '-' }}
                            </td>
                            <td data-account-column="role"><span class="badge {{ $u->role }}">{{ ucfirst((string) $u->role) }}</span></td>
                            <td data-account-column="email">{{ $u->email ?: '—' }}</td>
                            <td data-account-column="created">{{ $u->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                            <td data-account-column="last-online">{{ $u->last_online_at?->locale('id')->diffForHumans() ?? 'Belum pernah' }}</td>
                            <td class="aksi" data-account-column="actions">
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
                        <tr data-account-empty><td colspan="6">Belum ada pengguna.</td></tr>
                    @endforelse
                    @if ($users->isNotEmpty())
                        <tr data-account-no-results hidden><td colspan="6">Tidak ada akun yang sesuai dengan pencarian atau filter.</td></tr>
                    @endif
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
                    <span class="password-field">
                        <input type="password" name="password" placeholder="Masukkan password" required>
                        <button type="button" class="password-toggle" data-toggle-password
                                aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="ic" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="ic" viewBox="0 0 24 24" aria-hidden="true" hidden>
                                <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7c1 0 2-.2 2.9-.5"/>
                            </svg>
                        </button>
                    </span>
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
                    <span class="password-field">
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">
                        <button type="button" class="password-toggle" data-toggle-password
                                aria-label="Tampilkan password baru" aria-pressed="false">
                            <svg class="ic" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="ic" viewBox="0 0 24 24" aria-hidden="true" hidden>
                                <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7c1 0 2-.2 2.9-.5"/>
                            </svg>
                        </button>
                    </span>
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

@push('scripts')
    <script src="{{ asset('js/admin-akun.js') }}" defer></script>
@endpush
