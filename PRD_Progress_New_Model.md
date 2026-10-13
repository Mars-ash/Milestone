# PRD Aplikasi Progress New Model

Versi 2.0 • 13 Oktober 2026 • Acuan produk dan pelaksanaan prototype

Dokumen ini menetapkan aplikasi pendataan aktivitas pengembangan new model pengganti Microsoft Access. Pengguna operasionalnya satu admin; pengembangnya satu orang yang bekerja dengan bantuan AI. Tujuan utamanya adalah input yang nyaman, tindak lanjut yang mudah ditemukan, dan histori kegiatan beserta foto yang tetap dapat ditelusuri.

Pekerjaan dimulai dengan prototype interaktif dan review pengguna. Implementasi data operasional dilakukan setelah alur, istilah, dan tampilan prototype disetujui. Paket task list memecah pekerjaan menjadi tujuh fase dengan prasyarat, keluaran, langkah, verifikasi, dan kriteria selesai yang dapat dijalankan satu tugas pada satu waktu.

## 1 Kedudukan dokumen dan tujuan

PRD versi 2 menggantikan keputusan yang berbeda pada versi 1. Kebutuhan yang telah disepakati dibedakan dari parameter operasional yang masih perlu ditetapkan. Versi Markdown dan Word memuat isi PRD yang sama. File task fase menerjemahkan kebutuhan ini menjadi pekerjaan; task tidak boleh mengubah aturan bisnis secara diam-diam.

Hasil yang ingin dicapai adalah admin dapat memilih JO dan melihat identitas terkini, mencatat semua jenis Activity melalui satu bentuk form, menambahkan problem dan tindakan berulang, melanjutkan draft, serta menemukan progress Open. Close dan Reopen merupakan keputusan admin. Jumlah aktivitas bukan persentase penyelesaian proyek.

Keberhasilan prototype diukur melalui penyelesaian skenario kerja oleh pengguna dengan pendampingan minimal, keterbacaan data, kemudahan koreksi, serta pemahaman tentang draft dan Simpan. Ukur waktu dan kesalahan selama review sebagai baseline; dokumen ini tidak mengklaim peningkatan waktu yang sudah terbukti.

Pengembang mengelola kode, konfigurasi, dan migrasi. Admin mencatat kegiatan dan PIC. PIC adalah nama yang diketik bebas, bukan akun aplikasi. Belum ada kebutuhan akun viewer, approval berjenjang, atau portal customer.

### Cara memakai paket

1. Baca PRD dan README paket untuk memahami aturan yang berlaku.
2. Jalankan Fase 01 dan gunakan fixture fiktif. Simpan feedback serta keputusan review.
3. Sesudah prototype diterima, lanjutkan Fase 02 sampai Fase 07 sesuai ketergantungan.
4. Perubahan PRD berikutnya harus diajukan beserta dampaknya dan mendapat konfirmasi pemilik kebutuhan terlebih dahulu.

### Istilah utama

Project adalah satu pasangan JO No dan Part No. Progress adalah satu catatan kegiatan pada project. Problem adalah satu temuan pada progress. Corrective Action adalah tindakan terhadap satu problem. Next Action adalah langkah lanjutan yang berdiri pada progress. Feedback adalah satu entri tanggapan customer. Draft adalah isian kerja yang belum diterapkan menjadi data progress tersimpan.

<!-- pagebreak -->

## 2 Keputusan produk yang berlaku

| ID | Keputusan yang harus dipertahankan |
| --- | --- |
| D01 | Satu JO No untuk satu Part No dan satu Part No untuk satu JO No. Keduanya unik pada Project Master. |
| D02 | Satu admin. Semua PIC diketik bebas. Project User dan Status Project dihapus dari aplikasi. |
| D03 | Activity persis Trial, Sent Sample, Send Document, Audit, Others. Semua memakai form yang sama. |
| D04 | JO No dan Activity berupa dropdown. Part No, Part Name, Customer, Model, No Doc terisi dari master terkini. |
| D05 | Minimum proyek baru: JO No, Part No, Part Name. Minimum progress final: JO No, tanggal aktivitas, Activity. |
| D06 | Satu progress memiliki banyak problem; setiap problem memiliki banyak corrective action dengan PIC dan actual date masing-masing. |
| D07 | Next Action berulang dan terpisah dari problem. Setiap entri memiliki PIC dan actual date sendiri. |
| D08 | Feedback customer dapat berulang; setiap entri memiliki tanggal, isi, PIC, dan lampiran terkait. |
| D09 | Action tidak mempunyai target date. Tanggal kosong tidak menentukan status pekerjaan. |
| D10 | Close hanya melalui tombol admin. Closed harus Reopen sebelum diedit. Reopen menjaga seluruh isi. |
| D11 | Autosave menyimpan draft. Simpan menerapkan data ke progress; draft tidak masuk hitungan Open atau Closed. |
| D12 | Identitas progress mengikuti master terbaru; tidak ada snapshot identitas per aktivitas. |
| D13 | T0 adalah pengiriman sampel pertama. T0 Target dan T0 Actual adalah tanggal berbeda dan diisi admin. |
| D14 | PP1 menjadi Event 1, PP2 menjadi Event 2, MP menjadi Event 3. Semua tanggal master diisi manual. |
| D15 | DH berarti Die High pada mesin. Ukuran, DH, dan kuantitas menerima penulisan manual; koma/titik dipertahankan. |
| D16 | Koreksi melalui edit. Audit trail pengguna, tautan antaraktivitas, laporan, dan ekspor tidak masuk rilis ini. |
| D17 | Migrasi mencakup seluruh histori, diperkirakan sekitar 250 catatan progress. Angka ini perkiraan pengguna, bukan hasil pemeriksaan sumber. |
| D18 | Foto lama diambil dari dalam Access. File dokumen lama tidak dipindahkan; nama dan keterangannya dipertahankan sebagai arsip metadata. |
| D19 | Foto baru mendukung JPG/JPEG, PNG, WebP. Dokumen baru dan lampiran feedback baru tetap didukung. |
| D20 | Stack Laravel, PostgreSQL, Blade, Livewire, Tailwind CSS. Prototype dan review pengguna mendahului implementasi penuh. |

Status Project yang dihapus berbeda dari status progress Open/Closed. Penyimpanan draft juga berbeda dari status penyelesaian kegiatan. Penghapusan audit trail produk tidak menghapus kebutuhan timestamp teknis, log error, atau jejak pemetaan migrasi.

<!-- pagebreak -->

## 3 Cakupan rilis dan batas pekerjaan

Rilis pertama mencakup login satu admin, Project Master, daftar dan detail JO, form progress lengkap yang seragam, problem dan corrective action berulang, Next Action, feedback berulang, galeri foto, dokumen baru, autosave draft, Close/Reopen manual, dashboard, pencarian, serta migrasi seluruh histori dan foto.

Perbaikan dibanding Access terutama berupa hubungan data yang tidak dibatasi satu problem atau satu tindakan, pemulihan draft, akses melalui browser, pencarian lintas JO, dan alur kerja yang dapat digunakan dari desktop maupun HP. Akses di luar LAN tetap bergantung pada penempatan server dan jaringan yang dipilih pada fase operasional.

### Di luar cakupan rilis ini

- Status Project dan Project User, termasuk kontrol UI serta filter keduanya.
- Master PIC, akun per PIC, role tambahan, portal customer, dan alur persetujuan.
- Target date action, penutupan otomatis, persentase penyelesaian proyek, serta status action yang diturunkan dari tanggal kosong.
- Audit trail yang menampilkan nilai sebelum/sesudah, tautan otomatis antaraktivitas, label prioritas/pin yang belum disepakati, laporan bisnis dan ekspor.
- Migrasi byte dokumen lama, integrasi ERP/mesin, notifikasi email/WhatsApp, dan mode kerja offline penuh.
- Fitur hapus permanen atau arsip record operasional sebagai menu tersendiri. Koreksi dilakukan dengan edit; kasus pembatalan record yang tidak bisa dikoreksi menjadi bahan keputusan produk baru.

Menghapus baris anak yang belum disimpan dalam editor dan membuang draft adalah bagian pengelolaan isian. Ini tidak memberi izin menghapus project/progress tersimpan secara massal. Penghapusan anak/lampiran yang sudah tersimpan juga tidak ditambahkan pada rilis ini tanpa keputusan lebih lanjut; admin dapat memperbaiki keterangannya.

