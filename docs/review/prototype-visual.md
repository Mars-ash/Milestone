# Laporan Evaluasi Visual dan Aksesibilitas — Prototype Fase 01 (P01-T03)

Tanggal: 13 Oktober 2026  
Status: Terverifikasi (Lolos Semua Kriteria PRD UI01 s/d UI12 & NF06)  
URL: `http://127.0.0.1:8000/prototype`

---

## 1. Rangkuman Verifikasi Breakpoint

| Breakpoint | Resolusi Uji | Perilaku Layout | Status |
|---|---|---|---|
| **Desktop Lebar** | 1366 &times; 768 px | Header penuh dengan PROTOTYPE badge, 4 kartu ringkasan kerja, tab navigasi horizontal, tabel desktop dengan tinggi baris 44–52px. | **Lolos** |
| **Laptop Standar** | 1024 &times; 768 px | Padding kontainer menyesuaikan secara proporsional, teks dan kontrol tetap terbaca jelas tanpa layout breaking. | **Lolos** |
| **Tablet** | 768 &times; 1024 px | Grid ringkasan beralih ke 2 kolom &times; 2 baris, tabel tetap terstruktur rapi. | **Lolos** |
| **Mobile HP** | 390 &times; 844 px (iPhone) | Tabel desktop secara otomatis bertransformasi menjadi **ringkasan baris (card summary)** vertikal. Tidak ada scrollbar horizontal yang merusak layar. Touch target berukuran &ge; 44px. Input teks font-size 16px mencegah auto-zoom. | **Lolos** |

---

## 2. Pematuhan Panduan Desain PRD Bagian 6 (UI01 s/d UI12)

1. **Palet Warna & Kontras (UI04, UI05):**
   - Latar belakang: `#F5F6F8` (Canvas bersih)
   - Permukaan kartu/tabel: `#FFFFFF`
   - Teks utama: `#18212B` (kontras rasio > 7:1)
   - Teks sekunder/label: `#536170`
   - Aksen fungsional utama: `#2459A6` (Navy Blue)
   - Status Open: Hijau fungsional (`#0E6B38` / `#EAF7EE`)
   - Status Closed: Netral (`#536170` / `#F0F2F5`)
   - Status Draft: Amber (`#8A5B00` / `#FEF7E6`)
2. **Tanpa Dekorasi Berlebih (UI03, UI12):**
   - Tidak ada gradient dekoratif, glassmorphism, ilustrasi marketing, atau kartu statistik berlebihan.
   - Area kerja sepenuhnya didominasi daftar kerja aktual.
3. **Pustaka Komponen UI (`resources/views/components/ui/`):**
   - `button.blade.php`: Variasi Primary, Secondary, Outline, Danger, Ghost.
   - `badge.blade.php`: Status Open, Closed, Draft, dan 5 Activity (Trial, Sent Sample, Send Document, Audit, Others).
   - `input.blade.php` & `select.blade.php`: Label selalu terlihat (bukan placeholder), pesan error tepat di bawah field berbahasa Indonesia.
   - `autosave-status.blade.php`: Status real-time (Menyimpan draft, Draft tersimpan HH:mm, Draft belum tersimpan, Gagal).
   - `thumbnail.blade.php`: Thumbnail seragam dengan badge `[SIMULASI]` dan lightbox dialog modal.
   - `modal.blade.php`: Dialog modal dengan focus trap, tombol Batal, dan penutupan via tombol Escape keyboard.

---

## 3. Aksesibilitas Keyboard (Tab Navigation)

- **Fokus Terlihat:** Seluruh elemen interaktif (`button`, `a`, `input`, `select`, `textarea`) memiliki outline fokus terlihat: `outline: 2px solid #2459A6; outline-offset: 2px;`.
- **Navigasi Keyboard:** Berpindah antar tombol dan tab dapat dilakukan secara berurutan dengan tombol Tab dan Shift+Tab.
- **Escape Key:** Dialog modal tertutup secara responsif saat menekan tombol `Escape`.

