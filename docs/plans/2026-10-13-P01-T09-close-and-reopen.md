# Implementation Plan — P01-T09: Prototype Detail Close dan Reopen

**Tanggal:** 2026-10-13  
**Task:** P01-T09 Prototype detail Close dan Reopen  
**Acuan PRD:** FR16; AT20, AT21, AT22; PRD Bagian 11; D02, D10  
**Prasyarat:** P01-T04, P01-T08 (Keduanya selesai)

---

## 1. Analisis Kebutuhan & Kontrak Bisnis
1. **D10 & FR16: Close adalah Keputusan Eksplisit Admin:**
   - Status Open/Closed hanya diubah manual melalui tombol Close / Reopen oleh Admin.
   - Tidak ada syarat kelengkapan data (walaupun actual date kosong, feedback kosong, atau temuan problem belum selesai, progress tetap BISA ditutup).
   - Menutup progress tidak membatalkan atau mengubah draft aktif yang belum diterapkan (menutup versi tersimpan resmi).
2. **AT21: Closed Memblokir Edit Langsung (Read-Only):**
   - Saat status `Closed`, form edit langsung dinonaktifkan/diblokir.
   - Menampilkan banner peringatan status Closed dan tombol "Reopen Progress".
   - Mencoba mengakses form edit pada progress berstatus Closed akan menampilkan peringatan bahwa progress harus di-Reopen terlebih dahulu sebelum dapat diedit.
3. **AT22: Reopen Menjaga Seluruh Isi Data:**
   - Mengklik Reopen mengubah status kembali ke `Open`.
   - Seluruh data (catatan, tanggal, problem, corrective action, foto lampiran, feedback, dan rincian trial) tetap utuh 100% tanpa ada yang direset.
   - Tombol "Edit Progress" kembali aktif.
4. **Isolasi Efek (D02, D10):**
   - Perubahan status satu progress tidak mempengaruhi project master (karena Status Project dihapus per D02) dan tidak mempengaruhi record progress lainnya.
   - Dashboard segera mencerminkan perpindahan count Open / Closed.

---

## 2. Struktur File & Rencana Komponen
1. `resources/views/components/progress/status-actions.blade.php`:
   - Komponen Blade reusable untuk kontrol tombol status:
     - Jika Open: Tombol "Tutup Progress (Close)" dengan modal konfirmasi (atau konfirmasi langsung) dan tombol "Edit Progress".
     - Jika Closed: Tombol "Buka Kembali (Reopen Progress)" dan tombol "Edit Progress" dinonaktifkan dengan tooltip/pesan peringatan.
2. `app/Livewire/Prototype/ProgressDetail.php`:
   - Komponen Livewire untuk menampilkan rincian progress secara mendalam (Identitas Project terkini FR02/AT03, Data Aktivitas & PIC teks bebas, Rincian Mesin & DH, Problem & Tindakan bertingkat, Next Action, Feedback Customer, Galeri Foto dengan badge [SIMULASI] & Lightbox, serta status Open/Closed).
   - Method: `closeProgress()`, `reopenProgress()`.
3. `resources/views/livewire/prototype/progress-detail.blade.php`:
   - Tampilan visual detail progress berstandar PRD Bagian 6 (UI01–UI12) dengan responsivitas desktop & mobile.
4. Integrasi ke `resources/views/livewire/prototype/shell.blade.php`:
   - Penanganan query `?tab=progresses&detail=<id>` atau `?tab=progress_detail&id=<id>` untuk merender `<livewire:prototype.progress-detail :progressId="..." />`.
5. `app/Livewire/Prototype/ProgressForm.php`:
   - Tambahkan pengecekan: jika progress yang diedit berstatus `Closed`, cegah perubahan atau tampilkan banner bahwa progress harus di-Reopen sebelum diedit.

---

## 3. Rencana Pengujian Otomatis
- `tests/Feature/PrototypeCloseReopenTest.php`:
  - Uji AT20: Close progress berhasil meskipun actual date dan catatan tindakan kosong.
  - Uji AT21: Saat Closed, status menjadi Closed, edit diblokir dan muncul tombol Reopen.
  - Uji AT22: Reopen mengembalikan status menjadi Open dengan SELURUH data tetap utuh.
  - Uji Isolasi: Menutup satu progress tidak mengubah progress lain dan tidak mengubah project.

---

## 4. Rencana Verifikasi Visual & Browser
- `browser_subagent`:
  - Buka detail progress Open -> klik Close -> verifikasi status berubah menjadi Closed dan banner/tombol Reopen muncul.
  - Klik Reopen -> verifikasi status kembali Open dan data tetap utuh.
  - Verifikasi desktop 1366x768 & mobile 390x844.