### Prototype dan aplikasi operasional

Prototype harus memeragakan seluruh interaksi penting dengan data fiktif, termasuk nested corrective action, draft, gagal simpan, serta Close/Reopen. Data simulasi tidak boleh diklaim sebagai data operasional atau hasil migrasi. Mekanisme dummy berada dalam adapter terpisah yang dilepas sebelum rilis.

Kelengkapan dokumen berarti kebutuhan dan langkah pelaksanaannya dapat ditelusuri. Parameter server, backup, batas ukuran file, serta format dokumen baru tetap dicatat sebagai parameter operasional dengan pemilik dan batas waktu penetapan pada bagian 23.

<!-- pagebreak -->

## 4 Alur kerja dan keadaan data

### Mencatat aktivitas

Admin membuka Tambah Progress, memilih JO, melihat identitas master, memilih Activity dan tanggal. Semua bagian form tersedia dengan susunan yang sama. Admin dapat menambah beberapa problem, corrective action per problem, Next Action, feedback, dan lampiran. Autosave bekerja setelah ada perubahan; Simpan melakukan validasi final dan membuat progress Open.

### Mengoreksi progress

Untuk progress Open, Edit membuka salinan kerja sebagai draft perubahan. Data yang sedang digunakan dashboard/detail tetap merupakan versi tersimpan terakhir sampai admin menekan Simpan. Untuk progress Closed, admin harus Reopen terlebih dahulu. Reopen mengubah status menjadi Open tanpa membersihkan isi, actual date, foto, maupun feedback.

### Menutup progress

Close tersedia pada progress tersimpan. Tidak ada syarat semua problem selesai, semua tanggal terisi, feedback diterima, atau action terisi. Jika editor memiliki perubahan belum diterapkan, jelaskan bahwa draft dan data tersimpan berbeda; admin dapat menyimpan perubahan atau menutup versi tersimpan dengan tetap mempertahankan draft. Validasi isi draft tidak boleh menghalangi Close atas versi tersimpan.

| Keadaan awal | Tindakan | Hasil |
| --- | --- | --- |
| Form baru belum berubah | Membuka atau meninggalkan form | Tidak membuat draft kosong. |
| Draft baru | Autosave | Draft diperbarui; belum ada progress Open. |
| Draft baru | Simpan valid | Satu progress Open dibuat; draft ditandai diterapkan. |
| Progress Open | Edit dan autosave | Versi tersimpan tetap; draft perubahan diperbarui. |
| Draft perubahan | Simpan valid | Isi progress diperbarui; status tetap Open. |
| Progress Open | Close | Menjadi Closed tanpa syarat kelengkapan tambahan. |
| Progress Closed | Edit langsung | Ditolak; tampilkan Reopen. |
| Progress Closed | Reopen | Menjadi Open; seluruh isi dipertahankan. |
| Draft terkait progress Closed | Membuka draft | Dapat melihat draft; perlu Reopen sebelum menerapkannya. |

Simpan berulang atau pengiriman ulang akibat gangguan tidak boleh membuat progress kedua. Close/Reopen berulang pada keadaan yang sama bersifat aman dan tidak mengubah data anak. Tidak ada Close/Reopen massal pada rilis awal.

<!-- pagebreak -->

## 5 Layar dan organisasi form

| Layar | Isi dan tindakan utama |
| --- | --- |
| Dashboard | Ringkasan Open, Draft, aktivitas bulan berjalan; daftar kerja; Tambah Progress; Lanjutkan Draft. |
| Project Master | Pencarian JO/part/customer/model; tambah/edit; kolom JO, part, customer, model. |
| Detail project | Identitas terkini, jadwal T0 dan proyek, Event 1 sampai 3, remark, daftar progress. |
| Detail progress | Ringkasan kegiatan, seluruh problem/tindakan/feedback/lampiran, Edit atau Reopen, Close jika Open. |
| Form progress | Identitas otomatis, bagian form seragam, jumlah entri per bagian, status autosave dan Simpan. |
| Daftar draft | Draft baru atau perubahan, JO bila dipilih, Activity bila diisi, waktu autosave terakhir, Lanjutkan dan Buang Draft. |
| Login | Satu akun admin; pesan error jelas; tidak ada registrasi publik. |

### Susunan form yang sama untuk semua Activity

Bagian pertama selalu menampilkan dropdown JO, identitas autofill hanya baca, tanggal, Activity, uraian, PIC Progress, PIC Customer. Bagian rincian memuat Process Trial, Mesin, DH, Material Actual Trial, Qty Material, Qty Process, Qty Sample, Ket Event, dan Remark Progress.

Bagian selanjutnya adalah Problem dan Corrective Action, Next Action, Feedback Customer, serta Lampiran. Pada desktop gunakan navigasi bagian atau tab dengan penghitung entri; pada HP gunakan bagian yang dapat dibuka dengan urutan yang sama. Pergantian tab atau Activity tidak menghapus atau mengubah isian. Semua field tetap dapat diakses untuk kelima Activity.

Setiap problem memakai identitas internal stabil dan label urutan yang mudah dibaca. Di dalamnya terdapat daftar corrective action, masing-masing berisi uraian, PIC teks bebas, dan actual date. Foto melekat pada problem yang dipilih. Next Action dan feedback berada pada daftar berbeda di tingkat progress.

Tambah Problem, Tambah Corrective Action, Tambah Next Action, dan Tambah Feedback harus memiliki label jelas. Setelah penambahan, fokus berpindah ke input pertama item baru. Baris baru kosong yang belum pernah diisi tidak disimpan sebagai data anak.

Identitas project tidak disalin menjadi sumber kedua pada progress. Jika JO diubah dalam draft, tampilkan identitas baru dan pertahankan data kegiatan; sebelum Simpan admin melihat JO tujuan secara jelas. Semua data anak ikut progress yang sama dan tidak dipindahkan secara terpisah ke project lain.

<!-- pagebreak -->

## 6 Standar visual antarmuka

UI harus terlihat sebagai alat kerja pendataan. Keputusan visual didasarkan pada hierarki informasi dan kebiasaan kerja pengguna. Prototype menjadi tempat mengecek kepadatan data, keterbacaan, dan posisi tindakan.

| ID | Pedoman visual yang dapat diperiksa |
| --- | --- |
| UI01 | Gunakan navigasi pendek: Dashboard, Project, Progress, Draft. Judul halaman langsung menyebut objek dan tugas. |
| UI02 | Desktop memakai tabel untuk membandingkan record; form memakai label di atas input. Hindari menjadikan setiap field sebuah kartu. |
| UI03 | Ringkasan dashboard berupa tiga angka ringkas. Daftar kerja menjadi area paling dominan. Tidak ada hero pemasaran atau ilustrasi dekoratif. |
| UI04 | Palet awal netral dengan satu aksen biru. Warna status hanya membantu label teks, tidak menggantikan label. |
| UI05 | Baseline: latar #F5F6F8, permukaan #FFFFFF, teks #18212B, teks sekunder #536170, border #D8DEE6, aksen #2459A6. Uji kontras sebelum diterima. |
| UI06 | Font system sans atau satu keluarga yang disediakan lokal. Teks utama 14 sampai 16 px, judul halaman 24 px, label minimal 13 px; HP input minimal 16 px. |
| UI07 | Skala jarak 4, 8, 12, 16, 24, 32 px; radius 6 sampai 8 px. Bayangan digunakan hanya bila membantu lapisan dialog. |
| UI08 | Tinggi baris desktop sekitar 44 sampai 52 px, mengikuti panjang isi. Target sentuh utama minimal 44 px pada HP. |
| UI09 | Satu tombol utama Simpan pada form. Close dan Reopen diberi konteks terpisah dari penyimpanan. Tindakan berisiko tidak menjadi tombol dominan tanpa konteks. |
| UI10 | Foto berupa thumbnail seragam dengan keterangan dan pembesaran. Tidak menggunakan foto sebagai latar halaman. |
| UI11 | Ikon memakai satu set yang konsisten, disertai label untuk tindakan penting. Tidak memakai emoji sebagai ikon navigasi. |
| UI12 | Hindari gradient dekoratif, glassmorphism, kartu statistik berlebihan, pill pada setiap nilai, animasi terus-menerus, grafik tanpa kebutuhan, dan angka pencapaian buatan. |

