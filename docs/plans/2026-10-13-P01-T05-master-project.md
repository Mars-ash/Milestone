# P01-T05: Prototype Master Project dan Identitas Terkini Implementation Plan

> **Workflow Reference:** [AGENT_WORKFLOW.md](file:///c:/laragon/www/progress-new-model/AGENT_WORKFLOW.md) • [FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md#L159-L181)
> **PRD Reference:** FR01, FR02, FR03, AT01, AT02, AT03, AT04, Bagian 15, D01, D02, D14, D15, D16

**Goal:** Membangun manajemen Master Project (`app/Livewire/Prototype/ProjectForm.php`, `app/Livewire/Prototype/ProjectDetail.php`, `resources/views/livewire/prototype/project-form.blade.php`, `resources/views/livewire/prototype/project-detail.blade.php`) dengan validasi wajib JO No, Part No, dan Part Name, penolakan duplikasi JO (AT01) dan duplikasi Part No (AT02) secara terpisah tanpa menghilangkan angka nol di depan, jadwal manual independen (T0 Target/Actual, Event 1–3, dll tanpa trigger otomatis dari Activity), penghapusan total field Project User dan Status Project (D02), serta keterkinian identitas dinamis pada progress lama (AT03).

---

## Global Constraints & Business Rules
1. **D01:** Satu JO No untuk satu Part No dan sebaliknya. Keduanya wajib unik.
2. **D02 / FR01:** `Project User` dan `Status Project` dihapus permanen; tidak memiliki field, input, badge, kolom, atau logika apa pun.
3. **D14 / FR03 / AT04:** T0 Target/Actual dan Event 1 sampai 3 adalah tanggal manual terpisah; pengisian Sent Sample tidak boleh memicu T0 Actual secara otomatis.
4. **D15:** DH, Ukuran, Qty, Material Thickness/Width berupa teks mentah (raw text), mempertahankan koma/titik.
5. **D16 / FR02 / AT03:** Identitas progress (Part No, Part Name, Customer, Model, No Doc) selalu merujuk ke data Project Master terkini; koreksi master langsung tercermin saat melihat progress tanpa mengubah catatan uraian kegiatan.
6. **AT01:** Duplikasi JO pada project baru ditolak tegas dengan pesan error spesifik.
7. **AT02:** Duplikasi Part No pada project baru ditolak tegas dengan pesan error spesifik; nol di depan tetap utuh.

---

## Architecture & File Targets
- `app/Prototype/FakeStore.php`:
  - Tambahkan method `saveProject(array $data)`: validasi trim, cek duplikasi `jo_no` dan `part_no`, normalisasi field lengkap Bagian 15 (23 field aktif).
  - Tambahkan method `deleteProject(string $id)` (opsional jika dibutuhkan admin).
- `app/Livewire/Prototype/ProjectForm.php`:
  - Form Livewire untuk membuat project baru (`id = null`) atau mengedit project (`id = 'proj-xxx'`).
  - Aturan validasi Livewire untuk `jo_no`, `part_no`, `part_name`, dll.
  - Sinyal event & redirect/navigasi kembali ke list atau detail.
- `resources/views/livewire/prototype/project-form.blade.php`:
  - Formulir tertata rapi dalam kelompok logis: Identitas Utama (JO, Part No, Part Name, Customer, Model, No Doc), Spesifikasi Material & Proses (Material, Spec, Thickness, Width, Process, DH, Size, Qty), serta Jadwal & Target Manual (Date Receive, T0 Target/Actual, Plan Start/Finish, Actual Start/Finish, Event 1–3, Remark).
  - Menghilangkan sama sekali input `Project User` dan `Status Project`.
- `app/Livewire/Prototype/ProjectDetail.php`:
  - Tampilan detail komprehensif master project: data identitas lengkap, kartu jadwal master, serta daftar riwayat progress terkait yang otomatis mencerminkan identitas master terkini.
- `resources/views/livewire/prototype/project-detail.blade.php`:
  - View Blade detail project master dengan tombol "Edit Project" dan daftar progress.
- Integrasi ke Shell layout (`resources/views/livewire/prototype/shell.blade.php`):
  - Memperbarui tab `projects` agar dapat menampilkan daftar master project, membuka modal/tampilan form baru, mengedit master, dan membuka detail master.

---

## Implementation Steps

### Step 1: Perluas `FakeStore.php` dengan Operasi Project
- [ ] 1.1 Implementasikan `saveProject(array $data)` di `FakeStore.php`.
  - Validasi keunikan `jo_no` (case-insensitive & trim) terhadap project lain.
  - Validasi keunikan `part_no` (case-insensitive & trim) terhadap project lain.
  - Validasi keberadaan `jo_no`, `part_no`, dan `part_name`.
  - Pertahankan tipe data teks dan leading zero.
  - Simpan dan mutasikan array store.
- [ ] 1.2 Unit test di `tests/Unit/FakeStoreTest.php` untuk memvalidasi penolakan duplikasi JO, penolakan duplikasi Part No, pembaruan master, dan keterkinian identitas pada progress.

### Step 2: Bangun Komponen Livewire `ProjectForm.php` & View
- [ ] 2.1 Buat component `app/Livewire/Prototype/ProjectForm.php` dengan properti form lengkap sesuai 23 kolom master Bagian 15.
- [ ] 2.2 Tangani logika `mount(?string $projectId = null)`.
- [ ] 2.3 Tangani `save()` dengan validasi pesan error spesifik dan navigasi.
- [ ] 2.4 Buat view `resources/views/livewire/prototype/project-form.blade.php` dengan UI bersih tanpa `Project User` dan `Status Project`.

### Step 3: Bangun Komponen Livewire `ProjectDetail.php` & View
- [ ] 3.1 Buat component `app/Livewire/Prototype/ProjectDetail.php` untuk menampilkan detail satu project dan daftar progressnya.
- [ ] 3.2 Buat view `resources/views/livewire/prototype/project-detail.blade.php`.

### Step 4: Integrasikan ke Tab Project Master di Shell
- [ ] 4.1 Perbarui tab `projects` di `shell.blade.php` untuk mendukung tombol "Tambah Project", klik baris / tombol "Detail", dan tombol "Edit".
- [ ] 4.2 Pastikan navigasi tab tetap mulus.

### Step 5: Pengujian & Verifikasi Otomatis
- [ ] 5.1 Buat `tests/Feature/PrototypeProjectTest.php` untuk memverifikasi:
  - AT01: Duplikasi JO ditolak pada project baru.
  - AT02: Duplikasi Part No ditolak; leading zero tetap utuh.
  - AT03: Koreksi Part Name / identitas pada master otomatis terlihat pada progress terkait.
  - AT04: Jadwal manual T0 Target/Actual dan Event 1–3 tersimpan mandiri.
  - Penghapusan Project User dan Status Project.
- [ ] 5.2 Jalankan `php artisan test` dan pastikan seluruh test PASS.
- [ ] 5.3 Jalankan browser visual verification.

### Step 6: Dokumentasi Handoff & Checklist
- [ ] 6.1 Catat handoff di `docs/progress/P01.md`.
- [ ] 6.2 Centang `P01-T05` di `task list/FASE_01_Prototype_dan_Validasi.md`.
- [ ] 6.3 Commit git: `feat(p01): prototype master project dan identitas terkini (P01-T05)`.
