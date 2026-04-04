@extends('layouts.public')

@section('title', 'Cari Alumni — Sistem Pelacakan Alumni')

@section('content')

{{-- HERO --}}
<div class="hero" style="padding-bottom: 20px;">
    <div style="font-size: 56px; margin-bottom: 16px; filter: drop-shadow(0 0 15px rgba(168, 85, 247, 0.4));">🔎</div>
    <h1 style="font-size: 42px; font-weight: 800; letter-spacing: -1.5px; background: linear-gradient(to right, #a855f7, #d8b4fe); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Cari Alumni
    </h1>
    <p style="font-size: 16px; color: var(--muted); max-width: 550px; margin: 12px auto 0; line-height: 1.6;">
        Cari data mahasiswa & alumni dari PDDIKTI secara real-time
    </p>
</div>

{{-- SEARCH FORM --}}
<div style="background: rgba(30, 34, 53, 0.6); backdrop-filter: blur(10px); border-radius: 20px; padding: 28px; border: 1px solid rgba(168, 85, 247, 0.1); margin-bottom: 30px;">
    <form method="GET" action="{{ route('search') }}" id="searchForm">
        <div style="background: #0f1117; border-radius: 14px; padding: 5px; display: flex; align-items: center; gap: 8px;">
            <input type="text" name="q" class="search-input" value="{{ $keyword }}"
                placeholder="Ketik nama, NIM, atau program studi..." autofocus autocomplete="off" required
                style="border: none; background: transparent; padding: 14px 22px; flex: 1;">
            <button type="submit" class="btn btn-primary" style="background: #6366f1; border-radius: 10px; padding: 12px 28px; font-weight: 700;">
                🔎 Cari
            </button>
            @if($keyword || $angkatan || $prodi)
                <a href="{{ route('search') }}" class="btn btn-outline" style="border: 1px solid var(--border); border-radius: 10px; padding: 12px 16px;">✕</a>
            @endif
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 20px; align-items: flex-end; padding: 0 5px;">
            <div class="filter-group">
                <div class="filter-label">Angkatan</div>
                <select name="angkatan" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Angkatan</option>
                    @foreach($angkatanList as $thn)
                        <option value="{{ $thn }}" {{ $angkatan == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Program Studi</div>
                <select name="prodi" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList as $p)
                        <option value="{{ $p }}" {{ $prodi == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Urutkan</div>
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="nama_asc" {{ $sort == 'nama_asc' ? 'selected' : '' }}>Nama A–Z</option>
                    <option value="nama_desc" {{ $sort == 'nama_desc' ? 'selected' : '' }}>Nama Z–A</option>
                </select>
            </div>
            <div class="filter-group" style="margin-left: auto;">
                <div class="filter-label" style="text-align: right;">Sumber</div>
                <div style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.25); padding: 7px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; color: #818cf8;">
                    🌐 PDDIKTI Real-Time
                </div>
            </div>
        </div>
    </form>
</div>

{{-- SEARCH RESULTS --}}
@if($keyword)
    @php $displayedCount = count($results); @endphp

    @if($pddiktiError)
        <div class="alert alert-warning" style="margin-top: 10px;">⚠️ {{ $pddiktiError }}</div>
    @endif

    @if($displayedCount === 0 && !$pddiktiError)
        <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 60px 20px; text-align: center; color: var(--muted);">
            <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
            <div style="font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px;">Tidak ada data ditemukan</div>
            <div style="font-size: 13px;">Coba ubah kata kunci atau filter pencarian Anda.</div>
        </div>
    @elseif($displayedCount > 0)
        <div style="margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 16px;">📊</span>
            <span style="font-size: 14px; font-weight: 800;">Hasil Pencarian ({{ $displayedCount }} data)</span>
        </div>
        
        <div style="background: rgba(30, 34, 53, 0.4); border-radius: 16px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.05);">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: rgba(15, 17, 23, 0.5); border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 30%;">Nama</th>
                            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 18%;">NIM</th>
                            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 22%;">Program Studi</th>
                            <th style="padding: 14px 16px; text-align: center; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 10%;">Angkatan</th>
                            <th style="padding: 14px 16px; text-align: center; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 10%;">Status</th>
                            <th style="padding: 14px 20px; text-align: right; width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $item)
                        <tr data-pddikti-id="{{ $item['id'] }}" style="border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 700; font-size: 13px; color: #f1f5f9; text-transform: uppercase; margin-bottom: 2px;">{{ $item['nama'] }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $item['pt'] }}</div>
                            </td>
                            <td style="padding: 16px 20px;">
                                <span style="color: #818cf8; font-family: 'JetBrains Mono', monospace; font-weight: 600; font-size: 12px; letter-spacing: 0.3px;">{{ $item['nim'] ?? '-' }}</span>
                            </td>
                            <td style="padding: 16px 20px;">
                                <div style="font-size: 12px; font-weight: 600; color: #e2e8f0;">{{ $item['prodi'] ?? '-' }}</div>
                            </td>
                            <td style="padding: 16px 16px; text-align: center;">
                                <span class="col-angkatan" style="font-weight: 700; color: #f1f5f9; font-size: 13px;">{{ $item['angkatan'] ?? '-' }}</span>
                            </td>
                            <td style="padding: 16px 16px; text-align: center;">
                                <span class="col-status-lulus" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #94a3b8; font-weight: 600;">
                                    <span class="loading-dot"></span>
                                </span>
                            </td>
                            <td style="padding: 16px 20px; text-align: right;">
                                @if($item['id'])
                                <a href="{{ route('search.detail', $item['id']) }}" style="text-decoration: none; color: #818cf8; font-size: 12px; font-weight: 700; padding: 7px 16px; border-radius: 8px; border: 1px solid rgba(129, 140, 248, 0.2); background: rgba(99, 102, 241, 0.05); white-space: nowrap;">
                                    Lihat Detail
                                </a>
                                @else
                                <span style="color: #64748b; font-size: 12px;">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@else
{{-- Landing state --}}
<div style="display: flex; gap: 24px; justify-content: center; margin-top: 60px; flex-wrap: wrap;">
    <div class="info-box">
        <div style="font-size: 28px; margin-bottom: 15px;">🌐</div>
        <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 5px;">PDDIKTI Real-Time</h4>
        <p style="font-size: 12px; color: var(--muted); line-height: 1.4;">Data langsung dari server Kemdikbud</p>
    </div>
    <div class="info-box">
        <div style="font-size: 28px; margin-bottom: 15px;">🎓</div>
        <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 5px;">Status Kelulusan</h4>
        <p style="font-size: 12px; color: var(--muted); line-height: 1.4;">Filter angkatan & tahun lulus</p>
    </div>
    <div class="info-box">
        <div style="font-size: 28px; margin-bottom: 15px;">📊</div>
        <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 5px;">Sorting & Filter</h4>
        <p style="font-size: 12px; color: var(--muted); line-height: 1.4;">Urutkan dan filter hasil pencarian</p>
    </div>
</div>
<style>
    .info-box {
        background: rgba(30, 34, 53, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px;
        padding: 30px 25px; text-align: left; width: 260px; transition: all 0.3s ease;
    }
    .info-box:hover { background: rgba(30, 34, 53, 0.8); border-color: rgba(168, 85, 247, 0.3); transform: translateY(-5px); }
</style>
@endif

<style>
    .loading-dot {
        display: inline-block; width: 10px; height: 10px; border-radius: 50%;
        border: 2px solid #475569; border-top-color: #818cf8;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('tr[data-pddikti-id]').forEach(row => {
        const id = row.getAttribute('data-pddikti-id');
        const colAngkatan = row.querySelector('.col-angkatan');
        const colStatus = row.querySelector('.col-status-lulus');
        if (!id) return;
        
        fetch(`/api/pddikti/detail/${encodeURIComponent(id)}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    if (colStatus) colStatus.innerHTML = '<span style="font-size:11px; color:#64748b;">-</span>';
                    return;
                }
                if (colAngkatan && data.angkatan) colAngkatan.textContent = data.angkatan;
                const isLulus = data.is_lulus;
                const txt = isLulus ? '🎓 Lulus' : (data.status || '-');
                const c = isLulus ? '#34d399' : '#94a3b8';
                const bg = isLulus ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.1)';
                if (colStatus) colStatus.innerHTML = `<span style="background:${bg};color:${c};padding:3px 10px;border-radius:6px;font-size:10px;font-weight:700;white-space:nowrap;">${txt}</span>`;
            })
            .catch(() => { if (colStatus) colStatus.innerHTML = '<span style="font-size:11px;color:#64748b;">-</span>'; });
    });
});
</script>
@endsection
