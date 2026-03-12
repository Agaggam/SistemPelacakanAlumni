@extends('layouts.app')

@section('title', 'Tambah Alumni')
@section('page-title', 'Tambah Alumni Baru')
@section('page-subtitle', 'Masukkan data mahasiswa/alumni ke dalam sistem')

@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('alumni.index') }}" class="btn btn-secondary btn-sm">← Kembali</a>
</div>

<div class="card" style="max-width:750px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">📝 Data Alumni</div>
    </div>
    <form action="{{ route('alumni.store') }}" method="POST">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nim">NIM <span style="color:var(--danger)">*</span></label>
                <input type="text" id="nim" name="nim" class="form-control"
                    placeholder="Contoh: 2019001001" value="{{ old('nim') }}" required>
                @error('nim')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="nama">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control"
                    placeholder="Contoh: Budi Santoso" value="{{ old('nama') }}" required>
                @error('nama')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="prodi">Program Studi <span style="color:var(--danger)">*</span></label>
                <input type="text" id="prodi" name="prodi" class="form-control"
                    placeholder="Contoh: Teknik Informatika" value="{{ old('prodi') }}" required>
                @error('prodi')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="fakultas">Fakultas</label>
                <input type="text" id="fakultas" name="fakultas" class="form-control"
                    placeholder="Contoh: Fakultas Teknik" value="{{ old('fakultas') }}">
                @error('fakultas')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="angkatan">Angkatan <span style="color:var(--danger)">*</span></label>
                <input type="number" id="angkatan" name="angkatan" class="form-control"
                    placeholder="Contoh: 2019" value="{{ old('angkatan') }}" min="1990" max="{{ date('Y') }}" required>
                @error('angkatan')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="tahun_lulus">Tahun Lulus <span class="text-muted">(opsional)</span></label>
                <input type="number" id="tahun_lulus" name="tahun_lulus" class="form-control"
                    placeholder="Contoh: 2023" value="{{ old('tahun_lulus') }}" min="1990" max="{{ date('Y') + 1 }}">
                @error('tahun_lulus')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    placeholder="alumni@gmail.com" value="{{ old('email') }}">
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="no_hp">No. HP</label>
                <input type="text" id="no_hp" name="no_hp" class="form-control"
                    placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}">
                @error('no_hp')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="domisili">Domisili</label>
            <input type="text" id="domisili" name="domisili" class="form-control"
                placeholder="Kota domisili saat ini" value="{{ old('domisili') }}">
            @error('domisili')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="flex gap-3" style="justify-content:flex-end; margin-top:8px;">
            <a href="{{ route('alumni.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">💾 Simpan Alumni</button>
        </div>
    </form>
</div>
@endsection
