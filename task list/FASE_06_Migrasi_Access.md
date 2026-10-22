# Fase 06 Migrasi histori dan foto Access

Acuan PRD versi 2.0 • 13 Oktober 2026

Menyiapkan migrasi seluruh histori dengan foto dari Access, metadata dokumen lama, dan rekonsiliasi yang dapat diulang tanpa duplikasi.

## Prasyarat dan bahan baca

Fase 05 selesai; salinan database Access dan izin menggunakannya tersedia; lingkungan staging/uji terpisah dari produksi.

Baca PRD_Progress_New_Model.md: Bagian 15 sampai 19; FR21 sampai FR25.

## Keluaran fase

- Inventaris sumber dan kontrak ekspor beserta foto yang dapat dibuka.
- Importer idempoten, daftar exception, dan hasil dry run terukur.
- Rencana cutover/rollback yang dipakai pada Fase 07.

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

- [x] P06-T01 Inventarisasi salinan Access dan lindungi sumber
- [x] P06-T02 Definisikan dan hasilkan ekspor data serta manifest
- [x] P06-T03 Ekstrak dan validasi foto dari field Access
- [x] P06-T04 Bangun staging serta importer master dan progress
- [x] P06-T05 Impor anak foto dan metadata dokumen lama
- [x] P06-T06 Selesaikan exception dan pemetaan nilai sumber
- [x] P06-T07 Uji idempotensi sumber berubah dan pemulihan
- [x] P06-T08 Dry run lengkap dan rekonsiliasi dengan pengguna
- [x] P06-T09 Siapkan rencana cutover dan handoff

### P06-T01 Inventarisasi salinan Access dan lindungi sumber

**Acuan:** FR22; OP05.

**Prasyarat:** P05-T10.

**File atau keluaran sasaran:**

- `tools/access-export/README.md`
- `docs/migration/source-inventory.md`
- `Direktori kerja sumber yang tidak masuk git`

**Langkah kerja:**

1. Dapatkan salinan Access beserta versi/formatnya dan identitas sumber stabil. Jangan bekerja dengan modifikasi langsung pada satu-satunya file operasional.
2. Hitung baris master/progress sebenarnya. Bandingkan dengan perkiraan 250 progress tanpa menganggap selisih sebagai kesalahan otomatis.
3. Catat tipe setiap kolom, pola kosong, Activity dan Progress Close yang muncul, keunikan JO/Part, serta tipe field foto.
4. Tentukan metode baca/ekspor yang tersedia pada mesin pengembang. File database, foto, kredensial, dan data nyata tidak dimasukkan ke repository publik.

**Selesai jika:** Sumber diketahui dan semua keputusan ekstraksi berdasarkan bukti tipe field, bukan dugaan.

**Verifikasi:** Catat checksum salinan awal dan hitung ulang setelah inspeksi untuk memastikan tidak berubah.

### P06-T02 Definisikan dan hasilkan ekspor data serta manifest

**Acuan:** FR22; bagian 15 sampai 17.

**Prasyarat:** P06-T01.

**File atau keluaran sasaran:**

- `tools/access-export/export script sesuai lingkungan`
- `tools/access-export/schema.json`
- `docs/migration/export-contract.md`

**Langkah kerja:**

1. Ekspor seluruh 25 kolom master dan 40 kolom progress ke format terstruktur UTF-8, dengan null, tanggal, teks multiline, dan kode nol di depan tetap dibedakan.
2. Gunakan tanggal ISO pada manifest sesudah validasi format sumber; simpan nilai raw bila parsing gagal. Jangan memakai tanggal impor sebagai tanggal bisnis.
3. Manifest memuat source_key, tabel, source_id, row count, checksum file, tipe field, dan daftar foto per asal. Tetapkan identitas sumber yang sama pada pengulangan ekspor sumber sama.
4. Project User dan Status Project tetap ada pada arsip sumber untuk rekonsiliasi, tetapi mapping menandainya tidak menjadi field produk.

**Selesai jika:** Ekspor dapat dibaca secara deterministik dan semua kolom terpetakan tanpa perubahan makna.

**Verifikasi:** Bandingkan beberapa row berisi newline, comma, tanggal kosong, dan nol di depan terhadap Access; hitung row per tabel sama.

### P06-T03 Ekstrak dan validasi foto dari field Access

**Acuan:** FR22; AT30.

**Prasyarat:** P06-T01, P06-T02.

**File atau keluaran sasaran:**

- `tools/access-export/photo extraction script`
- `docs/migration/photo-extraction.md`
- `Manifest foto dalam hasil ekspor`

