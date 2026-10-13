# Fase 05 Draft status dan monitoring

Acuan PRD versi 2.0 • 13 Oktober 2026

Menyelesaikan autosave yang menjaga isian, status manual, dan dashboard operasional tanpa hitungan ganda.

## Prasyarat dan bahan baca

Fase 04 selesai; semua graph dan lampiran bisa disimpan melalui service.

Baca PRD_Progress_New_Model.md: Bagian 4, 7, 10, 11, 17; FR12 sampai FR18.

## Keluaran fase

- Autosave draft parsial dan pemulihan lintas sesi.
- Simpan graph idempoten dengan kontrol konflik.
- Close/Reopen manual dan dashboard berbasis data nyata.

## Kontrak yang tidak boleh berubah

- Satu admin; semua PIC teks bebas. Satu JO untuk satu Part No dan sebaliknya.
- Project User dan Status Project tidak ada. Open/Closed hanya milik progress.
- Lima Activity persis Trial, Sent Sample, Send Document, Audit, Others; bentuk form sama.
- Identitas progress mengikuti master terkini. Master wajib JO, Part No, Part Name; progress final wajib JO, tanggal, Activity.
- Banyak problem per progress; banyak corrective action per problem; setiap tindakan mempunyai PIC dan actual date sendiri.
- Next Action berada pada progress, terpisah dari problem. Feedback dapat berulang.
- Action tidak mempunyai target date; tanggal kosong tidak menentukan status.
- Autosave adalah draft; Simpan menerapkan perubahan. Draft tidak dihitung Open/Closed.
- Close manual tanpa syarat kelengkapan; Closed perlu Reopen sebelum edit. Reopen menjaga isi.
- DH berarti Die High; T0 adalah pengiriman sampel pertama; semua tanggal master manual. PP1/PP2/MP menjadi Event 1/2/3.
- Ukuran/DH/Qty berupa raw text, mempertahankan koma/titik; tidak ada perhitungan numerik otomatis.
- Tidak ada audit trail produk, report/export, link antaraktivitas, master PIC, atau penutupan otomatis.
- Seluruh histori Access dimigrasikan; perkiraan 250 progress diverifikasi. Foto lama ikut; byte dokumen lama tidak; nama/keterangannya menjadi metadata arsip.
- Foto baru JPG/JPEG, PNG, WebP. Dokumen baru tetap didukung sesuai konfigurasi OP03.
- Stack Laravel 13, PHP 8.4, PostgreSQL 18, Blade, Livewire 4, Tailwind 4 sebagai baseline; lock versi aktual saat setup.
- Prototype harus diterima pengguna sebelum fondasi operasional dilanjutkan. Perubahan PRD perlu konfirmasi pemilik kebutuhan.

## Cara AI menjalankan fase ini

1. Baca bagian PRD yang disebut dan catatan handoff fase sebelumnya. Path file di sini adalah target repository aplikasi, bukan file yang sudah tersedia dalam paket dokumentasi.
2. Periksa struktur repository dan status git sebelum mengedit. Pertahankan pekerjaan yang sudah ada; jangan melakukan reset/destructive cleanup.
3. Kerjakan satu task yang prasyaratnya selesai. Pecah sublangkah bila perlu; jangan menjalankan seluruh fase dalam satu perubahan besar.
4. Gunakan service/komponen yang sudah ada. Jika path aktual berbeda, catat pemetaannya; jangan membuat implementasi paralel tanpa alasan.
5. Verifikasi sesuai risiko task. Catat command, hasil aktual, file yang berubah, dan keterbatasan. Jangan mengarang hasil test, review, atau migrasi.
6. Jika keputusan bisnis kurang jelas, kumpulkan pertanyaan terkait sekaligus. Jangan menambah required field, role, target date, atau fitur lain untuk mengisi kekosongan.
7. Update checklist dan docs/progress/Pxx.md hanya sesudah bukti selesai. Tugas blocked tetap tidak dicentang; tulis sebab dan input yang dibutuhkan.
8. Hentikan fase pada gate yang membutuhkan review pengguna/lingkungan. Ini gate pelaksanaan, bukan izin untuk mengklaim persetujuan sudah ada.

## Daftar task

- [ ] P05-T01 Implementasikan penyimpanan draft parsial
- [ ] P05-T02 Hubungkan autosave dengan state UI dan pemulihan
- [ ] P05-T03 Lindungi urutan autosave dan konflik dua tab
- [ ] P05-T04 Integrasikan Simpan final dan idempotensi draft
- [ ] P05-T05 Selesaikan draft file dan Buang Draft
- [ ] P05-T06 Implementasikan Close Reopen dan draft belum diterapkan
- [ ] P05-T07 Implementasikan tiga ringkasan dashboard
- [ ] P05-T08 Implementasikan daftar kerja filter PIC dan pagination
- [ ] P05-T09 Uji alur browser kegagalan dan aksesibilitas
- [ ] P05-T10 Review kesiapan aplikasi untuk migrasi

