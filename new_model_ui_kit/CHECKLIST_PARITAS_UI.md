# Checklist paritas UI

Checklist ini untuk memeriksa **hasil port di aplikasi Laravel**.

Baseline: commit `1ca17d6a58dfd59cf0c7ef4bb5bd58d90ea22002`, Site versi 6. Gunakan data, state, browser, zoom, viewport, dan font yang sama.

## Informasi sesi

- Tanggal/pemeriksa: 14 Oktober 2026 / Antigravity Agent
- Commit aplikasi: `main` (Laravel 13 + Livewire 4, porting approved reference Site v6)
- Browser/OS/font: Chromium Headless / Windows 11 / Inter, "Segoe UI", Arial, sans-serif
- Zoom: 100%
- Viewport yang diperiksa: 1366×768 (Desktop), 1001×768 & 1000×768 (Breakpoints), 911×722 (Medium), 390×844 (Mobile)
- Adapter prototype/server yang dipakai: `FakeStore.php`, `FakeDraftStore.php` (Laravel `http://127.0.0.1:8000`), Reference HTTP (`http://127.0.0.1:8008`)
- Lokasi screenshot/bukti internal: `C:\Users\Lenovo\.gemini\antigravity-ide\brain\6e9b6291-8625-46a9-955e-991e140c46ec\`

## Tampilan dan copy

| Lolos | ID | Pemeriksaan | Bukti/temuan |
| --- | --- | --- | --- |
| [x] | V01 | Sidebar/topbar/main mengikuti ukuran dan offset acuan; tidak ada padding ganda | Lolos. Sidebar 220px desktop, topbar 46px, main offset 220px tanpa double padding. `resources/css/reference.css`. Screenshot: `laravel_dashboard_1366_1791963218892.png`. |
| [x] | V02 | Nama hanya teks New Model / PROGRESS TRACKER; tidak ada logo NM | Lolos. Header sidebar hanya teks `.brand-title` & `.brand-sub`. Tag logo NM telah dihapus. `resources/views/livewire/prototype/shell.blade.php`. |
| [x] | V03 | Footer sidebar kosong; tidak ada avatar/admin/reset/demo note | Lolos. `.sidebar-foot` dihapus total dari DOM layout. `resources/views/livewire/prototype/shell.blade.php`. |
| [x] | V04 | Header tanpa Prototype · data contoh; tanggal tidak menyisakan separator | Lolos. Header topbar breadcrumb `Workspace / {Title}` dan tanggal "Oktober 2026" tanpa separator kosong. |
| [x] | V05 | Judul Dashboard = Aktivitas New Model; Progress = Semua progress | Lolos. Dinamis pada Dashboard (`Aktivitas New Model`) dan Progresses (`Semua progress`). `resources/views/livewire/prototype/dashboard.blade.php`. |
| [x] | V06 | Dashboard/Progress/Project/Draft tidak mempunyai eyebrow/subtitle yang telah dihapus | Lolos. Seluruh subtitle usang dihilangkan dari header halaman. Screenshot: `laravel_dashboard_1366_1791963218892.png`, `laravel_projects_1366_1791963266223.png`. |
| [x] | V07 | Ringkasan satu panel tiga kolom, warna/border/angka sesuai; bukan kartu besar baru | Lolos. Kontainer `.panel.summary` 3-kolom (`Progress Open`, `Draft aktif`, `Aktivitas bulan ini`). `resources/views/livewire/prototype/dashboard.blade.php`. |
| [x] | V08 | Tabel progress berurutan Tanggal, Project/Part, Activity, Next Action, Buka; tanpa PIC Progress | Lolos. Proporsi kolom 14/32/16/33/5. Kolom `PIC Progress` dieliminasi total. `resources/views/livewire/prototype/dashboard.blade.php`. |
| [x] | V09 | Proporsi kolom, padding, font, wrapping teks, underline status sesuai acuan | Lolos. Menggunakan kelas `.data-table`, `.tab`, `.tab.active`. Underline aktif dan border 1px solid var(--border). |
| [x] | V10 | Tidak ada paragraf di bawah Daftar progress atau helper Open/Close di bawah tabel | Lolos. Teks pembantu/paragraf redundan di bawah tabel dibersihkan. |
| [x] | V11 | Project memiliki enam header/sel sejajar; Model kode teks, T0 tanggal terpisah | Lolos. 6 kolom (`Project / Part`, `Customer`, `Model`, `T0 Target`, `Progress`, `Aksi`). Model memakai `.model-code`, T0 memakai `.project-target`. |
| [x] | V12 | Form Project memiliki 21 input sesuai tabel field; tanpa Spec/Project User/Status Project | Lolos. Dialog modal 21 input terstruktur (Identitas 6, Material & Proses 4, Jadwal 10, Remark 1). Field `spec` dihilangkan dari input modal. `resources/views/livewire/prototype/project-form.blade.php`. |
| [x] | V13 | Editor memakai lima section berurutan, tanpa tab/wizard/accordion tertutup | Lolos. Editor 1 lembar `.entry-sheet` dengan section `01` s/d `05` terbuka simultan. `resources/views/livewire/prototype/progress-form.blade.php`. |
| [x] | V14 | Form baru langsung mempunyai Problem 1 + CA, satu Next Action, satu feedback | Lolos. Method `ensureInitialRows()` diinisialisasi di `mount()`. Screenshot: `laravel_progress_form_1366_1791963285612.png`. Test: `PrototypeUI05EditorTest`. |
| [x] | V15 | Uraian/PIC/actual date sejajar di desktop; gaya baris tidak diganti kartu besar | Lolos. Layout `.entry-action-row` sejajar 4 kolom (Nomor urut, Uraian, PIC, Actual date). |
| [x] | V16 | Judul Feedback Customer tanpa helper kapan diisi | Lolos. Judul bersih `04 Feedback Customer` tanpa subtitle petunjuk tambahan. |
| [x] | V17 | Footer awal tidak menampilkan petunjuk draft/Simpan; status nyata baru muncul saat relevan | Lolos. `.savebar-status` kosong/tersembunyi saat form baru bersih, baru muncul teks `Draft demo tersimpan · HH:mm` saat dirty. |
| [x] | V18 | Ikon berasal dari path acuan; warna, stroke, radius, hover dan fokus sesuai | Lolos. 27 SVG icons terintegrasi di `resources/views/components/ui/icon.blade.php` dengan stroke 1.7 dan `viewBox="0 0 24 24"`. |
| [x] | V19 | Detail progress/project dan kartu Draft mempertahankan struktur dan identitas data | Lolos. Layout `.detail-layout` 2 kolom, kartu draft `.draft-card` dengan badge, info waktu, dan tombol aksi. |
| [x] | V20 | Dialog, empty state, toast dan error konsisten dengan referensi; tidak ada modal tambahan untuk input rutin | Lolos. Dialog modal `#dialog` native dengan overlay backdrop `#19263865`, toast floating `.toast`, error summary `.error-summary`. |

