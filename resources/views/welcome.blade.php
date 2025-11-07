<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pencatatan Uang App</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-gray-900 via-gray-800 to-black text-white">
    <div class="flex flex-col items-center justify-center min-h-screen text-center px-6">
        <div class="max-w-3xl space-y-8">

            <!-- Logo Section -->
            <div class="flex justify-center mb-6">
                <img 
                    src="https://img.freepik.com/vektor-premium/logo-uang-tanda-logo-dolar_1174662-223.jpg?semt=ais_hybrid&w=740&q=80" 
                    alt="App Logo" 
                    class="w-40 h-40 rounded-full shadow-md border border-gray-700 bg-white object-contain">
            </div>

            <!-- Title -->
            <h1 class="text-5xl font-bold mb-2">
                <span class="text-red-500">Welcome to Pencatatan Uang App</span>
            </h1>

            <!-- Description -->
            <p class="text-gray-400 text-lg max-w-lg mx-auto">
                A simple Laravel application to record your income and expenses easily.
            </p>

            <!-- Buttons -->
            @if (Route::has('login'))
                <div class="flex justify-center gap-4 mt-8">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg shadow-md transition">
                            Login
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-900 font-semibold rounded-lg shadow-md transition">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif

            <!-- Footer -->
            <footer class="mt-12 text-sm text-gray-500">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </footer>
        </div>
    </div>
</body>
</html>
