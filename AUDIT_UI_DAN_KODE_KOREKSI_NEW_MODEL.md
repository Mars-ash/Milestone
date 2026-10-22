# Audit UI dan kode koreksi — Progress New Model

Dokumen ini berisi 32 temuan, kondisi yang seharusnya, dan kode koreksi konkret dalam unified diff untuk 21 file. Patch dibuat terhadap source persis dari ZIP yang dikirim. Repository asli, PRD, file fase, dan ZIP unggahan tidak diubah dalam audit ini.

## 1. Bukti dan batas pemeriksaan

| Pemeriksaan | Hasil |
| --- | --- |
| Source aplikasi, view aktif, binding, state, asset entry, CSS build, fixture, dan test | Dibaca dan dicocokkan |
| Empat file `new_model_ui_kit/reference/` terhadap baseline disetujui | Identik byte demi byte |
| `resources/css/reference.css` terhadap baseline | Identik byte demi byte |
| Framework pada lockfile | Laravel v13.35.0; Livewire v4.4.7 |
| Tailwind yang disertakan | v4.3.3 |
| Kebutuhan PHP dari `vendor/composer/platform_check.php` | PHP ≥ 8.4.1 |
| `git apply --check` terhadap source ZIP | Lulus |
| Patch diterapkan pada salinan verifikasi lalu dibandingkan | 21/21 file sama persis dengan kode usulan |
| Sintaks JavaScript Alpine yang ditambahkan | 19 ekspresi berhasil diparse setelah placeholder Blade disubstitusi |
| Pasangan directive Blade dan nama method ganda | Diperiksa secara struktural |
| `php -l`, kompilasi Blade, Artisan/PHPUnit, Vite build | Belum dijalankan |
| Render browser, computed style, screenshot/overlay lintas viewport | Belum dijalankan |

Lingkungan audit tidak memiliki executable PHP yang dibutuhkan maupun browser lokal. Karena itu, temuan di bawah membedakan **fakta pada source** dari **dampak visual/interaksi yang perlu diukur ulang di browser**. Dokumen ini tidak mengklaim hasil patch sudah identik secara piksel atau telah lulus UAT. Pemeriksaan pasangan directive bukan pengganti compiler Blade; parsing JavaScript bukan pengujian lifecycle Alpine/Livewire.

Identitas sumber:

- ZIP: `progress-new-model.zip`.
- SHA-256 ZIP: `b17526b73b388b9dd1e907d45dc37560cf7ede758edf92f226f0b6f52af23187`.
- Commit reference: `1ca17d6a58dfd59cf0c7ef4bb5bd58d90ea22002`.
- SHA-256 CSS reference: `390684822aa638eefbf979f0e12aab6afccc4705ce035f21fae23942aa0d0aff`.
- Pembanding operasional: `new_model_ui_kit/reference/`, bukan gambar desain hasil interpretasi baru.

## 2. Bagian yang sudah sesuai

Bagian berikut tidak perlu dibangun ulang lagi berdasarkan source yang diberikan:

- Nama halaman Aktivitas New Model, Semua progress, Project Master, dan Draft.
- Sidebar tanpa logo NM dan footer profil/prototype; label Prototype · data contoh sudah dihilangkan.
- Teks pengantar di atas/bawah judul daftar dan dua teks footer yang diminta dihapus sudah tidak dirender pada halaman aktif.
- Form progress satu halaman dengan lima section, termasuk isian pertama problem + CA, Next Action, dan feedback pada jalur browser.
- Tidak ada field PIC Progress pada form aktif; beberapa state/filter legacy masih perlu dibenahi seperti temuan F22.
- Identitas master memperlihatkan Part Name/No, Customer, Model, dan No Doc.
- Model adalah teks terpisah dari kolom T0; input Spec sudah tidak ada pada form Project.
- `Spec` read-only pada detail project masih ada dalam reference; jangan menghapusnya hanya karena menemukan kata Spec di source.
- Path SVG ikon, token utama, struktur grid editor, dan breakpoint pada reference.css sudah sama.
- Next Action terpisah dari corrective action; status Close/Reopen tetap aksi manual; normalisasi saat Simpan sudah menangani banyak kasus isian parsial.

Komponen legacy dalam `resources/views/components/progress/` serta `resources/views/prototype/index.blade.php` tidak semuanya dipanggil oleh route aktif. Keberadaan file lama saja tidak saya hitung sebagai selisih layar. Audit menelusuri route `/prototype` → `Shell` → view/komponen aktif.

## 3. Temuan rinci

P1 perlu diselesaikan sebelum review pengguna berikutnya. P2 adalah perbedaan tampilan/interaksi yang lebih kecil. Konteks QA menjelaskan selisih yang tidak boleh diperbaiki dengan mengubah data operasional.

### F01 — Reset Tailwind mengubah tinggi baris seluruh halaman

**Prioritas:** P1.

**Bukti:** `resources/css/app.css:1`; `node_modules/tailwindcss/preflight.css:30`.

**Sekarang:** Entry CSS memuat Preflight. Rule `html,:host { line-height:1.5 }` benar-benar ada pada CSS build yang disertakan. Reference menetapkan font 14 px tetapi tidak menetapkan root line-height tersebut. Label, menu, ringkasan, tombol, dan input yang mewarisi line-height mendapat kondisi berbeda.

**Seharusnya:** Gunakan entry CSS khusus prototype yang memuat theme + utilities Tailwind dan reference.css tanpa Preflight. Pertahankan entry app.css untuk halaman lain. Nilai `min-height:38px` saja tidak menjamin tinggi kontrol sama jika isi + padding menjadi lebih tinggi.

**Kode:** `resources/css/prototype.css`, `vite.config.js`, `resources/views/components/layouts/app.blade.php`. Semua perubahan tersedia di bagian 5.

### F02 — SVG inline dan margin paragraf ikut berubah

**Prioritas:** P1.

**Bukti:** `node_modules/tailwindcss/preflight.css:214`.

**Sekarang:** Preflight mengatur SVG menjadi `display:block` serta mereset margin semua elemen. Reference tidak memakai reset tersebut. Contoh yang terdampak adalah panah pada link `Lihat Project Master` yang tidak berada di flex button, serta jarak paragraf pada dialog.

**Seharusnya:** Hilangkan Preflight dari entry prototype, bukan menambahkan margin acak pada setiap halaman. Setelah itu ukur kembali dengan browser yang sama. Ini temuan cascade; belum merupakan pengukuran piksel hasil render.

**Kode:** `resources/css/prototype.css`. Semua perubahan tersedia di bagian 5.

### F03 — Tombol sekunder berbentuk link menjadi biru

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/dashboard.blade.php:10`; `resources/css/reference.css:2`.

**Sekarang:** Reference memakai `<button class="btn">`, sedangkan port memakai `<a class="btn">` untuk beberapa aksi navigasi. Rule global `a` berwarna biru; `.btn` dasar tidak mengatur warna. Teks dan ikon tombol sekunder dapat berbeda walaupun kelasnya sama.

**Seharusnya:** Tambahkan aturan eksplisit untuk `a.btn`, dengan varian primary/ghost/text tetap mengikuti warna acuan. Link navigasi tetap dapat digunakan.

**Kode:** `resources/css/prototype.css`. Semua perubahan tersedia di bagian 5.

### F04 — Dialog Tambah/Edit Project kehilangan style dasar dialog

**Prioritas:** P1.

**Bukti:** `resources/views/livewire/prototype/project-form.blade.php:1`.

**Sekarang:** Container adalah `div.dialog.dialog-wide`, bukan elemen `dialog`. Kelas `.dialog-wide` memang memberi max-width 790 px, tetapi border, radius 10 px, max-height 86vh, dan shadow acuan berasal dari selector tag `dialog`. Port menambahkan radius 8 px, shadow-2xl, dan max-height 90vh.

**Seharusnya:** Pakai `<dialog class="dialog-wide">` native dengan `.dialog-head`, `.dialog-body`, `.dialog-foot` persis seperti acuan. Jangan menyimpulkan lebarnya pasti salah pada semua viewport: yang terbukti berbeda adalah jenis elemen dan aturan yang berlaku.

**Kode:** `resources/views/components/ui/reference-dialog.blade.php`, `resources/views/livewire/prototype/project-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F05 — Dialog konfirmasi bukan modal native; ID dialog ganda

**Prioritas:** P1.

**Bukti:** `resources/views/components/layouts/app.blade.php:19`; `resources/views/livewire/prototype/progress-form.blade.php:560`; `resources/views/livewire/prototype/draft-list.blade.php:70`.

**Sekarang:** Layout menyediakan `#dialog` kosong; komponen kemudian membuat `#dialog` lagi. Dialog anak diberi atribut `open` di dalam overlay buatan. `open` saja tidak memberikan top layer, inert background, focus trapping, atau Escape seperti `showModal()`. Shortcut editor mencari satu ID yang ambigu.

**Seharusnya:** Hapus dialog kosong global. Pakai satu komponen native bersama, ID unik per dialog, `showModal()`, penanganan cancel/Escape, serta nama aksesibel unik. Keyboard shortcut memeriksa `dialog[open]`, tidak lagi satu ID global.

**Kode:** `resources/views/components/layouts/app.blade.php`, `resources/views/components/ui/reference-dialog.blade.php`, `resources/views/livewire/prototype/progress-form.blade.php`, `resources/views/livewire/prototype/draft-list.blade.php`, `resources/views/livewire/prototype/progress-detail.blade.php`. Semua perubahan tersedia di bagian 5.

### F06 — Isi dan spacing form Project berbeda sedikit

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/project-form.blade.php:11`.

**Sekarang:** Error container hanya dibuat jika terjadi error dan berada di luar form; reference selalu menaruh `master-error` kosong di awal form. Asterisk required menggunakan span biru, sementara reference menggunakan teks `JO No *`, `Part No *`, `Part Name *`. Ada tambahan error per-field dan class input pada textarea Remark.

**Seharusnya:** Ikuti struktur error, teks label, dan textarea reference. Tetap simpan validasi yang ada; patch menampilkan pesan pertama pada area error yang sama seperti acuan. Input Spec tetap tidak ada.

**Kode:** `resources/views/livewire/prototype/project-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F07 — Simpan/Batal Project berpindah ke halaman berbeda

**Prioritas:** P2.

**Bukti:** `app/Livewire/Prototype/ProjectForm.php:124`; `resources/views/livewire/prototype/shell.blade.php:181`.

**Sekarang:** Simpan project selalu membuka detail. Batal selalu kembali ke daftar. Membuka Edit dari detail juga menghilangkan detail dari latar dialog. Reference menutup dialog lalu tetap pada layar asal.

**Seharusnya:** Catat konteks asal saat dialog dibuka. Tambah dari daftar selesai di daftar; edit dari detail kembali ke detail yang sama. Render dialog di luar cabang daftar/detail sehingga latarnya tetap benar.

**Kode:** `app/Livewire/Prototype/ProjectForm.php`, `resources/views/livewire/prototype/shell.blade.php`, `resources/views/livewire/prototype/project-detail.blade.php`. Semua perubahan tersedia di bagian 5.

### F08 — Tanggal dan Activity form baru sudah terisi otomatis

**Prioritas:** P1.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:18`; `app/Livewire/Prototype/ProgressForm.php:117`.

**Sekarang:** Form baru otomatis memilih Trial dan tanggal hari ini. Reference `DesignStore.blank()` mengosongkan tanggal dan Activity sehingga pilihan awal adalah `Pilih Activity`.

**Seharusnya:** Gunakan string kosong untuk default; jangan mengisi tanggal historis yang kosong dengan hari ini. Baris awal problem + CA, Next Action, dan feedback tetap langsung tersedia.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`. Semua perubahan tersedia di bagian 5.

### F09 — Feedback kedua dan seterusnya otomatis diberi tanggal

**Prioritas:** P1.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:767`.

**Sekarang:** `addFeedback()` mengisi tanggal hari ini, berbeda dengan `emptyFeedback()` reference yang kosong. Ini juga membuat feedback yang tidak diisi terlihat sebagai data parsial bertanggal saat normalisasi.

**Seharusnya:** Tanggal feedback baru selalu kosong sampai admin mengisinya. Jangan membuat tanggal hanya karena admin menekan Tambah feedback.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`. Semua perubahan tersedia di bagian 5.

### F10 — Lanjutkan draft pada progress tersimpan membuka versi yang salah

**Prioritas:** P1.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:60`; `resources/views/livewire/prototype/progress-detail.blade.php:46`.

**Sekarang:** Tombol detail menuju `?tab=form&id=...`. Cabang mount untuk progressId hanya memuat record tersimpan. Draft diperiksa pada cabang `elseif` terpisah. Akibatnya label Lanjutkan draft tidak menjamin draft perubahan dimuat; tombol Open pada tabel punya masalah yang sama.

**Seharusnya:** Utamakan draft eksplisit jika ada. Ketika membuka progress, cari draft tertaut terbaru dan isi editor dari draft tersebut. Tetapkan draftUuid agar Simpan menghapus draft yang benar, sedangkan record applied tetap terpisah sebelum Simpan.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`. Semua perubahan tersedia di bagian 5.

### F11 — Waktu draft hilang saat resume; zona waktu tidak seragam

**Prioritas:** P2.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:114`; `resources/views/livewire/prototype/draft-list.blade.php:19`.

**Sekarang:** Saat membuka draft, status menjadi saved tetapi lastSavedTime tidak dipulihkan. Indikator dapat berhenti pada `Draft demo tersimpan ·`. Format jam pada kartu draft mengikuti timezone hasil parsing, sementara reference selalu Asia/Jakarta.

**Seharusnya:** Pulihkan updated_at ke lastSavedTime dan format jam secara konsisten ke Asia/Jakarta. Jangan mengubah timestamp datanya hanya untuk mengubah tampilan.

**Kode:** `app/Support/UiFormat.php`, `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/draft-list.blade.php`. Semua perubahan tersedia di bagian 5.

### F12 — URL editor Closed masih menampilkan formulir yang tampak dapat diedit

**Prioritas:** P1.

**Bukti:** `resources/views/livewire/prototype/progress-form.blade.php:11`; `resources/views/livewire/prototype/progress-form.blade.php:23`.

**Sekarang:** Header menampilkan Open setiap kali progressId ada, termasuk Closed. Banner Closed ditambahkan, tetapi sebagian besar input tetap aktif; hanya Simpan yang disabled. Reference mengarahkan Closed ke detail read-only dan mengharuskan Reopen.

**Seharusnya:** Saat membuka id atau draft yang tertaut ke Closed, arahkan ke detail. Jaga guard pada seluruh mutasi draft/action/file, tidak hanya Simpan. Status header juga harus mengikuti isClosed jika status berubah selama sesi.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F13 — Foto problem tidak membuka file picker

**Prioritas:** P1.

**Bukti:** `resources/views/livewire/prototype/progress-form.blade.php:253`; `app/Livewire/Prototype/ProgressForm.php:709`.

**Sekarang:** Tombol menambahkan nama file simulasi dan path null. Admin tidak bisa memilih foto miliknya; thumbnail menjadi placeholder. Reference memakai input file dan FileReader untuk preview gambar yang benar-benar dipilih.

**Seharusnya:** Kembalikan label photo-add dengan input JPEG/PNG/WebP multiple. Pada prototype, simpan data URL gambar terpilih dalam adapter yang ada. Batas 2 MB pada kode adalah batas demo dari reference, bukan keputusan kebijakan upload produksi.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F14 — Klik thumbnail editor menghapus foto

**Prioritas:** P1.

**Bukti:** `resources/views/livewire/prototype/progress-form.blade.php:238`.

**Sekarang:** Thumbnail memanggil removeSimulationPhoto; aria-label juga menyebut Hapus foto. Di reference, thumbnail memanggil pembesaran foto. Ini perbedaan aksi yang berpotensi membuat pengguna kehilangan foto dari draft.

**Seharusnya:** Klik thumbnail harus membuka preview. Kirim ID problem/foto ke action, cari data di state, dan tampilkan native dialog. Jangan mengirim nama file mentah sebagai potongan ekspresi JavaScript.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`, `app/Livewire/Prototype/ProgressDetail.php`, `resources/views/livewire/prototype/progress-detail.blade.php`. Semua perubahan tersedia di bagian 5.

### F15 — Dokumen feedback berupa dummy; daftar file punya aksi tambahan

**Prioritas:** P1.

**Bukti:** `resources/views/livewire/prototype/progress-form.blade.php:450`.

**Sekarang:** Tombol langsung membuat dokumen_feedback.pdf 45 KB. Reference membuka picker dan menampilkan metadata nama/ukuran file terpilih. Port menambahkan tombol hapus dan margin 8 px; reference memakai file-kind `Simulasi lampiran` dan spacing dari file-list.

**Seharusnya:** Gunakan input multiple dan render satu komponen daftar file yang sama dengan acuan. Untuk prototype, cukup metadata; jangan mengklaim isi dokumen sudah di-upload/disimpan ke server produksi.