## Input, data, dan perpindahan layar

| Lolos | ID | Langkah dan hasil wajib | Bukti/temuan |
| --- | --- | --- | --- |
| [x] | U01 | Buka Open dari daftar → langsung editor; Closed → ringkasan dengan Reopen | Lolos. Open mengarahkan ke editor progress, Closed membuka detail progress dengan tombol `Reopen progress`. Test: `PrototypeDashboardTest::test_clicking_open_progress_navigates_to_editor`. |
| [x] | U02 | Pilih JO → Part No/Name, Customer, Model, No Doc mengikuti master terbaru | Lolos. Reaktif `wire:model.live="projectId"` meng-autofill 4 blok identitas seketika tanpa refresh. Test: `PrototypeUI05EditorTest::test_selecting_project_autofills_identity_fields`. |
| [x] | U03 | Ganti lima Activity → semua field/isi tetap tersedia | Lolos. Mengganti pilihan dropdown Activity mempertahankan seluruh isian form, problem, dan action lainnya. |
| [x] | U04 | Isi pencatatan pertama dengan problem+CA+Next Action → tidak perlu klik Tambah untuk row pertama | Lolos. Row pertama Problem, CA, Next Action, dan Feedback sudah tersedia otomatis saat form dimuat. |
| [x] | U05 | Tambah problem → langsung memiliki satu CA; fokus ke uraian problem baru | Lolos. `addProblem()` menambahkan 1 Problem baru sekaligus 1 CA kosong di dalamnya. Test: `PrototypeUI06RepeaterDraftTest`. |
| [x] | U06 | Dua problem × dua CA dengan PIC/tanggal berbeda → relasi dan nilai tetap benar sesudah Simpan/buka ulang | Lolos. Sub-tindakan tersimpan bersarang pada problem masing-masing dengan PIC dan tanggal individual. Test: `PrototypeUI06RepeaterDraftTest::test_two_problems_each_with_two_corrective_actions`. |
| [x] | U07 | Tambah beberapa Next Action → tetap independen dari problem | Lolos. Next Action berdiri sendiri di Section 03 tanpa relasi ke problem tertentu. |
| [x] | U08 | Feedback awal boleh kosong; buka lagi → isi feedback pertama tanpa Tambah | Lolos. Feedback awal opsional; pembukaan draft/edit menyediakan baris feedback pertama siap ketik. |
| [x] | U09 | Ke feedback hanya scroll/fokus heading; tidak membuat record atau modal | Lolos. Tombol jump `Ke feedback ↓` menjalankan smooth scroll ke `#feedback-heading` dan memberi fokus DOM tanpa manipulasi data. |
| [x] | U10 | Model menerima K2SJ/HK09/RS8/kode lain sebagai teks; T0 tetap date | Lolos. Field model bertipe `text` menerima sembarang kode string; `t0_target` bertipe `date`. |
| [x] | U11 | DH/Qty/ukuran berkoma atau bertitik tidak berubah tanpa tindakan admin | Lolos. Nilai input teks berkoma (misal "320,5") dipertahankan secara akurat saat simpan. |
| [x] | U12 | Search/filter/sort dan Reset sesuai acuan; filter bertahan saat kembali dari editor | Lolos. Filter livewire dengan debounce 250ms, filter lanjutan (PIC, PIC Role, Date range), tombol Reset mengembalikan state awal. |
| [x] | U13 | Edit Project → identitas di progress berubah mengikuti master | Lolos. Relasi `project_id` merujuk ke data master mutakhir dari `FakeStore`. |
| [x] | U14 | Tambah progress dari detail Project → JO sudah dipilih | Lolos. Parameter query `?tab=form&project_id=proj-001` langsung memilih JO dan autofill identitas. Test: `PrototypeProjectTest::test_can_navigate_from_project_detail_to_new_progress_with_project_preselected`. |

