<?php

namespace App\Models\Admin;

use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const PERIODE = ['hari', 'minggu', 'bulan', 'tahun'];

    public const STATUS = ['menunggu', 'dikonfirmasi', 'lunas', 'selesai', 'batal'];

    public const STATUS_DIBATALKAN = ['batal', 'dibatalkan', 'cancelled'];

    protected $table = 'booking';

    protected $primaryKey = 'id_booking';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_booking',
        'id_user',
        'id_lapangan',
        'nama_pemesan',
        'no_hp_pemesan',
        'tgl_main',
        'jam_mulai',
        'durasi',
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

    public function scopeTidakDibatalkan(Builder $query): Builder
    {
        return $query->whereRaw(
            'LOWER(status_booking) NOT IN (?, ?, ?)',
            self::STATUS_DIBATALKAN
        );
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->whereRaw('LOWER(status_booking) = ?', ['menunggu']);
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            if (empty($booking->id_booking)) {
                do {
                    $id = Str::upper(Str::random(20));
                } while (static::whereKey($id)->exists());

                $booking->id_booking = $id;
            }
        });
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
        return static::menunggu()->count();
    }

    public static function daftarAdmin(array $filter): Builder
    {
        $query = static::with(['user:id,username', 'lapangan:id_lapangan,nama_lapangan']);

        if (! empty($filter['q'])) {
            $term = $filter['q'];
            $query->where(function (Builder $bookings) use ($term): void {
                $bookings->where('id_booking', 'like', "%{$term}%")
                    ->orWhere('nama_pemesan', 'like', "%{$term}%")
                    ->orWhere('no_hp_pemesan', 'like', "%{$term}%")
                    ->orWhereHas('user', fn (Builder $users) => $users->where('username', 'like', "%{$term}%"));
            });
        }

        if (! empty($filter['status'])) {
            $query->whereRaw('LOWER(status_booking) = ?', [$filter['status']]);
        }

        if (! empty($filter['tanggal'])) {
            $query->whereDate('tgl_main', $filter['tanggal']);
        }

        if (! empty($filter['id_lapangan'])) {
            $query->where('id_lapangan', $filter['id_lapangan']);
        }

        return $query;
    }

    public static function statistikAdmin(): array
    {
        return [
            'total' => static::count(),
            'menunggu' => static::menunggu()->count(),
            'hari_ini' => static::whereDate('tgl_main', today())->count(),
            'pendapatan' => (float) static::tidakDibatalkan()
                ->whereMonth('tgl_main', now()->month)
                ->whereYear('tgl_main', now()->year)
                ->sum('total_bayar'),
        ];
    }

    public static function adaBentrok(
        string $idLapangan,
        string $tanggal,
        Carbon $mulai,
        Carbon $selesai
    ): bool {
        return static::query()
            ->where('id_lapangan', $idLapangan)
            ->whereDate('tgl_main', $tanggal)
            ->tidakDibatalkan()
            ->get(['jam_mulai', 'durasi'])
            ->contains(function (self $booking) use ($tanggal, $mulai, $selesai): bool {
                $mulaiBooking = Carbon::parse($tanggal.' '.$booking->jam_mulai);
                $selesaiBooking = $mulaiBooking->copy()->addHours(max(1, (int) $booking->durasi));

                return $mulai->lt($selesaiBooking) && $selesai->gt($mulaiBooking);
            });
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
                'label' => $t['label'],
                'judul' => $t['judul'],
                'jumlah' => $tanggal->filter(
                    fn ($tgl) => Carbon::parse($tgl)->between($t['mulai'], $t['akhir'])
                )->count(),
            ];
        }, $titik);

        return [
            'periode' => $periode,
            'keterangan' => [
                'hari' => '7 hari terakhir',
                'minggu' => '8 minggu terakhir',
                'bulan' => '12 bulan terakhir',
                'tahun' => '5 tahun terakhir',
            ][$periode],
            'total' => array_sum(array_column($data, 'jumlah')),
            'data' => $data,
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
                    'label' => $hari[$d->dayOfWeek].' '.$d->day,
                    'judul' => $d->day.' '.$bulan[$d->month].' '.$d->year,
                    'mulai' => $d->copy()->startOfDay(),
                    'akhir' => $d->copy()->endOfDay(),
                ];
            }
        } elseif ($periode === 'minggu') {
            for ($i = 7; $i >= 0; $i--) {
                $awal = $sekarang->copy()->startOfWeek()->subWeeks($i);
                $akhir = $awal->copy()->endOfWeek();
                $hasil[] = [
                    'label' => $awal->day.' '.$bulan[$awal->month],
                    'judul' => $awal->day.' '.$bulan[$awal->month].' - '.$akhir->day.' '.$bulan[$akhir->month].' '.$akhir->year,
                    'mulai' => $awal,
                    'akhir' => $akhir,
                ];
            }
        } elseif ($periode === 'bulan') {
            for ($i = 11; $i >= 0; $i--) {
                $awal = $sekarang->copy()->startOfMonth()->subMonths($i);
                $hasil[] = [
                    'label' => $bulan[$awal->month],
                    'judul' => $bulan[$awal->month].' '.$awal->year,
                    'mulai' => $awal,
                    'akhir' => $awal->copy()->endOfMonth(),
                ];
            }
        } else {
            for ($i = 4; $i >= 0; $i--) {
                $awal = $sekarang->copy()->startOfYear()->subYears($i);
                $hasil[] = [
                    'label' => (string) $awal->year,
                    'judul' => 'Tahun '.$awal->year,
                    'mulai' => $awal,
                    'akhir' => $awal->copy()->endOfYear(),
                ];
            }
        }

        return $hasil;
    }
}