**Kode:** `resources/views/components/ui/reference-files.blade.php`, `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F16 — Tambah dokumen kegiatan tidak tersambung; lampiran lama tidak terlihat di editor

**Prioritas:** P1.

**Bukti:** `resources/views/livewire/prototype/progress-form.blade.php:508`.

**Sekarang:** Input file tidak memiliki wire:model, handler change, atau penerusan metadata ke attachments. Editor juga tidak merender daftar attachments yang sudah dimiliki progress. Tombol terlihat tersedia tetapi hasil pemilihan tidak ditampilkan/disimpan.

**Seharusnya:** Sambungkan picker ke attachFiles dengan pemilik progress dan tampilkan attachments di bawah attachment-line. Satu daftar untuk kegiatan, daftar lain untuk tiap feedback; jangan tertukar owner.

**Kode:** `resources/views/components/ui/reference-files.blade.php`, `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F17 — Tambah/hapus baris tidak selalu autosave dan tidak memindahkan fokus

**Prioritas:** P1.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:518`; `app/Livewire/Prototype/ProgressForm.php:742`.

**Sekarang:** Beberapa action PHP hanya memutasi array. Hook updated untuk input Livewire tidak otomatis dipanggil oleh penugasan properti di dalam action PHP. Reference secara eksplisit queueDraft dan memfokuskan input baris baru setelah penambahan.

**Seharusnya:** Panggil penyimpanan draft setelah mutasi yang relevan, tanpa menerapkan ke progress. Kirim selector fokus setelah render untuk problem/CA/Next Action/feedback baru. Normalisasi tetap membuang skeleton kosong dari payload tersimpan.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F18 — Script editor global tidak mengikuti lifecycle komponen

**Prioritas:** P1.

**Bukti:** `resources/views/livewire/prototype/progress-form.blade.php:593`.

**Sekarang:** Script biasa memasang listener global dan hook morph tanpa cleanup. Inisialisasi bergantung DOMContentLoaded, yang sudah lewat bila editor dimount setelah navigasi Livewire. Ada risiko auto-grow tidak terpasang atau shortcut/event handler bertambah setelah mount ulang.

**Seharusnya:** Pasang perilaku pada root Alpine editor dengan init/destroy. Auto-grow awal dan setelah perubahan DOM, event fokus, serta shortcut hanya hidup selama editor itu hidup. Ini risiko lifecycle berdasarkan source, bukan hasil reproduksi browser di audit ini.

**Kode:** `resources/views/livewire/prototype/progress-form.blade.php`. Semua perubahan tersedia di bagian 5.

### F19 — Navigasi dapat mendahului debounce autosave

**Prioritas:** P1.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:495`; `resources/views/livewire/prototype/progress-form.blade.php:15`.

**Sekarang:** Kembali dan link ringkasan/navigasi tidak memiliki langkah memastikan perubahan terakhir sudah dipersist. Reference memanggil flushDraft sebelum berpindah. Dengan debounce 300 ms, perpindahan cepat berisiko melewati penyimpanan terakhir.

**Seharusnya:** Navigasi dalam editor menunggu pekerjaan file, lalu memicu action wire:click yang menguras debounce Livewire dan menyimpan draft sebelum redirect. Jika gagal, tetap pada editor. Patch tidak menjanjikan autosave saat browser crash, koneksi putus, atau hard reload; itu perlu uji tersendiri.

**Kode:** `resources/views/livewire/prototype/progress-form.blade.php`, `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/shell.blade.php`. Semua perubahan tersedia di bagian 5.

### F20 — Status saving dan jumlah draft sidebar tidak mencerminkan siklus aktual

**Prioritas:** P1.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:448`; `app/Livewire/Prototype/Shell.php:29`.

**Sekarang:** updated mengubah status saving lalu saved dalam request yang sama; intermediate saving belum tentu pernah sampai ke browser. Parent Shell tidak mendengarkan pembuatan/pembuangan draft, sehingga count di sidebar dapat tertinggal saat anak berubah.

**Seharusnya:** Tampilkan isian pending lewat wire:dirty dan keberhasilan dari hasil persist. Dispatch draft-changed pada penyimpanan yang sukses; Shell mendengarkan draft-changed dan draft-discarded agar hitungan dirender ulang.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`, `resources/views/livewire/prototype/progress-form.blade.php`, `app/Livewire/Prototype/Shell.php`. Semua perubahan tersedia di bagian 5.

### F21 — Payload draft belum dinormalisasi seperti reference

**Prioritas:** P2.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:455`; `app/Prototype/FakeDraftStore.php:79`.

**Sekarang:** Simpan progress memanggil cleanProgressPayload, tetapi jalur autosave draft memakai payload mentah. isEmptyDraft menganggap array skeleton sebagai isi dan tidak menilai semua field secara setara. Reference membersihkan draft dengan cleanProgress tanpa memutasi editor.

**Seharusnya:** Normalisasi salinan payload sebelum penyimpanan draft. Gunakan fingerprint payload bermakna untuk membedakan perubahan dan kondisi awal; jangan memangkas array editor aktif. Pertahankan isian PIC/tanggal/foto/file parsial dan ID record applied.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`. Semua perubahan tersedia di bagian 5.

### F22 — Peran filter PIC default salah; PIC Progress legacy masih ikut dicari

**Prioritas:** P1.

**Bukti:** `app/Livewire/Prototype/Dashboard.php:38`; `app/Prototype/FakeStore.php:339`.

**Sekarang:** Reference default Semua peran. Port default progress yang labelnya PIC Customer, lalu mencocokkan pic_progress ATAU pic_customer. Hasil pencarian dapat muncul berdasarkan peran yang sudah dihapus dari produk.

**Seharusnya:** Default/reset adalah all. Opsi PIC Customer memakai key customer dan hanya mencari pic_customer. CA, Next Action, dan feedback tetap mencari pemilik masing-masing. Field legacy tidak perlu dihapus dari database untuk membetulkan filter UI.

**Kode:** `app/Livewire/Prototype/Dashboard.php`, `app/Prototype/FakeStore.php`, `resources/views/livewire/prototype/dashboard.blade.php`. Semua perubahan tersedia di bagian 5.

### F23 — Filter hilang saat berpindah layar; scope pencarian berbeda

**Prioritas:** P2.

**Bukti:** `app/Livewire/Prototype/Dashboard.php:87`; `app/Prototype/FakeStore.php:289`.

**Sekarang:** Reference mempertahankan filters saat nav, dan input pencarian utamanya mencari JO/Part No/nama part. Component mount port dapat mengembalikan default; query store juga mencari customer, notes, dan problem, sehingga baris hasil tidak sama pada query tertentu.

**Seharusnya:** Simpan state filter prototype untuk perpindahan layar, dengan menu Progress tetap memilih Semua. Kirim identity_only pada query khusus UI ini; pencarian store di pemanggil lain tetap dapat mempertahankan scope lamanya. Jangan mengubah isi histori.

**Kode:** `app/Livewire/Prototype/Dashboard.php`, `app/Prototype/FakeStore.php`, `resources/views/livewire/prototype/shell.blade.php`. Semua perubahan tersedia di bagian 5.

### F24 — Format tanggal tidak konsisten dengan acuan

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/dashboard.blade.php:217`; `resources/views/livewire/prototype/progress-detail.blade.php:15`.

**Sekarang:** Daftar memakai tanggal tanpa zero padding; detail memakai format nama bulan Inggris; waktu draft tidak dipaksa Jakarta. Reference memakai tanggal Indonesia dua digit dan jam Jakarta. Label bulan ringkasan juga masih bergantung locale aplikasi.

**Seharusnya:** Gunakan helper UiFormat untuk tanggal DD MMM YYYY ber-locale id dan jam Asia/Jakarta; paksa locale id pada ringkasan. Field tanggal tetap menyimpan YYYY-MM-DD, dan Model tetap teks.

**Kode:** `app/Support/UiFormat.php`, `resources/views/livewire/prototype/dashboard.blade.php`, `resources/views/livewire/prototype/shell.blade.php`, `resources/views/livewire/prototype/project-detail.blade.php`, `resources/views/livewire/prototype/progress-detail.blade.php`, `resources/views/livewire/prototype/draft-list.blade.php`, `app/Prototype/FakeStore.php`. Semua perubahan tersedia di bagian 5.

### F25 — Empty state daftar memiliki copy dan tombol berbeda

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/dashboard.blade.php:177`.

**Sekarang:** Port membedakan kondisi filter dengan Open kosong dan menambahkan teks panjang serta Tambah Progress Baru. Reference tableHTML memakai satu empty state `Tidak ada progress yang cocok` dan tombol `Bersihkan filter`.

**Seharusnya:** Samakan copy dan aksi empty state daftar dashboard/progress. Empty state histori project yang spesifik konteks dicatat terpisah sebagai delta kecil; tidak ditukar dengan pesan Open kosong.

**Kode:** `resources/views/livewire/prototype/dashboard.blade.php`. Semua perubahan tersedia di bagian 5.

### F26 — Status pada histori project selalu tampil

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/project-detail.blade.php:186`.

**Sekarang:** Reference menggunakan tableHTML bersama; status di bawah Activity hanya muncul ketika filter status bersama adalah Semua. Port histori project menambah status pada setiap keadaan, sehingga isi/tinggi baris berbeda.

**Seharusnya:** Gunakan status filter bersama untuk menentukan penampilan badge. Ini menyamakan konteks tableHTML tanpa mengubah status record. Urutan histori tetap perlu diuji dengan dataset sama; lihat catatan delta minor di bawah.

**Kode:** `resources/views/livewire/prototype/project-detail.blade.php`, `app/Livewire/Prototype/Dashboard.php`. Semua perubahan tersedia di bagian 5.

### F27 — Toast ada yang menetap dan ada yang tidak pernah tampil

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/draft-list.blade.php:88`; `app/Livewire/Prototype/ProjectForm.php:122`.

**Sekarang:** Toast berbasis flashMessage tidak punya timer 4,2 detik. Project/progress menyimpan successMessage lalu redirect sehingga komponen yang menampilkannya tidak aktif lagi. Toast global layout kosong tidak dihubungkan ke event.

**Seharusnya:** Satu toast global menerima event ui-toast; redirect membawa flash session yang sama. Timer 4200 ms di-reset setiap pesan, dan dibersihkan ketika elemen hilang.

**Kode:** `resources/views/components/layouts/app.blade.php`, `app/Livewire/Prototype/DraftList.php`, `app/Livewire/Prototype/ProgressDetail.php`, `app/Livewire/Prototype/ProjectForm.php`, `app/Livewire/Prototype/ProgressForm.php`. Semua perubahan tersedia di bagian 5.

### F28 — Spacing lampiran/foto di detail ditimpa inline style

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/progress-detail.blade.php:92`; `resources/views/livewire/prototype/progress-detail.blade.php:149`.

**Sekarang:** Margin foto dan file pada detail ditulis ulang. Preview foto juga menambahkan radius dan style paragraf yang tidak ada pada reference openDialog. Nilai kosong PIC Customer/No Doc memakai null fallback sehingga string kosong bisa tampak blank.

**Seharusnya:** Lepas override inline tersebut dan gunakan aturan CSS reference. Preview memakai full-photo dan paragraf biasa. Tampilkan em dash untuk nilai kosong sebagaimana kv() reference.

**Kode:** `resources/views/livewire/prototype/progress-detail.blade.php`. Semua perubahan tersedia di bagian 5.

### F29 — Teks tersembunyi legacy dan aria-current kurang tepat

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/dashboard.blade.php:6`; `resources/views/livewire/prototype/shell.blade.php:36`.

**Sekarang:** Ada label sr-only tambahan untuk mempertahankan kata yang dicari test lama; beberapa menduplikasi informasi. aria-current dibentuk dengan mencetak string berisi tanda kutip melalui output Blade escaped. Nilai atribut dapat mencakup literal quote, bukan token page.

**Seharusnya:** Hapus hanya teks kompatibilitas test yang bukan bagian UI/acuan. Pertahankan caption tabel dan label aksesibel yang berguna. Tulis atribut aria-current secara literal di dalam @if, dan ubah test untuk memeriksa perilaku/label yang benar.

**Kode:** `resources/views/livewire/prototype/shell.blade.php`, `resources/views/livewire/prototype/dashboard.blade.php`, `resources/views/livewire/prototype/draft-list.blade.php`, `resources/views/livewire/prototype/progress-detail.blade.php`, `resources/views/livewire/prototype/project-detail.blade.php`. Semua perubahan tersedia di bagian 5.

### F30 — Tab status belum punya navigasi tombol panah

**Prioritas:** P2.

**Bukti:** `resources/views/livewire/prototype/dashboard.blade.php:57`.

**Sekarang:** Port memberi role tablist/tab tetapi tidak memasang penanganan ArrowLeft/ArrowRight seperti reference. Mouse masih bekerja; interaksi keyboard berbeda.

**Seharusnya:** Tambahkan handler lokal pada tablist untuk memilih dan memfokuskan tab tetangga, tanpa memindahkan fokus ke luar daftar.

**Kode:** `resources/views/livewire/prototype/dashboard.blade.php`. Semua perubahan tersedia di bagian 5.

### F31 — Data contoh berbeda sehingga screenshot memang tidak akan sama

**Prioritas:** Konteks QA.

**Bukti:** `resources/fixtures/prototype.json:7`.

**Sekarang:** ZIP memuat BRACKET FRONT LH (FIKTIF), JO 0012/NM/2026, dan histori dengan 5 Open/3 Closed. Reference berisi Bracket fender, RH, JO-2026-018, dan 6 Open/2 Closed. Keduanya 3 project/8 progress/2 draft. Aktivitas Oktober pada fixture ZIP berjumlah 1, reference 8. Panjang teks dan jumlah baris ikut berubah.

**Seharusnya:** Samakan dataset/state sebelum menilai layout. Jangan hardcode statistik atau mengganti histori admin supaya menyerupai demo. Untuk memisahkan perbedaan data dari CSS, bandingkan form baru kosong dulu; utilitas QA di bawah bisa mengisi reference lokal dengan fixture ZIP.

### F32 — Test dan checklist belum membuktikan paritas

**Prioritas:** Konteks QA.

**Bukti:** `app/Livewire/Prototype/ProgressForm.php:135`; `docs/progress/UI_REBUILD_SESSION.md:471`.

**Sekarang:** Mount menyediakan kerangka awal dengan cabang khusus yang berbeda ketika unit test tanpa query tab. Dokumen sesi mengklaim checklist 62/62, tetapi source masih mempunyai thumbnail-delete dan input lampiran tanpa handler. Assertion teks atau build lulus tidak membuktikan seluruh interaksi/visual.

**Seharusnya:** Gunakan jalur mount yang sama di test dan browser. Tambahkan regresi untuk blank state, resume draft, Closed, feedback tanpa tanggal, foto/lampiran, dan filter PIC. Lakukan ulang perbandingan browser sebelum memberi tanda selesai visual.

**Kode:** `app/Livewire/Prototype/ProgressForm.php`, `tests/Feature/PrototypeUiParityRegressionTest.php`. Semua perubahan tersedia di bagian 5.

### Delta kecil yang sengaja dipisahkan dari patch utama

Tiga hal berikut terlihat pada source dan perlu dinilai dengan dataset/state yang sama; jangan menyebut seluruhnya bug hanya karena berbeda dari demo:

| Area | Selisih | Tindakan |
| --- | --- | --- |
| Histori project kosong | Port mempunyai pesan spesifik histori; reference memanggil empty state generik tabel. | Tidak termasuk fixture awal. Jika diperlukan kesamaan copy mutlak, gunakan blok pengganti di bagian 6. |
| Urutan histori dan tanggal kosong | Port menggunakan query store terurut; reference mengambil urutan array. Reverse sort reference juga dapat menaikkan tanggal kosong. | Pertahankan tanggal kosong di akhir sesuai kontrak aplikasi; jangan menyalin efek samping demo yang merusak kegunaan. Bandingkan urutan eksplisit ketika QA. |
| Preview foto pada detail read-only | Port menambahkan klik pembesaran; reference detail hanya menampilkan thumbnail, sedangkan editor mempunyai preview. | Patch menjaga pembesaran pada detail dan menyamakan dialognya. Ini fungsi tambahan kecil yang sudah ada, bukan penyebab utama selisih layout. Untuk DOM mutlak sama, thumbnail detail dapat dijadikan img biasa. |

## 4. Cara memakai kode di dokumen ini

Seluruh blok `diff` di bagian 5 membentuk **satu patch yang saling terkait**. File baru, method baru, dan view pemanggilnya harus diterapkan bersama. Jangan hanya menyalin view yang memanggil `attachFiles` tanpa method PHP dan komponen daftar file.

1. Kerjakan di branch kerja aplikasi. Simpan perubahan lokal yang sudah ada melalui kebiasaan kerja repository; jangan reset repository.
2. Simpan dokumen ini sebagai `AUDIT_UI_DAN_KODE_KOREKSI_NEW_MODEL.md` di root repository.
3. Ekstrak blok diff dengan script di bawah. Script hanya menulis patch; tidak mengedit aplikasi.
4. Jalankan pemeriksaan patch. Jika ada hunk gagal karena source telah berubah sejak ZIP ini, baca dan sesuaikan perubahan terkait; jangan memaksa overwrite.
5. Terapkan, build, lalu jalankan pemeriksaan pada bagian 8. Tidak ada migrasi database atau instalasi framework baru.

