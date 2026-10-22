# Index task implementasi UI

Task di sini memecah pekerjaan port desain untuk AI dengan konteks terbatas. ID `UI-01` sampai `UI-08` **bukan** ID fase PRD. Task canonical P01 dan PRD tidak diubah atau otomatis ditandai selesai.

Baseline: `reference/`, commit `1ca17d6a58dfd59cf0c7ef4bb5bd58d90ea22002`.

## Urutan

| ID | File instruksi | Dependensi | Hasil konkret |
| --- | --- | --- | --- |
| UI-01 | [01_Pemetaan_Repo_dan_Acuan.md](tasks/01_Pemetaan_Repo_dan_Acuan.md) | Tidak ada | Peta file/binding aktual dan cara menjalankan dua tampilan |
| UI-02 | [02_Fondasi_Visual_dan_Shell.md](tasks/02_Fondasi_Visual_dan_Shell.md) | UI-01 | CSS, ikon, kontrol dasar, sidebar, topbar, wrapper |
| UI-03 | [03_Dashboard_dan_Daftar_Progress.md](tasks/03_Dashboard_dan_Daftar_Progress.md) | UI-02 | Dashboard/Progress dengan tabel dan filter yang sesuai |
| UI-04 | [04_Project_Master.md](tasks/04_Project_Master.md) | UI-02 | Daftar/dialog Project, field yang benar, Model/T0 terpisah |
| UI-05 | [05_Editor_Satu_Halaman.md](tasks/05_Editor_Satu_Halaman.md) | UI-03, UI-04 | Kerangka editor lengkap, autofill, field, savebar |
| UI-06 | [06_Repeater_dan_Draft.md](tasks/06_Repeater_dan_Draft.md) | UI-05 | Repeater ber-ID stabil, normalisasi kosong, draft/apply |
| UI-07 | [07_Detail_Draft_dan_Dialog.md](tasks/07_Detail_Draft_dan_Dialog.md) | UI-04, UI-06 | Detail, kartu Draft, Close/Reopen, dialog, foto |
| UI-08 | [08_Verifikasi_Paritas.md](tasks/08_Verifikasi_Paritas.md) | UI-03–UI-07 | Bukti perbandingan visual dan alur; daftar selisih tersisa |

Kerjakan berurutan dalam satu agent kecuali delegasi memang diizinkan lingkungan dan dipilih pengguna. Pemisahan task bukan perintah membuat subagent. Jangan melakukan refactor backend besar bersama pekerjaan tampilan.

## Aturan tiap task

1. Baca instruksi repository dan handoff sesi terakhir.
2. Baca file task aktif dan bagian referensi yang ditunjuk.
3. Periksa file aktual sebelum mengedit; path tujuan di dokumen merupakan saran bila belum ada komponen setara.
4. Kerjakan langkah yang diizinkan task, pertahankan perubahan pengguna.
5. Periksa alur yang benar-benar terkena perubahan.
6. Catat hasil dengan format pada `PROMPT_IMPLEMENTASI.md`.
7. Jika ada hambatan, catat file/akses/keputusan yang hilang secara spesifik. Jangan mengarang bahwa task sudah selesai.

## Definisi selesai bersama

- Source `reference/` tetap tidak berubah.
- Tidak ada fitur/copy yang dihapus pengguna muncul kembali.
- Tampilan utama memakai urutan DOM, kelas, dan nilai visual acuan.
- Perilaku data yang sudah benar tetap bekerja.
- Pemeriksaan yang dilaporkan benar-benar dijalankan.
- Keterbatasan visual/browser disebutkan bila belum diperiksa.
- Tidak ada PRD, schema, atau status task fase yang diubah tanpa otorisasi yang sesuai.
