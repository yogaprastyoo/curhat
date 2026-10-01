@extends('layouts.app')
@section('title', 'Dashboard | Curhat')
@section('content-header')
    <h1>Dashboard</h1>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>Halo, {{ auth()->user()->name }}</h4>
            <p class="text-muted mb-0">Akun ini tidak memiliki akses ke isi curhat siswa.</p>
        </div>
    </div>
@endsection
