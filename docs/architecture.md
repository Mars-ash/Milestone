# Arsitektur fondasi Fase 2

## Batas implementasi

Baseline adalah ZIP yang diberikan pengguna, termasuk perubahan Fase 1 yang belum ada pada commit upstream. Baseline disimpan sebagai commit lokal tersendiri sebelum implementasi; tidak ada reset pekerjaan pengguna. PRD tidak diedit. UI ini adalah UI aplikasi sebenarnya, sesuai instruksi terbaru, dan tidak didesain ulang.

| Lokasi | Tanggung jawab |
|---|---|
| `app/Livewire/Prototype`, view yang sama | UI yang diterima; adapter session terisolasi dan guard di setiap boot Livewire |
| `app/Prototype` | Fixture dan simulasi; tidak diakses route operasional |
| `app/Livewire/Forms/ProgressFormData.php` | Jembatan DTO untuk integrasi fase berikutnya |
| `app/Data` | Kontrak project/progress, normalisasi tanggal kosong, validasi bentuk dan ownership |
| `app/Models` | Relasi eksplisit dan cast DATE/raw text |
| `app/Actions` | Satu operasi per kelas: Simpan, Close, Reopen |
| `app/Services` | Kontrak draft, finalisasi attachment, monitoring |
| `app/Policies`, middleware, controller auth | Otorisasi session admin, akses file privat |
| `database/migrations`, factories/seeders | Schema PostgreSQL, constraint dan fixture fiktif |
| `tests/Feature/Foundation`, `tests/Feature/Auth` | Test integritas dan perilaku database/auth |
| `tests/Browser` | Regresi browser terfokus; bukan bukti lulus sebelum benar-benar dijalankan |
| `tools/access-export` | Ruang alat Fase 6; belum ada importer |

`/` menuju login atau `/app`; `/app` hanya halaman kesiapan fondasi. Tidak mengklaim CRUD operasional telah terhubung. Route `/prototype` memerlukan auth/admin serta local/testing + flag. `PreviewComponent::boot()` menjaga initial render dan message/action Livewire langsung, sehingga route gate saja tidak menjadi satu-satunya pelindung.

## Schema dan integritas

`projects` memiliki JO dan Part No unik masing-masing, trim spasi luar dan tipe teks. Keunikan case-sensitive mempertahankan perbedaan casing untuk review, sesuai FR01; tidak menggabungkan identitas impor otomatis. Input final baru wajib JO, Part No, Part Name. `legacy_incomplete=true` memungkinkan nilai sumber null tanpa fakta buatan. `spec` tetap kolom arsip/master sesuai pemetaan, tetapi tidak muncul di form Project.

`progresses` hanya menyimpan project_id untuk identitas. Koreksi master langsung terlihat melalui relasi; tidak ada snapshot Part/Customer/Model/No Doc. Activity diperiksa database terhadap lima label persis. DATE tidak dikonversi ke UTC; timestamp teknis disimpan dengan zona dan UI menampilkan Asia/Jakarta. Raw ukuran/DH/Qty bertipe text, tanpa float atau perhitungan. Tidak ada PIC Progress, Project User, Status Project, atau target date.

Graph: project → progress → problems → corrective_actions; progress → next_actions dan feedbacks. Semua anak memakai stored ID, UUID `client_id`, position, FK RESTRICT, serta unique `(parent_id, client_id)`. Posisi bukan identitas dan dapat sama selama penyusunan ulang; relasi diurutkan position lalu ID. RESTRICT menghalangi orphan/delete parent; tidak menambahkan fitur hapus berantai UI.

Draft memakai UUID primary key, admin_id, progress_id nullable, base_revision, draft_revision, request_sequence, schema_version, JSONB object, state active/applied, applied_progress_id. Partial unique index membatasi satu draft edit active per admin/progress; draft baru boleh lebih dari satu. Applied harus menunjuk progress, dan draft edit tidak dapat diterapkan ke progress lain. JSON payload lama tetap disimpan bila versi tidak didukung DTO; pemulihannya dikerjakan Fase 5.

Attachment memakai **FK eksplisit yang eksklusif**, bukan owner polymorphic tanpa FK:

- Staging: hanya draft_id; owner_kind progress/problem/feedback dan UUID owner_client_id untuk baris anak baru.
- Attached: tepat satu progress_id/problem_id/feedback_id; draft_id dan owner_client_id null.
- CHECK database menegakkan XOR, state, kind, checksum SHA-256, ukuran positif, serta storage key server `attachments/<uuid>`. Checksum dan ukuran byte yang benar tetap harus diverifikasi service Fase 4.
- DTO mengecek uploader, draft admin, owner kind, client_id/stored parent, jenis file foto/dokumen serta penggunaan referensi ganda. FK tidak dapat sendirian membuktikan staging milik client row dalam JSON; pemeriksaan service ini wajib diulang di dalam transaksi final.
- Disk `private` tidak mempunyai URL publik atau signed local serving. Download hanya route terautentikasi + policy, hanya state attached; header download/no-store/nosniff. File kosong/hilang menghasilkan 404, bukan attachment sukses.

