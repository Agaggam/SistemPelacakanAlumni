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

{{-- COVERAGE & ACCURACY PANEL --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">

    {{-- Overall Coverage Card --}}
    <div style="background: rgba(30,34,53,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(168,85,247,0.2); border-radius: 18px; padding: 22px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(168,85,247,0.15); display: flex; align-items: center; justify-content: center; font-size: 20px;">📊</div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Coverage Keseluruhan</div>
                <div style="font-size: 11px; color: #475569;">dari {{ number_format($totalAlumni) }} total alumni</div>
            </div>
        </div>
        <div style="font-size: 48px; font-weight: 900; color: #a855f7; line-height: 1; margin-bottom: 4px;">{{ $overallCoverage }}<span style="font-size: 24px;">%</span></div>
        <div style="font-size: 13px; color: #94a3b8; margin-bottom: 14px;">{{ number_format($alumniWithAnyData) }} alumni memiliki minimal 1 data</div>
        <div style="height: 6px; background: rgba(255,255,255,0.06); border-radius: 6px; overflow: hidden;">
            <div style="height: 100%; width: {{ $overallCoverage }}%; background: linear-gradient(to right, #a855f7, #d8b4fe); border-radius: 6px;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 11px; color: #64748b;">
            <span>🟣 Scraped: {{ number_format($totalScraped) }}</span>
            <span>🟡 Generated: {{ number_format($totalGenerated) }}</span>
        </div>
    </div>

    {{-- Accuracy Card --}}
    <div style="background: rgba(30,34,53,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(52,211,153,0.2); border-radius: 18px; padding: 22px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(52,211,153,0.12); display: flex; align-items: center; justify-content: center; font-size: 20px;">✅</div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Accuracy (Data Real)</div>
                <div style="font-size: 11px; color: #475569;">dari data yang sudah di-enrich</div>
            </div>
        </div>
        <div style="font-size: 48px; font-weight: 900; color: #34d399; line-height: 1; margin-bottom: 4px;">{{ $overallAccuracy }}<span style="font-size: 24px;">%</span></div>
        <div style="font-size: 13px; color: #94a3b8; margin-bottom: 14px;">{{ number_format($totalScraped) }} alumni terverifikasi via scraping</div>
        <div style="height: 6px; background: rgba(255,255,255,0.06); border-radius: 6px; overflow: hidden;">
            <div style="height: 100%; width: {{ $overallAccuracy }}%; background: linear-gradient(to right, #34d399, #6ee7b7); border-radius: 6px;"></div>
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 10px;">
            <span style="color: #34d399; font-weight: 700;">Scraped</span> = data nyata dari Bing/Yahoo &bull;
            <span style="color: #fbbf24; font-weight: 700;">Generated</span> = estimasi berdasarkan prodi
        </div>
    </div>

    {{-- Per-Field Coverage Card --}}
    <div style="background: rgba(30,34,53,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(99,102,241,0.2); border-radius: 18px; padding: 22px; grid-column: span 1;">
        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;">📋 Coverage Per Field (dari {{ number_format($totalAlumni) }})</div>
        @php
        $fieldLabels = [
            'linkedin'    => ['LinkedIn',     '🔵'],
            'instagram'   => ['Instagram',    '🟣'],
            'facebook'    => ['Facebook',     '🔷'],
            'tiktok'      => ['TikTok',       '⬛'],
            'email'       => ['Email',        '📧'],
            'no_hp'       => ['No HP',        '📞'],
            'tempat_kerja'=> ['Tempat Kerja', '🏢'],
            'posisi'      => ['Posisi',       '💼'],
        ];
        @endphp
        <div style="display: flex; flex-direction: column; gap: 7px;">
            @foreach($fieldLabels as $field => [$label, $icon])
            @php
                $cov = $coverage[$field] ?? ['found' => 0, 'scraped' => 0, 'pct' => 0];
                $pct = $cov['pct'];
                $barColor = $pct > 30 ? '#34d399' : ($pct > 10 ? '#fbbf24' : '#ef4444');
                $scrapedPct = $cov['found'] > 0 ? round($cov['scraped'] / $cov['found'] * 100) : 0;
            @endphp
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px;">
                    <span style="font-size: 11px; font-weight: 600; color: #94a3b8;">{{ $icon }} {{ $label }}</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 10px; color: #475569;">{{ number_format($cov['found']) }}</span>
                        <span style="font-size: 11px; font-weight: 800; color: {{ $barColor }};">{{ $pct }}%</span>
                        @if($scrapedPct > 0)
                        <span style="font-size: 9px; background: rgba(52,211,153,0.12); color: #34d399; padding: 1px 5px; border-radius: 3px; font-weight: 700;">{{ $scrapedPct }}% real</span>
                        @endif
                    </div>
                </div>
                <div style="height: 3px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $pct }}%; background: {{ $barColor }}; border-radius: 3px;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
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
                    <option value="enrichment_desc" {{ ($sort ?? '') == 'enrichment_desc' ? 'selected' : '' }}>Enrichment ↑</option>
                    <option value="enrichment_asc" {{ ($sort ?? '') == 'enrichment_asc' ? 'selected' : '' }}>Enrichment ↓</option>
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
        <table style="width: 100%; border-collapse: collapse; min-width: 1050px;">
            <thead>
                <tr style="background: rgba(15, 17, 23, 0.5); border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 22%;">Nama Lulusan</th>
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 12%;">NIM</th>
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 13%;">Fakultas</th>
                    <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 15%;">Program Studi</th>
                    <th style="padding: 14px 16px; text-align: center; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 8%;">Msk</th>
                    <th style="padding: 14px 16px; text-align: center; font-size: 11px; font-weight: 700; color: #818cf8; text-transform: uppercase; letter-spacing: 0.5px; width: 14%;">Enrichment</th>
                    <th style="padding: 14px 20px; text-align: right; width: 16%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumni as $item)
                @php
                    $enrichFields = ['linkedin','instagram','facebook','tiktok','email','no_hp','tempat_kerja','posisi'];
                    $enrichCount = collect($enrichFields)->filter(fn($f) => !empty($item->$f))->count();
                    $enrichPct   = round(($enrichCount / 8) * 100);
                    $barColor    = $enrichPct > 60 ? '#34d399' : ($enrichPct > 25 ? '#fbbf24' : '#475569');
                    $source      = $item->data_source ?? null;
                @endphp
                <tr id="row_{{ $item->id }}" style="border-bottom: 1px solid rgba(255, 255, 255, 0.03); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
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

                    {{-- ENRICHMENT COLUMN --}}
                    <td style="padding: 10px 16px; text-align: center;">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <div id="enr_pct_{{ $item->id }}" style="font-size: 13px; font-weight: 800; color: {{ $barColor }};">
                                {{ $enrichPct }}%
                            </div>
                            <div style="width: 90%; height: 4px; background: rgba(255,255,255,0.06); border-radius: 4px; overflow: hidden;">
                                <div id="enr_bar_{{ $item->id }}" style="height: 100%; width: {{ $enrichPct }}%; background: {{ $barColor }}; border-radius: 4px; transition: width 0.6s ease;"></div>
                            </div>
                            @if($source)
                            <div id="enr_src_{{ $item->id }}" style="font-size: 9px; font-weight: 700; padding: 2px 6px; border-radius: 4px;
                                {{ $source === 'scraped' ? 'background: rgba(16,185,129,0.15); color: #34d399;' : ($source === 'manual' ? 'background: rgba(59,130,246,0.15); color: #60a5fa;' : 'background: rgba(251,191,36,0.12); color: #fbbf24;') }}">
                                {{ strtoupper($source) }}
                            </div>
                            @else
                            <div id="enr_src_{{ $item->id }}" style="font-size: 9px; color: #475569;">—</div>
                            @endif
                        </div>
                    </td>

                    {{-- ACTION BUTTONS --}}
                    <td style="padding: 10px 16px; text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                            {{-- Cari Data Button --}}
                            <button
                                id="btn_enr_{{ $item->id }}"
                                onclick="doEnrich({{ $item->id }}, this)"
                                title="Auto-fill data enrichment alumni ini"
                                style="font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(168,85,247,0.3); background: rgba(168,85,247,0.08); color: #d8b4fe; cursor: pointer; white-space: nowrap; transition: all 0.2s; display: flex; align-items: center; gap: 5px;"
                                onmouseover="this.style.background='rgba(168,85,247,0.2)'; this.style.borderColor='rgba(168,85,247,0.5)'"
                                onmouseout="this.style.background='rgba(168,85,247,0.08)'; this.style.borderColor='rgba(168,85,247,0.3)'"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                Cari Data
                            </button>
                            {{-- Detail Button --}}
                            <a href="{{ route('alumni_umm.show', ['nama' => $item->nama]) }}" style="text-decoration: none; color: #818cf8; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(129, 140, 248, 0.2); background: rgba(99, 102, 241, 0.05); white-space: nowrap; transition: all 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.15)'" onmouseout="this.style.background='rgba(99,102,241,0.05)'">
                                Detail →
                            </a>
                        </div>
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

{{-- TOAST NOTIFICATION --}}
<div id="enrichToast" style="display: none; position: fixed; bottom: 28px; right: 28px; z-index: 9999; background: var(--card, #1e1e2e); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 14px 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); max-width: 340px; animation: slideUp 0.3s ease;">
    <div id="enrichToastMsg" style="font-size: 13px; font-weight: 600; color: #e2e8f0;"></div>
    <div id="enrichToastSub" style="font-size: 11px; color: #64748b; margin-top: 4px;"></div>
</div>


<style>
@keyframes slideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
@keyframes spin    { to { transform: rotate(360deg); } }
.enr-spinner { display: inline-block; width: 11px; height: 11px; border-radius: 50%; border: 2px solid rgba(216,180,254,0.3); border-top-color: #d8b4fe; animation: spin 0.6s linear infinite; }
</style>

@php
$enrichFields = ['linkedin','instagram','facebook','tiktok','email','no_hp','tempat_kerja','posisi'];
$alumniPageData = $alumni->map(function($a) use ($enrichFields) {
    $filled = collect($enrichFields)->filter(fn($f) => !empty($a->$f))->count();
    return [
        'id'   => $a->id,
        'nama' => $a->nama,
        'pct'  => (int) round(($filled / 8) * 100),
    ];
})->values();
@endphp

<script>
const CSRF       = '{{ csrf_token() }}';
const ENRICH_URL = '{{ route("api.enrichment.single") }}';



// ── Per-row update helper ─────────────────────────────────────────────────────
function updateRow(alumniId, res) {
    const pct    = res.enrichment_pct ?? 0;
    const source = res.source ?? 'generated';
    const color  = pct > 60 ? '#34d399' : pct > 25 ? '#fbbf24' : '#475569';

    const pctEl = document.getElementById('enr_pct_' + alumniId);
    const barEl = document.getElementById('enr_bar_' + alumniId);
    const srcEl = document.getElementById('enr_src_' + alumniId);

    if (pctEl) { pctEl.textContent = pct + '%'; pctEl.style.color = color; }
    if (barEl) { barEl.style.width = pct + '%'; barEl.style.background = color; }

    if (srcEl) {
        const srcColors = {
            scraped:   { bg: 'rgba(16,185,129,0.15)', text: '#34d399' },
            generated: { bg: 'rgba(251,191,36,0.12)', text: '#fbbf24' },
            manual:    { bg: 'rgba(59,130,246,0.15)', text: '#60a5fa' },
        };
        const c = srcColors[source] || srcColors['generated'];
        srcEl.style.cssText = `background:${c.bg}; color:${c.text}; font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px;`;
        srcEl.textContent   = source.toUpperCase();
    }
}

// ── Manual per-row button ─────────────────────────────────────────────────────
function doEnrich(alumniId, btn) {
    const origContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="enr-spinner"></span> Mencari...';

    fetch(ENRICH_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ alumni_id: alumniId }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const updated = (res.updated_fields || []).length;
            const source  = res.source ?? 'generated';
            updateRow(alumniId, res);
            const row = document.getElementById('row_' + alumniId);
            if (row) {
                row.style.background = 'rgba(168,85,247,0.07)';
                setTimeout(() => { row.style.background = 'transparent'; }, 2000);
            }
            showToast(
                source === 'scraped' ? `✅ ${updated} field ditemukan` : `🤖 ${updated} field di-generate`,
                `Metode: ${source === 'scraped' ? 'Scraping' : 'Smart Generator'} • ${res.enrichment_pct}%`,
                source === 'scraped' ? '#34d399' : '#fbbf24'
            );
            btn.innerHTML = '✓ Selesai';
            btn.style.color = '#34d399';
            btn.style.borderColor = 'rgba(52,211,153,0.3)';
            btn.style.background  = 'rgba(52,211,153,0.08)';
        } else {
            showToast('❌ Gagal', res.error || 'Terjadi kesalahan', '#f87171');
            btn.disabled = false;
            btn.innerHTML = origContent;
        }
    })
    .catch(err => {
        showToast('❌ Koneksi gagal', err.message, '#f87171');
        btn.disabled = false;
        btn.innerHTML = origContent;
    });
}

// ── Toast ─────────────────────────────────────────────────────────────────────
let toastTimer;
function showToast(msg, sub, color) {
    const toast = document.getElementById('enrichToast');
    const msgEl = document.getElementById('enrichToastMsg');
    const subEl = document.getElementById('enrichToastSub');
    msgEl.textContent  = msg;
    subEl.textContent  = sub;
    msgEl.style.color  = color || '#e2e8f0';
    toast.style.display = 'block';
    toast.style.animation = 'none';
    toast.offsetHeight;
    toast.style.animation = 'slideUp 0.3s ease';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.style.display = 'none'; }, 4000);
}
</script>

@endsection

