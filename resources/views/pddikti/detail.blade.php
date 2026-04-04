@extends('layouts.app')

@section('title', ($detail['nama_mahasiswa'] ?? $detail['nama'] ?? 'Detail') . ' — PDDIKTI')
@section('page-title', $detail['nama_mahasiswa'] ?? $detail['nama'] ?? 'Detail Mahasiswa')
@section('page-subtitle', 'Data dari PDDIKTI · ' . ($detail['nama_pt'] ?? '-'))

@section('content')

{{-- BREADCRUMB --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <a href="{{ url()->previous() }}" class="btn btn-secondary" style="background: rgba(30, 34, 53, 0.6); border: 1px solid var(--border); padding: 8px 20px; border-radius: 10px; font-weight: 600;">← Kembali</a>
</div>

@php
    $statusTerakhir = $detail['status_saat_ini']
        ?? $detail['status_terakhir']
        ?? $detail['status_mahasiswa_saat_ini']
        ?? $detail['status_mahasiswa']
        ?? '-';
    $isLulus = str_contains(strtolower($statusTerakhir), 'lulus');

    $displayFields = [
        ['icon' => '🏛️', 'label' => 'Perguruan Tinggi', 'value' => $detail['nama_pt'] ?? '-'],
        ['icon' => '🔖', 'label' => 'Kode PT', 'value' => $detail['kode_pt'] ?? '-'],
        ['icon' => '🔖', 'label' => 'Kode Prodi', 'value' => $detail['kode_prodi'] ?? '-'],
        ['icon' => '📚', 'label' => 'Program Studi', 'value' => $detail['nama_prodi'] ?? '-'],
        ['icon' => '👤', 'label' => 'Nama Lengkap', 'value' => $detail['nama_mahasiswa'] ?? '-'],
        ['icon' => '🔢', 'label' => 'NIM', 'value' => $detail['nim'] ?? '-', 'mono' => true],
        ['icon' => '📌', 'label' => 'Jenis Daftar', 'value' => $detail['jenis_daftar'] ?? '-'],
        ['icon' => '👤', 'label' => 'Jenis Kelamin', 'value' => $detail['jenis_kelamin'] ?? '-'],
        ['icon' => '🎓', 'label' => 'Jenjang Pendidikan', 'value' => $detail['jenjang'] ?? '-'],
        ['icon' => '🏁', 'label' => 'Status Terakhir', 'value' => $statusTerakhir, 'highlight' => true],
        ['icon' => '📅', 'label' => 'Tanggal Masuk', 'value' => $detail['tanggal_masuk'] ?? '-'],
    ];
@endphp

<div class="grid-2" style="grid-template-columns: 1.2fr 0.8fr; gap: 25px;">
    {{-- LEFT CARD: DATA MAHASISWA --}}
    <div class="card" style="background: rgba(30, 34, 53, 0.4); border-radius: 20px; padding: 30px; border: 1px solid rgba(255, 255, 255, 0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px;">
                <span style="font-size: 18px;">🗃️</span> Data Mahasiswa PDDIKTI
            </div>
            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 12px; border-radius: 8px; font-size: 11px; border: 1px solid rgba(16, 185, 129, 0.2);">🌐 Real-Time API</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            @foreach($displayFields as $field)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <div style="display: flex; align-items: center; gap: 10px; color: #94a3b8; font-size: 13px;">
                        <span style="font-size: 16px; opacity: 0.8; width: 20px; text-align: center;">{{ $field['icon'] }}</span>
                        {{ $field['label'] }}
                    </div>
                    <div style="font-weight: 600; font-size: 13px; color: {{ isset($field['highlight']) ? ($isLulus ? '#34d399' : '#f59e0b') : '#f1f5f9' }}; text-align: right; max-width: 60%; {{ isset($field['mono']) ? 'font-family: monospace; letter-spacing: 0.5px;' : '' }}">
                        {{ $field['value'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- RIGHT SIDE --}}
    <div style="display: flex; flex-direction: column; gap: 25px;">
        {{-- CARD: STATUS KELULUSAN --}}
        <div class="card" style="background: rgba(30, 34, 53, 0.4); border-radius: 20px; padding: 30px; border: 1px solid rgba(255, 255, 255, 0.05); text-align: center;">
            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 15px; margin-bottom: 25px;">
                <span style="font-size: 18px;">📊</span> Status Kelulusan
            </div>
            
            <div style="padding: 20px 0;">
                <div style="font-size: 64px; margin-bottom: 20px;">
                    @if($isLulus)
                        🎓
                    @else
                        📖
                    @endif
                </div>
                <div style="font-size: 20px; font-weight: 800; color: {{ $isLulus ? '#34d399' : '#f59e0b' }}; letter-spacing: 0.5px;">
                    {{ $isLulus ? 'Lulus' : 'Belum Lulus' }}
                </div>
                <div style="margin-top: 15px; background: {{ $isLulus ? 'rgba(16,185,129,0.1)' : 'rgba(146, 64, 14, 0.1)' }}; border: 1px solid {{ $isLulus ? 'rgba(16,185,129,0.3)' : 'rgba(146, 64, 14, 0.3)' }}; border-radius: 12px; padding: 12px; color: {{ $isLulus ? '#34d399' : '#fbbf24' }}; font-size: 12px; font-weight: 600;">
                    {{ $statusTerakhir }}
                </div>
            </div>
        </div>

        {{-- CARD: ENRICHMENT LINK --}}
        <div class="card" style="background: #1a1d2e; border: 1px solid var(--border); border-radius: 20px; padding: 25px;">
            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 15px; margin-bottom: 12px;">
                <span style="font-size: 18px;">🔗</span> Data Enrichment
            </div>
            <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; margin-bottom: 20px;">
                Lihat data pengayaan alumni ini (sosial media, karir, kontak) di halaman Tracking Alumni UMM.
            </p>
            <a href="{{ route('alumni_umm.show', ['nama' => $detail['nama_mahasiswa'] ?? '']) }}" class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 12px; font-weight: 700; gap: 8px; justify-content: center; text-decoration: none;">
                🔍 Lihat Enrichment Data
            </a>
        </div>
    </div>
</div>

@endsection
