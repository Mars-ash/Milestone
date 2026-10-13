# P01-T02: Susun Fixture dan Adapter Data Simulasi Implementation Plan

> **Workflow Reference:** [AGENT_WORKFLOW.md](file:///c:/laragon/www/progress-new-model/AGENT_WORKFLOW.md) • [FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md#L89-L112)
> **PRD Reference:** D01 s/d D19, Bagian 23

**Goal:** Menyusun fixture data fiktif terstruktur (`resources/fixtures/prototype.json`), helper dataset (`tests/Fixtures/PrototypeDataset.php`), dan service adapter in-memory/session (`app/Prototype/FakeStore.php`) yang menyimulasikan seluruh operasi CRUD progress, problem berulang, corrective action berulang, Next Action, feedback, draft, Close/Reopen, dan reset data tanpa menyentuh database operasional.

**Architecture:**
- `resources/fixtures/prototype.json`: Matriks data fiktif murni (3 project dengan leading zero, 8 progress mencakup 5 Activity, Open/Closed, 2 problem x 2 corrective action, raw text 1,5 dan 1.5, draft baru dan draft perubahan).
- `tests/Fixtures/PrototypeDataset.php`: Factory/helper class untuk mengakses dan memvalidasi dataset fixture secara terisolasi.
- `app/Prototype/FakeStore.php`: Service class yang mengelola state data prototype via Laravel Session, menyediakan operasi query, get, save progress, save draft, close, reopen, dan reset fixture.
- Automated tests (`tests/Unit/FakeStoreTest.php`): Memastikan integritas relasi anak (corrective action tidak tertukar antar problem), mutasi draft, status toggle, dan idempotensi fungsi reset.

**Tech Stack:**
- PHP 8.4, Laravel 13, Session Store, PHPUnit 12

---

## Global Constraints
- D01: 1 JO No = 1 Part No dan sebaliknya, keduanya unik.
- D02: PIC teks bebas (nama fiktif), tanpa role/project user.
- D03: 5 Activity persis: `Trial`, `Sent Sample`, `Send Document`, `Audit`, `Others`.
- D06: 1 progress memiliki banyak problem; setiap problem memiliki banyak corrective action (masing-masing punya PIC dan actual date).
- D07: Next Action berulang dan berdiri sendiri pada progress.
- D09: Corrective action dan Next Action tidak punya target date; tanggal kosong tidak menentukan status.
- D10: Close manual; Closed harus Reopen sebelum edit; Reopen menjaga isi.
- D11: Autosave = draft; Simpan = progress final; Draft tidak masuk hitungan Open/Closed.
- D15: Ukuran, DH, dan Qty berupa raw text (mempertahankan koma/titik seperti `1,5` dan `1.5`).
- Seluruh lampiran dummy berlabel simulasi.

---

## Tasks & Steps

### Sub-Task 1: Susun File Fixture `resources/fixtures/prototype.json`
- [ ] 1.1 Buat 3 project fiktif (JO No dengan nol di depan, misal `0012/NM/2026`, Part No unik, Part Name, Customer, Model, No Doc, Event 1-3, T0 Target/Actual, DH, Size, Qty raw text).
- [ ] 1.2 Buat 8 progress fiktif mencakup kelima Activity, status Open & Closed, tanggal lama, teks panjang, dan progress kompleks (2 problem x 2 corrective action, 3 Next Action, 2 feedback, lampiran foto simulasi, raw text "1,5" dan "1.5").
- [ ] 1.3 Buat draft baru parsial dan draft perubahan.

### Sub-Task 2: Susun Helper Dataset `tests/Fixtures/PrototypeDataset.php`
- [ ] 2.1 Buat class helper `Tests\Fixtures\PrototypeDataset` untuk memuat dan menyajikan raw array fixture secara terstruktur.
- [ ] 2.2 Sediakan method helper: `raw()`, `projects()`, `progresses()`, `drafts()`.

### Sub-Task 3: Bangun Service `app/Prototype/FakeStore.php`
- [ ] 3.1 Implementasikan inisialisasi session store dari fixture default jika session kosong.
- [ ] 3.2 Implementasikan query method: `getProjects()`, `findProject($idOrJo)`, `getProgresses($filters = [])`, `findProgress($id)`.
- [ ] 3.3 Implementasikan mutasi: `saveProgress(array $data)`, `closeProgress($id)`, `reopenProgress($id)`.
- [ ] 3.4 Implementasikan manajemen draft: `getDrafts()`, `findDraft($idOrUuid)`, `saveDraft(array $payload)`, `deleteDraft($idOrUuid)`.
- [ ] 3.5 Implementasikan `reset()` untuk mengembalikan data simulasi ke kondisi awal fixture.

### Sub-Task 4: Pengujian & Verifikasi Otomatis
- [ ] 4.1 Buat test `tests/Unit/FakeStoreTest.php` untuk memvalidasi:
  - Jumlah 3 project unik dan 8 progress.
  - 5 Activity lengkap serta status Open dan Closed.
  - Relasi anak problem dan corrective action tidak tertukar.
  - Raw text "1,5" dan "1.5" dipertahankan.
  - Close dan Reopen mempertahankan seluruh atribut.
  - Draft tersimpan terpisah dari progress Open/Closed.
  - Reset membersihkan perubahan dan mengembalikan fixture asli.
- [ ] 4.2 Jalankan `php artisan test --filter=FakeStoreTest` dan pastikan PASS 100%.

### Sub-Task 5: Dokumentasi Handoff & Checklist
- [ ] 5.1 Update handoff `docs/progress/P01.md`.
- [ ] 5.2 Centang task `P01-T02` di [task list/FASE_01_Prototype_dan_Validasi.md](file:///c:/laragon/www/progress-new-model/task%20list/FASE_01_Prototype_dan_Validasi.md).
- [ ] 5.3 Commit perubahan ke git repository.