```python
from pathlib import Path
import re

md = Path('AUDIT_UI_DAN_KODE_KOREKSI_NEW_MODEL.md').read_text(encoding='utf-8')
blocks = re.findall(r'^```diff\n(.*?)^```\s*$', md, flags=re.M | re.S)
assert len(blocks) == 21, f'Expected 21 file patches, found {len(blocks)}'
Path('ui-parity.patch').write_text(''.join(blocks), encoding='utf-8')
print('ui-parity.patch dibuat')
```

```bash
git apply --check ui-parity.patch
git apply ui-parity.patch
npm run build
php artisan view:cache
php artisan test --filter=PrototypeUiParityRegressionTest
```

**Batas kode:** ini tetap prototype dengan FakeStore. Foto dipilih sungguhan dan preview data URL disimpan di adapter prototype; dokumen hanya metadata seperti reference. Kode tidak membangun storage upload produksi, autentikasi, migrasi Access, atau schema PostgreSQL. Pergantian entry CSS hanya ditujukan pada layout prototype. PRD tidak perlu diedit untuk menerapkan koreksi ini.

Untuk AI dengan konteks kecil, gunakan temuan F01–F07 dahulu untuk memahami fondasi, kemudian F08–F21 untuk editor/draft, dan F22–F30 untuk daftar/detail. Jangan meminta AI merancang kembali warna, grid, maupun komponen.

## 5. Kode koreksi lengkap per file

Angka baris pada diff mengacu ke ZIP yang diaudit. Baris berawalan `-` dihapus; baris berawalan `+` ditambahkan. Tidak ada placeholder `...` yang harus ditebak pada patch ini.

### C01 — `resources/css/prototype.css`

```diff
--- /dev/null
+++ b/resources/css/prototype.css
@@ -0,0 +1,13 @@
+/* Dedicated prototype entry: match reference without Tailwind Preflight. */
+@layer theme, base, components, utilities;
+@import 'tailwindcss/theme.css' layer(theme);
+@import 'tailwindcss/utilities.css' layer(utilities);
+@import './reference.css';
+@source '../views/**/*.blade.php';
+@source '../../app/Livewire/**/*.php';
+
+/* Reference uses buttons. Laravel navigation uses anchors with the same style. */
+a.btn { color: var(--ink); }
+a.btn.primary { color: #fff; }
+a.btn.ghost { color: var(--muted); }
+a.btn.text { color: var(--blue); }
```

### C02 — `vite.config.js`

```diff
--- a/vite.config.js
+++ b/vite.config.js
@@ -5,7 +5,7 @@
 export default defineConfig({
     plugins: [
         laravel({
-            input: ['resources/css/app.css', 'resources/js/app.js'],
+            input: ['resources/css/app.css', 'resources/css/prototype.css', 'resources/js/app.js'],
             refresh: true,
         }),
         tailwindcss(),
```

### C03 — `app/Support/UiFormat.php`

```diff
--- /dev/null
+++ b/app/Support/UiFormat.php
@@ -0,0 +1,22 @@
+<?php
+
+namespace App\Support;
+
+use Illuminate\Support\Carbon;
+
+final class UiFormat
+{
+    public static function date(?string $value): string
+    {
+        return filled($value)
+            ? Carbon::parse($value, 'Asia/Jakarta')->locale('id')->isoFormat('DD MMM YYYY')
+            : 'Belum diisi';
+    }
+
+    public static function time(?string $value): string
+    {
+        return filled($value)
+            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('H:i')
+            : '';
+    }
+}
```

### C04 — `resources/views/components/ui/reference-dialog.blade.php`

```diff
--- /dev/null
+++ b/resources/views/components/ui/reference-dialog.blade.php
@@ -0,0 +1,24 @@
+@props(['id', 'title', 'cancel', 'wide' => false])
+
+<dialog
+    id="{{ $id }}"
+    class="{{ $wide ? 'dialog-wide' : '' }}"
+    aria-labelledby="{{ $id }}-title"
+    wire:ignore.self
+    x-data="{
+        init() { this.$nextTick(() => { if (!this.$el.open) this.$el.showModal(); }); },
+        destroy() { if (this.$el.open) this.$el.close(); }
+    }"
+    x-on:cancel.prevent="$wire.{{ $cancel }}()"
+>
+    <div class="dialog-head">
+        <h2 id="{{ $id }}-title">{{ $title }}</h2>
+        <button type="button" class="icon-btn" wire:click="{{ $cancel }}" aria-label="Tutup dialog">
+            <x-ui.icon name="close" />
+        </button>
+    </div>
+    <div class="dialog-body">{{ $slot }}</div>
+    @isset($footer)
+        <div class="dialog-foot">{{ $footer }}</div>
+    @endisset
+</dialog>
```

### C05 — `resources/views/components/ui/reference-files.blade.php`

```diff
--- /dev/null
+++ b/resources/views/components/ui/reference-files.blade.php
@@ -0,0 +1,15 @@
+@props(['files' => []])
+@if(count($files))
+    <div class="file-list">
+        @foreach($files as $file)
+            <div class="file-row">
+                <x-ui.icon name="file" />
+                <div>
+                    <strong>{{ $file['name'] ?? $file['filename'] ?? 'dokumen' }}</strong>
+                    <small>{{ max(1, round(($file['size'] ?? 0) / 1024)) }} KB</small>
+                </div>
+                <span class="file-kind">Simulasi lampiran</span>
+            </div>
+        @endforeach
+    </div>
+@endif
```

### C06 — `resources/views/components/layouts/app.blade.php`

```diff
--- a/resources/views/components/layouts/app.blade.php
+++ b/resources/views/components/layouts/app.blade.php
@@ -7,7 +7,7 @@
     <meta name="environment" content="PROTOTYPE">
     <meta name="description" content="Prototype desain Progress New Model: dashboard aktivitas, project, dan form progress dengan problem serta corrective action bertingkat.">
     <title>{{ $title ?? 'Progress New Model — Prototype UI' }}</title>
-    @vite(['resources/css/app.css', 'resources/js/app.js'])
+    @vite(['resources/css/prototype.css', 'resources/js/app.js'])
     @livewireStyles
 </head>
 <body x-data="{ mobileMenu: false }" :class="{ 'menu-open': mobileMenu }">
@@ -15,8 +15,22 @@
     <div id="app">
         {{ $slot }}
     </div>
-    <div id="toast" class="toast" role="status" aria-live="polite" hidden></div>
-    <dialog id="dialog" aria-labelledby="dialog-title"></dialog>
+    <div
+        id="toast" class="toast" role="status" aria-live="polite"
+        x-data="{
+            message: '', visible: false, timer: null,
+            init() { const initial = @js(session('ui-toast', '')); if (initial) this.show(initial); },
+            show(text) {
+                clearTimeout(this.timer);
+                this.message = text;
+                this.visible = true;
+                this.timer = setTimeout(() => this.visible = false, 4200);
+            },
+            destroy() { clearTimeout(this.timer); }
+        }"
+        x-on:ui-toast.window="show($event.detail.message)"
+        x-show="visible" style="display:none"
+    ><span x-text="message"></span></div>
     @livewireScripts
 </body>
 </html>
```

### C07 — `app/Livewire/Prototype/Shell.php`

```diff
--- a/app/Livewire/Prototype/Shell.php
+++ b/app/Livewire/Prototype/Shell.php
@@ -4,6 +4,7 @@
 
 use App\Prototype\FakeStore;
 use Livewire\Component;
+use Livewire\Attributes\On;
 
 class Shell extends Component
 {
@@ -26,6 +27,8 @@
         $this->refreshStats();
     }
 
+    #[On('draft-changed')]
+    #[On('draft-discarded')]
     public function refreshStats(): void
     {
         $this->stats = FakeStore::getStats();
```

### C08 — `resources/views/livewire/prototype/shell.blade.php`

```diff
--- a/resources/views/livewire/prototype/shell.blade.php
+++ b/resources/views/livewire/prototype/shell.blade.php
@@ -21,7 +21,7 @@
 <div>
     <button class="scrim" @click="mobileMenu = false" aria-label="Tutup navigasi"></button>
     <aside class="sidebar" aria-label="Navigasi utama">
-        <a class="brand" href="{{ route('prototype.index', ['tab' => 'dashboard']) }}" wire:click.prevent="setTab('dashboard')" @click="mobileMenu = false">
+        <a class="brand" href="{{ route('prototype.index', ['tab' => 'dashboard']) }}" @click="mobileMenu = false">
             <span>
                 <span class="brand-name">New Model</span>
                 <span class="brand-sub" style="display:block">PROGRESS TRACKER</span>
@@ -29,49 +29,31 @@
         </a>
         <div class="nav-label">WORKSPACE</div>
         <nav class="nav">
-            <button
-                type="button"
-                wire:click="setTab('dashboard')"
+            <a href="{{ route('prototype.index', ['tab' => 'dashboard']) }}"
                 class="nav-item {{ $activeNav === 'dashboard' ? 'active' : '' }}"
-                {{ $activeNav === 'dashboard' ? 'aria-current="page"' : '' }}
-                @click="mobileMenu = false"
-            >
-                <x-ui.icon name="dashboard" />
-                <span>Dashboard</span>
-            </button>
-            <button
-                type="button"
-                wire:click="setTab('projects')"
+                @if($activeNav === 'dashboard') aria-current="page" @endif
+                @click="mobileMenu = false">
+                <x-ui.icon name="dashboard" /><span>Dashboard</span>
+            </a>
+            <a href="{{ route('prototype.index', ['tab' => 'projects']) }}"
                 class="nav-item {{ $activeNav === 'project' ? 'active' : '' }}"
-                {{ $activeNav === 'project' ? 'aria-current="page"' : '' }}
-                @click="mobileMenu = false"
-            >
-                <x-ui.icon name="project" />
-                <span>Project</span>
-            </button>
-            <button
-                type="button"
-                wire:click="setTab('progresses')"
+                @if($activeNav === 'project') aria-current="page" @endif
+                @click="mobileMenu = false">
+                <x-ui.icon name="project" /><span>Project</span>
+            </a>
+            <a href="{{ route('prototype.index', ['tab' => 'progresses']) }}"
                 class="nav-item {{ $activeNav === 'progress' ? 'active' : '' }}"
-                {{ $activeNav === 'progress' ? 'aria-current="page"' : '' }}
-                @click="mobileMenu = false"
-            >
-                <x-ui.icon name="progress" />
-                <span>Progress</span>
-            </button>
-            <button
-                type="button"
-                wire:click="setTab('drafts')"
+                @if($activeNav === 'progress') aria-current="page" @endif
+                @click="mobileMenu = false">
+                <x-ui.icon name="progress" /><span>Progress</span>
+            </a>
+            <a href="{{ route('prototype.index', ['tab' => 'drafts']) }}"
                 class="nav-item {{ $activeNav === 'draft' ? 'active' : '' }}"
-                {{ $activeNav === 'draft' ? 'aria-current="page"' : '' }}
-                @click="mobileMenu = false"
-            >
-                <x-ui.icon name="draft" />
-                <span>Draft</span>
-                @if($draftCount > 0)
-                    <span class="nav-count">{{ $draftCount }}</span>
-                @endif
-            </button>
+                @if($activeNav === 'draft') aria-current="page" @endif
+                @click="mobileMenu = false">
+                <x-ui.icon name="draft" /><span>Draft</span>
+                <span class="nav-count">{{ $draftCount }}</span>
+            </a>
         </nav>
     </aside>
 
@@ -124,7 +106,6 @@
             <div class="page-head">
                 <div>
                     <h1>Project Master</h1>
-                    <span class="sr-only">Daftar Project Master</span>
                 </div>
                 <a
                     href="?tab=projects&action=new"
@@ -155,7 +136,7 @@
                         @foreach($projects as $p)
                             @php
                                 $t0Target = !empty($p['t0_target']) ? $p['t0_target'] : (!empty($p['t0_target_date']) ? $p['t0_target_date'] : null);
-                                $t0TargetFormatted = !empty($t0Target) ? \Illuminate\Support\Carbon::parse($t0Target)->locale('id')->isoFormat('D MMM Y') : 'Belum diisi';
+                                $t0TargetFormatted = !empty($t0Target) ? \App\Support\UiFormat::date($t0Target) : 'Belum diisi';
                                 $progressCount = count(\App\Prototype\FakeStore::getProgresses(['project_id' => $p['id'], 'status' => 'all']));
                             @endphp
                             <tr data-testid="project-row" data-id="{{ $p['id'] }}">
@@ -178,13 +159,11 @@
                 </table>
             </section>
 
-            @if(request()->query('action') === 'new')
-                <!-- Mode Tambah Project Baru (Modal Dialog) -->
-                <livewire:prototype.project-form />
-            @elseif(request()->query('edit'))
-                <!-- Mode Edit Project (Modal Dialog) -->
-                <livewire:prototype.project-form :projectId="request()->query('edit')" />
-            @endif
+        @endif
+        @if(request()->query('action') === 'new')
+            <livewire:prototype.project-form />
+        @elseif(request()->query('edit'))
+            <livewire:prototype.project-form :projectId="request()->query('edit')" />
         @endif
 
     @elseif($activeTab === 'form')
```

### C09 — `app/Livewire/Prototype/ProjectForm.php`

```diff
--- a/app/Livewire/Prototype/ProjectForm.php
+++ b/app/Livewire/Prototype/ProjectForm.php
@@ -46,10 +46,12 @@
     public ?string $remark = '';
 
     public ?string $successMessage = null;
+    public bool $returnToDetail = false;
 
     public function mount(?string $projectId = null): void
     {
-        $this->projectId = $projectId ?? request()->query('id');
+        $this->projectId = $projectId;
+        $this->returnToDetail = filled(request()->query('detail'));
 
         if ($this->projectId) {
             $project = FakeStore::findProject($this->projectId);
@@ -121,7 +123,10 @@
             $this->projectId = $saved['id'];
             $this->successMessage = 'Data Master Project berhasil disimpan.';
             $this->dispatch('project-saved', projectId: $saved['id']);
-            return redirect()->to('?tab=projects&detail=' . $saved['id']);
+            $query = ['tab' => 'projects'];
+            if ($this->returnToDetail) $query['detail'] = $saved['id'];
+            return redirect()->to(route('prototype.index', $query))
+                ->with('ui-toast', 'Project tersimpan. Identitas seluruh progress mengikuti data terbaru.');
         } catch (ValidationException $e) {
             foreach ($e->errors() as $field => $messages) {
                 foreach ($messages as $msg) {
@@ -133,7 +138,9 @@
 
     public function cancel()
     {
-        return redirect()->to('?tab=projects');
+        $query = ['tab' => 'projects'];
+        if ($this->returnToDetail && $this->projectId) $query['detail'] = $this->projectId;
+        return redirect()->to(route('prototype.index', $query));
     }
 
     public function render()
```

### C10 — `resources/views/livewire/prototype/project-form.blade.php`

```diff
--- a/resources/views/livewire/prototype/project-form.blade.php
+++ b/resources/views/livewire/prototype/project-form.blade.php
@@ -1,25 +1,12 @@
-<div class="fixed inset-0 z-50 flex items-center justify-center bg-[#19263865] p-4 overflow-y-auto">
-    <div class="dialog dialog-wide bg-white rounded-lg shadow-2xl w-full max-h-[90vh] flex flex-col" role="dialog" aria-modal="true" aria-labelledby="dialog-title">
-        <div class="dialog-head">
-            <h2 id="dialog-title">{{ $projectId ? 'Edit project' : 'Tambah project' }}</h2>
-            <button type="button" class="icon-btn" wire:click="cancel" aria-label="Tutup dialog">
-                <x-ui.icon name="close" />
-            </button>
-        </div>
-
-        <div class="dialog-body">
-            @if($errors->any())
-                <div id="master-error" class="field-error mb-4" role="alert">
-                    {{ $errors->first() }}
-                </div>
-            @endif
-
+<div>
+    <x-ui.reference-dialog id="project-dialog" :title="$projectId ? 'Edit project' : 'Tambah project'" cancel="cancel" wide>
             <form wire:submit.prevent="save" id="master-form">
+                <div id="master-error" class="field-error" role="alert">{{ $errors->first() }}</div>
                 <!-- Kelompok 1: Identitas -->
                 <h3 class="group-title">Identitas</h3>
                 <div class="form-grid">
                     <div class="field">
-                        <label class="field-label" for="master-jo">JO No <span class="required">*</span></label>
+                        <label class="field-label" for="master-jo">JO No *</label>
                         <input
                             id="master-jo"
                             class="input @error('jo_no') input-error @enderror"
@@ -27,13 +14,10 @@
                             type="text"
                             data-testid="input-jo-no"
                         >
-                        @error('jo_no')
-                            <div class="field-error">{{ $message }}</div>
-                        @enderror
-                    </div>
-
-                    <div class="field">
-                        <label class="field-label" for="master-part">Part No <span class="required">*</span></label>
+                    </div>
+
+                    <div class="field">
+                        <label class="field-label" for="master-part">Part No *</label>
                         <input
                             id="master-part"
                             class="input @error('part_no') input-error @enderror"
@@ -41,13 +25,10 @@
                             type="text"
                             data-testid="input-part-no"
                         >
-                        @error('part_no')
-                            <div class="field-error">{{ $message }}</div>
-                        @enderror
-                    </div>
-
-                    <div class="field">
-                        <label class="field-label" for="master-name">Part Name <span class="required">*</span></label>
+                    </div>
+
+                    <div class="field">
+                        <label class="field-label" for="master-name">Part Name *</label>
                         <input
                             id="master-name"
                             class="input @error('part_name') input-error @enderror"
@@ -55,9 +36,6 @@
                             type="text"
                             data-testid="input-part-name"
                         >
-                        @error('part_name')
-                            <div class="field-error">{{ $message }}</div>
-                        @enderror
                     </div>
 
                     <div class="field">
@@ -245,16 +223,13 @@
                     <label class="field-label" for="master-remark">Remark Project</label>
                     <textarea
                         id="master-remark"
-                        class="input"
                         wire:model="remark"
                     ></textarea>
                 </div>
             </form>
-        </div>
-
-        <div class="dialog-foot">
+        <x-slot:footer>
             <button type="button" class="btn" wire:click="cancel">Batal</button>
             <button type="button" class="btn primary" wire:click="save">Simpan project</button>
-        </div>
-    </div>
+        </x-slot:footer>
+    </x-ui.reference-dialog>
 </div>
```

