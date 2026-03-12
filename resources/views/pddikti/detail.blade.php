@extends('layouts.app')

@section('title', ($detail['nama_mahasiswa'] ?? 'Detail') . ' — PDDIKTI')
@section('page-title', $detail['nama_mahasiswa'] ?? 'Detail Mahasiswa')
@section('page-subtitle', 'Data dari PDDIKTI · ' . ($detail['nama_pt'] ?? ''))

@section('content')

{{-- TOP ACTIONS --}}
<div class="flex items-center gap-3 mb-4">
    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">← Kembali</a>
    @if(!$savedAlumni)
        <button onclick="document.getElementById('saveModal').classList.add('open')" class="btn btn-success btn-sm">
            💾 Simpan ke Daftar Tracking
        </button>
    @else
        <a href="{{ route('alumni.show', $savedAlumni) }}" class="btn btn-outline btn-sm">
            📋 Lihat di Tracking Lokal
        </a>
    @endif
</div>

@php
    // Determine graduation status — PDDIKTI returns 'status_saat_ini' (e.g. 'Lulus-2019/2020 Genap')
    $statusTerakhir = $detail['status_saat_ini']
        ?? $detail['status_terakhir']
        ?? $detail['status_mahasiswa_saat_ini']
        ?? $detail['status_mahasiswa']
        ?? null;
    $isLulus = $statusTerakhir && str_starts_with(strtolower($statusTerakhir), 'lulus');

    // Extract year from "Lulus-2019/2020 Genap" or "Lulus-2019"
    $tahunLulus = null;
    if ($isLulus && preg_match('/(\d{4})\/(\d{4})/', $statusTerakhir, $m)) {
        $tahunLulus = $m[2]; // ambil tahun akhir
    } elseif ($isLulus && preg_match('/Lulus-(\d{4})/', $statusTerakhir, $m)) {
        $tahunLulus = $m[1];
    }

    // Map field labels - include status_saat_ini as highlight
    $fieldMap = [
        'nama_mahasiswa'           => ['label' => 'Nama Lengkap', 'icon' => '👤'],
        'nama'                     => ['label' => 'Nama Lengkap', 'icon' => '👤'],
        'jenis_kelamin'            => ['label' => 'Jenis Kelamin', 'icon' => '⚧'],
        'nim'                      => ['label' => 'NIM', 'icon' => '🔢', 'mono' => true],
        'nama_pt'                  => ['label' => 'Perguruan Tinggi', 'icon' => '🏛️'],
        'kode_pt'                  => ['label' => 'Kode PT', 'icon' => '🔖'],
        'nama_prodi'               => ['label' => 'Program Studi', 'icon' => '📚'],
        'prodi'                    => ['label' => 'Program Studi', 'icon' => '📚'],
        'kode_prodi'               => ['label' => 'Kode Prodi', 'icon' => '🔖'],
        'jenjang'                  => ['label' => 'Jenjang Pendidikan', 'icon' => '🎓'],
        'tanggal_masuk'            => ['label' => 'Tanggal Masuk', 'icon' => '📅'],
        'tgl_masuk'                => ['label' => 'Tanggal Masuk', 'icon' => '📅'],
        'angkatan'                 => ['label' => 'Angkatan', 'icon' => '📆'],
        'jenis_daftar'             => ['label' => 'Jenis Daftar', 'icon' => '📌'],
        'status_awal'              => ['label' => 'Status Awal', 'icon' => '📌'],
        'status_saat_ini'          => ['label' => 'Status Terakhir Mahasiswa', 'icon' => '🏁', 'highlight' => true],
        'status_mahasiswa_saat_ini'=> ['label' => 'Status Terakhir Mahasiswa', 'icon' => '🏁', 'highlight' => true],
        'status_terakhir'          => ['label' => 'Status Terakhir Mahasiswa', 'icon' => '🏁', 'highlight' => true],
    ];

    $skip = ['id', 'id_pt', 'id_sms', 'foto_profil', 'website', 'rasio'];
@endphp

