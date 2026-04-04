<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniUmm extends Model
{
    protected $table = 'alumni_umm';

    protected $fillable = [
        'nama',
        'nim',
        'prodi',
        'fakultas',
        'tahun_masuk',
        'tanggal_lulus',
        'linkedin',
        'instagram',
        'facebook',
        'tiktok',
        'email',
        'no_hp',
        'tempat_kerja',
        'alamat_kerja',
        'posisi',
        'status_kerja',
        'sosmed_perusahaan',
    ];
}