---

## 4. Bukti Rekaman Sesi Browser

- Sesi verifikasi browser otomatis tersimpan pada artifact:  
  `prototype_visual_p03_1791871625824.webp`

---

## 5. Evaluasi Visual & Form Interaktif (P01-T06: 1 Form Semua Activity)

- **Verifikasi Kontrak FR04 / AT05:** 1 Form terpadu digunakan untuk 5 pilihan Activity (`Trial`, `Sent Sample`, `Send Document`, `Audit`, `Others`). Mengganti Activity terbukti mempertahankan isian parameter lapangan (Mesin, DH, Process Trial, Qty, Event Note, Remark).
- **Autofill Identitas Terkini (FR02, AT03):** Memilih JO No menampilkan 5 kartu identitas hanya-baca (Part No, Part Name, Customer, Model, No Doc) yang diambil secara dinamis dari Project Master.
- **Dukungan Format Raw Text (FR05, AT06):** Input PIC teks bebas tanpa ketergantungan akun/tabel user; angka desimal dengan koma dan titik (`120,5 mm`, `1,5`) tersimpan apa adanya.
- **Responsivitas Form Mobile (390x844):** Seluruh input, textarea, sub-tab problem/action/feedback/lampiran, dan tombol aksi terstruktur vertikal tanpa overflow horizontal; tinggi tombol &ge; 44px memenuhi standar touch-target.
- **Bukti Rekaman Sesi Browser Form:** `prototype_form_test_1791876041924.webp`
- **Screenshots:**
  - Desktop Draft Saved: `draft_saved_desktop_1791876183635.png`
  - Mobile Form Top: `mobile_layout_top_1791876191714.png`
  - Mobile Form Bottom: `mobile_layout_bottom_1791876196625.png`

---

## 6. Evaluasi Komponen Bertingkat dan Galeri Foto (P01-T07)

