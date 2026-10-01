@extends('layouts.app')
@section('title', 'Jadwal Konseling')
@section('content-header')
    <h1>{{ auth()->user()->isStudent() ? 'Jadwal Saya' : 'Jadwal Konseling' }}</h1>
@endsection
@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table-hover table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Lokasi</th>
                        <th>Curhat</th>
                        <th>Pendamping</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->date->translatedFormat('d F Y') }}</td>
                            <td>{{ substr($schedule->start_time, 0, 5) }} WIB</td>
                            <td>{{ $schedule->location ?? '-' }}</td>
                            <td><a href="{{ route('curhats.show', $schedule->curhat) }}">{{ $schedule->curhat->curhat_number }}</a>
                            </td>
                            <td>{{ $schedule->counselor->name }}</td>
                            <td>
                                <span class="badge badge-{{ ['scheduled' => 'info', 'completed' => 'success', 'cancelled' => 'secondary'][$schedule->status] }}">
                                    {{ ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'][$schedule->status] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center">Belum ada jadwal konseling.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($schedules->hasPages())
            <div class="card-footer">{{ $schedules->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
@endsection
