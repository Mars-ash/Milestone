# Fase 02 Fondasi data dan akses

Acuan PRD versi 2.0 • 13 Oktober 2026

Mengganti fondasi simulasi dengan struktur aplikasi operasional, schema PostgreSQL, autentikasi, dan kontrak service tanpa mengubah UI yang diterima.

## Prasyarat dan bahan baca

P01-T12 selesai dengan penerimaan prototype; konfigurasi database development tersedia.

Baca PRD_Progress_New_Model.md: Bagian 2, 3, 13 sampai 17, dan 20.

## Keluaran fase

- Aplikasi development terhubung PostgreSQL dengan migration berurutan.
- Model, relasi, DTO, policy, auth, storage privat, serta fixture test.
- Catatan versi dan keputusan teknis yang dapat diikuti AI berikutnya.

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

- [x] P02-T01 Tetapkan struktur repository dan konfigurasi environment
- [x] P02-T02 Buat schema projects dan progresses
- [x] P02-T03 Buat schema relasi problem tindakan dan feedback
- [x] P02-T04 Buat schema draft attachment dan metadata legacy
- [x] P02-T05 Bangun autentikasi satu admin dan otorisasi
- [x] P02-T06 Definisikan payload dan validasi bertingkat
- [x] P02-T07 Siapkan kontrak service dan transaksi
- [x] P02-T08 Siapkan fixture pengujian PostgreSQL dan quality gate
- [x] P02-T09 Review fondasi dan handoff implementasi

### P02-T01 Tetapkan struktur repository dan konfigurasi environment

**Acuan:** D20; NF10.

**Prasyarat:** P01-T12.

**File atau keluaran sasaran:**

- `README.md pada repository aplikasi`
- `.env.example`
- `composer.lock`
- `package-lock.json atau lockfile yang dipilih`
- `docs/architecture.md`

**Langkah kerja:**

1. Pertahankan hasil UI prototype dan pisahkan adapter dummy. Tentukan branch/commit dasar yang diterima; jangan menimpa pekerjaan lain.
2. Catat PHP, Laravel, Livewire, Tailwind, PostgreSQL, serta toolchain build yang kompatibel. Gunakan satu package manager Node.
3. Sediakan environment development/testing yang berbeda. .env.example berisi nama variabel dan nilai dummy; kredensial nyata tidak masuk git.
4. Dokumentasikan struktur app/Livewire, app/Actions atau app/Services, app/Data, app/Models, resources/views, tests, dan tools/access-export.

**Selesai jika:** Pengembang baru dapat menjalankan app dengan instruksi jelas dan tanpa memakai database produksi.

**Verifikasi:** Lakukan setup dari checkout bersih pada environment uji, build asset, dan cek bahwa repository tidak mengandung secret.

### P02-T02 Buat schema projects dan progresses

**Acuan:** FR01 sampai FR06; bagian 15 sampai 17.

**Prasyarat:** P02-T01.

**File atau keluaran sasaran:**

- `database/migrations/*create_projects_table.php`
- `database/migrations/*create_progresses_table.php`
- `app/Models/Project.php`
- `app/Models/Progress.php`

**Langkah kerja:**

1. Petakan semua field master/progress dasar dari PRD, dengan JO/Part/No Doc sebagai teks, raw numeric sebagai teks, dan tanggal bisnis DATE.
2. Tambahkan unique JO dan unique Part No serta foreign key progress ke project. Jangan membuat Project User atau Status Project.
3. Sediakan status Open/Closed, revision teknis, timestamps, dan penanda legacy incomplete. Atur nullable hanya sesuai kebutuhan sumber lama; input baru tetap divalidasi wajib.
4. Tambahkan index untuk project_id, activity_date, status, dan filter utama yang diperlukan, tanpa mengindeks seluruh teks panjang.

**Selesai jika:** Schema mendukung identitas satu-ke-satu, data legacy kurang lengkap, dan field yang dihapus tidak hidup kembali.

**Verifikasi:** Jalankan migration pada PostgreSQL uji; coba JO duplikat dan Part No duplikat serta nilai 0007 dan tanggal nullable. Keduanya ditolak/tersimpan sesuai aturan.

### P02-T03 Buat schema relasi problem tindakan dan feedback

**Acuan:** FR07 sampai FR11.

**Prasyarat:** P02-T02.

**File atau keluaran sasaran:**

- `database/migrations/*create_progress_children_tables.php`
- `app/Models/Problem.php`
- `app/Models/CorrectiveAction.php`
- `app/Models/NextAction.php`
- `app/Models/Feedback.php`

**Langkah kerja:**

