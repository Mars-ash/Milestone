# P01-T03: Bangun Fondasi Visual dan Navigasi yang Ringkas Implementation Plan

> **Workflow Reference:** [AGENT_WORKFLOW.md](file:///c:/laragon/www/progress-new-model/AGENT_WORKFLOW.md) • [FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md#L112-L135)
> **PRD Reference:** UI01 s/d UI12, Bagian 5–7, NF06

**Goal:** Membangun fondasi visual berbasis token PRD Bagian 6 (Tailwind CSS 4, satu aksen biru `#2459A6`, latar `#F5F6F8`, permukaan `#FFFFFF`, teks `#18212B`), shell layout responsif dengan navigasi utama (Dashboard, Project, Progress, Draft), dan pustaka komponen Blade UI terstandardisasi yang dapat beradaptasi pada breakpoint desktop (1366px, 1024px), tablet (768px), dan mobile (390px).

**Architecture:**
- `resources/css/app.css`: Konfigurasi token warna, font, focus-ring, dan base styles Tailwind 4 tanpa efek dekoratif berlebih (tanpa gradient/glassmorphism/animasi terus-menerus).
- `resources/views/components/layouts/app.blade.php`: Layout induk Blade yang memuat header ringkas, navigasi tab aktif, banner simulasi prototype, tombol reset fixture, dan area konten utama.
- `resources/views/components/ui/`: Komponen Blade reusable:
  - `button.blade.php` (varian: primary, secondary, danger, subtle, ukuran kontrol 40-44px)
  - `input.blade.php` (label tetap terlihat, error dekat field, min 16px di mobile)
  - `select.blade.php` (dropdown standar dengan error state)
  - `textarea.blade.php` (textarea dengan tinggi dan resize yang sesuai)
  - `badge.blade.php` (indikator status Open, Closed, Draft, Activity)
  - `table.blade.php` & `responsive-table.blade.php` (tabel desktop 1366px bertransformasi menjadi card ringkasan baris pada 390px)
  - `empty-state.blade.php` (state kosong dengan pesan jelas dan tombol aksi)
  - `modal.blade.php` (dialog modal aksesibel dengan penutupan via tombol dan ESC)
  - `autosave-status.blade.php` (indikator: Menyimpan draft, Draft tersimpan HH:mm, Draft belum tersimpan)
  - `thumbnail.blade.php` (thumbnail foto berlabel [SIMULASI] dengan lightbox preview)
- `app/Livewire/Prototype/Shell.php`: Livewire component penyedia state global prototype shell (misal: pemicu reset fixture, notifikasi flash).
- Verifikasi visual: Dokumen pengujian [docs/review/prototype-visual.md](file:///c:/laragon/www/progress-new-model/docs/review/prototype-visual.md) berisi evaluasi visual 1366, 1024, 768, dan 390 px serta navigasi keyboard Tab.

**Tech Stack:**
- Blade, Tailwind CSS 4, Livewire 4, Alpine.js (bawaan Livewire)

---

## Global Constraints
- UI03: Pusat tampilan adalah daftar kerja dan form fungsional; tidak ada hero pemasaran, kartu statistik berlebihan, atau ilustrasi dekoratif.
- UI04–UI05: Palet netral dengan satu aksen biru `#2459A6`, kontras teks tinggi.
- UI08: Tinggi baris 44–52px, target sentuh HP minimal 44px.
- UI11: Ikon monokrom konsisten dengan teks label pada aksi penting.
- UI12: HINDARI gradient dekoratif, animasi terus-menerus, atau badge pada setiap nilai.

---

## Tasks & Steps

### Sub-Task 1: Konfigurasi Token Desain & Base Styles
- [ ] 1.1 Perbarui `resources/css/app.css` dengan token warna PRD Bagian 6 (`--color-surface`, `--color-background`, `--color-primary`, `--color-text-main`, dll.), focus ring yang jelas, dan typography baseline.
- [ ] 1.2 Uji kompilasi asset dengan `npm run build`.

### Sub-Task 2: Bangun Set Komponen UI Reusable (`resources/views/components/ui/`)
- [ ] 2.1 Buat komponen form: `button.blade.php`, `input.blade.php`, `select.blade.php`, `textarea.blade.php`.
- [ ] 2.2 Buat komponen display: `badge.blade.php`, `empty-state.blade.php`, `autosave-status.blade.php`, `thumbnail.blade.php`.
- [ ] 2.3 Buat komponen layout data: `table.blade.php` (dengan mode responsive card switch untuk mobile 390px).
- [ ] 2.4 Buat komponen interaksi: `modal.blade.php` (accessible dialog dengan dukungan keyboard Esc).

### Sub-Task 3: Bangun Layout Shell & Navigasi Prototype
- [ ] 3.1 Buat shell component `app/Livewire/Prototype/Shell.php` dan view `resources/views/livewire/prototype/shell.blade.php`.
- [ ] 3.2 Buat layout utama `resources/views/components/layouts/app.blade.php` dengan navigasi desktop & mobile drawer: Dashboard, Project, Progress, Draft.
- [ ] 3.3 Tambahkan tombol "Reset Data Simulasi" pada header prototype yang memanggil `FakeStore::reset()`.
- [ ] 3.4 Perbarui route `/prototype` agar menggunakan layout shell terpadu dan menampilkan showcase komponen visual.

### Sub-Task 4: Pengujian & Verifikasi Visual Breakpoint
- [ ] 4.1 Buat feature test `tests/Feature/PrototypeShellTest.php` untuk memverifikasi navigasi, render komponen UI, dan aksi reset data simulasi.
- [ ] 4.2 Jalankan browser test / subagent visual review pada breakpoint 1366px, 1024px, 768px, dan 390px.
- [ ] 4.3 Tulis laporan verifikasi visual di `docs/review/prototype-visual.md`.

### Sub-Task 5: Dokumentasi Handoff & Checklist
- [ ] 5.1 Update handoff `docs/progress/P01.md`.
- [ ] 5.2 Centang task `P01-T03` di `task list/FASE_01_Prototype_dan_Validasi.md`.
- [ ] 5.3 Commit ke git repository.
