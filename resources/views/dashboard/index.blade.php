@extends('layouts.app')

@section('title', 'Dashboard — Sistem Pelacakan Alumni')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan sistem')

@section('content')

<div class="card" style="padding: 40px 20px; text-align: center; margin-top: 20px;">
    <div style="font-size: 48px; margin-bottom: 15px;">??</div>
    <h2 style="margin-bottom: 10px;">Selamat Datang, Admin!</h2>
    <p style="color: var(--text-muted); margin-bottom: 25px;">Anda telah berhasil login ke Sistem Pelacakan Alumni.</p>
    
    <div style="display: flex; gap: 15px; justify-content: center;">
        <a href="{{ route('alumni.index') }}" class="btn btn-primary">
            ?? Kelola Data Alumni
        </a>
        <a href="{{ route('pddikti.search') }}" class="btn btn-outline">
            ?? Pencarian PDDIKTI
        </a>
    </div>
</div>

@endsection
