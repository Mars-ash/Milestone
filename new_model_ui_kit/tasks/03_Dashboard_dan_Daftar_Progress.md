# UI-03 — Dashboard dan daftar Progress

Dependensi: UI-02 selesai.

## Bahan

- Handoff bagian 2.1 dan 5.
- `dashboardHTML`, `summaryHTML`, `filtered`, `tableHTML`, `updateTable`.
- Handler `data-nav`, `data-status-filter`, `data-open-progress`, filter input/change, sort/reset.

## Langkah

1. Judul dashboard menjadi Aktivitas New Model; halaman Progress menjadi Semua progress. Hilangkan semua eyebrow/subtitle yang tercatat di daftar penghapusan.
2. Tempatkan Lanjutkan draft dan Tambah progress di kanan header seperti referensi.
3. Render satu panel ringkasan tiga kolom. Nilai berasal dari data keseluruhan, tidak dari hasil filter. Draft tidak ikut Open/Closed.
4. Render panel Daftar progress tanpa paragraf penjelas. Pertahankan tab status dengan counter dan active underline.
5. Hubungkan pencarian JO/Part No/Part Name, filter Activity/Customer, filter PIC/peran/tanggal, dan urutan tanggal.
6. Salin urutan dan proporsi tabel 14/32/16/33/5. Jangan tambahkan kolom PIC Progress.
7. Tampilkan Next Action pertama, baris tambahan +N, dan status pada tab Semua.
8. Klik Open langsung editor; klik Closed ringkasan. Simpan filter saat kembali.
9. Hubungkan Reset dan empty state. Reset status mengikuti halaman aktif sebagaimana handoff.
10. Pertahankan tautan Lihat Project Master di bawah tabel; jangan kembalikan helper Open/Close.
11. Sesuaikan kartu mobile mengikuti CSS. Jangan memasang tabel desktop yang terlalu lebar atau menyembunyikan seluruh Next Action.

## Pemeriksaan data

Dengan fixture awal: dashboard 6 Open, 2 draft, 8 aktivitas Oktober; tab Semua 8 dan Closed 2. Gunakan fixture yang sama untuk membandingkan, bukan angka hard-coded pada template Laravel.

Cari bagian JO/part/name, gabungkan dengan Activity dan Customer, buka satu row, kembali, lalu periksa filter tetap aktif. Coba tanpa hasil lalu Bersihkan filter. Jangan menulis filter PIC terhadap field progress.pic yang sudah dihapus.

## Kriteria selesai

- Header, ringkasan, filter, tabel, footer, empty state sesuai referensi.
- Perubahan filter tidak menghilangkan state halaman lain atau mereset data.
- Open tidak membutuhkan halaman perantara sebelum edit.
- Tidak ada tooltip/helper baru yang menggantikan teks yang dihapus.

Task berikutnya: UI-04.
