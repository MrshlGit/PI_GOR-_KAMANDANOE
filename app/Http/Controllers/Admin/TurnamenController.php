<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Turnamen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TurnamenController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'menunggu');
        if (! in_array($status, ['menunggu', 'disetujui', 'ditolak'], true)) {
            $status = 'menunggu';
        }

        return view('admin.turnamen.index', [
            'turnamen' => Turnamen::query()
                ->with('penyelenggara:id,username')
                ->withCount('peserta')
                ->where('status', $status)
                ->orderByDesc('id_turnamen')
                ->paginate(12)
                ->withQueryString(),
            'status' => $status,
            'jumlah' => [
                'menunggu' => Turnamen::where('status', 'menunggu')->count(),
                'disetujui' => Turnamen::where('status', 'disetujui')->count(),
                'ditolak' => Turnamen::where('status', 'ditolak')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
        ]);

        $updated = Turnamen::query()
            ->whereKey($id)
            ->where('status', 'menunggu')
            ->update(['status' => $validated['status']]);

        if (! $updated) {
            return back()->with('galat', 'Pengajuan tidak ditemukan atau sudah diproses.');
        }

        return back()->with('sukses', $validated['status'] === 'disetujui'
            ? 'Turnamen berhasil disetujui dan ditampilkan kepada pengguna.'
            : 'Pengajuan turnamen ditolak.');
    }
}
