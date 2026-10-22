# UI-08 — Verifikasi paritas tampilan dan alur

Dependensi: UI-03 sampai UI-07 selesai. Cakupan: temuan konkret dan perbaikan selisih; bukan kesempatan mendesain ulang.

## Bahan

- `UI_HANDOFF_NEW_MODEL.md` bagian 2.1, 3, 12, 13.
- `CHECKLIST_PARITAS_UI.md`.
- Reference utuh, source aplikasi, serta handoff sesi UI-01–UI-07.

## Persiapan

1. Pastikan reference belum diubah dan commit/hash sesuai manifest.
2. Buka reference dan aplikasi dengan data/state sama. Gunakan browser/OS/font yang sama, zoom 100%, viewport sama.
3. Pakai data contoh terpisah dari data operasional. Jangan menghapus data pengguna untuk mereset fixture.
4. Gunakan perangkat browser/testing yang memang tersedia. Jangan mengarang screenshot atau hasil QA bila alat tidak tersedia.

## Langkah pemeriksaan

1. Bandingkan dashboard, Progress Semua, Project, dialog tambah Project, Draft, form tambah progress, edit kompleks p1, ringkasan Closed, dan detail Project.
2. Mulai pada 1475×722 atau 1366×768, lalu 911×722 dan 390×844. Periksa breakpoint tambahan hanya jika menemukan risiko yang spesifik.
3. Cek posisi/ukuran sidebar, topbar, main, panel, tabel, field, judul, tombol, label, ikon, border, dan spacing. Periksa juga jumlah baris teks yang terbungkus.
4. Audit daftar elemen yang telah dihapus. Tidak boleh ada logo NM, footer sidebar, label prototype, PIC Progress, Spec input, tab editor, atau helper lama.
5. Periksa Model/T0 pada 911 px. Bila ada selisih di breakpoint, bandingkan 1001/1000. Header dan sel tidak bergeser.
6. Ulangi alur pencatatan lengkap dengan problem+CA+Next Action tanpa tombol persiapan baris pertama. Tambahkan feedback belakangan tanpa membuat record baru yang tidak perlu.
7. Jalankan skenario draft/status/error yang terpengaruh perubahan. Tidak perlu mengulang seluruh test backend yang tidak berhubungan.
8. Periksa Tab/Shift+Tab, dropdown, shortcut save, fokus error, dialog Escape/tutup, add-row focus, dan fokus yang tidak tertutup footer.
9. Cek no-horizontal-overflow, No Doc tetap tersedia, textarea panjang, dua problem dengan teks panjang, dan file name panjang.
10. Perbaiki hanya selisih yang tercatat. Bandingkan ulang layar/alur yang terkena perbaikan.

## Format temuan

| ID | Layar/state | Viewport | Harapan dari reference | Aktual | File penyebab | Perbaikan | Bukti setelah perbaikan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Isi | Isi | Isi | Isi | Isi | Isi | Isi | Isi |

## Kriteria selesai

- Semua pemeriksaan relevan pada checklist mempunyai hasil dan bukti nyata.
- Tidak ada perbedaan struktur, field hilang, header tertukar, atau alur input yang bertambah tanpa persetujuan.
- Perbedaan rendering font/native control yang tidak dapat disamakan karena lingkungan dicatat secara spesifik.
- Handoff akhir mencantumkan file aktual, hasil pemeriksaan, batas verifikasi, dan kebutuhan review pengguna bila masih ada.
- Jangan menandai PRD/fase UAT selesai hanya karena kode UI sudah terpasang. Jangan memakai persetujuan UI reference sebagai klaim penerimaan semua backend/perangkat.

Jika browser tidak tersedia: selesaikan pemeriksaan source/state yang memungkinkan, laporkan visual sebagai belum diverifikasi, dan jangan mengklaim identik.
