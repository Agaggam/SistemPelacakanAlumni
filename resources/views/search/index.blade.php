@extends('layouts.public')

@section('title', 'Cari Alumni — Sistem Pelacakan Alumni')

@section('content')

{{-- HERO --}}
<div class="hero">
    <h1>🔎 Cari Alumni</h1>
    <p>Cari data mahasiswa & alumni dari database kampus dan PDDIKTI secara real-time</p>
</div>

{{-- SEARCH FORM --}}
<div class="search-box">
    <form method="GET" action="{{ route('search') }}" id="searchForm">
        <div class="search-row">
            <input type="text" name="q" class="search-input" value="{{ $keyword }}"
                placeholder="Ketik nama, NIM, atau program studi..." autofocus autocomplete="off">
            <button type="submit" class="btn btn-primary">🔍 Cari</button>
            @if($keyword || $angkatan || $prodi)
                <a href="{{ route('search') }}" class="btn btn-outline">✕ Reset</a>
            @endif
        </div>

        @if($keyword || $angkatan || $prodi)
        <div class="filter-row">
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
                    <option value="nama_asc"       {{ $sort == 'nama_asc' ? 'selected' : '' }}>Nama A–Z</option>
                    <option value="nama_desc"      {{ $sort == 'nama_desc' ? 'selected' : '' }}>Nama Z–A</option>
                    <option value="angkatan_desc"  {{ $sort == 'angkatan_desc' ? 'selected' : '' }}>Angkatan Terbaru</option>
                    <option value="angkatan_asc"   {{ $sort == 'angkatan_asc' ? 'selected' : '' }}>Angkatan Terlama</option>
                </select>
            </div>
            <div class="filter-group ml-auto">
                <div class="filter-label">Sumber Data</div>
                <div style="display:flex; gap:6px;">
                    <a href="{{ request()->fullUrlWithQuery(['sumber' => 'semua']) }}"
                        class="source-tab {{ $sumber === 'semua' ? 'active' : '' }}">Semua</a>
                    <a href="{{ request()->fullUrlWithQuery(['sumber' => 'lokal']) }}"
                        class="source-tab {{ $sumber === 'lokal' ? 'active' : '' }}">📋 Lokal</a>
                    @if($keyword)
                    <a href="{{ request()->fullUrlWithQuery(['sumber' => 'pddikti']) }}"
                        class="source-tab {{ $sumber === 'pddikti' ? 'active' : '' }}">🌐 PDDIKTI</a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </form>
</div>

