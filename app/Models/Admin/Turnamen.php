<?php

namespace App\Models\Admin;

use App\Models\TurnamenPeserta;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turnamen extends Model
{
    public const KATEGORI = [
        'Tunggal Putra', 'Tunggal Putri', 'Ganda Putra', 'Ganda Putri', 'Ganda Campuran', 'Beregu',
    ];

    protected $table = 'turnamen';

    protected $primaryKey = 'id_turnamen';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_turnamen', 'id_user', 'nama_turnamen', 'kategori', 'tgl_pelaksanaan',
        'batas_pendaftaran', 'lokasi', 'biaya_pendaftaran', 'kuota_tim',
        'hadiah', 'kontak', 'deskripsi', 'poster', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pelaksanaan' => 'date',
            'batas_pendaftaran' => 'date',
            'biaya_pendaftaran' => 'float',
            'kuota_tim' => 'integer',
        ];
    }

    /** Turnamen mendatang yang sudah disetujui (dipakai dashboard admin). */
    public static function jumlahMendatang(): int
    {
        return static::where('status', 'disetujui')
            ->whereDate('tgl_pelaksanaan', '>=', today())
            ->count();
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(TurnamenPeserta::class, 'id_turnamen', 'id_turnamen');
    }

    public function penyelenggara(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->where('status', 'disetujui');
    }

    public function jumlahPeserta(): int
    {
        return (int) ($this->peserta_count ?? $this->peserta()->count());
    }

    /** buka | penuh | ditutup | selesai | menunggu | ditolak */
    public function fase(): string
    {
        if ($this->status !== 'disetujui') {
            return $this->status === 'ditolak' ? 'ditolak' : 'menunggu';
        }
        if ($this->tgl_pelaksanaan && $this->tgl_pelaksanaan->lt(today())) {
            return 'selesai';
        }
        if ($this->batas_pendaftaran && $this->batas_pendaftaran->lt(today())) {
            return 'ditutup';
        }
        if ($this->jumlahPeserta() >= (int) $this->kuota_tim) {
            return 'penuh';
        }

        return 'buka';
    }

    /** Selisih hari ke tanggal pelaksanaan (negatif bila sudah lewat). */
    public function sisaHari(): ?int
    {
        if (! $this->tgl_pelaksanaan) {
            return null;
        }

        return (int) today()->diffInDays($this->tgl_pelaksanaan->copy()->startOfDay(), false);
    }
}