<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminTurnamenRequest;
use App\Http\Requests\Admin\TurnamenFilterRequest;
use App\Http\Requests\Admin\UpdateTurnamenStatusRequest;
use App\Models\Admin\Turnamen;
use App\Services\TurnamenCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TurnamenController extends Controller
{
    public function index(TurnamenFilterRequest $request): View
    {
        $filters = $request->validated();
        $tab = $filters['tab'] ?? 'akan_datang';
        $search = trim($filters['q'] ?? '');

        $query = Turnamen::query()
            ->with('penyelenggara:id,username')
            ->withCount('peserta');

        if ($tab === 'berakhir') {
            $query->where('status', Turnamen::STATUS_APPROVED)
                ->whereDate('tgl_pelaksanaan', '<', today())
                ->whereDate('tgl_pelaksanaan', '>=', today()->subDays(30));
        } elseif ($tab === 'arsip') {
            $query->where(function ($events): void {
                $events->where('status', Turnamen::STATUS_REJECTED)
                    ->orWhere(function ($completed): void {
                        $completed->where('status', Turnamen::STATUS_APPROVED)
                            ->whereDate('tgl_pelaksanaan', '<', today()->subDays(30));
                    });
            });
        } else {
            $query->where(function ($events): void {
                $events->where('status', Turnamen::STATUS_PENDING)
                    ->orWhere(function ($published): void {
                        $published->where('status', Turnamen::STATUS_APPROVED)
                            ->whereDate('tgl_pelaksanaan', '>=', today());
                    });
            });
        }

        if ($search !== '') {
            $query->where(function ($events) use ($search): void {
                $events->where('nama_turnamen', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        return view('admin.turnamen.index', [
            'turnamen' => $query
                ->orderByDesc('id_turnamen')
                ->paginate(12)
                ->withQueryString(),
            'tab' => $tab,
            'search' => $search,
            'jumlah' => [
                'akan_datang' => Turnamen::where(function ($events): void {
                    $events->where('status', Turnamen::STATUS_PENDING)
                        ->orWhere(function ($published): void {
                            $published->where('status', Turnamen::STATUS_APPROVED)
                                ->whereDate('tgl_pelaksanaan', '>=', today());
                        });
                })->count(),
                'berakhir' => Turnamen::where('status', Turnamen::STATUS_APPROVED)
                    ->whereDate('tgl_pelaksanaan', '<', today())
                    ->whereDate('tgl_pelaksanaan', '>=', today()->subDays(30))
                    ->count(),
                'arsip' => Turnamen::where(function ($events): void {
                    $events->where('status', Turnamen::STATUS_REJECTED)
                        ->orWhere(function ($completed): void {
                            $completed->where('status', Turnamen::STATUS_APPROVED)
                                ->whereDate('tgl_pelaksanaan', '<', today()->subDays(30));
                        });
                })->count(),
            ],
        ]);
    }

    public function store(StoreAdminTurnamenRequest $request, TurnamenCreator $turnamenCreator): RedirectResponse
    {
        $turnamenCreator->create(
            $request->validated(),
            $request->user(),
            Turnamen::STATUS_APPROVED
        );

        return redirect()->route('admin.turnamen.index', ['tab' => 'akan_datang'])
            ->with('sukses', 'Event berhasil dibuat dan langsung dipublikasikan.');
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
