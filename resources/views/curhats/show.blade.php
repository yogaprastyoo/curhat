@extends('layouts.app')
@section('title', 'Detail Curhat')
@section('content-header')
    <h1>Detail Curhat</h1>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $curhat->curhat_number }}</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $curhat->statusBadge() }}">{{ $curhat->statusLabel() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Kategori:</strong> {{ $curhat->category->name }}</p>
                    <p class="mb-1"><strong>Tanggal:</strong> {{ $curhat->created_at->translatedFormat('d F Y H:i') }}</p>
                    <p class="mb-1"><strong>Pendamping:</strong> {{ $curhat->counselor->name ?? 'Belum ditugaskan' }}</p>
                    @if (auth()->user()->isCounselor())
                        <p class="mb-1"><strong>Siswa:</strong> {{ $curhat->student->name }}
                            ({{ $curhat->student->class_name ?? '-' }})</p>
                        <p class="mb-1"><strong>Permintaan konseling:</strong>
                            {{ ['no' => 'Belum perlu', 'yes' => 'Ya', 'unsure' => 'Belum yakin'][$curhat->counseling_request] ?? '-' }}
                        </p>
                    @endif
                    <hr>
                    <h5>{{ $curhat->title }}</h5>
                    <p style="white-space: pre-line">{{ $curhat->message }}</p>
                </div>
            </div>

            <div class="card message-card">
                <div class="card-header">
                    <h3 class="card-title">Percakapan</h3>
                </div>
                <div class="card-body">
                    @forelse ($curhat->messages as $message)
                        <div class="callout {{ $message->user->isStudent() ? 'callout-secondary' : 'callout-info' }}">
                            <strong>
                                {{ $message->user_id === auth()->id() ? 'Kamu' : ($message->user->isStudent() ? 'Siswa' : $message->user->name) }}
                            </strong>
                            <p class="mt-2 mb-1" style="white-space: pre-line">{{ $message->message }}</p>
                            <small class="text-muted">{{ $message->created_at->translatedFormat('d F Y • H:i') }}</small>
                        </div>
                    @empty
                        <p class="text-muted">Belum ada pesan.</p>
                    @endforelse

                    @if ($curhat->status !== 'completed')
                        <form method="POST" action="{{ route('curhats.messages.store', $curhat) }}" class="mt-3">
                            @csrf
                            <div class="form-group">
                                <textarea name="message" rows="3" class="form-control @error('message') is-invalid @enderror"
                                    placeholder="Tulis pesan...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane"></i> Kirim Pesan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Progress</h3>
                </div>
                <div class="card-body">
                    @foreach ($curhat->histories->sortBy('created_at') as $history)
                        <div class="mb-2">
                            <i class="fas fa-check-circle text-success"></i>
                            {{ \App\Models\Curhat::STATUS_LABELS[$history->status] ?? ucfirst($history->action) }}
                            <br>
                            <small class="text-muted">{{ $history->created_at->translatedFormat('d M Y H:i') }}</small>
                            @if (auth()->user()->isCounselor() && $history->note)
                                <br><small><em>{{ $history->note }}</em></small>
                            @endif
                        </div>
                    @endforeach

                    @if (auth()->user()->isCounselor() && $curhat->allowedNextStatuses())
                        <form method="POST" action="{{ route('curhats.status', $curhat) }}" class="border-top mt-3 pt-3">
                            @csrf
                            <div class="form-group">
                                <label>Ubah Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    @foreach ($curhat->allowedNextStatuses() as $status)
                                        <option value="{{ $status }}">{{ \App\Models\Curhat::STATUS_LABELS[$status] }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <textarea name="note" class="form-control" rows="2" placeholder="Catatan (opsional)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Simpan Status</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jadwal Konseling</h3>
                </div>
                <div class="card-body">
                    @forelse ($curhat->schedules->sortBy('date') as $schedule)
                        <div class="mb-2">
                            <i class="far fa-calendar"></i> {{ $schedule->date->translatedFormat('d F Y') }}
                            • {{ substr($schedule->start_time, 0, 5) }}
                            <br>
                            <small class="text-muted"><i class="fas fa-map-marker-alt"></i>
                                {{ $schedule->location ?? '-' }} • {{ $schedule->counselor->name }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada jadwal.</p>
                    @endforelse

                    @if (auth()->user()->isCounselor() && ($curhat->status === 'scheduled' || $curhat->canTransitionTo('scheduled')))
                        <form method="POST" action="{{ route('curhats.schedules.store', $curhat) }}"
                            class="border-top mt-3 pt-3">
                            @csrf
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="date" value="{{ old('date') }}"
                                    class="form-control @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Jam Mulai</label>
                                <input type="time" name="start_time" value="{{ old('start_time') }}"
                                    class="form-control @error('start_time') is-invalid @enderror">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Lokasi</label>
                                <input type="text" name="location" value="{{ old('location') }}" class="form-control"
                                    placeholder="Ruang BK">
                            </div>
                            <div class="form-group">
                                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan (opsional)">{{ old('notes') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm">Simpan Jadwal</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
