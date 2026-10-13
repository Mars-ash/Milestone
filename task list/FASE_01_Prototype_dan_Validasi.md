# Fase 01 Prototype dan validasi pengguna

Acuan PRD versi 2.0 • 13 Oktober 2026

Membuat prototype interaktif yang dapat dipakai pengguna untuk menilai alur dan tampilan sebelum schema operasional serta migrasi dibuat.

## Prasyarat dan bahan baca

PRD versi 2 dan keputusan stack tersedia. Fase ini tidak menunggu server produksi atau file Access.

Baca PRD_Progress_New_Model.md: Bagian 2 sampai 12, 21 sampai 23; fokus UI01 sampai UI12.

## Keluaran fase

- Prototype lokal yang dapat dijalankan ulang dengan fixture fiktif.
- Daftar skenario review, feedback, perbaikan, serta keputusan penerimaan pengguna.
- Screenshot internal desktop/HP dan catatan handoff untuk fondasi operasional.

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

- [x] P01-T01 Bootstrap workspace prototype dengan versi konsisten
- [x] P01-T02 Susun fixture dan adapter data simulasi
- [x] P01-T03 Bangun fondasi visual dan navigasi yang ringkas
- [ ] P01-T04 Prototype dashboard dan pencarian
- [ ] P01-T05 Prototype master project dan identitas terkini
- [ ] P01-T06 Prototype form progress yang sama pada semua Activity
- [ ] P01-T07 Prototype daftar bertingkat dan galeri
- [ ] P01-T08 Prototype autosave draft dan Simpan
- [ ] P01-T09 Prototype detail Close dan Reopen
- [ ] P01-T10 Periksa visual dan aksesibilitas prototype
- [ ] P01-T11 Lakukan review pengguna dengan skenario nyata
- [ ] P01-T12 Revisi prototype dan tetapkan hasil penerimaan

### P01-T01 Bootstrap workspace prototype dengan versi konsisten

**Acuan:** D20; bagian 13.

**Prasyarat:** Tidak ada.

**File atau keluaran sasaran:**

- `README.md pada repository aplikasi`
- `composer.json dan composer.lock`
- `package.json dan lockfile Node`
- `routes/web.php`
- `config/prototype.php`

**Langkah kerja:**

1. Periksa repository yang dipilih pengembang. Jika kosong, bootstrap satu aplikasi Laravel 13, PHP 8.4, Livewire 4, dan Tailwind 4; gunakan dokumentasi versi terpasang. Jangan membuat React/Vue atau repo frontend kedua.
2. Sediakan mode prototype hanya untuk lingkungan local/testing. Halaman prototype belum memakai data operasional; untuk session dummy gunakan file/session adapter yang tidak membutuhkan database bisnis.
3. Pastikan asset CSS/JS dibangun lokal melalui Vite. Kunci dependencies dan catat versi runtime, perintah install, build, dan start yang benar-benar berhasil.
4. Tetapkan direktori app/Prototype dan route berawalan /prototype. Hindari membuat seluruh schema domain sebelum review pengguna.

**Selesai jika:** Halaman prototype terbuka dari petunjuk README dan tidak memerlukan data perusahaan. Tidak ada jalur dummy yang terbuka pada environment production.

**Verifikasi:** Jalankan build asset dan buka route prototype; ubah environment menjadi production pada uji terpisah dan pastikan route dummy tidak tersedia.

### P01-T02 Susun fixture dan adapter data simulasi

**Acuan:** D01 sampai D19; bagian 23.

**Prasyarat:** P01-T01.

**File atau keluaran sasaran:**

- `app/Prototype/FakeStore.php`
- `resources/fixtures/prototype.json`
- `tests/Fixtures/PrototypeDataset.php`

**Langkah kerja:**

1. Buat tiga project fiktif dengan pasangan JO/part unik, termasuk kode dengan nol di depan. Buat delapan progress yang mencakup kelima Activity, Open/Closed, tanggal lama, dan teks panjang.
2. Satu progress berisi dua problem dengan dua corrective action per problem, tiga Next Action, dua feedback, beberapa foto contoh, dan angka raw 1,5 serta 1.5. Semua nama/customer/PIC harus jelas fiktif.
3. Buat draft baru parsial serta draft perubahan. Simulasi penyimpanan menggunakan session atau adapter dummy terpisah; sediakan reset fixture khusus prototype.
4. Gunakan ID stabil untuk setiap anak dan file. Lampiran dummy harus berlabel simulasi dan tidak mengaku telah diunggah ke penyimpanan produksi.

**Selesai jika:** Fixture memenuhi matriks contoh; setiap interaksi dapat diuji berulang setelah reset tanpa kehilangan dataset awal.

