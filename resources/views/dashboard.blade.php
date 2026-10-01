@extends('layouts.app')
@section('title', 'Dashboard | Curhat')
@section('content-header')
    <h1>Dashboard</h1>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary curhat-card">
                <div class="card-body curhat-welcome">
                    <h4>Halo, {{ auth()->user()->name }} 👋</h4>
                    <h5 class="mt-3">Apa kabarmu hari ini?</h5>
                    <p class="text-muted">Jika ada sesuatu yang ingin kamu ceritakan, kamu bisa menyampaikannya melalui
                        Curhat.</p>
                    <a href="{{ route('curhats.create') }}" class="btn btn-primary">
                        <i class="fas fa-comment-dots"></i> Mulai Curhat
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalCurhat }}</h3>
                    <p>Total Curhat</p>
                </div>
                <div class="icon"><i class="fas fa-comments"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $waiting }}</h3>
                    <p>Menunggu Tanggapan</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $completed }}</h3>
                    <p>Selesai</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Curhat Terbaru</h3>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse ($latest as $curhat)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $curhat->category->name }}</strong>
                            <p class="mb-0">
                                <a href="{{ route('curhats.show', $curhat) }}">{{ $curhat->title }}</a>
                            </p>
                            <small class="text-muted">{{ $curhat->curhat_number }} •
                                {{ $curhat->created_at->format('d M Y') }}</small>
                        </div>
                        <span class="badge badge-{{ $curhat->statusBadge() }}">{{ $curhat->statusLabel() }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Kamu belum pernah mengirim curhat.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
