<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    use HasFactory;

    protected $table = 'alumni';

    protected $fillable = [
        'nim',
        'nama',
        'prodi',
        'fakultas',
        'angkatan',
        'tahun_lulus',
        'email',
        'no_hp',
        'domisili',
        'status',
        'skor_kecocokan',
        'data_pddikti',
        'last_tracked_at',
    ];

    protected $casts = [
        'data_pddikti' => 'array',
        'last_tracked_at' => 'datetime',
        'angkatan' => 'integer',
        'tahun_lulus' => 'integer',
        'skor_kecocokan' => 'float',
    ];

    public function trackingHistories()
    {
        return $this->hasMany(TrackingHistory::class);
    }

    public function scopeBelumDilacak($query)
    {
        return $query->where('status', 'Belum Dilacak');
    }

    public function scopePerluVerifikasi($query)
    {
        return $query->where('status', 'Perlu Verifikasi Manual');
    }

    public function scopePerluDiTracking($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'Belum Dilacak')
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('last_tracked_at')
                     ->where('last_tracked_at', '<', now()->subMonths(6));
              });
        });
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'Teridentifikasi dari PDDIKTI' => 'badge-success',
            'Perlu Verifikasi Manual' => 'badge-warning',
            'Belum Ditemukan' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getSkorPersenAttribute(): string
    {
        if ($this->skor_kecocokan === null) return '-';
        return round($this->skor_kecocokan * 100) . '%';
    }
}
