# UI Handoff — Progress New Model

**Baseline UI disetujui · 14 Oktober 2026 · Versi handoff 1.0**

| Identitas | Nilai |
| --- | --- |
| Referensi interaktif | https://progress-new-model-ui.quick-panda-6120.chatgpt.site |
| Versi Site | 6 |
| Commit source | `1ca17d6a58dfd59cf0c7ef4bb5bd58d90ea22002` |
| Persetujuan tampilan | Pesan pengguna “udah ok”, 14 Oktober 2026 pukul 10.25 WIB |
| Stack aplikasi tujuan | Laravel, PostgreSQL, Blade, Livewire, Tailwind |
| Source yang disertakan | HTML, CSS, JavaScript mandiri, identik byte demi byte dengan empat file UI versi tersebut |
| Status aplikasi Laravel pengguna | Dinyatakan selesai sampai P01-T09; repository Laravel belum diberikan kepada pembuat handoff |
| Cakupan dokumen | Reproduksi tampilan dan interaksi UI yang disetujui; bukan revisi PRD atau migrasi database |

## 1. Cara memakai paket ini

**Gunakan design code dan dokumen ini bersama-sama.** Untuk model dengan kemampuan lebih rendah, jangan meminta “buat UI seperti deskripsi ini”. Minta model memindahkan struktur HTML, kelas CSS, ikon, dan perilaku dari source yang disertakan ke aplikasi yang sudah ada.

Urutan acuan:

1. Koreksi eksplisit terbaru dari pengguna.
2. `reference/index.html`, `reference/styles.css`, `reference/app.js`, `reference/state.js` pada commit di atas: acuan konkret tampilan/interaksi.
3. Dokumen handoff ini: makna tiap bagian, batas adaptasi, dan cara memeriksa hasil.
4. PRD dan file fase yang sudah ada untuk kebutuhan di luar perubahan UI ini. Jangan mengedit PRD tanpa konfirmasi pengguna.

Jika uraian ukuran di dokumen berbeda dengan hasil CSS pada viewport tertentu, periksa cascade CSS final dan catat perbedaannya. Jangan memilih nilai baru berdasarkan selera. Jika menemukan bug pada referensi, pisahkan perbaikannya dari penyalinan desain; jangan diam-diam mengubah aturan bisnis.

### 1.1 Isi paket

| File/folder | Kegunaan |
| --- | --- |
| `README_MULAI_DI_SINI.md` | Cara membuka referensi dan memulai implementasi |
| `UI_HANDOFF_NEW_MODEL.md` | Spesifikasi layar, field, tampilan, perilaku, dan batas adaptasi |
| `PROMPT_IMPLEMENTASI.md` | Prompt awal, prompt satu task, dan format handoff sesi untuk AI |
| `TASK_IMPLEMENTASI_UI.md` | Urutan delapan task UI; bukan pengganti ID task fase pada PRD |
| `tasks/` | Satu file instruksi detail per task UI |
| `CHECKLIST_PARITAS_UI.md` | Checklist pembanding visual, alur, dan regresi |
| `PETA_SOURCE_UI.md` | Peta fungsi render, handler, binding, dan selector ke komponen tujuan |
| `reference/` | Source referensi utuh; pertahankan sebagai pembanding yang tidak diedit |
| `MANIFEST.json` | Versi, commit, ukuran, dan SHA-256 empat file referensi |

Buka referensi dan aplikasi Laravel pada **browser, zoom, ukuran viewport, dan data contoh yang sama**. Jangan menilai kesamaan dari dua jendela dengan lebar berbeda. Source identik memberi acuan pasti, tetapi implementasi Laravel tetap perlu dibandingkan di browser. Perbedaan font OS, kontrol tanggal native, dan browser dapat mengubah beberapa piksel.

### 1.2 Yang dipindahkan dan yang diadaptasi

| Bagian | Tindakan |
| --- | --- |
| Struktur halaman, urutan field, label, kelas, ikon, warna, ukuran, breakpoint | Salin sedekat mungkin dari referensi |
| Rendering template literal di `app.js` | Konversi ke Blade tanpa mengubah hierarki DOM yang dipakai CSS |
| State view dan event klik | Hubungkan ke route/action/komponen yang sudah ada |
| Binding input | Hubungkan ke state Livewire aplikasi yang sudah ada |
| Relasi problem, CA, Next Action, feedback | Pertahankan hubungan dan ID stabil |
| `localStorage`, fixture, simulasi file | Gunakan hanya pada referensi; bukan backend produksi |
| Query/model/service/auth/autosave server | Pertahankan implementasi repository dan sesuaikan di batas adapter |
| PRD, schema database, phase checklist | Tidak diubah oleh pekerjaan handoff ini |

## 2. Keputusan UI yang sudah final

- Fokus utama: admin mengisi satu pencatatan lengkap dengan sesedikit mungkin perpindahan layar dan klik persiapan.
- Form progress satu halaman berurutan. **Tidak ada tab, wizard, accordion tertutup, atau modal untuk membuka kelompok isian utama.**
- Satu problem dengan satu corrective action, satu Next Action, dan satu feedback kosong langsung tersedia.
- Tombol Tambah digunakan untuk entri kedua dan seterusnya. Problem baru langsung berisi satu baris corrective action.
- Problem, CA, dan Next Action dapat diisi pada pencatatan pertama. Feedback dapat ditambahkan kemudian; field-nya tetap tersedia sejak awal.
- Feedback hanya memakai judul **Feedback Customer**. Jangan menambahkan penjelasan kapan bagian tersebut diisi.
- Semua jenis Activity menggunakan form yang sama: `Trial`, `Sent Sample`, `Send Document`, `Audit`, `Others`.
- `PIC Progress` tidak ada. `PIC Customer`, PIC setiap CA, PIC setiap Next Action, dan PIC feedback tetap ada dan berupa teks bebas.
- Next Action terpisah dari problem. CA selalu berada di dalam problem terkait.
- Setiap tindakan memiliki **actual date**, tanpa target date. Tanggal boleh kosong. Mengisi tanggal tidak menutup progress.
- Close dilakukan manual oleh admin. Progress Closed wajib Reopen sebelum diedit.
- Autosave menyimpan draft; tombol Simpan menerapkan draft ke progress. Draft tidak dihitung sebagai Open/Closed.
- JO No dan Activity berupa dropdown. Satu JO berhubungan dengan satu Part No, dan satu Part No hanya memiliki satu JO.
- Part No, Part Name, Customer, Model, No Doc mengikuti Project Master terbaru; bukan isian bebas di editor progress.
- Model berupa kode teks, misalnya `K2SJ`, `HK09`, `RS8`. Model bukan tanggal.
- Field `Spec` tidak ditampilkan dalam form tambah/edit Project. Jangan menghapus data lama atau kolom database hanya karena perubahan form ini.
- Judul dashboard: **Aktivitas New Model**. Judul menu Progress: **Semua progress**.
- Area bawah sidebar kosong. Branding atas hanya teks `New Model` dan `PROGRESS TRACKER`; tanpa simbol/logo NM.

### 2.1 Elemen yang sudah dihapus — jangan dimunculkan kembali

