# Implementation Plan — P01-T07: Prototype Daftar Bertingkat dan Galeri Foto

**Tanggal:** 2026-10-13  
**Task:** P01-T07 Prototype daftar bertingkat dan galeri  
**Acuan PRD:** FR07 s/d FR11, FR19; AT07 s/d AT11, AT26; D04, D05, D06, D07, D08, D09, D12, D18, D19  
**Prasyarat:** P01-T06 (Selesai di commit `d3fd3c7`)

---

## 1. Tujuan & Kontrak Bisnis
1. **Struktur Bertingkat Problem & Corrective Action (FR07, AT07, D06):**
   - 1 Progress dapat memiliki banyak Problem (Induk).
   - Setiap Problem dapat memiliki banyak Corrective Action (Anak).
   - Setiap Problem dapat memiliki banyak foto lampiran dengan caption.
   - Penomoran tampilan bersifat dinamis (label), key item menggunakan `id` / `client_id` unik stabil agar urutan dan nilai tidak tertukar saat penambahan/penghapusan item.
2. **Next Action Independen (FR08, AT08, D07):**
   - Next Action berada langsung pada Progress sebagai **saudara** Problem (bukan anak dari Problem).
   - Memiliki uraian kegiatan, PIC teks bebas, dan actual date (opsional, tanpa default hari ini).
3. **Feedback Customer Berulang (FR09, AT09, D08):**
   - Riwayat catatan tanggapan customer independen dengan tanggal feedback, isi, PIC customer teks bebas, dan lampiran.
4. **Galeri Foto & Lampiran Simulasi (FR10, FR11, FR19, AT10, AT11, AT26, D12):**
   - Menampilkan thumbnail foto dengan badge `[SIMULASI]`.
   - Dialog Lightbox modal untuk preview foto ukuran besar.
   - Simulasi upload foto baru (JPG/PNG/WebP) dengan status simulasi berhasil dan simulasi gagal.
   - Pembedaan dokumen baru vs arsip metadata dokumen lama (D18).
5. **PIC Teks Bebas & No Target Date (D02, D08, D09, D16):**
   - Seluruh kolom PIC adalah teks bebas tanpa ketergantungan tabel user.
   - Tidak ada kolom Target Date pada action; tanggal aktual opsional tanpa default hari ini.

---

## 2. File Sasaran & Struktur Komponen

### A. Komponen Blade Reusable
- `resources/views/components/progress/problems.blade.php`: Kontainer daftar problem dan manajemen interaktif penambahan problem.
- `resources/views/components/progress/corrective-actions.blade.php`: Sub-daftar corrective actions yang bersarang di dalam problem.
- `resources/views/components/progress/next-actions.blade.php`: Daftar saudara next actions.
- `resources/views/components/progress/feedback.blade.php`: Daftar feedback customer.
- `resources/views/components/progress/gallery.blade.php`: Komponen galeri foto simulasi per problem dengan lightbox modal.

### B. Livewire Component & View
- `app/Livewire/Prototype/ProgressForm.php`:
  - Penambahan method: `addProblem()`, `removeProblem()`, `addCorrectiveAction()`, `removeCorrectiveAction()`, `addNextAction()`, `removeNextAction()`, `addFeedback()`, `removeFeedback()`, `addSimulationPhoto()`, `removeSimulationPhoto()`.
  - Normalisasi data dengan client ID unik menggunakan `Str::random(6)`.
- `resources/views/livewire/prototype/progress-form.blade.php`:
  - Penyambungan tab sub-navigasi (Problem & Tindakan, Next Action, Feedback, Lampiran) dengan render komponen-komponen baru secara mulus.

### C. Testing & Verifikasi
- `tests/Feature/PrototypeNestedListsTest.php`:
  - Uji AT07: Menambah beberapa corrective action pada problem yang berbeda, PIC berbeda, data tidak tertukar saat menambah problem baru.
  - Uji AT08: Next action berdiri sendiri sebagai saudara problem.
  - Uji AT09: Feedback berulang dengan PIC dan tanggal mandiri.
  - Uji AT10 & AT26: Galeri foto per problem, penambahan foto simulasi, badge [SIMULASI], lightbox preview.
  - Uji Simpan utuh ke FakeStore dengan seluruh relasi graf tersimpan.
- Browser Subagent:
  - Uji interaktif di browser (1366x768 & 390x844).
  - Screenshot dan rekaman video WebP.

---

## 3. Tahapan Eksekusi
1. Buat 5 komponen Blade di `resources/views/components/progress/`.
2. Perluas `app/Livewire/Prototype/ProgressForm.php` dengan method reaktif manipulasi data bertingkat.
3. Hubungkan ke `resources/views/livewire/prototype/progress-form.blade.php`.
4. Buat feature test `tests/Feature/PrototypeNestedListsTest.php` dan jalankan `php artisan test`.
5. Jalankan verifikasi visual browser subagent (Desktop & Mobile 390px).
6. Perbarui dokumentasi handoff di `docs/progress/P01.md` dan checklist `task list/FASE_01_Prototype_dan_Validasi.md`.
7. Git commit.
