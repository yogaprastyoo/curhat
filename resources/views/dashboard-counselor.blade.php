@extends('layouts.app')
@section('title', 'Dashboard Pendamping | Curhat')
@section('content-header')
    <h1>Dashboard Pendamping</h1>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $new }}</h3>
                    <p>Curhat Baru</p>
                </div>
                <div class="icon"><i class="fas fa-inbox"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $needResponse }}</h3>
                    <p>Perlu Ditanggapi</p>
                </div>
                <div class="icon"><i class="fas fa-reply"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $todaySchedule }}</h3>
                    <p>Jadwal Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Curhat Ditugaskan Terbaru</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table-hover table">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Masuk</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latest as $curhat)
                        <tr>
                            <td>{{ $curhat->curhat_number }}</td>
                            <td>{{ $curhat->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $curhat->category->name }}</td>
                            <td><span class="badge badge-{{ $curhat->statusBadge() }}">{{ $curhat->statusLabel() }}</span>
                            </td>
                            <td><a href="{{ route('curhats.show', $curhat) }}" class="btn btn-sm btn-info"><i
                                        class="fas fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">Belum ada curhat yang ditugaskan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