Nilai visual di atas adalah baseline desain untuk diuji pada prototype, bukan identitas merek perusahaan yang sudah disediakan. Perubahan tampilan dapat dilakukan selama tetap memenuhi keterbacaan dan tidak mengubah aturan bisnis.

UI harus diuji pada lebar 1366, 1024, 768, dan 390 px serta diperiksa agar tetap dapat digunakan pada 360 px. Tabel desktop boleh berubah menjadi ringkasan baris di HP. Form tidak membutuhkan scroll horizontal. Navigasi bagian dan tombol Simpan tidak menutupi kolom terakhir atau pesan error.

<!-- pagebreak -->

## 7 Perilaku UX dan keadaan antarmuka

### Input dan validasi

Label tetap terlihat setelah diisi; placeholder bukan pengganti label. Hanya field wajib final yang diberi penanda wajib. Kesalahan validasi tampil dekat field dan dalam ringkasan pendek yang bisa membawa fokus ke field bermasalah. Tab/bagian berisi error diberi indikator. Pesan berbahasa Indonesia menyebut tindakan yang diperlukan, misalnya Pilih JO sebelum menyimpan progress.

Tanggal bisnis menggunakan pemilih tanggal dan tampilan yang konsisten. Tidak mengisi actual date dengan hari ini secara diam-diam. Angka manual menggunakan input teks yang mempertahankan koma/titik dan tidak memasukkan pemisah ribuan otomatis. Tidak ada agregasi numerik untuk nilai raw pada rilis ini.

### Autosave dan koneksi

Tampilkan Menyimpan draft, Draft tersimpan pukul HH:mm, atau Draft belum tersimpan. Jangan menampilkan status sukses sebelum server mengonfirmasi. Saat gagal, pertahankan isian di halaman dan sediakan Coba Lagi. Beri peringatan saat keluar jika masih ada perubahan yang belum diakui server. Autosave tidak menjanjikan pemulihan setelah browser ditutup sebelum ada konfirmasi simpan.

Perpindahan tab form tidak memicu kehilangan fokus atau menutup bagian yang sedang diisi. Loading lokal ditampilkan pada komponen yang bekerja. Selama Simpan final berjalan, cegah klik ganda; tetap perlakukan pengiriman ulang dengan aman di server.

### Daftar dan pencarian

Filter aktif terlihat, dapat dibersihkan, dan dipertahankan saat kembali dari detail. Bedakan Tidak ada progress Open dari Tidak ada hasil yang cocok dengan filter. Hasil kosong menawarkan langkah yang sesuai, seperti Tambah Progress atau Bersihkan Filter, tanpa membuat data contoh sebagai data nyata.

### Keyboard dan aksesibilitas

Semua input memiliki label, urutan tab mengikuti alur baca, fokus terlihat, dialog dapat ditutup dengan Escape, dan fokus kembali ke pemicu. Galeri foto dapat dioperasikan dengan keyboard. Pesan simpan/unggah menggunakan pengumuman status yang tidak mengganggu setiap ketikan. Uji kontras setidaknya 4,5 banding 1 untuk teks normal dan 3 banding 1 untuk komponen penting.

### Microcopy utama

Gunakan Simpan, Tambah Progress, Lanjutkan Draft, Close, Reopen, dan Buang Draft secara konsisten. Buang Draft memerlukan konfirmasi karena menghapus isian sementara; jangan menyebutnya Hapus Progress. Metadata dokumen lama berlabel File lama tidak dimigrasikan dan tidak memiliki tombol unduh aktif.

<!-- pagebreak -->

## 8 Kebutuhan proyek dan progress

### FR01 Project Master

Admin dapat menambah, melihat, mencari, dan mengedit project. JO No dan Part No masing-masing wajib unik; Part Name wajib terisi untuk data baru. Identitas berupa teks sehingga angka nol di depan tetap utuh. Trim spasi luar saat memeriksa identitas, tanpa mengubah angka atau menyisipkan format. Perbedaan casing pada data impor ditandai untuk review, tidak digabung berdasarkan tebakan.

Project User dan Status Project tidak mempunyai field, dropdown, filter, badge, atau logika aplikasi. Project tidak dihapus otomatis ketika semua progress Closed.

### FR02 Identitas otomatis terkini

Pemilihan JO menampilkan Part No, Part Name, Customer, Model, dan No Doc dari Project Master. Nilai ini hanya baca di progress. Perubahan master terlihat pada seluruh detail/list progress dan saat draft dibuka kembali. Draft menyimpan project_id, bukan snapshot identitas sebagai sumber kebenaran. Mengubah master tidak mengubah isi kegiatan atau status progress.

### FR03 Jadwal master

Date Receive, T0 Target, T0 Actual, Plan Start, Plan Finish, Actual Start, Actual Finish, Event 1, Event 2, Event 3 tetap terpisah. T0 Target adalah rencana pengiriman sampel pertama dan T0 Actual adalah tanggal pengiriman sampel pertama yang dicatat admin. Pengisian Sent Sample tidak otomatis mengisi T0 Actual. Semua tanggal boleh kosong selain tanggal progress pada Simpan final baru.

### FR04 Satu form untuk lima Activity

Activity menggunakan lima label persis sesuai D03. Semua jenis mempunyai field, bagian, dan kemampuan problem/tindakan/feedback/lampiran yang sama. Pergantian Activity hanya mengubah kategori. Form tidak menyembunyikan data karena Activity berbeda. Uraian, PIC, foto, action, feedback, dan nilai trial tidak dijadikan syarat wajib tambahan.

### FR05 Input teks dan angka manual

Semua PIC berupa teks bebas dan tidak mempunyai relasi wajib ke akun atau master PIC. Process dan Process Trial tetap dua kolom teks terpisah sesuai sumber, tanpa menambah arti bisnis baru. DH ditampilkan sebagai DH dengan bantuan teks Die High. Material Thickness, Material Width, DH, Qty Material, Qty Process, Qty Sample disimpan sebagai nilai raw teks; contoh 1,5 dan 1.5 tetap berbeda secara tampilan. Tidak memaksa satuan atau melakukan konversi otomatis.

### FR06 Koreksi data

Project dan progress dapat dikoreksi melalui Edit. Progress Closed perlu Reopen. Ubah identitas yang bertentangan dengan keunikan ditolak dengan pesan yang menyebut field terkait. Tidak membuat audit trail produk. created_at, updated_at, dan nomor revisi teknis tetap tersedia untuk urutan dan perlindungan dari penimpaan data.

<!-- pagebreak -->

## 9 Problem tindakan dan feedback berulang

### FR07 Problem dan foto problem

Satu progress memiliki nol atau lebih problem. Setiap problem memiliki uraian, urutan tampilan, ID stabil, galeri foto, dan daftar corrective action. Problem dapat digunakan pada semua Activity. Problem kosong yang dibuat untuk menampung isi legacy tidak diisi dengan kalimat buatan; tampilkan keterangan UI Uraian problem belum tersedia tanpa menyimpan kalimat itu sebagai fakta.

### FR08 Corrective Action

Satu problem memiliki nol atau lebih corrective action. Setiap entri menyimpan uraian tindakan, PIC teks bebas, actual date yang boleh kosong, dan urutan. Tidak meletakkan satu kolom corrective action tunggal pada progress atau problem sebagai pembatas. PIC dan tanggal milik corrective action, bukan milik seluruh daftar problem.

Action dapat dicatat sebelum tanggal aktual diketahui. Isi PIC atau tanggal tanpa uraian tidak dibuang diam-diam; aturan minimal final hanya berlaku pada progress. Baris yang seluruh isi bisnisnya kosong boleh diabaikan. Tidak ada target date, checkbox selesai wajib, persentase, atau penutupan problem otomatis.

### FR09 Next Action

Satu progress memiliki nol atau lebih Next Action. Setiap entri menyimpan uraian, PIC, actual date yang boleh kosong, dan urutan. Daftar ini tidak memerlukan problem_id dan tidak dipindahkan ke corrective action. Next Action dapat ada walaupun tidak ada problem. Tidak ada tautan ke aktivitas pelaksanaan pada rilis ini.

### FR10 Feedback Customer

Feedback disimpan sebagai daftar per progress. Setiap entri berisi tanggal feedback, isi feedback, PIC feedback, dan nol atau lebih dokumen pendukung baru. PIC Customer pada progress berbeda dari PIC Feedback Customer. Menambahkan feedback tidak menimpa entri terdahulu. Tanggal kosong tetap boleh kosong dan tidak diisi tanggal unggah.

