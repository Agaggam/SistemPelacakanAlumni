@extends('layouts.app')

@section('title', 'Dashboard — Sistem Pelacakan Alumni')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Data real-time dari database lokal dan PDDIKTI')

@section('content')

{{-- ── STAT CARDS ────────────────────────────────────────────────────── --}}
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">

    <div class="stat-card total">
        <div class="stat-icon">👥</div>
        <div class="stat-value">{{ $total }}</div>
        <div class="stat-label">Total Alumni Tersimpan</div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon">🎓</div>
        <div class="stat-value" style="color:#34d399">{{ $sudahLulus }}</div>
        <div class="stat-label">Sudah Lulus / Teridentifikasi</div>
    </div>

    <div class="stat-card" style="background:var(--bg-card); border-color:rgba(99,102,241,0.3);">
        <div class="stat-icon">🌐</div>
        <div class="stat-value" style="color:#818cf8">{{ $dariPddikti }}</div>
        <div class="stat-label">Disimpan dari PDDIKTI</div>
    </div>

    <div class="stat-card" style="background:var(--bg-card); border-color:rgba(59,130,246,0.3);">
        <div class="stat-icon">✏️</div>
        <div class="stat-value" style="color:#60a5fa">{{ $diinputManual }}</div>
        <div class="stat-label">Diinput Manual Admin</div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">📅</div>
        <div class="stat-value" style="color:#fbbf24">{{ $sudahAdaTahunLulus }}</div>
        <div class="stat-label">Alumni Tercatat Tahun Lulus</div>
    </div>

</div>