1. Buat problems milik progresses dan corrective_actions milik problems dengan ID, urutan, uraian, PIC, actual_date sesuai entitas.
2. Buat next_actions milik progresses tanpa problem_id. Buat feedbacks milik progresses dengan tanggal, isi, PIC.
3. Gunakan relasi hasMany/belongsTo eksplisit dan constraint FK. Jangan menambah target date atau status selesai otomatis.
4. Tetapkan kebijakan integritas ketika parent tidak ditemukan; tidak menambahkan fitur hapus berantai melalui UI.

**Selesai jika:** Satu progress dapat menyimpan dua problem dengan banyak corrective action, next action tanpa problem, dan feedback berulang.

**Verifikasi:** Seed graph kecil dan query kembali setiap jalur; foreign key salah ditolak serta ID anak tidak tertukar.

### P02-T04 Buat schema draft attachment dan metadata legacy

**Acuan:** FR12 sampai FR15 FR20 FR21.

**Prasyarat:** P02-T02, P02-T03.

**File atau keluaran sasaran:**

- `database/migrations/*create_progress_drafts_table.php`
- `database/migrations/*create_attachments_table.php`
- `database/migrations/*create_legacy_document_metadata_table.php`
- `app/Models/ProgressDraft.php`
- `app/Models/Attachment.php`

**Langkah kerja:**

1. Draft mempunyai UUID, admin_id, progress_id nullable, base_revision, draft_revision, schema_version payload, active/applied, dan applied_progress_id. Batasi satu draft perubahan aktif per progress per admin.
2. Attachment menyimpan satu pemilik sah, storage_key privat, metadata, checksum, caption, staging/attached. Pilih pola owner yang konsisten dan tulis validasinya.
3. Pisahkan legacy_document_metadata dari file attached agar nama dokumen lama tidak dianggap file tersedia.
4. Catat constraint draft dan kepemilikan yang perlu ditegakkan service bila tidak dapat dijamin satu FK; jangan menganggap polymorphic owner otomatis aman.

**Selesai jika:** Draft parsial, staging, idempotensi finalisasi, dan arsip metadata bisa diwakili tanpa mencampur data aktif.

**Verifikasi:** Buat dua draft edit aktif untuk target sama dan pastikan aturan penolakannya; buat metadata tanpa file dan pastikan tidak ada download key.

### P02-T05 Bangun autentikasi satu admin dan otorisasi

**Acuan:** NF01 NF02; OP02.

**Prasyarat:** P02-T01, P02-T04.

**File atau keluaran sasaran:**

- `routes/web.php`
- `app/Policies/`
- `config/auth.php`
- `app/Console/Commands/CreateAdmin.php`
- `tests/Feature/Auth/`

**Langkah kerja:**

1. Gunakan session auth Laravel yang sesuai versi terpasang. Sediakan login/logout; matikan registrasi publik serta pemberian akun otomatis kepada PIC.
2. Buat cara pembuatan admin melalui perintah lokal yang membaca password secara aman, bukan password hardcoded di seeder atau README.
3. Terapkan middleware dan policy pada halaman data, draft, upload, unduh, Close/Reopen, dan Livewire action. Validasi tetap di server.
4. Tentukan alur pemulihan akun bersama pemilik aplikasi sebelum go live; prototype tidak menjadi alasan bypass auth produksi.

**Selesai jika:** Satu admin dapat login, pengguna anonim tidak dapat membaca data/file atau melakukan perubahan.

**Verifikasi:** Uji akses langsung URL dan action tanpa sesi, login gagal, logout, serta percobaan ID milik record lain pada policy yang relevan.

### P02-T06 Definisikan payload dan validasi bertingkat

**Acuan:** FR04 FR05 FR11; bagian 17.

**Prasyarat:** P02-T03, P02-T04.

**File atau keluaran sasaran:**

- `app/Data/ProgressPayload.php`
- `app/Livewire/Forms/ProgressFormData.php`
- `app/Rules/`
- `tests/Unit/ProgressPayloadTest.php`

**Langkah kerja:**

1. Tulis kontrak field dari PRD termasuk client_id stabil untuk baris baru dan stored id untuk baris tersimpan. DTO tidak memakai snapshot master sebagai sumber kebenaran.
2. Pisahkan validasi draft parsial dari validasi Simpan final. Aturan wajib final hanya tiga field progress ditambah pemeriksaan tipe/relasi yang sah.
3. Pertahankan raw text koma/titik; tanggal kosong menjadi null. Jangan melakukan cast float atau mengisi tanggal default untuk action.
4. Tolak payload dengan ID parent/child tidak sesuai dan tangani field tak dikenal secara eksplisit. Daftar yang hilang bukan instruksi delete.

**Selesai jika:** Kontrak data jelas dan satu bentuk payload dapat dipakai form, service, serta importer dengan validasi yang sesuai jalurnya.