| Lokasi | Elemen/teks yang dilarang kembali |
| --- | --- |
| Header kanan | `Prototype · data contoh`, termasuk titik penandanya |
| Sidebar atas | Kotak/logo `NM` |
| Sidebar bawah | Catatan prototype, keterangan data contoh, tombol reset data contoh, avatar AD, identitas Administrator/New Model Engineering |
| Dashboard | Judul `Tindak lanjut` |
| Di atas judul dashboard | `Pantau aktivitas new model` |
| Di atas judul Progress | `Riwayat kegiatan` |
| Di bawah judul dashboard | `Temukan kegiatan terbuka, catat perkembangan berikutnya.` |
| Di bawah judul Progress | `Seluruh kegiatan, problem, dan tindak lanjut dalam satu tempat.` |
| Di bawah judul Daftar progress | `Satu baris untuk satu kegiatan. Pilih progress Open untuk langsung melanjutkan pencatatan.` |
| Di bawah tabel | `Progress tetap Open sampai admin memilih Close.`, beserta ikon informasinya |
| Di atas judul Project Master | `Identitas & jadwal project` |
| Di bawah judul Project Master | `Satu JO untuk satu Part No. Identitas digunakan di seluruh progress.` |
| Di atas judul Draft | `Isian yang belum diterapkan` |
| Di bawah judul Draft | `Lanjutkan pekerjaan dari isian terakhir yang tersimpan.` |
| Footer form progress baru | `Draft otomatis saat mulai mengisi` |
| Footer form progress | `Simpan untuk menerapkan perubahan ke progress.` |
| Feedback | `Diisi setelah ada tanggapan customer` atau kalimat serupa |
| Form, daftar, detail, filter | Field/peran `PIC Progress` |
| Form Project | Input `Spec` |

Penghapusan teks pendamping judul pada daftar **tidak** berarti menghapus identitas JO, Part No, nama part, customer, atau model pada detail project/progress. Teks dinamis tersebut masih diperlukan. Caption ringkasan, label field, error, toast, dan empty state yang masih ada dalam source tetap dipertahankan.

CSS referensi masih mengandung beberapa selector lama seperti `.brand-mark`, `.sidebar-bottom`, `.prototype-label`, `.form-panel > .tabs`, dan `.pic-col`. Selector sisa **bukan** perintah untuk mengembalikan markup yang sudah dihapus. DOM dari fungsi render versi final menentukan elemen yang benar-benar tampil.

## 3. Fondasi visual yang harus dipertahankan

### 3.1 Token

| Token/elemen | Nilai |
| --- | --- |
| `--bg` | `#f5f6f8` |
| `--paper` | `#ffffff` |
| `--ink` | `#18212b` |
| `--muted` | `#5d6877` |
| `--line` | `#d8dee6` |
| `--line-soft` | `#e9edf2` |
| `--blue` | `#2459a6` |
| `--blue-deep` | `#18447f` |
| `--blue-wash` | `#edf3fb` |
| `--green` / `--green-wash` | `#24674b` / `#eef7f1` |
| `--amber` / `--amber-wash` | `#805611` / `#fff6e5` |
| `--red` | `#b03838` |
| Border input | `#cbd4df`; hover `#9facbf` |
| Focus ring umum | 3 px solid `#80a7df`, offset 3 px |
| Radius panel | 8 px |
| Radius tombol/input | 6 px |
| Radius dialog | 10 px |
| Font utama | `Inter, "Segoe UI", Arial, sans-serif` |
| Font kode JO/Model | `"SFMono-Regular", Consolas, monospace` |

Referensi tidak mengunduh font. Inter dipakai hanya jika tersedia pada perangkat, lalu fallback. Untuk pembandingan identik gunakan font yang benar-benar sama pada kedua tampilan. Jangan menambahkan Google Fonts, font dekoratif, gradient, gambar hero, ilustrasi, shadow pada semua panel, glass effect, badge baru, atau animasi dekoratif.

### 3.2 Ukuran umum desktop

Nilai berikut untuk desktop di antara breakpoint, kecuali jika disebut lain. Aturan akhir tetap berada di `reference/styles.css`.

| Selector/elemen | Nilai |
| --- | --- |
| `.sidebar` | Fixed; lebar dari `--side`; putih; border kanan 1 px; z-index 30 |
| `.brand` | Tinggi 91 px, padding horizontal 24 px |
| `.brand-name` | 15 px, weight 700 |
| `.brand-sub` | 10 px, letter-spacing 1.4 px |
| `.workspace` | Margin kiri sebesar `--side`; min-height 100vh |
| `.topbar` | Tinggi 65 px; padding horizontal 34 px; border bawah tipis |
| `.main` | Max-width 1510 px; margin auto; padding 32 px 34 px 40 px |
| `.page-head` | Flex, jarak 24 px, margin bawah 27 px |
| `h1` umum | 28 px, weight 650, line-height 1.25, letter-spacing -0.8 px |
| `.editor-head h1` | 26 px desktop; 24 px pada ≤600 px |
| `h2` umum | 17 px, weight 650 |
| `.entry-section-head h2` | 16 px desktop; 15 px pada ≤600 px |
| `.btn` | Min-height 38 px; padding 8 px 14 px; font final 13 px; border 1 px |
| `.btn.primary` | Background biru, teks putih; hover biru lebih gelap |
| `.icon-btn` | 32 × 32 px desktop; 36 × 36 px pada ≤600 px, kecuali override repeater |
| `.icon` | SVG viewBox 24; ukuran 18 px; stroke 1.7; tanpa fill; round cap/join |
| Input umum | Font 14 px desktop, min-height 38 px, padding 9 px 11 px |
| Input pada `.entry-sheet` | Min-height 39 px; label 12 px desktop |
| Textarea `.entry-sheet` | Min-height 56 px, max-height 320 px, line-height 1.5; bertambah tinggi sesuai isi |
| `.panel-head` | Padding 21 px 24 px 17 px |
| `.data-table` | Width 100%; border-collapse collapse; table-layout fixed |
| Header tabel | Font final 12 px, latar `#fafbfc` |
| Sel tabel | Font final 13 px; padding dasar 17 px |
| Nama part dalam tabel | 14 px, weight 600; warna teks utama; hover biru |

Gunakan ikon SVG dari objek `paths` pada `app.js`; jangan mengganti dengan emoji atau ikon paket lain yang ketebalannya berbeda. Ikon dekoratif memakai `aria-hidden`; tombol ikon tetap mempunyai nama aksesibel.

### 3.3 Breakpoint dan cascade

| Lebar viewport | Perilaku penting |
| --- | --- |
| ≥1500 px | Sidebar 230 px; main 40 px 46 px; topbar horizontal 46 px |
| 1201–1499 px | Sidebar 220 px; main mengikuti nilai dasar |
| 801–1200 px | Sidebar 192 px; main 26 px 24 px; topbar horizontal 24 px |
| ≤1050 px | Identitas editor menjadi 2 kolom; rincian trial 2 kolom; activity tetap 3 kolom sampai ≤600 px |
| ≤1000 px | Filter dapat membungkus; tanggal kanan topbar disembunyikan; detail menjadi satu kolom utama |
| ≤800 px | `--side:0`; sidebar menjadi drawer selebar 220 px; hamburger tampil; topbar 57 px; overlay saat menu dibuka |
| ≤700 px | Repeater memperkecil kolom dan gap; belum bertumpuk penuh |
| ≤600 px | Main 23 px 16 px 32 px; tabel menjadi kartu/baris ringkas; activity satu kolom; trial tetap dua kolom; CA/Next Action/Feedback bertumpuk |
| ≤440 px | Savebar dapat membungkus; area tombol selebar container; min-width tombol Simpan 162 px; padding bawah editor 145 px |

