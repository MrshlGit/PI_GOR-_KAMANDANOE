<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use App\Models\Admin\Lapangan;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Booking extends Model
{
    public const PERIODE = ['hari', 'minggu', 'bulan', 'tahun'];

    protected $table = 'booking';

    protected $primaryKey = 'id_booking';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_booking',
        'id_user',
        'id_lapangan',
        'tgl_main',
        'jam_mulai',
        'status_booking',
        'total_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tgl_main' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'id_lapangan', 'id_lapangan');
    }

    public static function terbaru(int $batas = 6): Collection
    {
        return static::with(['user:id,username', 'lapangan:id_lapangan,nama_lapangan'])
            ->orderByDesc('tgl_main')
            ->orderByDesc('jam_mulai')
            ->limit($batas)
            ->get();
    }

    public static function pendapatanBulanIni(): float
    {
        return (float) static::whereMonth('tgl_main', now()->month)
            ->whereYear('tgl_main', now()->year)
            ->sum('total_bayar');
    }

    public static function jumlahMenunggu(): int
    {
        return static::whereRaw('LOWER(status_booking) = ?', ['menunggu'])->count();
    }

    public static function grafik(string $periode): array
    {
        $periode = in_array($periode, self::PERIODE, true) ? $periode : 'hari';
        $titik = self::rentang($periode);

        $tanggal = static::whereBetween('tgl_main', [
            $titik[0]['mulai']->toDateString(),
            end($titik)['akhir']->toDateString(),
        ])->pluck('tgl_main');

        $data = array_map(function (array $t) use ($tanggal) {
            return [
                'label'  => $t['label'],
                'judul'  => $t['judul'],
                'jumlah' => $tanggal->filter(
                    fn ($tgl) => Carbon::parse($tgl)->between($t['mulai'], $t['akhir'])
                )->count(),
            ];
        }, $titik);

        return [
            'periode'    => $periode,
            'keterangan' => [
                'hari'   => '7 hari terakhir',
                'minggu' => '8 minggu terakhir',
                'bulan'  => '12 bulan terakhir',
                'tahun'  => '5 tahun terakhir',
            ][$periode],
            'total'      => array_sum(array_column($data, 'jumlah')),
            'data'       => $data,
        ];
    }

    private static function rentang(string $periode): array
    {
        $hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $sekarang = now();
        $hasil = [];

        if ($periode === 'hari') {
            for ($i = 6; $i >= 0; $i--) {
                $d = $sekarang->copy()->subDays($i);
                $hasil[] = [
                    'label' => $hari[$d->dayOfWeek] . ' ' . $d->day,
                    'judul' => $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year,
                    'mulai' => $d->copy()->startOfDay(),
                    'akhir' => $d->copy()->endOfDay(),
                ];
            }
        } elseif ($periode === 'minggu') {
            for ($i = 7; $i >= 0; $i--) {
                $awal = $sekarang->copy()->startOfWeek()->subWeeks($i);
                $akhir = $awal->copy()->endOfWeek();
                $hasil[] = [
                    'label' => $awal->day . ' ' . $bulan[$awal->month],
                    'judul' => $awal->day . ' ' . $bulan[$awal->month] . ' - ' . $akhir->day . ' ' . $bulan[$akhir->month] . ' ' . $akhir->year,
                    'mulai' => $awal,
                    'akhir' => $akhir,
                ];
            }
        } elseif ($periode === 'bulan') {
            for ($i = 11; $i >= 0; $i--) {
                $awal = $sekarang->copy()->startOfMonth()->subMonths($i);
                $hasil[] = [
                    'label' => $bulan[$awal->month],
                    'judul' => $bulan[$awal->month] . ' ' . $awal->year,
                    'mulai' => $awal,
                    'akhir' => $awal->copy()->endOfMonth(),
                ];
            }
        } else {
            for ($i = 4; $i >= 0; $i--) {
                $awal = $sekarang->copy()->startOfYear()->subYears($i);
                $hasil[] = [
                    'label' => (string) $awal->year,
                    'judul' => 'Tahun ' . $awal->year,
                    'mulai' => $awal,
                    'akhir' => $awal->copy()->endOfYear(),
                ];
            }
        }

        return $hasil;
    }
}
