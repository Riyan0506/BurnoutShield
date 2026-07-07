<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — BurnoutShield</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#f0f4ff',
                            100: '#e0e9ff',
                            400: '#6b82d6',
                            500: '#4f63d2',
                            600: '#3d50c3',
                            700: '#2f3fa8'
                        },
                        burnout: {
                            low: '#10b981',
                            moderate: '#f59e0b',
                            high: '#ef4444'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* Prevent horizontal overflow globally */
        *, *::before, *::after {
            box-sizing: border-box;
            max-width: 100%;
        }
        
        html, body {
            overflow-x: hidden;
            min-width: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Sidebar */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #94a3b8;
            text-decoration: none;
            transition: .2s;
        }

        .sidebar-link:hover {
            background: #334155;
            color: #fff;
        }

        .sidebar-link.active {
            background: #4f63d2;
            color: #fff;
        }

        /* Cards */
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        /* Buttons */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background: #4f63d2;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #3d50c3;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background: white;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background: #f8fafc;
        }

        /* Badges */
        .badge-high {
            background: #fee2e2;
            color: #b91c1c;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 12px;
        }

        .badge-moderate {
            background: #fef3c7;
            color: #b45309;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 12px;
        }

        .badge-low {
            background: #dcfce7;
            color: #15803d;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 12px;
        }

        /* Risk */
        .risk-high {
            color: #ef4444;
        }

        .risk-moderate {
            color: #f59e0b;
        }

        .risk-low {
            color: #10b981;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }

        /* Sidebar transitions */
        .sidebar-overlay {
            transition: opacity 0.3s ease;
        }
        
        .sidebar-panel {
            transition: transform 0.3s ease;
        }
        
        /* Ensure no horizontal overflow */
        .no-overflow {
            overflow-x: hidden !important;
        }
        
        /* Canvas and Chart containers */
        canvas {
            max-width: 100%;
            height: auto;
        }
        
        /* Content wrapper */
        .content-wrapper {
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-100 h-full overflow-x-hidden">

    <!-- Mobile Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- ─── Sidebar ──────────────────────────────────────── -->
    <aside id="sidebar" class="w-60 min-h-screen bg-slate-800 flex flex-col fixed inset-y-0 left-0 z-50 shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out sidebar-panel">
        <!-- Brand -->
        <div class="px-5 py-5 border-b border-slate-700 flex items-center justify-between lg:justify-start">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🔥</span>
                <div>
                    <div class="text-white font-bold text-base leading-tight">BurnoutShield</div>
                    <div class="text-primary-400 text-xs font-medium">AI-Powered Wellness</div>
                </div>
            </div>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
                class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="{{ route('assessments.create') }}"
                class="sidebar-link {{ request()->routeIs('assessments.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                New Assessment
            </a>
            <a href="{{ route('assessments.history') }}"
                class="sidebar-link {{ request()->routeIs('assessments.history') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                History
            </a>
            <a href="{{ route('calendar.index') }}"
                class="sidebar-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Google Calendar
            </a>

            <div class="pt-3 pb-1 px-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">Account</div>

            <a href="{{ route('profile.show') }}"
                class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full text-left">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </nav>

        <!-- Version -->
        <div class="px-5 py-3 border-t border-slate-700">
            <span class="text-xs text-slate-500">BurnoutShield v1.0</span>
        </div>
    </aside>

    <!-- ─── Main Content ─────────────────────────────────── -->
    <div class="lg:ml-60 flex-1 flex flex-col min-h-screen">

        <!-- Top bar -->
        <header class="bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-3 lg:py-4 flex items-center justify-between sticky top-0 z-30">
            <!-- Mobile Menu Button -->
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 hover:text-slate-800 p-2 -ml-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            
            <div class="flex-1 min-w-0 lg:ml-0 ml-2">
                <h1 class="text-base lg:text-lg font-semibold text-slate-800 truncate">@yield('page-title', 'Dashboard')</h1>
                <p class="text-xs text-slate-500 mt-0.5 hidden sm:block">@yield('page-subtitle', '')</p>
            </div>
            <div class="flex items-center gap-2 lg:gap-3 ml-2 sm:ml-4">
                <!-- User avatar -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-right">
                        <div class="text-sm font-medium text-slate-800 truncate max-w-[120px] lg:max-w-none">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-500 hidden lg:block">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
            <div class="flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700 mb-3 sm:mb-4">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="break-words">{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-3 sm:mb-4">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="break-words">{{ session('error') }}</span>
            </div>
            @endif
            @if(session('warning'))
            <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700 mb-3 sm:mb-4">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="break-words">{{ session('warning') }}</span>
            </div>
            @endif
        </div>

        <!-- Page content -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-4 lg:py-6 content-wrapper">
            @yield('content')
        </main>
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('translate-x-0');
            overlay.classList.toggle('hidden');
            
            // Prevent body scroll when sidebar is open on mobile
            if (!sidebar.classList.contains('-translate-x-full')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            }
        });

        // Close sidebar when window is resized to desktop size
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