### FR11 Integritas daftar bertingkat

ID baris stabil dipakai sejak draft agar tambah, pindah fokus, autosave, dan Simpan tidak menukar PIC, tanggal, ataupun foto. Baris dan file hanya dapat digunakan oleh induk yang sah. Mengirim ID corrective action milik problem lain ditolak. Semua perubahan data terstruktur pada Simpan final berada dalam satu transaksi.

Nomor tampilan seperti Problem 1 dan Corrective Action 2 membantu pengguna, tetapi bukan identitas database. Mengedit uraian suatu problem tidak membuat corrective action pindah ke problem lain. Item tersimpan tidak dihapus diam-diam karena tidak ikut dalam payload draft yang belum lengkap.

<!-- pagebreak -->

## 10 Draft otomatis dan Simpan final

### FR12 Autosave draft

Autosave aktif setelah perubahan nyata pertama. Baseline implementasi adalah menyimpan setelah jeda sekitar 1 detik dari perubahan terakhir dan saat meninggalkan field/bagian bila masih ada perubahan. Jeda dapat disetel pada prototype tanpa mengubah semantik draft. Kirim hanya setelah upload terkait memperoleh identitas staging yang sah; unggahan yang masih berjalan tetap ditampilkan sebagai belum siap.

Draft baru boleh belum mempunyai JO, Activity, atau tanggal. Draft perubahan terikat ke progress_id dan versi progress saat editor dibuka. Per admin hanya ada satu draft perubahan aktif per progress; draft baru dibedakan dengan UUID draft. Daftar draft menampilkan nilai yang tersedia dan waktu simpan terakhir.

### FR13 Simpan final

Simpan memvalidasi field wajib, keabsahan relasi, serta bentuk payload. Untuk draft baru, server membuat tepat satu progress Open dan menyimpan seluruh anak yang berisi data. Untuk edit, server memperbarui progress Open yang dituju. Sesudah berhasil, draft ditandai applied dan tidak dihitung sebagai draft aktif. Kegagalan apa pun mempertahankan draft untuk diperbaiki.

Gunakan draft_id sebagai identitas pengiriman yang stabil dan simpan applied_progress_id. Pengiriman ulang draft yang telah diterapkan mengembalikan progress yang sama. Nilai wajib final tidak digunakan untuk menolak autosave draft parsial. Tidak menyimpan hanya sebagian problem ketika penyimpanan corrective action gagal.

### FR14 Urutan simpan dan konflik

Simpan draft membawa draft_revision dan nomor urut permintaan. Server tidak boleh menimpa versi baru dengan respons/permintaan autosave yang lebih lama. UI mengabaikan konfirmasi lama yang tidak cocok dengan revisi terkini. Saat Simpan final berjalan, tahan antrean autosave dan tandai draft diterapkan secara atomik agar permintaan terlambat tidak menghidupkan draft kembali.

Dua tab dengan basis revisi sama tidak boleh saling menimpa diam-diam. Jika versi progress berubah melalui tab lain atau Close/Reopen, tampilkan konflik; pertahankan draft dan minta pengguna membuka data terbaru sebelum menerapkan perubahan. Tidak melakukan merge otomatis atau last write wins untuk data bertingkat.

### FR15 Pemulihan dan pembuangan draft

Draft yang telah dikonfirmasi server dapat dilanjutkan setelah reload atau login kembali oleh admin. File yang hanya dipilih pada perangkat dan belum selesai diunggah mungkin perlu dipilih ulang; UI menyatakan kondisinya. Buang Draft menghapus draft aktif serta staging yang hanya dimiliki draft tersebut, setelah konfirmasi. Jangan menghapus file yang sudah direferensikan progress tersimpan.

<!-- pagebreak -->

## 11 Status dan dashboard

### FR16 Close dan Reopen

Close mengubah Open menjadi Closed; Reopen mengubah Closed menjadi Open. Kedua operasi hanya dipicu tindakan admin dan tidak diturunkan dari field lain. Operasi status memeriksa versi terbaru tetapi tidak menjalankan validasi kelengkapan action/feedback. Closed tidak dapat diubah melalui endpoint edit atau autosave perubahan baru tanpa Reopen; draft lama tetap bisa dibaca.

Reopen mempertahankan seluruh data. Close hanya berlaku pada progress yang dituju, tidak menutup project atau progress lain. Data draft yang belum diterapkan tidak hilang saat Close. Tombol Close pada editor menjelaskan bahwa yang ditutup adalah versi tersimpan; validasi draft tidak menjadi syarat Close.

### FR17 Ringkasan dashboard

Tampilkan tiga angka: Progress Open tersimpan, Draft aktif, dan Aktivitas bulan berjalan. Open dihitung berdasarkan status progress. Draft menghitung draft baru dan draft perubahan aktif yang belum applied. Aktivitas bulan berjalan menghitung progress tersimpan berdasarkan tanggal aktivitas dalam bulan kalender Asia/Jakarta, termasuk Open dan Closed, tanpa draft. Progress dengan tanggal legacy kosong tidak masuk hitungan bulan.

Angka ringkasan tidak dikalikan jumlah problem/action/feedback. Label periodenya terlihat. Secara default ringkasan bersifat global; filter pada daftar di bawahnya tidak mengubah ringkasan, dan UI memberi label Ringkasan keseluruhan agar konteks jelas.

### FR18 Daftar kerja dan filter

Default dashboard menampilkan progress Open dengan tanggal aktivitas terlama lebih dahulu, kemudian ID sebagai pengurut stabil. Tanggal kosong hasil migrasi ditempatkan setelah tanggal valid dan diberi label Tanggal belum tersedia. Tersedia pilihan urutan terbaru. Kolom: tanggal, JO, Part No/Name, Activity, PIC Progress, ringkasan Next Action, dan akses detail.

Pencarian mencakup JO, Part No, Part Name; filter customer, Activity, periode, Open/Closed/Semua, dan PIC. Filter PIC default mencari PIC Progress. Bila pengguna memilih peran Corrective Action, Next Action, atau Feedback, pencarian memakai peran tersebut dan mengembalikan progress unik. Nama dicari dari teks bebas tanpa memerlukan master PIC.

Hasil dipaginasi; baseline 25 baris per halaman. Seluruh problem, corrective action, feedback, serta foto dibuka pada detail agar daftar tetap terbaca. Tidak mengartikan actual date kosong sebagai overdue. Tombol Tambah Progress dan Lanjutkan Draft selalu mudah ditemukan.

<!-- pagebreak -->

## 12 Lampiran dan pengelolaan file

### FR19 Foto dan dokumen baru

Foto baru mendukung JPG/JPEG, PNG, dan WebP dengan validasi isi MIME dan ekstensi yang sesuai. Satu problem dapat memiliki beberapa foto dengan caption masing-masing. Tampilkan thumbnail, pembesaran, status unggah, dan error per file. Berkas gagal tidak ditampilkan sebagai unggahan berhasil.

Dokumen baru pada progress dan feedback menggunakan daftar lampiran, tidak dibatasi slot Doc1 sampai Doc5. Kemampuan ini tetap tersedia meskipun byte dokumen lama tidak diimpor. Daftar format dokumen dan batas ukuran foto/dokumen ditetapkan sebagai konfigurasi sebelum unggahan operasional diaktifkan; draft/prototype dapat menggunakan file fiktif untuk review alur.

### FR20 Kepemilikan file

Byte file disimpan pada storage privat; database menyimpan storage key, nama asli, MIME, ukuran, checksum, caption, jenis, pemilik, serta state staging/attached. Nama asli hanya untuk tampilan, bukan sebagai path yang dipercaya. Unduh dan thumbnail harus memeriksa autentikasi serta kepemilikan. File tidak dibuka melalui URL publik yang dapat ditebak.

Satu lampiran mempunyai satu pemilik: problem, progress, feedback, atau draft staging. Saat finalisasi, staging hanya boleh dipindahkan ke pemilik dalam draft yang sama. Database dan filesystem tidak berbagi transaksi penuh: pindahkan file dengan urutan aman, simpan referensi hanya untuk file yang tersedia, dan bersihkan orphan melalui rekonsiliasi terbatas. Kegagalan finalisasi tidak boleh menghilangkan sumber file.

### FR21 Metadata dokumen lama