CSS mempunyai aturan dasar, override keterbacaan, media query lama, lalu **override editor satu halaman** dan **perbaikan tabel Project** di bagian akhir. Pertahankan urutannya. Jangan mengambil hanya potongan pertama untuk `.field-label`, `.editor-main`, `.context`, atau repeater karena nilai akhirnya bisa berbeda.

### 3.4 Mengapa CSS asli sebaiknya dipakai dahulu

Untuk mengejar kesamaan, salin CSS asli ke stylesheet aplikasi dengan scope yang sesuai, kemudian render DOM dan kelas yang sama. Gunakan asset pipeline repository yang sudah ada. Tailwind tetap dipakai aplikasi; tidak perlu langsung mengubah seluruh CSS menjadi utility.

Tailwind Preflight, style global `button/input/table`, style dialog, dan urutan import dapat mengubah hasil walaupun markup sama. Periksa computed style pada browser sebelum menambal dengan banyak utility atau `!important`. Setelah tampilan cocok, refactor ke Tailwind hanya jika memang diminta dan hasilnya tetap sama. Jangan memasang versi framework baru untuk pekerjaan UI ini.

## 4. Shell dan navigasi

Sumber: `shell()`, `content()`, `nav()`, `render()`.

| Bagian | Tampilan/perilaku |
| --- | --- |
| Branding | Teks `New Model`, lalu `PROGRESS TRACKER`; klik menuju dashboard |
| Label navigasi | `WORKSPACE` |
| Menu berurutan | Dashboard, Project, Progress, Draft |
| Menu aktif | Latar biru lembut, teks biru; `aria-current="page"` |
| Counter menu | Hanya Draft, jumlah draft aktif |
| Footer sidebar | Kosong; tidak ada profil, avatar, tombol reset, atau catatan prototype |
| Breadcrumb | `Workspace /` lalu nama area aktif |
| Editor/detail progress | Menu Progress aktif; breadcrumb Progress |
| Detail project | Menu Project aktif; breadcrumb Project |
| Header kanan | Referensi hanya menampilkan `Oktober 2026`; pada ≤1000 px tanggal ini tidak tampil |
| Hamburger | Tampil pada ≤800 px; membuka/menutup drawer dan scrim |

Tanggal Oktober 2026 adalah bagian fixture referensi, bukan tanggal yang harus dikunci pada aplikasi produksi. Untuk screenshot pembanding gunakan periode yang sama. Label `Prototype · data contoh` sudah dihapus dan tidak boleh ditambahkan sebagai ganti tanggal.

Navigasi keluar dari editor menyelesaikan autosave yang tertunda. Jika penyimpanan gagal, pertahankan isian dan tampilkan kegagalan; jangan membuang editor. Pada referensi, perpindahan halaman kembali ke atas. Browser URL pada referensi bukan rancangan route Laravel yang wajib diikuti.

## 5. Dashboard dan daftar Progress

Sumber: `dashboardHTML()`, `summaryHTML()`, `filtered()`, `tableHTML()`, `updateTable()`.

### 5.1 Susunan halaman

1. Judul: Dashboard = `Aktivitas New Model`; menu Progress = `Semua progress`.
2. Tombol kanan: `Lanjutkan draft` + jumlah draft, lalu `Tambah progress` sebagai tombol biru.
3. Caption ringkasan: `RINGKASAN KESELURUHAN` di kiri, `Semua project` di kanan.
4. Satu panel ringkasan horizontal dengan tiga kolom.
5. Panel `Daftar progress`.
6. Tab status `Open`, `Closed`, `Semua`, masing-masing dengan jumlah.
7. Baris pencarian/filter, filter tambahan bila dibuka, informasi hasil dan pengurutan.
8. Tabel hasil dan footer tabel.
9. Tautan `Lihat Project Master` di kanan bawah.

Tidak ada eyebrow atau subtitle di sekitar judul halaman. Tidak ada paragraf penjelas di bawah `Daftar progress`, dan tidak ada kalimat penjelas Open/Close di bawah tabel.

### 5.2 Ringkasan

| Urutan | Label | Metadata referensi | Sumber hitung |
| --- | --- | --- | --- |
| 1 | Progress Open | Perlu ditinjau & dilanjutkan | Semua progress dengan status Open |
| 2 | Draft aktif | Belum diterapkan ke progress | Semua draft |
| 3 | Aktivitas bulan ini | Oktober 2026 | Tanggal aktivitas berawalan `2026-10` pada fixture |

Angka ditampilkan minimal dua digit, misalnya `06`. Ringkasan tidak berubah cakupannya menjadi hasil pencarian. Tiga kolom berada dalam satu panel dengan separator vertikal, bukan tiga kartu besar terpisah.

### 5.3 Filter dan urutan

| Kontrol | Isi/perilaku |
| --- | --- |
| Pencarian | Placeholder `Cari JO, Part No, nama part…`; mencari ketiga identitas tersebut, tidak peka huruf besar/kecil |
| Activity | `Semua Activity`, lalu lima opsi Activity yang disepakati |
| Customer | `Semua customer`, lalu nilai unik dari Project Master |
| Filter | Membuka baris filter tambahan; tidak berpindah halaman |
| Reset | Membersihkan pencarian, Activity, Customer, PIC, peran PIC, tanggal; mengembalikan urutan terlama |
| Nama PIC | Teks bebas; menyaring PIC terkait, bukan PIC Progress |
| Peran PIC | Semua peran; PIC Customer; Corrective Action; Next Action; Feedback Customer |
| Dari/Sampai tanggal | Menyaring tanggal aktivitas, batas inklusif |
| Urutan | `Terlama lebih dulu` ↔ `Terbaru lebih dulu`; tanggal lalu ID sebagai tie-break referensi |

Masuk pertama ke dashboard memakai Open dan urutan terlama. Klik menu Progress memilih Semua. Filter dipertahankan saat kembali dari editor/detail; jangan membuat ulang state filter pada setiap render. Reset pada dashboard memilih Open; Reset pada menu Progress memilih Semua. Tab status mendukung navigasi panah kiri/kanan. Ini **tab daftar**, bukan tab editor.

### 5.4 Tabel progress

| Kolom | Lebar desktop | Isi |
| --- | --- | --- |
| Tanggal | 14% | Tanggal aktivitas dalam format lokal Indonesia |
| Project / Part | 32% | Nama part sebagai tautan; Part No dan JO No pada baris kedua |
| Activity | 16% | Nama Activity; status juga ditampilkan ketika filter Semua |
| Next Action | 33% | Uraian Next Action pertama, maksimal dua baris; `+N tindakan lainnya` bila lebih dari satu |
| Buka | 5% | Tombol ikon chevron dengan label aksesibel |

Tidak ada kolom PIC Progress. Belum ada Next Action menampilkan `Belum ada next action`.

Tautan nama part dan tombol chevron pada progress **Open** langsung membuka editor. Progress **Closed** membuka ringkasan read-only dengan tombol Reopen. Jangan menambahkan langkah Detail → Edit untuk progress Open.

Footer referensi: `Menampilkan N progress` dan `Halaman 1 dari 1`. Fixture kecil tidak menerapkan pagination server. Jangan menjadikan angka tersebut klaim pagination produksi atau menambah fitur pagination baru di luar task UI yang sedang dikerjakan.

Empty state hasil filter: judul `Tidak ada progress yang cocok`, kalimat bantuan sebagaimana source, dan tombol `Bersihkan filter`. Filter kosong tidak menghapus data atau mengubah ringkasan keseluruhan.