**Langkah kerja:**

1. Periksa apakah foto memakai Attachment, OLE, atau struktur lain. Pilih teknik ekstraksi yang sesuai lingkungan Access/driver yang benar-benar tersedia.
2. Uji beberapa foto terlebih dahulu termasuk lebih dari satu foto bila ada. Hasil harus berhasil didecode sebagai gambar, bukan hanya byte wrapper OLE dengan ekstensi jpg.
3. Ekstrak foto dengan nama internal aman; catat source table/id/field, nama asli bila tersedia, MIME, ukuran, checksum, dan hasil decode.
4. Jangan mengekstrak byte dokumen Doc1 sampai Doc5 atau Feedback Customer Doc sebagai file migrasi. Foto gagal dicatat sebagai exception, bukan dilewati diam-diam.

**Selesai jika:** Foto berhasil dibuka dan mempunyai hubungan sumber yang jelas; kegagalan ekstraksi terdaftar.

**Verifikasi:** Bandingkan visual sampel dengan tampilan Access; cek checksum setelah penyalinan dan verifikasi count berhasil/gagal.

### P06-T04 Bangun staging serta importer master dan progress

**Acuan:** FR23 FR24; AT29 AT33 AT34.

**Prasyarat:** P06-T02, P06-T03.

**File atau keluaran sasaran:**

- `app/Console/Commands/ImportAccess.php`
- `app/Services/Import/AccessImporter.php`
- `database/migrations/*create_import_tables.php`
- `tests/Feature/Import/`

**Langkah kerja:**

1. Sediakan dry run yang membaca manifest ke staging dan memvalidasi tanpa menulis data operasional. Simpan raw row dan hasil per source identity.
2. Map master sesuai PRD dan tegakkan JO/part satu-ke-satu. Hubungkan progress ke master valid; konflik atau JO tidak ditemukan menjadi exception.
3. Map Activity dan Progress Close hanya berdasarkan nilai sumber yang telah diperiksa. Status/tanggal tidak ditebak dari isi action.
4. Gunakan jalur legacy yang eksplisit untuk nilai nullable dan penanda incomplete; data baru tetap mengikuti required normal.

**Selesai jika:** Master/progress dapat diimpor pada uji dengan sumber dan hasil yang dapat ditelusuri.

**Verifikasi:** Uji JO ganda, part ganda, JO hilang, Activity asing, status asing, tanggal null, dan data minimal; setiap row berakhir imported atau exception.

### P06-T05 Impor anak foto dan metadata dokumen lama

**Acuan:** FR21 FR23; AT29 AT30.

**Prasyarat:** P06-T04.

**File atau keluaran sasaran:**

- `app/Services/Import/ChildMapper.php`
- `app/Services/Import/PhotoImporter.php`
- `app/Services/Import/LegacyDocumentMapper.php`

**Langkah kerja:**

1. Buat problem awal, corrective action, Next Action, dan feedback dari kolom sumber yang terkait. Jangan memecah teks multi-tindakan dengan tebakan.
2. Jika corrective action ada tanpa uraian problem, buat wadah legacy null. Pertahankan PIC/tanggal tanpa uraian; jangan membuang informasi parsial.
3. Foto dihubungkan ke problem yang dapat dipastikan atau progress sumber jika ambigu; copy ke storage privat dan verifikasi checksum.
4. Impor nama/keterangan Doc1 sampai Doc5 dan feedback doc sebagai metadata-only dengan field asal. Tidak membuat storage key atau download palsu.

**Selesai jika:** Seluruh informasi sumber yang termasuk cakupan terwakili dan relasi tidak ditebak.

**Verifikasi:** Uji catatan multiline, action tanpa problem, foto ambigu, serta Ket Doc tanpa nama file; cek UI hasil pada detail progress.

### P06-T06 Selesaikan exception dan pemetaan nilai sumber

**Acuan:** Bagian 19; OP05.

**Prasyarat:** P06-T05.

**File atau keluaran sasaran:**

- `docs/migration/exceptions.md`
- `tools/access-export/value-mapping.json`
- `docs/migration/resolution-decisions.md`

**Langkah kerja:**

1. Kelompokkan exception menurut konflik identitas, parsing tanggal/status, relasi, dan foto gagal. Tampilkan nilai sumber yang diperlukan untuk keputusan.
2. Kumpulkan pertanyaan kepada pemilik data dalam satu daftar; jangan mengisi tanggal/nama buatan atau menggabungkan JO sendiri.
3. Catat keputusan pemetaan yang disetujui dengan sumber dan alasan. Terapkan koreksi pada transformasi/staging yang dapat diulang, bukan mengedit Access asal.
4. Jalankan ulang subset terdampak lalu seluruh validasi. Exception yang belum diputuskan tetap terlihat sebagai belum selesai.

