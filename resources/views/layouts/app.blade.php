<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Curhat')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <span class="nav-link"><i class="fas fa-user-circle"></i> {{ auth()->user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <a href="{{ route('dashboard') }}" class="brand-link text-center">
                    <span class="brand-text">CURHAT</span>
                </a>
                <div class="sidebar">
                    <div class="user-panel mb-3 mt-3 pb-3">
                        <div class="info"><a href="#" class="d-block">Portal Konseling</a></div>
                    </div>
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                            @if (auth()->user()->isStudent())
                                <li class="nav-item">
                                    <a href="{{ route('dashboard') }}"
                                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-home"></i>
                                        <p>Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-header">CURHAT</li>
                                <li class="nav-item">
                                    <a href="{{ route('curhats.create') }}"
                                        class="nav-link {{ request()->routeIs('curhats.create') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-comment-medical"></i>
                                        <p>Curhat Baru</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('curhats.index') }}"
                                        class="nav-link {{ request()->routeIs('curhats.index', 'curhats.show') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-comments"></i>
                                        <p>Riwayat Curhat</p>
                                    </a>
                                </li>
                                <li class="nav-header">KONSELING</li>
                                <li class="nav-item">
                                    <a href="{{ route('schedule') }}"
                                        class="nav-link {{ request()->routeIs('schedule') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-calendar-alt"></i>
                                        <p>Jadwal Saya</p>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->user()->isCounselor())
                                <li class="nav-item">
                                    <a href="{{ route('dashboard') }}"
                                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-home"></i>
                                        <p>Dashboard Pendamping</p>
                                    </a>
                                </li>
                                <li class="nav-header">CURHAT</li>
                                <li class="nav-item">
                                    <a href="{{ route('curhats.index') }}"
                                        class="nav-link {{ request()->routeIs('curhats.index', 'curhats.show') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-inbox"></i>
                                        <p>Curhat Ditugaskan</p>
                                    </a>
                                </li>
                                <li class="nav-header">KONSELING</li>
                                <li class="nav-item">
                                    <a href="{{ route('schedule') }}"
                                        class="nav-link {{ request()->routeIs('schedule') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-calendar-alt"></i>
                                        <p>Jadwal Konseling</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    <div class="container-fluid">@yield('content-header')</div>
                </section>
                <section class="content">
                    <div class="container-fluid">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                            </div>
                        @endif
                        @yield('content')
                    </div>
                </section>
            </div>
            <footer class="main-footer">
                <strong>CURHAT - Portal Konseling Siswa</strong>
                <div class="d-none d-sm-inline-block float-right">MVP v2.0</div>
            </footer>
        </div>
    </body>

</html>