## 6. Project Master

Sumber: `projectsHTML()`, `projectDetailHTML()`, `masterDialog()`, `saveMaster()`.

### 6.1 Daftar Project

Header hanya judul `Project Master` dan tombol biru `Tambah project`. Tidak ada eyebrow/subtitle. Panel di bawahnya menampilkan jumlah project dan tabel.

| Kolom berurutan | Binding referensi | Aturan |
| --- | --- | --- |
| Project / Part | `name`, `part`, `jo` | Nama sebagai tautan; Part No / JO pada baris kedua; lebar header 35% |
| Customer | `customer` | Teks; gunakan em dash bila kosong |
| Model | `model` | Kode teks di `.model-code`, font monospace; jangan format sebagai tanggal |
| T0 Target | `t0Target` | Tanggal tersendiri; `Belum diisi` bila kosong |
| Progress | Jumlah progress untuk project | `N kegiatan` |
| Buka | `id` | Tombol chevron; area header terakhir 50 px |

Gunakan kelas sel `.project-customer`, `.project-model`, `.project-target`, `.project-count`. Header dan body mempunyai enam kolom dengan urutan identik.

**Regresi yang sudah diperbaiki:** jangan menambahkan kembali aturan yang menyembunyikan sel Model tanpa menyembunyikan headernya. Versi lama memakai `.customer-col { display:none }` untuk sel Model pada viewport sempit, sehingga tanggal T0 tampak berada di bawah header Model.

Pada ≤600 px tabel berubah menjadi kartu. Part berada di baris teratas, Customer di baris berikutnya, Model dan T0 Target masing-masing memiliki labelnya sendiri, lalu jumlah progress. Label kecil berasal dari `data-label`; kedua nilai tetap tersedia. Jangan menyelesaikan masalah ruang dengan menghilangkan Model.

### 6.2 Dialog tambah/edit Project

Dialog lebar maksimum 790 px; width `calc(100% - 32px)`; max-height 86vh. Body dapat digulir, dengan tinggi maksimal 65vh. Header berisi judul dan tombol tutup. Footer berisi `Batal` dan `Simpan project`. Isi form dibagi menjadi kelompok berikut; desktop dua kolom, mobile satu kolom.

| Kelompok | Label persis | Binding | Kontrol | Wajib |
| --- | --- | --- | --- | --- |
| Identitas | JO No * | `jo` | Teks | Ya |
| Identitas | Part No * | `part` | Teks | Ya |
| Identitas | Part Name * | `name` | Teks | Ya |
| Identitas | Customer | `customer` | Teks | Tidak |
| Identitas | Model | `model` | Teks; placeholder `Contoh: K2SJ, HK09, RS8` | Tidak |
| Identitas | No Doc | `doc` | Teks | Tidak |
| Material & proses | Material | `material` | Teks | Tidak |
| Material & proses | Material Thickness | `thickness` | Teks | Tidak |
| Material & proses | Material Width | `width` | Teks | Tidak |
| Material & proses | Process | `process` | Teks | Tidak |
| Jadwal | Date Receive | `receive` | Tanggal | Tidak |
| Jadwal | T0 Target | `t0Target` | Tanggal | Tidak |
| Jadwal | T0 Actual | `t0Actual` | Tanggal | Tidak |
| Jadwal | Plan Start | `planStart` | Tanggal | Tidak |
| Jadwal | Plan Finish | `planFinish` | Tanggal | Tidak |
| Jadwal | Actual Start | `actualStart` | Tanggal | Tidak |
| Jadwal | Actual Finish | `actualFinish` | Tanggal | Tidak |
| Jadwal | Event 1 | `event1` | Tanggal | Tidak |
| Jadwal | Event 2 | `event2` | Tanggal | Tidak |
| Jadwal | Event 3 | `event3` | Tanggal | Tidak |
| Setelah kelompok | Remark Project | `remark` | Textarea | Tidak |

Spec, Project User, dan Status Project tidak menjadi input form ini. JO No dan Part No harus unik sesuai hubungan satu-ke-satu. Jangan mengganti kode Model menjadi dropdown terbatas; contoh K2SJ/HK09/RS8 hanya contoh format, bukan daftar yang membatasi nilai.

T0 berarti tanggal pengiriman sample pertama. T0 Target dan Actual diisi manual; jangan otomatis mengambil semua aktivitas Sent Sample sebagai T0. Event 1/2/3 juga tanggal manual. Ukuran mempertahankan teks koma/titik dari admin.

Error form muncul dalam dialog, mempertahankan seluruh input. Referensi memeriksa tiga field wajib dan duplikasi JO/Part No. Aplikasi operasional menggunakan validasi server yang sudah berlaku; jangan menganggap validasi browser cukup.

### 6.3 Detail Project

Header tetap berisi JO No dan Part No di atas nama part, customer/model di bawah nama, serta tombol `Edit project`. Ini identitas data, bukan helper yang dihapus dari halaman daftar.

Area utama menampilkan jadwal; area samping menampilkan spesifikasi/material. Di bawahnya ada `Histori progress` dan tombol `Tambah progress` yang membuka editor dengan JO sudah terpilih. Identitas editor mengikuti data master terbaru.

**Keadaan legacy yang perlu diketahui:** source snapshot masih membawa properti `spec` pada fixture lama dan menampilkannya read-only di detail Project. Pengguna meminta menghapus **input Spec** dari form. Handoff ini tidak memberi izin menghapus kolom atau histori Spec dari database, dan tidak memberi izin mengembalikan input Spec ke dialog. Jika perlu menghilangkan seluruh data/display legacy tersebut, perlakukan sebagai perubahan terpisah.

## 7. Editor progress: satu lembar kerja

Sumber utama: `startEditor()`, `editorHTML()`, `sectionHTML()`, `problemHTML()`, `actionHTML()`, `field()`.

### 7.1 Susunan keseluruhan

1. Tautan `Kembali ke daftar`.
2. Judul `Tambah progress` atau `Edit progress`; status Draft/Open di samping.
3. Penanda `* Wajib diisi` di kanan; untuk progress tersimpan terdapat `Lihat ringkasan`.
4. Panel identitas project: JO No dan identitas autofill.
5. Error summary hanya bila validasi gagal.
6. Satu panel `.entry-sheet` berisi lima section yang selalu dirender, bernomor 01–05.
7. Savebar fixed pada bawah viewport.

| Nomor | Judul section persis | ID referensi |
| --- | --- | --- |
| 01 | Aktivitas | `entry-activity` |
| 02 | Problem & Corrective Action | `entry-problems` |
| 03 | Next Action | `entry-nextActions` |
| 04 | Feedback Customer | `entry-feedback` |
| 05 | Catatan & lampiran | `entry-files` |

Section dipisahkan border tipis. Judul disertai nomor kecil; tidak ada kartu besar terpisah untuk setiap field. Semua section tersedia untuk kelima Activity. Pergantian Activity tidak boleh mereset atau menyembunyikan data trial/problem/action/feedback.

### 7.2 Panel identitas

Desktop: selector JO di kiri, identitas di kanan, dipisahkan border vertikal. Pada lebar dasar editor, grid `205px minmax(0,1fr)`, gap 22 px, padding 16 px 20 px. Pada ≤1200 px selector 190 px; pada mobile panel menjadi bertumpuk.

