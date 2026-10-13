# Fase 07 UAT kesiapan operasi dan serah terima

Acuan PRD versi 2.0 • 13 Oktober 2026

Memastikan kebutuhan, UI/UX, migrasi, serta pemulihan data benar-benar siap sebelum pemakaian operasional.

## Prasyarat dan bahan baca

Fase 06 selesai dengan rekonsiliasi; tidak ada blocker kehilangan/tertukarnya data dari fase sebelumnya.

Baca PRD_Progress_New_Model.md: Bagian 20 sampai 24; AT01 sampai AT40 dan seluruh OP.

## Keluaran fase

- Hasil acceptance test dan review UX yang dapat diperiksa.
- Konfigurasi operasional, restore teruji, deployment/cutover terkendali.
- Panduan admin dan pengembang serta catatan pilot.

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

- [ ] P07-T01 Tutup parameter operasional yang masih terbuka
- [ ] P07-T02 Jalankan penerimaan fungsi lintas fase
- [ ] P07-T03 Periksa akses dan perlindungan data operasional
- [ ] P07-T04 Review UX final dan kinerja pada data representatif
- [ ] P07-T05 Uji backup restore dan cleanup yang aman
- [ ] P07-T06 Deploy ke staging dan lakukan UAT pengguna
- [ ] P07-T07 Laksanakan cutover dan pilot terkendali
- [ ] P07-T08 Serah terima panduan dan status pekerjaan

### P07-T01 Tutup parameter operasional yang masih terbuka

**Acuan:** OP01 sampai OP05; NF09 NF10.

**Prasyarat:** P06-T09.

**File atau keluaran sasaran:**

- `docs/operations/configuration.md`
- `docs/decisions.md`
- `.env.example`

**Langkah kerja:**

1. Tetapkan lingkungan server, akses jaringan/HTTPS, cara provisioning admin, storage, serta batas upload dengan pemilik aplikasi/IT.
2. Tetapkan jadwal/retensi backup, target pemulihan, cleanup staging/draft, dan pemilik pekerjaan operasional.
3. Kumpulkan keputusan dalam satu review; jangan menganggap web otomatis dapat diakses di luar LAN. Tidak mengunci layanan berbayar tanpa keputusan pemilik.
4. Perbarui konfigurasi dan runbook. Bila keputusan mengubah kebutuhan produk, ajukan revisi PRD untuk konfirmasi terlebih dahulu.

**Selesai jika:** Tidak ada parameter wajib produksi yang ditutup dengan asumsi tersembunyi.

**Verifikasi:** Checklist OP01 sampai OP05 mempunyai nilai, pemilik, dan tanggal keputusan; secret hanya berada pada konfigurasi terlindungi.

### P07-T02 Jalankan penerimaan fungsi lintas fase

**Acuan:** AT01 sampai AT34 AT37 AT39.

**Prasyarat:** P07-T01.

**File atau keluaran sasaran:**

- `docs/uat/acceptance-results.md`
- `tests/Feature/`
- `tests/Browser/`

**Langkah kerja:**

1. Gunakan dataset uji tetap dan versi aplikasi yang akan dirilis. Jalankan tes kritis master, graph, draft, status, file, serta migrasi.
2. Isi hasil per ID acceptance: lulus/gagal/belum diuji, bukti, serta bug terkait. Jangan mengisi lulus untuk skenario yang belum dijalankan.
3. Perbaiki blocker dan ulangi skenario terdampak beserta dependensi yang relevan. Hindari tes massal tambahan tanpa risiko yang belum terjawab.
4. Pastikan tidak ada menu/fungsi di luar scope yang terbawa dari starter kit atau prototype.

**Selesai jika:** Kriteria produk penting terpenuhi dan hasilnya dapat diaudit sebagai bukti pengujian, tanpa menambah fitur audit trail pengguna.

