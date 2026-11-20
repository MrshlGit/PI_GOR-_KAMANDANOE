<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Makanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MakananController extends Controller
{
    public function ambil(Request $request)
    {
        $keranjang = $request->session()->get('keranjang_makanan', []);

        if ($request->query->has('id_makanan')) {
            $data = $request->validate([
                'id_makanan' => ['required', 'integer', 'exists:makanans,id_makanan'],
                'jumlah' => ['required', 'integer', 'min:1'],
            ]);

            $makanan = Makanan::where('aktif', true)->findOrFail($data['id_makanan']);
            $jumlahSaatIni = (int) ($keranjang[$makanan->id_makanan] ?? 0);

            if ($data['jumlah'] + $jumlahSaatIni > $makanan->stok) {
                return back()
                    ->withErrors(['jumlah' => "Stok {$makanan->nama} yang tersedia hanya {$makanan->stok}."])
                    ->withInput();
            }

            $keranjang[$makanan->id_makanan] = $jumlahSaatIni + $data['jumlah'];
            $request->session()->put('keranjang_makanan', $keranjang);

            return redirect()->route('makanan.create')->with('success', "{$makanan->nama} ditambahkan ke pesanan.");
        }

        $makanans = Makanan::where('aktif', true)->get();
        $pesanan = Makanan::where('aktif', true)
            ->whereIn('id_makanan', array_keys($keranjang))
            ->get()
            ->map(function (Makanan $makanan) use ($keranjang) {
                $makanan->jumlah_pesan = (int) $keranjang[$makanan->id_makanan];
                $makanan->subtotal = $makanan->jumlah_pesan * $makanan->harga_sewa;

                return $makanan;
            });
        $totalPesanan = $pesanan->sum('subtotal');

        return view('makanan_view', compact('makanans', 'pesanan', 'totalPesanan'));
    }

    public function konfirmasi(Request $request)
    {
        $keranjang = $request->session()->get('keranjang_makanan', []);

        if (empty($keranjang)) {
            return redirect()->route('makanan.create')->with('orderResult', [
                'type' => 'error',
                'title' => 'Pesanan gagal',
                'message' => 'Belum ada makanan di dalam pesanan.',
            ]);
        }

        try {
            $idPesanan = DB::transaction(function () use ($request, $keranjang) {
                $makanans = Makanan::where('aktif', true)
                    ->whereIn('id_makanan', array_keys($keranjang))
                    ->lockForUpdate()
                    ->get();

                if ($makanans->count() !== count($keranjang)) {
                    throw ValidationException::withMessages([
                        'pesanan' => 'Ada makanan yang sudah tidak tersedia. Periksa kembali pesanan Anda.',
                    ]);
                }

                $total = 0;
                $detailPesanan = [];

                foreach ($makanans as $makanan) {
                    $jumlah = (int) ($keranjang[$makanan->id_makanan] ?? 0);

                    if ($jumlah < 1 || $jumlah > $makanan->stok) {
                        throw ValidationException::withMessages([
                            'pesanan' => "Stok {$makanan->nama} tidak mencukupi. Silakan periksa kembali pesanan Anda.",
                        ]);
                    }

                    $subtotal = round((float) $makanan->harga_sewa * $jumlah, 2);
                    $total += $subtotal;
                    $detailPesanan[] = [
                        'id_alat' => null,
                        'id_makanan' => $makanan->id_makanan,
                        'nama_item' => $makanan->nama,
                        'jumlah' => $jumlah,
                        'harga_satuan' => $makanan->harga_sewa,
                        'subtotal' => $subtotal,
                    ];
                }

                foreach ($makanans as $makanan) {
                    $jumlah = (int) $keranjang[$makanan->id_makanan];
                    $stokDiperbarui = Makanan::whereKey($makanan->getKey())
                        ->where('stok', '>=', $jumlah)
                        ->decrement('stok', $jumlah);

                    if ($stokDiperbarui !== 1) {
                        throw ValidationException::withMessages([
                            'pesanan' => "Stok {$makanan->nama} baru saja berubah. Silakan coba lagi.",
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

            return redirect()->route('makanan.create')->with('orderResult', [
                'type' => 'error',
                'title' => 'Pesanan gagal',
                'message' => $message,
            ]);
        }

        $request->session()->forget('keranjang_makanan');

        return redirect()->route('makanan.create')->with('orderResult', [
            'type' => 'success',
            'title' => 'Pesanan berhasil',
            'message' => "Pesanan makanan #{$idPesanan} berhasil dibuat.",
        ]);
    }
}
