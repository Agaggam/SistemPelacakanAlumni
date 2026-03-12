<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumni_id',
        'status_sebelum',
        'status_sesudah',
        'skor_kecocokan',
        'query_pencarian',
        'hasil_mentah',
        'catatan',
        'diverifikasi_oleh',
    ];

    protected $casts = [
        'skor_kecocokan' => 'float',
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }

    public function getSkorPersenAttribute(): string
    {
        if ($this->skor_kecocokan === null) return '-';
        return round($this->skor_kecocokan * 100) . '%';
    }
}