<div class="grid-2">
    {{-- PROFIL CARD --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">🗃️ Data Mahasiswa PDDIKTI</div>
            @if($isLulus)
                <span class="badge badge-success">🎓 Alumni Lulus</span>
            @else
                <span class="badge badge-secondary">Mahasiswa</span>
            @endif
        </div>

        <div style="display:flex; flex-direction:column; gap:12px;">
            @foreach($detail as $key => $value)
                @if(in_array($key, $skip) || is_array($value) || $value === null || $value === '') @continue @endif
                @php
                    $meta = $fieldMap[$key] ?? ['label' => ucwords(str_replace('_', ' ', $key)), 'icon' => '·'];
                    $isHighlight = isset($meta['highlight']) && $meta['highlight'];
                @endphp
                <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1px solid var(--border); padding-bottom:10px;">
                    <span class="text-muted text-sm">{{ $meta['icon'] }} {{ $meta['label'] }}</span>
                    <span style="font-size:13px; font-weight:{{ $isHighlight ? '700' : '500' }}; text-align:right; max-width:55%;
                        {{ isset($meta['mono']) ? 'font-family:monospace' : '' }};
                        {{ $isHighlight && $isLulus ? 'color:#34d399' : ($isHighlight ? 'color:#fbbf24' : '') }}">
                        @if($isHighlight && $isLulus)
                            🎓 {{ $value }}
                        @else
                            {{ $value }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- STATUS & SIMPAN CARD --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card">
            <div class="card-title" style="margin-bottom:14px;">📊 Status Kelulusan</div>
            @if($isLulus)
            <div style="text-align:center; padding:24px 0;">
                <div style="font-size:48px; margin-bottom:8px;">🎓</div>
                <div style="font-size:20px; font-weight:700; color:#34d399; margin-bottom:4px;">LULUS</div>
                @if($tahunLulus)
                    <div class="text-muted text-sm">Tahun Lulus: {{ $tahunLulus }}</div>
                @endif
                <div style="margin-top:12px; font-size:13px; background:#064e3b33; border:1px solid #10b98133; border-radius:8px; padding:10px 14px; color:#6ee7b7;">
                    {{ $statusTerakhir }}
                </div>
            </div>
            @elseif($statusTerakhir)
            <div style="text-align:center; padding:20px 0;">
                <div style="font-size:40px; margin-bottom:8px;">📚</div>
                <div style="font-size:16px; font-weight:600; color:#fbbf24; margin-bottom:8px;">Belum Lulus</div>
                <div style="font-size:13px; background:#78350f33; border:1px solid #f59e0b33; border-radius:8px; padding:10px 14px; color:#fcd34d;">
                    {{ $statusTerakhir }}
                </div>
            </div>
            @else
            <div style="text-align:center; padding:20px;">
                <div style="font-size:14px; color:var(--text-muted);">Status tidak tersedia</div>
            </div>
            @endif
        </div>

        {{-- Sudah disimpan? --}}
        @if($savedAlumni)
        <div class="card" style="border-color: var(--accent);">
            <div class="card-title" style="margin-bottom:10px;">✅ Sudah di Tracking Lokal</div>
            <p class="text-muted text-sm" style="margin-bottom:12px;">
                Alumni ini sudah ada di database tracking dengan status
                <span class="badge {{ $savedAlumni->status_badge_class }}">{{ $savedAlumni->status }}</span>
            </p>
            <a href="{{ route('alumni.show', $savedAlumni) }}" class="btn btn-outline btn-sm" style="width:100%; justify-content:center;">
                📋 Buka Halaman Tracking
            </a>
        </div>
        @else
        <div class="card">
            <div class="card-title" style="margin-bottom:10px;">💾 Simpan ke Tracking</div>
            <p class="text-muted text-sm" style="margin-bottom:12px;">
                Tambahkan alumni ini ke daftar pelacakan lokal untuk memantau statusnya secara berkala.
            </p>
            <button onclick="document.getElementById('saveModal').classList.add('open')" class="btn btn-success btn-sm" style="width:100%; justify-content:center;">
                + Simpan ke Daftar Tracking
            </button>
        </div>
        @endif
    </div>
</div>

{{-- MODAL SIMPAN --}}
@if(!$savedAlumni)
<div class="modal-overlay" id="saveModal">
    <div class="modal">
        <div class="modal-title">💾 Simpan Alumni ke Tracking</div>
        <p class="text-muted text-sm" style="margin-bottom:20px;">
            Data berikut akan disimpan dari PDDIKTI ke database tracking lokal.
        </p>
        <form action="{{ route('pddikti.save', $id) }}" method="POST">
            @csrf
            {{-- Hidden PDDIKTI values --}}
            <input type="hidden" name="nama"            value="{{ $detail['nama_mahasiswa'] ?? '' }}">
            <input type="hidden" name="nim"             value="{{ $detail['nim'] ?? '' }}">
            <input type="hidden" name="prodi"           value="{{ $detail['nama_prodi'] ?? '' }}">
            <input type="hidden" name="perguruan_tinggi" value="{{ $detail['nama_pt'] ?? '' }}">
            <input type="hidden" name="jenjang"         value="{{ $detail['jenjang'] ?? '' }}">
            <input type="hidden" name="status_awal"     value="{{ $detail['status_awal'] ?? '' }}">
            <input type="hidden" name="status_terakhir" value="{{ $statusTerakhir ?? '' }}">
            <input type="hidden" name="tanggal_masuk"   value="{{ $detail['tanggal_masuk'] ?? '' }}">

            <div class="form-group">
                <label class="form-label">Angkatan (Tahun Masuk)</label>
                <input type="number" name="angkatan" class="form-control" required
                    value="{{ $detail['angkatan'] ?? (preg_match('/(\d{4})/', $detail['tanggal_masuk'] ?? '', $m) ? $m[1] : date('Y')) }}"
                    min="2000" max="{{ date('Y') }}">
            </div>

            @if($tahunLulus)
            <div class="form-group">
                <label class="form-label">Tahun Lulus</label>
                <input type="number" name="tahun_lulus" class="form-control" value="{{ $tahunLulus }}" min="2000" max="{{ date('Y') + 1 }}">
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">Domisili (opsional)</label>
                <input type="text" name="domisili" class="form-control" placeholder="Kota/Kabupaten">
            </div>

            <div class="flex gap-3" style="justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('saveModal').classList.remove('open')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