Impor nama file dan keterangan Doc1 sampai Doc5 serta metadata Feedback Customer Doc yang tersedia ke arsip metadata. Simpan nama kolom asal agar pasangan keterangan dapat ditelusuri. Tampilkan label File lama tidak dimigrasikan. Tidak membuat tombol unduh atau link ke path komputer asal.

### Koreksi dan retensi

Caption serta keterangan dapat diperbaiki melalui edit. Pembatasan hapus record berlaku pula pada lampiran tersimpan. Cleanup otomatis hanya untuk staging yang tidak direferensikan draft aktif atau record final, dengan masa retensi yang disetujui. Cadangan harus mencakup database dan byte file; backup database saja tidak memulihkan galeri foto.

<!-- pagebreak -->

## 13 Arsitektur dan baseline teknologi

Aplikasi merupakan satu proyek Laravel dengan Blade dan Livewire. PostgreSQL menjadi database operasional. Tailwind mengatur presentasi; Alpine boleh digunakan untuk interaksi lokal seperti dialog/tab yang tidak memerlukan request server. Tidak memerlukan frontend React/Vue, aplikasi API terpisah, layanan AI runtime, microservices, atau koneksi langsung browser ke database.

| Komponen | Baseline implementasi |
| --- | --- |
| Runtime backend | PHP 8.4 dan Laravel 13; kunci patch yang kompatibel pada composer.lock saat bootstrap. |
| UI | Blade, Livewire 4, Tailwind CSS 4; gunakan dokumentasi versi yang sama dengan dependency terpasang. |
| Database | PostgreSQL 18 sebagai baseline; patch ditetapkan saat provisioning. |
| Build | Composer dan toolchain Node/Vite yang kompatibel; simpan lockfile, tanpa CDN runtime untuk CSS/JS inti. |
| Login | Session authentication Laravel untuk satu akun admin; registrasi publik dimatikan. |
| Storage | Laravel filesystem disk privat; pemilihan disk server atau object storage mengikuti operasi. |
| Testing | PHPUnit melalui artisan test; browser test terfokus untuk autosave dan alur utama. |
| Deployment | Ditentukan pada Fase 07 bersama pemilik server; tidak mengunci provider hosting pada PRD. |

Laravel 13 mensyaratkan PHP minimal 8.3 menurut dokumentasi resminya. PHP 8.4 pada tabel adalah pilihan baseline, bukan klaim versi terbaru. Versi minor/patch dan kompatibilitas paket diperiksa ketika bootstrap; jangan mengganti major framework di tengah fase tanpa mencatat dampaknya.

### Pemisahan kode

Livewire component mengelola interaksi dan state form; kelas Form/DTO memetakan payload; service aplikasi mengelola Simpan, Close/Reopen, draft, dan migrasi. Model Eloquent mewakili relasi; policy/otorisasi memeriksa akses. Hindari memasukkan seluruh proses upload, impor, dan transaksi nested ke satu component besar.

Gunakan komponen reusable untuk field, error, status simpan, daftar anak, galeri, dan tabel. Path yang disarankan pada task list merupakan kontrak organisasi awal, bukan klaim bahwa repository tersebut sudah dibuat. Adapter prototype terpisah dari service PostgreSQL operasional.

<!-- pagebreak -->

## 14 Model data dan hubungan

| Entitas | Hubungan dan atribut teknis utama |
| --- | --- |
| projects | ID internal; JO No unik; Part No unik; field master yang dipertahankan; created_at/updated_at. |
| progresses | project_id; Activity; tanggal dan rincian kegiatan; status Open/Closed; revision untuk kontrol konflik. |
| problems | progress_id; uraian; urutan; ID stabil. |
| corrective_actions | problem_id; uraian; PIC teks; actual_date nullable; urutan. |
| next_actions | progress_id; uraian; PIC teks; actual_date nullable; urutan. |
| feedbacks | progress_id; tanggal nullable; isi; PIC teks; urutan. |
| attachments | Pemilik tunggal; metadata file; storage key privat; caption; status staging/attached. |
| progress_drafts | UUID; admin_id; progress_id nullable; base_revision; draft_revision; payload JSON; active/applied; applied_progress_id. |
| legacy_document_metadata | progress_id atau feedback_id; kolom sumber; nama; keterangan; tidak mempunyai file aktif. |
| import_batches dan import_records | Identitas sumber/batch; tabel dan ID Access; raw row; target ID; hasil dan exception. Khusus alat migrasi. |
| users | Satu akun admin; kredensial hash; session sesuai mekanisme Laravel. |

Project memiliki banyak progress. Progress memiliki banyak problem, Next Action, dan feedback. Problem memiliki banyak corrective action serta foto. Tidak ada hubungan wajib antara Next Action dan problem. Lampiran tidak menggunakan banyak foreign key kosong tanpa aturan; bila memakai owner_type/owner_id, validasi owner wajib pada server dan konsistensinya diuji.

### Ketentuan tipe

JO No, Part No, No Doc dan nama disimpan sebagai teks. Nilai ukuran/kuantitas/DH disimpan sebagai raw text supaya bentuk input dipertahankan. Tanggal bisnis berupa DATE nullable, tanpa konversi zona waktu. created_at/updated_at adalah timestamp teknis dan ditampilkan dalam zona Asia/Jakarta. Kontrol Close menyimpan status, bukan menebak status dari closed_at atau actual date.

Draft menyimpan UUID lokal untuk baris yang belum mempunyai ID database. Simpan final memetakan UUID ke ID tanpa membuat entri ganda. Foreign key melindungi anak dari induk yang tidak ada. Unik JO dan Part No ditegakkan di database serta divalidasi pada form.

Minimum field berlaku bagi input final baru. Staging migrasi mempertahankan data sumber yang kurang lengkap untuk ditangani sesuai bagian 19. Jangan mengubah nilai kosong menjadi tanggal/nama buatan agar lolos constraint.

<!-- pagebreak -->

## 15 Pemetaan seluruh kolom Project Master

Seluruh 25 kolom sumber tetap tercatat dalam pemetaan, termasuk dua kolom yang sengaja tidak menjadi field aplikasi. Nama target berikut menjadi acuan implementasi dan impor.

| Kolom Access | Target | Ketentuan |
| --- | --- | --- |
| ID | import_records.source_id | Legacy ID dipertahankan; bukan ID UI. |
| JO No | projects.jo_no | Teks, wajib dan unik untuk data baru. |
| Project User | Tidak menjadi field aplikasi | Nilai asal hanya tetap pada arsip sumber/staging. |
| Date Receive | projects.date_receive | DATE nullable. |
| Part No | projects.part_no | Teks, wajib dan unik. |
| Part Name | projects.part_name | Teks, wajib untuk data baru. |
| Customer | projects.customer | Teks opsional. |
| Model | projects.model | Teks opsional. |
| No Doc | projects.no_doc | Teks opsional; bukan file. |
| Material | projects.material | Material rencana. |
| Spec | projects.spec | Teks spesifikasi. |
| Material Thickness | projects.material_thickness_raw | Teks angka sesuai input. |
| Material Width | projects.material_width_raw | Teks angka sesuai input. |
| Process | projects.process | Teks, terpisah dari Process Trial. |
| T0 Target | projects.t0_target | Target pengiriman sampel pertama; manual. |
| T0 Actual | projects.t0_actual | Aktual pengiriman sampel pertama; manual. |
| Plan Start | projects.plan_start | DATE nullable, manual. |
| Plan Finish | projects.plan_finish | DATE nullable, manual. |
| Actual Start | projects.actual_start | DATE nullable, manual. |
| Actual Finish | projects.actual_finish | DATE nullable, manual. |
| PP1 | projects.event_1 | Tanggal Event 1. |
| PP2 | projects.event_2 | Tanggal Event 2. |
| MP | projects.event_3 | Tanggal Event 3. |
| Remark Project list | projects.remark | Teks panjang. |
| Status Project | Tidak menjadi field aplikasi | Tidak dipetakan menjadi status progress. |

Apabila Access menyimpan nilai numeric yang tidak lagi memiliki informasi penulisan awal, ekspor nilai yang dapat dibaca beserta tipe/format sumber. Jangan mengklaim separator asli berhasil dipulihkan bila sumbernya tidak menyimpan bentuk teks tersebut.

<!-- pagebreak -->

## 16 Pemetaan Progress kegiatan dan tindakan

Bagian ini memetakan 24 kolom pertama dari 40 kolom Progress. Relasi project ditentukan melalui sumber JO yang tervalidasi, dengan pemeriksaan kesesuaian Part No.

