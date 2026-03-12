@extends('layouts.app')

@section('title', 'Data Alumni — Sistem Pelacakan Alumni')
@section('page-title', 'Data Alumni')
@section('page-subtitle', 'Kelola data dan pelacakan alumni')

@section('content')
<!-- FILTER BAR -->
<div class="card">
    <form method="GET" action="{{ route('alumni.index') }}">
        <div class="search-filters">
            <input type="text" name="search" class="form-control"
                placeholder="🔎 Cari nama, NIM, atau prodi..."
                value="{{ request('search') }}">
            <select name="status" class="form-control" style="min-width:200px">
                <option value="">Semua Status</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <select name="angkatan" class="form-control" style="min-width:130px">
                <option value="">Semua Angkatan</option>
                @foreach($angkatanList as $a)
                    <option value="{{ $a }}" {{ request('angkatan') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search','status','angkatan']))
                <a href="{{ route('alumni.index') }}" class="btn btn-secondary">Reset</a>
            @endif
            <div style="margin-left:auto; display:flex; gap:8px;">
                <a href="{{ route('alumni.create') }}" class="btn btn-success">➕ Tambah Alumni</a>
                <form action="{{ route('tracking.batch') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Lacak semua alumni yang belum dilacak?')">
                        🚀 Lacak Semua
                    </button>
                </form>
            </div>
        </div>
    </form>
</div>

<!-- TABLE -->
<div class="card" style="margin-bottom:0">
    <div class="card-header">
        <div class="card-title">Daftar Alumni ({{ $alumni->total() }} data)</div>
    </div>
    @if($alumni->isEmpty())
        <p class="text-muted" style="text-align:center; padding:40px">
            Tidak ada data alumni ditemukan.
        </p>
    @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIM</th>
                        <th>Nama Alumni</th>
                        <th>Program Studi</th>
                        <th>Angkatan</th>
                        <th>Status Pelacakan</th>
                        <th>Skor</th>
                        <th>Terakhir Dilacak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alumni as $i => $a)
                    <tr>
                        <td class="text-muted">{{ $alumni->firstItem() + $i }}</td>
                        <td class="font-mono" style="font-size:13px">{{ $a->nim }}</td>
                        <td>
                            <a href="{{ route('alumni.show', $a) }}" style="color:var(--accent-light); text-decoration:none; font-weight:600;">
                                {{ $a->nama }}
                            </a>
                        </td>
                        <td>
                            <div style="font-size:13px">{{ $a->prodi }}</div>
                            @if($a->fakultas)
                                <div class="text-muted" style="font-size:11px">{{ $a->fakultas }}</div>
                            @endif
                        </td>
                        <td>{{ $a->angkatan }}</td>
                        <td>
                            <span class="badge {{ $a->status_badge_class }}">{{ $a->status }}</span>
                        </td>
                        <td>
                            @if($a->skor_kecocokan !== null)
                                <span style="font-weight:700; color:{{ $a->skor_kecocokan >= 0.75 ? '#34d399' : ($a->skor_kecocokan >= 0.45 ? '#fbbf24' : '#f87171') }}">
                                    {{ $a->skor_persen }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:12px">
                            {{ $a->last_tracked_at ? $a->last_tracked_at->diffForHumans() : 'Belum pernah' }}
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('alumni.show', $a) }}" class="btn btn-sm btn-outline" title="Detail">👁</a>
                                <form action="{{ route('tracking.single', $a) }}" method="POST" style="margin:0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" title="Run Tracking">🔍</button>
                                </form>
                                <a href="{{ route('alumni.edit', $a) }}" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
                                <form action="{{ route('alumni.destroy', $a) }}" method="POST" style="margin:0;"
                                    onsubmit="return confirm('Hapus alumni {{ $a->nama }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">
            {{ $alumni->links() }}
        </div>
    @endif
</div>
@endsection
