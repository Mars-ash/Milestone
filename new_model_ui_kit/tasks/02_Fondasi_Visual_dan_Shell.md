# UI-02 — Fondasi visual dan shell

Dependensi: UI-01 selesai. Cakupan: stylesheet, ikon, kontrol dasar, layout dan navigasi; belum mengubah aturan bisnis.

## Bahan

- Peta file aktual dari UI-01.
- Handoff bagian 2–4 dan 11.
- Seluruh `reference/styles.css`; `shell()`, `icon()`, objek `paths`, `status()` pada app.js.

## Langkah

1. Pasang CSS referensi melalui asset pipeline yang sudah ada. Jangan mengubah reference/styles.css itu sendiri. Pertahankan urutan rule dan override akhir.
2. Jika CSS perlu scope, petakan root, dialog, toast, sidebar, main, dan body state agar semua selector tetap bekerja. Periksa efek scope terhadap custom properties dan `.menu-open`.
3. Hubungkan font stack asli. Jangan mengunduh font baru untuk mempercantik. Bandingkan pada font OS yang sama.
4. Pindahkan SVG ikon dengan path, viewBox, stroke 1.7, ukuran, dan nama aksesibel yang sama.
5. Buat/ubah wrapper sidebar fixed, workspace dengan offset sidebar, topbar, breadcrumb, main max-width 1510 px.
6. Sidebar hanya memuat nama teks, label WORKSPACE, dan empat menu. Footer kosong. Tidak ada logo NM, profil, avatar, reset, atau catatan prototype.
7. Header tidak memuat `Prototype · data contoh`. Tanggal fixture tampil sesuai baseline desktop dan disembunyikan pada ≤1000 px.
8. Hubungkan active state menu: editor/detail progress → Progress, detail project → Project.
9. Salin tampilan button, primary/ghost/text, input, textarea, label, required, status, error, toast, dan dialog ke komponen yang sudah ada bila sesuai.
10. Implementasikan drawer pada ≤800 px, hamburger, scrim, aria-expanded, dan close menu saat navigasi. Jangan mengubah semua halaman menjadi sidebar permanent pada mobile.
11. Periksa computed style bila Tailwind/stylesheet global menimpa border, font, input appearance, margin, atau padding.

## Batas

Jangan mengganti fungsi state atau memindahkan upload ke backend baru. Jangan mengubah warna/spacing agar “lebih modern”. Jangan mengaktifkan markup hanya karena selector lama masih ada di CSS.

## Pemeriksaan

- Sidebar pada 1366 px: 220 px. Pada 911 px: 192 px. Pada ≤800 px: drawer, konten tanpa offset kiri.
- Header/tombol/input sesuai ukuran handoff; tidak ada wrapper ganda yang menggandakan padding.
- Fokus keyboard terlihat; tombol ikon mempunyai nama.
- Header kanan tanpa label prototype dan tanpa separator yang tertinggal.

## Kriteria selesai

Shell dan kontrol dasar dapat dibandingkan dengan reference pada viewport yang sama. Screenshot/hasil computed style dicatat bila browser tersedia. Jika belum ada browser, catat bahwa paritas visual belum terbukti.

Task berikutnya: UI-03.