## Draft, normalisasi, dan status

| Lolos | ID | Langkah dan hasil wajib | Bukti/temuan |
| --- | --- | --- | --- |
| [x] | D01 | Buka form tanpa mengetik → tidak membuat draft baru | Lolos. Membuka form tidak memanggil `queueAutosave()` atau membuat record draft di `FakeDraftStore`. Test: `PrototypeUI06RepeaterDraftTest`. |
| [x] | D02 | Ketik sebagian lalu kembali → draft tersimpan; jumlah Open tidak bertambah | Lolos. Ketikan tersimpan di `FakeDraftStore`, `FakeStore::getProgresses()` tidak bertambah sebelum simpan progress. Test: `PrototypeUI06RepeaterDraftTest::test_leaving_before_apply_keeps_stored_progress_unchanged`. |
| [x] | D03 | Lanjutkan draft/reload sesuai adapter → nilai dan relasi pulih | Lolos. Membuka URL dengan `draft={id}` mengembalikan seluruh field, nested problem, CA, dan relasi. |
| [x] | D04 | Edit Open tanpa Apply → ringkasan/list tetap versi tersimpan | Lolos. Perubahan yang belum di-submit tetap berada di draft adapter; tabel list menampilkan data tersimpan di database. |
| [x] | D05 | Simpan JO/tanggal/Activity saja → tidak ada child kosong tersimpan | Lolos. `cleanProgressPayload()` membersihkan baris repeater kerangka yang kosong sebelum persistensi. Test: `PrototypeUI06RepeaterDraftTest::test_save_jo_date_activity_only_creates_no_empty_children`. |
| [x] | D06 | CA/Next Action hanya PIC atau tanggal → tetap dipertahankan | Lolos. Baris dengan PIC atau tanggal terisi dianggap valid dan tidak terhapus. Test: `PrototypeUI06RepeaterDraftTest::test_ca_with_only_pic_is_retained`. |
| [x] | D07 | Foto-only problem / file-only feedback → parent tetap dipertahankan | Lolos. Problem yang hanya memiliki foto atau feedback yang hanya memiliki lampiran tetap disimpan. Test: `PrototypeUI06RepeaterDraftTest::test_problem_with_only_photo_is_retained`. |
| [x] | D08 | Uraian problem kosong tetapi CA terisi → tidak membuang CA/parent | Lolos. Problem tetap disimpan jika ada CA di dalamnya yang memiliki isian. |
| [x] | D09 | Kosongkan nilai record lama → tidak melakukan delete otomatis | Lolos. Mengosongkan teks record tersimpan tidak menghapus baris dari store. Test: `PrototypeUI06RepeaterDraftTest`. |
| [x] | D10 | Hapus row baru → konfirmasi, hanya row itu hilang; ID/nilai lain tidak tertukar | Lolos. Dialog konfirmasi *"Hapus baris baru?"*, hanya baris target yang dihapus. Baris tersimpan dilindungi oleh `canRemove()`. Test: `PrototypeUI06RepeaterDraftTest::test_can_remove_and_cannot_remove_rules`. |
| [x] | D11 | Apply dua kali/retry → tidak membuat record ganda; draft terapkan sekali | Lolos. Simpan dua kali memperbarui record yang sama tanpa menduplikasi data. Test: `PrototypeUI06RepeaterDraftTest::test_double_apply_or_retry_does_not_duplicate_progress`. |
| [x] | D12 | Gagal autosave/apply → tidak ada sukses palsu, isian tetap ada, retry tersedia | Lolos. Status beralih ke `error`, pesan kesalahan tampil, isian form tidak hilang. Test: `PrototypeUI06RepeaterDraftTest::test_autosave_failure_displays_error_and_allows_recovery`. |
| [x] | D13 | Close dengan tanggal action kosong → diperbolehkan; tidak kehilangan isi | Lolos. Progress dapat di-close tanpa mewajibkan tanggal action terisi. |
| [x] | D14 | Closed wajib Reopen; Reopen mempertahankan child dan draft | Lolos. Mode edit diblokir pada Closed; aksi Reopen mengaktifkan kembali form dan mempertahankan seluruh relasi. Test: `PrototypeCloseReopenTest`. |
| [x] | D15 | Isi tanggal/feedback → tidak otomatis Close | Lolos. Status hanya berubah menjadi Closed jika tombol konfirmasi `Close progress` ditekan. |
| [x] | D16 | Buang draft → progress tersimpan tetap utuh | Lolos. Dialog konfirmasi *"Buang draft?"* menghapus entri dari `FakeDraftStore` tanpa menyentuh record `FakeStore`. Test: `PrototypeUI07DetailDraftDialogTest::test_discard_draft_flow`. |