### C11 — `app/Livewire/Prototype/ProgressForm.php`

```diff
--- a/app/Livewire/Prototype/ProgressForm.php
+++ b/app/Livewire/Prototype/ProgressForm.php
@@ -7,6 +7,8 @@
 use Illuminate\Support\Str;
 use Illuminate\Validation\ValidationException;
 use Livewire\Component;
+use Livewire\Attributes\Locked;
+use App\Support\UiFormat;
 
 class ProgressForm extends Component
 {
@@ -15,11 +17,10 @@
     // Field Wajib Progress (FR04, AT05)
     public ?string $projectId = '';
     public string $activityDate = '';
-    public string $activity = 'Trial';
+    public string $activity = '';
 
     // Field Utama & PIC Teks Bebas (FR05, AT06)
     public string $notes = '';
-    public string $picProgress = '';
     public ?string $picCustomer = '';
 
     // Field Rincian Parameter Trial (D15 Raw Text - Tersedia untuk semua Activity)
@@ -56,85 +57,64 @@
     public ?array $pendingRemoval = null;
 
     public ?string $successMessage = null;
+    public ?string $pendingUrl = null;
+    public ?array $activePhoto = null;
+
+    #[Locked]
+    public string $draftFingerprint = '';
 
     public function mount(?string $progressId = null, ?string $projectId = null): void
     {
         $this->progressId = $progressId ?? request()->query('id');
-        $initProjectId = $projectId ?? request()->query('project_id');
-        $initDraftUuid = request()->query('draft');
-
-        if ($this->progressId) {
-            $progress = FakeStore::findProgress($this->progressId);
-            if ($progress) {
-                $this->isClosed = strtolower($progress['status'] ?? 'open') === 'closed';
-                $this->projectId = $progress['project_id'] ?? '';
-                $this->activity = $progress['activity'] ?? 'Trial';
-                $this->activityDate = $progress['activity_date'] ?? date('Y-m-d');
-                $this->notes = $progress['notes'] ?? $progress['description'] ?? '';
-                $this->picProgress = $progress['pic_progress'] ?? '';
-                $this->picCustomer = $progress['pic_customer'] ?? '';
-                $this->processTrial = $progress['process_trial'] ?? '';
-                $this->machine = $progress['machine'] ?? '';
-                $this->dieHighRaw = $progress['die_high_raw'] ?? $progress['dh'] ?? '';
-                $this->materialActualTrial = $progress['material_actual_trial'] ?? '';
-                $this->qtyMaterialRaw = $progress['qty_material_raw'] ?? '';
-                $this->qtyProcessRaw = $progress['qty_process_raw'] ?? '';
-                $this->qtySampleRaw = $progress['qty_sample_raw'] ?? '';
-                $this->eventNote = $progress['event_note'] ?? '';
-                $this->remark = $progress['remark'] ?? '';
-                $this->problems = $progress['problems'] ?? [];
-                $this->nextActions = $progress['next_actions'] ?? [];
-                $this->feedbacks = $progress['feedbacks'] ?? [];
-                $this->attachments = $progress['attachments'] ?? [];
-            }
-        } elseif ($initDraftUuid) {
-            $draft = FakeStore::findDraft($initDraftUuid);
-            if ($draft) {
-                $this->draftUuid = $draft['uuid'];
-                $this->progressId = $draft['progress_id'] ?? null;
-                $this->projectId = $draft['project_id'] ?? '';
-                $this->activity = $draft['activity'] ?? 'Trial';
-                $this->activityDate = $draft['activity_date'] ?? date('Y-m-d');
-                $this->notes = $draft['notes'] ?? '';
-                $this->picProgress = $draft['pic_progress'] ?? '';
-                $this->picCustomer = $draft['pic_customer'] ?? '';
-                $this->processTrial = $draft['process_trial'] ?? '';
-                $this->machine = $draft['machine'] ?? '';
-                $this->dieHighRaw = $draft['die_high_raw'] ?? '';
-                $this->materialActualTrial = $draft['material_actual_trial'] ?? '';
-                $this->qtyMaterialRaw = $draft['qty_material_raw'] ?? '';
-                $this->qtyProcessRaw = $draft['qty_process_raw'] ?? '';
-                $this->qtySampleRaw = $draft['qty_sample_raw'] ?? '';
-                $this->eventNote = $draft['event_note'] ?? '';
-                $this->remark = $draft['remark'] ?? '';
-                $this->problems = $draft['problems'] ?? [];
-                $this->nextActions = $draft['next_actions'] ?? [];
-                $this->feedbacks = $draft['feedbacks'] ?? [];
-                $this->attachments = $draft['attachments'] ?? [];
-                $this->autosaveStatus = 'saved';
-            }
-        } else {
-            $this->activityDate = date('Y-m-d');
-            if ($initProjectId) {
-                $this->projectId = $initProjectId;
-            }
-        }
-
-        // Cek guard status Closed pada progress tertaut (baik langsung maupun via draft)
-        if ($this->progressId) {
-            $linked = FakeStore::findProgress($this->progressId);
-            if ($linked && strtolower($linked['status'] ?? 'open') === 'closed') {
-                $this->isClosed = true;
-            }
-        }
-
-        if (! empty($this->projectId)) {
-            $this->selectedProject = FakeStore::findProject($this->projectId);
-        }
-
-        if (! $this->isClosed && (! app()->runningUnitTests() || request()->has('tab'))) {
-            $this->ensureInitialRows();
-        }
+        $draftId = request()->query('draft');
+        $draft = $draftId ? FakeStore::findDraft($draftId) : null;
+        abort_if($draftId && ! $draft, 404);
+
+        if ($draft) {
+            $this->progressId = $draft['progress_id'] ?? null;
+        }
+        $progress = $this->progressId ? FakeStore::findProgress($this->progressId) : null;
+        abort_if($this->progressId && ! $progress, 404);
+
+        if (strtolower($progress['status'] ?? '') === 'closed') {
+            $this->isClosed = true;
+            $this->redirectRoute('prototype.index', ['tab' => 'progresses', 'detail' => $this->progressId]);
+            return;
+        }
+
+        // Opening an existing progress must resume its latest draft, if present.
+        if (! $draft && $this->progressId) {
+            $drafts = array_values(array_filter(FakeDraftStore::all(),
+                fn ($d) => ($d['progress_id'] ?? null) === $this->progressId));
+            usort($drafts, fn ($a, $b) => strtotime($b['updated_at'] ?? '') <=> strtotime($a['updated_at'] ?? ''));
+            $draft = $drafts[0] ?? null;
+        }
+        $row = array_replace($progress ?? [], $draft ?? []);
+        $this->projectId = $row['project_id'] ?? $projectId ?? request()->query('project_id') ?? '';
+        $this->activity = (string) ($row['activity'] ?? '');
+        $this->activityDate = (string) ($row['activity_date'] ?? '');
+        $this->notes = (string) ($row['notes'] ?? $row['description'] ?? '');
+        foreach ([
+            'picCustomer' => 'pic_customer', 'processTrial' => 'process_trial',
+            'machine' => 'machine', 'dieHighRaw' => 'die_high_raw',
+            'materialActualTrial' => 'material_actual_trial', 'qtyMaterialRaw' => 'qty_material_raw',
+            'qtyProcessRaw' => 'qty_process_raw', 'qtySampleRaw' => 'qty_sample_raw',
+            'eventNote' => 'event_note', 'remark' => 'remark',
+        ] as $property => $key) {
+            $this->{$property} = (string) ($row[$key] ?? ($key === 'die_high_raw' ? ($row['dh'] ?? '') : ''));
+        }
+        $this->problems = $row['problems'] ?? [];
+        $this->nextActions = $row['next_actions'] ?? [];
+        $this->feedbacks = $row['feedbacks'] ?? [];
+        $this->attachments = $row['attachments'] ?? [];
+        if ($draft) {
+            $this->draftUuid = $draft['uuid'];
+            $this->autosaveStatus = 'saved';
+            $this->lastSavedTime = UiFormat::time($draft['updated_at'] ?? null);
+        }
+        $this->selectedProject = filled($this->projectId) ? FakeStore::findProject($this->projectId) : null;
+        $this->ensureInitialRows();
+        $this->draftFingerprint = $this->payloadFingerprint();
     }
 
     /**
@@ -249,6 +229,7 @@
         }
 
         if (! empty($errors)) {
+            $this->persistDraft();
             foreach ($errors as $field => $msg) {
                 $this->addError($field, $msg);
             }
@@ -263,7 +244,6 @@
             'activity_date' => $this->activityDate,
             'notes' => $this->notes,
             'description' => $this->notes,
-            'pic_progress' => $this->picProgress,
             'pic_customer' => $this->picCustomer,
             'process_trial' => $this->processTrial,
             'machine' => $this->machine,
@@ -290,7 +270,7 @@
             $this->progressId = $saved['id'];
             $this->successMessage = 'Progress kegiatan berhasil disimpan.';
             $this->dispatch('progress-saved', progressId: $saved['id']);
-            return redirect()->to('?tab=progresses&id=' . $saved['id']);
+            return redirect()->to(route('prototype.index', ['tab' => 'progresses', 'detail' => $saved['id']]))->with('ui-toast', 'Progress tersimpan.');
         } catch (ValidationException $e) {
             foreach ($e->errors() as $field => $messages) {
                 foreach ($messages as $msg) {
@@ -409,7 +389,6 @@
             'activity' => $this->activity,
             'activity_date' => $this->activityDate,
             'notes' => $this->notes,
-            'pic_progress' => $this->picProgress,
             'pic_customer' => $this->picCustomer,
             'process_trial' => $this->processTrial,
             'machine' => $this->machine,
@@ -433,29 +412,16 @@
      */
     public function updated($property): void
     {
-        if (in_array($property, ['activeSection', 'successMessage', 'autosaveStatus', 'simulateSaveError'], true)) {
-            return;
-        }
-
-        $payload = $this->getDraftPayload();
-
-        // Jangan buat draft bila form belum diisi perubahan apapun
-        if (FakeDraftStore::isEmptyDraft($payload)) {
-            $this->autosaveStatus = 'unsaved';
-            return;
-        }
-
-        $this->autosaveStatus = 'saving';
-
-        if ($this->simulateSaveError) {
-            $this->autosaveStatus = 'error';
-            return;
-        }
-
-        $draft = FakeDraftStore::save($payload);
-        $this->draftUuid = $draft['uuid'];
-        $this->autosaveStatus = 'saved';
-        $this->lastSavedTime = date('H:i');
+        $root = explode('.', $property)[0];
+        if (! in_array($root, [
+            'projectId', 'activityDate', 'activity', 'notes', 'picCustomer',
+            'processTrial', 'machine', 'dieHighRaw', 'materialActualTrial',
+            'qtyMaterialRaw', 'qtyProcessRaw', 'qtySampleRaw', 'eventNote', 'remark',
+            'problems', 'nextActions', 'feedbacks', 'attachments',
+        ], true)) {
+            return;
+        }
+        $this->persistDraft();
     }
 
     /**
@@ -464,19 +430,7 @@
      */
     public function saveDraft(): void
     {
-        $payload = $this->getDraftPayload();
-
-        if ($this->simulateSaveError) {
-            $this->autosaveStatus = 'error';
-            $this->addError('save', 'Simulasi kegagalan simpan draft. Silakan klik Coba Lagi.');
-            return;
-        }
-
-        $draft = FakeDraftStore::save($payload);
-        $this->draftUuid = $draft['uuid'];
-        $this->autosaveStatus = 'saved';
-        $this->lastSavedTime = date('H:i');
-        $this->successMessage = 'Draft berhasil disimpan (tidak mempengaruhi status Open/Closed).';
+        $this->persistDraft();
     }
 
     /**
@@ -494,7 +448,8 @@
 
     public function cancel()
     {
-        return redirect()->to('?tab=dashboard');
+        $this->pendingUrl = route('prototype.index', ['tab' => 'dashboard']);
+        return $this->leaveEditor();
     }
 
     /**
@@ -517,6 +472,7 @@
 
     public function addProblem(): void
     {
+        if (! $this->writable()) return;
         $newId = 'prob-' . Str::lower(Str::random(5));
         $this->problems[] = [
             'id' => $newId,
@@ -534,6 +490,8 @@
             ],
         ];
         $this->activeSection = 'problems';
+        $this->updated('problems');
+        $this->dispatch('editor-focus', selector: '#problem-' . (count($this->problems) - 1));
     }
 
     /**
@@ -659,6 +617,7 @@
 
     public function removeProblem(string $problemId): void
     {
+        if (! $this->writable()) return;
         if (! $this->canRemove('problem', $problemId)) {
             $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
             return;
@@ -668,10 +627,12 @@
             $this->problems,
             fn ($p) => ($p['id'] ?? '') !== $problemId
         ));
+        $this->updated('problems');
     }
 
     public function addCorrectiveAction(string $problemId): void
     {
+        if (! $this->writable()) return;
         foreach ($this->problems as &$prob) {
             if (($prob['id'] ?? '') === $problemId) {
                 $prob['corrective_actions'][] = [
@@ -685,10 +646,17 @@
             }
         }
         unset($prob);
+        $this->updated('problems');
+        $index = array_search($problemId, array_column($this->problems, 'id'), true);
+        if ($index !== false) {
+            $last = count($this->problems[$index]['corrective_actions']) - 1;
+            $this->dispatch('editor-focus', selector: "#f-ca-{$index}-{$last}-desc");
+        }
     }
 
     public function removeCorrectiveAction(string $problemId, string $actionId): void
     {
+        if (! $this->writable()) return;
         if (! $this->canRemove('corrective_action', $actionId, $problemId)) {
             $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
             return;
@@ -704,10 +672,12 @@
             }
         }
         unset($prob);
+        $this->updated('problems');
     }
 
     public function addSimulationPhoto(string $problemId): void
     {
+        if (! $this->writable()) return;
         foreach ($this->problems as &$prob) {
             if (($prob['id'] ?? '') === $problemId) {
                 $nextNum = count($prob['photos'] ?? []) + 1;
@@ -723,10 +693,12 @@
             }
         }
         unset($prob);
+        $this->updated('problems');
     }
 
     public function removeSimulationPhoto(string $problemId, string $photoId): void
     {
+        if (! $this->writable()) return;
         foreach ($this->problems as &$prob) {
             if (($prob['id'] ?? '') === $problemId) {
                 $prob['photos'] = array_values(array_filter(
@@ -737,10 +709,12 @@
             }
         }
         unset($prob);
+        $this->updated('problems');
     }
 
     public function addNextAction(): void
     {
+        if (! $this->writable()) return;
         $this->nextActions[] = [
             'id' => 'next-' . Str::lower(Str::random(5)),
             'description' => '',
@@ -749,10 +723,13 @@
             'order' => count($this->nextActions) + 1,
         ];
         $this->activeSection = 'next_actions';
+        $this->updated('nextActions');
+        $this->dispatch('editor-focus', selector: '#f-na-' . (count($this->nextActions) - 1) . '-desc');
     }
 
     public function removeNextAction(string $actionId): void
     {
+        if (! $this->writable()) return;
         if (! $this->canRemove('next_action', $actionId)) {
             $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
             return;
@@ -762,23 +739,28 @@
             $this->nextActions,
             fn ($a) => ($a['id'] ?? '') !== $actionId
         ));
+        $this->updated('nextActions');
     }
 
     public function addFeedback(): void
     {
+        if (! $this->writable()) return;
         $this->feedbacks[] = [
             'id' => 'fb-' . Str::lower(Str::random(5)),
-            'date' => date('Y-m-d'),
+            'date' => '',
             'pic' => '',
             'content' => '',
             'order' => count($this->feedbacks) + 1,
             'files' => [],
         ];
         $this->activeSection = 'feedback';
+        $this->updated('feedbacks');
+        $this->dispatch('editor-focus', selector: '#f-fb-' . (count($this->feedbacks) - 1) . '-content');
     }
 
     public function removeFeedback(string $feedbackId): void
     {
+        if (! $this->writable()) return;
         if (! $this->canRemove('feedback', $feedbackId)) {
             $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
             return;
@@ -788,10 +770,12 @@
             $this->feedbacks,
             fn ($f) => ($f['id'] ?? '') !== $feedbackId
         ));
+        $this->updated('feedbacks');
     }
 
     public function addSimulationFeedbackFile(string $feedbackId, string $filename = 'dokumen_feedback.pdf'): void
     {
+        if (! $this->writable()) return;
         foreach ($this->feedbacks as &$fb) {
             if (($fb['id'] ?? '') === $feedbackId) {
                 $fb['files'] = $fb['files'] ?? [];
@@ -809,6 +793,7 @@
 
     public function removeSimulationFeedbackFile(string $feedbackId, string $fileId): void
     {
+        if (! $this->writable()) return;
         foreach ($this->feedbacks as &$fb) {
             if (($fb['id'] ?? '') === $feedbackId) {
                 $fb['files'] = array_values(array_filter(
@@ -822,9 +807,127 @@
         $this->updated('feedbacks');
     }
 
+
+    private function payloadFingerprint(): string
+    {
+        $payload = $this->cleanProgressPayload($this->getDraftPayload());
+        unset($payload['uuid']);
+        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
+    }
+
+    private function writable(): bool
+    {
+        $current = $this->progressId ? FakeStore::findProgress($this->progressId) : null;
+        $this->isClosed = $this->isClosed || strtolower($current['status'] ?? '') === 'closed';
+        if ($this->isClosed) {
+            $this->addError('save', 'Reopen progress terlebih dahulu untuk mengedit.');
+            return false;
+        }
+        return true;
+    }
+
+    private function persistDraft(): bool
+    {
+        if (! $this->writable()) return false;
+        $fingerprint = $this->payloadFingerprint();
+        if ($fingerprint === $this->draftFingerprint) return true;
+        try {
+            if ($this->simulateSaveError) throw new \RuntimeException('Draft belum tersimpan.');
+            $draft = FakeDraftStore::save($this->cleanProgressPayload($this->getDraftPayload()));
+            $this->draftUuid = $draft['uuid'];
+            $this->lastSavedTime = UiFormat::time($draft['updated_at'] ?? null);
+            $this->draftFingerprint = $fingerprint;
+            $this->autosaveStatus = 'saved';
+            $this->dispatch('draft-changed');
+            return true;
+        } catch (\Throwable $e) {
+            report($e);
+            $this->autosaveStatus = 'error';
+            return false;
+        }
+    }
+
+    public function leaveEditor()
+    {
+        $url = $this->pendingUrl ?: route('prototype.index', ['tab' => 'dashboard']);
+        $actual = parse_url($url);
+        $base = parse_url(route('prototype.index'));
+        abort_unless(is_array($actual)
+            && ($actual['scheme'] ?? '') === ($base['scheme'] ?? '')
+            && ($actual['host'] ?? '') === ($base['host'] ?? '')
+            && ($actual['port'] ?? null) === ($base['port'] ?? null)
+            && rtrim($actual['path'] ?? '', '/') === rtrim($base['path'] ?? '', '/'), 422);
+        if (! $this->persistDraft()) return null;
+        return redirect()->to($url);
+    }
+
+    public function openProblemPhoto(string $problemId, string $photoId): void
+    {
+        foreach ($this->problems as $problem) {
+            if (($problem['id'] ?? '') !== $problemId) continue;
+            foreach ($problem['photos'] ?? [] as $photo) {
+                if (($photo['id'] ?? '') !== $photoId) continue;
+                $this->activePhoto = [
+                    'src' => $photo['path'] ?? $photo['src'] ?? '',
+                    'name' => $photo['filename'] ?? $photo['name'] ?? 'Foto problem',
+                ];
+                return;
+            }
+        }
+    }
+
+    public function closePhoto(): void { $this->activePhoto = null; }
+
+    // Prototype parity only: store the selected image's data URL, not a fake thumbnail.
+    public function attachProblemPhoto(string $problemId, array $photo): void
+    {
+        if (! $this->writable()) return;
+        $valid = validator($photo, [
+            'name' => ['required', 'string', 'max:255'],
+            'src' => ['required', 'string', 'max:2800000', 'regex:/\Adata:image\/(jpeg|png|webp);base64,[A-Za-z0-9+\/=]+\z/'],
+        ])->validate();
+        $bytes = base64_decode(explode(',', $valid['src'], 2)[1], true);
+        abort_unless($bytes !== false && strlen($bytes) <= 2 * 1024 * 1024 && @getimagesizefromstring($bytes) !== false, 422);
+        foreach ($this->problems as &$problem) {
+            if (($problem['id'] ?? '') !== $problemId) continue;
+            $problem['photos'][] = [
+                'id' => (string) Str::uuid(), 'filename' => $valid['name'],
+                'path' => $valid['src'], 'caption' => '', 'is_simulation' => true,
+            ];
+            unset($problem);
+            $this->persistDraft();
+            return;
+        }
+        unset($problem);
+        abort(422, 'Problem tidak ditemukan.');
+    }
+
+    // Metadata only, exactly as the approved prototype (not a production upload).
+    public function attachFiles(string $owner, array $files): void
+    {
+        if (! $this->writable()) return;
+        $valid = validator(['files' => $files], [
+            'files' => ['required', 'array'],
+            'files.*.name' => ['required', 'string', 'max:255'],
+            'files.*.size' => ['required', 'integer', 'min:0'],
+        ])->validate()['files'];
+        $items = array_map(fn ($file) => [
+            'id' => (string) Str::uuid(), 'name' => $file['name'], 'size' => $file['size'],
+        ], $valid);
+        if ($owner === 'progress') {
+            $this->attachments = array_merge($this->attachments, $items);
+        } else {
+            $index = array_search($owner, array_column($this->feedbacks, 'id'), true);
+            abort_if($index === false, 422, 'Feedback tidak ditemukan.');
+            $this->feedbacks[$index]['files'] = array_merge($this->feedbacks[$index]['files'] ?? [], $items);
+        }
+        $this->persistDraft();
+    }
+
     public function render()
     {
         $projects = FakeStore::getProjects();
+        $this->selectedProject = filled($this->projectId) ? FakeStore::findProject($this->projectId) : null;
 
         return view('livewire.prototype.progress-form', [
             'projects' => $projects,
```

