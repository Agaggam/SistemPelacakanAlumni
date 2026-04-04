@extends('layouts.public')

@section('title', 'Tracking Alumni UMM — Sistem Pelacakan Alumni')

@section('content')

{{-- HERO --}}
<div class="hero" style="padding-bottom: 16px;">
    <div style="font-size: 56px; margin-bottom: 16px; filter: drop-shadow(0 0 15px rgba(168, 85, 247, 0.4));">🚀</div>
    <h1 style="font-size: 42px; font-weight: 800; letter-spacing: -1.5px; background: linear-gradient(to right, #a855f7, #d8b4fe); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Tracking Alumni UMM
    </h1>
    <p style="font-size: 16px; color: var(--muted); max-width: 550px; margin: 12px auto 0; line-height: 1.6;">
        Data lulusan Universitas Muhammadiyah Malang
        <span style="display: block; margin-top: 8px; font-size: 13px; color: #818cf8; font-weight: 700;">
            📊 {{ number_format($totalAlumni ?? 0) }} Total Alumni Tercatat
        </span>
    </p>
</div>

{{-- SEARCH FORM --}}
<div style="background: rgba(30, 34, 53, 0.6); backdrop-filter: blur(10px); border-radius: 20px; padding: 28px; border: 1px solid rgba(168, 85, 247, 0.1); margin-bottom: 30px;">
    <form method="GET" action="{{ route('alumni_umm.tracking') }}" id="trackingForm">
        <div style="background: #0f1117; border-radius: 14px; padding: 5px; display: flex; align-items: center; gap: 8px;">
            <input type="text" name="keyword" class="search-input" value="{{ $keyword ?? '' }}"
                placeholder="Cari nama alumni atau NIM..." autofocus autocomplete="off"
                style="border: none; background: transparent; padding: 14px 22px; flex: 1;">
            <button type="submit" class="btn btn-primary" style="background: #6366f1; border-radius: 10px; padding: 12px 28px; font-weight: 700;">
                🔎 Cari
            </button>
            @if($keyword || $prodi || $fakultas)
                <a href="{{ route('alumni_umm.tracking') }}" class="btn btn-outline" style="border: 1px solid var(--border); border-radius: 10px; padding: 12px 16px;">✕</a>
            @endif
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 20px; align-items: flex-end; padding: 0 5px;">
            <div class="filter-group">
                <div class="filter-label">Fakultas</div>
                <select name="fakultas" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Fakultas</option>
                    @foreach($fakultasList ?? [] as $f)
                        <option value="{{ $f }}" {{ ($fakultas ?? '') == $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Program Studi</div>
                <select name="prodi" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList ?? [] as $p)
                        <option value="{{ $p }}" {{ ($prodi ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Urutkan</div>
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="nama_asc" {{ ($sort ?? '') == 'nama_asc' ? 'selected' : '' }}>Nama A–Z</option>
                    <option value="nama_desc" {{ ($sort ?? '') == 'nama_desc' ? 'selected' : '' }}>Nama Z–A</option>
                </select>
            </div>
            <div class="filter-group" style="margin-left: auto;">
                <div class="filter-label" style="text-align: right;">Sumber</div>
                <div style="background: rgba(168,85,247,0.1); border: 1px solid rgba(168,85,247,0.25); padding: 7px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; color: #d8b4fe;">
                    📁 Database Lokal UMM
                </div>
            </div>
        </div>

    </form>
</div>

{{-- RESULTS --}}
@if($keyword || $prodi || $fakultas)
    <div style="margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 16px;">📊</span>
        <span style="font-size: 14px; font-weight: 800;">Hasil ({{ $alumni->total() }} data)</span>
    </div>
@endif

<div style="background: rgba(30, 34, 53, 0.4); border-radius: 16px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.05);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="background: rgba(15, 17, 23, 0.5); border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 25%;">Nama Lulusan</th>
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 14%;">NIM</th>
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 14%;">Fakultas</th>
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 17%;">Program Studi</th>
                    <th style="padding: 14px 16px; text-align: center; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 10%;">Tahun Masuk</th>
                    <th style="padding: 14px 16px; text-align: center; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 12%;">Tanggal Lulus</th>
                    <th style="padding: 14px 20px; text-align: right; width: 8%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumni as $item)
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                    <td style="padding: 14px 20px;">
                        <div style="font-weight: 700; font-size: 13px; color: #f1f5f9; text-transform: uppercase;">{{ $item->nama }}</div>
                    </td>
                    <td style="padding: 14px 20px;">
                        <span style="color: #a855f7; font-family: 'JetBrains Mono', monospace; font-weight: 600; font-size: 12px; letter-spacing: 0.3px;">{{ $item->nim ?? '-' }}</span>
                    </td>
                    <td style="padding: 14px 20px;">
                        <div style="font-size: 12px; font-weight: 600; color: #94a3b8;">{{ $item->fakultas ?? '-' }}</div>
                    </td>
                    <td style="padding: 14px 20px;">
                        <div style="font-size: 12px; font-weight: 600; color: #e2e8f0;">{{ $item->prodi ?? '-' }}</div>
                    </td>
                    <td style="padding: 14px 16px; text-align: center;">
                        <span style="font-weight: 700; color: #f1f5f9; font-size: 13px;">{{ $item->tahun_masuk ?? '-' }}</span>
                    </td>
                    <td style="padding: 14px 16px; text-align: center;">
                        <span style="font-size: 12px; color: #34d399; font-weight: 600;">{{ $item->tanggal_lulus ?? '-' }}</span>
                    </td>
                    <td style="padding: 14px 20px; text-align: right;">
                        <a href="{{ route('alumni_umm.show', ['nama' => $item->nama]) }}" style="text-decoration: none; color: #818cf8; font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(129, 140, 248, 0.2); background: rgba(99, 102, 241, 0.05); white-space: nowrap;">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 60px 20px; text-align: center; color: #64748b;">
                        <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
                        <div style="font-size: 16px; font-weight: 700; color: #f1f5f9; margin-bottom: 8px;">Tidak ada data ditemukan</div>
                        <div style="font-size: 13px;">Coba ubah kata kunci atau filter pencarian.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- PAGINATION --}}
@if($alumni->hasPages())
<div style="display: flex; justify-content: center; margin-top: 24px;">
    {{ $alumni->appends(request()->query())->links() }}
</div>
@endif

@endsection