{{-- ── ROW 2: DISTRIBUSI + AKSI CEPAT ─────────────────────────────────── --}}
<div class="grid-2" style="margin-bottom:20px;">

    {{-- Distribusi Angkatan --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📆 Distribusi Angkatan</div>
            <span class="chip">{{ $distribusiAngkatan->count() }} angkatan</span>
        </div>
        @if($distribusiAngkatan->isEmpty())
            <div style="color:var(--text-muted); font-size:13px; text-align:center; padding:20px 0;">
                Belum ada data angkatan tersimpan.
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
                🌐 Buka Halaman Pencarian Publik
            </a>
        </div>

        @if($tahunLulusTerbanyak)
        <div style="margin-top:16px; padding:12px 14px; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2); border-radius:10px; font-size:12px; color:var(--text-secondary);">
            📌 Tahun lulus terbanyak: <strong style="color:var(--accent-light);">{{ $tahunLulusTerbanyak }}</strong>
        </div>
        @endif
    </div>
</div>

{{-- ── ALUMNI TERBARU (tabel) ──────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">🕐 Alumni Terbaru Ditambahkan</div>
        <a href="{{ route('alumni.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>

    @if($recentAlumni->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
            <div style="font-size:36px; margin-bottom:12px;">📭</div>
            <div style="font-weight:600; color:var(--text-secondary); margin-bottom:8px;">Belum ada data alumni</div>
            <div style="font-size:13px;">Tambah alumni manual atau simpan dari hasil pencarian PDDIKTI.</div>
            <div style="margin-top:16px; display:flex; gap:10px; justify-content:center;">
                <a href="{{ route('pddikti.search') }}" class="btn btn-primary btn-sm">🔎 Cari di PDDIKTI</a>
                <a href="{{ route('alumni.create') }}" class="btn btn-outline btn-sm">➕ Tambah Manual</a>
            </div>
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
                                Ditambah {{ $alumni->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td><code style="color:var(--accent-light); font-size:12px;">{{ $alumni->nim }}</code></td>
                        <td style="font-size:13px;">{{ $alumni->prodi }}</td>
                        <td style="font-weight:600;">{{ $alumni->angkatan ?? '-' }}</td>
                        <td style="font-weight:600; color:{{ $alumni->tahun_lulus ? '#34d399' : 'var(--text-muted)' }}">
                            {{ $alumni->tahun_lulus ?? '–' }}
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

{{-- ── DISTRIBUSI PRODI ─────────────────────────────────────────────────── --}}
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

{{-- ── WIDGET: LIVE PDDIKTI SEARCH ───────────────────────────────────────── --}}
<div class="card" style="margin-top:20px; border-color:rgba(99,102,241,0.5); box-shadow:0 10px 30px rgba(99,102,241,0.1);">

    <div class="card-header">
        <div class="card-title">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="live-dot" style="width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block; box-shadow:0 0 8px #10b981;"></span>
                🌐 Pencarian Cepat PDDIKTI (Live API)
            </div>
        </div>
    </div>
    <div style="display:flex; gap:10px; margin-bottom:16px;">
        <input type="text" id="livePddiktiInput" class="form-control" placeholder="Ketik nama mahasiswa..." style="flex:1;">
        <button id="livePddiktiBtn" class="btn btn-primary" onclick="searchPddiktiLive()">Cari API</button>
    </div>
    <div id="livePddiktiLoading" style="display:none; text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
        ⏳ Menghubungi server Kemdikbud...
    </div>
    <div id="livePddiktiResults" style="display:grid; gap:10px;">
        {{-- Hasil AJAX akan muncul di sini --}}
        <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px; border:1px dashed var(--border); border-radius:12px;">
            Gunakan widget ini untuk mengecek ketersediaan data di PDDIKTI langsung dari dashboard.
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── 1. Auto Refresh Status Alumni via AJAX ──────────────────────────────
    const alumniList = @json($alumniDenganPddiktiId ?? []);
    
    if (alumniList.length > 0) {
        console.log(`Checking ${alumniList.length} alumni status via PDDIKTI API...`);
        // Ambil 3 sampel teratas untuk di-refresh background (agar tidak spamming API PDDIKTI)
        const sampleToCheck = alumniList.slice(0, 3);
        
        sampleToCheck.forEach(alumni => {
            fetch(`/dashboard/pddikti-status/${encodeURIComponent(alumni.pddikti_id)}`)
                .then(res => res.json())
                .then(data => {
                    if(!data.error) {
                        console.log(`[PDDIKTI Live Update] ${alumni.nama}: ${data.status}`);
                        // Di sini bisa ditambahkan logika DOM update kecil jika status alumni ditampilkan di layar
                    }
                })
                .catch(err => console.error(err));
        });
    }

    // ── 2. Live PDDIKTI Search Widget ───────────────────────────────────────
    function searchPddiktiLive() {
        const keyword = document.getElementById('livePddiktiInput').value.trim();
        if (!keyword) return;

        const btn = document.getElementById('livePddiktiBtn');
        const loading = document.getElementById('livePddiktiLoading');
        const container = document.getElementById('livePddiktiResults');

        btn.disabled = true;
        loading.style.display = 'block';
        container.innerHTML = '';

        fetch(`/dashboard/pddikti-search/${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                btn.disabled = false;

                if (data.error || data.results.length === 0) {
                    container.innerHTML = `<div style="text-align:center; padding:16px; color:var(--danger); background:rgba(239,68,68,0.1); border-radius:10px;">Pencarian tidak menemukan hasil atau terjadi kesalahan.</div>`;
                    return;
                }

                let html = '';
                data.results.forEach(item => {
                    const statusColor = item.is_lulus ? '#34d399' : '#94a3b8';
                    const link = item.id ? `<a href="/pddikti/${item.id}" target="_blank" class="btn btn-outline btn-sm" style="font-size:11px; padding:4px 8px;">Detail</a>` : '';
                    
                    html += `
                        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-weight:600; font-size:14px;">${item.nama}</div>
                                <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">
                                    ${item.nim} · ${item.pt} · ${item.prodi}
                                </div>
                                <div style="font-size:11px; margin-top:6px; color:${statusColor}; font-weight:600;">
                                    ● ${item.status || 'Status tidak diketahui'}
                                </div>
                            </div>
                            <div>${link}</div>
                        </div>
                    `;
                });
                
                if(data.total > 10) {
                    html += `<div style="text-align:center; font-size:12px; color:var(--text-muted); margin-top:8px;">Menampilkan 10 dari ${data.total} keseluruhan hasil. <a href="/pddikti?q=${encodeURIComponent(keyword)}" style="color:var(--accent-light);">Lihat semua</a></div>`;
                }
                
                container.innerHTML = html;
            })
            .catch(err => {
                loading.style.display = 'none';
                btn.disabled = false;
                container.innerHTML = `<div style="color:var(--danger);">Terjadi kesalahan koneksi AJAX.</div>`;
            });
    }

    // Trigger pencarian dengan Enter
    document.getElementById('livePddiktiInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchPddiktiLive();
    });
</script>
@endpush