### C12 — `resources/views/livewire/prototype/progress-form.blade.php`

```diff
--- a/resources/views/livewire/prototype/progress-form.blade.php
+++ b/resources/views/livewire/prototype/progress-form.blade.php
@@ -1,6 +1,81 @@
-<div>
+<div
+    x-data="{
+    observer: null, linkHandler: null, pending: Promise.resolve(), busy: 0, leaving: false,
+    init() {
+        this.$nextTick(() =&gt; this.growAll());
+        this.observer = new MutationObserver(() =&gt; this.growAll());
+        this.observer.observe(this.$el, { childList: true, subtree: true, characterData: true });
+        this.linkHandler = async event =&gt; {
+            const link = event.target.closest(&#x27;a[href]&#x27;);
+            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.hasAttribute(&#x27;download&#x27;) || (link.target &amp;&amp; link.target !== &#x27;_self&#x27;)) return;
+            const target = new URL(link.href, location.href);
+            if (target.origin !== location.origin || target.pathname.replace(/\/$/, &#x27;&#x27;) !== location.pathname.replace(/\/$/, &#x27;&#x27;) || link.getAttribute(&#x27;href&#x27;).startsWith(&#x27;#&#x27;)) return;
+            event.preventDefault();
+            event.stopImmediatePropagation();
+            if (this.leaving) return;
+            this.leaving = true;
+            try {
+                await this.pending;
+                await this.$wire.$set(&#x27;pendingUrl&#x27;, target.href, false);
+                this.$refs.leave.click();
+            } catch (_) {
+                this.$dispatch(&#x27;ui-toast&#x27;, { message: &#x27;Isian belum tersimpan. Coba lagi sebelum berpindah halaman.&#x27; });
+            } finally { this.leaving = false; }
+        };
+        document.addEventListener(&#x27;click&#x27;, this.linkHandler, true);
+    },
+    destroy() {
+        this.observer?.disconnect();
+        document.removeEventListener(&#x27;click&#x27;, this.linkHandler, true);
+    },
+    grow(el) {
+        if (!el?.matches(&#x27;.entry-sheet textarea&#x27;)) return;
+        el.style.height = &#x27;auto&#x27;;
+        el.style.height = Math.min(320, Math.max(56, el.scrollHeight + 2)) + &#x27;px&#x27;;
+    },
+    growAll() { this.$el.querySelectorAll(&#x27;.entry-sheet textarea&#x27;).forEach(el =&gt; this.grow(el)); },
+    focus(selector) {
+        this.$nextTick(() =&gt; requestAnimationFrame(() =&gt; this.$el.querySelector(selector)?.focus()));
+    },
+    enqueue(job) {
+        this.busy++;
+        const task = this.pending.catch(() =&gt; {}).then(job);
+        this.pending = task;
+        task.catch(() =&gt; this.$dispatch(&#x27;ui-toast&#x27;, { message: &#x27;Lampiran belum tersimpan. Coba pilih file kembali.&#x27; })).finally(() =&gt; this.busy--);
+        return task;
+    },
+    pickPhotos(event, problemId) {
+        const files = Array.from(event.target.files || []);
+        event.target.value = &#x27;&#x27;;
+        this.enqueue(async () =&gt; {
+            for (const file of files) {
+                if (![&#x27;image/jpeg&#x27;, &#x27;image/png&#x27;, &#x27;image/webp&#x27;].includes(file.type)) {
+                    this.$dispatch(&#x27;ui-toast&#x27;, { message: &#x27;Gunakan foto JPG, PNG, atau WebP.&#x27; }); continue;
+                }
+                if (file.size &gt; 2 * 1024 * 1024) {
+                    this.$dispatch(&#x27;ui-toast&#x27;, { message: &#x27;Untuk prototype ini, gunakan foto di bawah 2 MB.&#x27; }); continue;
+                }
+                const src = await new Promise((resolve, reject) =&gt; {
+                    const reader = new FileReader(); reader.onload = () =&gt; resolve(reader.result);
+                    reader.onerror = reject; reader.readAsDataURL(file);
+                });
+                await this.$wire.$call(&#x27;attachProblemPhoto&#x27;, problemId, { name: file.name, src });
+            }
+        });
+    },
+    pickFiles(event, owner) {
+        const files = Array.from(event.target.files || [], f =&gt; ({ name: f.name, size: f.size }));
+        event.target.value = &#x27;&#x27;;
+        if (files.length) this.enqueue(() =&gt; this.$wire.$call(&#x27;attachFiles&#x27;, owner, files));
+    }
+}"
+    x-on:input.capture="grow($event.target)"
+    x-on:editor-focus.window="focus($event.detail.selector)"
+    x-on:focus-first-error.window="focus('.input-error, [aria-invalid=true]')"
+    x-on:keydown.window="if (($event.ctrlKey || $event.metaKey) &amp;&amp; $event.key === 'Enter' &amp;&amp; !document.querySelector('dialog[open]')) { $event.preventDefault(); if (!busy &amp;&amp; !$wire.isClosed) $refs.save.click(); }"
+>
     <!-- Tombol kembali -->
-    <button type="button" class="back" wire:click="cancel">
+    <button type="button" class="back" wire:click="cancel" x-bind:disabled="busy > 0 || leaving">
         <x-ui.icon name="left" />Kembali ke daftar
     </button>
 
@@ -8,7 +83,7 @@
     <div class="page-head editor-head">
         <div class="heading-inline">
             <h1>{{ $progressId ? 'Edit progress' : 'Tambah progress' }}</h1>
-            <span class="status {{ $progressId ? 'open' : 'draft' }}">{{ $progressId ? 'Open' : 'Draft' }}</span>
+            <span class="status {{ $isClosed ? 'closed' : ($progressId ? '' : 'draft') }}">{{ $isClosed ? 'Closed' : ($progressId ? 'Open' : 'Draft') }}</span>
         </div>
         <div class="actions">
             @if($progressId)
@@ -20,16 +95,7 @@
         </div>
     </div>
 
-    @if($isClosed)
-        <div class="panel" style="margin-bottom: 18px; padding: 14px 18px; background: #fff8e6; border: 1px solid #ecd8a5; display: flex; align-items: center; justify-content: space-between; gap: 12px;" data-testid="form-closed-banner">
-            <div style="font-size: 12px; color: #805611;">
-                Catatan progress ini berstatus <strong>Closed (Read-Only)</strong>. Klik tombol Reopen untuk mengaktifkan kembali mode edit.
-            </div>
-            <button type="button" class="btn primary sm" wire:click="reopenAndEnableEdit">
-                <x-ui.icon name="reopen" />Buka kembali (Reopen)
-            </button>
-        </div>
-    @endif
+
 
     <!-- Panel JO No & Autofill Identitas Project Master -->
     <section class="context" aria-label="Identitas project">
@@ -235,8 +301,8 @@
                                         <button
                                             type="button"
                                             class="icon-btn photo-thumb"
-                                            wire:click="removeSimulationPhoto('{{ $p['id'] }}', '{{ $ph['id'] }}')"
-                                            aria-label="Hapus foto {{ $ph['filename'] ?? 'foto' }}"
+                                            wire:click="openProblemPhoto('{{ $p['id'] }}', '{{ $ph['id'] }}')"
+                                            aria-label="Perbesar {{ $ph['filename'] ?? 'foto' }}"
                                             title="{{ $ph['caption'] ?? $ph['filename'] ?? '' }}"
                                         >
                                             <img
@@ -247,14 +313,12 @@
                                             <span class="sr-only">[SIMULASI] {{ $ph['caption'] ?? '' }}</span>
                                         </button>
                                     @endforeach
-                                    <button
-                                        type="button"
-                                        class="photo-add"
-                                        wire:click="addSimulationPhoto('{{ $p['id'] }}')"
-                                        aria-label="Tambah foto problem {{ $i + 1 }}"
-                                    >
+                                    <label class="photo-add">
                                         <x-ui.icon name="camera" />Foto problem
-                                    </button>
+                                        <input type="file" accept="image/jpeg,image/png,image/webp" multiple
+                                            x-on:change="pickPhotos($event, @js($p['id']))"
+                                            aria-label="Tambah foto problem {{ $i + 1 }}">
+                                    </label>
                                 </div>
                             </div>
 
@@ -444,36 +508,12 @@
                             @endif
                         </div>
                         <div class="feedback-attachments">
-                            <button
-                                type="button"
-                                class="photo-add compact-upload"
-                                wire:click="addSimulationFeedbackFile('{{ $f['id'] }}')"
-                                aria-label="Tambah dokumen feedback {{ $i + 1 }}"
-                            >
+                            <label class="photo-add compact-upload">
                                 <x-ui.icon name="file" />Dokumen feedback
-                            </button>
-                            @if(!empty($f['files']))
-                                <div class="file-list" style="margin-top: 8px;">
-                                    @foreach($f['files'] as $doc)
-                                        <div class="file-row">
-                                            <x-ui.icon name="file" />
-                                            <div>
-                                                <strong>{{ $doc['name'] ?? 'dokumen' }}</strong>
-                                                <small>{{ max(1, round(($doc['size'] ?? 1024) / 1024)) }} KB</small>
-                                            </div>
-                                            <button
-                                                type="button"
-                                                class="icon-btn"
-                                                wire:click="removeSimulationFeedbackFile('{{ $f['id'] }}', '{{ $doc['id'] }}')"
-                                                aria-label="Hapus dokumen {{ $doc['name'] ?? '' }}"
-                                                style="width: 24px; height: 24px; margin-left: auto;"
-                                            >
-                                                <x-ui.icon name="close" />
-                                            </button>
-                                        </div>
-                                    @endforeach
-                                </div>
-                            @endif
+                                <input type="file" multiple x-on:change="pickFiles($event, @js($f['id']))"
+                                    aria-label="Tambah dokumen feedback {{ $i + 1 }}">
+                            </label>
+                            <x-ui.reference-files :files="$f['files'] ?? []" />
                         </div>
                     </article>
                 @endforeach
@@ -505,33 +545,29 @@
             <div class="attachment-line">
                 <label class="photo-add">
                     <x-ui.icon name="upload" />Tambah dokumen
-                    <input type="file" multiple aria-label="Tambah dokumen kegiatan" />
+                    <input type="file" multiple x-on:change="pickFiles($event, 'progress')" aria-label="Tambah dokumen kegiatan" />
                 </label>
             </div>
+            <x-ui.reference-files :files="$attachments" />
         </section>
     </div>
 
     <!-- Fixed Savebar di Bagian Bawah -->
     <footer class="savebar">
         <div class="save-state">
-            <div
-                id="save-indicator"
-                class="save-indicator {{ $autosaveStatus === 'error' ? 'error' : '' }}"
-                role="status"
-                aria-live="polite"
-                @if($autosaveStatus === 'unsaved') hidden @endif
-            >
-                @if($autosaveStatus === 'saving')
-                    <x-ui.icon name="check" />
-                    <span>Menyimpan draft demo…</span>
-                @elseif($autosaveStatus === 'saved')
-                    <x-ui.icon name="check" />
-                    <span>Draft demo tersimpan · {{ $lastSavedTime }}</span>
-                @elseif($autosaveStatus === 'error')
-                    <x-ui.icon name="info" />
-                    <span>Draft belum tersimpan</span>
-                    <button type="button" class="btn text sm" wire:click="saveDraft">Coba lagi</button>
-                @endif
+            <div class="save-indicator" wire:dirty role="status" aria-live="polite">
+                <x-ui.icon name="check" /><span>Menyimpan draft demo…</span>
+            </div>
+            <div wire:dirty.remove>
+                <div id="save-indicator" class="save-indicator {{ $autosaveStatus === 'error' ? 'error' : '' }}"
+                    role="status" aria-live="polite" @if($autosaveStatus === 'unsaved') hidden @endif>
+                    @if($autosaveStatus === 'saved')
+                        <x-ui.icon name="check" /><span>Draft demo tersimpan · {{ $lastSavedTime }}</span>
+                    @elseif($autosaveStatus === 'error')
+                        <x-ui.icon name="info" /><span>Draft belum tersimpan</span>
+                        <button type="button" class="btn text sm" wire:click="saveDraft">Coba lagi</button>
+                    @endif
+                </div>
             </div>
         </div>
         <div class="actions">
@@ -546,6 +582,10 @@
                 type="button"
                 class="btn primary"
                 wire:click="save"
+                x-ref="save"
+                x-bind:disabled="busy > 0 || leaving || $wire.isClosed"
+                wire:loading.attr="disabled"
+                wire:target="save"
                 title="Simpan progress (Ctrl+Enter)"
                 @if($isClosed) disabled @endif
             >
@@ -554,71 +594,21 @@
         </div>
     </footer>
 
-    {{-- Dialog Konfirmasi Hapus Baris Baru (UI-06) --}}
-    @if ($pendingRemoval)
-        <div class="dialog-overlay" style="position: fixed; inset: 0; background: #19263865; z-index: 999; display: grid; place-items: center;" wire:click.self="cancelRemove">
-            <dialog class="dialog-open" id="dialog" open style="position: static; margin: auto; z-index: 1000;">
-                <div class="dialog-head">
-                    <h2 id="dialog-title">Hapus baris baru?</h2>
-                    <button type="button" class="icon-btn" wire:click="cancelRemove" aria-label="Tutup dialog">
-                        <x-ui.icon name="close" />
-                    </button>
-                </div>
-                <div class="dialog-body">
-                    <p>Baris ini belum diterapkan ke progress. Isian pada baris ini akan dihapus dari draft.</p>
-                </div>
-                <div class="dialog-foot">
-                    <button type="button" class="btn" wire:click="cancelRemove">Batal</button>
-                    <button type="button" class="btn danger" wire:click="confirmRemove">Hapus baris</button>
-                </div>
-            </dialog>
-        </div>
+    <button type="button" hidden x-ref="leave" wire:click="leaveEditor"></button>
+
+    @if($pendingRemoval)
+        <x-ui.reference-dialog id="remove-row-dialog" title="Hapus baris baru?" cancel="cancelRemove">
+            <p>Baris ini belum diterapkan ke progress. Isian pada baris ini akan dihapus dari draft.</p>
+            <x-slot:footer>
+                <button type="button" class="btn" wire:click="cancelRemove">Batal</button>
+                <button type="button" class="btn danger" wire:click="confirmRemove">Hapus baris</button>
+            </x-slot:footer>
+        </x-ui.reference-dialog>
     @endif
-
-    <!-- Client-side Scripts: Textarea Auto-grow, Shortcut Ctrl/Cmd+Enter, Error Focus -->
-    <script>
-        (function() {
-            function growTextarea(el) {
-                if (!el || el.tagName !== 'TEXTAREA') return;
-                el.style.height = 'auto';
-                el.style.height = Math.min(320, Math.max(56, el.scrollHeight + 2)) + 'px';
-            }
-
-            document.addEventListener('input', function(e) {
-                if (e.target.tagName === 'TEXTAREA' && e.target.closest('.entry-sheet')) {
-                    growTextarea(e.target);
-                }
-            });
-
-            document.addEventListener('DOMContentLoaded', function() {
-                document.querySelectorAll('.entry-sheet textarea').forEach(growTextarea);
-            });
-
-            window.addEventListener('focus-first-error', function() {
-                setTimeout(function() {
-                    var el = document.querySelector('.input-error, [aria-invalid="true"]');
-                    if (el) {
-                        el.focus();
-                    }
-                }, 50);
-            });
-
-            window.addEventListener('keydown', function(e) {
-                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
-                    var dialog = document.getElementById('dialog');
-                    if (dialog && dialog.open) return;
-                    e.preventDefault();
-                    if (typeof @this !== 'undefined') {
-                        @this.call('save');
-                    }
-                }
-            });
-
-            if (typeof Livewire !== 'undefined') {
-                Livewire.hook('morph.updated', function() {
-                    document.querySelectorAll('.entry-sheet textarea').forEach(growTextarea);
-                });
-            }
-        })();
-    </script>
+    @if($activePhoto)
+        <x-ui.reference-dialog id="editor-photo-dialog" title="Foto problem" cancel="closePhoto">
+            <img class="full-photo" src="{{ $activePhoto['src'] }}" alt="{{ $activePhoto['name'] }}">
+            <p>{{ $activePhoto['name'] }}</p>
+        </x-ui.reference-dialog>
+    @endif
 </div>
```

