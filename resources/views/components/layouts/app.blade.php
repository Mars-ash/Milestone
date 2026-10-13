<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Progress New Model — Prototype' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-[#F5F6F8] text-[#18212B] flex flex-col font-sans antialiased">
    <!-- Header Utama (UI01, UI02, UI05) -->
    <header class="bg-white border-b border-[#D8DEE6] sticky top-0 z-40">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16">
                <!-- Brand & Badge -->
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-[#FEF7E6] text-[#8A5B00] border border-[#F8DDA4] tracking-wide uppercase select-none">
                        PROTOTYPE
                    </span>
                    <a href="{{ route('prototype.index') }}" class="text-base sm:text-lg font-bold text-[#18212B] tracking-tight hover:text-[#2459A6] transition-colors focus-visible:rounded-sm">
                        Progress New Model
                    </a>
                </div>

                <!-- Admin & Action Controls -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <div class="hidden sm:flex items-center text-xs text-[#536170] bg-[#F5F6F8] px-2.5 py-1 rounded border border-[#D8DEE6]">
                        <span class="w-2 h-2 rounded-full bg-[#0E6B38] mr-1.5"></span>
                        Admin Tunggal (Teks Bebas)
                    </div>

                    @if(isset($headerActions))
                        {{ $headerActions }}
                    @endif
                </div>
            </div>

            <!-- Navigasi Tab Kerja (Dashboard, Project, Progress, Draft) -->
            <nav class="flex space-x-1 sm:space-x-6 overflow-x-auto no-scrollbar -mb-px border-t border-[#F0F2F5] sm:border-t-0" aria-label="Navigasi Utama">
                @php
                    $currentRoute = request()->route()->getName();
                @endphp
                <a
                    href="{{ route('prototype.index') }}"
                    class="min-h-[44px] flex items-center px-3 py-2 border-b-2 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ $currentRoute === 'prototype.index' ? 'border-[#2459A6] text-[#2459A6]' : 'border-transparent text-[#536170] hover:text-[#18212B] hover:border-[#D8DEE6]' }}"
                >
                    <svg class="w-4 h-4 mr-1.5 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a
                    href="{{ route('prototype.index', ['tab' => 'projects']) }}"
                    class="min-h-[44px] flex items-center px-3 py-2 border-b-2 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('tab') === 'projects' ? 'border-[#2459A6] text-[#2459A6]' : 'border-transparent text-[#536170] hover:text-[#18212B] hover:border-[#D8DEE6]' }}"
                >
                    <svg class="w-4 h-4 mr-1.5 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Project Master
                </a>

                <a
                    href="{{ route('prototype.index', ['tab' => 'progresses']) }}"
                    class="min-h-[44px] flex items-center px-3 py-2 border-b-2 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('tab') === 'progresses' ? 'border-[#2459A6] text-[#2459A6]' : 'border-transparent text-[#536170] hover:text-[#18212B] hover:border-[#D8DEE6]' }}"
                >
                    <svg class="w-4 h-4 mr-1.5 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Progress List
                </a>

                <a
                    href="{{ route('prototype.index', ['tab' => 'drafts']) }}"
                    class="min-h-[44px] flex items-center px-3 py-2 border-b-2 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('tab') === 'drafts' ? 'border-[#2459A6] text-[#2459A6]' : 'border-transparent text-[#536170] hover:text-[#18212B] hover:border-[#D8DEE6]' }}"
                >
                    <svg class="w-4 h-4 mr-1.5 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Draft
                </a>

                <a
                    href="{{ route('prototype.index', ['tab' => 'components']) }}"
                    class="min-h-[44px] flex items-center px-3 py-2 border-b-2 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('tab') === 'components' ? 'border-[#2459A6] text-[#2459A6]' : 'border-transparent text-[#536170] hover:text-[#18212B] hover:border-[#D8DEE6]' }}"
                >
                    <svg class="w-4 h-4 mr-1.5 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Pustaka Komponen UI
                </a>
            </nav>
        </div>
    </header>

    <!-- Konten Halaman -->
    <main class="flex-1 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 w-full">
        {{ $slot }}
    </main>

    <!-- Footer Ringkas (UI02, UI12) -->
    <footer class="bg-white border-t border-[#D8DEE6] py-3 text-xs text-[#536170] select-none">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div>
                Progress New Model &bull; Prototype Fase 01 &bull; Stack: Laravel 13, Livewire 4, Tailwind 4
            </div>
            <div class="text-[11px] text-[#8C9BAE]">
                Lingkungan: {{ app()->environment() }} &bull; Data Simulasi
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
