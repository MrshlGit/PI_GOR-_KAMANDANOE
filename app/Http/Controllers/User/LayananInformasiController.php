<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Models\SaranKritik;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LayananInformasiController extends Controller
{
    // Jam operasional (ubah di sini kalau berbeda)
    private const JAM_BUKA = 8;
    private const JAM_TUTUP = 22;

    // Urutan prioritas status bila satu jam punya lebih dari satu booking
    private const RANK = ['kosong' => 0, 'menunggu' => 1, 'terisi' => 2, 'saya' => 3];

    public function index(Request $request)
    {
        $request->validate(['tanggal' => ['nullable', 'date']]);

        $tanggal = Carbon::parse($request->input('tanggal', today()))->startOfDay();
        $lapangans = Lapangan::orderBy('id_lapangan')->get();
        $idUser = $request->user()->getAuthIdentifier();

        // 1. Semua slot dimulai "kosong" (atau "maintenance" bila lapangan nonaktif)
        $jadwal = [];
        for ($jam = self::JAM_BUKA; $jam < self::JAM_TUTUP; $jam++) {
            foreach ($lapangans as $l) {
                $jadwal[$jam][$l->id_lapangan] =
                    strtolower((string) $l->status_lapangan) === 'nonaktif' ? 'maintenance' : 'kosong';
            }
        }

        // 2. Isi slot dari booking pada tanggal tersebut (yang batal diabaikan)
        $bookings = Booking::whereDate('tgl_main', $tanggal->toDateString())
            ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', ['batal', 'dibatalkan', 'cancelled'])
            ->get(['id_user', 'id_lapangan', 'jam_mulai', 'durasi', 'status_booking']);

        foreach ($bookings as $b) {
            $mulai = (int) substr((string) $b->jam_mulai, 0, 2);
            $status = $b->id_user == $idUser
                ? 'saya'
                : (strtolower((string) $b->status_booking) === 'menunggu' ? 'menunggu' : 'terisi');

            for ($jam = $mulai; $jam < $mulai + max(1, (int) $b->durasi); $jam++) {
                $lama = $jadwal[$jam][$b->id_lapangan] ?? null;
                if ($lama === null || $lama === 'maintenance') {
                    continue;
                }
                if (self::RANK[$status] > self::RANK[$lama]) {
                    $jadwal[$jam][$b->id_lapangan] = $status;
                }
            }
        }

        return view('layanan_informasi', [
            'tanggal' => $tanggal,
            'labelTanggal' => $tanggal->copy()->locale('id')->translatedFormat('l, j F Y'),
            'lapangans' => $lapangans,
            'jadwal' => $jadwal,
        ]);
    }

    public function kirimSaran(Request $request)
    {
        $data = $request->validate([
            'nama' => ['nullable', 'string', 'max:100'],
            'isi' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'isi.required' => 'Isi saran atau kritik wajib diisi.',
            'isi.min' => 'Isi saran atau kritik terlalu pendek.',
        ]);

        SaranKritik::create([
            'id_user' => $request->user()->getAuthIdentifier(),
            'nama' => $data['nama'] ?: $request->user()->username,
            'isi' => $data['isi'],
        ]);

        return redirect(route('layanan.index') . '#saran')
            ->with('sukses_saran', 'Terima kasih! Saran dan kritikmu sudah kami terima.');
    }
}