### C13 — `app/Livewire/Prototype/Dashboard.php`

```diff
--- a/app/Livewire/Prototype/Dashboard.php
+++ b/app/Livewire/Prototype/Dashboard.php
@@ -35,7 +35,7 @@
     /**
      * Role of the PIC to search: 'progress' (default), 'corrective_action', 'next_action', 'feedback', 'all'.
      */
-    public string $picRole = 'progress';
+    public string $picRole = 'all';
 
     /**
      * Sort order: 'oldest' (default) or 'newest'.
@@ -78,7 +78,7 @@
         'activity' => ['except' => ''],
         'customer' => ['except' => ''],
         'pic' => ['except' => ''],
-        'picRole' => ['except' => 'progress'],
+        'picRole' => ['except' => 'all'],
         'sort' => ['except' => 'oldest'],
         'from' => ['except' => ''],
         'to' => ['except' => ''],
@@ -94,9 +94,14 @@
             $this->initialStatus = $initialStatus;
         }
 
-        if (! request()->has('status')) {
-            $this->status = $this->initialStatus;
-        }
+        foreach (session('prototype_ui_filters', []) as $key => $value) {
+            if (in_array($key, ['search', 'status', 'activity', 'customer', 'pic', 'picRole', 'sort', 'from', 'to', 'advanced'], true)
+                && ! request()->has($key)) {
+                $this->{$key} = $value;
+            }
+        }
+        if (! $this->isDashboard && ! request()->has('status')) $this->status = 'all';
+        $this->rememberFilters();
     }
 
     /**
@@ -105,6 +110,7 @@
     public function toggleAdvanced(): void
     {
         $this->advanced = ! $this->advanced;
+        $this->rememberFilters();
     }
 
     /**
@@ -113,6 +119,7 @@
     public function toggleSort(): void
     {
         $this->sort = $this->sort === 'oldest' ? 'newest' : 'oldest';
+        $this->rememberFilters();
     }
 
     /**
@@ -125,6 +132,7 @@
             'Closed' => 'Closed',
             default => 'all',
         };
+        $this->rememberFilters();
     }
 
     /**
@@ -137,10 +145,11 @@
         $this->activity = '';
         $this->customer = '';
         $this->pic = '';
-        $this->picRole = 'progress';
+        $this->picRole = 'all';
         $this->sort = 'oldest';
         $this->from = '';
         $this->to = '';
+        $this->rememberFilters();
     }
 
     /**
@@ -155,10 +164,27 @@
             || $this->activity !== ''
             || $this->customer !== ''
             || $this->pic !== ''
-            || ! in_array($this->picRole, ['progress', 'customer'], true)
+            || $this->picRole !== 'all'
             || $this->sort !== 'oldest'
             || $this->from !== ''
             || $this->to !== '';
+    }
+
+    public function updated($property): void
+    {
+        if (in_array($property, ['search', 'status', 'activity', 'customer', 'pic', 'picRole', 'sort', 'from', 'to'], true)) {
+            $this->rememberFilters();
+        }
+    }
+
+    private function rememberFilters(): void
+    {
+        session()->put('prototype_ui_filters', [
+            'search' => $this->search, 'status' => $this->status,
+            'activity' => $this->activity, 'customer' => $this->customer,
+            'pic' => $this->pic, 'picRole' => $this->picRole, 'sort' => $this->sort,
+            'from' => $this->from, 'to' => $this->to, 'advanced' => $this->advanced,
+        ]);
     }
 
     public function render()
@@ -179,6 +205,7 @@
         // Filtered work list
         $filters = [
             'search' => $this->search,
+            'identity_only' => true,
             'status' => $this->status,
             'activity' => $this->activity,
             'customer' => $this->customer,
```

### C14 — `app/Prototype/FakeStore.php`

```diff
--- a/app/Prototype/FakeStore.php
+++ b/app/Prototype/FakeStore.php
@@ -289,11 +289,14 @@
         // Filter by search keyword (JO, Part No, Part Name, Customer, Notes, Problems)
         if (! empty($filters['search'])) {
             $keyword = strtolower(trim($filters['search']));
-            $progresses = array_filter($progresses, function ($p) use ($keyword) {
+            $progresses = array_filter($progresses, function ($p) use ($keyword, $filters) {
                 $project = static::findProject($p['project_id']);
                 $joNo = strtolower($project['jo_no'] ?? '');
                 $partNo = strtolower($project['part_no'] ?? '');
                 $partName = strtolower($project['part_name'] ?? '');
+                if (! empty($filters['identity_only'])) {
+                    return str_contains(implode(' ', [$joNo, $partNo, $partName]), $keyword);
+                }
                 $customer = strtolower($project['customer'] ?? '');
                 $notes = strtolower($p['notes'] ?? '');
 
@@ -333,11 +336,10 @@
         // Filter by PIC and PIC Role (FR18, AT24) - Guarantees unique progress records
         if (! empty($filters['pic'])) {
             $picKw = strtolower(trim($filters['pic']));
-            $role = strtolower(trim($filters['pic_role'] ?? 'progress'));
+            $role = strtolower(trim($filters['pic_role'] ?? 'all'));
 
             $progresses = array_filter($progresses, function ($p) use ($picKw, $role) {
-                $matchProgress = str_contains(strtolower($p['pic_progress'] ?? ''), $picKw)
-                    || str_contains(strtolower($p['pic_customer'] ?? ''), $picKw);
+                $matchProgress = str_contains(strtolower($p['pic_customer'] ?? ''), $picKw);
 
                 $matchCorrective = false;
                 foreach ($p['problems'] ?? [] as $prob) {
@@ -714,12 +716,12 @@
 
         $draftCount = count($drafts);
 
-        $now = \Illuminate\Support\Carbon::now('Asia/Jakarta');
+        $now = \Illuminate\Support\Carbon::now('Asia/Jakarta')->locale('id');
         $targetYm = $yearMonth ?: $now->format('Y-m');
 
         if ($yearMonth) {
             $parsed = \Illuminate\Support\Carbon::createFromFormat('Y-m', $yearMonth, 'Asia/Jakarta');
-            $monthLabel = $parsed ? $parsed->translatedFormat('F Y') : $yearMonth;
+            $monthLabel = $parsed ? $parsed->locale('id')->translatedFormat('F Y') : $yearMonth;
         } else {
             $monthLabel = $now->translatedFormat('F Y');
         }
```

### C15 — `resources/views/livewire/prototype/dashboard.blade.php`

```diff
--- a/resources/views/livewire/prototype/dashboard.blade.php
+++ b/resources/views/livewire/prototype/dashboard.blade.php
@@ -2,9 +2,6 @@
     <div class="page-head">
         <div>
             <h1>{{ $isDashboard ? 'Aktivitas New Model' : 'Semua progress' }}</h1>
-            @if(!$isDashboard)
-                <span class="sr-only">Seluruh Riwayat Progress</span>
-            @endif
         </div>
         <div class="actions">
             <a class="btn" href="?tab=drafts">
@@ -19,7 +16,7 @@
     </div>
 
     <div class="summary-caption">
-        <span style="text-transform:uppercase">Ringkasan Keseluruhan</span>
+        <span>RINGKASAN KESELURUHAN</span>
         <span>Semua project</span>
     </div>
 
@@ -34,14 +31,14 @@
         <div class="stat">
             <div class="stat-number" data-testid="stat-draft">{{ str_pad($globalStats['draft_count'], 2, '0', STR_PAD_LEFT) }}</div>
             <div>
-                <div class="stat-label">Draft aktif <span class="sr-only">Draft Tersimpan</span></div>
+                <div class="stat-label">Draft aktif</div>
                 <div class="stat-meta">Belum diterapkan ke progress</div>
             </div>
         </div>
         <div class="stat">
             <div class="stat-number" data-testid="stat-month">{{ str_pad($globalStats['current_month_count'], 2, '0', STR_PAD_LEFT) }}</div>
             <div>
-                <div class="stat-label">Aktivitas bulan ini <span class="sr-only">Aktivitas Bulan Berjalan</span></div>
+                <div class="stat-label">Aktivitas bulan ini</div>
                 <div class="stat-meta">{{ $globalStats['current_month_label'] }}</div>
             </div>
         </div>
@@ -54,7 +51,14 @@
             </div>
         </div>
 
-        <div class="tabs" role="tablist" aria-label="Status progress">
+        <div class="tabs" role="tablist" aria-label="Status progress" x-data
+            x-on:keydown="if (['ArrowLeft', 'ArrowRight'].includes($event.key) && $event.target.matches('[role=tab]')) {
+                $event.preventDefault();
+                const tabs = Array.from($el.querySelectorAll('[role=tab]'));
+                const step = $event.key === 'ArrowRight' ? 1 : -1;
+                const next = tabs[(tabs.indexOf($event.target) + step + tabs.length) % tabs.length];
+                next.click(); next.focus();
+            }">
             @foreach(['Open', 'Closed', 'Semua'] as $s)
                 @php
                     $count = match($s) {
@@ -137,7 +141,7 @@
                     Peran PIC
                     <select wire:model.live="picRole" data-testid="pic-role-select">
                         <option value="all">Semua peran</option>
-                        <option value="progress">PIC Customer</option>
+                        <option value="customer">PIC Customer</option>
                         <option value="corrective_action">Corrective Action</option>
                         <option value="next_action">Next Action</option>
                         <option value="feedback">Feedback Customer</option>
@@ -173,20 +177,9 @@
             @if(count($progresses) === 0)
                 <div class="empty" data-testid="empty-state-container">
                     <x-ui.icon name="search" />
-                    @if($isFiltered)
-                        <h3>Tidak ada hasil yang cocok dengan filter</h3>
-                        <p>Coba kata pencarian lain atau bersihkan filter untuk melihat kegiatan lainnya.</p>
-                        <button type="button" class="btn" wire:click="clearFilters" data-testid="empty-clear-filter-btn">
-                            Bersihkan Filter
-                        </button>
-                    @else
-                        <h3>Tidak ada progress Open</h3>
-                        <p>Semua kegiatan telah ditutup (Closed) atau belum ada progress yang dicatat untuk pasangan JO & Part No yang aktif.</p>
-                        <a href="?tab=form" class="btn primary">
-                            <x-ui.icon name="plus" />
-                            Tambah Progress Baru
-                        </a>
-                    @endif
+                    <h3>Tidak ada progress yang cocok</h3>
+                    <p>Coba kata pencarian lain atau bersihkan filter untuk melihat kegiatan lainnya.</p>
+                    <button type="button" class="btn" wire:click="clearFilters" data-testid="empty-clear-filter-btn">Bersihkan filter</button>
                 </div>
             @else
                 <table class="data-table" data-testid="progress-table">
@@ -214,7 +207,7 @@
                                 $isOpen = (strtolower($prog['status'] ?? '') === 'open');
                                 $url = $isOpen ? '?tab=form&id=' . $prog['id'] : '?tab=progresses&detail=' . $prog['id'];
                                 $dateStr = !empty($prog['activity_date'])
-                                    ? \Illuminate\Support\Carbon::parse($prog['activity_date'])->locale('id')->isoFormat('D MMM Y')
+                                    ? \App\Support\UiFormat::date($prog['activity_date'])
                                     : 'Belum diisi';
                                 $nextActions = $prog['next_actions'] ?? [];
                                 $nextCount = count($nextActions);
@@ -223,7 +216,6 @@
                             <tr data-testid="progress-row" data-id="{{ $prog['id'] }}">
                                 <td class="date-col date-cell">
                                     {{ $dateStr }}
-                                    <span class="sr-only">{{ $prog['id'] }}</span>
                                 </td>
                                 <td class="part-col">
                                     <a href="{{ $url }}" class="row-title" data-open-progress="{{ $prog['id'] }}">
```

