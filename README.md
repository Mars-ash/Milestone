# Paket PRD dan task pelaksanaan Progress New Model

Versi 2.0 • 13 Oktober 2026

Paket ini memuat PRD lengkap dan 65 task dalam tujuh file fase. Urutannya dimulai dari prototype interaktif agar pengguna dapat menilai tampilan serta alur sebelum fondasi data operasional dan migrasi dikerjakan. File Markdown disediakan agar mudah dibaca AI dan diperbarui melalui version control; PRD Word digunakan untuk review dokumen.

## Isi paket

| File | Fungsi |
| --- | --- |
| [PRD_Progress_New_Model.docx](PRD_Progress_New_Model.docx) | PRD versi 2 untuk pembacaan dan review. |
| [PRD_Progress_New_Model.md](PRD_Progress_New_Model.md) | Isi PRD yang sama dalam teks terstruktur untuk AI. |
| [FASE_01_Prototype_dan_Validasi.md](FASE_01_Prototype_dan_Validasi.md) | 12 task prototype dan review pengguna. |
| [FASE_02_Fondasi_Data_dan_Akses.md](FASE_02_Fondasi_Data_dan_Akses.md) | 9 task arsitektur, schema PostgreSQL, autentikasi, dan kontrak service. |
| [FASE_03_Project_dan_Progress.md](FASE_03_Project_dan_Progress.md) | 8 task master, identitas terkini, dan progress dasar. |
| [FASE_04_Problem_Tindakan_dan_Lampiran.md](FASE_04_Problem_Tindakan_dan_Lampiran.md) | 9 task data bertingkat, feedback, foto, dan dokumen. |
| [FASE_05_Draft_Status_dan_Monitoring.md](FASE_05_Draft_Status_dan_Monitoring.md) | 10 task autosave, konflik, Close/Reopen, serta dashboard. |
| [FASE_06_Migrasi_Access.md](FASE_06_Migrasi_Access.md) | 9 task ekstraksi, transformasi, rekonsiliasi, dan rencana cutover. |
| [FASE_07_UAT_dan_Operasi.md](FASE_07_UAT_dan_Operasi.md) | 8 task acceptance, UX, operasi, pilot, dan serah terima. |

File task adalah rencana pekerjaan; checkbox awal belum dicentang. Paket ini tidak menyatakan bahwa prototype, aplikasi, atau migrasi sudah dilaksanakan. Screenshot QA dokumen dan script pembuat paket tidak termasuk deliverable.

## Mulai dari prototype

Buka Fase 01 dan mulai P01-T01. Prototype dibangun dengan Laravel, Blade, Livewire, dan Tailwind agar interaksinya mendekati stack tujuan. Gunakan fixture fiktif dan adapter dummy yang terpisah; schema bisnis PostgreSQL mulai pada Fase 02 setelah prototype diterima. Prototype harus dapat menjalankan tambah problem, beberapa corrective action per problem, Next Action, draft, Simpan, Close/Reopen, pencarian, dan preview foto.

Review pengguna merupakan keluaran nyata fase pertama. Catat bagian yang membingungkan, kesalahan input, kebutuhan bantuan, dan keputusan perbaikan. Jangan membuat catatan persetujuan fiktif untuk melanjutkan fase. Perubahan kebutuhan dari review harus dirangkum dan mendapat konfirmasi sebelum mengubah PRD.

## Cara memberikan konteks kepada AI

Untuk setiap sesi, berikan file PRD Markdown, file fase yang dikerjakan, catatan handoff terakhir, serta akses repository aplikasi. Jika kapasitas konteks model terbatas, berikan bagian PRD yang dirujuk fase dan pertahankan bagian Kontrak yang tidak boleh berubah dalam file fase. Jangan memberikan versi 1 sebagai acuan bersamaan tanpa menjelaskan bahwa versi 2 menggantikannya.

Minta AI mengerjakan satu task atau satu sublangkah yang jelas. Jangan langsung meminta bangun semua aplikasi dari seluruh paket; task mempunyai ketergantungan dan hasil yang harus diperiksa. Setiap task mencantumkan file sasaran, langkah kerja, kriteria selesai, dan verifikasi. Nama path merupakan rancangan organisasi repository; bila repository memakai struktur setara, AI harus mencatat pemetaannya.

```text
Anda membantu implementasi aplikasi Progress New Model.
Acuan utama: PRD_Progress_New_Model.md versi 2.0.
Fase aktif: [NAMA_FILE_FASE]. Task yang diizinkan: [ID_TASK].
Baca kontrak fase dan bagian PRD yang dirujuk. Periksa repository, handoff, serta prasyarat.
Kerjakan hanya task tersebut; jangan menambah fitur, mengubah stack, atau mengubah aturan bisnis.
Jaga pekerjaan yang sudah ada. Hindari reset database yang berisi data dan hindari mengubah produksi.
Jalankan verifikasi yang relevan dan laporkan hasil sebenarnya.
Tulis file yang berubah, apa yang selesai, bukti, masalah tersisa, dan task berikutnya.
Jangan mencentang task yang belum memenuhi kriteria selesai.
Jika PRD perlu berubah, ajukan perubahan konkret dan minta konfirmasi sebelum mengeditnya.
```

