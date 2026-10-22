# Peta source UI ke implementasi

Gunakan fungsi dan selector sebagai penanda pencarian. Nomor baris berlaku untuk snapshot pada paket, bukan untuk file Laravel. `app.js` sengaja disertakan tanpa format ulang agar hash tetap sama dengan versi yang disetujui.

## Tanggung jawab

| Bagian | Simbol sumber | Tujuan adaptasi | Binding/aksi penting |
| --- | --- | --- | --- |
| Shell dan SVG | shell, icon, paths | Layout/komponen navigasi | data-nav, data-do=menu |
| Router tampilan | content, render, nav | Route/action/layout yang sudah ada | view dan selected; bukan URL wajib |
| Ringkasan | summaryHTML | Komponen summary | Counter Open, Draft, bulan fixture |
| Dashboard/Progress | dashboardHTML, filtered, tableHTML, updateTable | Daftar dan filter | data-filter, data-status-filter, data-open-progress |
| Buka editor | startEditor | Mount/load editor | Resume draft atau salin progress; guard Closed |
| Kerangka editor | editorHTML, sectionHTML, field | Blade editor dan field | data-path; required/errors |
| Problem dan action | problemHTML, actionHTML, canRemove | Repeater stabil | data-add-ca, data-remove, data-confirm-remove |
| Draft/apply | queueDraft, flushDraft, saveProgress, indicator | Adapter autosave/apply | dirty, savedAt; indikator sesuai hasil nyata |
| Ringkasan progress | detailHTML, kv | View read-only | data-edit-progress, data-close-progress, data-reopen |
| Draft list | draftsHTML | Daftar draft | data-draft, data-discard, data-confirm-discard |
| Project list/detail | projectsHTML, projectDetailHTML | Daftar/detail project | data-project-detail, data-new-project |
| Form Project | masterDialog, saveMaster | Dialog/komponen form Project | data-master, data-do=saveMaster |
| Dialog/foto/file | openDialog, fileList | Dialog/upload/file item | data-photo, data-photo-owner, data-file-owner |
| Close/hapus row | confirmClose, removeItem | Action konfirmasi | data-confirm-close, data-confirm-remove |
| Toast/text helpers | notify, esc, options | Komponen/helpers yang setara | Escape teks; toast status |

## Lokasi fungsi app.js

| Fungsi | Baris awal |
| --- | --- |
| `persist()` | 17 |
| `notify()` | 18 |
| `nav()` | 19 |
| `shell()` | 20 |
| `render()` | 21 |
| `content()` | 22 |
| `summaryHTML()` | 23 |
| `dashboardHTML()` | 24 |
| `options()` | 25 |
| `filtered()` | 26 |
| `tableHTML()` | 27 |
| `updateTable()` | 28 |
| `startEditor()` | 29 |
| `editorHTML()` | 31 |
| `getValue()` | 40 |
| `setValue()` | 41 |
| `field()` | 42 |
| `sectionHTML()` | 51 |
| `actionHTML()` | 58 |
| `canRemove()` | 63 |
| `problemHTML()` | 64 |
| `growTextarea()` | 67 |
| `fileList()` | 68 |
| `queueDraft()` | 69 |
| `indicator()` | 70 |
| `flushDraft()` | 71 |
| `rerenderSection()` | 72 |
| `saveProgress()` | 73 |
| `detailHTML()` | 74 |
| `kv()` | 75 |
| `draftsHTML()` | 76 |
| `projectsHTML()` | 77 |
| `projectDetailHTML()` | 78 |
| `openDialog()` | 79 |
| `masterDialog()` | 80 |
| `saveMaster()` | 81 |
| `confirmClose()` | 82 |
| `removeItem()` | 83 |

`icon`, `paths`, `status`, `date`, `time`, `filters`, dan `sections` dideklarasikan sebagai konstanta. Cari namanya secara literal. Event delegation berada di bagian akhir file: input, change, keydown, click, beforeunload.

## Lokasi fungsi state.js

| Fungsi | Baris awal |
| --- | --- |
| `blank()` | 8 |
| `prepareEditor()` | 13 |
| `cleanProgress()` | 21 |
| `migrate()` | 33 |
| `seed()` | 38 |
| `saveDraft()` | 51 |
| `validate()` | 52 |
| `applyDraft()` | 53 |
| `setStatus()` | 54 |