**Verifikasi:** Tinjau semua acceptance ID; kegagalan data, duplikasi, atau Close otomatis harus diselesaikan sebelum rilis.

### P07-T03 Periksa akses dan perlindungan data operasional

**Acuan:** NF01 NF02 NF05 NF10; AT27.

**Prasyarat:** P07-T02.

**File atau keluaran sasaran:**

- `docs/uat/access-checks.md`
- `tests/Feature/Auth/`
- `tests/Feature/Attachments/`
- `config/session.php`

**Langkah kerja:**

1. Uji halaman/file/draft/action langsung tanpa login, session habis, serta ID milik parent berbeda.
2. Periksa validasi server, escaping uraian/PIC, penolakan file invalid, serta path upload/download yang tidak dapat diarahkan dari input pengguna.
3. Matikan debug publik, registrasi, seed credential, serta route prototype pada konfigurasi produksi.
4. Pastikan log error tidak mencatat password atau payload seluruh aktivitas/lampiran. Hindari menambah checklist compliance yang tidak relevan.

**Selesai jika:** Data hanya dapat diakses admin dan jalur file/state tidak melewati pemeriksaan server.

**Verifikasi:** Jalankan tes akses negatif dan periksa konfigurasi environment staging yang menyerupai produksi.

### P07-T04 Review UX final dan kinerja pada data representatif

**Acuan:** UI01 sampai UI12; AT35 AT36 AT40.

**Prasyarat:** P07-T02.

**File atau keluaran sasaran:**

- `docs/uat/ux-results.md`
- `docs/uat/performance-results.md`

**Langkah kerja:**

1. Minta pengguna mengerjakan skenario prototype pada aplikasi nyata dengan dataset staging; bandingkan tempat ragu dan kehilangan konteks.
2. Uji desktop/HP, keyboard, teks panjang, banyak problem, autosave lambat, serta status/error yang tidak bergantung warna.
3. Ukur daftar/filter dengan sekitar 250 progress atau jumlah sumber sebenarnya. Catat server, browser, jaringan, jumlah sampel, serta persentil 95.
4. Perbaiki query berulang, pemuatan foto penuh, atau layout yang menghambat. Jangan mengubah angka target menjadi klaim sudah lulus tanpa pengukuran.

**Selesai jika:** UI terasa konsisten dengan prototype yang diterima dan target performa diuji pada kondisi tercatat.

**Verifikasi:** Bukti AT35/AT36/AT40 tersedia; ukur ulang hanya bagian yang diperbaiki dan catat hasilnya.

### P07-T05 Uji backup restore dan cleanup yang aman

**Acuan:** NF09; AT19 AT38.

**Prasyarat:** P07-T01, P07-T03.

**File atau keluaran sasaran:**

- `docs/operations/backup-restore.md`
- `docs/operations/cleanup.md`
- `Script backup sesuai lingkungan`

**Langkah kerja:**

1. Buat backup database serta byte storage pada waktu yang konsisten, dengan metadata konfigurasi yang dibutuhkan untuk pemulihan.
2. Restore ke lingkungan terpisah; login, buka project/progress, graph anak, dan foto sampel. Catat durasi aktual serta kekurangan.
3. Uji cleanup dry run kemudian eksekusi pada data uji sesuai OP04; draft aktif dan file attached tidak boleh terhapus.
4. Tulis runbook langkah demi langkah dan penanggung jawab; backup yang belum diuji restore tidak dianggap memenuhi gate.

**Selesai jika:** Pemulihan data dan foto terbukti, serta cleanup tidak menghapus data aktif.

**Verifikasi:** Jalankan AT38 dan periksa checksum file sampel; simpan bukti waktu serta hasil restore.

### P07-T06 Deploy ke staging dan lakukan UAT pengguna

**Acuan:** Bagian 20 dan 23; seluruh AT relevan.

**Prasyarat:** P07-T03 sampai P07-T05.

**File atau keluaran sasaran:**