### P05-T01 Implementasikan penyimpanan draft parsial

**Acuan:** FR12 FR15; AT12 AT13.

**Prasyarat:** P04-T09.

**File atau keluaran sasaran:**

- `app/Services/ProgressDraftService.php`
- `app/Models/ProgressDraft.php`
- `tests/Feature/Drafts/SaveDraftTest.php`

**Langkah kerja:**

1. Buat draft hanya setelah perubahan pertama. Draft baru tidak memerlukan JO, Activity, atau tanggal; draft edit menyimpan progress_id dan base_revision.
2. Simpan graph JSON dengan schema_version, client_id anak, dan attachment_refs staging yang telah diunggah.
3. Tegakkan satu draft edit aktif per admin per progress; draft baru boleh banyak dengan UUID berbeda.
4. Return draft_revision dan saved_at dari server. Jangan menjalankan validasi final atau mengubah progress aktif saat autosave.

**Selesai jika:** Draft parsial tersimpan terpisah dan dapat dimuat kembali tanpa mengubah data operasional.

**Verifikasi:** Jalankan AT12/AT13; pastikan membuka form kosong tidak menambah draft dan autosave edit tidak mengubah progresses.

### P05-T02 Hubungkan autosave dengan state UI dan pemulihan

**Acuan:** FR12 FR15; bagian 7.

**Prasyarat:** P05-T01.

**File atau keluaran sasaran:**

- `app/Livewire/Progresses/ProgressEditor.php`
- `app/Livewire/Drafts/DraftIndex.php`
- `resources/views/components/progress/save-state.blade.php`

**Langkah kerja:**

1. Gunakan baseline jeda sekitar 1 detik dan blur/bagian sebagai pemicu bila ada perubahan. Hindari request berlebihan pada setiap ketikan.
2. Tampilkan Menyimpan draft, konfirmasi waktu server, dan Draft belum tersimpan. Draft list menunjukkan baru/perubahan, JO/Activity yang tersedia, dan Lanjutkan.
3. Saat koneksi gagal atau session habis, pertahankan UI, sediakan retry, dan peringatkan sebelum meninggalkan perubahan yang belum diakui.
4. Reload memulihkan draft server yang sudah dikonfirmasi. File yang belum selesai upload diberi pesan perlu dipilih ulang bila diperlukan.

**Selesai jika:** Pengguna mengetahui apakah isian sudah aman dan dapat melanjutkan draft dengan nested state utuh.

**Verifikasi:** Putus koneksi saat mengetik lalu sambungkan; uji status tidak sukses palsu dan retry tidak menduplikasi draft.

### P05-T03 Lindungi urutan autosave dan konflik dua tab

**Acuan:** FR14; AT17 AT18.

**Prasyarat:** P05-T01, P05-T02.

**File atau keluaran sasaran:**

- `app/Services/ProgressDraftService.php`
- `app/Livewire/Progresses/ProgressEditor.php`
- `tests/Feature/Drafts/DraftConcurrencyTest.php`

**Langkah kerja:**

1. Periksa revision dengan conditional update atau lock yang sesuai PostgreSQL. Tolak write dengan basis revisi lebih lama.
2. Kirim sequence/revision pada setiap autosave dan cocokkan response ke perubahan terkini. Respons lama tidak boleh mengganti status/isi baru.
3. Jika dua tab mengedit progress yang sama, tampilkan konflik yang mempertahankan draft; jangan menerapkan merge otomatis.
4. Pastikan Close/Reopen meningkatkan revision sehingga draft yang berbasis data lama tidak menimpa perubahan status.

**Selesai jika:** Isi terbaru tidak hilang karena jaringan lambat atau tab ganda.

**Verifikasi:** Buat request A/B dengan B lebih baru dan kirim A terakhir; ulangi dua tab dengan base_revision sama. Verifikasi AT17/AT18.

### P05-T04 Integrasikan Simpan final dan idempotensi draft

**Acuan:** FR13 FR14; AT14 AT15 AT16.

**Prasyarat:** P05-T03, P04-T08.

**File atau keluaran sasaran:**

- `app/Actions/ApplyProgressDraft.php`
- `app/Actions/SaveProgress.php`
- `tests/Feature/Drafts/ApplyDraftTest.php`

**Langkah kerja:**

