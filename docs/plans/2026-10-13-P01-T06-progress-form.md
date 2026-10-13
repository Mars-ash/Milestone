# P01-T06: Prototype Form Progress yang Sama pada Semua Activity Implementation Plan

> **Workflow Reference:** [AGENT_WORKFLOW.md](file:///c:/laragon/www/progress-new-model/AGENT_WORKFLOW.md) • [FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md#L182-L204)
> **PRD Reference:** FR04, FR05, AT05, AT06, Bagian 6, Bagian 16, D03, D04, D05, D06, D07, D08, D15

**Goal:** Membangun antarmuka form progress interaktif (`app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`, `resources/views/components/progress/identity.blade.php`) dengan satu bentuk form yang identik untuk kelima Activity (Trial, Sent Sample, Send Document, Audit, Others), dropdown JO yang dapat dicari dengan autofill 5 field identitas hanya-baca, input raw text koma/titik, PIC teks bebas, serta validasi final yang tepat.

---

## Global Constraints & Business Rules
1. **FR04 & AT05:** Kelima Activity persis: `Trial`, `Sent Sample`, `Send Document`, `Audit`, `Others`. Semua field dan bagian tetap tersedia dan identik untuk kelima Activity. Pergantian Activity tidak boleh menghapus atau menyembunyikan isian (misal mengisi Mesin dan DH pada Trial, lalu diubah ke Others, nilai tetap utuh).
2. **FR02 & AT03:** Pemilihan JO otomatis memicu autofill 5 field identitas hanya-baca dari master project terkini: Part No, Part Name, Customer, Model, dan No Doc.
3. **FR05 & AT06:** PIC Progress dan PIC Customer berupa teks bebas tanpa ketergantungan akun/master PIC. Nilai raw text koma dan titik (misal `1,5` dan `1.5`) dipertahankan tanpa konversi otomatis.
4. **Validasi Simpan Baru:** Hanya tiga field yang wajib: `project_id` (JO No), `activity_date` (Tanggal Aktivitas), dan `activity` (Activity). Uraian, PIC, foto, problem, dan detail trial adalah opsional untuk simpan baru.
5. **UI05 & UI06:** Navigasi antar bagian yang nyaman untuk teks panjang tanpa kehilangan fokus atau isian.

---

## Architecture & File Targets
- `resources/views/components/progress/identity.blade.php`:
  - Reusable component yang menampilkan 5 kartu/field identitas hanya-baca (Part No, Part Name, Customer, Model, No Doc) saat project dipilih.
- `app/Prototype/FakeStore.php`:
  - Perluas `saveProgress` untuk memastikan kelengkapan field rincian: `process_trial`, `machine`, `die_high_raw`, `material_actual_trial`, `qty_material_raw`, `qty_process_raw`, `qty_sample_raw`, `event_note`, `remark`, `notes`/`description`, `pic_progress`, `pic_customer`.
- `app/Livewire/Prototype/ProgressForm.php`:
  - Komponen Livewire form progress: mengelola state `projectId`, autofill identitas, `activityDate`, `activity`, `picProgress`, `picCustomer`, `notes`, serta rincian parameter trial.
  - Mengelola tab bagian: Data Aktivitas & Trial, Problem & Corrective Action, Next Action, Feedback Customer, Lampiran.
  - Method `updatedProjectId()` untuk autofill identitas instan.
  - Method `save()` untuk validasi final dan simpan ke `FakeStore`.
- `resources/views/livewire/prototype/progress-form.blade.php`:
  - Form Blade lengkap dengan dropdown JO searchable, 5 pill selector Activity, bagian identitas hanya-baca, rincian trial, dan navigasi tab bagian.
- Integrasi Shell:
  - Hubungkan tab `form` di `shell.blade.php` atau tombol "Tambah Progress" di Dashboard agar langsung membuka `ProgressForm`.
- `tests/Feature/PrototypeProgressFormTest.php`:
  - Feature test untuk AT05 (pergantian Activity mempertahankan isian), AT06 (PIC teks bebas dan raw number), autofill 5 identitas hanya-baca, serta validasi 3 field wajib.

---

## Implementation Steps
1. **Step 1:** Perluas `saveProgress` di `FakeStore.php` agar menormalisasi seluruh field rincian progress Bagian 16 PRD.
2. **Step 2:** Buat komponen Blade `resources/views/components/progress/identity.blade.php`.
3. **Step 3:** Buat komponen Livewire `app/Livewire/Prototype/ProgressForm.php`.
4. **Step 4:** Buat tampilan view `resources/views/livewire/prototype/progress-form.blade.php`.
5. **Step 5:** Integrasikan ke `shell.blade.php` pada tab `form`.
6. **Step 6:** Buat automated test `tests/Feature/PrototypeProgressFormTest.php` dan jalankan verifikasi `php artisan test`.
7. **Step 7:** Uji visual di browser dengan `browser_subagent`.
8. **Step 8:** Dokumentasikan handoff di `docs/progress/P01.md`, centang `P01-T06` di task list, dan commit ke git.
