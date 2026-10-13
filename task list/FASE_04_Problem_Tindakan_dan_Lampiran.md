# Fase 04 Problem tindakan feedback dan lampiran

Acuan PRD versi 2.0 • 13 Oktober 2026

Mengimplementasikan graph data berulang beserta penyimpanan lampiran privat yang tidak tertukar antarproblem atau feedback.

## Prasyarat dan bahan baca

Fase 03 selesai. OP03 harus ditetapkan sebelum upload dokumen operasional diaktifkan; sampai itu jelas gunakan file uji pada environment testing.

Baca PRD_Progress_New_Model.md: Bagian 9, 12, 14, 16, 17; FR07 sampai FR11 dan FR19 sampai FR21.

## Keluaran fase

- Editor problem dengan beberapa corrective action per problem.
- Next Action dan feedback independen.
- Foto/dokumen baru privat serta tampilan metadata dokumen lama.

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

- [ ] P04-T01 Implementasikan editor problem dan identitas baris stabil
- [ ] P04-T02 Implementasikan beberapa corrective action per problem
- [ ] P04-T03 Implementasikan Next Action independen
- [ ] P04-T04 Implementasikan riwayat feedback customer
- [ ] P04-T05 Tetapkan konfigurasi upload dan staging privat
- [ ] P04-T06 Implementasikan galeri foto per problem
- [ ] P04-T07 Implementasikan dokumen baru dan arsip metadata lama
- [ ] P04-T08 Finalisasi Simpan graph dalam satu transaksi
- [ ] P04-T09 Review relasi dan UX daftar panjang

### P04-T01 Implementasikan editor problem dan identitas baris stabil

**Acuan:** FR07 FR11; AT07.

**Prasyarat:** P03-T08.

**File atau keluaran sasaran:**

- `app/Livewire/Progresses/ProgressEditor.php`
- `resources/views/components/progress/problems.blade.php`
- `app/Data/ProgressPayload.php`

**Langkah kerja:**

1. Gunakan problems[] dengan client_id UUID yang tetap dari draft hingga final, serta stored id bila item sudah ada.
2. Tambah problem mempertahankan state item lain, mengarahkan fokus ke uraian baru, dan menampilkan nomor urut hanya sebagai label.
3. Baris baru yang seluruhnya kosong diabaikan pada Simpan. Item legacy yang berisi foto/tindakan tetapi uraian kosong tetap dipertahankan.
4. Integrasikan bagian problem pada semua Activity tanpa conditional pembatas Trial. Koreksi uraian item tersimpan tidak mengganti identitasnya.

**Selesai jika:** Beberapa problem dapat ditambah/diedit tanpa kehilangan nilai atau identitas anak.

**Verifikasi:** Tambah tiga problem, isi problem kedua, tambah item baru, dan cek problem kedua tetap sama setelah request Livewire dan Simpan.

### P04-T02 Implementasikan beberapa corrective action per problem

**Acuan:** FR08 FR11; AT07 AT10 AT11.

**Prasyarat:** P04-T01.

**File atau keluaran sasaran:**

- `resources/views/components/progress/corrective-actions.blade.php`
- `app/Actions/SaveProgress.php`
- `tests/Feature/Progress/CorrectiveActionTest.php`

**Langkah kerja:**

1. Setiap problem memiliki corrective_actions[] dengan ID stabil, uraian, PIC bebas, actual_date nullable, dan urutan.
2. Tambahkan kontrol Tambah Corrective Action di dalam problem yang benar. Simpan tidak menggabungkan tindakan menjadi satu textarea.
3. Pertahankan koma/titik di field raw lain dan actual_date null; tidak menambahkan target date atau status selesai otomatis.
4. Validasi ID tindakan milik problem dan progress yang sedang diedit sebelum update. PIC/tanggal tanpa uraian tetap dianggap data.

**Selesai jika:** Dua problem dengan masing-masing dua tindakan tersimpan dan kembali pada induk yang benar.

**Verifikasi:** Jalankan AT07, AT10, AT11; kirim ID tindakan milik problem lain dan pastikan ditolak tanpa mengubah sumber.

### P04-T03 Implementasikan Next Action independen

**Acuan:** FR09 FR11; AT08 AT10.

**Prasyarat:** P04-T01.

**File atau keluaran sasaran:**