{{-- UNIFIED SEARCH RESULTS --}}
@if($keyword || $angkatan || $prodi)
    @php $displayedCount = $allResults ? count($allResults) : 0; @endphp
    @if($displayedCount === 0)
        <div class="results-card">
            <div class="empty">
                <div class="empty-icon">📭</div>
                <div class="empty-title">Tidak ada data ditemukan</div>
                <div class="empty-sub">Coba ubah kata kunci atau filter pencarian Anda.</div>
            </div>
        </div>
    @else
        <div class="section-header" style="margin-top:20px;">
            <div class="section-title">📊 Hasil Pencarian ({{ $displayedCount }} data)</div>
            @if(isset($pddiktiError) && $pddiktiError)
                <span class="badge badge-warning" style="font-size:11px;">⚠️ Sebagian data mungkin gagal dimuat dari PDDIKTI</span>
            @endif
        </div>
        
        <div class="results-card">
            <div class="table-wrap">
                <table>
                    <thead>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Program Studi</th>
                            <th>Angkatan</th>
                            <th>Status Kelulusan</th>
                            <th>Status Data</th>
                            <th></th>
                    </thead>
                    <tbody>
                        @foreach($allResults as $item)
                        <tr data-sumber="{{ $item['sumber'] }}" @if($item['sumber'] === 'pddikti') data-pddikti-id="{{ $item['id'] }}" @endif>
                            <td>
                                <div style="font-weight:600;">{{ $item['nama'] }}</div>
                                @if($item['sumber'] === 'pddikti' && isset($item['pt']))
                                    <div style="font-size:11px; color:var(--muted);">🏢 {{ $item['pt'] }}</div>
                                @elseif($item['sumber'] === 'lokal' && isset($item['model']) && $item['model']->domisili)
                                    <div style="font-size:11px; color:var(--muted);">📍 {{ $item['model']->domisili }}</div>
                                @endif
                            </td>
                            <td><code style="color:var(--accent-light);">{{ $item['nim'] }}</code></td>
                            <td>
                                <div>{{ $item['prodi'] }}</div>
                                @if($item['sumber'] === 'lokal' && isset($item['model']) && $item['model']->fakultas)
                                    <div style="font-size:11px; color:var(--muted);">{{ $item['model']->fakultas }}</div>
                                @elseif($item['sumber'] === 'pddikti' && isset($item['jenjang']) && $item['jenjang'] !== '-')
                                    <div style="font-size:11px; color:var(--muted);">{{ $item['jenjang'] }}</div>
                                @endif
                            </td>
                            <td class="col-angkatan">
                                @if($item['sumber'] === 'lokal')
                                    <span style="font-weight:600;">{{ $item['angkatan'] ?? '-' }}</span>
                                @else
                                    <span class="text-muted" style="font-size:11px;">🔄 Memuat...</span>
                                @endif
                            </td>
                            <td class="col-status-lulus">
                                @if($item['sumber'] === 'lokal')
                                    @php
                                        $isLulus = !empty($item['tahun_lulus']);
                                    @endphp
                                    <span class="badge {{ $isLulus ? 'badge-success' : 'badge-secondary' }}" style="font-size:11px;">
                                        {{ $isLulus ? '🎓 Lulus' : 'Belum Lulus' }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:11px;">🔄 Memuat...</span>
                                @endif
                            </td>
                            <td>
                                @if($item['sumber'] === 'lokal')
                                    @php
                                        $statusClass = match(true) {
                                            str_contains($item['status'], 'Teridentifikasi') => 'badge-success',
                                            str_contains($item['status'], 'Verifikasi')      => 'badge-warning',
                                            str_contains($item['status'], 'Ditemukan')       => 'badge-secondary',
                                            default                                           => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-local" style="margin-bottom:3px;">📋 Lokal</span><br>
                                    <span class="badge {{ $statusClass }}" style="font-size:10px;">{{ $item['status'] }}</span>
                                @else
                                    <span class="badge badge-pddikti" style="margin-bottom:3px;">🌐 PDDIKTI</span><br>
                                    @if(isset($item['status_mhs']) && $item['status_mhs'])
                                        <span class="badge {{ str_starts_with(strtolower($item['status_mhs']), 'lulus') ? 'badge-success' : 'badge-secondary' }}" style="font-size:10px;">
                                            {{ $item['status_mhs'] }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($item['sumber'] === 'lokal')
                                    <a href="{{ route('alumni.show', $item['id']) }}" class="btn btn-outline btn-sm">Lihat Detail</a>
                                @elseif($item['id'])
                                    <a href="{{ route('pddikti.detail', $item['id']) }}" class="btn btn-outline btn-sm"
                                        @guest onclick="return confirm('Login sebagai admin untuk simpan/track alumni dari PDDIKTI ke database.')" @endguest>
                                        Lihat Detail
                                    </a>
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
{{-- Landing state — sebelum search --}}
<div style="text-align:center; padding:20px 0 10px;">
    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-top:8px;">
        <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 20px; text-align:left; min-width:180px;">
            <div style="font-size:22px; margin-bottom:8px;">📋</div>
            <div style="font-weight:600; font-size:13px; margin-bottom:4px;">Data Alumni Lokal</div>
            <div style="color:var(--muted); font-size:12px;">Diinput & diverifikasi admin kampus</div>
        </div>
        <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 20px; text-align:left; min-width:180px;">
            <div style="font-size:22px; margin-bottom:8px;">🌐</div>
            <div style="font-weight:600; font-size:13px; margin-bottom:4px;">PDDIKTI Real-Time</div>
            <div style="color:var(--muted); font-size:12px;">Dari server Kemdikbud langsung</div>
        </div>
        <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 20px; text-align:left; min-width:180px;">
            <div style="font-size:22px; margin-bottom:8px;">🎓</div>
            <div style="font-weight:600; font-size:13px; margin-bottom:4px;">Status Kelulusan</div>
            <div style="color:var(--muted); font-size:12px;">Filter angkatan & tahun lulus</div>
        </div>
    </div>
</div>
@endif

{{-- DEMO MODAL --}}
<div id="demoModal" class="demo-modal" style="display:none;">
    <div class="demo-modal-content">
        <div class="demo-modal-icon">🚀</div>
        <h4 style="font-size: 22px;">Pengumuman</h4>
        <p style="font-size: 16px; line-height: 1.6;">Website ini masih dalam tahap <strong>Demo & Pengembangan</strong>.</p>
        <div style="margin-top: 15px;">
            <button id="closeDemoModal" class="btn btn-primary" style="padding: 12px 40px; font-size: 16px; border-radius: 12px; font-weight: 600;">Oke</button>
        </div>
    </div>
</div>

<style>
.demo-modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.demo-modal-content {
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 28px;
    padding: 50px 40px;
    text-align: center;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    animation: modalPop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.85) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.demo-modal-icon {
    font-size: 64px;
    margin-bottom: 20px;
}
.demo-modal h4 {
    margin: 0 0 15px;
    font-weight: 700;
}
.demo-modal p {
    color: var(--muted);
    margin-bottom: 30px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Demo Modal Logic
    const modal = document.getElementById('demoModal');
    const btn = document.getElementById('closeDemoModal');
    
    if (!sessionStorage.getItem('demo_notified')) {
        modal.style.display = 'flex';
    }
    
    btn.onclick = function() {
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            modal.style.display = 'none';
            sessionStorage.setItem('demo_notified', 'true');
        }, 300);
    }

    // Existing PDDIKTI Detail Fetch Logic
    const pddiktiRows = document.querySelectorAll('tr[data-pddikti-id]');
    
    pddiktiRows.forEach(row => {
        const id = row.getAttribute('data-pddikti-id');
        const colAngkatan = row.querySelector('.col-angkatan');
        const colStatusLulus = row.querySelector('.col-status-lulus');
        
        if (!id) return;
        
        fetch(`/api/pddikti/detail/${encodeURIComponent(id)}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    if (colAngkatan) colAngkatan.innerHTML = '<span class="text-danger" style="font-size:11px;">Gagal</span>';
                    if (colStatusLulus) colStatusLulus.innerHTML = '<span class="text-danger" style="font-size:11px;">Gagal</span>';
                    return;
                }
                
                if (colAngkatan) colAngkatan.innerHTML = `<span style="font-weight:600;">${data.angkatan || '-'}</span>`;
                
                const isLulus = data.is_lulus;
                const statusText = isLulus ? '🎓 Lulus' : 'Belum Lulus';
                const badgeClass = isLulus ? 'badge-success' : 'badge-secondary';
                
                if (colStatusLulus) {
                    colStatusLulus.innerHTML = `<span class="badge ${badgeClass}" style="font-size:11px;">${statusText}</span>`;
                }
                
                const statusDataCol = row.cells[5];
                if (data.status && statusDataCol) {
                    const statusVal = data.status.toLowerCase();
                    const subBadgeClass = statusVal.startsWith('lulus') ? 'badge-success' : 'badge-secondary';
                    
                    const existingSubBadge = statusDataCol.querySelector('.badge:not(.badge-pddikti)');
                    if (existingSubBadge) {
                        existingSubBadge.className = `badge ${subBadgeClass}`;
                        existingSubBadge.style.fontSize = '10px';
                        existingSubBadge.innerText = data.status;
                    } else {
                        statusDataCol.innerHTML += `<br><span class="badge ${subBadgeClass}" style="font-size:10px; margin-top:3px;">${data.status}</span>`;
                    }
                }
            })
            .catch(err => {
                console.error('PDDIKTI Detail Fetch Error:', err);
                if (colAngkatan) colAngkatan.innerHTML = '<span class="text-danger" style="font-size:11px;">Error</span>';
                if (colStatusLulus) colStatusLulus.innerHTML = '<span class="text-danger" style="font-size:11px;">Error</span>';
            });
    });
});
</script>
@if(isset($allResults) && count($allResults) > 0)
{{-- Removed duplicate script block script content moved above --}}
@endif
@endsection
