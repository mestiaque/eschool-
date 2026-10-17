<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Student Portal')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --portal-primary: #1565c0; --portal-dark: #0d47a1; }
        body { background: #f0f4f8; min-height: 100vh; font-size: 14px; }
        .portal-navbar { background: linear-gradient(135deg,var(--portal-dark),var(--portal-primary)); box-shadow: 0 2px 12px rgba(0,0,0,.25); }
        .portal-navbar .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.1rem; }
        .portal-navbar .nav-link { color: rgba(255,255,255,.85) !important; font-size: 13px; padding: .4rem .75rem; border-radius: 6px; }
        .portal-navbar .nav-link:hover, .portal-navbar .nav-link.active { background: rgba(255,255,255,.15); color: #fff !important; }
        .portal-navbar .nav-link i { margin-right: 4px; }
        .stat-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.07); padding: 1.25rem; border-left: 4px solid var(--portal-primary); }
        .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .badge-status-paid   { background: #d1fae5; color: #065f46; }
        .badge-status-unpaid { background: #fee2e2; color: #991b1b; }
        .badge-status-partial{ background: #fef3c7; color: #92400e; }
        .portal-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .table-portal th { background: #eff6ff; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }
        .student-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #94a3b8; }
        @media(max-width:768px){ .portal-navbar .nav-link span { display: none; } }
    </style>
    @stack('styles')
</head>
<body>

<nav class="portal-navbar navbar navbar-expand-md">
    <div class="container-fluid px-3">
        <a class="navbar-brand" href="{{ route('portal.dashboard') }}">
            <i class="fas fa-graduation-cap me-2"></i>Student Portal
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
            <i class="fas fa-bars text-white"></i>
        </button>
        <div class="collapse navbar-collapse" id="portalNav">
            <ul class="navbar-nav me-auto gap-1 mt-1 mt-md-0">
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('portal.dashboard')) active @endif" href="{{ route('portal.dashboard') }}"><i class="fas fa-home"></i><span>Home</span></a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('portal.fees')) active @endif" href="{{ route('portal.fees') }}"><i class="fas fa-money-bill-wave"></i><span>Fees</span></a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('portal.results')) active @endif" href="{{ route('portal.results') }}"><i class="fas fa-chart-bar"></i><span>Results</span></a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('portal.library')) active @endif" href="{{ route('portal.library') }}"><i class="fas fa-book"></i><span>Library</span></a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('portal.notices')) active @endif" href="{{ route('portal.notices') }}"><i class="fas fa-bell"></i><span>Notices</span></a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('portal.attendance')) active @endif" href="{{ route('portal.attendance') }}"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 mt-1 mt-md-0">
                <span class="text-white-50 small">{{ $portalStudent->first_name ?? '' }}</span>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-3 py-3">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