- `resources/views/components/progress/next-actions.blade.php`
- `app/Actions/SaveProgress.php`
- `tests/Feature/Progress/NextActionTest.php`

**Langkah kerja:**

1. Tambahkan next_actions[] di tingkat progress dengan uraian, PIC, actual_date dan ID stabil.
2. Pastikan pengguna bisa menambah Next Action pada form tanpa problem. Tidak menambahkan problem_id atau link ke progress pelaksanaan.
3. Terapkan pola fokus/validasi konsisten dengan corrective action, tetapi jangan menggunakan state array yang sama.
4. Pertahankan baris yang hanya mempunyai PIC atau tanggal; baris seluruhnya kosong belum tersimpan boleh diabaikan.

**Selesai jika:** Next Action dapat berdiri sendiri dan tidak tertukar dengan corrective action.

**Verifikasi:** Simpan tiga Next Action pada progress tanpa problem; edit satu PIC dan cek dua item lainnya tetap.

### P04-T04 Implementasikan riwayat feedback customer

**Acuan:** FR10 FR11; AT09.

**Prasyarat:** P04-T01.

**File atau keluaran sasaran:**

- `resources/views/components/progress/feedback.blade.php`
- `app/Actions/SaveProgress.php`
- `tests/Feature/Progress/FeedbackTest.php`

**Langkah kerja:**

1. Gunakan feedbacks[] dengan tanggal nullable, isi, PIC bebas, ID stabil, dan daftar document_refs untuk integrasi upload.
2. Tampilkan tanggal feedback berbeda dari tanggal aktivitas. PIC Feedback tidak mengambil alih PIC Customer pada progress.
3. Menambahkan entri tidak menimpa feedback terdahulu; koreksi dilakukan pada ID yang dipilih.
4. Pertahankan urutan dan state ketika pindah tab atau Activity; tidak menjadikan feedback syarat Close.

**Selesai jika:** Riwayat feedback berulang dapat ditambah dan dikoreksi dengan konteks yang jelas.

**Verifikasi:** Jalankan AT09 memakai dua tanggal/PIC/dokumen berbeda; periksa setelah reload dan pergantian Activity.

### P04-T05 Tetapkan konfigurasi upload dan staging privat

**Acuan:** FR19 FR20; NF05; OP03.

**Prasyarat:** P02-T04, P02-T05, P04-T04.

**File atau keluaran sasaran:**

- `config/uploads.php`
- `config/filesystems.php`
- `app/Services/AttachmentService.php`
- `app/Policies/AttachmentPolicy.php`

**Langkah kerja:**

1. Tetapkan foto JPG/JPEG, PNG, WebP. Minta keputusan OP03 secara terkumpul untuk allowlist dokumen baru dan batas ukuran; catat di konfigurasi, bukan hardcoded di berbagai component.
2. Validasi MIME/isi, ukuran, dan tipe pada server. Simpan byte dengan storage key acak di disk privat; nama asli hanya metadata.
3. Buat staging milik admin/draft yang sah. Unggahan gagal atau belum selesai tidak boleh berstatus attached.
4. Rancang finalisasi file yang aman: pertahankan sumber saat gagal, cegah referensi lintas draft, dan jangan membuat URL publik permanen.

**Selesai jika:** Upload uji menghasilkan staging privat yang dapat diverifikasi kepemilikannya, dengan konfigurasi format/ukuran jelas.

**Verifikasi:** Coba file dengan ekstensi gambar tetapi isi bukan gambar, file terlalu besar, serta staging draft lain; semuanya ditolak secara tepat.

### P04-T06 Implementasikan galeri foto per problem

**Acuan:** FR07 FR19 FR20; AT26 AT27.

**Prasyarat:** P04-T01, P04-T05.

**File atau keluaran sasaran:**

- `resources/views/components/progress/gallery.blade.php`
- `app/Services/ThumbnailService.php`
- `app/Http/Controllers/AttachmentController.php`

**Langkah kerja:**

1. Hubungkan upload multi-foto ke problem yang sedang dipilih melalui ID/client_id stabil.
2. Tampilkan progres upload, thumbnail, caption, preview besar, serta error per file. Buat thumbnail tanpa mengganti byte sumber secara diam-diam.
3. Layani preview/unduh melalui auth dan policy; jangan mempercayai path dari request. Kontrol galeri memakai label serta keyboard.
4. Pertahankan file tersimpan ketika caption dikoreksi. Staging gagal tidak menghilangkan referensi foto yang telah attached.

