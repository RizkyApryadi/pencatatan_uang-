<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <livewire:layout.navigation />

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>


    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- AlpineJS (untuk modal) -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- SweetAlert listener for Livewire browser events -->
    <script>
        window.addEventListener('swal:alert', event => {
            const detail = event.detail || {};
            Swal.fire({
                icon: detail.type || 'success',
                title: detail.title || 'Berhasil!',
                text: detail.message || '',
                timer: detail.timer || 2000,
                showConfirmButton: detail.showConfirmButton ?? false,
            });
        });
        // Also listen for Livewire emitted events for compatibility with older Livewire versions
        if (window.Livewire && typeof Livewire.on === 'function') {
            Livewire.on('swal:alert', detail => {
                detail = detail || {};
                Swal.fire({
                    icon: detail.type || 'success',
                    title: detail.title || 'Berhasil!',
                    text: detail.message || '',
                    timer: detail.timer || 2000,
                    showConfirmButton: detail.showConfirmButton ?? false,
                });
            });
        }
    </script>
    <!-- ApexCharts (used by pages/components) -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Place for pages/components to push additional scripts (e.g. ApexCharts initializers) -->
    @stack('scripts')

</body>

</html>