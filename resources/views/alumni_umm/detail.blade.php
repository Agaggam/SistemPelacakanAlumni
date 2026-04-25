@extends('layouts.public')

@section('title', 'Detail Alumni — ' . $searchNama)

@section('content')

{{-- BACK --}}
<div style="margin-bottom: 24px; display: flex; gap: 12px;">
    <a href="{{ url()->previous() }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #94a3b8; font-size: 13px; font-weight: 600; padding: 10px 18px; border-radius: 10px; border: 1px solid var(--border); background: var(--card);">
        ← Kembali
    </a>
</div>

@if($alumni)
{{-- ═══ ALUMNI FOUND — SHOW DATA ═══ --}}
@php
    $enrichedCount = collect(['linkedin','instagram','facebook','tiktok','email','no_hp','tempat_kerja','posisi'])->filter(fn($f) => !empty($alumni->$f))->count();
    $enrichPercent = round(($enrichedCount / 8) * 100);
    $needsScraping = $enrichedCount < 4; // Auto-scrape if less than half is filled
@endphp

{{-- PROFILE HEADER --}}
<div style="background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 32px; margin-bottom: 24px; display: flex; align-items: center; gap: 24px;">
    <div style="width: 72px; height: 72px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; color: white; flex-shrink: 0;">
        {{ strtoupper(substr($alumni->nama, 0, 1)) }}
    </div>
    <div style="flex: 1;">
        <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 4px;">{{ $alumni->nama }}</h1>
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; font-size: 13px; color: #94a3b8;">
            <span style="color: #818cf8; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ $alumni->nim ?? '-' }}</span>
            <span>•</span>
            <span>{{ $alumni->prodi ?? '-' }}</span>
            <span>•</span>
            <span>{{ $alumni->fakultas ?? '-' }}</span>
        </div>
    </div>
    <div style="flex-shrink: 0; text-align: right;">
        <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Enrichment</div>
        <div id="enrichPercent" style="font-size: 24px; font-weight: 800; color: {{ $enrichPercent > 50 ? '#34d399' : ($enrichPercent > 0 ? '#fbbf24' : '#64748b') }};">{{ $enrichPercent }}%</div>
    </div>
</div>

{{-- AUTO-SCRAPING BANNER --}}
<div id="scrapingBanner" style="display: none; background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.08)); border: 1px solid rgba(99,102,241,0.2); border-radius: 16px; padding: 18px 24px; margin-bottom: 24px; animation: slideDown 0.4s ease;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <div id="scrapingSpinner" style="width: 24px; height: 24px; border-radius: 50%; border: 3px solid rgba(129,140,248,0.2); border-top-color: #818cf8; animation: spin 0.7s linear infinite; flex-shrink: 0;"></div>
        <div style="flex: 1;">
            <div style="font-size: 14px; font-weight: 700; color: #818cf8; margin-bottom: 2px;" id="scrapingTitle">🔍 Auto-Scraping sedang berjalan...</div>
            <div style="font-size: 12px; color: #94a3b8;" id="scrapingSubtitle">Mencari data dari LinkedIn, Instagram, Facebook, GitHub, dan platform lainnya</div>
        </div>
        <div id="scrapingPlatformChips" style="display: flex; flex-wrap: wrap; gap: 6px;"></div>
    </div>
    {{-- Progress bar --}}
    <div style="margin-top: 12px; height: 4px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden;">
        <div id="scrapingProgressBar" style="height: 100%; width: 0%; background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899); border-radius: 4px; transition: width 0.5s ease;"></div>
    </div>
</div>

