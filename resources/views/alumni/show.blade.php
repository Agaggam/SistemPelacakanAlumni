@extends('layouts.app')

@section('title', $alumni->nama . ' — Detail Alumni')
@section('page-title', $alumni->nama)
@section('page-subtitle', 'NIM: ' . $alumni->nim . ' | ' . $alumni->prodi)

@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('search') }}" class="btn btn-secondary btn-sm">← Kembali</a>
    
    @if(Auth::check() && Auth::user()->isAdmin())
        <a href="{{ route('alumni.edit', $alumni) }}" class="btn btn-outline btn-sm">✏️ Edit</a>
        <form action="{{ route('tracking.single', $alumni) }}" method="POST" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">🔍 Run Tracking Sekarang</button>
        </form>
        @if($alumni->status === 'Perlu Verifikasi Manual')
            <button onclick="document.getElementById('verifyModal').classList.add('open')" class="btn btn-warning btn-sm">
                ✅ Verifikasi Manual
            </button>
        @endif
    @endif
</div>

<div class="grid-2">
    <!-- PROFIL ALUMNI -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">👤 Profil Alumni</div>
            <span class="badge {{ $alumni->status_badge_class }}">{{ $alumni->status }}</span>
        </div>
        <div style="display:flex; flex-direction:column; gap:14px;">
            @php
                $fields = [
                    ['label' => 'NIM', 'value' => $alumni->nim, 'mono' => true],
                    ['label' => 'Nama Lengkap', 'value' => $alumni->nama],
                    ['label' => 'Program Studi', 'value' => $alumni->prodi],
                    ['label' => 'Fakultas', 'value' => $alumni->fakultas ?? '-'],
                    ['label' => 'Angkatan', 'value' => $alumni->angkatan],
                    ['label' => 'Tahun Lulus', 'value' => $alumni->tahun_lulus ?? 'Belum lulus'],
                    ['label' => 'Email', 'value' => $alumni->email ?? '-'],
                    ['label' => 'No. HP', 'value' => $alumni->no_hp ?? '-'],
                    ['label' => 'Domisili', 'value' => $alumni->domisili ?? '-'],
                ];
            @endphp
            @foreach($fields as $f)
            <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1px solid var(--border); padding-bottom:10px;">
                <span class="text-muted text-sm">{{ $f['label'] }}</span>
                <span style="font-size:14px; font-weight:500; text-align:right; {{ isset($f['mono']) ? 'font-family:monospace' : '' }}">{{ $f['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- HASIL PELACAKAN -->
    <div>
        <!-- SKOR -->
        <div class="card">
            <div class="card-title" style="margin-bottom:14px">📊 Status Pelacakan</div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted text-sm">Status</span>
                    <span class="badge {{ $alumni->status_badge_class }}">{{ $alumni->status }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="text-muted text-sm">Skor Kecocokan</span>
                    <span style="font-weight:700; font-size:20px; color:{{ ($alumni->skor_kecocokan ?? 0) >= 0.70 ? '#34d399' : (($alumni->skor_kecocokan ?? 0) >= 0.40 ? '#fbbf24' : '#94a3b8') }}">
                        {{ $alumni->skor_persen }}
                    </span>
                </div>
                @if($alumni->skor_kecocokan !== null)
                <div>
                    <div class="score-bar">
                        <div class="score-fill" style="width:{{ $alumni->skor_kecocokan * 100 }}%; background:{{ $alumni->skor_kecocokan >= 0.70 ? '#10b981' : ($alumni->skor_kecocokan >= 0.40 ? '#f59e0b' : '#ef4444') }}"></div>
                    </div>
                </div>
                @endif
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted text-sm">Strategi Kelulusan</span>
                    @php $selisih = (int)date('Y') - (int)$alumni->angkatan; @endphp
                    @if($selisih >= 4)
                        <span class="badge badge-info" style="font-size:11px">🎓 Angkatan lama ({{ $selisih }} thn)</span>
                    @else
                        <span class="badge badge-secondary" style="font-size:11px">📚 Angkatan baru ({{ $selisih }} thn)</span>
                    @endif
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted text-sm">Terakhir Dilacak</span>
                    <span class="text-sm">{{ $alumni->last_tracked_at ? $alumni->last_tracked_at->format('d M Y H:i') : 'Belum pernah' }}</span>
                </div>
            </div>
        </div>

        <!-- DATA PDDIKTI -->
        @if($alumni->data_pddikti)
        @php
            $pddikti = $alumni->data_pddikti;
            $labelMap = [
                'nama'              => 'Nama di PDDIKTI',
                'nim'               => 'NIM',
                'prodi'             => 'Program Studi',
                'perguruan_tinggi'  => 'Perguruan Tinggi',
                'jenjang'           => 'Jenjang',
                'status_mahasiswa'  => 'Status Mahasiswa',
                'angkatan'          => 'Tahun Masuk',
                'kemiripan_nama'    => 'Kemiripan Nama',
                'sumber'            => 'Sumber Data',
            ];
            $skip = ['id_mahasiswa', 'is_lulus', 'detail_pddikti', 'raw_score'];
        @endphp
        <div class="card">
            <div class="card-header">
                <div class="card-title">🗃️ Data dari PDDIKTI</div>
                <span class="badge {{ isset($pddikti['sumber']) && $pddikti['sumber'] === 'PDDIKTI_REAL' ? 'badge-success' : 'badge-warning' }}" style="font-size:10px">
                    {{ isset($pddikti['sumber']) && $pddikti['sumber'] === 'PDDIKTI_REAL' ? '🌐 API Real' : '🔬 Simulasi' }}
                </span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach($pddikti as $key => $val)
                    @if(!in_array($key, $skip) && !is_array($val) && $val !== null && $val !== '')
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:8px;">
                        <span class="text-muted text-sm">{{ $labelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}</span>
                        <span class="text-sm" style="font-weight:500; text-align:right">
                            @if($key === 'kemiripan_nama')
                                {{ $val }}%
                            @elseif($key === 'status_mahasiswa' && strtolower($val) === 'lulus')
                                <span class="badge badge-success">{{ $val }}</span>
                            @else
                                {{ $val }}
                            @endif
                        </span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>


<!-- TIMELINE RIWAYAT -->
<div class="card">
    <div class="card-header">
        <div class="card-title">📋 Riwayat Pelacakan</div>
        <span class="chip">{{ $histories->count() }} entri</span>
    </div>
    @if($histories->isEmpty())
        <p class="text-muted" style="text-align:center; padding:30px">
            Belum ada riwayat pelacakan. Klik "Run Tracking Sekarang" untuk memulai.
        </p>
    @else
        <div class="timeline">
            @foreach($histories as $h)
            <div class="timeline-item">
                <div class="timeline-dot" style="background: {{ $h->status_sesudah === 'Teridentifikasi dari PDDIKTI' ? '#10b981' : ($h->status_sesudah === 'Perlu Verifikasi Manual' ? '#f59e0b' : ($h->status_sesudah === 'Belum Ditemukan' ? '#ef4444' : '#6366f1')) }}"></div>
                <div class="timeline-date">{{ $h->created_at->format('d M Y, H:i:s') }} &mdash; {{ $h->created_at->diffForHumans() }}</div>
                <div class="timeline-content">
                    <div class="timeline-title" style="display:flex; gap:8px; align-items:center;">
                        <span class="badge badge-secondary" style="font-size:10px">{{ $h->status_sebelum }}</span>
                        <span style="color:var(--text-muted)">→</span>
                        @php
                            $cls2 = match($h->status_sesudah) {
                                'Teridentifikasi dari PDDIKTI' => 'badge-success',
                                'Perlu Verifikasi Manual' => 'badge-warning',
                                'Belum Ditemukan' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $cls2 }}">{{ $h->status_sesudah }}</span>
                        @if($h->skor_kecocokan !== null)
                            <span class="chip" style="font-size:11px">Skor: {{ $h->skor_persen }}</span>
                        @endif
                    </div>
                    @if($h->query_pencarian)
                        <div class="text-muted" style="margin-top:6px; font-size:12px;">
                            🔎 Query: <code style="color:var(--accent-light)">{{ $h->query_pencarian }}</code>
                        </div>
                    @endif
                    @if($h->catatan)
                        <div class="text-muted" style="margin-top:4px; font-size:12px;">💬 {{ $h->catatan }}</div>
                    @endif
                    @if($h->diverifikasi_oleh)
                        <div style="margin-top:4px; font-size:12px; color:#818cf8">👤 Diverifikasi oleh: {{ $h->diverifikasi_oleh }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<!-- MODAL VERIFIKASI -->
<div class="modal-overlay" id="verifyModal">
    <div class="modal">
        <div class="modal-title">✅ Verifikasi Manual Alumni</div>
        <p class="text-muted text-sm" style="margin-bottom:20px">
            Tentukan status akhir untuk <strong style="color:var(--text-primary)">{{ $alumni->nama }}</strong> setelah melakukan verifikasi manual.
        </p>
        <form action="{{ route('tracking.verify', $alumni) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Status Verifikasi</label>
                <select name="status_verifikasi" class="form-control" required>
                    <option value="Teridentifikasi dari PDDIKTI">✅ Teridentifikasi dari PDDIKTI</option>
                    <option value="Belum Ditemukan">❌ Belum Ditemukan</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea name="catatan" class="form-control" rows="3"
                    placeholder="Tambahkan catatan verifikasi..."></textarea>
            </div>
            <div class="flex gap-3" style="justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('verifyModal').classList.remove('open')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success">Simpan Verifikasi</button>
            </div>
        </form>
    </div>
</div>
@endsection
