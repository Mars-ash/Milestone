<x-layouts.app>
    <main class="main" style="margin:0;max-width:480px;padding:48px 24px;margin-inline:auto">
        <h1>Progress New Model</h1>
        <p class="sub">Masuk sebagai admin</p>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" required autofocus value="{{ old('email') }}"></div>
            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
            @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
            <button class="btn primary" type="submit">Masuk</button>
        </form>
    </main>
</x-layouts.app>
