# UI-04 — Project Master

Dependensi: UI-02. Disarankan setelah UI-03 agar pola tabel sudah tersedia.

## Bahan

- Handoff bagian 6 dan tabel kontrak data bagian 10.
- `projectsHTML`, `masterDialog`, `saveMaster`; selector `.project-table` dan override akhir CSS.

## Langkah

1. Halaman daftar hanya menampilkan judul Project Master dan tombol Tambah project. Hapus eyebrow/subtitle lama.
2. Render enam kolom: Project/Part, Customer, Model, T0 Target, Progress, Buka. Header dan body harus mempunyai urutan/jumlah sama.
3. Bind Model ke field kode teks. Bind T0 Target ke field tanggal tersendiri. Gunakan `.project-model` dan `.project-target`; jangan gunakan `.customer-col` lama pada Model.
4. Di mobile beri label Model dan T0 Target terpisah seperti `data-label` pada referensi. Model tidak boleh disembunyikan sementara header tetap ada.
5. Adaptasi dialog tambah/edit: tiga kelompok Identitas, Material & proses, Jadwal; Remark Project setelahnya. Gunakan tabel field handoff, total 21 input, tanpa Spec.
6. Model tetap input text, placeholder Contoh: K2SJ, HK09, RS8. Jangan membuat regex yang hanya mengizinkan tiga contoh tersebut.
7. Pertahankan JO/Part No unik serta tiga field wajib. Validasi tidak menutup dialog atau mengosongkan data.
8. Simpan mengikuti adapter aplikasi yang sudah ada. Data ukuran koma/titik tidak diformat ulang tanpa kebutuhan.
9. Setelah edit master, identitas di progress menggunakan nilai terbaru. Jangan menyalin master secara stale ke payload editor sebagai sumber kebenaran.
10. Data Spec lama jangan dihapus/migrasikan pada task UI. Ketiadaan input bukan izin drop column.

## Pemeriksaan wajib untuk bug Model

Isi tiga contoh project test dengan kode K2SJ, HK09, RS8 dan T0 Target berbeda. Periksa kolom pada desktop, viewport 911 px, dan mobile. Jika browser tersedia, cek juga 1001/1000 px karena rule lama rusak di breakpoint tersebut. Model selalu kode; T0 selalu di bawah label tanggalnya sendiri.

## Kriteria selesai

- Daftar dan dialog sesuai baseline, tanpa field Spec dan tanpa caption lama.
- Edit/simpan Model mempertahankan teks, termasuk kode yang tidak sama dengan contoh.
- Perbaikan responsive tidak menyembunyikan identitas project lain.
- PRD, schema, dan histori data tidak diubah sebagai efek samping.

Task berikutnya: UI-05.