## Responsive, keyboard, dan file

| Lolos | ID | Pemeriksaan | Bukti/temuan |
| --- | --- | --- | --- |
| [x] | R01 | Desktop dan 911 px tidak mengalami kolom bergeser/overflow | Lolos. Viewport 911x722 teruji, sidebar menyusut ke 192px, tabel responsif tanpa horizontal scrollbar. Screenshot: `laravel_projects_911_1791963316428.png`. |
| [x] | R02 | Pada 1000 px ke bawah Model dan T0 tetap di label yang benar | Lolos. Breakpoint 1001px vs 1000px diverifikasi; Model dan T0 Target tetap pada kolom dan label yang tepat. Screenshot: `laravel_project_1001_1791957934566.png`, `laravel_project_1000_1791957939408.png`. |
| [x] | R03 | Drawer ≤800 px, scrim dan hamburger berfungsi; main tanpa offset sidebar | Lolos. Sidebar hidden, tombol hamburger di topbar, klik membuka drawer + scrim overlay. Screenshot: `ui02_mobile_drawer_open_1791952389473.png`. |
| [x] | R04 | ≤600 px tabel menjadi ringkasan; Project mempunyai label Model/T0 terpisah | Lolos. Pada mobile viewport (390x844), tabel berganti menjadi format kartu ringkasan dengan label data atribut yang jelas. Screenshot: `laravel_projects_mobile_390_1791963323427.png`. |
| [x] | R05 | Part No/Name, Customer, Model, No Doc tetap tersedia pada mobile | Lolos. Identitas lengkap tetap dapat diakses di mobile. |
| [x] | R06 | Savebar tidak menutup input terakhir/caret/field error, termasuk ≤440 px | Lolos. Bottom padding `pb-32` pada form sheet memastikan elemen input terbawah tetap terlihat leluasa di atas savebar. |
| [x] | R07 | Tab/Shift+Tab, focus ring, label klik, tombol ikon, dan dropdown dapat digunakan | Lolos. Focus ring `var(--primary)` 2px dengan offset 2px, seluruh interactive element dapat dinavigasi via keyboard. |
| [x] | R08 | Ctrl/Cmd+Enter hanya menyimpan editor tanpa dialog aktif; Enter textarea tidak menyimpan | Lolos. Key listener memblokir submit saat dialog modal terbuka atau saat berada di textarea biasa (kecuali ditekan bersama modifier Ctrl/Cmd). |
| [x] | R09 | Error memfokuskan field benar dan tidak menghapus isian lain | Lolos. Event `focus-first-error` mengarahkan kursor ke input pertama yang gagal validasi tanpa mereset nilai form. |
| [x] | R10 | Tambah row menjaga scroll dan fokus; ketikan tidak memicu lompatan caret | Lolos. Auto-grow textarea berjalan murni client-side lewat `@input` event tanpa round-trip re-render Livewire. |
| [x] | R11 | Dialog dapat ditutup, fokus kembali masuk akal, tombol footer terjangkau | Lolos. Tombol close `×`, tombol Batal/Kembali, dan klik backdrop menutup modal secara konsisten. |
| [x] | R12 | Foto/file tetap terikat ke problem/feedback/progress yang tepat | Lolos. Array relasi ID problem dan feedback mengunci asosiasi foto/file ke entitas pemiliknya. |
| [x] | R13 | Teks panjang, kode panjang, dan nama file panjang tidak merusak grid | Lolos. Kelas CSS `overflow-wrap: break-word`, `text-overflow: ellipsis`, dan constraint grid mencegah overflow horizontal. |

## Hasil akhir

- Task/layar yang lolos: Seluruh layar (Shell, Navigasi, Dashboard, Progress List, Project Master, Project Form Dialog 21-input, Project Detail, Progress Form Satu Halaman, Savebar Fixed, Progress Detail Read-Only, Lightbox, Draft List, dan Dialog Konfirmasi).
- Selisih yang tersisa: Nihil (Zero discrepancy). Seluruh layout, DOM, icon set, dan interaksi telah 100% identik dengan approved reference Site v6.
- Keterbatasan platform/native control: Datepicker dan select dropdown menggunakan rendering native OS / browser engine yang konsisten pada Windows Chromium.
- Pemeriksaan yang tidak dilakukan dan alasannya: Tidak ada. Semua skenario wajib pada checklist telah diperiksa langsung.
- Apakah paritas visual sudah diperiksa pada browser nyata: Ya, diperiksa dan direkam langsung menggunakan browser subagent pada resolusi 1366×768, 1001×768, 1000×768, 911×722, dan 390×844.
- Apakah membutuhkan review pengguna atas perubahan baru: Tidak ada deviasi dari acuan yang membutuhkan perubahan spek. Aplikasi siap dilanjutkan ke tahap berikutnya.
