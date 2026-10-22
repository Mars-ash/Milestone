# Fase 06 Access Data Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Idempotently import 51 Projects and 266 Progress records (including Problems, Corrective Actions, Next Actions, Feedbacks, and Legacy Document Metadata) from `import/` Excel files into PostgreSQL without duplicates or data loss.

**Architecture:** 
1. Database migration to create an `import_records` tracking table to map legacy source IDs (`source_table`, `source_id`) to database targets (`target_table`, `target_id`).
2. An `AccessImporter` service that reads Excel data (or canonical dumps), validates/cleans data according to PRD Phase 06 rules (e.g. `legacy_incomplete`, raw text preservation, date serial conversion), and performs idempotent upserts into `projects`, `progresses`, `problems`, `corrective_actions`, `next_actions`, `feedbacks`, and `legacy_document_metadata`.
3. An Artisan command `php artisan import:access` with `--dry-run` and `--force` support.
4. Comprehensive test coverage for parsing, child entity relations, null handling, and idempotency.

**Tech Stack:** PHP 8.4, Laravel 12, PostgreSQL 18, PHPUnit.

**Spec:** `task list/FASE_06_Migrasi_Access.md`, `PRD_Progress_New_Model.md` (Sections 15-19, FR21-FR25).

## Global Constraints

- Satu admin; semua PIC teks bebas.
- Satu JO untuk satu Part No dan sebaliknya.
- Project User dan Status Project tidak ada dalam model produk.
- Lima Activity persis: Trial, Sent Sample, Send Document, Audit, Others.
- Progress Close '1' -> 'Closed', '0' atau null -> 'Open'. Tidak ada auto-close.
- Ukuran/DH/Qty berupa raw text, mempertahankan koma/titik tanpa konversi numerik.
- Tanggal serial Excel dikonversi ke Y-m-d (basis 1899-12-30). Tanggal kosong disimpan null dengan `legacy_incomplete = true`.
- Corrective Action tanpa deskripsi problem membuat problem container dengan deskripsi null.
- Tidak ada foto dalam dataset yang diberikan (dikonfirmasi pengguna: "ga ada foto").
- Byte file Doc1-Doc5 tidak diimpor; nama dan keterangan disimpan dalam `legacy_document_metadata`.
- Idempoten: Menjalankan ulang impor berkali-kali menghasilkan jumlah record yang persis sama.

## Review Focus

1. Progress dengan tanggal kosong atau activity kosong: tersimpan sebagai `legacy_incomplete = true` tanpa melanggar check constraints PostgreSQL.
2. Tanggal serial Excel: dikonversi dengan presisi (misal serial 45678 -> Y-m-d) tanpa off-by-one error (basis 1899-12-30).
3. Corrective action tanpa problem: membuat problem container `description = null` agar tindakan dan PIC/tanggal tidak hilang.
4. Re-run idempotency: menjalankan `php artisan import:access` kedua kali tidak menambah satupun record project, progress, problem, action, feedback, atau legacy document metadata.
5. Filter dan pagination di UI `/app`: halaman progress dan filter customer/status tetap berfungsi lancar dengan data riil (255 Open, 11 Closed).

---

### Task 1: Migration for `import_records` Tracking Table

**Files:**
- Create: `database/migrations/2026_10_20_000001_create_import_records_table.php`
- Create: `app/Models/ImportRecord.php`
- Test: `tests/Feature/Import/ImportRecordMigrationTest.php`

**Interfaces:**
- Consumes: PostgreSQL schema
- Produces: `App\Models\ImportRecord` (`source_table`, `source_id`, `target_table`, `target_id`, `checksum`, `status`, `notes`)

- [ ] **Step 1: Write failing test for `import_records` table and model**
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Create migration `create_import_records_table` and `ImportRecord` model**
- [ ] **Step 4: Run test to verify it passes**

---

### Task 2: Excel Parser and Access Import Service

**Files:**
- Create: `app/Services/Import/AccessImporter.php`
- Test: `tests/Feature/Import/AccessImporterTest.php`

**Interfaces:**
- Consumes: Excel files in `import/` (`Table - Project List Master.xlsx`, `Table - Progress New model Master.xlsx`)
- Produces: `AccessImporter::importProjects(bool $dryRun = false): array`, `AccessImporter::importProgresses(bool $dryRun = false): array`, `AccessImporter::run(bool $dryRun = false): array`

- [ ] **Step 1: Write failing test in `AccessImporterTest` verifying project and progress mapping**
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Implement `AccessImporter` with date conversion, raw text preservation, child mapping, and `import_records` tracking**
- [ ] **Step 4: Run test to verify it passes**

---

### Task 3: Idempotency Verification Test

**Files:**
- Create: `tests/Feature/Import/ImportIdempotencyTest.php`

**Interfaces:**
- Consumes: `AccessImporter`
- Produces: Verified idempotent behavior across multiple runs

- [ ] **Step 1: Write test `ImportIdempotencyTest` asserting record counts before and after 2nd import run**
- [ ] **Step 2: Run test to verify idempotency passes**

---

### Task 4: Artisan Command `import:access`

**Files:**
- Create: `app/Console/Commands/ImportAccess.php`
- Test: `tests/Feature/Import/ImportAccessCommandTest.php`

**Interfaces:**
- Consumes: `AccessImporter`
- Produces: Console command `php artisan import:access {--dry-run} {--force}`

- [ ] **Step 1: Write failing test for `import:access` artisan command**
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Implement `ImportAccess` command with output summary table**
- [ ] **Step 4: Run test to verify it passes**

---

### Task 5: Execute Import on Development Database & Full Suite Verification

**Files:**
- Modify: `task list/FASE_06_Migrasi_Access.md`

- [ ] **Step 1: Execute `php artisan import:access --dry-run` on dev environment**
- [ ] **Step 2: Execute `php artisan import:access` on `progress_new_model_dev`**
- [ ] **Step 3: Verify record counts in database (51 projects, 266 progresses: 255 Open, 11 Closed)**
- [ ] **Step 4: Run full test suite `vendor/bin/phpunit` (ensure 100% pass)**
- [ ] **Step 5: Check UI and verify KPI numbers and progress listing**
- [ ] **Step 6: Update Phase 06 task checklist and documentation**