- **Struktur Dua Tingkat Problem & Corrective Action (FR07, AT07, D06):** Problem terbukti dapat ditambahkan berulang dengan penomoran urut label dinamis (Problem #1, #2). Corrective Action bersarang di dalam problem masing-masing dengan PIC teks bebas dan tanggal aktual (tanpa target date — D09, D16).
- **Stabilitas Kunci & Independensi Graf:** Pengisian PIC berbeda pada beberapa tindakan dan penambahan problem baru terbukti tidak menukar nilai, tindakan, atau lampiran foto (AT07).
- **Next Action sebagai Saudara Problem (FR08, AT08, D07):** Next Action berdiri langsung pada level progress secara mandiri tanpa terikat pada problem tertentu.
- **Feedback Customer Berulang (FR09, AT09, D08):** Riwayat evaluasi customer dapat ditambahkan berulang dengan tanggal, PIC evaluator, dan uraian isi tanggapan.
- **Galeri Foto & Lightbox Modal (FR10, FR11, FR19, AT10, AT26, D12):** Lampiran foto per problem menampilkan badge `[SIMULASI]`, input caption, serta dialog Lightbox modal yang dapat diperbesar dan ditutup via tombol Escape keyboard (UI10).
- **Bukti Rekaman Sesi Browser:** `prototype_nested_test_1791876835358.webp`
- **Screenshots:**
  - Lightbox Modal Open: `lightbox_open_1791877040391.png`
  - Form Loaded: `initial_form_loaded_1791876864025.png`

---

## 7. Evaluasi Autosave Draft dan Simpan Final (P01-T08)

- **Pemisahan Draft dari Data Tersimpan (PRD D11, FR12, AT12, AT13):** Perubahan input form memicu autosave reaktif ke Draft tanpa mengubah data progress resmi tersimpan di dashboard dan tanpa mempengaruhi hitungan Open/Closed.
- **Dirty Check & Form Baru Kosong (AT14):** Membuka form baru tanpa mengisi perubahan terbukti tidak membuat entri draft kosong yang mengotori daftar draft.
- **Indikator Autosave & Retry (UI07, AT37):** Komponen `save-state.blade.php` menampilkan status visual real-time "Draft tersimpan [HH:mm]", "Menyimpan draft...", serta status "Gagal menyimpan draft" dengan tautan aksi Coba Lagi (Retry).
- **Manajemen Daftar Draft (FR14, FR15, AT15, AT16, AT19):** Komponen `DraftList` menampilkan seluruh draft aktif (Draft Baru vs Draft Perubahan), tombol Lanjutkan Draft yang memuat ulang data ke form secara utuh, serta tombol Buang Draft dengan modal dialog konfirmasi yang aman.
- **Simpan Final (FR13, AT17, AT18):** Eksekusi Simpan Progress menerapkan perubahan ke data operasional dan secara otomatis menghapus record draft terkait.
- **Bukti Rekaman Sesi Browser:** `prototype_draft_test_1791877784002.webp`
- **Screenshots:**
  - Draft List Desktop: `draft_list_desktop_1791877875574.png`
  - Form Draft Loaded: `form_with_draft_loaded_ok_1791878049637.png`
  - Autosave Header Status: `form_autosave_indicator_1791878082210.png`
  - Dashboard Keberlanjutan Data Lama: `dashboard_after_draft_1791878095265.png`
  - Modal Konfirmasi Buang Draft: `draft_discard_modal_1791878191064.png`
  - Draft List Mobile (390x844): `draft_list_mobile_390x844_1791878204540.png`
---

## 8. Evaluasi Detail Progress, Close, dan Reopen (P01-T09)

- **Keputusan Eksplisit Admin (FR16, D10, AT20):** Tombol "Tutup Progress (Close)" tersedia dan sukses menutup catatan progress berstatus Open tanpa mempersyaratkan kelengkapan data (actual date, tindakan perbaikan, atau catatan feedback tetap diizinkan kosong). Hitungan statistik dashboard langsung beralih secara akurat (Open berkurang 1, Closed bertambah 1).
- **Mode Read-Only & Proteksi Status Closed (AT21):** Progress dengan status Closed menampilkan banner peringatan read-only (`Progress ini berstatus Closed (Read-Only)`). Tombol "Edit Progress" otomatis dinonaktifkan (`Edit Dinonaktifkan (Closed)`) dan tombol "Buka Kembali (Reopen Progress)" dimunculkan. Mencoba memanggil aksi `save()` pada form berstatus Closed ditolak dengan pesan validasi yang jelas.
- **Integritas Data Saat Reopen (AT22):** Mengklik "Buka Kembali (Reopen Progress)" mengembalikan status menjadi Open tanpa mereset data. Seluruh problem, tindakan perbaikan bersarang, lampiran foto, parameter mesin/DH, next action, dan feedback customer tetap utuh 100%. Mode edit kembali aktif.
- **Isolasi Efek (D02, D10):** Perubahan status progress terisolasi penuh; record master project tidak terpengaruh dan progress kegiatan lain tidak berubah status.
- **Responsivitas Mobile (390x844):** Tampilan halaman detail menyusun kartu-kartu identitas, parameter, problem, dan action secara vertikal rapi, bebas overflow horizontal, serta tombol aksi membungkus dengan touch-target &ge; 44px.
- **Bukti Rekaman Sesi Browser:** `prototype_close_reopen_1791878811167.webp`
- **Screenshots:**
  - Detail Open Desktop: `detail_open_desktop_1791878822663.png`
  - Detail Closed Desktop: `detail_closed_desktop_1791878998279.png`
  - Detail Reopened Desktop: `detail_reopened_desktop_1791879013263.png`
  - Detail Mobile (390x844): `detail_mobile_390x844_1791879024268.png`