| Item | Input atau display | Perilaku |
| --- | --- | --- |
| JO No * | Dropdown `projectId` | Placeholder `Pilih JO No`; wajib saat Simpan |
| Part | Read-only | Part Name kuat di baris pertama, Part No lebih kecil di bawah |
| Customer | Read-only | Dari project terpilih |
| Model | Read-only | Kode teks dari project terpilih |
| No Doc | Read-only | Dari project terpilih; harus tetap terlihat di layar kecil |

Di atas identitas ada ikon kunci kecil dan teks `Identitas Project Master`. Semua identitas kosong menampilkan `—`. Pemilihan JO menyegarkan konteks identitas dan membuat editor dirty. Jangan membuat five autofill tersebut bisa mengedit Project Master dari form progress.

### 7.3 Section 01 — Aktivitas

| Urutan | Label persis | Binding | Kontrol/placeholder | Wajib |
| --- | --- | --- | --- | --- |
| 1 | Tanggal aktivitas | `date` | Date | Ya |
| 2 | Activity | `activity` | Select; `Pilih Activity` | Ya |
| 3 | PIC Customer | `customerPic` | Teks; `Nama PIC customer` | Tidak |
| 4 | Uraian aktivitas | `description` | Textarea; `Kegiatan yang dilakukan`; span penuh | Tidak |

Desktop baris pertama tiga kolom; uraian di bawahnya selebar kelompok. Setelah border tipis ada subjudul `Rincian trial & material`.

| Urutan | Label persis | Binding | Kontrol |
| --- | --- | --- | --- |
| 1 | Process Trial | `processTrial` | Teks |
| 2 | Mesin | `machine` | Teks |
| 3 | DH · Die High | `dh` | Teks; contoh `320,5` |
| 4 | Material Actual Trial | `material` | Teks |
| 5 | Qty Material | `qtyMaterial` | Teks |
| 6 | Qty Process | `qtyProcess` | Teks |
| 7 | Qty Sample | `qtySample` | Teks |
| 8 | Keterangan event | `eventNote` | Teks |

Keempat field pertama berada pada satu baris desktop, empat berikutnya pada baris berikut. Pada ≤1050 px menjadi dua kolom dan tetap dua kolom pada mobile. Semua opsional. DH bermakna Die High; tipe kontrol teks sengaja menjaga input angka dengan koma/titik. Jangan mengubah Qty atau DH menjadi kontrol spinner atau membulatkan nilai tanpa kebutuhan baru.

### 7.4 Section 02 — Problem & Corrective Action

Ketika form baru dibuka sudah terdapat **Problem 1** dengan satu CA kosong. Struktur per problem:

1. Header tipis dengan `Problem N` dan tombol hapus bila baris baru tersebut boleh dihapus.
2. Textarea uraian problem; placeholder `Jelaskan temuan atau ketidaksesuaian`.
3. Deretan thumbnail foto, jika ada, serta kontrol `Foto problem`.
4. Daftar corrective action milik problem itu.
5. Link `Tambah corrective action` di bawah daftar CA.

Setelah seluruh problem terdapat link `Tambah problem`. Problem baru selalu mempunyai satu CA siap diisi.

| Field | Binding | Aturan |
| --- | --- | --- |
| Uraian problem | `problems[i].description` | Textarea opsional; label aksesibel mencantumkan nomor problem |
| Foto problem | `problems[i].photos[]` | Foto milik problem tersebut; bukan lampiran global |
| Corrective Action | `problems[i].actions[j].description` | Textarea; placeholder `Tindakan perbaikan` |
| PIC | `problems[i].actions[j].pic` | Teks bebas; placeholder `Nama PIC` |
| Actual date | `problems[i].actions[j].actualDate` | Tanggal opsional |

Nomor 01/02 pada baris CA hanya nomor urut tampilan. Identitas data adalah ID stabil. Jangan memakai index sebagai key persistent setelah menambah/menghapus baris.

Desktop satu baris action: nomor → uraian → PIC → actual date → area tombol hapus. Grid utama: `20px minmax(170px,1fr) minmax(120px,160px) 145px 25px`, gap 10 px. Pada ≤1200 px dan ≤700 px ada ukuran lebih padat; gunakan CSS sumber. Pada ≤600 px uraian memenuhi lebar, PIC dan actual date di bawah dalam dua kolom.

CA ditambah tanpa modal. Fokus pindah ke uraian CA baru dan semua isian lama tetap ada. Menambah problem memfokuskan textarea problem baru.

### 7.5 Section 03 — Next Action

Satu baris kosong tersedia sejak awal. Struktur baris mengikuti grid action yang sama dengan CA, tetapi list-nya berada langsung pada progress.

| Label | Binding | Kontrol |
| --- | --- | --- |
| Uraian next action | `nextActions[i].description` | Textarea; `Langkah berikutnya` |
| PIC | `nextActions[i].pic` | Teks bebas |
| Actual date | `nextActions[i].actualDate` | Tanggal opsional |

Link bawah: `Tambah next action`. Tidak ada pilihan problem, target date, checkbox otomatis selesai, atau progress percentage.

### 7.6 Section 04 — Feedback Customer

Satu baris feedback kosong langsung tersedia ketika belum ada feedback. Tidak ada kalimat helper di bawah judul section.

| Urutan desktop | Label | Binding | Kontrol |
| --- | --- | --- | --- |
| 1 | Isi feedback | `feedback[i].content` | Textarea; `Tanggapan customer` |
| 2 | PIC Feedback Customer | `feedback[i].pic` | Teks bebas; `Nama PIC` |
| 3 | Tanggal feedback | `feedback[i].date` | Date opsional |
| Di bawah baris | Dokumen feedback | `feedback[i].files[]` | Pemilih file; list file milik feedback itu |

Semua field feedback opsional pada Simpan. Link `Tambah feedback` hanya diperlukan untuk feedback berikutnya. Feedback yang sudah ada tetap menjadi entri tersendiri saat entri baru ditambahkan.

Footer mempunyai tombol `Ke feedback ↓`: menggulir ke section ini dan memfokuskan heading tanpa membuka tab, modal, atau entry baru. Tombol tersebut tidak membuat feedback kosong menjadi record tersimpan.

### 7.7 Section 05 — Catatan & lampiran

| Label | Binding | Kontrol |
| --- | --- | --- |
| Remark Progress | `remark` | Textarea penuh; `Catatan tambahan` |
| Tambah dokumen | `files[]` | Pemilih file untuk lampiran progress |

Foto problem tetap berada pada problem. Dokumen feedback tetap pada feedback. Jangan menggabungkan ketiganya menjadi satu daftar yang kehilangan pemilik relasi.

### 7.8 Foto dan file pada referensi

- Foto: JPG, PNG, WebP; batas demo 2 MB per file; thumbnail 54 × 48 px. Klik thumbnail membuka dialog `Foto problem`.
- Batas 2 MB berasal dari simulasi penyimpanan browser, bukan spesifikasi batas file produksi yang baru.
- Dokumen: referensi menyimpan nama dan ukuran, **bukan konten file**; label file menyebut simulasi lampiran. Tidak ada unduhan dokumen riil.
- Paket tidak menyertakan foto masalah nyata atau dokumen perusahaan.
- Implementasi Laravel menggunakan mekanisme upload/storage yang sudah ditetapkan. Jangan menyimpan foto produksi sebagai base64 dalam localStorage hanya karena referensi melakukannya.
- Instruksi migrasi “foto saja, dokumen tidak” berlaku pada pemindahan file histori Access; jangan mengartikannya sebagai perintah menghilangkan seluruh kontrol dokumen pada UI referensi yang disetujui.

