# UI-06 — Repeater dan draft

Dependensi: UI-05. Risiko utama: data tertukar, row kosong menjadi record, draft langsung mengubah progress, dan penyimpanan ganda.

## Bahan

- Handoff bagian 7.4–7.10, 8, 10, 11.3.
- State: `emptyProblem`, `emptyAction`, `emptyFeedback`, `prepareEditor`, `cleanProgress`, `saveDraft`, `applyDraft`.
- UI: `problemHTML`, `actionHTML`, `canRemove`, `queueDraft`, `flushDraft`, `saveProgress`, handler tambah/hapus/upload.

## Langkah

1. Siapkan ID sementara stabil untuk row baru. Key komponen mengikuti ID entitas/parent yang stabil, bukan nomor urut tampilan.
2. Pastikan hanya problem yang menjadi parent CA. Next Action dan feedback berada pada progress.
3. Tambah problem membuat satu CA. Tambah CA/Next Action/feedback memfokuskan uraian baru dan mempertahankan row lama.
4. Hapus row baru hanya jika diizinkan; dialog konfirmasi dan tindakan hanya memengaruhi row tersebut. Jangan menampilkan kontrol hapus record tersimpan sebagai fitur baru.
5. Terapkan normalisasi kosong sesuai tabel handoff. PIC/date-only, foto-only, dokumen-feedback-only, dan parent dengan child terisi tetap disimpan.
6. Jangan menghapus record lama secara otomatis ketika nilainya dikosongkan. Jangan memutasi editor aktif saat normalisasi sehingga binding bergeser.
7. Pisahkan state draft dan progress yang sudah diterapkan. Membuka editor saja tidak membuat draft. Ketikan memicu autosave sesuai adapter aplikasi.
8. Status simpan mengikuti respons nyata. Indikator idle kosong; saat menyimpan/berhasil/gagal tampil sesuai keadaan. Retry tersedia bila gagal.
9. Navigasi keluar menyelesaikan draft tertunda. Jika gagal, pertahankan isian dan jalan pemulihan.
10. Apply memvalidasi JO/tanggal/Activity, menulis sekali, membersihkan draft yang telah diterapkan, lalu membuka ringkasan. Double-click/retry tidak membuat duplikasi.
11. Guard Closed berlaku pada membuka editor dan apply, termasuk ketika status berubah sesudah draft dibuat.
12. Peta upload mempertahankan pemilik file. Gunakan uploader/storage aplikasi; file metadata demo dan base64 localStorage hanya pada reference.

## Skenario pemeriksaan berisiko

| Skenario | Hasil wajib |
| --- | --- |
| Simpan hanya JO/tanggal/Activity | Satu Open; tidak ada child kosong |
| Dua problem × dua CA, PIC/tanggal berbeda | Empat CA tetap di parent dan nilai yang benar |
| Satu CA hanya berisi PIC | Tetap ada setelah draft/apply |
| Next Action hanya berisi tanggal | Tetap ada, tidak menjadi CA |
| Problem hanya berisi foto | Parent tidak dibuang |
| Feedback hanya berisi file | Feedback tidak dibuang |
| Keluar sebelum Apply, buka draft lagi | Data pulih; progress tersimpan belum berubah |
| Simpan dua kali / retry gagal | Tidak ada progress/child ganda |
| Kegagalan autosave/apply | Tidak muncul sukses palsu; isian tetap dapat dipulihkan |
| Closed dengan draft aktif | Baca versi tersimpan; wajib Reopen sebelum melanjutkan edit |

Gunakan test state yang sudah tersedia atau pemeriksaan perilaku terarah untuk risiko ini. Jangan membuat suite besar yang hanya memeriksa nama class CSS.

## Kriteria selesai

Semua skenario yang terkait perubahan lolos dan bukti dicatat. UI tetap satu halaman dengan baris pertama siap ketik; perbaikan state tidak menambah langkah input atau syarat Close baru.

Task berikutnya: UI-07.
