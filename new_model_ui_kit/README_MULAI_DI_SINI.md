# Mulai di sini — paket UI Progress New Model

Paket ini memuat desain yang disetujui 14 Oktober 2026, beserta source asli dan instruksi penerapannya ke Laravel/Blade/Livewire/Tailwind.

## Langkah singkat

1. Ekstrak ZIP ke folder kerja.
2. Baca `UI_HANDOFF_NEW_MODEL.md`, terutama bagian keputusan final dan elemen yang sudah dihapus.
3. Jalankan `reference/index.html` sebagai website statis. Tidak perlu npm install, build, database, atau bundler untuk referensi ini.
4. Buka aplikasi Laravel yang sudah dikerjakan sampai P01-T09 pada jendela lain.
5. Berikan seluruh folder paket kepada AI, kemudian gunakan prompt awal dari `PROMPT_IMPLEMENTASI.md`.
6. Kerjakan satu file task dalam `tasks/` per putaran. Gunakan checklist pembanding sebelum menandainya selesai.

## Membuka reference

Buka `reference/index.html` langsung untuk melihat tampilan awal. Untuk mencoba draft/penyimpanan browser secara konsisten, gunakan origin HTTP lokal tersendiri melalui static server yang sudah tersedia di komputer.

Jika Python sudah terpasang, dari folder paket dapat menjalankan:

```bash
python -m http.server 8000 --bind 127.0.0.1 --directory reference
```

Lalu buka `http://127.0.0.1:8000`. Gunakan port lain jika 8000 sudah terpakai. Alternatifnya, letakkan folder referensi pada static host lokal terpisah yang biasa dipakai di Laragon. Jangan menimpa root aplikasi Laravel.

Referensi online: https://progress-new-model-ui.quick-panda-6120.chatgpt.site

## Peran source

| File | Fungsi |
| --- | --- |
| `reference/index.html` | Entry point; load CSS, state.js, lalu app.js |
| `reference/styles.css` | Semua token, layout, komponen, dan breakpoint versi final |
| `reference/app.js` | Markup semua halaman, SVG ikon, dan interaksi |
| `reference/state.js` | Fixture dan perilaku state simulasi |

Simpan `reference/` tanpa perubahan sebagai pembanding. Terapkan port di repository Laravel. Jangan memasang `app.js` ini di dalam komponen Livewire sebagai pengganti lifecycle-nya: script tersebut menguasai `#app` sendiri.

## File pendamping

- `TASK_IMPLEMENTASI_UI.md`: urutan kerja dan dependensi.
- `tasks/`: langkah detail setiap task.
- `PROMPT_IMPLEMENTASI.md`: prompt siap tempel.
- `CHECKLIST_PARITAS_UI.md`: hasil yang harus dibandingkan.
- `PETA_SOURCE_UI.md`: letak implementasi acuan.
- `MANIFEST.json`: hash source yang dibekukan.

PRD dan phase checklist asli tidak diubah oleh paket ini. Backend produksi tidak disertakan. Angka/kode/nama pada reference adalah data contoh; upload dokumen hanya simulasi metadata. Kebenaran implementasi Laravel dan tampilan browser tetap perlu diperiksa pada komputer pengembang.