1. Lock draft/target secara konsisten. Validasi final lalu panggil SaveProgress graph yang telah ada, tanpa membuat jalur save kedua dengan aturan berbeda.
2. Untuk draft baru, simpan applied_progress_id dalam transaksi; replay mengembalikan record sama. Untuk edit, periksa base_revision terbaru.
3. Tandai draft applied hanya setelah graph tersimpan. Saat Simpan berjalan, tahan autosave; request terlambat untuk draft applied ditolak aman.
4. Validation failure atau error child mempertahankan draft active. Dashboard/detail tetap menampilkan versi lama hingga transaksi final commit.

**Selesai jika:** Simpan final tepat satu kali secara logis dan tidak menghidupkan draft lagi karena request terlambat.

**Verifikasi:** Jalankan AT14/AT15/AT16 serta kirim autosave lama setelah apply sukses; jumlah progress/anak dan draft aktif tetap benar.

### P05-T05 Selesaikan draft file dan Buang Draft

**Acuan:** FR15 FR20; AT19.

**Prasyarat:** P05-T04.

**File atau keluaran sasaran:**

- `app/Services/ProgressDraftService.php`
- `app/Services/AttachmentService.php`
- `app/Console/Commands/CleanupStaging.php`
- `tests/Feature/Drafts/DiscardDraftTest.php`

**Langkah kerja:**

1. Buang Draft memerlukan konfirmasi UI lalu memeriksa owner/admin di server. Ini tidak sama dengan menghapus progress.
2. Hapus referensi draft aktif yang dibuang dan staging yang hanya dipakai draft itu. Pertahankan file attached serta staging yang direferensikan draft lain.
3. Cleanup otomatis belum dijadwalkan sebelum OP04 menetapkan retensi. Sediakan mode dry run yang menampilkan kandidat tanpa menghapus.
4. Pastikan file staging aktif tidak kedaluwarsa hanya karena draft belum lengkap; kebijakan retensi harus terlihat dan tercatat.

**Selesai jika:** Pembuangan draft dan cleanup tidak menghilangkan file operasional.

**Verifikasi:** Jalankan AT19 dengan file attached, staging draft A/B, dan orphan; dry run serta eksekusi uji hanya mempengaruhi kandidat sah.

### P05-T06 Implementasikan Close Reopen dan draft belum diterapkan

**Acuan:** FR16; AT20 AT21 AT22.

**Prasyarat:** P05-T04.

**File atau keluaran sasaran:**

- `app/Actions/CloseProgress.php`
- `app/Actions/ReopenProgress.php`
- `resources/views/components/progress/status-actions.blade.php`
- `tests/Feature/Progress/ProgressStatusTest.php`

**Langkah kerja:**

1. Close dan Reopen memeriksa auth serta revision; tidak memvalidasi kelengkapan action/feedback atau mengisi actual date.
2. Closed menolak edit langsung. Reopen mengubah status saja dan mempertahankan graph serta file.
3. Saat ada draft belum diterapkan, UI menjelaskan Close versi tersimpan dan memberi pilihan menyimpan dulu atau tetap menutup versi tersimpan. Draft tidak dihapus.
4. Request status berulang aman. Tidak mengubah project, progress lain, atau membuat audit trail produk.

**Selesai jika:** Admin memiliki kendali penuh atas Open/Closed dengan semantik draft yang jelas.

**Verifikasi:** Jalankan AT20/AT21/AT22 termasuk legacy field kosong, draft invalid, status request berulang, dan edit langsung ke Closed.

### P05-T07 Implementasikan tiga ringkasan dashboard

**Acuan:** FR17; AT23.

**Prasyarat:** P05-T06.

**File atau keluaran sasaran:**

- `app/Queries/DashboardSummary.php`
- `app/Livewire/Dashboard.php`
- `resources/views/livewire/dashboard.blade.php`
- `tests/Feature/Dashboard/SummaryTest.php`

**Langkah kerja:**

1. Hitung Open dari progress tersimpan; hitung draft aktif baru/edit; hitung aktivitas bulan kalender berjalan dari activity_date termasuk Open dan Closed.
2. Kecualikan draft dari hitungan progress dan legacy activity_date null dari hitungan bulan. Gunakan bulan Asia/Jakarta tanpa menggeser DATE.
3. Gunakan count record atau distinct progress, bukan count baris hasil join anak. Ringkasan bersifat global dan diberi label jelas.
4. Tampilkan tiga angka ringkas sesuai prototype; jangan menambah total fiktif, persentase proyek, atau overdue action.

**Selesai jika:** Angka dashboard cocok dengan query dasar dan tidak berlipat akibat anak.

**Verifikasi:** Uji tanggal awal/akhir bulan, progress Closed, draft applied, legacy null, dan satu progress dengan banyak tindakan untuk AT23.

### P05-T08 Implementasikan daftar kerja filter PIC dan pagination