**Verifikasi:** Hitung fixture dan relasi; buka satu problem lalu pastikan dua corrective action miliknya berbeda dari problem kedua.

### P01-T03 Bangun fondasi visual dan navigasi yang ringkas

**Acuan:** UI01 sampai UI12; NF06.

**Prasyarat:** P01-T01.

**File atau keluaran sasaran:**

- `resources/css/app.css`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/components/ui/`
- `app/Livewire/Prototype/Shell.php`

**Langkah kerja:**

1. Terapkan token warna, font, spacing, radius, dan tinggi kontrol dari PRD bagian 6. Gunakan satu set ikon; label tindakan penting tetap terlihat.
2. Buat navigasi Dashboard, Project, Progress, Draft dengan active state jelas. Ruang utama didominasi informasi kerja, bukan hero, grafik dekoratif, atau kartu berulang.
3. Siapkan komponen field berlabel, error, tombol, tabel/list, empty state, dialog, status autosave, dan thumbnail. Gunakan fokus keyboard terlihat serta target sentuh cukup besar.
4. Pada 390 px ubah tabel menjadi ringkasan baris; jangan mengecilkan seluruh layar desktop. Pastikan kontrol utama tidak tertutup sticky footer.

**Selesai jika:** Komponen dasar konsisten dan layar tetap terbaca pada 1366, 1024, 768, serta 390 px.

**Verifikasi:** Periksa visual di browser dan navigasi memakai Tab; catat screenshot serta temuan pada docs/review/prototype-visual.md.

### P01-T04 Prototype dashboard dan pencarian

**Acuan:** FR17 FR18; AT23 AT24 AT25.

**Prasyarat:** P01-T02, P01-T03.

**File atau keluaran sasaran:**

- `app/Livewire/Prototype/Dashboard.php`
- `resources/views/livewire/prototype/dashboard.blade.php`
- `app/Prototype/FakeStore.php`

**Langkah kerja:**

1. Tampilkan ringkasan global Open, Draft aktif, dan aktivitas bulan berjalan yang dihitung dari fixture; jangan memakai angka dekoratif.
2. Buat daftar Open terlama, satu baris per progress. Tampilkan tanggal, JO/part, Activity, PIC Progress, ringkasan Next Action, serta Buka Detail.
3. Implementasikan pencarian dan filter lokal atas fixture, pilihan peran PIC, urutan terbaru, serta Clear Filter. Biarkan filter hanya mempengaruhi daftar dan beri label Ringkasan keseluruhan.
4. Buat keadaan daftar kosong dan hasil filter kosong yang berbeda. Tambah Progress dan Lanjutkan Draft harus menuju layar yang berfungsi.

**Selesai jika:** Filter, urutan, ringkasan, dan navigasi dapat dicoba; jumlah baris tidak berlipat karena corrective action.

**Verifikasi:** Gunakan fixture satu progress dengan empat corrective action; pastikan tetap dihitung satu Open dan muncul satu kali pada hasil pencarian PIC.

### P01-T05 Prototype master project dan identitas terkini

**Acuan:** FR01 FR02 FR03; AT01 sampai AT04.

**Prasyarat:** P01-T02, P01-T03.

**File atau keluaran sasaran:**

- `app/Livewire/Prototype/ProjectForm.php`
- `app/Livewire/Prototype/ProjectDetail.php`
- `resources/views/livewire/prototype/project-form.blade.php`

**Langkah kerja:**

1. Tampilkan seluruh kolom master yang dipertahankan pada PRD bagian 15. Hapus Project User dan Status Project dari tampilan, data editor, dan filter.
2. Simulasikan validasi wajib JO, Part No, Part Name serta dua aturan unik. Kode dengan nol di depan tidak berubah.
3. Tampilkan T0 Target/Actual dan Event 1 sampai 3 sebagai tanggal manual terpisah; tidak ada trigger dari Activity.
4. Edit Part Name di master dummy lalu buka progress lama: identitas harus mengikuti perubahan master tanpa mengubah uraian kegiatan.

**Selesai jika:** Pengguna dapat membuat dan mengoreksi master pada simulasi serta memahami hubungan satu-ke-satu JO/part.

**Verifikasi:** Coba duplikasi JO dan Part No secara terpisah; pesan error tepat dan record lain tidak ikut berubah.

### P01-T06 Prototype form progress yang sama pada semua Activity

**Acuan:** FR04 FR05; AT05 AT06.

**Prasyarat:** P01-T03, P01-T05.

**File atau keluaran sasaran:**

- `app/Livewire/Prototype/ProgressForm.php`
- `resources/views/livewire/prototype/progress-form.blade.php`
- `resources/views/components/progress/identity.blade.php`

**Langkah kerja:**

1. Buat dropdown JO yang dapat dicari dan lima Activity dengan ejaan persis. Setelah JO dipilih, tampilkan lima field identitas hanya baca.
2. Susun bagian Data Aktivitas, Problem dan Corrective Action, Next Action, Feedback, Lampiran. Field rincian trial tetap tersedia pada kelima Activity.
3. PIC memakai teks bebas. Input raw menerima koma/titik tanpa format otomatis; tanggal aktual tidak diberi default hari ini.
4. Simulasikan validasi final hanya JO, tanggal aktivitas, Activity. Pertahankan isian dan fokus saat ganti bagian atau Activity.

**Selesai jika:** Form identik untuk semua Activity, nyaman untuk teks panjang, dan kesalahan mudah ditemukan pada bagian terkait.

**Verifikasi:** Isi Mesin dan DH pada Trial lalu ubah menjadi Others; nilainya tetap tersedia. Simpan kosong menyorot hanya tiga field wajib.

### P01-T07 Prototype daftar bertingkat dan galeri

**Acuan:** FR07 sampai FR11 FR19; AT07 sampai AT11 AT26.

**Prasyarat:** P01-T06.

**File atau keluaran sasaran:**

- `resources/views/components/progress/problems.blade.php`
- `resources/views/components/progress/corrective-actions.blade.php`
- `resources/views/components/progress/next-actions.blade.php`
- `resources/views/components/progress/feedback.blade.php`
- `resources/views/components/progress/gallery.blade.php`

**Langkah kerja:**

1. Tambah dua problem dan beberapa corrective action dalam masing-masing problem. Gunakan client_id stabil sebagai key; nomor tampilan hanya label.
2. Buat Next Action sebagai daftar saudara problem, bukan anak problem. Tambah feedback sebagai riwayat berulang dengan PIC/tanggal sendiri.
3. Simulasikan beberapa foto per problem, caption, preview besar, upload berjalan dan gagal. Dokumen baru serta metadata dokumen lama ditampilkan dengan perilaku berbeda.
4. Penambahan baris memindahkan fokus ke input pertama; hapus hanya baris dummy/baru dalam simulasi. Pertahankan nilai saat menambah item lain.

**Selesai jika:** Struktur dua tingkat corrective action mudah dipahami dan semua isi kembali ke induk yang benar.

**Verifikasi:** Isi PIC berbeda pada empat tindakan, tambah problem ketiga, kembali ke problem pertama; tidak ada nilai atau foto yang tertukar.

### P01-T08 Prototype autosave draft dan Simpan

**Acuan:** FR12 sampai FR15; AT12 sampai AT19 AT37.

**Prasyarat:** P01-T06, P01-T07.

**File atau keluaran sasaran:**

- `app/Prototype/FakeDraftStore.php`
- `app/Livewire/Prototype/ProgressForm.php`
- `app/Livewire/Prototype/DraftList.php`
- `resources/views/components/progress/save-state.blade.php`

**Langkah kerja:**

1. Simulasikan autosave setelah perubahan dengan indikator Menyimpan draft dan konfirmasi waktu. Draft parsial tetap boleh tersimpan.
2. Persist fixture draft pada session adapter sehingga reload dalam sesi yang sama memulihkan data; jelaskan bahwa ini perilaku simulasi.
3. Pisahkan Simpan final dari autosave: data daftar kerja baru berubah setelah Simpan. Tampilkan retry, error validasi, konflik dua tab, serta upload belum selesai sebagai state prototype.
4. Buat daftar draft baru/perubahan dan Buang Draft dengan dialog. Jangan membuat draft kosong hanya karena form dibuka.

**Selesai jika:** Pengguna dapat membedakan data draft dan data tersimpan, melanjutkan draft, serta memahami kegagalan simpan.

**Verifikasi:** Edit fixture progress, tunggu autosave, buka dashboard: versi lama tetap. Sesudah Simpan, versi baru terlihat. Ulangi dengan mode gagal.

### P01-T09 Prototype detail Close dan Reopen

**Acuan:** FR16; AT20 AT21 AT22.

**Prasyarat:** P01-T04, P01-T08.

**File atau keluaran sasaran:**

- `app/Livewire/Prototype/ProgressDetail.php`
- `resources/views/livewire/prototype/progress-detail.blade.php`
- `resources/views/components/progress/status-actions.blade.php`

**Langkah kerja:**

1. Tampilkan detail lengkap dan Open/Closed dengan label. Close tersedia untuk data tersimpan walau action atau feedback kosong.
2. Saat Closed, tampilkan Reopen dan blokir edit langsung. Reopen membuka edit tanpa mereset data.
3. Jika ada draft belum diterapkan, jelaskan bahwa Close menutup versi tersimpan; tetap sediakan jalan menutup versi itu tanpa melengkapi draft.
4. Perlihatkan hasil status pada dashboard dan jangan mengubah status project atau aktivitas lain.

**Selesai jika:** Close/Reopen dipahami sebagai keputusan manual yang berbeda dari Simpan.

**Verifikasi:** Tutup catatan dengan actual date kosong dan draft belum valid; status berubah, draft tetap ada, record lain tidak berubah.

### P01-T10 Periksa visual dan aksesibilitas prototype

**Acuan:** UI01 sampai UI12; AT35 AT36.

**Prasyarat:** P01-T04 sampai P01-T09.

**File atau keluaran sasaran:**

- `docs/review/prototype-visual.md`
- `resources/views/components/ui/`
- `resources/css/app.css`

**Langkah kerja:**

1. Uji seluruh layar pada viewport yang ditetapkan PRD. Periksa overlap, ruang kosong berlebihan, tabel terlalu rapat, label terpotong, dan tombol menutupi field.
2. Gunakan keyboard untuk navigasi, tambah tindakan, dialog, dan galeri. Periksa pengembalian fokus dan kontras teks/komponen.
3. Pastikan tidak ada grafik/kartu fiktif, gradient dekoratif, badge berlebihan, atau teks pemasaran. Setiap kontrol harus berfungsi atau ditandai simulasi secara jelas.
4. Perbaiki masalah UX yang menghambat skenario sebelum meminta pengguna melakukan review. Catat hasil serta layar yang diperiksa.

**Selesai jika:** Seluruh layar penting bersih dan skenario dapat dijalankan di desktop maupun HP.

**Verifikasi:** Jalankan AT35 dan AT36 secara manual; simpan bukti temuan dan perbaikannya, bukan hanya pernyataan tampilan sudah bagus.

### P01-T11 Lakukan review pengguna dengan skenario nyata

**Acuan:** Bagian 23; D20.

**Prasyarat:** P01-T10.

**File atau keluaran sasaran:**

- `docs/review/prototype-scenarios.md`
- `docs/review/prototype-feedback.md`

**Langkah kerja:**

1. Siapkan skenario dari PRD bagian 23 dalam bahasa pengguna, tanpa instruksi teknis seperti klik komponen X. Gunakan data fiktif.
2. Minta pengguna mengerjakan input trial, beberapa corrective action, draft, pencarian, Close kosong, dan Reopen. Catat keberhasilan, keraguan, salah klik, dan bantuan yang dibutuhkan.
3. Kelompokkan feedback menjadi perbaikan tampilan/alur, bug simulasi, dan perubahan aturan bisnis. Ajukan pertanyaan yang masih perlu sekaligus.
4. Jangan mengarang hasil review bila pengguna belum mencoba. Tandai sesi sebagai belum dilakukan dan berhenti sebelum fondasi produksi.

**Selesai jika:** Ada feedback nyata yang dapat ditindaklanjuti serta daftar keputusan yang jelas.

**Verifikasi:** Pastikan catatan menyebut siapa/peran yang meninjau, tanggal, versi prototype, skenario, temuan, dan keputusan tanpa mengisi data palsu.

### P01-T12 Revisi prototype dan tetapkan hasil penerimaan

**Acuan:** D20; UI01 sampai UI12.

**Prasyarat:** P01-T11.

**File atau keluaran sasaran:**

- `docs/review/prototype-acceptance.md`
- `docs/progress/P01.md`
- `docs/decisions.md`

**Langkah kerja:**

1. Perbaiki temuan yang telah disetujui dan ulangi hanya skenario terdampak. Jangan menambahkan fitur yang tidak dibahas.
2. Jika feedback mengubah aturan bisnis, tulis usulan perubahan PRD beserta ID terkait dan minta konfirmasi sebelum mengedit PRD.
3. Catat versi prototype yang diterima, temuan minor yang disepakati ditunda, struktur layar final, dan komponen yang dapat digunakan kembali.
4. Buat handoff untuk P02 yang membedakan adapter dummy, UI yang diterima, dan perilaku yang masih perlu implementasi database nyata.

**Selesai jika:** Ada penerimaan pengguna yang eksplisit terhadap prototype dan semua penghambat review sudah selesai.

**Verifikasi:** Periksa checklist fase, feedback, dan catatan penerimaan. Jika belum ada penerimaan, status fase tetap blocked dan P02 belum dimulai.

## Gate akhir fase

Pengguna menyelesaikan skenario review dan menerima struktur form serta perilaku draft/Close. Catat persetujuan beserta versi prototype; jika belum diterima, perbaiki fase ini sebelum P02.

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