- `docs/operations/deploy.md`
- `docs/uat/user-acceptance.md`
- `docs/release-readiness.md`

**Langkah kerja:**

1. Tetapkan target staging yang sah dan gunakan prosedur deployment sesuai lingkungan; tidak mempublikasikan data perusahaan ke demo publik.
2. Jalankan migration dan build memakai artifact/version yang terkunci. Jangan memakai migration reset pada database berisi data yang perlu dipertahankan.
3. Minta pengguna menguji input, follow-up, draft, Close/Reopen, pencarian, serta hasil migrasi. Catat versi dan temuan.
4. Selesaikan blocker dan minta penerimaan terhadap hasil UAT. Siapkan rencana produksi yang konkret untuk persetujuan pemilik lingkungan.

**Selesai jika:** Aplikasi staging diterima pengguna dan rencana rilis dapat diperiksa sebelum produksi.

**Verifikasi:** Checklist readiness menyebut bukti hasil, persetujuan pengguna, backup/restore, serta target deployment yang benar.

### P07-T07 Laksanakan cutover dan pilot terkendali

**Acuan:** FR25; NF09.

**Prasyarat:** P07-T06 serta persetujuan target produksi/cutover.

**File atau keluaran sasaran:**

- `docs/migration/cutover-result.md`
- `docs/operations/pilot-log.md`
- `docs/operations/rollback-result.md bila diperlukan`

**Langkah kerja:**

1. Sesudah persetujuan pemilik lingkungan, lakukan penghentian input Access, backup, ekspor terakhir, dan impor sesuai rencana yang telah diuji.
2. Rekonsiliasi jumlah/exception/foto sebelum mengalihkan pemakaian. Jika indikator gagal terpenuhi, jalankan pemulihan yang disepakati; jangan menutup kegagalan dengan data buatan.
3. Lakukan pilot dengan admin selama periode yang disepakati. Catat masalah nyata dan perbaikan, termasuk draft, upload, dan pencarian.
4. Jangan menghapus arsip Access atau mendeklarasikan pilot selesai sebelum periode serta kriteria penerimaannya terpenuhi.

**Selesai jika:** Penggunaan berpindah dengan data yang dapat dipertanggungjawabkan dan pilot diterima pemilik aplikasi.

**Verifikasi:** Cocokkan rekonsiliasi final, sample foto, catatan input baru, serta keputusan lanjut/rollback dengan bukti sebenarnya.

### P07-T08 Serah terima panduan dan status pekerjaan

**Acuan:** D20; bagian 24.

**Prasyarat:** P07-T07.

**File atau keluaran sasaran:**

- `docs/handbook/admin.md`
- `docs/handbook/developer.md`
- `docs/progress/P07.md`
- `docs/known-issues.md`

**Langkah kerja:**

1. Panduan admin menjelaskan master, form seragam, problem/tindakan, draft versus Simpan, Close/Reopen, pencarian, foto, serta pesan error penting.
2. Panduan pengembang menjelaskan setup, versi, schema/relasi, service save/draft, importer, test, deployment, dan restore.
3. Catat isu tersisa yang diterima, pemilik, serta cara melaporkan masalah. Jangan menyebut fitur masa depan sebagai fitur tersedia.
4. Pastikan semua task tercentang hanya bila memiliki bukti dan serahkan versi kode serta dokumen yang konsisten.

**Selesai jika:** Admin dan pengembang berikutnya dapat mengoperasikan serta merawat aplikasi tanpa bergantung pada percakapan AI sebelumnya.

**Verifikasi:** Minta pembaca mengikuti satu prosedur input dan satu prosedur restore/setup dari panduan; koreksi bagian yang tidak dapat diikuti.

## Gate akhir fase

Pengguna menerima UAT, semua blocker data/akses selesai, backup dapat dipulihkan, cutover direkonsiliasi, dan serah terima dicatat.

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
