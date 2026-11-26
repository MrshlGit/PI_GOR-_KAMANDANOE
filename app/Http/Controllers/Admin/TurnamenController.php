<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TurnamenFilterRequest;
use App\Http\Requests\Admin\UpdateTurnamenStatusRequest;
use App\Models\Admin\Turnamen;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TurnamenController extends Controller
{
    public function index(TurnamenFilterRequest $request): View
    {
        $status = $request->validated()['status'] ?? Turnamen::STATUS_PENDING;

        return view('admin.turnamen.index', [
            'turnamen' => Turnamen::query()
                ->with('penyelenggara:id,username')
                ->withCount('peserta')
                ->where('status', $status)
                ->orderByDesc('id_turnamen')
                ->paginate(12)
                ->withQueryString(),
            'status' => $status,
            'jumlah' => Turnamen::jumlahPerStatus(),
        ]);
    }

    public function updateStatus(UpdateTurnamenStatusRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $updated = Turnamen::query()
            ->whereKey($id)
            ->where('status', Turnamen::STATUS_PENDING)
            ->update(['status' => $validated['status']]);

        if (! $updated) {
            return back()->with('galat', 'Pengajuan tidak ditemukan atau sudah diproses.');
        }

        return back()->with('sukses', $validated['status'] === 'disetujui'
            ? 'Turnamen berhasil disetujui dan ditampilkan kepada pengguna.'
            : 'Pengajuan turnamen ditolak.');
    }
}