### C16 — `app/Livewire/Prototype/ProgressDetail.php`

```diff
--- a/app/Livewire/Prototype/ProgressDetail.php
+++ b/app/Livewire/Prototype/ProgressDetail.php
@@ -71,7 +71,7 @@
             $updated = FakeStore::closeProgress($this->progressId);
             if ($updated) {
                 $this->progress = $updated;
-                $this->flashMessage = "Progress #{$this->progressId} berhasil ditutup (Closed). Reopen tersedia jika perlu dilanjutkan.";
+                $this->dispatch('ui-toast', message: 'Progress ditutup. Reopen tersedia jika perlu dilanjutkan.');
                 $this->dispatch('progress-closed', progressId: $this->progressId);
             }
         }
@@ -88,7 +88,7 @@
             $updated = FakeStore::reopenProgress($this->progressId);
             if ($updated) {
                 $this->progress = $updated;
-                $this->flashMessage = "Progress #{$this->progressId} berhasil dibuka kembali (Open). Seluruh catatan tetap tersimpan.";
+                $this->dispatch('ui-toast', message: 'Progress kembali Open. Seluruh catatan tetap tersimpan.');
                 $this->dispatch('progress-reopened', progressId: $this->progressId);
             }
         }
@@ -100,6 +100,18 @@
             'src' => $src,
             'name' => $name,
         ];
+    }
+
+    public function openPhotoById(string $problemId, string $photoId): void
+    {
+        foreach ($this->progress['problems'] ?? [] as $problem) {
+            if (($problem['id'] ?? '') !== $problemId) continue;
+            foreach ($problem['photos'] ?? [] as $photo) {
+                if (($photo['id'] ?? '') !== $photoId) continue;
+                $this->openPhoto($photo['path'] ?? $photo['src'] ?? '', $photo['filename'] ?? $photo['name'] ?? 'Foto problem');
+                return;
+            }
+        }
     }
 
     public function closePhoto(): void
```

### C17 — `resources/views/livewire/prototype/progress-detail.blade.php`

```diff
--- a/resources/views/livewire/prototype/progress-detail.blade.php
+++ b/resources/views/livewire/prototype/progress-detail.blade.php
@@ -12,8 +12,8 @@
             $nextActions = $progress['next_actions'] ?? [];
             $feedbacks = $progress['feedbacks'] ?? [];
             $attachments = $progress['attachments'] ?? [];
-            $activityDate = !empty($progress['activity_date']) ? \Illuminate\Support\Carbon::parse($progress['activity_date'])->format('d M Y') : '—';
-            $docNo = $project['doc_no'] ?? $project['no_doc'] ?? $project['doc'] ?? '—';
+            $activityDate = !empty($progress['activity_date']) ? \App\Support\UiFormat::date($progress['activity_date']) : '—';
+            $docNo = ($project['doc_no'] ?? $project['no_doc'] ?? $project['doc'] ?? '') ?: '—';
         @endphp
 
         <a href="?tab=dashboard" class="back">
@@ -49,9 +49,7 @@
                     <button type="button" class="btn primary" wire:click="reopenProgress" data-testid="reopen-progress-btn">
                         <x-ui.icon name="reopen" />
                         Reopen progress
-                        <span class="sr-only">Buka Kembali (Reopen Progress)</span>
                     </button>
-                    <span class="sr-only" data-testid="closed-warning-banner">Progress ini berstatus Closed (Read-Only) · Edit Dinonaktifkan (Closed)</span>
                 @endif
             </div>
         </div>
@@ -83,18 +81,18 @@
                                         <small>PIC {{ !empty($act['pic']) ? $act['pic'] : 'belum diisi' }}</small>
                                     </div>
                                     <div class="date">
-                                        {{ !empty($act['actual_date']) ? \Illuminate\Support\Carbon::parse($act['actual_date'])->format('d M Y') : 'Actual date belum diisi' }}
+                                        {{ !empty($act['actual_date']) ? \App\Support\UiFormat::date($act['actual_date']) : 'Actual date belum diisi' }}
                                     </div>
                                 </div>
                             @endforeach
 
                             @if(!empty($prob['photos']))
-                                <div class="photo-strip" style="margin-top: 12px;">
+                                <div class="photo-strip">
                                     @foreach($prob['photos'] as $ph)
                                         <button
                                             type="button"
                                             class="icon-btn photo-thumb"
-                                            wire:click="openPhoto('{{ $ph['path'] ?? 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%23e2e8f0%22/><text x=%2250%22 y=%2250%22 font-size=%2212%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 fill=%22%2364748b%22>Foto</text></svg>' }}', '{{ $ph['filename'] ?? 'Foto problem' }}')"
+                                            wire:click="openPhotoById('{{ $prob['id'] }}', '{{ $ph['id'] }}')"
                                             aria-label="Perbesar {{ $ph['filename'] ?? 'foto' }}"
                                         >
                                             <img
@@ -126,7 +124,7 @@
                                 <small>PIC {{ !empty($act['pic']) ? $act['pic'] : 'belum diisi' }}</small>
                             </div>
                             <div class="date">
-                                {{ !empty($act['actual_date']) ? \Illuminate\Support\Carbon::parse($act['actual_date'])->format('d M Y') : 'Actual date belum diisi' }}
+                                {{ !empty($act['actual_date']) ? \App\Support\UiFormat::date($act['actual_date']) : 'Actual date belum diisi' }}
                             </div>
                         </div>
                     @empty
@@ -146,7 +144,7 @@
                                 {{ !empty($fb['content']) ? $fb['content'] : 'Isi feedback belum diisi' }}
                                 <small>{{ !empty($fb['pic']) ? $fb['pic'] : 'PIC belum diisi' }}</small>
                                 @if(!empty($fb['files']))
-                                    <div class="file-list" style="margin-top: 8px;">
+                                    <div class="file-list">
                                         @foreach($fb['files'] as $doc)
                                             <div class="file-row">
                                                 <x-ui.icon name="file" />
@@ -161,7 +159,7 @@
                                 @endif
                             </div>
                             <div class="date">
-                                {{ !empty($fb['date']) ? \Illuminate\Support\Carbon::parse($fb['date'])->format('d M Y') : 'Tanggal belum diisi' }}
+                                {{ !empty($fb['date']) ? \App\Support\UiFormat::date($fb['date']) : 'Belum diisi' }}
                             </div>
                         </div>
                     @empty
@@ -210,7 +208,7 @@
                     <div class="divider" style="margin: 17px 0;"></div>
                     <div class="key-value">
                         <span>PIC Customer</span>
-                        <strong>{{ $progress['pic_customer'] ?? '—' }}</strong>
+                        <strong>{{ ($progress['pic_customer'] ?? null) ?: '—' }}</strong>
                     </div>
                     @if(!empty($project['id']))
                         <a class="btn text sm" href="?tab=projects&detail={{ $project['id'] }}">
@@ -269,57 +267,21 @@
             </aside>
         </div>
 
-        <!-- Toast Flash Notification -->
-        @if($flashMessage)
-            <div class="toast" role="status">
-                {{ $flashMessage }}
-            </div>
+        @if($confirmingClose)
+            <x-ui.reference-dialog id="close-progress-dialog" title="Close progress?" cancel="cancelConfirmClose">
+                <p>Progress ini akan menjadi Closed. Seluruh catatan, problem, dan tindakan tetap tersimpan.</p>
+                <p>{{ $hasDraft ? 'Ada draft perubahan yang belum diterapkan. Close berlaku pada versi tersimpan dan draft tetap dipertahankan.' : 'Progress dapat di-Reopen jika perlu dilanjutkan atau dikoreksi.' }}</p>
+                <x-slot:footer>
+                    <button type="button" class="btn" wire:click="cancelConfirmClose">Kembali</button>
+                    <button type="button" class="btn primary" wire:click="confirmCloseProgress">Close progress</button>
+                </x-slot:footer>
+            </x-ui.reference-dialog>
         @endif
-
-        <!-- Dialog Konfirmasi Close Progress -->
-        @if ($confirmingClose)
-            <div class="dialog-overlay" style="position: fixed; inset: 0; background: #19263865; z-index: 999; display: grid; place-items: center;" wire:click.self="cancelConfirmClose">
-                <dialog class="dialog-open" id="dialog" open style="position: static; margin: auto; z-index: 1000;">
-                    <div class="dialog-head">
-                        <h2 id="dialog-title">Close progress?</h2>
-                        <button type="button" class="icon-btn" wire:click="cancelConfirmClose" aria-label="Tutup dialog">
-                            <x-ui.icon name="close" />
-                        </button>
-                    </div>
-                    <div class="dialog-body">
-                        <p>Progress ini akan menjadi Closed. Seluruh catatan, problem, dan tindakan tetap tersimpan.</p>
-                        <p style="margin-top: 10px;">
-                            @if ($hasDraft)
-                                Ada draft perubahan yang belum diterapkan. Close berlaku pada versi tersimpan dan draft tetap dipertahankan.
-                            @else
-                                Progress dapat di-Reopen jika perlu dilanjutkan atau dikoreksi.
-                            @endif
-                        </p>
-                    </div>
-                    <div class="dialog-foot">
-                        <button type="button" class="btn" wire:click="cancelConfirmClose">Kembali</button>
-                        <button type="button" class="btn primary" wire:click="confirmCloseProgress">Close progress</button>
-                    </div>
-                </dialog>
-            </div>
-        @endif
-
-        <!-- Dialog Preview Foto Problem -->
-        @if ($activePhoto)
-            <div class="dialog-overlay" style="position: fixed; inset: 0; background: #19263865; z-index: 999; display: grid; place-items: center;" wire:click.self="closePhoto">
-                <dialog class="dialog-open" id="dialog" open style="position: static; margin: auto; z-index: 1000;">
-                    <div class="dialog-head">
-                        <h2 id="dialog-title">Foto problem</h2>
-                        <button type="button" class="icon-btn" wire:click="closePhoto" aria-label="Tutup dialog">
-                            <x-ui.icon name="close" />
-                        </button>
-                    </div>
-                    <div class="dialog-body" style="text-align: center;">
-                        <img class="full-photo" src="{{ $activePhoto['src'] }}" alt="{{ $activePhoto['name'] }}" style="max-height: 60vh; max-width: 100%; margin: auto; display: block; border-radius: 6px;">
-                        <p style="margin-top: 12px; font-weight: 500; font-size: 13px; color: var(--ink);">{{ $activePhoto['name'] }}</p>
-                    </div>
-                </dialog>
-            </div>
+        @if($activePhoto)
+            <x-ui.reference-dialog id="detail-photo-dialog" title="Foto problem" cancel="closePhoto">
+                <img class="full-photo" src="{{ $activePhoto['src'] }}" alt="{{ $activePhoto['name'] }}">
+                <p>{{ $activePhoto['name'] }}</p>
+            </x-ui.reference-dialog>
         @endif
     @endif
 </div>
```

### C18 — `resources/views/livewire/prototype/project-detail.blade.php`

```diff
--- a/resources/views/livewire/prototype/project-detail.blade.php
+++ b/resources/views/livewire/prototype/project-detail.blade.php
@@ -17,7 +17,7 @@
                 <h1>{{ $project['part_name'] }}</h1>
                 <p class="subtitle">{{ $project['customer'] ?: '—' }} / {{ $project['model'] ?: '—' }}</p>
             </div>
-            <a href="?tab=projects&edit={{ $project['id'] }}" class="btn" data-testid="edit-project-btn">
+            <a href="?tab=projects&detail={{ $project['id'] }}&edit={{ $project['id'] }}" class="btn" data-testid="edit-project-btn">
                 <x-ui.icon name="edit" />
                 Edit project
             </a>
@@ -34,7 +34,7 @@
                         $event2 = !empty($project['event_2']) ? $project['event_2'] : (!empty($project['event_2_date']) ? $project['event_2_date'] : null);
                         $event3 = !empty($project['event_3']) ? $project['event_3'] : (!empty($project['event_3_date']) ? $project['event_3_date'] : null);
 
-                        $formatDate = fn($v) => !empty($v) ? \Illuminate\Support\Carbon::parse($v)->locale('id')->isoFormat('D MMM Y') : 'Belum diisi';
+                        $formatDate = fn($v) => !empty($v) ? \App\Support\UiFormat::date($v) : 'Belum diisi';
                     @endphp
 
                     <div class="key-value">
@@ -159,7 +159,7 @@
                                 $isOpen = (strtolower($prog['status'] ?? '') === 'open');
                                 $url = $isOpen ? '?tab=form&id=' . $prog['id'] : '?tab=progresses&detail=' . $prog['id'];
                                 $dateStr = !empty($prog['activity_date'])
-                                    ? \Illuminate\Support\Carbon::parse($prog['activity_date'])->locale('id')->isoFormat('D MMM Y')
+                                    ? \App\Support\UiFormat::date($prog['activity_date'])
                                     : 'Belum diisi';
                                 $nextActions = $prog['next_actions'] ?? [];
                                 $nextCount = count($nextActions);
@@ -168,7 +168,6 @@
                             <tr data-testid="progress-row" data-id="{{ $prog['id'] }}">
                                 <td class="date-col date-cell">
                                     {{ $dateStr }}
-                                    <span class="sr-only">{{ $prog['id'] }}</span>
                                 </td>
                                 <td class="part-col">
                                     <a href="{{ $url }}" class="row-title">
@@ -182,8 +181,9 @@
                                 </td>
                                 <td class="activity-col">
                                     <span class="activity-label">{{ $prog['activity'] }}</span>
-                                    <br>
-                                    <span class="status {{ $isOpen ? '' : 'closed' }}">{{ $prog['status'] }}</span>
+                                    @if(session('prototype_ui_filters.status', 'Open') === 'all')
+                                        <br><span class="status {{ $isOpen ? '' : 'closed' }}">{{ $prog['status'] }}</span>
+                                    @endif
                                 </td>
                                 <td class="next-col">
                                     <span class="next-text">{{ $firstNext ?: 'Belum ada next action' }}</span>
```

### C19 — `app/Livewire/Prototype/DraftList.php`

```diff
--- a/app/Livewire/Prototype/DraftList.php
+++ b/app/Livewire/Prototype/DraftList.php
@@ -32,7 +32,7 @@
     {
         FakeDraftStore::discard($uuid);
         $this->confirmingDiscardUuid = null;
-        $this->flashMessage = 'Draft berhasil dibuang.';
+        $this->dispatch('ui-toast', message: 'Draft dibuang.');
         $this->dispatch('draft-discarded', uuid: $uuid);
     }
 
```

### C20 — `resources/views/livewire/prototype/draft-list.blade.php`

```diff
--- a/resources/views/livewire/prototype/draft-list.blade.php
+++ b/resources/views/livewire/prototype/draft-list.blade.php
@@ -2,7 +2,6 @@
     <div class="page-head">
         <div>
             <h1>Draft</h1>
-            <span class="sr-only">{{ count($drafts) }} Draft Tersimpan</span>
         </div>
         <a href="?tab=form" class="btn primary">
             <x-ui.icon name="plus" />
@@ -16,7 +15,7 @@
                 $proj = $d['project'] ?? null;
                 $isEdit = !empty($d['progress_id']);
                 $isClosed = $d['is_closed'] ?? false;
-                $timeStr = !empty($d['updated_at']) ? \Illuminate\Support\Carbon::parse($d['updated_at'])->format('H:i') : '—';
+                $timeStr = !empty($d['updated_at']) ? \App\Support\UiFormat::time($d['updated_at']) : '—';
             @endphp
             <article class="draft-card" wire:key="draft-{{ $d['uuid'] }}">
                 <div class="draft-card-icon">
@@ -64,31 +63,13 @@
         @endforelse
     </div>
 
-    <!-- Modal Konfirmasi Buang Draft -->
     @if($confirmingDiscardUuid)
-        <div class="dialog-overlay" style="position: fixed; inset: 0; background: #19263865; z-index: 999; display: grid; place-items: center;" wire:click.self="cancelDiscard">
-            <dialog class="dialog-open" id="dialog" open style="position: static; margin: auto; z-index: 1000;">
-                <div class="dialog-head">
-                    <h2 id="dialog-title">Buang draft?</h2>
-                    <button type="button" class="icon-btn" wire:click="cancelDiscard" aria-label="Tutup dialog">
-                        <x-ui.icon name="close" />
-                    </button>
-                </div>
-                <div class="dialog-body">
-                    <p>Isian draft akan dihapus. Progress yang sudah tersimpan tetap utuh.</p>
-                </div>
-                <div class="dialog-foot">
-                    <button type="button" class="btn" wire:click="cancelDiscard">Batal</button>
-                    <button type="button" class="btn danger" wire:click="confirmDiscard" data-testid="confirm-discard-btn">Buang draft</button>
-                </div>
-            </dialog>
-        </div>
-    @endif
-
-    <!-- Flash Toast Notification -->
-    @if($flashMessage)
-        <div class="toast" role="status">
-            {{ $flashMessage }}
-        </div>
+        <x-ui.reference-dialog id="discard-draft-dialog" title="Buang draft?" cancel="cancelDiscard">
+            <p>Isian draft akan dihapus. Progress yang sudah tersimpan tetap utuh.</p>
+            <x-slot:footer>
+                <button type="button" class="btn" wire:click="cancelDiscard">Batal</button>
+                <button type="button" class="btn danger" wire:click="confirmDiscard" data-testid="confirm-discard-btn">Buang draft</button>
+            </x-slot:footer>
+        </x-ui.reference-dialog>
     @endif
 </div>
```

