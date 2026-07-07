<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — BurnoutShield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 500:'#4f63d2', 600:'#3d50c3', 700:'#2f3fa8' }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            overflow-x: hidden;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-primary-700 flex items-center justify-center p-4">

    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-600 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md mx-auto">
        <!-- Brand -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="inline-flex items-center gap-2 mb-3">
                <span class="text-4xl">🔥</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">BurnoutShield</h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-1">AI-Powered Employee Wellness</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
            @yield('content')
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-500 text-xs mt-6">
            © {{ date('Y') }} BurnoutShield. All rights reserved.
        </p>
    </div>

</body>
</html>
