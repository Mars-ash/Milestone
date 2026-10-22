# UI-07 — Detail, Draft, dan dialog

Dependensi: UI-04 dan UI-06.

## Bahan

- Handoff bagian 6.3, 8.4, 9.
- `detailHTML`, `draftsHTML`, `projectDetailHTML`, `openDialog`, `confirmClose`, `setStatus`.
- Handler Close, Reopen, discard draft, foto, dan master dialog.

## Langkah

1. Pindahkan ringkasan progress: header identitas dan status, blok Aktivitas → Problem/CA → Next Action → Feedback → Lampiran.
2. Pindahkan aside identitas dan rincian trial. Tidak ada PIC Progress. PIC Customer tetap tersedia.
3. Ringkasan membaca data tersimpan, bukan draft aktif. Beri catatan draft sebagaimana referensi.
4. Pindahkan tombol status: Open mempunyai Close dan Edit/Lanjutkan draft; Closed mempunyai Reopen.
5. Close manual melalui konfirmasi, tanpa syarat tanggal action atau feedback. Draft yang belum diterapkan dipertahankan. Reopen tidak menghapus child.
6. Halaman Draft hanya judul Draft + Tambah progress. Pindahkan kartu, label baru/perubahan, informasi project/waktu, Continue dan discard.
7. Draft pada Closed menuju ringkasan; tidak melewati guard edit. Buang draft tidak menghapus progress.
8. Pindahkan detail Project: jadwal, spesifikasi, histori, edit, tambah progress dengan JO terpilih.
9. Pertahankan identitas data pada header detail. Penghapusan helper pada halaman daftar tidak menghapus JO/customer/model di detail.
10. Pindahkan dialog konfirmasi, dialog foto, toast, focus/escape behavior yang sesuai komponen aplikasi.
11. Pertahankan kondisi empty state untuk problem/action/feedback/file/draft yang tidak ada. Jangan menyimpan row kosong untuk menghindari tampilan empty state.
12. Jangan mengaktifkan kembali reset data contoh, profile, atau label prototype di shell.

## Pemeriksaan

- Buka p1: dua problem, empat CA, tiga Next Action, satu feedback terbaca dengan benar.
- Buat draft perubahan; ringkasan tetap versi tersimpan.
- Close dengan actual date kosong; Closed, semua catatan tetap ada.
- Reopen dan edit; data tetap sama.
- Buang draft: jumlah draft turun, progress tetap utuh.
- Klik thumbnail: gambar milik problem yang dipilih, dialog dapat ditutup.
- Detail project → Tambah progress: JO sudah terpilih dan autofill benar.
- Dialog tidak melampaui viewport atau menutupi tombol footer pada konten panjang.

## Kriteria selesai

Seluruh rute baca/edit/draft/status konsisten dengan referensi. Tidak ada status otomatis, kehilangan relasi, atau tombol baru yang mengubah workflow.

Task berikutnya: UI-08.
