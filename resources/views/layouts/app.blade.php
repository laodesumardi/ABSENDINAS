<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi') - {{ config('app.name') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #3a0ca3;
            --primary-dark: #2c0a7a;
            --primary-light: #5b2ed8;
            --secondary-color: #4361ee;
            --success-color: #06d6a0;
            --danger-color: #ef476f;
            --warning-color: #ffd166;
            --info-color: #118ab2;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 1rem 1.5rem;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: #ffd166;
        }

        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
            border-left-color: #ffd166;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

    /* Pagination Styles */
    .pagination {
        gap: 5px;
    }

    .page-item .page-link {
        color: #3a0ca3;
        background-color: white;
        border: 1px solid #dee2e6;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .page-item .page-link:hover {
        background-color: #3a0ca3;
        color: white;
        border-color: #3a0ca3;
        transform: translateY(-2px);
    }

    .page-item.active .page-link {
        background-color: #3a0ca3;
        border-color: #3a0ca3;
        color: white;
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #f8f9fa;
    }

     /* ============================================
       PAGINATION STYLES
    ============================================ */
    .pagination {
        gap: 6px;
        margin-bottom: 0;
        flex-wrap: wrap;
    }

    .page-item .page-link {
        color: #3a0ca3;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .page-item .page-link:hover {
        background: linear-gradient(135deg, #3a0ca3 0%, #2c0a7a 100%);
        color: #ffffff;
        border-color: #3a0ca3;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(58, 12, 163, 0.3);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #3a0ca3 0%, #2c0a7a 100%);
        border-color: #3a0ca3;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(58, 12, 163, 0.3);
    }

    .page-item.disabled .page-link {
        color: #94a3b8;
        background-color: #f1f5f9;
        border-color: #e2e8f0;
        cursor: not-allowed;
        opacity: 0.7;
        transform: none;
        box-shadow: none;
    }

    .page-item.disabled .page-link:hover {
        transform: none;
        box-shadow: none;
    }

    /* Go to page select */
    .form-select-sm.page-selector {
        cursor: pointer;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 6px 30px 6px 12px;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        background-color: #ffffff;
        transition: all 0.3s ease;
    }

    .form-select-sm.page-selector:hover {
        border-color: #3a0ca3;
        box-shadow: 0 2px 8px rgba(58, 12, 163, 0.1);
    }

    .form-select-sm.page-selector:focus {
        border-color: #3a0ca3;
        box-shadow: 0 0 0 0.2rem rgba(58, 12, 163, 0.25);
        outline: none;
    }

    /* Pagination info text */
    .text-muted.small i {
        color: #3a0ca3;
    }

    /* Responsive Pagination */
    @media (max-width: 768px) {
        .pagination {
            gap: 4px;
        }

        .page-item .page-link {
            padding: 6px 10px;
            font-size: 12px;
        }

        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            padding: 6px 10px;
        }

        /* Hide middle pages on mobile, show only first, active, last */
        .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
            display: none;
        }

        .page-item:nth-child(2) .page-link,
        .page-item:nth-last-child(2) .page-link {
            display: block;
        }

        .page-item.disabled .page-link {
            display: block;
        }

        .form-select-sm.page-selector {
            width: 100px !important;
            font-size: 12px;
            padding: 4px 25px 4px 8px;
        }
    }

    /* Tablet responsive */
    @media (min-width: 769px) and (max-width: 1024px) {
        .page-item .page-link {
            padding: 7px 12px;
            font-size: 13px;
        }
    }

    /* Animation for pagination */
    .pagination .page-item {
        animation: fadeInUp 0.3s ease-out;
        animation-fill-mode: both;
    }

    .pagination .page-item:nth-child(1) { animation-delay: 0.05s; }
    .pagination .page-item:nth-child(2) { animation-delay: 0.1s; }
    .pagination .page-item:nth-child(3) { animation-delay: 0.15s; }
    .pagination .page-item:nth-child(4) { animation-delay: 0.2s; }
    .pagination .page-item:nth-child(5) { animation-delay: 0.25s; }
    .pagination .page-item:nth-child(6) { animation-delay: 0.3s; }
    .pagination .page-item:nth-child(7) { animation-delay: 0.35s; }
    .pagination .page-item:nth-child(8) { animation-delay: 0.4s; }
    .pagination .page-item:nth-child(9) { animation-delay: 0.45s; }
    .pagination .page-item:nth-child(10) { animation-delay: 0.5s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Pagination info text */
    .pagination-info {
        font-size: 14px;
        color: #6c757d;
    }

    /* Go to page select */
    .form-select-sm {
        cursor: pointer;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 4px 24px 4px 8px;
    }

    .form-select-sm:focus {
        border-color: #3a0ca3;
        box-shadow: 0 0 0 0.2rem rgba(58, 12, 163, 0.25);
    }


        .sidebar .brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .sidebar .brand h3 {
            color: white;
            font-weight: bold;
            margin: 0;
        }

        .sidebar .brand p {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            margin: 0;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        /* Cards */
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            margin-bottom: 20px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .stat-card .stat-icon.primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        }

        .stat-card .stat-icon.success {
            background: linear-gradient(135deg, var(--success-color), #05a07a);
        }

        .stat-card .stat-icon.warning {
            background: linear-gradient(135deg, var(--warning-color), #e6b800);
        }

        .stat-card .stat-icon.danger {
            background: linear-gradient(135deg, var(--danger-color), #d64161);
        }

        /* Table Styles */
        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .table-custom thead {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        /* Buttons */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(58,12,163,0.3);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.active {
                margin-left: 250px;
            }
        }

        .btn-login {
    background-color: #3a0ca3;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 10px;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: 0.2s ease;
}

.btn-login:hover {
    background-color: #2f0a86;
}

.btn-login:focus {
    box-shadow: 0 0 0 0.2rem rgba(58, 12, 163, 0.25);
}

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Badge */
        .badge-custom {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        /* User Dropdown */
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    @include('layouts.sidebar-' . auth()->user()->role)

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="row align-items-center">
                <div class="col-auto">
                    <button class="btn btn-link text-dark" id="sidebarToggle">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                </div>
                <div class="col">
                    <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
                </div>
                <div class="col-auto">
                    <div class="dropdown user-dropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <div class="user-avatar d-inline-block">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span class="ms-2 d-none d-md-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user me-2"></i> Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.getElementById('mainContent').classList.toggle('active');
        });

        // Set active nav link
        const currentLocation = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => {
            if(link.getAttribute('href') === currentLocation) {
                link.classList.add('active');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