`emptyAction`, `emptyProblem`, dan `emptyFeedback` adalah factory konstanta di dekat `blank()`. Fungsi `migrate()` hanya menyentuh data demo browser; jangan menyalinnya menjadi migration SQL.

## Selector aktif yang paling penting

| Kelompok | Cari di styles.css | Hal yang dijaga |
| --- | --- | --- |
| Fondasi | `:root`, `*`, `.icon`, `:focus-visible` | Token, box-sizing, font, ikon, fokus |
| Shell | `.sidebar`, `.brand`, `.nav-item`, `.workspace`, `.topbar`, `.main` | Lebar/offset, active menu, padding |
| Kontrol | `.btn`, `.input`, `select`, `textarea`, `.field-label`, `.status` | Dimensi, warna, focus, label |
| Dashboard | `.summary`, `.stat`, `.tabs`, `.filters`, `.advanced-filters` | Ringkasan dan filter |
| Tabel umum | `.data-table`, `.row-title`, `.row-sub`, `.next-text` | Kolom, wrapping, padding |
| Project | `.project-table`, `.project-model`, `.project-target`, `.model-code` | Model/T0 terpisah; mobile labels |
| Editor | `.editor-main`, `.editor-head`, `.context`, `.identity` | Context JO, padding bawah |
| Lembar input | `.entry-sheet`, `.entry-section`, `.activity-fields`, `.trial-grid` | Urutan dan grid |
| Repeater | `.problem`, `.entry-action-row`, `.feedback-grid`, `.repeat-index` | Relasi visual parent/child dan alignment |
| File | `.photo-strip`, `.photo-add`, `.thumb`, `.file-list` | Upload focus, preview, panjang nama |
| Footer | `.savebar`, `.save-state`, `.save-indicator`, `.feedback-jump` | Tombol selalu tersedia, idle kosong |
| Baca/draft | `.detail-layout`, `.detail-block`, `.detail-aside`, `.draft-card` | Layout read-only dan kartu |
| Dialog | `dialog`, `.dialog-head`, `.dialog-body`, `.dialog-foot`, `.dialog-wide` | Lebar dan scroll area |

Jangan mengambil hanya rule pertama suatu selector. File berisi override ukuran input/font, media query, lalu revisi editor dan Project. CSS terakhir yang berlaku bersama specificity menentukan hasil.

## Selector yang tidak menjadi alasan menambah markup

Sisa style untuk `.brand-mark`, `.sidebar-bottom`, `.demo-note`, `.user`, `.avatar`, `.prototype-label`, `.pic-col`, `.form-panel > .tabs`, `.section-intro`, `.action-grid`, dan `.next-card` tidak membuktikan elemen itu masih dipakai pada editor/shell final. Pindahkan hanya DOM aktif dari fungsi render. Aturan status tabs daftar tetap dipakai.

## Binding penting

| Referensi | Adaptasi |
| --- | --- |
| `data-path="projectId"` | Select JO; isi ID project, bukan Part No |
| `data-path="problems.i.actions.j.pic"` | PIC CA dengan parent/ID stabil |
| `data-path="nextActions.i.pic"` | PIC Next Action pada progress |
| `data-path="feedback.i.pic"` | PIC feedback; tidak sama dengan customerPic |
| `data-file-owner="feedback.i.files"` | Lampiran milik satu feedback |
| `data-photo-owner="i"` | Foto milik problem yang dimaksud; adapter gunakan ID stabil |
| `data-do="save"` | Apply draft, bukan Close |
| `data-confirm-close` | Close manual pada versi tersimpan |
| `data-reopen` | Kembali Open tanpa membuang data |

Template JavaScript memakai index untuk membaca array saat render. Dalam Livewire, identitas komponen wajib berbasis ID yang stabil agar penghapusan/penambahan tidak menukar isian.

## Contoh pemetaan file target

Pilih nama aktual dari repository, bukan membuat duplikat berdasarkan contoh ini:

- Layout aplikasi → shell/sidebar/topbar.
- Komponen dashboard/progress → summary/filter/table.
- Komponen editor progress → context/sections/repeaters/savebar.
- Komponen master project → table/dialog/detail.
- Adapter draft/status → normalisasi/autosave/apply/Close/Reopen.
- Entry CSS aplikasi → stylesheet referensi dengan cascade yang telah diverifikasi.

Peta target final harus dicatat pada UI-01 dengan nama file yang benar-benar ditemukan.