### 7.9 Hapus baris baru

Tombol hapus tampil hanya untuk entri yang belum menjadi record progress tersimpan dan ketika repeater memiliki lebih dari satu baris. Baris tunggal kosong tetap tersedia sebagai bantuan input.

Penghapusan memakai dialog konfirmasi `Hapus baris baru?`. Tombol `Batal` dan `Hapus baris`. Penghapusan hanya memengaruhi baris tersebut, tidak mengubah urutan/isi entri lain. Tidak ada fitur hapus permanen record progress/proyek dari perubahan UI ini.

### 7.10 Savebar dan status simpan

Desktop: fixed di bawah, left sebesar lebar sidebar, right 0, border atas, background putih hampir solid, z-index 20. Padding dasar 14 px 35 px; mengikuti breakpoint. Tombol tetap dapat dicapai saat form panjang.

| Keadaan | Tampilan |
| --- | --- |
| Form baru, belum disentuh | Area indikator kosong/tersembunyi; tidak ada ikon centang palsu atau petunjuk autosave |
| Ada perubahan, belum selesai autosave | `Menyimpan draft demo…` pada referensi |
| Autosave berhasil | `Draft demo tersimpan · HH:mm` pada referensi |
| Autosave gagal | Pesan gagal, indikator error, dan `Coba lagi` |
| Menyimpan progress gagal | Isian tetap ada; tampilkan kegagalan dan izinkan mencoba kembali |

Di kanan ada `Ke feedback ↓` dan tombol biru `Simpan progress` dengan ikon centang. Shortcut Ctrl+Enter atau Cmd+Enter menyimpan ketika editor aktif dan dialog tidak terbuka. Enter biasa pada textarea tetap menambah baris.

Teks `Draft otomatis saat mulai mengisi` dan `Simpan untuk menerapkan perubahan ke progress.` telah dihapus. Jangan menggantinya dengan helper baru. Status nyata saat menyimpan bukan helper permanen dan tetap diperlukan. Dalam aplikasi produksi, kata “demo” disesuaikan dengan adapter yang benar-benar bekerja; jangan mengklaim tersimpan sebelum server mengakui keberhasilannya.

Padding bawah editor: 120 px dasar, 130 px pada ≤600 px, 145 px pada ≤440 px. Input memakai scroll margin bawah yang memadai. Jangan membiarkan field terakhir atau fokus keyboard tertutup savebar.

## 8. Draft, Simpan, dan status progress

### 8.1 Memulai dan melanjutkan editor

- Membuka form baru saja tidak membuat draft.
- Form baru mengambil data kosong kemudian menyiapkan baris awal melalui logika setara `prepareEditor()`.
- Membuka Open yang sudah memiliki draft melanjutkan draft tersebut. Tanpa draft, editor memakai salinan progress tersimpan.
- Perubahan editor tidak langsung mengubah versi progress yang sedang dibaca di daftar/ringkasan.
- Referensi memakai debounce autosave 650 ms setelah perubahan; jangan memakai timer dummy untuk menyatakan server berhasil.
- Kembali atau navigasi keluar menyelesaikan autosave tertunda dahulu. Jika gagal, isian tetap dapat dipulihkan.

### 8.2 Normalisasi baris kosong

Normalisasi dilakukan sebelum menyimpan draft maupun menerapkan progress. Jangan menggunakan aturan “hapus row kalau description kosong”.

| Entitas baru | Boleh ditiadakan hanya jika |
| --- | --- |
| CA | Description, PIC, dan actual date semuanya kosong setelah pengecekan whitespace |
| Next Action | Description, PIC, dan actual date semuanya kosong |
| Feedback | Content, PIC, date semuanya kosong dan tidak ada file |
| Problem | Description kosong, tidak ada foto, dan tidak ada CA yang berisi data |

Aturan tambahan:

1. PIC saja atau tanggal saja tetap merupakan data dan harus dipertahankan.
2. Foto saja mempertahankan problem; file saja mempertahankan feedback.
3. Problem dengan CA terisi tetap dipertahankan walaupun uraian problem kosong.
4. Record yang sudah tersimpan sebelumnya tidak otomatis dihapus ketika admin mengosongkan nilainya.
5. ID entri yang berisi data tetap sama setelah autosave, Simpan, dan buka ulang.
6. Normalisasi tidak boleh memutasi editor aktif lalu menggeser fokus/nomor binding saat admin sedang mengetik.
7. Baris kosong untuk memudahkan input boleh dibuat kembali ketika editor dibuka; jangan menghitungnya sebagai record bisnis.

Contoh minimal Simpan: JO + tanggal aktivitas + Activity terisi, seluruh repeater kosong → satu progress Open, nol problem tersimpan, nol Next Action tersimpan, nol feedback tersimpan.

### 8.3 Simpan progress

Wajib hanya JO, tanggal aktivitas, Activity yang valid. Semua isian tetap tersedia jika validasi gagal. Tampilkan error summary dan error per field; fokus menuju JO, lalu tanggal, lalu Activity sesuai field yang belum lengkap.

Setelah sukses: terapkan perubahan satu kali, hapus draft yang sudah diterapkan, tampilkan ringkasan progress tersimpan dan toast `Progress tersimpan.`. Double click/retry tidak boleh menciptakan duplikasi progress atau child. Status progress baru Open; update mempertahankan status yang berlaku. Closed harus menolak edit/apply sampai Reopen.

Referensi sudah mempertahankan editor jika penyimpanan browser gagal. Adapter aplikasi harus memberikan sifat pemulihan yang sama ketika request gagal. Jangan mengandalkan `db.applied` di browser sebagai mekanisme idempotensi produksi.

### 8.4 Close dan Reopen

Close tersedia pada ringkasan progress Open. Konfirmasi menjelaskan catatan tetap tersimpan, dan bila ada draft belum diterapkan, Close berlaku pada versi tersimpan. Draft tersebut dipertahankan.

Tidak ada syarat semua CA selesai, semua actual date terisi, ada feedback, atau persentase 100%. Admin menentukan Close. Setelah Closed, editor tidak boleh aktif. Reopen mempertahankan semua problem, tindakan, feedback, dan lampiran. Pada referensi, Reopen mengembalikan status Open pada ringkasan; admin kemudian dapat memilih Edit/Lanjutkan draft.

## 9. Ringkasan progress dan halaman Draft

### 9.1 Ringkasan progress

Sumber: `detailHTML()`.

Header: tautan kembali, JO dan tanggal, nama Activity sebagai judul, status Open/Closed, kemudian Part Name / Part No. Tombol Open: `Close progress` dan `Edit progress` atau `Lanjutkan draft` bila draft tersedia. Tombol Closed: `Reopen progress`.

Desktop memakai `.detail-layout`: area utama fleksibel dan aside 260 px, gap 22 px; aside 230 px pada ≤1200 px. Pada ≤1000 px area utama bertumpuk, dua panel aside dapat berdampingan; pada ≤600 px seluruhnya satu kolom.

Urutan blok utama:

1. Aktivitas — uraian kegiatan.
2. Problem & Corrective Action — jumlah problem, setiap problem dengan tindakan, PIC, actual date, dan foto.
3. Next Action — jumlah dan daftar terpisah dari problem.
4. Feedback Customer — jumlah, isi, PIC, tanggal, serta file masing-masing feedback.
5. Lampiran kegiatan.

