@extends('layouts.app')
@section('title', auth()->user()->isStudent() ? 'Riwayat Curhat' : 'Curhat Ditugaskan')
@section('content-header')
    <div class="row">
        <div class="col">
            <h1>{{ auth()->user()->isStudent() ? 'Riwayat Curhat' : 'Curhat Ditugaskan' }}</h1>
        </div>
        @if (auth()->user()->isStudent())
            <div class="col text-right">
                <a href="{{ route('curhats.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Curhat Baru</a>
            </div>
        @endif
    </div>
@endsection
@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table-hover table">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Tanggal Masuk</th>
                        @if (auth()->user()->isCounselor())
                            <th>Kelas</th>
                        @endif
                        <th>Kategori</th>
                        @if (auth()->user()->isStudent())
                            <th>Judul</th>
                        @endif
                        <th>Status</th>
                        <th>Pendamping</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($curhats as $curhat)
                        <tr>
                            <td>{{ $curhat->curhat_number }}</td>
                            <td>{{ $curhat->created_at->format('d M Y H:i') }}</td>
                            @if (auth()->user()->isCounselor())
                                <td>{{ $curhat->student->class_name ?? '-' }}</td>
                            @endif
                            <td>{{ $curhat->category->name }}</td>
                            @if (auth()->user()->isStudent())
                                <td>{{ $curhat->title }}</td>
                            @endif
                            <td><span class="badge badge-{{ $curhat->statusBadge() }}">{{ $curhat->statusLabel() }}</span>
                            </td>
                            <td>{{ $curhat->counselor->name ?? 'Belum ditugaskan' }}</td>
                            <td><a href="{{ route('curhats.show', $curhat) }}" class="btn btn-sm btn-info"><i
                                        class="fas fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted text-center">Belum ada curhat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($curhats->hasPages())
            <div class="card-footer">{{ $curhats->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
@endsection
