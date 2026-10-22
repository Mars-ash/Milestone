# Kesiapan Aplikasi untuk Migrasi Data (Release Readiness)

Dokumen ini memverifikasi kesiapan teknis dan operasional basis data serta antarmuka aplikasi sebelum migrasi data Access dijalankan pada Fase 06 (Acuan: PRD v2.0 Bagian 4, 10, 11, 17; D01–D20; AT01–AT28).

---

## 1. Verifikasi Status Gate (Fase 02 – Fase 05)

| Fase | Fokus & Hasil | Status Gate |
| :--- | :--- | :--- |
| **Fase 02** | Fondasi Data & Akses (Single Admin, Auth Session, Project Master & Unik JO No, PostgreSQL 18 Schema). | **LULUS** (AT01–AT03, AT29, AT30) |
| **Fase 03** | Project & Progress (Aktivitas 5 Macam, Tanggal Manual, Identitas Master Terkini, Raw Numeric Text). | **LULUS** (AT04–AT06, AT31–AT34) |
| **Fase 04** | Problem, Tindakan, Lampiran Privat & Metadata Arsip (Graph bertingkat, Staging, Rollback Atomik). | **LULUS** (AT07–AT11, AT26–AT28, AT35, AT36) |
| **Fase 05** | Draft Status & Monitoring (Autosave Parsial, Idempotensi, Two-tab Concurrency, Close/Reopen, KPI & Filter). | **LULUS** (AT12–AT25, AT37) |

---

## 2. Pengecekan Scope yang Dihilangkan (Out of Scope Adherence)

Sesuai keputusan arsitektur dan bisnis PRD v2.0:
1. **Tidak Ada Status Project & Project User:**
   - Kolom `status` dan `user` pada project master telah sepenuhnya dihilangkan. Status `Open`/`Closed` mutlak hanya milik record `progress`.
2. **Tidak Ada Master PIC:**
   - Semua PIC pada corrective action, next action, feedback, dan progress adalah teks bebas (`varchar`/`text`). Tidak ada tabel master PIC maupun relasi foreign key ke tabel pegawai.
3. **Tidak Ada Target Date pada Action:**
   - Corrective Action dan Next Action hanya memiliki `actual_date` nullable. Kolom target date tidak ada di schema maupun input form.
4. **Tidak Ada Penutupan Otomatis (No Auto-closing):**
   - Progress hanya ditutup melalui tindakan manual eksplisit admin (`CloseProgress`). Kelengkapan problem/tindakan tidak pernah otomatis mengubah status progress.
5. **Tidak Ada Audit Trail Produk & Report/Export:**
   - Tidak ada tabel log audit trail atau fitur export PDF/Excel. Semua riwayat terekam melalui mutasi entitas asli dan revision number.
6. **Tidak Ada Link Antaraktivitas:**
   - Setiap progress berdiri mandiri; tidak ada dependency graph atau link wajib antar 5 tipe aktivitas.

---

## 3. Pembekuan Kontrak Field & Relasi untuk Importer (Fase 06)

Schema database PostgreSQL 18 saat ini dibekukan untuk kebutuhan script migrasi:

### A. Tabel `projects`
- `id` (bigint, PK)
- `jo_no` (varchar, Unique)
- `part_no` (varchar, Not Null)
- `part_name` (text, Not Null)
- `customer` (varchar, Nullable)
- `model` (varchar, Nullable)
- `no_doc` (varchar, Nullable)
- `t0_target_date` (date, Nullable)
- `plan_finish_date` (date, Nullable)
- `event1_pp1_date`, `event2_pp2_date`, `event3_mp_date` (date, Nullable)
- `created_at`, `updated_at` (timestamptz)

### B. Tabel `progresses`
- `id` (bigint, PK)
- `project_id` (bigint, FK `projects.id`)
- `activity_date` (date, Not Null untuk progress final)
- `activity` (varchar: `'Trial'|'Sent Sample'|'Send Document'|'Audit'|'Others'`)
- `status` (varchar: `'Open'|'Closed'`)
- `revision` (bigint, CAS optimistic revision)
- `pic_customer` (text, Nullable)
- `description` (text, Nullable)
- `process_trial`, `machine`, `die_high_raw`, `material_actual_trial`, `qty_material_raw`, `qty_process_raw`, `qty_sample_raw`, `event_note`, `remark` (text, Nullable)
- `legacy_incomplete` (boolean: `true` jika JO No, tanggal, atau Activity tidak lengkap dari data Access lama)
- `legacy_source_id` (varchar, Nullable untuk audit penelusuran Access)

### C. Tabel Anak Graf
- `problems`: `id`, `progress_id`, `client_id`, `position`, `description`.
- `corrective_actions`: `id`, `problem_id`, `client_id`, `position`, `description`, `pic`, `actual_date`.
- `next_actions`: `id`, `progress_id`, `client_id`, `position`, `description`, `pic`, `actual_date`.
- `feedbacks`: `id`, `progress_id`, `client_id`, `position`, `content`, `pic`, `feedback_date`.

### D. Tabel Lampiran & Arsip
- `attachments`: `id` (UUID), `state` (`'staging'|'attached'`), `kind` (`'photo'|'document'`), `storage_key`, `checksum`, `caption`.
- `legacy_document_metadata`: `id`, `progress_id`, `feedback_id`, `source_field`, `name`, `description`. Berkas fisik tidak ada; metadata tersimpan untuk dibaca pengguna.

---

## 4. Kesimpulan Kesiapan Migrasi

Aplikasi operasional inti (Fase 01 hingga Fase 05) telah terverifikasi secara penuh dan **SIAP** untuk menerima dry run migrasi data Access (perkiraan 250 progress dan riwayat terkait) pada Fase 06.
