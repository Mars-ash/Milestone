# P01-T01: Bootstrap Workspace Prototype Implementation Plan

> **Workflow Reference:** [AGENT_WORKFLOW.md](file:///c:/laragon/www/progress-new-model/AGENT_WORKFLOW.md) • [FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md#L64-L88)
> **PRD Reference:** D20, Bagian 13

**Goal:** Bootstrap aplikasi Laravel 13 dengan PHP 8.4, Livewire 4, dan Tailwind CSS 4 di workspace `c:\laragon\www\progress-new-model`, mengonfigurasi jalur khusus prototype (`/prototype`), direktori `app/Prototype`, isolasi environment (aktif hanya di `local`/`testing`, 404 di `production`), dan build asset lokal via Vite.

**Architecture:**
- Skeleton Laravel 13 murni tanpa merusak file yang sudah ada (`.git`, `PRD_Progress_New_Model.md`, `AGENT_WORKFLOW.md`, `skills/`, `task list/`).
- Integrasi `livewire/livewire` v4 dan Tailwind CSS v4 via `@tailwindcss/vite`.
- Route group `/prototype` dengan proteksi environment guard (hanya `local` dan `testing`).
- Konfigurasi `config/prototype.php` yang mengontrol status prototype.
- Feature tests untuk memverifikasi proteksi environment `/prototype`.

**Tech Stack:**
- PHP 8.4.18
- Laravel 13.x
- Livewire 4.x
- Tailwind CSS 4.x + Vite
- PHPUnit 11/12

---

## Global Constraints
- PRD D20: Stack Laravel, Blade, Livewire, Tailwind CSS.
- Mode prototype HANYA untuk local dan testing; tidak ada jalur dummy yang terbuka di environment production.
- Menjaga file dokumentasi dan repo yang sudah ada.
- Jangan membuat React/Vue atau repo frontend kedua.

---

## Tasks & Steps

### Sub-Task 1: Scaffold Laravel 13 Skeleton
- [ ] 1.1 Download skeleton Laravel 13 ke direktori temporer
- [ ] 1.2 Salin file skeleton ke workspace tanpa menimpa file spesifikasi/skills/task list
- [ ] 1.3 Perbarui `README.md` agar mencakup petunjuk instalasi, build, dan start aplikasi

### Sub-Task 2: Dependencies & Asset Pipeline (Livewire 4 & Tailwind 4)
- [ ] 2.1 Install dependencies composer (`laravel/framework`, `livewire/livewire:^4.0`)
- [ ] 2.2 Setup package.json (`tailwindcss:^4.0`, `@tailwindcss/vite`, `vite`)
- [ ] 2.3 Konfigurasi `vite.config.js` dan `resources/css/app.css`
- [ ] 2.4 Jalankan `npm install` dan build asset `npm run build`

### Sub-Task 3: Prototype Directory, Config & Routing
- [ ] 3.1 Buat `config/prototype.php` dengan setting `enabled` berdasarkan environment (`local`, `testing`)
- [ ] 3.2 Buat direktori `app/Prototype`
- [ ] 3.3 Konfigurasi `routes/web.php` dengan prefix `/prototype` dan middleware/guard proteksi environment
- [ ] 3.4 Buat view dasar `resources/views/prototype/index.blade.php`

### Sub-Task 4: Automated Testing & Verification
- [ ] 4.1 Buat test `tests/Feature/PrototypeRouteTest.php`:
  - Test 1: `/prototype` returns 200 OK pada environment testing/local
  - Test 2: `/prototype` returns 404 Not Found ketika `APP_ENV=production`
- [ ] 4.2 Jalankan `php artisan test --filter=PrototypeRouteTest` dan pastikan PASS
- [ ] 4.3 Verifikasi build `npm run build` dan pastikan asset ter-bundle dengan benar

### Sub-Task 5: Handoff & Checklist
- [ ] 5.1 Buat catatan handoff di `docs/progress/P01.md` sesuai template [AGENT_WORKFLOW.md](file:///c:/laragon/www/progress-new-model/AGENT_WORKFLOW.md)
- [ ] 5.2 Perbarui checklist task `P01-T01` di [task list/FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md)
