# Fase 03 Project Master dan progress dasar

Acuan PRD versi 2.0 • 13 Oktober 2026

Mewujudkan master dan progress dasar memakai database serta UI prototype yang sudah diterima.

## Prasyarat dan bahan baca

Fase 02 selesai; database development dan akun admin uji tersedia.

Baca PRD_Progress_New_Model.md: Bagian 4, 5, 8, 15 sampai 17; FR01 sampai FR06.

## Keluaran fase

- Master dan progress dasar tersimpan nyata dengan validasi.
- Pencarian JO serta autofill terkini.
- Layar detail dan editor menggunakan data operasional melalui service.

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

- [x] P03-T01 Implementasikan form dan Simpan master
- [x] P03-T02 Implementasikan daftar dan detail project
- [x] P03-T03 Implementasikan pencarian JO dan autofill terbaru
- [x] P03-T04 Implementasikan editor progress dasar yang seragam
- [x] P03-T05 Implementasikan Simpan progress dasar secara aman
- [x] P03-T06 Implementasikan daftar dan detail progress
- [x] P03-T07 Tangani validasi dan legacy incomplete pada editor
- [x] P03-T08 Review fungsi dan UI fase master progress

### P03-T01 Implementasikan form dan Simpan master

**Acuan:** FR01 FR03 FR05; AT01 AT02 AT04 AT06.

**Prasyarat:** P02-T09.

**File atau keluaran sasaran:**

- `app/Livewire/Projects/ProjectForm.php`
- `resources/views/livewire/projects/project-form.blade.php`
- `app/Actions/SaveProject.php`

**Langkah kerja:**

1. Gunakan field master sesuai pemetaan; terapkan required JO, Part No, Part Name pada create/edit baru.
2. Validasi dua unique constraint dengan pengecualian ID sendiri saat edit; tangani race unique database menjadi error field yang dapat dipahami.
3. Simpan tanggal secara manual dan raw numeric sebagai teks. Jangan menghidupkan Status Project/Project User.
4. Gunakan service dan transaksi seperlunya; pertahankan input saat gagal. Cegah submit ganda tanpa menghapus data pengguna.

**Selesai jika:** Tambah/edit master bekerja dengan pesan validasi yang tepat dan kode tetap utuh.

**Verifikasi:** Jalankan AT01, AT02, AT04, AT06 serta edit project tanpa mengubah JO/Part No sendiri.

### P03-T02 Implementasikan daftar dan detail project

**Acuan:** FR01 FR02; UI01 UI02.

**Prasyarat:** P03-T01.

**File atau keluaran sasaran:**

- `app/Livewire/Projects/ProjectIndex.php`
- `app/Livewire/Projects/ProjectShow.php`
- `resources/views/livewire/projects/`

**Langkah kerja:**

1. Bangun pencarian JO/Part No/Part Name serta filter customer/model dari data nyata, dengan pagination.
2. Detail menampilkan identitas, jadwal, Event 1 sampai 3, remark, dan progress project terkait.
3. Sediakan Tambah Progress dari detail dengan project_id terpilih. Tidak menampilkan badge status project atau persentase penyelesaian.
4. Pertahankan query filter saat kembali dari detail dan sediakan empty state yang sesuai.

**Selesai jika:** Admin bisa menemukan dan membuka project tanpa membuka form Access atau membaca ID internal.

**Verifikasi:** Cari JO dengan nol di depan, buka detail, lalu kembali; filter dan halaman daftar tetap benar.

### P03-T03 Implementasikan pencarian JO dan autofill terbaru

**Acuan:** FR02; AT03.

**Prasyarat:** P03-T01, P03-T02.

**File atau keluaran sasaran:**

- `app/Livewire/Components/ProjectPicker.php`
- `resources/views/livewire/components/project-picker.blade.php`
- `app/Queries/ProjectLookup.php`

**Langkah kerja:**

1. Dropdown JO mencari teks JO/part dan menampilkan pilihan yang dapat dibedakan. Simpan project_id saat dipilih.
2. Tampilkan Part No, Part Name, Customer, Model, No Doc sebagai read-only. Server mengambil ulang master ketika menyimpan atau membuka form.
3. Tangani project yang tidak ditemukan dengan error tanpa menghapus isi kegiatan. Jangan mempercayai hidden autofill dari browser sebagai perubahan master.
4. Saat project_id draft berubah, tampilkan identitas tujuan dan pertahankan isi kegiatan. Seluruh anak tetap bagian dari progress yang sedang diedit.

**Selesai jika:** Identitas konsisten mengikuti master terkini dan tidak menjadi snapshot kedua.

**Verifikasi:** Edit Part Name master di tab lain kemudian muat ulang editor/detail; jalankan AT03 termasuk draft lama.

### P03-T04 Implementasikan editor progress dasar yang seragam

**Acuan:** FR04 FR05; AT05 AT06.

**Prasyarat:** P03-T03, P02-T06.

**File atau keluaran sasaran:**

- `app/Livewire/Progresses/ProgressEditor.php`
- `resources/views/livewire/progresses/progress-editor.blade.php`
- `resources/views/components/progress/activity-fields.blade.php`

**Langkah kerja:**

