<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prototype — Progress New Model</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col">
    <!-- Header ringkas -->
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 border border-amber-300">
                    PROTOTYPE
                </span>
                <h1 class="text-lg font-semibold text-slate-900 tracking-tight">Progress New Model</h1>
            </div>
            <div class="text-xs text-slate-500">
                Mode Simulasi &bull; Lingkungan: {{ app()->environment() }}
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 w-full">
        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
            <div class="max-w-2xl">
                <h2 class="text-xl font-bold text-slate-900 mb-2">Workspace Prototype Siap</h2>
                <p class="text-sm text-slate-600 mb-6">
                    Aplikasi telah di-bootstrap menggunakan stack target <strong>Laravel 13, Livewire 4, dan Tailwind CSS 4</strong>.
                    Halaman prototype ini menggunakan data simulasi fiktif dan diisolasi khusus lingkungan local/testing.
                </p>

                <div class="rounded-md bg-slate-50 p-4 border border-slate-200 text-xs font-mono text-slate-700 space-y-1">
                    <div><strong>Framework:</strong> Laravel {{ Illuminate\Foundation\Application::VERSION }}</div>
                    <div><strong>PHP Version:</strong> {{ PHP_VERSION }}</div>
                    <div><strong>Environment:</strong> {{ app()->environment() }}</div>
                    <div><strong>Prototype Status:</strong> Aktif (Simulasi)</div>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white py-3 text-center text-xs text-slate-400">
        Progress New Model &bull; Prototype Validasi Pengguna &bull; 2026
    </footer>
</body>
</html>
