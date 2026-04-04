@extends('layouts.public')

@section('title', ($detail['nama_mahasiswa'] ?? $detail['nama'] ?? 'Detail') . ' — PDDIKTI')

@section('content')

{{-- BACK --}}
<div style="margin-bottom: 24px;">
    <a href="{{ url()->previous() }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #94a3b8; font-size: 13px; font-weight: 600; padding: 10px 18px; border-radius: 10px; border: 1px solid var(--border); background: var(--card); transition: all 0.2s;">
        ← Kembali ke Pencarian
    </a>
</div>

@php
    $namaDisplay = $detail['nama_mahasiswa'] ?? $detail['nama'] ?? '-';
    $statusTerakhir = $detail['status_saat_ini']
        ?? $detail['status_terakhir']
        ?? $detail['status_mahasiswa_saat_ini']
        ?? $detail['status_mahasiswa']
        ?? '-';
    $isLulus = str_contains(strtolower($statusTerakhir), 'lulus');
@endphp

{{-- HEADER --}}
<div style="background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 32px; margin-bottom: 24px; display: flex; align-items: center; gap: 24px;">
    <div style="width: 72px; height: 72px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; color: white; flex-shrink: 0;">
        {{ strtoupper(substr($namaDisplay, 0, 1)) }}
    </div>
    <div style="flex: 1; min-width: 0;">
        <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 6px; color: #f1f5f9;">{{ $namaDisplay }}</h1>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span style="color: #818cf8; font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 13px;">{{ $detail['nim'] ?? '-' }}</span>
            <span style="color: #475569;">•</span>
            <span style="color: #94a3b8; font-size: 13px;">{{ $detail['nama_prodi'] ?? '-' }}</span>
            <span style="color: #475569;">•</span>
            <span style="color: #94a3b8; font-size: 13px;">{{ $detail['nama_pt'] ?? '-' }}</span>
        </div>
    </div>
    <div style="flex-shrink: 0;">
        <span style="background: {{ $isLulus ? 'rgba(16,185,129,0.15)' : 'rgba(245,158,11,0.15)' }}; color: {{ $isLulus ? '#34d399' : '#fbbf24' }}; padding: 8px 18px; border-radius: 10px; font-size: 13px; font-weight: 800; border: 1px solid {{ $isLulus ? 'rgba(16,185,129,0.3)' : 'rgba(245,158,11,0.3)' }};">
            {{ $isLulus ? '🎓 Lulus' : '📖 Belum Lulus' }}
        </span>
    </div>
</div>

{{-- DATA GRID --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    {{-- LEFT: DETAIL DATA --}}
    <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px;">
        <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
            <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            Data Mahasiswa
        </h3>

        @php
            $fields = [
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="20" width="20" height="2"></rect><polygon points="12 2 2 7 22 7"></polygon><line x1="6" y1="7" x2="6" y2="20"></line><line x1="18" y1="7" x2="18" y2="20"></line><line x1="12" y1="7" x2="12" y2="20"></line></svg>', 'Perguruan Tinggi', $detail['nama_pt'] ?? '-'],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>', 'Kode PT', $detail['kode_pt'] ?? '-'],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>', 'Program Studi', $detail['nama_prodi'] ?? '-'],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>', 'Kode Prodi', $detail['kode_prodi'] ?? '-'],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>', 'Jenis Daftar', $detail['jenis_daftar'] ?? '-'],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>', 'Jenis Kelamin', $detail['jenis_kelamin'] ?? '-'],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#14b8a6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>', 'Jenjang', $detail['jenjang'] ?? '-'],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>', 'Tanggal Masuk', $detail['tanggal_masuk'] ?? '-'],
            ];
        @endphp

        <div style="display: flex; flex-direction: column; gap: 0;">
            @foreach($fields as $f)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; color: #94a3b8; font-size: 13px;">
                    <span style="display:flex;align-items:center;justify-content:center; width: 20px;">{!! $f[0] !!}</span>
                    {{ $f[1] }}
                </div>
                <div style="font-weight: 600; font-size: 13px; color: #e2e8f0; text-align: right; max-width: 55%;">
                    {{ $f[2] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- RIGHT: STATUS + ENRICHMENT --}}
    <div style="display: flex; flex-direction: column; gap: 24px;">
        {{-- STATUS --}}
        <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px; text-align: center;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Status Kelulusan
            </h3>
            <div style="margin-bottom: 24px; display: flex; justify-content: center;">
                @if($isLulus)
                <svg style="width:64px;height:64px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                @else
                <svg style="width:64px;height:64px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                @endif
            </div>
            <div style="font-size: 20px; font-weight: 800; color: {{ $isLulus ? '#34d399' : '#f59e0b' }}; margin-bottom: 12px;">
                {{ $isLulus ? 'Lulus' : 'Belum Lulus' }}
            </div>
            <div style="background: {{ $isLulus ? 'rgba(16,185,129,0.08)' : 'rgba(245,158,11,0.08)' }}; border: 1px solid {{ $isLulus ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)' }}; border-radius: 10px; padding: 10px; color: {{ $isLulus ? '#6ee7b7' : '#fcd34d' }}; font-size: 12px; font-weight: 600;">
                {{ $statusTerakhir }}
            </div>
        </div>

        {{-- ENRICHMENT LINK --}}
        @php
            $alumniNama = $detail['nama_mahasiswa'] ?? $detail['nama'] ?? null;
        @endphp
        <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Data Enrichment
            </h3>
            <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; margin-bottom: 18px;">
                Lihat data pengayaan alumni ini (sosial media, karir, kontak) di halaman Tracking Alumni UMM.
            </p>
            @if($alumniNama)
                @auth
                <a href="{{ route('alumni_umm.show', ['nama' => $alumniNama]) }}" class="btn btn-primary" style="width: 100%; border-radius: 10px; padding: 12px; font-weight: 700; justify-content: center; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <svg style="width:16px;height:16px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Lihat Enrichment Data
                </a>
                @else
                <a href="{{ route('login') }}" class="btn btn-outline" style="width: 100%; border-radius: 10px; padding: 12px; font-weight: 700; justify-content: center; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <svg style="width:16px;height:16px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Login untuk Melihat Enrichment
                </a>
                @endauth
            @else
                <div style="text-align: center; color: #64748b; font-size: 13px; padding: 10px;">
                    Data nama tidak tersedia untuk pencarian enrichment.
                </div>
            @endif
        </div>

        {{-- SOURCE --}}
        <div style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.15); border-radius: 12px; padding: 14px 18px; font-size: 12px; color: #818cf8; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px;height:18px;flex-shrink:0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            <span>Data real-time dari server PDDIKTI Kemdikbud</span>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; }
}
</style>
@endsection