### C21 — `tests/Feature/PrototypeUiParityRegressionTest.php`

```diff
--- /dev/null
+++ b/tests/Feature/PrototypeUiParityRegressionTest.php
@@ -0,0 +1,89 @@
+<?php
+
+namespace Tests\Feature;
+
+use App\Livewire\Prototype\ProgressForm;
+use App\Prototype\FakeDraftStore;
+use App\Prototype\FakeStore;
+use Livewire\Livewire;
+use Tests\TestCase;
+
+class PrototypeUiParityRegressionTest extends TestCase
+{
+    protected function setUp(): void
+    {
+        parent::setUp();
+        $this->withoutVite();
+        session()->forget('prototype_ui_filters');
+        FakeStore::reset();
+    }
+
+    public function test_new_editor_is_blank_but_first_rows_are_ready_without_creating_draft(): void
+    {
+        $before = count(FakeDraftStore::all());
+        $component = Livewire::test(ProgressForm::class)
+            ->assertSet('activity', '')->assertSet('activityDate', '');
+        $this->assertCount(1, $component->get('problems'));
+        $this->assertCount(1, $component->get('problems')[0]['corrective_actions']);
+        $this->assertCount(1, $component->get('nextActions'));
+        $this->assertCount(1, $component->get('feedbacks'));
+        $this->assertSame($before, count(FakeDraftStore::all()));
+    }
+
+    public function test_opening_saved_progress_resumes_draft_and_keeps_applied_record_separate(): void
+    {
+        $before = FakeStore::findProgress('prog-001');
+        $draft = FakeDraftStore::save([
+            'uuid' => 'audit-resume', 'progress_id' => 'prog-001',
+            'project_id' => 'proj-001', 'activity' => 'Trial',
+            'notes' => 'Perubahan yang belum diterapkan',
+        ]);
+        Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
+            ->assertSet('draftUuid', $draft['uuid'])
+            ->assertSet('notes', 'Perubahan yang belum diterapkan');
+        $this->assertSame($before, FakeStore::findProgress('prog-001'));
+    }
+
+    public function test_closed_url_uses_read_only_detail(): void
+    {
+        FakeStore::closeProgress('prog-001');
+        Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
+            ->assertRedirect(route('prototype.index', ['tab' => 'progresses', 'detail' => 'prog-001']));
+    }
+
+    public function test_new_feedback_has_no_automatic_date(): void
+    {
+        $component = Livewire::test(ProgressForm::class)->call('addFeedback');
+        $this->assertCount(2, $component->get('feedbacks'));
+        $this->assertSame('', $component->get('feedbacks')[1]['date']);
+    }
+
+    public function test_selected_photo_and_documents_are_kept_in_draft_and_photo_open_does_not_delete(): void
+    {
+        $component = Livewire::test(ProgressForm::class);
+        $problemId = $component->get('problems')[0]['id'];
+        $feedbackId = $component->get('feedbacks')[0]['id'];
+        $image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aE1sAAAAASUVORK5CYII=';
+        $component->call('attachProblemPhoto', $problemId, ['name' => 'uji.png', 'src' => $image]);
+        $photos = $component->get('problems')[0]['photos'];
+        $this->assertCount(1, $photos);
+        $component->call('openProblemPhoto', $problemId, $photos[0]['id'])
+            ->assertSet('activePhoto.name', 'uji.png');
+        $this->assertSame($photos, $component->get('problems')[0]['photos']);
+        $component->call('attachFiles', 'progress', [['name' => 'hasil.pdf', 'size' => 1200]])
+            ->call('attachFiles', $feedbackId, [['name' => 'feedback.pdf', 'size' => 2300]]);
+        $draft = FakeDraftStore::find($component->get('draftUuid'));
+        $this->assertSame('hasil.pdf', $draft['attachments'][0]['name']);
+        $this->assertSame('feedback.pdf', $draft['feedbacks'][0]['files'][0]['name']);
+        $this->assertSame($image, $draft['problems'][0]['photos'][0]['path']);
+    }
+
+    public function test_removed_pic_progress_is_not_matched_as_pic_customer(): void
+    {
+        $progress = FakeStore::findProgress('prog-001');
+        FakeStore::saveProgress(array_replace($progress, ['pic_progress' => 'PIC_LEGACY_UNIQUE']));
+        $this->assertSame([], FakeStore::getProgresses([
+            'pic' => 'PIC_LEGACY_UNIQUE', 'pic_role' => 'customer', 'status' => 'all',
+        ]));
+    }
+}
```

## 6. Copy histori kosong bila menginginkan kesamaan mutlak

Ini pilihan copy kecil yang dipisahkan dari patch utama, karena pesan histori khusus saat ini masih masuk akal dan bukan penyebab selisih struktur. Di cabang `count($progresses) === 0` pada `project-detail.blade.php`, bentuk visual reference adalah:

```blade
<div class="empty">
    <x-ui.icon name="search" />
    <h3>Tidak ada progress yang cocok</h3>
    <p>Coba kata pencarian lain atau bersihkan filter untuk melihat kegiatan lainnya.</p>
    <button type="button" class="btn" wire:click="clearHistoryFilters">Bersihkan filter</button>
</div>
```

Jika blok di atas dipakai, tambahkan method berikut ke `app/Livewire/Prototype/ProjectDetail.php`. Jangan menambahkan wire:click tanpa implementasi action:

```php
public function clearHistoryFilters(): void
{
    session()->forget('prototype_ui_filters');
    // Histori kosong tetap kosong: reset filter tidak membuat data baru.
}
```

## 7. Menyamakan data untuk perbandingan visual

Jangan mengganti angka ringkasan secara manual. Jangan mengganti histori admin dengan fixture desain. Perbandingan tercepat adalah **Tambah progress baru yang benar-benar kosong**, karena susunan fieldnya sama tanpa perlu menyeragamkan histori.

Untuk daftar/detail, gunakan reference lokal di profil browser QA tersendiri. Script berikut dijalankan pada console reference lokal, lalu pilih `resources/fixtures/prototype.json` dari ZIP. Script mengganti data contoh pada browser QA tersebut agar reference memakai data yang sama dengan fixture Laravel. Gunakan sesi Laravel QA yang juga masih memakai fixture awal; jangan membandingkan fixture dengan session Laravel yang telah diedit.

```javascript
(() => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json,application/json';
    const action = a => ({ id: a.id, description: a.description || '', pic: a.pic || '', actualDate: a.actual_date || '' });
    const placeholder = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#e2e8f0"/><text x="50" y="50" font-size="12" text-anchor="middle" dominant-baseline="middle" fill="#64748b">Foto</text></svg>');
    const files = rows => (rows || []).map((f, i) => ({ id: f.id || `qa-file-${i}`, name: f.name || f.filename || 'dokumen', size: f.size || 0 }));
    const progress = p => ({
        id: p.id || p.uuid, projectId: p.project_id || '', date: p.activity_date || '', activity: p.activity || '',
        description: p.notes || p.description || '', customerPic: p.pic_customer || '',
        processTrial: p.process_trial || '', machine: p.machine || '', dh: p.die_high_raw || p.dh || '',
        material: p.material_actual_trial || '', qtyMaterial: p.qty_material_raw || '', qtyProcess: p.qty_process_raw || '',
        qtySample: p.qty_sample_raw || '', eventNote: p.event_note || '', remark: p.remark || '', status: p.status || 'Open',
        problems: (p.problems || []).map(x => ({
            id: x.id, description: x.description || '', actions: (x.corrective_actions || []).map(action),
            photos: (x.photos || []).map(f => ({ id: f.id, name: f.filename || f.name || 'Foto', src: f.path || f.src || placeholder }))
        })),
        nextActions: (p.next_actions || []).map(action),
        feedback: (p.feedbacks || []).map(f => ({ id: f.id, date: f.date || '', content: f.content || '', pic: f.pic || '', files: files(f.files) })),
        files: files(p.attachments)
    });
    input.onchange = async () => {
        if (!input.files[0]) return;
        const raw = JSON.parse(await input.files[0].text());
        if (!Array.isArray(raw.projects) || !Array.isArray(raw.progresses)) throw new Error('Pilih fixture prototype Laravel.');
        const db = {
            version: 1, applied: {},
            projects: raw.projects.map(p => ({
                id: p.id, jo: p.jo_no, part: p.part_no, name: p.part_name, customer: p.customer || '', model: p.model || '',
                doc: p.no_doc || '', material: p.material || '', spec: p.spec || '', thickness: p.material_thickness_raw || '',
                width: p.material_width_raw || '', process: p.process || '', receive: p.date_receive || '',
                t0Target: p.t0_target || p.t0_target_date || '', t0Actual: p.t0_actual || p.t0_actual_date || '',
                planStart: p.plan_start || '', planFinish: p.plan_finish || '', actualStart: p.actual_start || '', actualFinish: p.actual_finish || '',
                event1: p.event_1 || p.event_1_date || '', event2: p.event_2 || p.event_2_date || '', event3: p.event_3 || p.event_3_date || '', remark: p.remark || ''
            })),
            progress: raw.progresses.map(progress),
            drafts: (raw.drafts || []).map(d => ({ id: d.uuid, progressId: d.progress_id || null, data: progress(d), updatedAt: d.updated_at }))
        };
        localStorage.setItem('new-model-ui-design-v1', JSON.stringify(db));
        location.reload();
    };
    input.click();
})();
```

Ini utilitas QA, bukan script migrasi dan bukan bagian dari patch aplikasi. Source reference tetap tidak diedit. Count Oktober tetap mengikuti aturan demo reference (Oktober 2026); untuk memeriksa periode lain, selaraskan jam uji/periode tanpa mengubah data operasional.

## 8. Pemeriksaan setelah implementasi

### Pemeriksaan visual

Gunakan browser, OS, font yang tersedia, zoom 100%, data, state, dan scroll yang sama. Bandingkan reference lokal dan aplikasi setelah build. Minimal: 1366×768 atau 1475×722, 911×722, dan 390×844. Periksa breakpoint tambahan hanya bila terkait selisih yang ditemukan.

| Bagian | Acuan yang harus dipertahankan |
| --- | --- |
| Sidebar | 220 px desktop 1366/1475; 192 px pada 911; drawer pada ≤800 px |
| Main desktop | Padding 32 px 34 px; max-width 1510 px; pada ≤1200 menjadi 26 px 24 px |
| Header | Tinggi 65 px; header tanggal tersembunyi pada ≤1000 px |
| Heading | h1 28 px; editor 26 px desktop; mengikuti override mobile reference |
| Panel | Radius 8 px, border #d8dee6, background putih |
| Kontrol | Min-height 38 px; font 14 px desktop; ukur tinggi computed, jangan hanya nilai min-height |
| Form editor | Lima section berurutan; input pertama setiap repeater tersedia |
| Trial | Empat kolom desktop, mengikuti breakpoint reference |
| Action row | Uraian, PIC, actual date, dan kontrol baris mengikuti grid reference |
| Project dialog | Native dialog, max-width 790 px, max-height 86vh, radius 10 px |
| Dialog biasa | Max-width 620 px; backdrop #19263865; fokus terkurung selama modal terbuka |
| Savebar | Fixed; offset sidebar; tidak menutup Catatan & lampiran; mobile mengikuti override reference |
| Teks | Tidak mengembalikan logo, footer profil, PIC Progress, penjelasan feedback, maupun copy yang sudah dihapus |

Urutan layar yang dibandingkan: Dashboard Open → Semua progress → filter terbuka → Project → dialog baru/edit → detail project → form baru kosong → Trial kompleks → draft baru/perubahan → detail Open/Closed → preview foto → dialog buang/hapus/Close → empty search.

Console probe berikut dapat dijalankan pada kedua halaman untuk membandingkan geometri dan computed style. Hasil probe belum diambil dalam audit ini:

```javascript
(() => {
    const selectors = ['html', '.sidebar', '.topbar', '.main', '.page-head', '.summary', '.nav-item', '.btn', '.input', '.context', '.entry-section', '.entry-action-row', '.savebar', 'dialog[open]'];
    return selectors.map(selector => {
        const el = document.querySelector(selector);
        if (!el) return { selector, present: false };
        const css = getComputedStyle(el), r = el.getBoundingClientRect();
        return {
            selector, x: r.x, y: r.y, width: r.width, height: r.height,
            display: css.display, fontFamily: css.fontFamily, fontSize: css.fontSize, lineHeight: css.lineHeight,
            color: css.color, background: css.backgroundColor, padding: css.padding, margin: css.margin,
            border: css.border, radius: css.borderRadius, columns: css.gridTemplateColumns
        };
    });
})();
```

### Pemeriksaan interaksi

- Buka form baru: tanggal dan Activity kosong; tidak menambah draft hanya karena dibuka; isian pertama tersedia.
- Pilih JO, isi aktivitas, problem/CA, Next Action sekaligus; identitas master lengkap; semua Activity tetap form sama.
- Tambah problem/CA/Next Action/feedback: fokus ke input baru; feedback belum diberi tanggal; PIC dan tanggal tidak tertukar setelah hapus baris baru.
- Pilih foto sungguhan: thumbnail adalah gambar yang dipilih; klik memperbesar dan tidak menghapus; simpan/resume mempertahankan foto.
- Pilih dokumen feedback dan kegiatan: nama/ukuran muncul pada owner yang benar; dokumen tetap metadata demo.
- Isi perubahan pada progress yang sudah tersimpan; buka Lanjutkan draft dari tabel dan detail: keduanya memuat draft, sedangkan ringkasan sebelum Simpan masih versi applied.
- Mengetik lalu segera klik Kembali/menu/Lihat ringkasan: karakter terakhir tetap masuk draft. Uji juga request lambat dan kegagalan persist; jangan hanya menunggu autosave selesai sebelum mencoba.
- Saat modal terbuka: Tab tetap dalam dialog, Escape sesuai aksi cancel, Ctrl+Enter tidak menyimpan editor di belakang modal, fokus kembali secara masuk akal setelah dialog ditutup.
- Tutup progress tanpa mengisi semua tanggal, kemudian Reopen: seluruh isian utuh. Membuka URL editor Closed harus menuju detail.
- Default filter PIC adalah Semua peran; PIC Customer tidak mencocokkan PIC Progress legacy.
- Sidebar jumlah draft berubah setelah membuat/membuang draft. Toast hilang sekitar 4,2 detik dan pesan baru mereset timer.
- Periksa ulang reload browser, tombol Back/Forward, pergantian tab browser, dan koneksi putus secara khusus. Patch navigasi in-app belum membuktikan ketahanan semua kondisi tersebut.

### Pemeriksaan kode dan regresi

Jalankan `php -l` untuk file PHP yang berubah dan `php artisan view:cache` sebelum browser QA. Jalankan test regresi baru, kemudian test prototype terkait. Test lama yang mengasumsikan Trial/tanggal otomatis, tidak adanya kerangka awal, atau teks tersembunyi legacy perlu diperbarui sesuai keputusan UI terbaru; jangan mengembalikan UI yang salah agar test lama hijau.

```bash
php -l app/Livewire/Prototype/ProgressForm.php
php -l app/Livewire/Prototype/Dashboard.php
php -l app/Livewire/Prototype/ProjectForm.php
php -l app/Livewire/Prototype/ProgressDetail.php
php -l app/Livewire/Prototype/DraftList.php
php -l app/Livewire/Prototype/Shell.php
php -l app/Prototype/FakeStore.php
php -l app/Support/UiFormat.php
php artisan view:cache
php artisan test --filter=PrototypeUiParityRegressionTest
php artisan test --filter=Prototype
```

Jangan menandai checklist visual selesai berdasarkan perintah di atas saja. Tulis viewport, layar/state, data pembanding, screenshot yang benar-benar diperiksa, selisih yang ditemukan, dan hasil setelah perbaikan. Nilai ukuran pada tabel adalah acuan CSS; kesamaan hasil render baru dapat dinyatakan setelah perbandingan browser.