`legacy_document_metadata` terpisah, tepat satu progress/feedback sebagai pemilik, source_field, nama dan keterangan nullable. Tidak ada storage_key, route download, atau byte dokumen lama. `import_batches/import_records` ditunda ke Fase 6; lihat pemetaan 65 kolom.

## Kontrak payload

Root: `schema_version=1`, project_id, activity_date, activity, sebelas field teks kegiatan, problems[], next_actions[], feedbacks[], attachment_refs[]. Tiap baris memakai UUID client_id; stored id harus hadir jika baris sudah tersimpan dan client_id tidak boleh berubah. Nama field DTO `description` sesuai database; adapter UI fase berikutnya memetakan `notes` menjadi description, `date` feedback menjadi feedback_date, ID lokal preview menjadi UUID stabil. Jangan membuat UUID baru pada setiap autosave.

Draft parsial tidak wajib JO, tanggal, atau Activity. Final hanya mewajibkan tiga field itu, ditambah pemeriksaan tipe/relasi. PIC/tanggal tanpa uraian dipertahankan. Tanggal `''` menjadi null; string raw koma/titik tidak diubah. Field tidak dikenal ditolak eksplisit (termasuk snapshot master, pic_progress, target_date, status). Daftar/key yang hilang tetap **hilang** dalam DTO, bukan diubah menjadi `[]` dan bukan instruksi delete. Penghapusan record tersimpan bukan cakupan fondasi.

Test DTO ditempatkan di `tests/Feature/Foundation/PayloadTest.php` (bukan Unit), karena rule exists/unique dan ownership harus memeriksa PostgreSQL nyata. Tidak dibuat kelas Rules paralel untuk pemeriksaan yang sudah memakai Validator Laravel dan DTO terpusat.

`ProjectPayload` memvalidasi tiga field wajib master, unique identity (ignore ID terpercaya saat edit), teks opsional, dan tanggal manual. Spec hanya untuk kontrak data/import; UI Project tetap tidak menampilkannya.

## Kontrak hasil dan transaksi

| Operasi | Input | Hasil/kondisi saat ini |
|---|---|---|
| `ProgressDraftService::save` | User admin, draft UUID, input, expectedRevision, requestSequence, progress/base revision optional | Validasi parsial + auth; **501 FeatureNotReady**. Fase 5 mengisi penulisan CAS/sequence |
| `SaveProgress::handle` | User admin, draft UUID, expected draft revision | Lock draft; auth; applied replay mengembalikan progress yang sama; cek revision dan lock progress; validasi final; **501 untuk graph write yang belum diimplementasikan** |
| `CloseProgress::handle` | User admin, progress ID, expected revision | Transaksi + row lock, cek revision, ubah status/revision saja; tanpa syarat kelengkapan; draft/anak tidak diubah |
| `ReopenProgress::handle` | sama | Transaksi yang sama, isi tetap, revision meningkat bila status berubah |
| `AttachmentFinalizer::finalize` | admin, draft, attachment IDs server | **501**; Fase 4 verifikasi byte/checksum/mime lalu promosi |
| `ProgressMonitoring::list` | admin, filters | Kontrak pagination; **501**, query Fase 5 |

`ProgressResult` memuat model, outcome, pesan; sukses yang sudah ada: closed/open/already_applied. Validasi: `ValidationException` (422 + field errors); auth: AuthenticationException/AuthorizationException (redirect login/401 atau 403); not found 404; `VersionConflict` 409; `UploadNotReady` 422; skeleton 501. Jangan tangkap semua exception lalu menampilkan “tersimpan”.

Batas transaksi final yang harus diisi Fase 5: lock draft → jika applied kembalikan applied_progress_id → cek expected draft revision → lock progress dan cek base_revision + status Open → ulangi validasi ownership → simpan graph secara idempoten dengan mapping client_id → finalisasi referensi staging yang byte-nya sudah terverifikasi → naikkan revision → tandai draft applied + applied_progress_id → commit. Kegagalan anak membatalkan semuanya dan draft tetap active. Tidak memindahkan/menghapus byte di tengah transaksi tanpa mekanisme pemulihan; file immutable privat dapat memakai key yang sama saat metadata ownership dipromosikan. Cleanup fisik setelah commit harus dapat diulang.

AT11–AT22: ID silang/validasi parsial/missing lists sudah diuji di DTO; applied replay, rollback database, konflik revision, Close/Reopen dan pelestarian draft diuji. Penulisan graph final, ordering autosave, CAS dua tab, discard staging, reload draft database, dan koordinasi upload belum diimplementasikan dan **belum dianggap lulus acceptance produk**. Test fixture rollback membuktikan batas transaksi PostgreSQL, bukan klaim Simpan final operasional sudah selesai.
