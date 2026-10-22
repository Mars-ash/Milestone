<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2459a6">
    <meta name="environment" content="{{ \App\Prototype\PrototypeManager::isEnabled() ? 'PROTOTYPE' : 'APPLICATION' }}">
    <meta name="description" content="Pendataan aktivitas pengembangan new model.">
    <title>{{ $title ?? 'Progress New Model' }}</title>
    @vite(['resources/css/prototype.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="{ mobileMenu: false }" :class="{ 'menu-open': mobileMenu }">
    <a class="skip-link" href="#main">Langsung ke konten</a>
    <div id="app">
        {{ $slot }}
    </div>
    <div
        id="toast" class="toast" role="status" aria-live="polite"
        x-data="{
            message: '', visible: false, timer: null,
            init() { const initial = @js(session('ui-toast', '')); if (initial) this.show(initial); },
            show(text) {
                clearTimeout(this.timer);
                this.message = text;
                this.visible = true;
                this.timer = setTimeout(() => this.visible = false, 4200);
            },
            destroy() { clearTimeout(this.timer); }
        }"
        x-on:ui-toast.window="show($event.detail.message)"
        x-show="visible" style="display:none"
    ><span x-text="message"></span></div>
    @livewireScripts
</body>
</html>
