<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Employee Attendance System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --sidebar-bg: #1a1a2e;
            --sidebar-hover: #16213e;
            --sidebar-active: #0f3460;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --top-navbar-height: 60px;
            --card-border-radius: 10px;
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Layout Structure */
        .wrapper {
            display: flex;
            height: 100vh;
            width: 100%;
        }
        
        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            transition: all var(--transition-speed) ease;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }
        
        .sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
            overflow-x: hidden;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.6rem 1.25rem;
            border-radius: 0.35rem;
            margin: 0.15rem 0.75rem;
            display: flex;
            align-items: center;
            transition: all var(--transition-speed) ease;
            white-space: nowrap;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: var(--sidebar-hover);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--sidebar-active);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .sidebar-heading {
            padding: 0.75rem 1.25rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 600;
            margin-top: 1rem;
            white-space: nowrap;
        }
        
        .content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            transition: all var(--transition-speed) ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-collapsed .content-wrapper {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }
        
        .content {
            padding: 20px 30px;
            flex: 1 0 auto;
        }
        
        /* Logo and Branding */
        .logo-container {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            height: var(--top-navbar-height);
            overflow: hidden;
            position: relative;
        }
        
        .sidebar-collapsed .logo-container {
            justify-content: center;
        }
        
        .logo-icon {
            font-size: 1.8rem;
            margin-right: 15px;
            color: var(--success);
            transition: all var(--transition-speed) ease;
            flex-shrink: 0;
        }
        
        .sidebar-collapsed .logo-icon {
            margin-right: 0;
        }
        
        .logo-text-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: opacity var(--transition-speed), width var(--transition-speed);
            width: 170px;
            overflow: hidden;
        }
        
        .sidebar-collapsed .logo-text-container {
            opacity: 0;
            width: 0;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
            line-height: 1.2;
        }
        
        .logo-subtext {
            font-size: 0.7rem;
            opacity: 0.7;
        }
        
        /* Top Navbar */
        .header {
            background-color: #fff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        /* Top Navigation */
        .topnav {
            height: var(--top-navbar-height);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        /* Header Info Bar */
        .header-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .header-title {
            margin-right: auto;
        }
        
        .header-title h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .header-title p {
            margin: 0;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        /* DateTime Display */
        .date-display {
            display: flex;
            align-items: center;
            margin-right: 1.5rem;
            padding: 0.5rem 0.75rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            color: #6c757d;
        }
        
        .date-display i {
            margin-right: 0.5rem;
            color: var(--primary);
        }
        
        /* Card Styling */
        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-radius: var(--card-border-radius);
            transition: all var(--transition-speed) ease;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
            font-weight: 500;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        /* Button Styling */
        .btn {
            border-radius: 0.35rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
        }
        
        /* Quick Action Cards */
        .quick-action-card {
            border-radius: var(--card-border-radius);
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }
        
        .quick-action-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            pointer-events: none;
        }
        
        .quick-action-card:hover {
            transform: translateY(-8px);
        }
        
        .quick-action-card i {
            opacity: 1;
            font-size: 2.5rem;
            transition: all var(--transition-speed) ease;
        }
        
        .quick-action-card:hover i {
            transform: scale(1.2);
        }
        
        /* Sidebar Toggle Animation */
        .sidebar-toggle {
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: all var(--transition-speed) ease;
        }
        
        .sidebar-toggle:hover {
            color: var(--primary);
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        /* User Info Styling */
        .user-info {
            display: flex;
            align-items: center;
            padding: 0.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
            background-color: var(--primary);
            color: white;
            flex-shrink: 0;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--dark);
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        /* Dropdown Menu Styling */
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
        }
        
        .dropdown-item {
            padding: 0.5rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(3px);
        }
        
        .dropdown-item i {
            width: 1.5rem;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        /* Sidebar Dropdown */
        .sidebar-dropdown {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 0.35rem;
            margin: 0 0.75rem;
            overflow: hidden;
        }
        
        .sidebar-dropdown .nav-link {
            padding-left: 2.5rem;
            margin: 0.1rem 0;
        }
        
        .sidebar .nav-link[data-bs-toggle="collapse"] {
            position: relative;
        }
        
        .sidebar .nav-link[data-bs-toggle="collapse"]::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 1rem;
            transition: transform 0.2s;
        }
        
        .sidebar .nav-link[data-bs-toggle="collapse"][aria-expanded="true"]::after {
            transform: rotate(180deg);
        }
        
        /* Footer Styling */
        footer {
            padding: 1rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: #6c757d;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            
            .header-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-title {
                margin-bottom: 1rem;
                margin-right: 0;
            }
            
            .date-display {
                margin-right: 0;
                margin-bottom: 0.5rem;
                width: 100%;
            }
            
            .user-info {
                margin-top: 0.5rem;
                width: 100%;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
    <div class="wrapper {{ session('sidebar_collapsed') ? 'sidebar-collapsed' : '' }}">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-container">
                <i class="fas fa-user-clock logo-icon"></i>
                <div class="logo-text-container">
                    <div class="logo-text">EAMS</div>
                    <div class="logo-subtext">Employee Attendance</div>
                </div>
            </div>
            
            <hr class="border-light m-0 opacity-25">
            
            <nav class="mt-2">
                <!-- Admin & HR Menu -->
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHR()))
                    <div class="sidebar-heading">Main</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('hr.dashboard') ? 'active' : '' }}" 
                               href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('hr.dashboard') }}">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('counter.*') ? 'active' : '' }}" href="{{ route('counter.dashboard') }}">
                                <i class="fas fa-desktop"></i> Counter Terminal
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-heading">Employee Management</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                                <i class="fas fa-users"></i> Employees
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('attendance.*') && !request()->routeIs('attendance.dashboard') ? 'active' : '' }}" href="{{ route('attendance.index') }}">
                                <i class="fas fa-clipboard-list"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leaves.*') && !request()->routeIs('leaves.calendar') ? 'active' : '' }}" href="{{ route('leaves.index') }}">
                                <i class="fas fa-calendar-alt"></i> Leaves
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leaves.calendar') ? 'active' : '' }}" href="{{ route('leaves.calendar') }}">
                                <i class="fas fa-calendar-check"></i> Leave Calendar
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Leave Credits System (Admin/HR) -->
                    <div class="sidebar-heading">Leave Credits</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#leaveCreditsCollapse" aria-expanded="false" aria-controls="leaveCreditsCollapse">
                                <i class="fas fa-credit-card"></i> Leave Management
                            </a>
                            <div class="collapse sidebar-dropdown" id="leaveCreditsCollapse">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('leave-types.*') ? 'active' : '' }}" href="{{ route('leave-types.index') }}">
                                            <i class="fas fa-tags"></i> Leave Types
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('leave-credits.index') ? 'active' : '' }}" href="{{ route('leave-credits.index') }}">
                                            <i class="fas fa-list-alt"></i> Manage Credits
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('leave-credits.bulk-allocate') ? 'active' : '' }}" href="{{ route('leave-credits.bulk-allocate') }}">
                                            <i class="fas fa-layer-group"></i> Bulk Allocate
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('my-leave-credits') ? 'active' : '' }}" href="{{ route('my-leave-credits') }}">
                                <i class="fas fa-wallet"></i> My Credits
                            </a>
                        </li>
                    </ul>
                    
                    @if(auth()->user()->isAdmin())
                    <div class="sidebar-heading">Settings</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                                <i class="fas fa-building"></i> Departments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}" href="{{ route('shifts.index') }}">
                                <i class="fas fa-clock"></i> Shifts
                            </a>
                        </li>
                    </ul>
                    @endif
                    
                    <div class="sidebar-heading">Reports</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                    </ul>
                <!-- Employee Menu -->
                @elseif(auth()->check())
                    <div class="sidebar-heading">Main</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-heading">Attendance</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('my-attendance') ? 'active' : '' }}" href="{{ route('my-attendance') }}">
                                <i class="fas fa-clipboard-check"></i> My Attendance
                            </a>
                        </li>
                    </ul>

                    <!-- Leave Credits System (Employee) -->
                    <div class="sidebar-heading">Leaves</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leaves.*') ? 'active' : '' }}" href="{{ route('leaves.index') }}">
                                <i class="fas fa-calendar-alt"></i> My Leave Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('my-leave-credits') ? 'active' : '' }}" href="{{ route('my-leave-credits') }}">
                                <i class="fas fa-wallet"></i> My Leave Credits
                            </a>
                        </li>
                    </ul>
                @endif
            </nav>
        </div>

        <!-- Page Content -->
        <div class="content-wrapper">
            <!-- Top Navigation and Info Bar -->
            <div class="header">
                <!-- Top Navbar -->
                <div class="topnav">
                    <div class="sidebar-toggle me-3" id="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                    
                    <h1 class="h5 mb-0">Admin Dashboard</h1>
                    
                    <div class="ms-auto">
                        <div class="dropdown">
                            <button class="btn dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(auth()->check() && auth()->user()->profile_picture)
                                    <img class="rounded-circle me-2" src="{{ asset('storage/' . auth()->user()->profile_picture) }}" width="32" height="32" alt="Profile">
                                @else
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->email, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="d-none d-md-inline">{{ auth()->user()->employee_id ?? 'ADM001' }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile') }}">
                                        <i class="fas fa-user-circle text-primary"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt text-danger"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Header Info Bar -->
<div class="header-info">
    <div class="header-title">
        <h1>@yield('title')</h1>
        <p>Welcome back, {{ auth()->user()->first_name ?? 'User' }}!</p>
    </div>
    
    <div class="date-display">
        <i class="far fa-calendar-alt"></i>
        <span id="current-datetime">2025-05-14 22:23:38</span>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->email, 0, 1)) }}
        </div>
        <div class="user-details">
            <div class="user-name">DuckworthL</div>
            <div class="user-role">{{ auth()->user()->isAdmin() ? 'Administrator' : (auth()->user()->isHR() ? 'HR Manager' : 'Employee') }}</div>
        </div>
    </div>