1. Hubungkan komponen prototype ke DTO dengan semua field dasar progress, lima Activity tetap, dan PIC bebas.
2. Pastikan rincian trial, quantity, remark, serta Ket Event tetap tersedia pada semua Activity. Jangan memakai conditional yang menghapus bagian karena kategori.
3. Tanggal aktivitas wajib pada final save; actual date anak tetap belum masuk validasi wajib. Raw numeric tidak diparse menjadi float.
4. Siapkan titik integrasi daftar anak dan autosave tanpa menampilkan status draft tersimpan sampai service nyata selesai pada P05.

**Selesai jika:** Form dasar bekerja dan susunannya identik dengan prototype serta semua Activity.

**Verifikasi:** Isi seluruh field, ganti kelima Activity, pindah bagian, dan pastikan nilai serta fokus tetap; uji input raw dan PIC bebas.

### P03-T05 Implementasikan Simpan progress dasar secara aman

**Acuan:** FR06 FR11 FR13.

**Prasyarat:** P03-T04, P02-T07.

**File atau keluaran sasaran:**

- `app/Actions/SaveProgress.php`
- `app/Data/ProgressPayload.php`
- `tests/Feature/Progress/SaveBasicProgressTest.php`

**Langkah kerja:**

1. Implementasikan bagian penyimpanan scalar sesuai kontrak draft_id/submission ID stabil. Simpan pertama membuat Open; edit mempertahankan status.
2. Jalankan validasi final tiga field utama dan pemeriksaan project. Aturan legacy incomplete dibatasi pada record impor.
3. Periksa status/revision di server; tolak edit Closed. Jangan memakai update massal tanpa allowlist field.
4. Siapkan transaksi graph yang akan diperluas P04; scalar yang gagal validasi tidak boleh membuat record setengah tersimpan.

**Selesai jika:** Progress dasar dapat dibuat/diedit melalui service dan request berulang tidak menduplikasi record.

**Verifikasi:** Coba Simpan tanpa field wajib, kirim dua kali ID sama, ubah revision lewat tab lain, dan coba edit Closed langsung.

### P03-T06 Implementasikan daftar dan detail progress

**Acuan:** FR02 FR06 FR18.

**Prasyarat:** P03-T05.

**File atau keluaran sasaran:**

- `app/Livewire/Progresses/ProgressIndex.php`
- `app/Livewire/Progresses/ProgressShow.php`
- `resources/views/livewire/progresses/`

**Langkah kerja:**

1. Daftar menampilkan identitas master terbaru, tanggal, Activity, PIC, Open/Closed, dan akses detail. Gunakan pagination.
2. Detail memakai komponen section yang akan menerima graph anak P04. Jangan menampilkan panel palsu seolah data sudah tersimpan.
3. Tampilkan Edit untuk Open; Closed menjelaskan Reopen yang akan disambungkan P05. Status server tetap menjadi penjaga akses edit.
4. Jaga filter/sort saat navigasi kembali; bedakan belum ada record dengan tidak ada hasil.

**Selesai jika:** Record tersimpan mudah ditemukan dan dibaca tanpa duplikasi identitas atau status project.

**Verifikasi:** Buat tiga progress pada satu JO, koreksi master, lalu periksa seluruh daftar/detail menampilkan identitas terbaru.

### P03-T07 Tangani validasi dan legacy incomplete pada editor

**Acuan:** FR06; bagian 7 dan 19.

**Prasyarat:** P03-T05, P03-T06.

**File atau keluaran sasaran:**

- `app/Livewire/Forms/ProgressFormData.php`
- `resources/views/components/ui/error-summary.blade.php`
- `tests/Feature/Progress/ValidationTest.php`

**Langkah kerja:**

1. Tampilkan error dekat field serta ringkasan yang memindahkan fokus ke bagian yang benar. Isian tidak dikosongkan setelah validasi gagal.
2. Record legacy dengan tanggal atau nama kosong ditampilkan sebagai data belum tersedia; label bantuan tidak disimpan menjadi nilai bisnis.
3. Simpan perubahan legacy meminta field minimum dilengkapi; operasi Close nanti tetap tidak ikut syarat tersebut.
4. Jangan menyisipkan required baru pada uraian/PIC/feedback atau mengisi tanggal hari ini otomatis.

**Selesai jika:** Validasi dapat dipahami dan tidak menambah aturan bisnis tersembunyi.

**Verifikasi:** Uji data baru minimal, data legacy parsial, error di bagian tersembunyi, dan kembali ke form setelah gagal.

### P03-T08 Review fungsi dan UI fase master progress

**Acuan:** AT01 sampai AT06 AT35 AT36.

**Prasyarat:** P03-T07.

**File atau keluaran sasaran:**

- `docs/progress/P03.md`
- `tests/Feature/Project/`
- `tests/Feature/Progress/`

**Langkah kerja:**

1. Jalankan skenario aturan master, autofill, form sama, dan raw input pada PostgreSQL.
2. Periksa tampilan desktop/HP terhadap prototype yang diterima; perbaiki pergeseran layout yang menghambat input.
3. Catat integrasi belum selesai secara tepat: daftar anak/foto di P04, autosave/status/dashboard lengkap di P05.
4. Serahkan hasil dan command verifikasi tanpa mengklaim seluruh aplikasi sudah selesai.

**Selesai jika:** Master/progress dasar stabil dan siap menerima relasi anak.

**Verifikasi:** Pastikan gate fase serta daftar perubahan/file/test yang dijalankan dicatat dengan hasil sebenarnya.

## Gate akhir fase

AT01 sampai AT06 lolos pada PostgreSQL; UI tetap sesuai prototype. Fitur draft/daftar anak yang belum selesai tidak diberi indikator sukses palsu.

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