## Ketergantungan fase

| Fase | Syarat masuk | Syarat keluar |
| --- | --- | --- |
| 01 | Keputusan kebutuhan dan stack tersedia. | Prototype diterima pengguna; feedback penghambat diselesaikan. |
| 02 | P01-T12 selesai. | Schema, relasi, auth, dan test environment teruji. |
| 03 | P02-T09 selesai. | Master/progress dasar serta autofill benar. |
| 04 | P03-T08 selesai; OP03 ditetapkan sebelum upload operasional. | Graph anak/file aman dan tidak tertukar. |
| 05 | P04-T09 selesai. | Draft, Simpan, status, dan monitoring memenuhi skenario kritis. |
| 06 | P05-T10 selesai dan salinan Access tersedia. | Dry run lengkap direkonsiliasi dan cutover direncanakan. |
| 07 | P06-T09 selesai. | UAT, restore, rilis, pilot, dan serah terima diterima. |

## Penelusuran kebutuhan

| Kelompok kebutuhan | Pelaksanaan utama | Bukti penerimaan |
| --- | --- | --- |
| UI01 sampai UI12 serta alur pengguna | Fase 01, dipertahankan pada Fase 03 sampai 05, diverifikasi Fase 07. | AT35, AT36 dan catatan review pengguna. |
| FR01 sampai FR06 master dan progress | Fase 02 serta Fase 03. | AT01 sampai AT06. |
| FR07 sampai FR11 daftar bertingkat | Fase 02 serta Fase 04. | AT07 sampai AT11, AT16. |
| FR12 sampai FR15 draft dan Simpan | Fase 05. | AT12 sampai AT19, AT37. |
| FR16 Close dan Reopen | Fase 05. | AT20 sampai AT22. |
| FR17 dan FR18 dashboard/filter | Fase 05. | AT23 sampai AT25. |
| FR19 sampai FR21 file dan metadata | Fase 04, integrasi impor Fase 06. | AT26 sampai AT28, AT30. |
| FR22 sampai FR25 migrasi | Fase 06, cutover Fase 07. | AT29 sampai AT34. |
| NF01 sampai NF10 operasi/kualitas | Fase 02, 04, 05, dan 07 sesuai risiko. | AT27, AT35 sampai AT40. |

## Standar hasil UI dan UX

Gunakan daftar kerja dan form sebagai pusat tampilan. Referensi lengkap ada pada PRD bagian 5 sampai 7: tiga angka ringkas, tabel yang terbaca, satu aksen warna, komponen konsisten, semua Activity mempunyai form sama, error dekat field, status autosave nyata, dan tombol Simpan yang jelas. Setiap elemen harus mempunyai fungsi. Jangan mengisi halaman dengan grafik, gradient, kartu besar, badge, atau angka buatan agar terlihat modern.

Tampilan ditinjau pada desktop dan HP. Kerapian visual tidak cukup: pengguna harus dapat menemukan progress lama, mengisi beberapa tindakan tanpa tertukar, memahami draft versus data tersimpan, dan memperbaiki kesalahan tanpa kehilangan isian.

## Pengendalian pekerjaan

Gunakan dokumen docs/progress/P01.md sampai P07.md di repository aplikasi sebagai handoff. Formatnya tersedia di akhir setiap file fase. Catat command dan hasil test yang benar-benar dijalankan. Bug yang menyebabkan data hilang, salah relasi, duplikasi, Close otomatis, atau akses file tanpa login menghalangi rilis.

Parameter OP01 sampai OP05 belum memblokir prototype. Tetapkan ketika fase terkait membutuhkannya. Kumpulkan pertanyaan untuk pemilik kebutuhan sekaligus agar pengembang tidak perlu bolak-balik menanyakan hal kecil. Jangan mengganti ketidakpastian dengan asumsi yang tidak dicatat.

---

## Cara Menjalankan Prototype (Fase 01)

### Prasyarat Lingkungan
- PHP `>= 8.4` dengan ekstensi standar Laravel
- Composer `>= 2.8`
- Node.js `>= 20.x` & npm

### Langkah Instalasi & Menjalankan Aplikasi
1. **Salin environment configuration:**
   ```bash
   cp .env.example .env
   ```
2. **Install dependensi PHP & generate app key:**
   ```bash
   composer install
   php artisan key:generate
   ```
3. **Install dependensi Frontend & build asset:**
   ```bash
   npm install
   npm run build
   ```
4. **Jalankan local development server:**
   ```bash
   php artisan serve
   ```
   *(Opsional jika ingin hot-reload asset Vite: buka terminal terpisah dan jalankan `npm run dev`)*

5. **Akses Prototype:**
   Buka browser ke `http://127.0.0.1:8000/prototype` (atau `http://127.0.0.1:8000/` yang akan mengarahkan otomatis ke prototype saat mode prototype aktif).

### Menjalankan Uji Otomatis
```bash
php artisan test
```

