# Review UX Operasional dan Alur Browser (Fase 05)

Dokumen ini merekam hasil evaluasi alur pengguna nyata, uji kegagalan jaringan, konkurensi tab, dan aksesibilitas sesuai task P05-T09 dan kriteria AT12–AT25 serta AT37.

---

## 1. Skenario yang Dievaluasi

### A. Pengisian Form Panjang & Autosave Parsial (AT12, AT13, AT14)
- **Perilaku:** Saat pertama kali membuka form baru (`/prototype?tab=form` atau form operasional), tidak ada record draft kosong yang dibuat ke basis data.
- **Pemicu Autosave:** Begitu input pertama dimodifikasi atau item baru ditambahkan, autosave menyimpan snapshot graph parsial ke tabel `progress_drafts`.
- **Indikator Simpan:** Komponen `<x-progress.save-state>` menampilkan indikator waktu nyata:
  - `Menyimpan draft...` (saat request berlangsung)
  - `Draft tersimpan [jam]` (konfirmasi server)
  - `Perubahan belum disimpan` (saat data lokal kotor sebelum debounce 1 detik)
  - `Gagal menyimpan draft` + tombol **Coba lagi** (jika ada error koneksi)

### B. Pemulihan Lintas Sesi & Crash Recovery (AT15, AT16)
- **Perilaku:** Refresh halaman (F5), navigasi kembali dari daftar, atau membuka URL draft (`?draft={uuid}`) mengembalikan seluruh state yang telah dikonfirmasi server.
- **Graph Anak & Lampiran:** Seluruh `client_id` problem, corrective action, next action, dan feedback tetap konsisten. Staging attachments (foto/dokumen) tetap tertaut ke draft dan siap dipromosikan saat final save.

### C. Proteksi Konkurensi & Dua Tab (AT17, AT18)
- **Urutan Request (AT17):** Request autosave lama yang tiba terlambat karena latensi jaringan diabaikan (`request_sequence < draft->request_sequence`), sehingga data yang lebih baru tidak tertimpa.
- **Konflik Revisi Tab Ganda (AT18):**
  - Jika Tab A dan Tab B membuka draft yang sama dan Tab A menyimpan perubahan (menaikkan `draft_revision`), Tab B yang mengirim revisi lama akan mendeteksi `VersionConflict`.
  - UI Tab B menampilkan banner peringatan **Konflik Versi Terdeteksi** tanpa menghapus isian lokal pengguna dan tanpa merge otomatis yang tidak aman.
  - Data di database dari Tab A tetap terlindungi.

### D. Siklus Close & Reopen Manual (AT20, AT21, AT22)
- **Close Manual (AT20):** Status progress dapat diubah menjadi `Closed` tanpa syarat kelengkapan (misal: tindakan belum selesai atau actual date kosong tetap diizinkan).
- **Proteksi Read-Only (AT21):** Saat Closed, form edit langsung diblokir dan autosave dinonaktifkan. UI menampilkan status `Closed` dan tombol `Reopen progress`.
- **Reopen Manual (AT22):** Menekan tombol Reopen mengembalikan status menjadi `Open`, menaikkan nomor `revision`, dan mempertahankan 100% isi data (problem, corrective action, lampiran foto/dokumen, catatan rincian kegiatan).

### E. Tiga KPI Dashboard & Daftar Kerja (AT23, AT24, AT25)
- **KPI Monitoring (AT23):**
  - Open Projects: jumlah project unik yang memiliki sedikitnya satu progress Open.
  - Open Progress: seluruh progress Open di database.
  - Closed Progress: seluruh progress Closed di database.
  - Draft Aktif: terisolasi dan tidak dihitung ke Open maupun Closed.
- **Filter PIC Lintas Peran (AT24):** Pencarian teks bebas PIC dapat diarahkan ke semua role (`all`) atau role tertentu (`corrective_action`, `next_action`, `feedback`, `customer`).
- **Pagination & Sorting (AT25):** 15 baris per halaman, sort default terbaru/terlama, dan reset halaman otomatis saat filter berubah.

---

## 2. Aksesibilitas & Responsivitas Mobile

1. **Mobile Viewport 390px (iPhone 12/13/14 Baseline):**
   - Savebar fixed tetap terlihat dan tidak menutupi konten penting.
   - Tidak terjadi scroll horizontal berlebih (`scrollWidth <= innerWidth + 1`).
2. **Keyboard Accessibility:**
   - Navigasi tab status mendukung tombol panah kiri/kanan (`ArrowLeft`/`ArrowRight`).
   - Tombol tambah baris secara otomatis mengarahkan fokus keyboard ke field input baru yang dibuat (`x-on:editor-focus`).
   - Modal konfirmasi (buang draft, hapus baris, close/reopen) dapat dibatalkan melalui keyboard Escape atau tombol Batal.

---

## 3. Bukti Pengujian Otomatis

| Berkas Pengujian | Skenario / Acuan | Status |
| :--- | :--- | :--- |
| `tests/Feature/Drafts/SaveDraftTest.php` | AT12, AT13 (Autosave parsial) | Passed (6 tests, 33 assertions) |
| `tests/Feature/Drafts/DraftRecoveryTest.php` | AT14, AT15, AT16, AT37 (Pemulihan & retry) | Passed (7 tests, 47 assertions) |
| `tests/Feature/Drafts/DraftConcurrencyTest.php` | AT17, AT18 (Out of order & dua tab) | Passed (3 tests, 22 assertions) |
| `tests/Feature/Drafts/ApplyDraftTest.php` | AT14, AT15, AT16 (Simpan final idempoten) | Passed (6 tests, 36 assertions) |
| `tests/Feature/Drafts/DiscardDraftTest.php` | AT19 (Buang draft & cleanup staging) | Passed (4 tests, 21 assertions) |
| `tests/Feature/Progress/CloseReopenTest.php` | AT20, AT21, AT22 (Siklus Close & Reopen) | Passed (5 tests, 26 assertions) |
| `tests/Feature/Dashboard/DashboardSummaryTest.php` | AT23 (3 Ringkasan KPI) | Passed (1 test, 10 assertions) |
| `tests/Feature/Progress/ProgressFilterTest.php` | AT24, AT25 (Filter PIC & Pagination) | Passed (3 tests, 22 assertions) |
| `tests/Feature/Progress/ProgressWorkflowTest.php` | End-to-end Operational Workflow | Passed (1 test, 34 assertions) |
| `tests/Browser/progress-workflow.spec.js` | Browser E2E Playwright validation | Spec Created |