**Acuan:** FR18; AT24 AT25.

**Prasyarat:** P05-T07.

**File atau keluaran sasaran:**

- `app/Queries/ProgressFilterQuery.php`
- `app/Livewire/Dashboard.php`
- `app/Livewire/Progresses/ProgressIndex.php`
- `tests/Feature/Dashboard/FilterTest.php`

**Langkah kerja:**

1. Default Open terlama dengan ID sebagai tie-breaker dan tanggal null terakhir. Sediakan terbaru serta Open/Closed/Semua.
2. Pencarian JO/Part No/Part Name dan filter customer/Activity/periode diterapkan pada query yang sama dan dibatasi pagination 25.
3. Filter PIC default PIC Progress; pilihan peran corrective/next/feedback memakai exists atas relasi terkait agar result tidak duplikat.
4. Simpan filter pada URL/state navigasi, tampilkan active filter dan Clear Filter. Arahkan setiap hasil ke detail yang benar.

**Selesai jika:** Daftar kerja dapat dipakai untuk menemukan tindak lanjut sesuai peran PIC.

**Verifikasi:** Jalankan AT24/AT25; cari PIC yang ada pada dua tindakan dalam progress yang sama dan pastikan hasil hanya satu.

### P05-T09 Uji alur browser kegagalan dan aksesibilitas

**Acuan:** AT12 sampai AT25 AT35 AT36 AT37.

**Prasyarat:** P05-T05 sampai P05-T08.

**File atau keluaran sasaran:**

- `tests/Browser/ProgressWorkflowTest.php atau padanan tool yang dipilih`
- `docs/review/operational-ux.md`

**Langkah kerja:**

1. Jalankan skenario form panjang, nested input, reload draft, final save, Close/Reopen, dan filter pada browser desktop.
2. Simulasikan koneksi lambat/putus, session habis, upload belum selesai, validation error bagian lain, serta konflik dua tab.
3. Uji HP dan keyboard: fokus tidak hilang, tombol tidak tumpang tindih, dan error mudah ditemukan.
4. Perbaiki hanya penyebab nyata; jangan mengganti seluruh desain atau memperluas framework saat menangani bug terlokalisasi.

**Selesai jika:** Interaksi inti tidak kehilangan data dan sesuai hasil prototype.

**Verifikasi:** Simpan bukti hasil untuk setiap skenario kritis; ulangi skenario terdampak setelah perbaikan.

### P05-T10 Review kesiapan aplikasi untuk migrasi

**Acuan:** D01 sampai D20; AT01 sampai AT28.

**Prasyarat:** P05-T09.

**File atau keluaran sasaran:**

- `docs/progress/P05.md`
- `docs/release-readiness.md`

**Langkah kerja:**

1. Periksa hasil gate fase 02 sampai 05 dan tandai skenario yang benar-benar lulus.
2. Pastikan adapter dummy tidak digunakan pada route operasional dan label simulasi tidak muncul pada data nyata.
3. Cocokkan seluruh scope yang dibuang: Status Project, Project User, report/export, audit trail, link antaraktivitas, dan master PIC.
4. Bekukan kontrak field/relasi untuk importer. Perubahan setelah ini harus memperbarui pemetaan serta tes migrasi.

**Selesai jika:** Aplikasi operasional dasar siap menerima dry run data Access pada lingkungan terpisah.

**Verifikasi:** Review AT01 sampai AT28 dan catat blocker yang tersisa; jangan mengimpor produksi selama blocker data/status masih ada.

## Gate akhir fase

AT12 sampai AT25 dan AT37 lolos, termasuk request out of order, dua tab, dan Close dengan draft belum valid.

## Format catatan handoff

```text
Fase dan task terakhir:
Status: selesai / sebagian / blocked
Commit atau versi kerja:
File yang berubah:
Keputusan dan acuan PRD:
Verifikasi yang benar-benar dijalankan:
Hasil dan bukti:
Masalah tersisa:
Input atau persetujuan yang masih dibutuhkan:
Task berikutnya yang aman dimulai:
```

## Prompt untuk satu task

```text
Baca PRD versi 2.0 pada bagian yang dirujuk dan file fase ini.
Kerjakan hanya task [ID_TASK] beserta sublangkahnya. Periksa prasyarat dan repository terlebih dahulu.
Pertahankan kontrak bisnis; gunakan struktur yang sudah ada. Jangan memperluas scope.
Jalankan verifikasi yang relevan, catat hasil sebenarnya, lalu isi handoff.
Jangan mencentang task jika masih ada kriteria selesai yang belum terpenuhi.
Jika ada perubahan kebutuhan, rangkum usulan dan minta konfirmasi sebelum mengubah PRD.
```