Aside pertama: Customer, Model, No Doc, PIC Customer, tautan `Lihat project`. Aside kedua: Process Trial, Mesin, DH · Die High, Material, Qty Material, Qty Process, Qty Sample, Ket Event, Remark. Tidak ada PIC Progress.

Jika ada draft perubahan, ringkasan tetap menampilkan versi yang sudah diterapkan dan memberi catatan bahwa draft belum diterapkan. Draft pada Closed memerlukan Reopen. Jangan menampilkan isi draft sebagai progress tersimpan hanya karena keduanya memakai komponen baca yang sama.

### 9.2 Halaman Draft

Sumber: `draftsHTML()`.

Judul hanya `Draft`, tombol kanan `Tambah progress`. Tidak ada eyebrow/subtitle. Draft diurutkan berdasarkan waktu update terbaru.

Setiap kartu mempunyai ikon draft, label `Draft baru` atau `Draft perubahan`, nama part dan Activity bila tersedia, JO/waktu tersimpan, tombol buang draft, serta `Lanjutkan`.

Untuk draft pada progress Closed, kartu memberi keterangan Closed dan tombol `Lihat progress` yang menuju ringkasan. Jangan langsung membuka editor. Buang draft menggunakan dialog konfirmasi dan tidak menghapus progress yang sudah tersimpan. Empty state: `Semua draft sudah ditangani` dengan kalimat bantuan sebagaimana source.

### 9.3 Dialog, toast, dan fokus

| Dialog | Pemicu | Aksi utama |
| --- | --- | --- |
| Tambah project / Edit project | Halaman Project/detail project | Batal; Simpan project |
| Close progress? | Ringkasan Open | Kembali; Close progress |
| Hapus baris baru? | Hapus entri baru pada repeater | Batal; Hapus baris |
| Buang draft? | Ikon hapus pada kartu draft | Batal; Buang draft |
| Foto problem | Klik thumbnail | Tombol tutup; gambar maksimal lebar container/tinggi 60vh |

Referensi memakai elemen `<dialog>` native. Jangan menambahkan popup sukses untuk setiap input atau setiap autosave. Toast muncul saat aksi penting selesai, sekitar 4,2 detik, dengan `role="status"`/live region. Kesalahan yang memerlukan tindakan tetap tersedia pada lokasi yang relevan dan tidak hanya bergantung pada toast sementara.

Handler reset data lama masih ada pada source, tetapi tidak punya tombol yang ditampilkan. Jangan mengaktifkan kembali fitur reset melalui sidebar atau shortcut baru.

## 10. Kontrak data referensi untuk pemetaan

Nama berikut adalah key prototype, bukan nama kolom Laravel yang wajib dibuat. Petakan ke model/service yang sudah ada pada task pertama.

| Objek | Key utama |
| --- | --- |
| Project | `id`, `jo`, `part`, `name`, `customer`, `model`, `doc`, `material`, `thickness`, `width`, `process`, `receive`, `t0Target`, `t0Actual`, `planStart`, `planFinish`, `actualStart`, `actualFinish`, `event1`, `event2`, `event3`, `remark` |
| Progress | `id`, `projectId`, `date`, `activity`, `description`, `customerPic`, `processTrial`, `machine`, `dh`, `material`, `qtyMaterial`, `qtyProcess`, `qtySample`, `eventNote`, `remark`, `status` |
| Problem | `id`, `description`, `photos[]`, `actions[]` |
| CA | `id`, `description`, `pic`, `actualDate` |
| Next Action | `id`, `description`, `pic`, `actualDate`; berada dalam `progress.nextActions[]` |
| Feedback | `id`, `date`, `content`, `pic`, `files[]` |
| Foto demo | `id`, `name`, `src` berupa data URL |
| Dokumen demo | `id`, `name`, `size`; hanya metadata |
| Draft | `id`, `progressId` nullable, `data` berupa salinan payload editor, `updatedAt` |

`progress.problems[]`, `progress.nextActions[]`, `progress.feedback[]`, dan `progress.files[]` adalah daftar independen sesuai pemilik relasinya. Jangan menyimpan Next Action sebagai anak problem. Jangan menggunakan nama PIC sebagai ID relasi atau mengharuskan akun PIC.

Nilai tanggal payload memakai `YYYY-MM-DD`; kosong memakai string kosong pada reference. Aplikasi dapat memakai null melalui adapter yang konsisten. Format tanggal tampilan menggunakan locale Indonesia. Angka, ukuran, dan Qty dalam prototype dipertahankan sebagai teks sesuai input. Model juga teks dan tidak melewati formatter tanggal.

`spec` legacy hanya dijelaskan pada bagian 6.3. Properti `pic` tingkat progress yang masih ada pada data demo lama dibuang oleh `migrate()`, tanpa membuang `pic` milik action atau feedback. Jangan menerjemahkan migrasi demo ini menjadi migrasi database destruktif.

## 11. Panduan penerapan ke Blade, Livewire, Tailwind

### 11.1 Langkah pertama wajib memetakan repository aktual

Baca instruksi repository, `composer.lock`, `package.json`/lockfile, komponen prototype aktif, CSS yang benar-benar di-load, route, adapter/service, fixture, dan catatan P01 terakhir. Versi Laravel/Livewire/Tailwind belum diketahui dari paket ini; jangan menebak API versi tertentu.

Buat tabel pemetaan berisi fungsi referensi → file aktual → data sumber → event/action aktual. Nama file target di bawah hanya contoh struktur. Jika aplikasi sudah memiliki komponen yang tepat, edit komponen tersebut alih-alih membuat duplikat.

| Kelompok | Sumber yang dibaca | Contoh tujuan jika belum tersedia |
| --- | --- | --- |
| Shell | `shell()`, `icon()`, `paths` | Layout Blade / komponen navigasi |
| Dashboard/Progress | `dashboardHTML()`, `tableHTML()`, `filtered()` | View dashboard dan daftar yang sudah ada |
| Project | `projectsHTML()`, `masterDialog()` | Komponen daftar Project dan form dialog |
| Editor | `editorHTML()`, `sectionHTML()` | View editor progress dengan section berurutan |
| Repeater | `problemHTML()`, `actionHTML()` | Partial/komponen Problem, Action, Feedback |
| Detail/Draft | `detailHTML()`, `draftsHTML()` | View read-only dan daftar draft |
| CSS | Seluruh `styles.css`, urutan utuh | Stylesheet referensi melalui asset pipeline aplikasi |
| Draft/status | `prepareEditor`, `cleanProgress`, `saveDraft`, `applyDraft`, `setStatus` | Adapter/state/action yang sudah ada; logika dijadikan kontrak perilaku |

Jangan menjalankan `app.js` referensi untuk menguasai seluruh DOM di dalam komponen Livewire. Script tersebut membangun ulang `#app`; jika dipasang bersama rendering Livewire, input/fokus/binding dapat saling menimpa. Konversi template ke Blade dan event ke pemilik state aplikasi. Boleh memakai source mentah sebagai halaman referensi terpisah untuk membandingkan.

### 11.2 Aturan pemindahan markup

