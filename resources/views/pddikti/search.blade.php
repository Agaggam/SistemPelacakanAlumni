@extends('layouts.app')

@section('title', 'Cari di PDDIKTI')
@section('page-title', '🔎 Cari Alumni di PDDIKTI')
@section('page-subtitle', 'Data real-time langsung dari pddikti.kemdikbud.go.id')

@section('content')

{{-- SEARCH BAR --}}
<div class="card" style="margin-bottom:24px;">
    <form method="GET" action="{{ route('pddikti.search') }}" style="display:flex; gap:12px; align-items:flex-end;">
        <div class="form-group" style="flex:1; margin:0;">
            <label class="form-label">Nama Mahasiswa / Alumni</label>
            <input type="text" name="q" class="form-control" value="{{ $keyword }}"
                placeholder="cth: Ahmad Fauzi, Budi Santoso, ..." autofocus autocomplete="off">
        </div>
        <button type="submit" class="btn btn-primary" style="height:44px; padding:0 24px;">🔍 Cari</button>
        @if($keyword)
            <a href="{{ route('pddikti.search') }}" class="btn btn-secondary" style="height:44px; padding:0 16px;">✕ Reset</a>
        @endif
    </form>
</div>

{{-- ERROR --}}
@if($error)
<div class="alert alert-danger">⚠️ {{ $error }}</div>
@endif

{{-- HASIL --}}
@if($searched)

{{-- FILTER / SUMMARY BAR --}}
@php
    $total  = count($results);
    $lulus  = collect($results)->where('is_lulus', true)->count();
    $aktif  = $total - $lulus;
    $filter = request('filter', 'semua');

    $displayed = $filter === 'lulus'
        ? collect($results)->where('is_lulus', true)->values()
        : collect($results);
@endphp

<div style="display:flex; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
    <span class="text-muted text-sm">{{ $total }} hasil untuk "<strong style="color:var(--text-primary)">{{ $keyword }}</strong>"</span>
    <div style="display:flex; gap:8px; margin-left:auto;">
        <a href="{{ request()->fullUrlWithQuery(['filter' => 'semua']) }}"
            class="btn btn-sm {{ $filter === 'semua' ? 'btn-primary' : 'btn-secondary' }}">
            Semua ({{ $total }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['filter' => 'lulus']) }}"
            class="btn btn-sm {{ $filter === 'lulus' ? 'btn-success' : 'btn-secondary' }}">
            🎓 Lulus ({{ $lulus }})
        </a>
    </div>
</div>

@if($displayed->isEmpty())
    <div class="card" style="text-align:center; padding:40px 20px;">
        <div style="font-size:40px; margin-bottom:12px;">🔍</div>
        <div style="font-size:16px; font-weight:600; margin-bottom:8px;">Tidak ada hasil ditemukan</div>
        <p class="text-muted text-sm">
            @if($filter === 'lulus')
                Tidak ada mahasiswa dengan status "Lulus" untuk pencarian ini.
                <a href="{{ request()->fullUrlWithQuery(['filter' => 'semua']) }}">Lihat semua hasil</a>
            @else
                PDDIKTI tidak mengembalikan hasil untuk "<strong>{{ $keyword }}</strong>". Coba kata kunci lain.
            @endif
        </p>
    </div>
@else

{{-- HASIL TABLE --}}
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Perguruan Tinggi</th>
                    <th>Prodi / Jenjang</th>
                    <th>Status Terakhir</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($displayed as $item)
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $item['nama'] }}</div>
                        @if($item['angkatan'])
                            <div class="text-muted" style="font-size:12px;">Angkatan {{ $item['angkatan'] }}</div>
                        @endif
                    </td>
                    <td><code style="font-size:13px; color:var(--accent-light)">{{ $item['nim'] }}</code></td>
                    <td style="font-size:13px;">{{ $item['pt'] }}</td>
                    <td style="font-size:13px;">
                        {{ $item['prodi'] }}
                        @if($item['jenjang'] && $item['jenjang'] !== '-')
                            <div class="text-muted" style="font-size:11px;">{{ $item['jenjang'] }}</div>
                        @endif
                    </td>
                    <td>
                        @if($item['is_lulus'])
                            <span class="badge badge-success">🎓 {{ $item['status'] }}</span>
                        @elseif($item['status'])
                            <span class="badge badge-secondary">{{ $item['status'] }}</span>
                        @else
                            <span class="text-muted" style="font-size:12px;">-</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($item['id'])
                            <a href="{{ route('pddikti.detail', $item['id']) }}"
                                class="btn btn-sm btn-outline">📄 Detail</a>
                        @else
                            <span class="text-muted text-sm">No ID</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif {{-- empty --}}
@endif {{-- searched --}}

{{-- LANDING STATE --}}
@if(!$searched)
<div class="card" style="text-align:center; padding:40px 20px;">
    <div style="font-size:52px; margin-bottom:16px;">🎓</div>
    <div style="font-size:19px; font-weight:700; margin-bottom:10px;">Cari Alumni via PDDIKTI</div>
    <p class="text-muted text-sm" style="max-width:440px; margin:0 auto 24px;">
        Ketikkan nama mahasiswa di atas untuk mencari data real-time dari
        <strong style="color:var(--accent-light)">Pangkalan Data Pendidikan Tinggi</strong> (pddikti.kemdikbud.go.id).
    </p>
    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
        <div style="background:var(--bg-hover); border-radius:12px; padding:16px 20px; text-align:left; min-width:220px;">
            <div style="font-size:22px; margin-bottom:8px;">🌐</div>
            <div style="font-weight:600; margin-bottom:4px;">Data Real-Time</div>
            <div class="text-muted text-sm">Langsung dari server PDDIKTI Kemdikbud</div>
        </div>
        <div style="background:var(--bg-hover); border-radius:12px; padding:16px 20px; text-align:left; min-width:220px;">
            <div style="font-size:22px; margin-bottom:8px;">🎓</div>
            <div style="font-weight:600; margin-bottom:4px;">Status Terakhir</div>
            <div class="text-muted text-sm">Termasuk "Lulus", "Aktif", "Cuti", dsb.</div>
        </div>
        <div style="background:var(--bg-hover); border-radius:12px; padding:16px 20px; text-align:left; min-width:220px;">
            <div style="font-size:22px; margin-bottom:8px;">💾</div>
            <div style="font-weight:600; margin-bottom:4px;">Simpan ke Tracking</div>
            <div class="text-muted text-sm">Tandai alumni untuk dipantau</div>
        </div>
    </div>
</div>
@endif

@endsection
