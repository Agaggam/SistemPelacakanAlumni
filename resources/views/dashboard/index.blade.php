@extends('layouts.app')

@section('title', 'Dashboard — Sistem Pelacakan Alumni')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan data alumni dan statistik sistem')

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card total">
        <div class="stat-icon">👥</div>
        <div class="stat-value">{{ $total }}</div>
        <div class="stat-label">Total Alumni Tersimpan</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">🎓</div>
        <div class="stat-value" style="color:#34d399">{{ $sudahLulus }}</div>
        <div class="stat-label">Sudah Lulus</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border-color:rgba(99,102,241,0.3);">
        <div class="stat-icon">🌐</div>
        <div class="stat-value" style="color:#818cf8">{{ $dariPddikti }}</div>
        <div class="stat-label">Dari PDDIKTI</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border-color:rgba(59,130,246,0.3);">
        <div class="stat-icon">✏️</div>
        <div class="stat-value" style="color:#60a5fa">{{ $diinputManual }}</div>
        <div class="stat-label">Input Manual</div>
    </div>
</div>

{{-- ROW 2: DISTRIBUSI + AKSI CEPAT --}}
<div class="grid-2" style="margin-bottom:20px;">
    {{-- Distribusi Angkatan --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📆 Distribusi Angkatan</div>
            <span class="chip">{{ $distribusiAngkatan->count() }} angkatan</span>
        </div>
        @if($distribusiAngkatan->isEmpty())
            <div style="color:var(--text-muted); font-size:13px; text-align:center; padding:20px 0;">
                Tidak ada data angkatan.
            </div>
        @else
            @php $maxAngkatan = $distribusiAngkatan->max('total') ?: 1; @endphp
            @foreach($distribusiAngkatan as $item)
            <div style="margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px;">
                    <span style="font-weight:600;">Angkatan {{ $item->angkatan }}</span>
                    <span style="color:var(--accent-light);">{{ $item->total }} alumni</span>
                </div>
                <div class="score-bar">
                    <div class="score-fill" style="width:{{ ($item->total / $maxAngkatan) * 100 }}%; background:var(--accent);"></div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- Aksi Cepat --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">⚡ Aksi Cepat</div>
        </div>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="{{ route('pddikti.search') }}" class="btn btn-primary" style="justify-content:center;">
                🔎 Cari Alumni di PDDIKTI
            </a>
            <a href="{{ route('alumni.create') }}" class="btn btn-success" style="justify-content:center;">
                ➕ Tambah Alumni Manual
            </a>
            <a href="{{ route('alumni.index') }}" class="btn btn-secondary" style="justify-content:center;">
                👥 Lihat Semua Data Alumni
            </a>
            <a href="{{ route('search') }}" class="btn btn-outline" style="justify-content:center;" target="_blank">
                🌐 Halaman Pencarian Publik
            </a>
        </div>
    </div>
</div>

{{-- ALUMNI TERBARU --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">🕐 Alumni Terbaru</div>
        <a href="{{ route('alumni.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>

    @if($recentAlumni->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
            <div style="font-size:36px; margin-bottom:12px;">📭</div>
            <div style="font-weight:600; color:var(--text-secondary); margin-bottom:8px;">Belum ada data alumni</div>
            <div style="font-size:13px;">Tambah alumni manual atau simpan dari hasil pencarian PDDIKTI.</div>
        </div>
    @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Angkatan</th>
                        <th>Tahun Lulus</th>
                        <th>Sumber</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAlumni as $alumni)
                    @php
                        $isPddikti = !empty($alumni->data_pddikti) && ($alumni->data_pddikti['sumber'] ?? '') === 'PDDIKTI_REAL';
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $alumni->nama }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                {{ $alumni->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td><code style="color:var(--accent-light); font-size:12px;">{{ $alumni->nim ?? '-' }}</code></td>
                        <td style="font-size:13px;">{{ $alumni->prodi ?? '-' }}</td>
                        <td style="font-weight:600;">{{ $alumni->angkatan ?? '-' }}</td>
                        <td style="font-weight:600; color:{{ $alumni->tahun_lulus ? '#34d399' : 'var(--text-muted)' }}">
                            {{ $alumni->tahun_lulus ?? '-' }}
                        </td>
                        <td>
                            @if($isPddikti)
                                <span class="badge badge-success" style="font-size:10px;">🌐 PDDIKTI</span>
                            @else
                                <span class="badge badge-info" style="font-size:10px;">✏️ Manual</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('alumni.show', $alumni) }}" class="btn btn-outline btn-sm">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- DISTRIBUSI PRODI --}}
@if($distribusiProdi->isNotEmpty())
<div class="card">
    <div class="card-header">
        <div class="card-title">📚 Top Program Studi</div>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        @foreach($distribusiProdi as $item)
        <div style="background:var(--bg-hover); border:1px solid var(--border); border-radius:20px; padding:6px 14px; font-size:13px;">
            <span style="color:var(--text-primary); font-weight:600;">{{ $item->prodi }}</span>
            <span style="color:var(--accent-light); margin-left:6px;">{{ $item->total }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
