# UI-01 — Pemetaan repository dan acuan

Status awal: belum diverifikasi pada aplikasi Laravel. Dependensi: tidak ada.

## Tujuan

Mengetahui file dan adapter yang benar-benar digunakan agar port UI tidak membuat aplikasi kedua atau menimpa pekerjaan P01-T01–T09.

## Bahan

- Instruksi repository/AGENTS.md, handoff P01 terakhir.
- `UI_HANDOFF_NEW_MODEL.md` bagian 1–4, 10–13.
- `MANIFEST.json`, `PETA_SOURCE_UI.md`, empat file `reference/`.
- Route, Blade, class Livewire, CSS entry, lockfile, fixture, service, dan test yang benar-benar tersedia.

## Langkah

1. Periksa status Git. Catat perubahan pengguna yang sudah ada; jangan reset atau menimpa.
2. Catat versi PHP, Laravel, Livewire, Tailwind dari dependency/lockfile aktual. Jangan upgrade dependency.
3. Temukan route prototype dan layout yang merender halaman setelah P01-T09. Gunakan pencarian file/text repository, bukan menebak nama class.
4. Identifikasi setiap layar: dashboard, progress, project, detail project, editor, detail progress, draft, dialog project.
5. Petakan data Project/Progress/Problem/CA/Next Action/Feedback dan adapter draft/apply/status yang ada. Jika tahap P01 memakai fixture, catat itu; jangan membuat schema produksi baru.
6. Petakan binding setiap field yang tercantum pada handoff. Tandai gap nyata, terutama PIC Progress lama, input Spec, Model/T0, dan repeated action.
7. Temukan CSS yang masuk ke halaman, style global, reset/Tailwind Preflight, plugin form/table, font, dan icon set yang berpotensi mengubah referensi.
8. Jalankan aplikasi dengan cara yang sudah didokumentasikan. Buka referensi melalui origin lokal terpisah. Jangan menimpa public root aplikasi dengan reference.
9. Jika alat browser tersedia, simpan screenshot pembanding internal halaman saat ini. Jika tidak, catat keterbatasannya.
10. Tulis peta file aktual dan strategi pemindahan minimum. Pilih penggunaan ulang komponen yang ada sebelum membuat komponen baru.

## Keluaran

Catatan mapping di lokasi dokumentasi repository yang sudah digunakan. Isinya harus mencakup:

| Area | File aktual | Fungsi referensi | Data/binding/action saat ini | Perubahan UI yang diperlukan |
| --- | --- | --- | --- | --- |
| Shell | Isi hasil inspeksi | shell | Isi hasil inspeksi | Isi hasil inspeksi |
| Dashboard/Progress | Isi hasil inspeksi | dashboardHTML/tableHTML | Isi hasil inspeksi | Isi hasil inspeksi |
| Project | Isi hasil inspeksi | projectsHTML/masterDialog | Isi hasil inspeksi | Isi hasil inspeksi |
| Editor/repeater | Isi hasil inspeksi | editorHTML/sectionHTML | Isi hasil inspeksi | Isi hasil inspeksi |
| Draft/status | Isi hasil inspeksi | saveDraft/applyDraft/setStatus | Isi hasil inspeksi | Isi hasil inspeksi |

## Kriteria selesai

- Setiap layar dan binding penting memiliki file/owner aktual atau gap yang spesifik.
- Cara start aplikasi dan referensi diketahui.
- Pilihan stylesheet dan urutan load ditetapkan berdasarkan file aktual.
- Tidak ada perubahan desain, schema, versi framework, atau PRD pada task ini.

Task berikutnya: UI-02. Jika source aplikasi belum diberikan, laporkan kebutuhan source tersebut; jangan mengarang implementasi Laravel lengkap dari fixture JavaScript.