| Kolom Access | Target | Ketentuan |
| --- | --- | --- |
| ID | import_records.source_id | Identitas sumber untuk impor ulang. |
| Part No | projects.part_no melalui project_id | Cocokkan; konflik masuk exception. |
| JO No | progresses.project_id | Hubungkan lewat master yang sah. |
| Date | progresses.activity_date | Tanggal aktivitas, bukan waktu input. |
| Activity | progresses.activity | Lima pilihan tetap. Nilai lain direview. |
| Activity Description | progresses.description | Teks panjang. |
| PIC Progress | progresses.pic_progress | Teks bebas. |
| PIC Customer | progresses.pic_customer | Teks bebas. |
| Process Trial | progresses.process_trial | Teks terpisah dari Process master. |
| Mesin | progresses.machine | Teks opsional. |
| DH | progresses.die_high_raw | Die High, penulisan angka dipertahankan. |
| Material Actual trial | progresses.material_actual_trial | Teks material aktual. |
| Qty Material | progresses.qty_material_raw | Raw text. |
| Qty Process | progresses.qty_process_raw | Raw text. |
| Problem | problems.description | Satu entri awal dari isi sumber, tanpa pemecahan otomatis. |
| Foto Problem | attachments pada problem/progress | Ekstrak foto; konteks yang pasti dipertahankan. |
| Corrective Action | corrective_actions.description | Entri pada problem sumber. |
| Next Action | next_actions.description | Entri terpisah pada progress. |
| PIC Corrective Action | corrective_actions.pic | Milik entri corrective action tersebut. |
| Date Corrective Action | corrective_actions.actual_date | DATE nullable, hanya aktual. |
| PIC Next Action | next_actions.pic | Teks bebas per entri. |
| Date Next Action | next_actions.actual_date | DATE nullable, hanya aktual. |
| Qty Sample | progresses.qty_sample_raw | Raw text. |
| Ket Event | progresses.event_note | Keterangan; terpisah dari Event 1 sampai 3. |

Kumpulan corrective action baru tidak lagi berbentuk satu field datar. Untuk data lama yang menggabungkan banyak tindakan dalam teks, pertahankan teks utuh pada satu entri awal sampai admin memutuskan pemecahannya.

<!-- pagebreak -->

## 17 Pemetaan feedback dokumen dan status

| Kolom Access | Target | Ketentuan |
| --- | --- | --- |
| Date Feedback Customer | feedbacks.feedback_date | DATE nullable. |
| Feedback Customer | feedbacks.content | Entri awal pada daftar feedback. |
| Feedback Customer Doc | legacy_document_metadata | Metadata lama saja; file tidak diimpor. |
| PIC Feedback Customer | feedbacks.pic | PIC pada entri feedback, bukan PIC Customer progress. |
| Doc1 | legacy_document_metadata | source_field Doc1 dan nama file bila tersedia. |
| Doc2 | legacy_document_metadata | source_field Doc2. |
| Doc3 | legacy_document_metadata | source_field Doc3. |
| Doc4 | legacy_document_metadata | source_field Doc4. |
| Doc5 | legacy_document_metadata | source_field Doc5. |
| Ket Doc1 | legacy_document_metadata.description | Pasangan Doc1; tetap disimpan jika nama kosong. |
| Ket Doc2 | legacy_document_metadata.description | Pasangan Doc2. |
| Ket Doc3 | legacy_document_metadata.description | Pasangan Doc3. |
| Ket Doc4 | legacy_document_metadata.description | Pasangan Doc4. |
| Ket Doc5 | legacy_document_metadata.description | Pasangan Doc5. |
| Progress Close | progresses.status | Nilai sumber divalidasi menjadi Open/Closed. |
| Remark Progress | progresses.remark | Teks panjang. |

Total pemetaan adalah 25 kolom master dan 40 kolom progress. Lampiran baru disimpan sebagai record berulang, sehingga keberadaan Doc1 sampai Doc5 di sumber tidak membatasi jumlah dokumen baru.

### Kontrak payload draft

Payload berisi schema_version, project_id, data kegiatan, problems[], next_actions[], feedbacks[], dan attachment_refs[]. Setiap problem memiliki client_id, ID tersimpan bila ada, description, corrective_actions[], serta photo_refs[]. Setiap corrective action memiliki client_id, description, pic, actual_date. Next Action memiliki bentuk yang sama tanpa problem_id. Feedback memuat client_id, feedback_date, content, pic, document_refs[].

Identitas master yang ditampilkan boleh berada dalam cache UI, tetapi diabaikan sebagai sumber perubahan master pada Simpan progress. Nama file/path dari client tidak dipercaya. Server mengecek semua referensi lampiran dan anak. Payload yang tidak memuat sebagian daftar tersimpan bukan instruksi untuk menghapus daftar itu.

schema_version menjadi versi struktur draft, bukan versi perubahan bisnis. Bila versi draft lama tidak kompatibel, tampilkan kebutuhan pemulihan dan pertahankan payload; jangan membuang isian agar migrasi schema terlihat berhasil.

<!-- pagebreak -->

## 18 Rencana migrasi seluruh histori

### FR22 Inventarisasi dan ekstraksi

Gunakan salinan Access yang dapat dibaca tanpa mengubah sumber. Hitung baris master/progress sebenarnya dan identifikasi tipe field foto. Foto di dalam Access dapat memerlukan metode ekstraksi berbeda sesuai tipe penyimpanannya; uji beberapa foto sampai byte hasil benar-benar terbuka. Ekspor teks/tanggal dan foto disertai manifest berisi source_key, tabel, source_id, nama kolom, nama file, MIME bila diketahui, ukuran, dan checksum.

Foto yang dibungkus OLE atau data lain tidak dianggap berhasil hanya karena file berhasil ditulis. Pastikan decoder gambar dapat membacanya dan hasilnya sesuai contoh sumber. Bila hanya preview yang tersedia, catat keterbatasannya dan minta keputusan sebelum menggantikan foto asli.

### FR23 Staging dan dry run

Impor awal ke ruang uji. Pertahankan raw row dan hasil pemetaan per sumber. Validasi keunikan JO serta Part No, hubungan progress ke master, Activity, tanggal, status Close, dan kepemilikan foto. Tidak menggabungkan record berdasarkan kemiripan nama. Dua kolom master yang dihapus tetap terdapat pada arsip sumber/staging, tanpa muncul kembali di produk.

### FR24 Idempotensi

Gunakan identitas sumber stabil dan pasangan source_table serta source_id sebagai kunci pemetaan; UUID batch bukan satu-satunya kunci deduplikasi. Foto menggunakan pemetaan sumber dan checksum sebagai pemeriksaan. Impor ulang sumber yang sama tidak menambah project, progress, anak, metadata dokumen, atau foto yang sama. Jika sumber berubah atau target sudah dikoreksi admin, laporkan konflik; jangan menimpa data operasional diam-diam.

### FR25 Rekonsiliasi dan perpindahan

Untuk setiap baris sumber, hasil harus berupa imported atau exception yang dapat diperiksa. Ringkasan teknis memuat jumlah sumber, hasil, duplikat, exception, dan keberhasilan foto. Ini keluaran alat migrasi, bukan fitur laporan bisnis yang dihapus dari scope. Uji contoh kelima Activity, tanggal kosong, Closed/Open, metadata dokumen, dan banyak anak/foto.

Tentukan waktu penghentian input Access dan ekspor perubahan terakhir sebelum penggunaan operasional. Access dan arsip ekspor dipertahankan sesuai kebijakan perusahaan. Cutover dilakukan setelah rekonsiliasi diterima, cadangan dibuat, dan pemulihan diuji. Jangan mencampur dataset prototype ke migrasi produksi.

<!-- pagebreak -->

## 19 Perlakuan data lama yang tidak lengkap

