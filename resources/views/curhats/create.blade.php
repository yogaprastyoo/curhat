@extends('layouts.app')
@section('title', 'Curhat Baru')
@section('content-header')
    <h1>Ceritakan yang Kamu Rasakan</h1>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Curhat Baru</h3>
                </div>
                <form method="POST" action="{{ route('curhats.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-light border">
                            <i class="fas fa-lock mr-2"></i>
                            Cerita yang kamu kirim akan diperlakukan secara privat dan hanya dapat dilihat oleh pihak
                            pendamping yang berwenang.
                        </div>
                        <div class="form-group">
                            <label for="title">Judul Singkat</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="150"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Contoh: Saya kesulitan berkonsentrasi">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="category_id">Kategori</label>
                            <select id="category_id" name="category_id"
                                class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="message">Ceritakan yang Kamu Rasakan</label>
                            <textarea id="message" name="message" rows="8" class="form-control @error('message') is-invalid @enderror"
                                placeholder="Kamu bisa bercerita dengan bahasamu sendiri...">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="privacy_scope">Saya ingin cerita ini ditangani oleh</label>
                            <select id="privacy_scope" name="privacy_scope"
                                class="form-control @error('privacy_scope') is-invalid @enderror">
                                <option value="counselor" @selected(old('privacy_scope', 'counselor') === 'counselor')>Guru BK</option>
                                <option value="counselor_psychologist" @selected(old('privacy_scope') === 'counselor_psychologist')>
                                    Guru BK / Psikolog
                                </option>
                            </select>
                            @error('privacy_scope')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="counseling_request">Apakah kamu ingin melakukan pertemuan konseling?</label>
                            <select id="counseling_request" name="counseling_request"
                                class="form-control @error('counseling_request') is-invalid @enderror">
                                <option value="no" @selected(old('counseling_request', 'no') === 'no')>Belum perlu</option>
                                <option value="yes" @selected(old('counseling_request') === 'yes')>Ya, saya ingin bertemu</option>
                                <option value="unsure" @selected(old('counseling_request') === 'unsure')>Saya belum yakin</option>
                            </select>
                            @error('counseling_request')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Curhat
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
