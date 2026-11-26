<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin\Turnamen;
use App\Models\TurnamenPeserta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TurnamenController extends Controller
{
    /**
     * true  = pengajuan user berstatus "menunggu" sampai admin menyetujui
     * false = pengajuan langsung tampil di daftar turnamen
     */
    private const PERLU_PERSETUJUAN = true;

    private const TELEPON = '/^[0-9+\-\s]{8,20}$/';

    public function index(Request $request)
    {
        $user = $request->user();
        $tab = in_array($request->query('tab'), ['semua', 'ikut', 'saya'], true) ? $request->query('tab') : 'semua';
        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');

        $idIkut = TurnamenPeserta::where('id_user', $user->id)->pluck('id_turnamen')->all();

        $query = Turnamen::query()->with('penyelenggara:id,username')->withCount('peserta');

        if ($tab === 'saya') {
            $query->where('id_user', $user->id);
        } elseif ($tab === 'ikut') {
            $query->disetujui()->whereIn('id_turnamen', $idIkut);
        } else {
            $query->disetujui();
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_turnamen', 'like', "%{$q}%")
                    ->orWhere('lokasi', 'like', "%{$q}%");
            });
        }
        if ($kategori && in_array($kategori, Turnamen::KATEGORI, true)) {
            $query->where('kategori', $kategori);
        }

        // yang akan datang di atas (terdekat dulu), yang sudah lewat di bawah
        $turnamen = $query->orderByRaw('tgl_pelaksanaan < ?', [today()->toDateString()])
            ->orderBy('tgl_pelaksanaan')
            ->get();

        return view('turnamen_view', [
            'turnamen' => $turnamen,
            'tab' => $tab,
            'q' => $q,
            'kategori' => $kategori,
            'kategoriList' => Turnamen::KATEGORI,
            'idIkut' => $idIkut,
            'jumlah' => [
                'semua' => Turnamen::disetujui()->count(),
                'ikut' => count($idIkut),
                'saya' => Turnamen::where('id_user', $user->id)->count(),
            ],
            'perluPersetujuan' => self::PERLU_PERSETUJUAN,
        ]);
    }

    /** User mengajukan / menambahkan turnamen baru. */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_turnamen' => ['required', 'string', 'max:100'],
            'kategori' => ['required', Rule::in(Turnamen::KATEGORI)],
            'tgl_pelaksanaan' => ['required', 'date', 'after_or_equal:today'],
            'batas_pendaftaran' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:tgl_pelaksanaan'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'biaya_pendaftaran' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'kuota_tim' => ['required', 'integer', 'min:2', 'max:128'],
            'hadiah' => ['nullable', 'string', 'max:150'],
            'kontak' => ['required', 'regex:' . self::TELEPON],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang.',
            'tgl_pelaksanaan.after_or_equal' => 'Tanggal pelaksanaan tidak boleh sebelum hari ini.',
            'batas_pendaftaran.after_or_equal' => 'Batas pendaftaran tidak boleh sebelum hari ini.',
            'batas_pendaftaran.before_or_equal' => 'Batas pendaftaran harus sebelum atau sama dengan tanggal pelaksanaan.',
            'kontak.regex' => 'Nomor kontak tidak valid.',
            'kuota_tim.min' => 'Kuota minimal 2 tim.',
        ], [
            'nama_turnamen' => 'Nama turnamen',
            'kategori' => 'Kategori',
            'tgl_pelaksanaan' => 'Tanggal pelaksanaan',
            'biaya_pendaftaran' => 'Biaya pendaftaran',
            'kuota_tim' => 'Kuota tim',
            'kontak' => 'Kontak',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'ajukan')->withInput()->with('buka_ajukan', true);
        }

        $data = $validator->validated();
        $poster = $request->file('poster');
        unset($data['poster']);

        if ($poster) {
            $directory = public_path('uploads/turnamen');
            File::ensureDirectoryExists($directory);
            $filename = $poster->hashName();
            $poster->move($directory, $filename);
            $data['poster'] = 'uploads/turnamen/' . $filename;
        }

        try {
            Turnamen::create($data + [
                'id_turnamen' => $this->buatId(),
                'id_user' => $request->user()->id,
                'status' => self::PERLU_PERSETUJUAN ? 'menunggu' : 'disetujui',
            ]);
        } catch (QueryException $e) {
            if (isset($data['poster'])) {
                File::delete(public_path($data['poster']));
            }

            throw $e;
        }

        return redirect()->route('turnamen.index', ['tab' => 'saya'])->with(
            'tn_ok',
            self::PERLU_PERSETUJUAN
                ? 'Turnamen berhasil diajukan. Admin akan memeriksanya sebelum ditampilkan.'
                : 'Turnamen berhasil ditambahkan.'
        );
    }

    /** User mendaftarkan tim ke sebuah turnamen. */
    public function daftar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_tim' => ['required', 'string', 'max:100'],
            'kontak' => ['required', 'regex:' . self::TELEPON],
        ], [
            'nama_tim.required' => 'Nama tim wajib diisi.',
            'kontak.required' => 'Kontak wajib diisi.',
            'kontak.regex' => 'Nomor kontak tidak valid.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'daftar')->withInput()->with('buka_daftar', $id);
        }

        $user = $request->user();
        $data = $validator->validated();

        // dikunci supaya kuota tidak terlewati bila dua orang mendaftar bersamaan
        $hasil = DB::transaction(function () use ($id, $user, $data) {
            $t = Turnamen::whereKey($id)->lockForUpdate()->first();

            if (! $t || $t->status !== 'disetujui') {
                return ['tn_err', 'Turnamen tidak ditemukan.'];
            }
            if ($t->id_user == $user->id) {
                return ['tn_err', 'Kamu tidak bisa mendaftar di turnamen yang kamu ajukan sendiri.'];
            }
            if (TurnamenPeserta::where('id_turnamen', $id)->where('id_user', $user->id)->exists()) {
                return ['tn_err', 'Kamu sudah terdaftar di turnamen ini.'];
            }
            if ($t->fase() !== 'buka') {
                return ['tn_err', 'Pendaftaran turnamen ini sudah ditutup atau kuotanya penuh.'];
            }

            TurnamenPeserta::create([
                'id_turnamen' => $id,
                'id_user' => $user->id,
                'nama_tim' => $data['nama_tim'],
                'kontak' => $data['kontak'],
            ]);

            return ['tn_ok', 'Tim "' . $data['nama_tim'] . '" berhasil didaftarkan.'];
        });

        return back()->with($hasil[0], $hasil[1]);
    }

    public function batalDaftar(Request $request, string $id)
    {
        $t = Turnamen::whereKey($id)->first();

        if ($t && $t->fase() === 'selesai') {
            return back()->with('tn_err', 'Turnamen sudah selesai, pendaftaran tidak bisa dibatalkan.');
        }

        TurnamenPeserta::where('id_turnamen', $id)->where('id_user', $request->user()->id)->delete();

        return back()->with('tn_ok', 'Pendaftaran dibatalkan.');
    }

    /** Menghapus pengajuan milik sendiri yang belum disetujui. */
    public function destroy(Request $request, string $id)
    {
        $t = Turnamen::whereKey($id)
            ->where('id_user', $request->user()->id)
            ->whereIn('status', ['menunggu', 'ditolak'])
            ->first();

        if (! $t) {
            return back()->with('tn_err', 'Pengajuan tidak bisa dihapus.');
        }

        TurnamenPeserta::where('id_turnamen', $id)->delete();
        $t->delete();

        return back()->with('tn_ok', 'Pengajuan turnamen dihapus.');
    }

    private function buatId(): string
    {
        do {
            $id = 'TRN' . now()->format('ymd') . strtoupper(Str::random(5));
        } while (Turnamen::whereKey($id)->exists());

        return $id;
    }
}