| Kondisi | Perlakuan wajib |
| --- | --- |
| JO atau Part No ganda | Tahan record terkait dalam exception. Keputusan pemetaan dibuat berdasarkan sumber, tanpa merge otomatis. |
| JO progress tidak ditemukan | Pertahankan raw row; jangan membuat master palsu atau menghubungkan berdasarkan nama part yang mirip. |
| Part No progress berbeda dari master JO | Catat konflik. Gunakan master terkini hanya setelah pemetaan tersebut diverifikasi. |
| Tanggal aktivitas kosong | Boleh dipertahankan sebagai legacy null dengan penanda; tidak dimasukkan ke hitungan bulan. Data baru tetap wajib tanggal. |
| Activity atau Progress Close tidak dikenali | Exception sampai pemetaan nilai sumber disepakati. Jangan menebak Closed atau memilih Others secara otomatis. |
| Nama part kosong pada master lama | Pertahankan sumber dan tandai legacy incomplete. Tampilkan Part Name belum tersedia; jangan menyimpan label itu sebagai nama part. |
| Corrective action ada dan problem kosong | Buat wadah problem legacy dengan uraian null; tindakan asli tetap tersimpan. |
| Satu teks memuat beberapa problem/action | Simpan utuh sebagai satu entri awal; pemecahan hanya berdasarkan review admin. |
| PIC/tanggal ada tanpa uraian action | Tetap bawa nilai yang ada dan relasinya. Jangan membuang baris karena uraian kosong. |
| Foto tidak jelas problem pemiliknya | Tautkan ke progress sumber dan label belum dipetakan ke problem. |
| Foto gagal diekstrak/rusak | Catat exception file. Record teks dapat diimpor, tetapi keberhasilan foto tidak diklaim. |
| Metadata dokumen tanpa file | Tampilkan nama/keterangan arsip, tanpa download aktif. |

Pengecualian legacy terhadap field wajib dibatasi pada jalur impor dan record berpenanda teknis. Form untuk record legacy menjelaskan data yang belum ada; bila admin menekan Simpan perubahan, tampilkan field minimum yang harus dilengkapi. Close tetap dapat dilakukan pada versi tersimpan tanpa menjadikan kelengkapan legacy syarat tambahan.

Seluruh histori berarti seluruh record sumber dipertanggungjawabkan. Exception belum terselesaikan tetap harus ditampilkan dalam rekonsiliasi; migrasi tidak dinyatakan selesai hanya karena sebagian record berhasil masuk.

<!-- pagebreak -->

## 20 Kualitas dan operasi

| ID | Kriteria |
| --- | --- |
| NF01 | Semua halaman data, perubahan, file, draft, dan operasi status memerlukan admin terautentikasi pada aplikasi operasional. |
| NF02 | Password di-hash; registrasi publik nonaktif; input divalidasi di server; output teks di-escape; request perubahan dilindungi mekanisme Laravel. |
| NF03 | Simpan nested bersifat atomik dan idempoten. Dua tab atau autosave terlambat tidak boleh menimpa data lebih baru. |
| NF04 | Field DATE tidak bergeser karena zona waktu. Timestamp teknis ditampilkan konsisten dalam Asia/Jakarta. |
| NF05 | File privat hanya dapat diakses melalui otorisasi; file invalid/gagal tidak menjadi attachment berhasil. |
| NF06 | Form dapat dipakai pada lebar 360 px dan desktop, melalui keyboard serta sentuhan; UI01 sampai UI12 diperiksa. |
| NF07 | Baseline performa: daftar/filter pada data sekitar 250 progress terbuka dalam 3 detik pada 95 persen percobaan di lingkungan uji yang dicatat. Ini target uji, bukan hasil pengukuran. |
| NF08 | Pagination dan query relasi mencegah daftar memuat seluruh file/foto; thumbnail dipakai pada daftar galeri. |
| NF09 | Backup database dan storage diuji melalui restore pada lingkungan terpisah sebelum go live. Jadwal, retensi, dan pemiliknya ditetapkan pada Fase 07. |
| NF10 | Debug publik mati saat produksi. Log error teknis tidak menampilkan password, isi seluruh draft, atau byte lampiran. |

Penyimpanan lokal perangkat bukan sumber data operasional utama. Aplikasi tidak menjanjikan mode offline penuh. Isian yang belum berhasil dikirim server harus tetap terlihat sebagai belum tersimpan. Sesi login habis tidak boleh ditampilkan sebagai simpan berhasil.

### Kesiapan rilis

Semua skenario kritis pada bagian 21 dan 22 harus lolos, terutama relasi tindakan, autosave, keunikan JO/part, Close manual, serta migrasi. Masalah yang menyebabkan data salah, tertukar, hilang, atau terbuka tanpa login harus selesai sebelum rilis. Perbaikan visual minor hanya dapat ditunda setelah dicatat dampaknya dan diterima pemilik kebutuhan.

Pengembangan, staging, dan produksi memakai konfigurasi serta database terpisah. Perintah reset atau penghapusan tabel tidak digunakan untuk memperbaiki produksi. Deploy dan cutover membutuhkan tujuan yang jelas serta persetujuan pihak yang berwenang atas lingkungan tersebut.

<!-- pagebreak -->

## 21 Kriteria penerimaan produk

| Uji | Skenario dan hasil yang diharapkan | Acuan |
| --- | --- | --- |
| AT01 | JO yang sudah ada ditolak pada project baru; tidak muncul project kedua. | FR01 |
| AT02 | Part No yang sudah dipakai JO lain ditolak; nol di depan tetap utuh. | FR01 |
| AT03 | Koreksi Part Name/Customer/Model/No Doc master terlihat pada progress lama dan draft yang dibuka. | FR02 |
| AT04 | T0 Target/Actual, jadwal, dan Event 1 sampai 3 tersimpan manual; Sent Sample tidak mengubahnya otomatis. | FR03 |
| AT05 | Kelima Activity memiliki field dan bagian sama; perubahan Activity tidak menghilangkan isian. | FR04 |
| AT06 | PIC nama bebas tersimpan tanpa master; 1,5 dan 1.5 kembali persis seperti input. | FR05 |
| AT07 | Dua problem masing-masing memiliki dua corrective action; PIC/tanggal tidak tertukar setelah edit. | FR07 FR08 |
| AT08 | Tiga Next Action tersimpan tanpa problem dan tanpa hubungan ke corrective action. | FR09 |
| AT09 | Dua feedback dengan PIC, tanggal, dan dokumen berbeda tetap merupakan dua entri. | FR10 |
| AT10 | Actual date kosong boleh; mengisinya tidak menutup progress atau menambah target date. | FR08 FR09 FR16 |
| AT11 | ID anak milik progress/problem lain ditolak dan data sumber tetap utuh. | FR11 |
| AT12 | Draft tanpa JO atau Activity berhasil autosave, tetapi Simpan final menjelaskan field yang kurang. | FR12 FR13 |
| AT13 | Reload sesudah konfirmasi autosave memulihkan seluruh nested entry dan referensi upload selesai. | FR12 FR15 |
| AT14 | Autosave perubahan tidak mengganti versi progress pada dashboard sebelum Simpan final. | FR13 |
| AT15 | Simpan dua kali dari draft yang sama menghasilkan satu progress dan satu set anak. | FR13 |
| AT16 | Gagal pada salah satu penyimpanan anak tidak meninggalkan progress setengah lengkap; draft tetap ada. | FR11 FR13 |
| AT17 | Permintaan autosave lama tiba terakhir; isi paling baru tetap tersimpan. | FR14 |
| AT18 | Dua tab mengedit basis revisi sama; konflik terlihat dan tidak ada overwrite diam-diam. | FR14 |
| AT19 | Buang Draft tidak menghapus progress atau file attached; staging aktif draft lain tetap ada. | FR15 |
| AT20 | Close berhasil meskipun action, PIC, actual date, atau feedback kosong. Status record lain tetap. | FR16 |

Data contoh uji harus fiktif dan mempunyai identitas yang mudah dibedakan. Pengujian pada kasus negatif harus memeriksa bahwa data lama tetap utuh, bukan hanya bahwa error muncul.

<!-- pagebreak -->

## 22 Kriteria penerimaan lanjutan