**Verifikasi:** Uji payload nested lengkap, draft parsial, ID silang, raw 1,5/1.5, dan field Activity di luar lima nilai.

### P02-T07 Siapkan kontrak service dan transaksi

**Acuan:** FR11 FR13 FR14 FR16.

**Prasyarat:** P02-T06.

**File atau keluaran sasaran:**

- `app/Actions/SaveProgress.php`
- `app/Actions/CloseProgress.php`
- `app/Actions/ReopenProgress.php`
- `app/Services/ProgressDraftService.php`
- `docs/architecture.md`

**Langkah kerja:**

1. Tentukan tanggung jawab input/output untuk save draft, apply draft, Close, Reopen, serta daftar monitoring. Component tidak boleh menjadi lokasi seluruh logika bisnis.
2. Definisikan kontrak hasil: sukses, validation error, version conflict, upload belum siap, auth error. Setiap hasil memiliki pesan yang dapat dipetakan ke UI.
3. Rancang transaksi Simpan graph, kontrol revision, dan applied_progress_id untuk idempotensi. File staging memiliki proses finalisasi terpisah yang aman.
4. Buat skeleton service yang belum selesai secara jelas; jangan mengembalikan sukses palsu atau menandai fitur implementasi selesai pada fase fondasi.

**Selesai jika:** Fase berikutnya dapat mengisi service tanpa menebak alur, error, dan batas transaksi.

**Verifikasi:** Review signature dan kontrak terhadap AT11 sampai AT22. Catat bagian skeleton yang masih harus diimplementasikan.

### P02-T08 Siapkan fixture pengujian PostgreSQL dan quality gate

**Acuan:** AT01 sampai AT40.

**Prasyarat:** P02-T02 sampai P02-T07.

**File atau keluaran sasaran:**

- `database/factories/`
- `database/seeders/DevelopmentSeeder.php`
- `phpunit.xml`
- `tests/Fixtures/`
- `docs/testing.md`

**Langkah kerja:**

1. Sediakan fixture fiktif dari prototype ditambah kasus legacy null, ID silang, angka raw, dan dokumen metadata-only.
2. Pisahkan database testing dari development/produksi. Test constraint penting dijalankan pada PostgreSQL, bukan hanya SQLite.
3. Tulis petunjuk artisan test, build asset, dan tes browser terfokus yang akan digunakan. Pilih satu tool browser yang kompatibel dan catat versinya.
4. Jangan membuat banyak tes yang hanya memeriksa nama kelas; fokus aturan yang dapat merusak data dan skenario pengguna.

**Selesai jika:** Test environment bisa direproduksi dan fixture mencakup kasus penting tanpa data perusahaan.

**Verifikasi:** Jalankan tes constraint/DTO/auth yang sudah ada dan build. Simpan hasil nyata serta command pada catatan fase.

### P02-T09 Review fondasi dan handoff implementasi

**Acuan:** D01 sampai D20; NF01 sampai NF05.

**Prasyarat:** P02-T08.

**File atau keluaran sasaran:**

- `docs/progress/P02.md`
- `docs/architecture.md`
- `docs/decisions.md`

**Langkah kerja:**

1. Bandingkan schema dengan semua 65 kolom sumber dan daftar field yang sengaja dihapus. Pastikan corrective action berada di bawah problem.
2. Periksa tidak ada dependency React/Vue, layanan AI runtime, audit package produk, atau master PIC yang ditambahkan tanpa kebutuhan.
3. Catat file, service skeleton, migration, test yang lulus, serta keputusan OP yang belum diperlukan. Jangan menutup parameter operasional dengan asumsi tak tercatat.
4. Serahkan commit dan catatan kesinambungan ke P03; jangan menjalankan importer atau menyentuh produksi pada fase ini.

**Selesai jika:** Fondasi konsisten dengan PRD dan siap untuk penerapan master/progress.

**Verifikasi:** Review dependency dan schema serta ulangi tes yang terdampak koreksi fondasi; hentikan pemeriksaan setelah gate terpenuhi.

## Status pengerjaan 19 Oktober 2026

Implementasi T01–T09 selesai dan terverifikasi penuh. Database PostgreSQL (`progress_new_model_test` dan `progress_new_model_dev`) termigrasi; 116 PHP test (708 assertions) lulus; seluruh 8 Playwright browser test lulus di Google Chrome lokal (1.2m); Vite build, view:cache, platform-reqs, dan strict composer validation lulus. Auth redirect ke `/app` diselaraskan. Gate akhir fase terpenuhi penuh dan siap handoff ke Fase 03.

## Gate akhir fase

Migration berhasil pada database uji bersih, constraint penting terbukti, auth menutup data/file dari pengguna anonim, dan dummy tidak masuk jalur operasional.

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
