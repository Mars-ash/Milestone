# UI-05 — Editor satu halaman

Dependensi: UI-03 dan UI-04. Fokus task ini: struktur dan binding semua field. Perilaku repeater/draft dipastikan pada UI-06; jangan mematikan perilaku yang sudah bekerja.

## Bahan

- Handoff bagian 7, tabel field, aturan penghapusan bagian 2.1.
- `startEditor`, `editorHTML`, `sectionHTML`, `field`, `growTextarea`.
- Blok CSS mulai komentar Editor: satu lembar kerja hingga override akhir.

## Langkah

1. Pindahkan page header editor, tautan kembali, status, Lihat ringkasan untuk edit, serta penanda field wajib.
2. Pindahkan panel JO/autofill. Kelima identitas harus tersedia termasuk No Doc; Model teks. Perubahan JO menyegarkan identitas tanpa kehilangan isian lain.
3. Render kelima section sekaligus dan dalam urutan acuan. Jangan gunakan tab, accordion, lazy mount yang mengosongkan bagian, atau langkah wizard.
4. Pindahkan field Aktivitas dan subkelompok trial sesuai urutan tabel handoff. Tidak ada PIC Progress.
5. Sediakan kerangka satu problem + satu CA, satu Next Action, dan satu feedback. Gunakan mekanisme state aplikasi, bukan HTML placeholder di luar binding.
6. Pindahkan baris tindakan sejajar pada desktop serta susunan stacked pada mobile.
7. Pindahkan feedback tanpa helper kapan diisi. File feedback berada pada entri feedback masing-masing.
8. Pindahkan Remark Progress dan lampiran progress ke section terakhir.
9. Pasang savebar fixed, tombol Ke feedback dan Simpan progress. Pada form belum disentuh tidak ada pesan/ikon autosave. Jangan kembalikan dua teks footer yang sudah dihapus.
10. Ke feedback menggulir dan memfokuskan heading tanpa membuat record. Shortcut Ctrl/Cmd+Enter hanya berlaku saat editor aktif dan tidak ada dialog.
11. Pertahankan label, required, inline error dan error summary. Error memfokuskan field pertama yang perlu dilengkapi.
12. Pindahkan pertumbuhan textarea dengan maksimum 320 px tanpa membuat rerender seluruh form pada setiap ketikan. Pertahankan posisi scroll/fokus.

## Pemeriksaan

- Buka Tambah progress. Semua 24 binding awal tersedia, termasuk field tiga repeater pertama, tanpa klik Tambah.
- Ganti kelima Activity; semua section dan isi tetap ada.
- Pilih JO; Part Name/No, Customer, Model, No Doc benar dan read-only.
- Periksa font input 14 px desktop / 16 px mobile sesuai override, grid trial 4→2 kolom, dan editor head 26→24 px.
- Gulir sampai Catatan & lampiran; field terakhir tidak tertutup footer.
- Ke feedback tidak membuka layar baru.

## Kriteria selesai

Editor satu halaman dapat diisi pada semua field dan tampil seperti baseline. Belum boleh menyatakan alur lengkap selesai sebelum UI-06 memeriksa normalisasi, autosave, apply, dan relasi repeater.

Task berikutnya: UI-06.