</div>

            <!-- Main Content -->
            <div class="content">
                <!-- Alerts -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer>
                <div class="container-fluid">
                    <span class="text-muted">© {{ date('Y') }} Employee Attendance Monitoring System</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Custom scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const wrapper = document.querySelector('.wrapper');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            
            // Function to toggle sidebar
            function toggleSidebar() {
                if (window.innerWidth < 768) {
                    sidebar.classList.toggle('show');
                } else {
                    wrapper.classList.toggle('sidebar-collapsed');
                    
                    // Save state to session
                    fetch('/toggle-sidebar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            collapsed: wrapper.classList.contains('sidebar-collapsed')
                        })
                    }).catch(error => console.error('Error:', error));
                }
            }
            
            // Event listeners
            sidebarToggle.addEventListener('click', toggleSidebar);
            
            // Show fully expanded sidebar for screens wider than 768px
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('show');
                }
            });
            
            // Update the current datetime
            function updateDateTime() {
                const now = new Date();
                
                // Format date as YYYY-MM-DD HH:MM:SS
                const year = now.getUTCFullYear();
                const month = String(now.getUTCMonth() + 1).padStart(2, '0');
                const day = String(now.getUTCDate()).padStart(2, '0');
                const hours = String(now.getUTCHours()).padStart(2, '0');
                const minutes = String(now.getUTCMinutes()).padStart(2, '0');
                const seconds = String(now.getUTCSeconds()).padStart(2, '0');
                
                const formattedDatetime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                
                const dateTimeElement = document.getElementById('current-datetime');
                if (dateTimeElement) {
                    dateTimeElement.textContent = formattedDatetime;
                }
            }
            
            // Update time initially and set interval
            updateDateTime();
            setInterval(updateDateTime, 1000);
            
            // Initialize Select2
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5'
                });
            }
            
            // Handle collapsible menu items in sidebar
            document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]').forEach(item => {
                item.addEventListener('click', function(e) {
                    if (window.innerWidth < 768) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('data-bs-target'));
                        const bsCollapse = new bootstrap.Collapse(target);
                        target.classList.contains('show') ? bsCollapse.hide() : bsCollapse.show();
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>