**Selesai jika:** Keputusan pemetaan dapat direproduksi dan exception material mempunyai penyelesaian yang disetujui.

**Verifikasi:** Bandingkan sebelum/sesudah pada ID sumber yang sama dan pastikan resolusi satu konflik tidak mengubah record lain.

### P06-T07 Uji idempotensi sumber berubah dan pemulihan

**Acuan:** FR24; AT31 AT32.

**Prasyarat:** P06-T06.

**File atau keluaran sasaran:**

- `tests/Feature/Import/ImportIdempotencyTest.php`
- `app/Services/Import/AccessImporter.php`
- `docs/migration/rollback-test.md`

**Langkah kerja:**

1. Impor ulang sumber identik menggunakan source_key/table/source_id yang sama; count semua entitas dan foto harus tetap.
2. Ubah satu row sumber uji dan satu target yang sudah dikoreksi admin. Importer melaporkan konflik atau perubahan yang perlu review, tanpa overwrite otomatis.
3. Uji kegagalan di tengah batch: hasil per row dan status batch jelas, retry tidak menggandakan baris yang sudah sukses.
4. Uji pemulihan pada database/storage uji dari backup/snapshot yang dibuat sebelumnya. Jangan menerapkan rollback dengan menghapus seluruh tabel secara sembarang.

**Selesai jika:** Importer aman untuk retry dan perubahan sumber tidak merusak data yang telah digunakan.

**Verifikasi:** Jalankan AT31/AT32 dan cocokkan counts/checksum; dokumentasikan langkah restore serta batasan rollback.

### P06-T08 Dry run lengkap dan rekonsiliasi dengan pengguna

**Acuan:** FR25; AT29 sampai AT34.

**Prasyarat:** P06-T07.

**File atau keluaran sasaran:**

- `docs/migration/reconciliation.md`
- `docs/migration/sample-review.md`
- `Hasil teknis importer pada direktori privat`

**Langkah kerja:**

1. Jalankan sumber lengkap pada staging bersih setelah mapping final. Catat jumlah sumber dan hasil imported/exception per entitas dan file.
2. Cocokkan project/progress, kelima Activity, Open/Closed, tanggal kosong, action/feedback, foto, serta metadata dokumen melalui sampel yang dipilih.
3. Minta pemilik data memeriksa hasil penting; catat persetujuan dan exception yang masih diterima/harus diselesaikan.
4. Bedakan laporan teknis migrasi ini dari fitur laporan bisnis; jangan menambahkan menu report ke aplikasi karena kebutuhan rekonsiliasi.

**Selesai jika:** Seluruh histori dipertanggungjawabkan dan hasil migrasi dapat dinilai pengguna.

**Verifikasi:** Total source rows harus sama dengan imported ditambah exception; periksa tidak ada row diam-diam tidak terhitung.

### P06-T09 Siapkan rencana cutover dan handoff

**Acuan:** FR25; NF09.

**Prasyarat:** P06-T08.

**File atau keluaran sasaran:**

- `docs/migration/cutover-plan.md`
- `docs/migration/rollback-plan.md`
- `docs/progress/P06.md`

**Langkah kerja:**

1. Tentukan calon waktu penghentian input Access, ekspor terakhir, backup, validasi, impor, dan pergantian penggunaan bersama pemilik aplikasi.
2. Tulis siapa menjalankan tiap langkah, indikator gagal, kondisi rollback, dan cara menjaga data yang masuk sesudah perpindahan.
3. Pastikan file sumber/ekspor dan hasil rekonsiliasi disimpan pada lokasi yang ditentukan perusahaan; sumber tidak dihapus.
4. Serahkan dry-run result dan command yang telah teruji ke P07. Jangan menjalankan impor produksi hanya karena rencana sudah ditulis.

**Selesai jika:** Paket migrasi siap dijalankan setelah UAT, backup/restore, dan persetujuan target produksi.

**Verifikasi:** Lakukan walkthrough rencana dengan data uji; pastikan setiap langkah mempunyai hasil pemeriksaan sebelum langkah berikutnya.

## Gate akhir fase

Semua sumber terhitung imported atau exception; konflik material diselesaikan, foto diverifikasi, impor ulang tidak duplikat. Produksi belum diimpor pada fase ini.

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