1. Pertahankan wrapper, class, urutan kolom, child, dan span yang menjadi bagian grid.
2. Pertahankan label, placeholder, ukuran ikon, dan caption final.
3. Ganti interpolasi JavaScript dengan output Blade yang di-escape; gunakan cara escaping aplikasi yang benar.
4. Jangan mengganti tombol dengan link tanpa mempertahankan perilaku/fokus, atau sebaliknya.
5. Pertahankan ID unik dan hubungan label `for` ke kontrol. ID prototype berisi titik pada beberapa field; selector JavaScript mentah perlu escaping jika menargetkan ID tersebut. Binding berbasis atribut/ID komponen yang aman boleh dipakai.
6. Pertahankan `aria-live`, `aria-invalid`, `aria-describedby`, nama tombol ikon, dan heading section.
7. Gunakan key komponen repeater berdasarkan ID stabil, termasuk ID sementara untuk baris baru. Nomor urut bukan key.
8. Jangan menambah wrapper yang mengubah anak langsung grid/flex tanpa mengecek efeknya.
9. Jangan memindah field yang dipakai admin bersamaan ke modal atau tab demi menyederhanakan kode.
10. Pastikan rerender tidak memindahkan caret/fokus atau posisi scroll ketika mengetik.

### 11.3 Penerapan state

- Pada tahap prototype, gunakan adapter/fixture yang sudah ada; jangan membangun backend baru hanya untuk menyamakan UI.
- Untuk server aktif, status simpan mengikuti respons nyata. Kegagalan request tidak boleh diganti timer sukses.
- Autosave harus menjaga perbedaan payload draft dan record yang sudah diterapkan.
- Simpan/Close/Reopen menjadi aksi berbeda. Tidak ada watcher yang menutup progress ketika field tanggal terisi.
- Saat JO berubah, ambil identitas master terkini. Jangan memakai snapshot identitas lama sebagai sumber perubahan master.
- Di Livewire, implementasikan lifecycle, debounce, upload, dan event sesuai versi yang terpasang; baca dokumentasi versi tersebut bila diperlukan. Paket ini tidak memaksa migrasi versi framework.
- Gunakan strategi error/transaction/idempotensi aplikasi yang sudah ada. Jangan memasukkan seluruh database simulasi ke state komponen publik.

### 11.4 Ruang lingkup yang tidak boleh meluas

Pekerjaan ini tidak mencakup perubahan stack ke React/Vue, desain baru, role baru, laporan, audit trail, kalkulasi persentase selesai, due date action, approval flow, autentikasi baru, migrasi Access, atau deployment server perusahaan. PRD tetap perlu persetujuan sebelum diedit. ID task UI dalam paket bukan bukti bahwa P01-T10/T11/T12 otomatis selesai.

## 12. Cara membuktikan tampilan identik

### 12.1 Kondisi pembanding

Gunakan reference pada paket sebagai baseline tetap. Website online dapat berubah di masa depan; SHA-256 dan commit pada `MANIFEST.json` mengikat paket ini ke versi disetujui.

Gunakan data yang sama, zoom 100%, browser/OS/font yang sama, viewport yang sama, serta state yang sama. Jangan membandingkan dashboard Open dengan Progress Semua, atau membandingkan editor berisi dua problem dengan editor kosong.

Viewport utama:

| Viewport | Alasan |
| --- | --- |
| 1475 × 722 | Ukuran desktop yang digunakan pada anotasi awal |
| 1366 × 768 | Laptop kerja yang umum untuk pengujian lokal |
| 911 × 722 | Ukuran anotasi terakhir; menguji layout dengan sidebar 192 px dan header kanan tersembunyi |
| 390 × 844 | Memeriksa fallback layar kecil dan savebar |

Jika ditemukan perbedaan terkait breakpoint, cek batas yang bersangkutan saja: 1001/1000 untuk regresi Model, 801/800 untuk drawer, 601/600 untuk tabel/repeater, 441/440 untuk savebar. Tidak perlu mengulang semua kombinasi setelah perubahan yang tidak memengaruhinya.

### 12.2 State layar yang dibandingkan

| State | Data/cara membuka |
| --- | --- |
| Dashboard Open | Fixture awal; 6 Open, 2 draft, 8 aktivitas Oktober |
| Progress Semua | Menu Progress; 8 progress termasuk 2 Closed |
| Project Master | 3 project fixture; Model dan T0 harus terpisah |
| Dialog tambah Project | Dari Project; 21 input, tanpa Spec; Model teks |
| Draft | 2 draft awal |
| Tambah progress | JO belum dipilih; satu problem + CA, satu Next Action, satu feedback kosong |
| Edit Trial kompleks | `p1`, JO-2026-018, 1 Oktober 2026; 2 problem × 2 CA, 3 Next Action, 1 feedback |
| Ringkasan Closed | `p7` atau `p8`; Reopen tersedia |
| Detail Project | `jo18`; jadwal, spesifikasi, histori |
| Error form | Simpan progress baru tanpa JO/tanggal/Activity |
| Empty search | Kata pencarian yang tidak cocok |

Nama/kode fixture adalah data contoh: Model awal masih `NX-26`/`MT-04`. K2SJ/HK09/RS8 adalah contoh input yang diberikan pengguna, bukan nilai yang otomatis mengganti histori atau project tersimpan.

### 12.3 Pemeriksaan visual

Periksa posisi sidebar/topbar, tepi panel, jarak antarbagian, lebar kolom, ukuran teks, jumlah baris yang terbungkus, tinggi input, bentuk tombol, warna/border, dan letak savebar. Bandingkan screenshot berdampingan atau overlay jika alat tersedia di repository. Catat viewport, state, dan perbedaan yang nyata; jangan hanya menulis “sudah mirip”.

Tidak boleh tersisa perbedaan struktur seperti field tersembunyi, urutan field berubah, header tabel bergeser, teks yang sudah dihapus muncul kembali, atau tombol Simpan menutup field. Selisih raster font/kontrol native antarplatform perlu dicatat, bukan ditutupi dengan klaim identik mutlak.

Checklist terperinci ada di `CHECKLIST_PARITAS_UI.md`. Hasil port dianggap selesai hanya setelah alur yang berubah dan visualnya benar-benar diperiksa pada lingkungan Laravel.

## 13. Batas paket dan verifikasi yang sudah dilakukan

- Empat file dalam `reference/` disalin dari source bersih commit yang tertera dan diikat dengan SHA-256.
- Site version 6 mengarah ke commit tersebut. Desain inilah yang disetujui pengguna pada percakapan.
- Pada pengerjaan referensi sebelumnya sudah dilakukan pemeriksaan sintaks, state draft/normalisasi/status, markup/binding, serta perbaikan header/sel Model. Itu bukan pengujian aplikasi Laravel pengguna.
- Browser control untuk QA visual tidak tersedia di lingkungan penyusunan paket. Paket tidak menyertakan screenshot pembanding atau klaim semua viewport telah diuji.
- Persetujuan pengguna berlaku terhadap UI yang dilihatnya. Tidak berarti backend, seluruh perangkat, UAT operasional, atau seluruh fase PRD sudah diterima.
- Source referensi memakai browser-local data, tidak memakai PostgreSQL, tidak terhubung ke Access/LAN perusahaan, dan tidak menyediakan autentikasi operasional.
- Histori/draft yang pernah diketik ke browser pengguna tidak ikut diekspor. Yang disertakan adalah source dan fixture fiktif bawaan.
- File deployment, credential, `.git`, dan identitas hosting yang dapat mengubah Site tidak disertakan dalam paket.

Gunakan prompt dan task kecil yang disertakan. Selesaikan satu task, periksa hasilnya, catat file yang benar-benar berubah, lalu lanjutkan. Source yang tetap dan pemeriksaan konkret lebih membantu model kecil daripada instruksi umum “buat bagus” atau “jangan AI slop”.