**Selesai jika:** Foto tetap berada pada problem yang benar dan hanya admin terautentikasi dapat mengaksesnya.

**Verifikasi:** Jalankan AT26/AT27 dengan dua problem, beberapa file, caption berbeda, dan permintaan unduh tanpa sesi.

### P04-T07 Implementasikan dokumen baru dan arsip metadata lama

**Acuan:** FR19 FR21; AT28.

**Prasyarat:** P04-T04, P04-T05.

**File atau keluaran sasaran:**

- `resources/views/components/progress/documents.blade.php`
- `resources/views/components/progress/legacy-documents.blade.php`
- `app/Models/LegacyDocumentMetadata.php`

**Langkah kerja:**

1. Sediakan daftar dokumen baru pada progress dan pada setiap feedback, dengan nama, keterangan, serta status unggah.
2. Gunakan konfigurasi OP03 yang sudah ditetapkan. Tidak memakai batas lima slot kolom Doc1 sampai Doc5.
3. Tampilkan legacy metadata sebagai nama/keterangan dan File lama tidak dimigrasikan. Jangan memberi download aktif atau membuka path jaringan lama.
4. Dokumen feedback harus terkait ke feedback_id yang benar; metadata hanya nama/keterangan tidak membuat file palsu.

**Selesai jika:** Dokumen baru dapat diunggah dan metadata lama dapat dibaca tanpa menyamakan keduanya.

**Verifikasi:** Jalankan AT28 dengan enam file uji dan dua entri metadata-only; periksa tidak ada tombol unduh untuk metadata.

### P04-T08 Finalisasi Simpan graph dalam satu transaksi

**Acuan:** FR11 FR13 FR20; AT11 AT16.

**Prasyarat:** P04-T02 sampai P04-T07.

**File atau keluaran sasaran:**

- `app/Actions/SaveProgress.php`
- `app/Services/AttachmentService.php`
- `tests/Feature/Progress/SaveGraphTest.php`

**Langkah kerja:**

1. Validasi payload dan semua kepemilikan sebelum transaksi. Simpan progress, problem, corrective action, Next Action, dan feedback dengan pemetaan client_id konsisten.
2. Finalisasi referensi staging yang sah saja; field absent tidak berarti hapus anak tersimpan. Upsert hanya ID dalam progress ini.
3. Jika salah satu penyimpanan anak gagal, rollback data terstruktur dan pertahankan draft/staging agar dapat retry.
4. Untuk file yang sudah terlanjur dipindahkan, simpan jejak teknis agar dapat direkonsiliasi; jangan menghapus file yang direferensikan record final.

**Selesai jika:** Tidak ada graph setengah tersimpan atau lampiran yang tertukar ketika terjadi kegagalan.

**Verifikasi:** Suntikkan kegagalan pada corrective action dan finalisasi file di lingkungan test; periksa atomisitas serta kemampuan retry.

### P04-T09 Review relasi dan UX daftar panjang

**Acuan:** AT07 sampai AT11 AT26 sampai AT28 AT35 AT36.

**Prasyarat:** P04-T08.

**File atau keluaran sasaran:**

- `docs/progress/P04.md`
- `tests/Feature/Progress/`
- `tests/Feature/Attachments/`

**Langkah kerja:**

1. Jalankan kasus positif/negatif relasi dan upload pada database/storage uji.
2. Uji form berisi lima problem dengan beberapa tindakan serta teks panjang di desktop/HP; navigasi bagian dan fokus tetap dapat dipakai.
3. Periksa tidak ada target date, master PIC, atau kewajiban mengisi seluruh action yang muncul akibat library form.
4. Catat batas ukuran/format yang disepakati OP03, hasil test, dan handoff ke autosave P05.

**Selesai jika:** Graph dan file siap dipakai oleh penyimpanan draft/final yang andal.

**Verifikasi:** Review file/policy serta jalankan tes terdampak; bukti menyebut hasil aktual dan keterbatasan yang masih ada.

## Gate akhir fase

AT07 sampai AT11 dan AT26 sampai AT28 lolos; satu transaksi Simpan menjaga relasi anak, dan akses file tidak melewati otorisasi.

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
