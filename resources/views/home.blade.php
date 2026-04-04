@extends('layouts.public')

@section('title', 'Selamat Datang')

@section('content')
<div class="container" style="max-width: 1200px; padding: 100px 20px;">
    <div class="text-center mb-12" style="margin-bottom: 60px;">
        <h1 style="font-size: 48px; font-weight: 800; background: linear-gradient(to right, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 20px;">
            Sistem Pelacakan Alumni
        </h1>
        <p style="font-size: 18px; color: var(--muted); max-width: 600px; margin: 0 auto;">
            Pilih layanan pelacakan yang Anda butuhkan untuk memulai pencarian mahasiswa dan alumni.
        </p>
    </div>

    <div class="grid-2 mt-40">
        {{-- CARD 1: CARI MAHASISWA (PDDIKTI) - OFFICIAL --}}
        <a href="{{ route('search') }}" class="gate-card theme-blue">
            <div class="gate-badge">LAYANAN DATA RESMI</div>
            <div class="gate-icon">🌐</div>
            <h3 class="gate-title">Cari Mahasiswa</h3>
            <p class="gate-desc">
                Portal verifikasi status mahasiswa secara **Real-Time**. Sumber data terintegrasi langsung dengan server pusat **PDDIKTI**.
            </p>
            <div class="gate-action">
                <span>Mulai Pencarian</span>
                <span class="arrow">→</span>
            </div>
        </a>

        {{-- CARD 2: TRACKING MAHASISWA UMM (LOCAL) - ENRICHMENT --}}
        <a href="{{ route('alumni_umm.tracking') }}" class="gate-card theme-purple">
            <div class="gate-badge alt">LAYANAN PENGAYAAN DATA</div>
            <div class="gate-icon">🚀</div>
            <h3 class="gate-title">Tracking Alumni UMM</h3>
            <p class="gate-desc">
                Sistem pelacakan alumni khusus **UMM**. Fokus pada data **Enrichment** seperti Sosial Media, Email, dan Karir Profesional.
            </p>
            <div class="gate-action">
                <span>Buka Panel Tracking</span>
                <span class="arrow">→</span>
            </div>
        </a>
    </div>
</div>

{{-- MODAL PENGUMUMAN --}}
<div id="announcementModal" class="modal-overlay open">
    <div class="modal-content">
        <div class="rocket-icon">🚀</div>
        <h2 class="modal-title">Pengumuman</h2>
        <p class="modal-text">
            Website ini masih dalam tahap <strong>Demo & Pengembangan.</strong>
        </p>
        <button onclick="closeModal()" class="modal-btn">Oke</button>
    </div>
</div>

<style>
    .grid-2 {
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    @media (min-width: 768px) {
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
    }

    .mt-40 { margin-top: 40px; }

    .gate-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 40px;
        padding: 60px 40px;
        text-align: center;
        text-decoration: none;
        color: var(--text);
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    .theme-blue:hover {
        border-color: #3b82f6;
        box-shadow: 0 30px 60px rgba(59, 130, 246, 0.15);
        transform: translateY(-12px);
    }
    
    .theme-purple:hover {
        border-color: #a855f7;
        box-shadow: 0 30px 60px rgba(168, 85, 247, 0.15);
        transform: translateY(-12px);
    }

    .gate-badge {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2px;
        padding: 8px 20px;
        border-radius: 100px;
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
        margin-bottom: 30px;
        text-transform: uppercase;
    }
    
    .gate-badge.alt {
        background: rgba(168, 85, 247, 0.1);
        color: #d8b4fe;
    }

    .gate-icon {
        font-size: 72px;
        margin-bottom: 30px;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
    }

    .gate-title {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 20px;
        letter-spacing: -0.5px;
    }

    .gate-desc {
        font-size: 16px;
        color: var(--muted);
        line-height: 1.7;
        margin-bottom: 40px;
        max-width: 320px;
    }

    .gate-action {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 700;
        opacity: 0.8;
        transition: all 0.3s;
    }
    
    .gate-card:hover .gate-action {
        opacity: 1;
        gap: 18px;
    }
    
    .theme-blue .gate-action { color: #60a5fa; }
    .theme-purple .gate-action { color: #d8b4fe; }
    
    .arrow {
        font-size: 20px;
        transition: transform 0.3s;
    }
    .gate-card:hover .arrow {
        transform: translateX(5px);
    }

    /* MODAL STYLES */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(10px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        padding: 20px;
    }
    
    .modal-overlay.open {
        display: flex;
    }

    .modal-content {
        background: #1e2235;
        border: 1px solid #2d3154;
        border-radius: 35px;
        padding: 50px 40px;
        text-align: center;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 40px 100px rgba(0,0,0,0.6);
        animation: modalAppear 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes modalAppear {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    .rocket-icon {
        font-size: 80px;
        margin-bottom: 25px;
        line-height: 1;
    }

    .modal-title {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 15px;
        color: white;
        letter-spacing: -0.5px;
    }

    .modal-text {
        font-size: 18px;
        color: #94a3b8;
        line-height: 1.6;
        margin-bottom: 35px;
    }

    .modal-btn {
        background: #6366f1;
        color: white;
        border: none;
        padding: 14px 60px;
        border-radius: 16px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        width: auto;
    }

    .modal-btn:hover {
        background: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    .modal-btn:active {
        transform: translateY(0);
    }
</style>

@push('scripts')
<script>
    function closeModal() {
        const modal = document.getElementById('announcementModal');
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            modal.classList.remove('open');
        }, 300);
    }

    // Optional: Close on Esc key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
</script>
@endpush
@endsection
