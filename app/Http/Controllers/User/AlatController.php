<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlatController extends Controller
{
    public function ambil(Request $request)
    {
        $keranjang = $request->session()->get('keranjang_alat', []);

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'id_alat' => ['required', 'integer', 'exists:alats,id_alat'],
                'jumlah' => ['required', 'integer', 'min:1'],
            ]);

            $alat = Alat::where('aktif', true)->findOrFail($data['id_alat']);
            $jumlahSaatIni = (int) ($keranjang[$alat->id_alat] ?? 0);

            if ($data['jumlah'] + $jumlahSaatIni > $alat->stok) {
                return back()
                    ->withErrors(['jumlah' => "Stok {$alat->nama} yang tersedia hanya {$alat->stok}."])
                    ->withInput();
            }

            $keranjang[$alat->id_alat] = $jumlahSaatIni + $data['jumlah'];
            $request->session()->put('keranjang_alat', $keranjang);

            return redirect()->route('alat.create')->with('success', "{$alat->nama} ditambahkan ke pesanan.");
        }

        $alats = Alat::where('aktif', true)->get();
        $pesanan = Alat::where('aktif', true)
            ->whereIn('id_alat', array_keys($keranjang))
            ->get()
            ->map(function (Alat $alat) use ($keranjang) {
                $alat->jumlah_pesan = (int) $keranjang[$alat->id_alat];
                $alat->subtotal = $alat->jumlah_pesan * $alat->harga_sewa;

                return $alat;
            });
        $totalPesanan = $pesanan->sum('subtotal');

        return view('alat_view', compact('alats', 'pesanan', 'totalPesanan'));
    }

    public function konfirmasi(Request $request)
    {
        $keranjang = $request->session()->get('keranjang_alat', []);

        if (empty($keranjang)) {
            return redirect()->route('alat.create')->with('orderResult', [
                'type' => 'error',
                'title' => 'Pesanan gagal',
                'message' => 'Belum ada alat di dalam pesanan.',
            ]);
        }

        try {
            $idPesanan = DB::transaction(function () use ($request, $keranjang) {
                $alats = Alat::where('aktif', true)
                    ->whereIn('id_alat', array_keys($keranjang))
                    ->lockForUpdate()
                    ->get();

                if ($alats->count() !== count($keranjang)) {
                    throw ValidationException::withMessages([
                        'pesanan' => 'Ada alat yang sudah tidak tersedia. Periksa kembali pesanan Anda.',
                    ]);
                }

                $total = 0;
                $detailPesanan = [];

                foreach ($alats as $alat) {
                    $jumlah = (int) ($keranjang[$alat->id_alat] ?? 0);

                    if ($jumlah < 1 || $jumlah > $alat->stok) {
                        throw ValidationException::withMessages([
                            'pesanan' => "Stok {$alat->nama} tidak mencukupi. Silakan periksa kembali pesanan Anda.",
                        ]);
                    }

                    $subtotal = round((float) $alat->harga_sewa * $jumlah, 2);
                    $total += $subtotal;
                    $detailPesanan[] = [
                        'id_alat' => $alat->id_alat,
                        'id_makanan' => null,
                        'nama_item' => $alat->nama,
                        'jumlah' => $jumlah,
                        'harga_satuan' => $alat->harga_sewa,
                        'subtotal' => $subtotal,
                    ];
                }

                foreach ($alats as $alat) {
                    $jumlah = (int) $keranjang[$alat->id_alat];
                    $stokDiperbarui = Alat::whereKey($alat->getKey())
                        ->where('stok', '>=', $jumlah)
                        ->decrement('stok', $jumlah);

                    if ($stokDiperbarui !== 1) {
                        throw ValidationException::withMessages([
                            'pesanan' => "Stok {$alat->nama} baru saja berubah. Silakan coba lagi.",
                        ]);
                    }
                }

                $idPesanan = DB::table('pesanans')->insertGetId([
                    'id_user' => $request->user()?->getAuthIdentifier(),
                    'status' => 'menunggu',
                    'status_pembayaran' => 'pending',
                    'subtotal' => $total,
                    'total' => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'id_pesanan');

                foreach ($detailPesanan as &$detail) {
                    $detail['id_pesanan'] = $idPesanan;
                }
                unset($detail);

                DB::table('detail_items')->insert($detailPesanan);

                return $idPesanan;
            });
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();

            return redirect()->route('alat.create')->with('orderResult', [
                'type' => 'error',
                'title' => 'Pesanan gagal',
                'message' => $message,
            ]);
        }

        $request->session()->forget('keranjang_alat');

        return redirect()->route('alat.create')->with('orderResult', [
            'type' => 'success',
            'title' => 'Pesanan berhasil',
            'message' => "Pesanan alat #{$idPesanan} berhasil dibuat.",
        ]);
    }
}
