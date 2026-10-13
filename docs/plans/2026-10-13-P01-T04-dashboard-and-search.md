# P01-T04: Prototype Dashboard dan Pencarian Implementation Plan

> **Workflow Reference:** [AGENT_WORKFLOW.md](file:///c:/laragon/www/progress-new-model/AGENT_WORKFLOW.md) • [FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md#L136-L158)
> **PRD Reference:** FR17, FR18, AT23, AT24, AT25, UI03, UI04

**Goal:** Membangun antarmuka Dashboard interaktif (`app/Livewire/Prototype/Dashboard.php` dan `resources/views/livewire/prototype/dashboard.blade.php`) dengan 3 angka ringkasan global (Open tersimpan, Draft aktif, Aktivitas bulan berjalan), daftar kerja berurut terlama lebih dahulu (default), multi-filter (Customer, Activity, Status, PIC berdasarkan peran), pencarian instan, pencegahan baris berlipat akibat corrective action (menjamin 1 progress = 1 baris unik), serta diferensiasi empty state.

**Architecture:**
- `app/Prototype/FakeStore.php`: Peningkatan query engine:
  - Perhitungan statistik global `getGlobalStats()`: Open tersimpan, Draft aktif, Aktivitas bulan berjalan (`Y-m`), label periode Asia/Jakarta.
  - Pencarian PIC multi-peran (`all`, `progress`, `corrective_action`, `next_action`, `feedback`) yang menjamin output record progress unik (deduplikasi).
  - Pengurutan default: tanggal terlama (`oldest_first`), atau terbaru (`newest_first`).
- `app/Livewire/Prototype/Dashboard.php`: Livewire component yang mengelola state filter (`search`, `status`, `activity`, `customer`, `pic`, `pic_role`, `sort`), pagination simulasi, dan aksi reset filter.
- `resources/views/livewire/prototype/dashboard.blade.php`: Tampilan daftar kerja fungsional dengan panel filter rapi, tabel desktop dengan tinggi baris 44–52px, kartu baris responsif pada mobile 390px, dan diferensiasi empty state.

**Tech Stack:**
- Livewire 4, Blade, Tailwind CSS 4, FakeStore

---

## Global Constraints
- FR17: Tiga angka ringkas global: Open, Draft aktif, Aktivitas bulan berjalan. Filter daftar kerja tidak mengubah angka ringkasan global. UI diberi label "Ringkasan keseluruhan".
- FR18: Default menampilkan progress Open terlama lebih dahulu. Satu baris per progress (jumlah baris tidak boleh berlipat karena problem/corrective action).
- AT23: Jumlah anak/action tidak menggandakan angka ringkasan.
- AT24: Filter PIC mendukung pilihan peran (PIC Progress, Corrective Action, Next Action, Feedback) dan mengembalikan progress unik.
- AT25: Pembedaan pesan antara "Tidak ada progress Open" dan "Tidak ada hasil yang cocok dengan filter".

---

## Tasks & Steps

### Sub-Task 1: Perluas Query & Statistik di `app/Prototype/FakeStore.php`
- [ ] 1.1 Implementasikan `getGlobalStats()`: menghitung Open tersimpan, Draft aktif, dan Aktivitas bulan berjalan berdasarkan timezone Asia/Jakarta.
- [ ] 1.2 Perluas `getProgresses($filters)` dengan parameter:
  - `sort`: `oldest` (default) vs `newest`
  - `customer`: filter berdasarkan customer
  - `pic` & `pic_role`: filter pencarian PIC teks bebas per peran (Progress, Corrective Action, Next Action, Feedback, atau Semua Peran) dengan jaminan record progress unik.
- [ ] 1.3 Tambahkan method helper `getCustomers()` untuk daftar dropdown filter.

### Sub-Task 2: Bangun Komponen Livewire `Dashboard.php`
- [ ] 2.1 Buat component `app/Livewire/Prototype/Dashboard.php` dengan reactive filter properties (`search`, `status`, `activity`, `customer`, `pic`, `picRole`, `sort`).
- [ ] 2.2 Sediakan method `clearFilter()` untuk mengembalikan filter ke kondisi default (Status: Open, Sort: Oldest, field lain kosong).
- [ ] 2.3 Tangani navigasi Tambah Progress dan Lanjutkan Draft.

### Sub-Task 3: Bangun View `resources/views/livewire/prototype/dashboard.blade.php`
- [ ] 3.1 Tampilkan 3 kartu ringkasan global berlabel "Ringkasan Keseluruhan" dan periode bulan berjalan.
- [ ] 3.2 Tampilkan panel filter dan pencarian instan yang ringkas dan fungsional.
- [ ] 3.3 Tampilkan daftar kerja satu baris per progress (kolom: tanggal, JO/Part, Activity, PIC Progress, ringkasan Next Action, tombol Buka Detail).
- [ ] 3.4 Sediakan tampilan mobile card summary pada 390px.
- [ ] 3.5 Tampilkan empty state yang tepat: "Tidak ada progress Open" (dengan aksi Tambah Progress) vs "Tidak ada hasil yang cocok dengan filter" (dengan aksi Bersihkan Filter).

### Sub-Task 4: Integrasikan ke Route `/prototype` dan Shell
- [ ] 4.1 Hubungkan rute `/prototype` ke `Dashboard` component sebagai tampilan utama tab Dashboard.
- [ ] 4.2 Pastikan navigasi antar tab tetap mulus.

### Sub-Task 5: Pengujian & Verifikasi Otomatis
- [ ] 5.1 Buat feature test `tests/Feature/PrototypeDashboardTest.php`:
  - Verifikasi 3 angka ringkasan global (AT23).
  - Verifikasi default urutan Open terlama lebih dahulu (AT24).
  - Verifikasi pencarian PIC pada progress kompleks (2 problem x 2 corrective action) tetap menghasilkan tepat 1 baris progress unik.
  - Verifikasi filter Customer, Activity, dan Status.
  - Verifikasi tombol Bersihkan Filter (AT24).
  - Verifikasi diferensiasi empty state (AT25).
- [ ] 5.2 Jalankan `php artisan test` dan pastikan seluruh test PASS.
- [ ] 5.3 Jalankan browser visual verification.

### Sub-Task 6: Dokumentasi Handoff & Checklist
- [ ] 6.1 Catat hasil di `docs/progress/P01.md`.
- [ ] 6.2 Centang task `P01-T04` di `task list/FASE_01_Prototype_dan_Validasi.md`.
- [ ] 6.3 Commit ke git repository.