| Uji | Skenario dan hasil yang diharapkan | Acuan |
| --- | --- | --- |
| AT21 | Closed menolak edit langsung; Reopen mempertahankan isi dan mengizinkan edit. | FR16 |
| AT22 | Close versi tersimpan ketika draft belum valid tetap dimungkinkan; draft tidak hilang. | FR16 |
| AT23 | Ringkasan Open/Draft/bulan menghitung record unik; jumlah anak tidak menggandakan angka. | FR17 |
| AT24 | Daftar default Open terlama; filter PIC sesuai peran dan clear filter bekerja. | FR18 |
| AT25 | Pencarian yang kosong menampilkan pesan tepat; kembali dari detail mempertahankan filter. | FR18 UI01 |
| AT26 | Beberapa foto pada problem yang berbeda tetap pada pemilik benar, dengan caption dan preview. | FR19 FR20 |
| AT27 | Foto invalid atau terlalu besar ditolak jelas; login tidak sah tidak dapat mengunduh lampiran. | FR19 FR20 NF01 |
| AT28 | Enam dokumen baru dapat menjadi enam entri; metadata dokumen lama tidak mempunyai download aktif. | FR19 FR21 |
| AT29 | Semua baris master/progress sumber mempunyai hasil imported atau exception yang jelas. | FR22 FR25 |
| AT30 | Foto dari Access berhasil dibuka dan dicocokkan sampel; file dokumen lama tidak ikut. | FR22 FR21 |
| AT31 | Impor ulang sumber sama tidak menggandakan record, anak, metadata, atau foto. | FR24 |
| AT32 | Data sumber berubah atau target sudah diedit: konflik dicatat, tidak ditimpa otomatis. | FR24 |
| AT33 | Tanggal legacy kosong tetap kosong; nilai status tak dikenal ditahan; tidak ada fakta buatan. | Bagian 19 |
| AT34 | JO/Part konflik dan foto gagal tetap tercatat; ringkasan tidak menyebut seluruh impor sukses. | Bagian 19 |
| AT35 | Form desktop/HP dan seluruh tindakan utama bisa dipakai dengan keyboard/sentuhan; tidak ada clipping. | UI01 sampai UI12 NF06 |
| AT36 | Error pada bagian form lain mudah ditemukan; fokus dan isian tidak hilang saat validasi. | Bagian 7 |
| AT37 | Koneksi putus/sesi habis menampilkan belum tersimpan; retry tidak membuat duplikat. | FR12 FR13 NF03 |
| AT38 | Restore database beserta storage memulihkan contoh progress dan foto yang dapat dibuka. | NF09 |
| AT39 | Menu Status Project, Project User, audit trail, report/export, dan link antaraktivitas tidak muncul. | Bagian 3 |
| AT40 | Daftar/filter memenuhi target performa pada dataset dan lingkungan uji yang dicatat. | NF07 NF08 |

Skenario browser diprioritaskan pada nesting, autosave, status, upload, dan navigasi yang berpotensi menghilangkan isian. Aturan database/migrasi diverifikasi pada PostgreSQL dan file uji terpisah. Pemeriksaan visual serta sesi review pengguna tidak diganti seluruhnya oleh screenshot otomatis.

<!-- pagebreak -->

## 23 Prototype fase kerja dan keputusan operasional

| Fase | Hasil dan syarat lanjut |
| --- | --- |
| 01 Prototype dan validasi | Tampilan interaktif seluruh alur, fixture fiktif, keadaan error/loading/draft; feedback pengguna diselesaikan dan alur diterima. |
| 02 Fondasi data dan akses | Repository, versi, schema PostgreSQL, model/relasi, auth, service contract, serta fixture test siap. |
| 03 Project dan progress | Master serta progress dasar terhubung database dengan validasi dan identitas terkini. |
| 04 Problem tindakan dan file | Semua daftar bertingkat, feedback, lampiran privat, dan metadata arsip berjalan. |
| 05 Draft status dan monitoring | Autosave andal, Simpan atomik/idempoten, Close/Reopen, serta dashboard lengkap. |
| 06 Migrasi Access | Ekstraksi foto, dry run, rekonsiliasi, dan rencana cutover teruji. |
| 07 UAT dan operasi | Uji menyeluruh, review UX, restore, deployment, pilot, serta serah terima. |

### Skenario review prototype

Gunakan setidaknya tiga JO berbeda dan delapan progress fiktif yang mencakup lima Activity, Open/Closed, tanggal lama, draft parsial, serta teks panjang. Satu progress mempunyai dua problem dengan dua corrective action masing-masing, tiga Next Action, dua feedback, dan beberapa foto. Tampilkan contoh 1,5 serta 1.5 untuk memeriksa input raw.

Minta pengguna menambah progress, mengganti Activity, menambah problem dan dua tindakan, melanjutkan draft setelah reload, memperbaiki error, mencari progress lama, Close catatan yang action-nya kosong, dan Reopen. Catat tempat pengguna ragu, jumlah bantuan yang dibutuhkan, dan apakah istilah serta struktur form sesuai pekerjaan. Jangan melanjutkan fondasi operasional sebelum hasil review prototype diterima.

### Parameter yang ditetapkan saat pelaksanaan

| ID | Parameter dan pemilik | Batas penetapan |
| --- | --- | --- |
| OP01 | OS/server, jalur akses, domain dan HTTPS; pengembang dan IT. | Sebelum deployment staging/produksi. |
| OP02 | Akun admin awal dan pemulihan akses; pemilik aplikasi. | Sebelum penggunaan operasional. |
| OP03 | Allowlist dokumen baru, batas ukuran per file, kapasitas storage; pengembang dan pengguna. | Sebelum mengaktifkan unggahan operasional pada Fase 04. |
| OP04 | Retensi staging/draft tak aktif, backup, retensi cadangan dan target pemulihan; pengembang/IT. | Sebelum cleanup otomatis dan go live. |
| OP05 | Nilai aktual sumber, tipe field foto, konflik legacy, waktu cutover; pengembang dan pemilik data. | Saat Fase 06 sebelum impor produksi. |

Parameter ini tidak menjadi alasan menunda prototype. Jika keputusan baru mengubah perilaku produk atau PRD, kumpulkan pertanyaan sekaligus dan minta konfirmasi sebelum revisi.

<!-- pagebreak -->

## 24 Referensi dan pengendalian perubahan

Sumber kebutuhan adalah pembahasan dengan pengembang dan jawaban pengguna operasional hingga 13 Oktober 2026. Tabel sumber berisi 25 kolom Project Master dan 40 kolom Project Progress. Angka sekitar 250 progress serta lokasi foto di Access berasal dari keterangan pengguna dan harus diverifikasi pada migrasi.

### Referensi teknis resmi

Referensi diperiksa untuk memilih baseline dan menyusun task, bukan untuk menggantikan kebutuhan bisnis.

- Laravel frontend dan pendekatan Livewire: https://laravel.com/docs/13.x/frontend
- Laravel release dan persyaratan PHP: https://laravel.com/docs/13.x/releases
- Laravel starter kits dan autentikasi: https://laravel.com/docs/13.x/starter-kits
- Laravel file storage: https://laravel.com/docs/13.x/filesystem
- Livewire form dan validasi: https://livewire.laravel.com/docs/4.x/forms
- Livewire file uploads: https://livewire.laravel.com/docs/4.x/uploads
- Tailwind dengan Laravel dan Vite: https://tailwindcss.com/docs/installation/framework-guides/laravel/vite
- PostgreSQL constraints: https://www.postgresql.org/docs/18/ddl-constraints.html
- PostgreSQL dukungan versi: https://www.postgresql.org/support/versioning/

### Perubahan utama dari versi 1

Versi 2 menetapkan stack, menghapus dua field master dan fitur yang tidak diperlukan, mengunci hubungan JO/part satu-ke-satu, menggunakan identitas master terkini, serta menyamakan form seluruh Activity. Corrective action menjadi daftar tersendiri di bawah problem. Autosave, Reopen, dashboard, cakupan migrasi seluruh histori, dan metadata dokumen lama telah disepakati.

Versi ini menambahkan standar UI/UX, semantik draft terpisah dari data final, penanganan konflik simpan, baseline arsitektur, pemetaan data lengkap, 40 skenario penerimaan, serta tujuh fase pelaksanaan. Task fase disusun agar AI dapat mengerjakan satu bagian dengan batas jelas tanpa mengarang persyaratan tambahan.

### Aturan revisi berikutnya

Setiap usulan perubahan menyebut ID kebutuhan yang terpengaruh, alasan, perubahan UI/data, serta skenario penerimaan yang berubah. Pengembang meminta konfirmasi sebelum memperbarui PRD. Keputusan dari review prototype dicatat; desain yang belum disetujui tidak dianggap sebagai perubahan aturan bisnis.

Task list adalah rencana pekerjaan. Checkbox awal yang belum dicentang tidak berarti fitur sudah diterapkan atau diuji. Hasil implementasi dinilai melalui bukti, bukan hanya keberadaan file atau jawaban AI bahwa pekerjaan selesai.
