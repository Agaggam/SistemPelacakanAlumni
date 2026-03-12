@extends('layouts.app')

@section('title', 'Edit Alumni — ' . $alumni->nama)
@section('page-title', 'Edit Alumni')
@section('page-subtitle', $alumni->nama . ' | NIM: ' . $alumni->nim)

@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('alumni.show', $alumni) }}" class="btn btn-secondary btn-sm">← Kembali</a>
</div>

<div class="card" style="max-width:750px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">✏️ Edit Data Alumni</div>
        <span class="badge {{ $alumni->status_badge_class }}">{{ $alumni->status }}</span>
    </div>
    <form action="{{ route('alumni.update', $alumni) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nim">NIM <span style="color:var(--danger)">*</span></label>
                <input type="text" id="nim" name="nim" class="form-control"
                    value="{{ old('nim', $alumni->nim) }}" required>
                @error('nim')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="nama">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control"
                    value="{{ old('nama', $alumni->nama) }}" required>
                @error('nama')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="prodi">Program Studi <span style="color:var(--danger)">*</span></label>
                <input type="text" id="prodi" name="prodi" class="form-control"
                    value="{{ old('prodi', $alumni->prodi) }}" required>
                @error('prodi')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="fakultas">Fakultas</label>
                <input type="text" id="fakultas" name="fakultas" class="form-control"
                    value="{{ old('fakultas', $alumni->fakultas) }}">
                @error('fakultas')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="angkatan">Angkatan <span style="color:var(--danger)">*</span></label>
                <input type="number" id="angkatan" name="angkatan" class="form-control"
                    value="{{ old('angkatan', $alumni->angkatan) }}" min="1990" max="{{ date('Y') }}" required>
                @error('angkatan')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="tahun_lulus">Tahun Lulus</label>
                <input type="number" id="tahun_lulus" name="tahun_lulus" class="form-control"
                    value="{{ old('tahun_lulus', $alumni->tahun_lulus) }}" min="1990" max="{{ date('Y') + 1 }}">
                @error('tahun_lulus')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="{{ old('email', $alumni->email) }}">
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="no_hp">No. HP</label>
                <input type="text" id="no_hp" name="no_hp" class="form-control"
                    value="{{ old('no_hp', $alumni->no_hp) }}">
                @error('no_hp')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="domisili">Domisili</label>
            <input type="text" id="domisili" name="domisili" class="form-control"
                value="{{ old('domisili', $alumni->domisili) }}">
            @error('domisili')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="flex gap-3" style="justify-content:flex-end; margin-top:8px;">
            <a href="{{ route('alumni.show', $alumni) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
