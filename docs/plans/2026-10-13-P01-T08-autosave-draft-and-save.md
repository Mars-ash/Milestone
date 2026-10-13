# Implementation Plan — P01-T08: Prototype Autosave Draft dan Simpan Final

**Tanggal:** 2026-10-13  
**Task:** P01-T08 Prototype autosave draft dan Simpan  
**Acuan PRD:** FR12 sampai FR15; AT12 sampai AT19, AT37; D11  
**Prasyarat:** P01-T06, P01-T07 (Keduanya selesai)

---

## 1. Analisis Kebutuhan & Kontrak Bisnis
1. **D11: Draft vs Simpan Final:**
   - Autosave hanya menyimpan ke Draft (tidak memengaruhi data progress tersimpan di dashboard/daftar kerja, dan tidak dihitung dalam count Open/Closed).
   - Draft boleh tersimpan parsial (bahkan jika JO belum dipilih atau tanggal kosong).
   - Jangan membuat draft kosong otomatis hanya karena form baru dibuka (hanya autosave jika ada data/perubahan yang diisi).
2. **Indikator Visual Status Simpan (`save-state.blade.php` / UI07):**
   - Menampilkan status visual: "Menyimpan draft..." (spinner), "Draft tersimpan [HH:mm]", "Belum tersimpan", atau "Gagal menyimpan".
   - Dukungan tombol coba lagi (Retry) jika terjadi simulasi kegagalan.
3. **Daftar Draft Terpadu (`DraftList.php`):**
   - Menampilkan daftar draft yang tersimpan (Draft Baru vs Draft Perubahan).
   - Tombol "Lanjutkan Draft" yang membuka form dengan parameter `?tab=form&draft=<uuid>`.
   - Tombol "Buang Draft" dengan modal dialog konfirmasi yang aman (menghapus draft tanpa mempengaruhi data master/progress).
4. **Pemisahan Adapter Draft (`FakeDraftStore.php`):**
   - Adapter khusus untuk mengelola lifecycle draft pada session/store prototype: list, find, save, discard, purge.
5. **Simulasi Error & Konflik Tab (State Prototype):**
   - Mendukung simulasi penanganan validasi gagal, simulasi konflik tab (misal draft ditimpa sesi lain), dan simulasi retry.

---

## 2. Struktur File & Rencana Komponen
1. `app/Prototype/FakeDraftStore.php`:
   - Class adapter draft mandiri yang terhubung dengan session storage `FakeStore`.
   - Method: `all()`, `find(string $uuid)`, `save(array $payload)`, `delete(string $uuid)`, `discard(string $uuid)`.
2. `resources/views/components/progress/save-state.blade.php`:
   - Komponen Blade untuk rendering status save/autosave, waktu tersimpan, dan tombol retry bila error.
3. `app/Livewire/Prototype/DraftList.php`:
   - Komponen Livewire untuk menampilkan daftar draft di tab "Draft Tersimpan" (menggantikan tampilan statis di Shell).
   - Aksi: `discardDraft(string $uuid)` dengan konfirmasi modal.
4. `resources/views/livewire/prototype/draft-list.blade.php`:
   - View Blade daftar draft responsif desktop & mobile.
5. `app/Livewire/Prototype/ProgressForm.php` & view:
   - Integrasi autosave reaktif (listener `updated` property), deteksi form dirty (tidak membuat draft kosong), tombol "Buang Draft", dan penanganan parameter `?draft=<uuid>`.
6. `resources/views/livewire/prototype/shell.blade.php`:
   - Integrasi `<livewire:prototype.draft-list />` pada tab `'drafts'`.

---

## 3. Rencana Pengujian Otomatis
- `tests/Feature/PrototypeAutosaveDraftTest.php`:
  - Uji AT12/AT13: Perubahan data pada form memicu simpan draft tanpa merubah data progress resmi di dashboard.
  - Uji AT14: Draft baru dibuka tidak membuat record draft bila belum ada input (dirty check).
  - Uji AT15/AT16: Melanjutkan draft dari URL/uuid mengembalikan seluruh isian.
  - Uji AT17/AT18: Simpan final menerapkan perubahan ke progress, menghapus draft terkait, dan mengupdate dashboard.
  - Uji AT19: Buang draft melalui dialog konfirmasi menghapus draft dari `DraftList`.
  - Uji AT37: Status autosave waktu tersimpan HH:mm.

---

## 4. Rencana Verifikasi Visual & Browser
- Uji interaktif via `browser_subagent`:
  - Buka progress form, isi field, cek status autosave "Draft tersimpan", cek dashboard (data lama tetap), cek tab Draft (draft ada), klik "Buang Draft" / "Lanjutkan Draft", dan Simpan final.
  - Screenshot desktop 1366x768 & mobile 390x844.
