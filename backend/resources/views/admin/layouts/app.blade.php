<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Kopi Senja Admin</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-coffee: #3E2723;
            --primary-coffee-light: #5D4037;
            --accent-cream: #FFF8F0;
            --accent-cream-dark: #F5EBE0;
            --text-dark: #2B1A11;
            --text-muted: #8D6E63;
            --bg-body: #FAF6F0;
            --accent-gold: #D4A373;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: var(--primary-coffee);
            min-height: 100vh;
            color: var(--accent-cream);
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .brand {
            padding: 24px;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--accent-gold);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 16px 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: var(--accent-cream-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            color: var(--accent-cream);
            background-color: rgba(255,255,255,0.05);
            border-left-color: var(--accent-gold);
        }

        .sidebar-menu li a i {
            font-size: 1.2rem;
            color: var(--accent-gold);
        }

        /* Header / Navbar */
        .topbar {
            background-color: #ffffff;
            height: 70px;
            border-bottom: 1px solid var(--accent-cream-dark);
            padding: 0 30px;
        }

        /* Premium Cards */
        .card-premium {
            background: #ffffff;
            border: 1px solid rgba(212, 163, 115, 0.2);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(43, 26, 17, 0.02);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(43, 26, 17, 0.05);
        }

        /* Button Customizations */
        .btn-coffee {
            background-color: var(--primary-coffee);
            color: var(--accent-cream);
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.2s;
            border: none;
        }

        .btn-coffee:hover {
            background-color: var(--primary-coffee-light);
            color: var(--accent-cream);
        }

        .btn-outline-coffee {
            border: 2px solid var(--primary-coffee);
            color: var(--primary-coffee);
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 18px;
            transition: all 0.2s;
        }

        .btn-outline-coffee:hover {
            background-color: var(--primary-coffee);
            color: var(--accent-cream);
        }

        /* Table Design */
        .table-premium {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .table-premium th {
            background-color: var(--accent-cream-dark);
            color: var(--primary-coffee);
            font-weight: 700;
            border: none;
            padding: 15px 20px;
        }

        .table-premium td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid var(--accent-cream-dark);
        }

        /* Status Badges */
        .badge-status {
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .badge-waiting_payment { background-color: #FFF3CD; color: #856404; }
        .badge-processing { background-color: #D1ECF1; color: #0C5460; }
        .badge-making { background-color: #E8F0FE; color: #1A73E8; }
        .badge-ready { background-color: #D4EDDA; color: #155724; }
        .badge-completed { background-color: #C3E6CB; color: #1E4620; }
        .badge-cancelled { background-color: #F8D7DA; color: #721C24; }

        .badge-pending { background-color: #FFF3CD; color: #856404; }
        .badge-paid { background-color: #D4EDDA; color: #155724; }
        .badge-failed { background-color: #F8D7DA; color: #721C24; }
        .badge-expired { background-color: #E2E3E5; color: #383D41; }
    </style>
    @yield('styles')
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar d-none d-md-block">
            <div class="brand">
                <i class="bi bi-cup-hot-fill"></i> Kopi Senja
            </div>
            <ul class="sidebar-menu">
                <li class="{{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                
                @if(Auth::user()->isSuperAdmin())
                <li class="{{ Request::routeIs('admin.branches.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.branches.index') }}">
                        <i class="bi bi-shop"></i> Cabang
                    </a>
                </li>
                <li class="{{ Request::routeIs('admin.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}">
                        <i class="bi bi-grid-fill"></i> Kategori
                    </a>
                </li>
                <li class="{{ Request::routeIs('admin.products.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.products.index') }}">
                        <i class="bi bi-cup-straw"></i> Menu Produk
                    </a>
                </li>
                <li class="{{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people-fill"></i> Admin Cabang
                    </a>
                </li>
                @endif

                <li class="{{ Request::routeIs('admin.stocks.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.stocks.index') }}">
                        <i class="bi bi-box-seam"></i> Kelola Stok
                    </a>
                </li>
                <li class="{{ Request::routeIs('admin.orders.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.orders.index') }}">
                        <i class="bi bi-receipt"></i> Pesanan
                        @php
                            $waitingCount = \App\Models\Order::query()
                                ->when(Auth::user()->isAdminBranch(), function($q) {
                                    $q->where('branch_id', Auth::user()->branch_id);
                                })
                                ->where('order_status', 'waiting_payment')
                                ->count();
                        @endphp
                        @if($waitingCount > 0)
                            <span class="badge bg-danger ms-auto">{{ $waitingCount }}</span>
                        @endif
                    </a>
                </li>
                
                <li class="mt-5">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger">
                        <i class="bi bi-box-arrow-left text-danger"></i> Keluar
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 min-vh-100 d-flex flex-column">
            <!-- Topbar -->
            <div class="topbar d-flex align-items-center justify-content-between">
                <h4 class="m-0 font-weight-700 text-dark">
                    @yield('header_title', 'Dashboard')
                </h4>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-sm-block">
                        <h6 class="m-0 font-weight-600">{{ Auth::user()->name }}</h6>
                        <small class="text-muted text-uppercase">
                            {{ Auth::user()->role === 'super_admin' ? 'Super Admin' : Auth::user()->branch->name }}
                        </small>
                    </div>
                    <i class="bi bi-person-circle fs-3 text-dark"></i>
                </div>
            </div>

            <!-- Main Body -->
            <div class="p-4 flex-grow-1">
                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="text-center py-3 bg-white border-top text-muted mt-auto" style="font-size: 0.85rem;">
                &copy; 2026 Kopi Senja. All Rights Reserved. Designed for Portfolios.
            </footer>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@yield('scripts')
</body>
</html>