{{-- TWO-COLUMN LAYOUT --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    {{-- LEFT: ENRICHMENT DATA --}}
    <div style="display: flex; flex-direction: column; gap: 20px;">
        {{-- SOCIAL MEDIA --}}
        <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                Jejaring Sosial
            </h3>
            @php
                $socials = [
                    ['<img src="https://cdn.simpleicons.org/linkedin/0077b5" style="width:20px;height:20px;" alt="LI">', 'LinkedIn', $alumni->linkedin, true, 'linkedin'],
                    ['<img src="https://cdn.simpleicons.org/instagram/e4405f" style="width:20px;height:20px;" alt="IG">', 'Instagram', $alumni->instagram, false, 'instagram'],
                    ['<img src="https://cdn.simpleicons.org/facebook/1877f2" style="width:20px;height:20px;" alt="FB">', 'Facebook', $alumni->facebook, false, 'facebook'],
                    ['<img src="https://cdn.simpleicons.org/tiktok/ffffff" style="width:20px;height:20px;" alt="TK">', 'TikTok', $alumni->tiktok, false, 'tiktok'],
                ];
            @endphp
            @foreach($socials as $s)
            <div style="display: flex; align-items: center; gap: 14px; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <span style="display:flex; align-items:center; justify-content:center; width: 24px;">{!! $s[0] !!}</span>
                <div style="flex: 1;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 2px;">{{ $s[1] }}</div>
                    <span id="field_{{ $s[4] }}" style="font-size: 13px; color: {{ $s[2] ? '#e2e8f0' : '#475569' }}; transition: all 0.4s;">
                        @if($s[2])
                            @if($s[3])
                            <a href="{{ $s[2] }}" target="_blank" style="color: #818cf8; text-decoration: none; word-break: break-all;">{{ $s[2] }}</a>
                            @else
                            {{ $s[2] }}
                            @endif
                        @else
                            <span class="empty-placeholder">-</span>
                        @endif
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- CONTACT --}}
        <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                Kontak
            </h3>
            @foreach([['<img src="https://cdn.simpleicons.org/gmail/ea4335" style="width:20px;height:20px;" alt="GM">', 'Email', $alumni->email, 'email'], ['<img src="https://cdn.simpleicons.org/whatsapp/25D366" style="width:20px;height:20px;" alt="WA">', 'No HP / WhatsApp', $alumni->no_hp, 'no_hp']] as $c)
            <div style="display: flex; align-items: center; gap: 14px; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <span style="display:flex; align-items:center; justify-content:center; width: 24px;">{!! $c[0] !!}</span>
                <div style="flex: 1;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 2px;">{{ $c[1] }}</div>
                    <span id="field_{{ $c[3] }}" style="font-size: 13px; color: {{ $c[2] ? '#e2e8f0' : '#475569' }}; transition: all 0.4s;">{{ $c[2] ?? '-' }}</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- WORK --}}
        <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path></svg> 
                Pekerjaan
            </h3>
            
            @php
            $pekerjaan = [
                ['<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path></svg>', 'Tempat Kerja', $alumni->tempat_kerja, 'tempat_kerja'],
                ['<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>', 'Alamat Kerja', $alumni->alamat_kerja, 'alamat_kerja'],
                ['<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>', 'Posisi', $alumni->posisi, 'posisi'],
                ['<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>', 'Status', $alumni->status_kerja, 'status_kerja'],
                ['<img src="https://cdn.simpleicons.org/googlechrome/6366f1" style="width:18px;height:18px;" alt="Web">', 'Sosmed Perusahaan', $alumni->sosmed_perusahaan, 'sosmed_perusahaan']
            ];
            @endphp

            @foreach($pekerjaan as $w)
            <div style="display: flex; align-items: center; gap: 14px; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <span style="display:flex;align-items:center;justify-content:center; width: 24px;">{!! $w[0] !!}</span>
                <div style="flex: 1;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 2px;">{{ $w[1] }}</div>
                    <span id="field_{{ $w[3] }}" style="font-size: 13px; color: {{ $w[2] ? '#e2e8f0' : '#475569' }}; transition: all 0.4s;">{{ $w[2] ?? '-' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- RIGHT: SCRAPING RESULTS + AKADEMIK + EDIT --}}
    <div style="display: flex; flex-direction: column; gap: 20px;">

        {{-- 🔍 SCRAPING RESULTS PANEL --}}
        <div id="scrapingResultsPanel" style="display: none; background: var(--card); border: 1px solid rgba(168,85,247,0.2); border-radius: 16px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Hasil Auto-Scraping
            </h3>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 16px;">Data ditemukan dari berbagai platform dan otomatis disimpan.</p>
            <div id="scrapingResultsList" style="display: flex; flex-direction: column; gap: 8px;"></div>
        </div>

        {{-- AKADEMIK INFO --}}
        <div style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                Data Akademik
            </h3>

            @php
            $akademik = [
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect><line x1="15" y1="9" x2="15.01" y2="9"></line><line x1="9" y1="9" x2="9.01" y2="9"></line><path d="M12 15a4 4 0 0 0-4-4h8a4 4 0 0 0-4 4z"></path></svg>', 'NIM', $alumni->nim],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>', 'Program Studi', $alumni->prodi],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="20" width="20" height="2"></rect><polygon points="12 2 2 7 22 7"></polygon><line x1="6" y1="7" x2="6" y2="20"></line><line x1="18" y1="7" x2="18" y2="20"></line><line x1="12" y1="7" x2="12" y2="20"></line></svg>', 'Fakultas', $alumni->fakultas],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>', 'Tahun Masuk', $alumni->tahun_masuk],
                ['<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>', 'Tanggal Lulus', $alumni->tanggal_lulus]
            ];
            @endphp
            
            @foreach($akademik as $a)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; color: #94a3b8; font-size: 13px;">
                    <span style="display:flex;align-items:center;justify-content:center; width: 20px;">{!! $a[0] !!}</span> {{ $a[1] }}
                </div>
                <div style="font-weight: 600; font-size: 13px; color: #e2e8f0;">{{ $a[2] ?? '-' }}</div>
            </div>
            @endforeach
        </div>

        {{-- RE-SCRAPE BUTTON --}}
        @auth
        <button onclick="runEnrichment(true)" class="btn btn-primary" id="btnRescrape" style="width: 100%; border-radius: 12px; padding: 14px; font-weight: 700; justify-content: center; background: linear-gradient(135deg, #6366f1, #8b5cf6); border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            🔍 Scrape Ulang
        </button>
        @endauth

        {{-- EDIT BUTTON (Admin only) --}}
        @auth
        @if(auth()->user()->isAdmin())
        <button onclick="document.getElementById('editPanel').style.display = document.getElementById('editPanel').style.display === 'none' ? 'block' : 'none'" class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 14px; font-weight: 700; justify-content: center; background: #6366f1; border: none; cursor: pointer; font-size: 14px;">
            ✏️ Edit Data Enrichment
        </button>

        <div id="editPanel" style="display: none; background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 20px;">📝 Edit Data Pengayaan</h3>
            <form action="{{ route('alumni_umm.update', $alumni->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="nama" value="{{ $alumni->nama }}">
                @php
                    $editFields = [
                        ['linkedin', 'LinkedIn URL', 'url', $alumni->linkedin],
                        ['instagram', 'Instagram', 'text', $alumni->instagram],
                        ['facebook', 'Facebook', 'text', $alumni->facebook],
                        ['tiktok', 'TikTok', 'text', $alumni->tiktok],
                        ['email', 'Email', 'email', $alumni->email],
                        ['no_hp', 'No HP / WhatsApp', 'text', $alumni->no_hp],
                        ['tempat_kerja', 'Tempat Kerja', 'text', $alumni->tempat_kerja],
                        ['alamat_kerja', 'Alamat Kerja', 'text', $alumni->alamat_kerja],
                        ['posisi', 'Posisi', 'text', $alumni->posisi],
                        ['status_kerja', 'Status Kerja', 'select', $alumni->status_kerja],
                        ['sosmed_perusahaan', 'Sosmed Perusahaan', 'text', $alumni->sosmed_perusahaan],
                    ];
                @endphp
                @foreach($editFields as $ef)
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">{{ $ef[1] }}</label>
                    @if($ef[2] === 'select')
                    <select name="{{ $ef[0] }}" style="width: 100%; padding: 10px 14px; background: #0f1117; border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; font-family: 'Inter', sans-serif;">
                        <option value="">— Pilih —</option>
                        @foreach(['PNS', 'Swasta', 'Wirausaha', 'BUMN'] as $opt)
                        <option value="{{ $opt }}" {{ $ef[3] == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="{{ $ef[2] }}" name="{{ $ef[0] }}" value="{{ $ef[3] }}" placeholder="{{ $ef[1] }}..."
                        style="width: 100%; padding: 10px 14px; background: #0f1117; border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; font-family: 'Inter', sans-serif;">
                    @endif
                </div>
                @endforeach
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: 10px; font-weight: 700; margin-top: 10px; border: none; cursor: pointer;">
                    💾 Simpan Perubahan
                </button>
            </form>
        </div>
        @endif
        @endauth
    </div>
</div>

@else
{{-- ═══ NOT FOUND ═══ --}}
<div style="background: var(--card); border: 1px dashed var(--border); border-radius: 20px; padding: 60px 30px; text-align: center;">
    <div style="font-size: 56px; margin-bottom: 16px;">🔍</div>
    <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 10px;">Data Tidak Ditemukan</h2>
    <p style="color: #94a3b8; font-size: 14px; margin-bottom: 24px;">
        Data enrichment untuk <strong style="color: #818cf8;">"{{ $searchNama }}"</strong> belum ada di database.
    </p>
    @auth
    @if(auth()->user()->isAdmin())
    <a href="#" onclick="document.getElementById('createForm').style.display='block'; this.parentElement.style.display='none'; return false;" class="btn btn-primary" style="padding: 12px 30px; border-radius: 10px; text-decoration: none;">
        ➕ Tambah Data Manual
    </a>
    @endif
    @endauth
</div>

@auth
@if(auth()->user()->isAdmin())
<div id="createForm" style="display: none; margin-top: 24px; background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px;">📝 Tambah Data Enrichment Baru</h3>
    <form action="{{ route('alumni_umm.store') }}" method="POST">
        @csrf
        <input type="hidden" name="nama" value="{{ $searchNama }}">
        @php
            $createFields = [
                ['nim', 'NIM', 'text'],
                ['prodi', 'Program Studi', 'text'],
                ['linkedin', 'LinkedIn URL', 'url'],
                ['instagram', 'Instagram', 'text'],
                ['facebook', 'Facebook', 'text'],
                ['tiktok', 'TikTok', 'text'],
                ['email', 'Email', 'email'],
                ['no_hp', 'No HP', 'text'],
                ['tempat_kerja', 'Tempat Kerja', 'text'],
                ['alamat_kerja', 'Alamat Kerja', 'text'],
                ['posisi', 'Posisi', 'text'],
                ['sosmed_perusahaan', 'Sosmed Perusahaan', 'text'],
            ];
        @endphp
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        @foreach($createFields as $cf)
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">{{ $cf[1] }}</label>
                <input type="{{ $cf[2] }}" name="{{ $cf[0] }}" placeholder="{{ $cf[1] }}..."
                    style="width: 100%; padding: 10px; background: #0f1117; border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; font-family: 'Inter', sans-serif;">
            </div>
        @endforeach
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">Status Kerja</label>
                <select name="status_kerja" style="width: 100%; padding: 10px; background: #0f1117; border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; font-family: 'Inter', sans-serif;">
                    <option value="">— Pilih —</option>
                    <option>PNS</option><option>Swasta</option><option>Wirausaha</option><option>BUMN</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; border-radius: 10px; font-weight: 700; margin-top: 20px; border: none; cursor: pointer;">💾 Simpan Data</button>
    </form>
</div>
@endif
@endauth
@endif

<style>
    .loading-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; border: 2px solid #475569; border-top-color: #818cf8; animation: spin 0.7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fieldPop {
        0% { background: rgba(99,102,241,0.2); }
        100% { background: transparent; }
    }
    .field-updated {
        animation: fieldPop 2s ease;
        color: #34d399 !important;
        font-weight: 600;
    }
    .scrape-result-chip {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
        border-radius: 10px; font-size: 12px; transition: all 0.3s;
    }
    .scrape-result-chip.found { border-color: rgba(16,185,129,0.2); background: rgba(16,185,129,0.05); }
    .scrape-result-chip.not-found { border-color: rgba(100,116,139,0.15); }
    @media (max-width: 768px) { div[style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; } }
</style>

@if($alumni)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ALUMNI_ID   = {{ $alumni->id }};
    const ALUMNI_NAMA = @json($alumni->nama);
    const CSRF        = '{{ csrf_token() }}';
    const ENRICH_URL  = '{{ route("api.enrichment.single") }}';

    // ── Auto-run enrichment on every page load ──────────────────────────────
    // (will only fill empty fields unless force=true)
    setTimeout(() => runEnrichment(false), 400);

    /**
     * Main enrichment runner.
     * force=false → fill empty fields only (normal Cari Data)
     * force=true  → re-scrape & overwrite all fields (Scrape Ulang)
     */
    window.runEnrichment = function(force) {
        const banner     = document.getElementById('scrapingBanner');
        const btnRescrape = document.getElementById('btnRescrape');

        // Reset & show banner
        banner.style.display  = 'block';
        banner.style.opacity  = '1';
        document.getElementById('scrapingSpinner').style.display = 'block';
        document.getElementById('scrapingTitle').innerHTML   = force
            ? '🔄 Scraping ulang sedang berjalan...'
            : '🔍 Cari data alumni sedang berjalan...';
        document.getElementById('scrapingSubtitle').textContent = 'Mencari dari Bing, Yahoo, dan database internal...';
        document.getElementById('scrapingProgressBar').style.width = '0%';

        if (btnRescrape) {
            btnRescrape.disabled = true;
            btnRescrape.innerHTML = '<div class="loading-dot"></div> ' + (force ? 'Scraping...' : 'Mencari...');
        }

        // Animate progress bar
        let prog = 0;
        const progInterval = setInterval(() => {
            prog = Math.min(prog + Math.random() * 8, 92);
            document.getElementById('scrapingProgressBar').style.width = prog + '%';
        }, 500);

        fetch(ENRICH_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ alumni_id: ALUMNI_ID, force: force }),
        })
        .then(r => r.json())
        .then(res => {
            clearInterval(progInterval);
            document.getElementById('scrapingProgressBar').style.width = '100%';
            document.getElementById('scrapingSpinner').style.display = 'none';

            if (res.success) {
                const updated = (res.updated_fields || []).length;
                const source  = res.source || 'generated';
                const pct     = res.enrichment_pct ?? 0;
                const fromCache = res.from_cache ? ' <span style="color:#fbbf24">(cache)</span>' : '';

                const srcLabel = source === 'scraped' ? '✅ Scraping nyata' : '🤖 Smart Generator';
                document.getElementById('scrapingTitle').innerHTML =
                    force ? `🔄 Scrape ulang selesai — ${srcLabel}` : `✅ Cari data selesai — ${srcLabel}`;
                document.getElementById('scrapingSubtitle').innerHTML =
                    `${updated} field diperbarui &bull; Enrichment: <strong style="color:#818cf8">${pct}%</strong>${fromCache}`;

                // ── Update field displays on page ─────────────────────────
                const alumniData = res.alumni || {};
                const updatedFields = res.updated_fields || [];

                const FIELD_MAP = [
                    'linkedin','instagram','facebook','tiktok',
                    'email','no_hp','tempat_kerja','alamat_kerja',
                    'posisi','status_kerja','sosmed_perusahaan',
                ];

                FIELD_MAP.forEach(key => {
                    const val     = alumniData[key];
                    const el      = document.getElementById('field_' + key);
                    const inputEl = document.querySelector(`input[name="${key}"]`);
                    const selEl   = document.querySelector(`select[name="${key}"]`);

                    if (val) {
                        if (inputEl) inputEl.value = val;
                        if (selEl)   selEl.value   = val;

                        if (updatedFields.includes(key) && el) {
                            if (key === 'linkedin' && String(val).startsWith('http')) {
                                el.innerHTML = `<a href="${escHtml(val)}" target="_blank" style="color:#818cf8;text-decoration:none;word-break:break-all;">${escHtml(val)}</a>`;
                            } else {
                                el.textContent = val;
                            }
                            el.style.color = '#e2e8f0';
                            el.classList.add('field-updated');
                        }
                    }
                });

                // ── Update enrichment % header ────────────────────────────
                const pctEl = document.getElementById('enrichPercent');
                if (pctEl) {
                    pctEl.textContent = pct + '%';
                    pctEl.style.color = pct > 50 ? '#34d399' : pct > 0 ? '#fbbf24' : '#64748b';
                }

                // ── Show source badge ─────────────────────────────────────
                const srcBadge = document.getElementById('sourceBadge');
                if (srcBadge) {
                    const srcColors = {
                        scraped:   { bg: 'rgba(16,185,129,0.15)', text: '#34d399', label: 'SCRAPED' },
                        generated: { bg: 'rgba(251,191,36,0.12)', text: '#fbbf24', label: 'GENERATED' },
                        manual:    { bg: 'rgba(59,130,246,0.15)', text: '#60a5fa', label: 'MANUAL' },
                    };
                    const c = srcColors[source] || srcColors['generated'];
                    srcBadge.style.background = c.bg;
                    srcBadge.style.color      = c.text;
                    srcBadge.textContent      = c.label;
                    srcBadge.style.display    = 'inline-block';
                }

                // ── Show results panel ────────────────────────────────────
                showEnrichResults(alumniData, updatedFields, source);

            } else {
                document.getElementById('scrapingTitle').innerHTML   = '⚠️ Gagal mendapatkan data';
                document.getElementById('scrapingSubtitle').textContent = res.error || 'Terjadi kesalahan';
            }
        })
        .catch(err => {
            clearInterval(progInterval);
            document.getElementById('scrapingSpinner').style.display = 'none';
            document.getElementById('scrapingTitle').innerHTML   = '❌ Koneksi gagal';
            document.getElementById('scrapingSubtitle').textContent = err.message;
        })
        .finally(() => {
            if (btnRescrape) {
                btnRescrape.disabled = false;
                btnRescrape.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> 🔍 Scrape Ulang`;
            }
            setTimeout(() => {
                document.getElementById('scrapingBanner').style.opacity = '0.7';
            }, 10000);
        });
    };

    // Backward-compat: detail page used to call runAutoScrape
    window.runAutoScrape = (force) => window.runEnrichment(force);

    // ── Show enrichment results panel ─────────────────────────────────────────
    function showEnrichResults(alumniData, updatedFields, source) {
        const panel = document.getElementById('scrapingResultsPanel');
        const list  = document.getElementById('scrapingResultsList');
        if (!panel || !list) return;
        panel.style.display = 'block';

        const ICONS = {
            linkedin:          { src: 'https://cdn.simpleicons.org/linkedin/0077b5',     label: 'LinkedIn' },
            instagram:         { src: 'https://cdn.simpleicons.org/instagram/e4405f',    label: 'Instagram' },
            facebook:          { src: 'https://cdn.simpleicons.org/facebook/1877f2',     label: 'Facebook' },
            tiktok:            { src: 'https://cdn.simpleicons.org/tiktok/ffffff',        label: 'TikTok' },
            email:             { src: 'https://cdn.simpleicons.org/gmail/ea4335',         label: 'Email' },
            no_hp:             { src: 'https://cdn.simpleicons.org/whatsapp/25D366',      label: 'No HP' },
            tempat_kerja:      { src: 'https://cdn.simpleicons.org/google/f59e0b',        label: 'Tempat Kerja' },
            posisi:            { src: 'https://cdn.simpleicons.org/googlechrome/6366f1',  label: 'Posisi' },
            status_kerja:      { src: 'https://cdn.simpleicons.org/notion/f1f5f9',        label: 'Status Kerja' },
            alamat_kerja:      { src: 'https://cdn.simpleicons.org/googlemaps/ea4335',    label: 'Alamat Kerja' },
            sosmed_perusahaan: { src: 'https://cdn.simpleicons.org/googlechrome/818cf8', label: 'Sosmed Perusahaan' },
        };

        const order = ['linkedin','instagram','facebook','tiktok','email','no_hp','tempat_kerja','alamat_kerja','posisi','status_kerja','sosmed_perusahaan'];
        let html = '';

        order.forEach(key => {
            const val     = alumniData[key];
            const isFound = !!val;
            const icon    = ICONS[key] || { src: '', label: key };
            const isNew   = updatedFields.includes(key);

            html += `<div class="scrape-result-chip ${isFound ? 'found' : 'not-found'}">
                <img src="${icon.src}" style="width:16px;height:16px" alt="">
                <span style="flex:1;font-weight:600;color:${isFound ? '#e2e8f0' : '#64748b'}">${icon.label}</span>
                ${isFound
                    ? `<span style="color:#34d399;font-weight:700;font-size:11px">✓ ${isNew ? 'Diisi' : 'Ada'}</span>`
                    : `<span style="color:#475569;font-size:11px">—</span>`}
            </div>`;
        });

        list.innerHTML = html;
    }

    function escHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }
});
</script>
@endif

@endsection

