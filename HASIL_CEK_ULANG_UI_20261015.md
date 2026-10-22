# Hasil cek ulang UI — Progress New Model

15 Oktober 2026 · Source: `progress-new-model(1).zip`

## Keputusan review

**Struktur UI dalam source sudah jauh lebih dekat ke desain yang disetujui. Sebagian besar koreksi sebelumnya telah diterapkan. Masih ada empat kelompok masalah interaksi yang perlu diselesaikan sebelum pengecekan Fase 1 dinyatakan selesai.**

Ini bukan alasan untuk membangun ulang UI sekali lagi. Pertahankan layout, CSS acuan, komponen, dan susunan form yang sudah diterapkan; lakukan koreksi terarah di bawah.

Perubahan rencana diperhitungkan: UI ini akan diteruskan menjadi UI aplikasi. Backend database, autentikasi, upload operasional, dan kontrol konflik tetap merupakan pekerjaan fase berikutnya. Keberadaan FakeStore sekarang tidak diperlakukan sebagai bukti bahwa semua fase tersebut gagal.

ZIP asli dan source hasil ekstraksi tidak diedit oleh review ini. Kode dalam dokumen adalah usulan koreksi untuk diterapkan pada branch kerja.

## 1. Bukti yang diperiksa

| Pemeriksaan | Hasil aktual |
| --- | --- |
| Source terbaru dibanding ZIP sebelumnya | Diperiksa pada view aktif, component, store, routes, asset, dan test terkait |
| Empat file reference dibanding baseline approved | `styles.css`, `app.js`, `state.js`, `index.html` masih identik byte demi byte |
| 21 file dari patch audit sebelumnya | 20 sesuai usulan setelah mengabaikan baris kosong/trailing whitespace; `ProgressForm.php` mempunyai perubahan logika tambahan |
| Entry asset aktif | Layout menggunakan `resources/css/prototype.css` |
| CSS hasil build yang disertakan ZIP | Tidak memuat reset `html,:host` / `img,svg` yang menyebabkan selisih sebelumnya; style dialog dan tombol tersedia |
| Sintaks Alpine tanpa placeholder Blade | 26 ekspresi berhasil diparse dengan Node |
| Fungsi antrean/navigasi Alpine yang diekstrak dari source | Dijalankan dengan stub DOM dan Livewire; kegagalan antrean dan pengiriman navigasi berulang berhasil direproduksi |
| Usulan koreksi fungsi Alpine | Skenario antrean gagal, navigasi berulang, retry, error jaringan, dan acknowledgement input lolos harness fungsi |
| Build baru | **Belum berhasil dijalankan**: dependensi native `@rolldown/binding-linux-x64-gnu` tidak tersedia dalam node_modules yang diunggah |
| PHP lint, Artisan, PHPUnit | **Belum dijalankan**: executable PHP tidak tersedia di lingkungan review |
| Browser aplikasi, screenshot, overlay pixel | **Belum dijalankan** |

Harness fungsi tidak menjalankan Alpine/Livewire di browser dan tidak menggantikan pengujian Laravel. Kecocokan source atau keberadaan CSS build tidak membuktikan tampilan identik secara piksel. Kegagalan build di lingkungan ini juga bukan bukti bahwa build pada mesin pengembang gagal.

SHA-256 ZIP yang diperiksa:

```text
f97eca905a2e9a7c65da6130f4aa146901cce97d4a383990a9b2c7da21140dd7
```

Nomor baris di bawah mengikuti baris LF pada berkas ZIP. Sebagian file memakai CRCRLF sehingga editor tertentu dapat menampilkan baris kosong tambahan; nama method dan potongan kode menjadi penanda utama.

## 2. Koreksi sebelumnya yang sudah terlihat dalam source

| Audit lama | Kondisi pada ZIP terbaru |
| --- | --- |
| F01–F03: reset CSS, SVG/margin, warna tombol link | Entry CSS khusus tanpa Preflight dan aturan warna `a.btn` sudah masuk; asset build yang disertakan memuatnya |
| F04–F07: modal Project, struktur field, konteks Simpan/Batal | Native dialog, struktur form, dan pengembalian ke konteks detail/list sudah diterapkan |
| F08–F09: default tanggal/Activity | Form baru dan feedback baru tidak otomatis diisi tanggal/Activity |
| F10–F12: lanjutkan draft dan editor Closed | Pemilihan draft, waktu tersimpan, dan redirect Closed sudah ada |
| F13–F16: foto dan lampiran | File picker tersambung; thumbnail membuka preview; daftar lampiran tersambung ke state. Dokumen masih metadata pada adapter saat ini |
| F17–F18: mutasi baris dan lifecycle | Tambah/hapus baris memanggil penyimpanan draft; fokus dan cleanup listener telah ditambahkan |
| F19–F21: navigasi, indikator, normalisasi draft | Sudah berubah, tetapi masih menyisakan R01, R03, dan R04 di bawah |
| F22: filter PIC | UI default Semua peran dan pencarian PIC Customer tidak lagi mencocokkan PIC Progress legacy |
| F23–F30: filter, format tanggal, empty state, detail, toast, aksesibilitas | Koreksi source dari patch sebelumnya sudah diterapkan; perilaku browser tetap perlu diuji |
| F31: data contoh | Masih berbeda dari reference; bukan otomatis kesalahan desain |
| F32: bukti pengujian | Test regresi sudah ditambahkan, tetapi belum bisa dijalankan ulang di lingkungan review ini |

Field Spec tetap tidak muncul pada form Project; Model tetap field teks dan terpisah dari T0 Target. Lima bagian editor tetap terbuka dalam satu halaman. Tidak ada alasan mengubah bagian-bagian tersebut kembali.

## 3. Masalah yang masih perlu diperbaiki

### R01 — P1: keluar dari editor tanpa perubahan bisa membuat draft baru

**Lokasi:** `app/Livewire/Prototype/ProgressForm.php`, method `persistDraft()`, sekitar baris 830–847; `leaveEditor()` sekitar 856–867. Store di `app/Prototype/FakeStore.php::saveDraft()` selalu menyimpan payload yang diterima.

Kondisi sekarang:

```php
if ($fingerprint === $this->draftFingerprint && $this->autosaveStatus === 'saved') return true;
```

Saat editor baru dibuka, fingerprint sudah diisi tetapi status masih `unsaved`. Menekan Kembali atau link sidebar memanggil `leaveEditor()` lalu `persistDraft()`. Fingerprint sebenarnya sama, tetapi syarat status membuat pengecekan perubahan dilewati. Store kemudian membuat draft baru. Hal yang sama berlaku untuk progress tersimpan tanpa draft yang dibuka lalu ditinggalkan tanpa diedit.

**Dampak:** jumlah draft bertambah hanya karena navigasi. Progress dapat terlihat mempunyai draft perubahan padahal admin tidak mengubah isinya.

**Seharusnya:** fingerprint yang sama cukup untuk melewati penulisan. Status error dapat dipulihkan berdasarkan keberadaan draft tanpa membuat record baru. Simulasi kegagalan hanya relevan ketika benar-benar ada data yang perlu disimpan.

**Cara menguji:** hitung draft, buka form kosong, langsung Kembali; jumlah harus tetap. Ulangi pada progress Open yang tidak mempunyai draft. Test saat ini hanya memeriksa kondisi sesudah mount, sehingga belum menangkap aksi keluar ini.

**Dasar temuan:** penelusuran alur PHP; belum dieksekusi dengan Laravel. Kode koreksi: C01.

### R02 — P1: tanggal Project yang dikosongkan bisa kembali menjadi nilai lama

**Lokasi:** `app/Prototype/FakeStore.php::saveProject()`, terutama mapping tanggal sekitar baris 189–216. Pemanggil: `app/Livewire/Prototype/ProjectForm.php::save()`.

Form mengirim tanggal kosong sebagai `null`. Store memakai pola berikut:

```php
'plan_finish' => $data['plan_finish'] ?? $existing['plan_finish'] ?? null,
```

Operator `??` menganggap null sebagai alasan mengambil nilai lama. Akibatnya, admin menghapus tanggal lalu menekan Simpan, tetapi tanggal lama tetap tersimpan. T0 dan Event memiliki alias legacy yang juga harus ikut diperbarui.

**Seharusnya:** bedakan key tidak dikirim dengan key sengaja dikirim null. Key yang ada dengan nilai null berarti hapus nilai tanggal; key yang tidak ada mempertahankan data lama.

**Cara menguji:** isi T0 Target dan Plan Finish, simpan, edit dan kosongkan keduanya, simpan, buka ulang. Keduanya harus kosong. Ulangi pada T0 Actual, Date Receive, tanggal plan/actual lain, serta Event 1–3. Alias T0/Event juga harus null.

**Dasar temuan:** kontrak payload dan operator PHP; belum dieksekusi dengan Laravel. Ini masalah yang sudah ada pada store dan belum tertangkap audit sebelumnya. Kode koreksi: C02.

### R03 — P2: kegagalan lampiran dapat menahan navigasi; penguncian navigasi terlalu singkat

**Lokasi:** `resources/views/livewire/prototype/progress-form.blade.php`, root `x-data`; `await this.pending` sekitar baris 18, `finally` sekitar 23, dan `this.pending = task` sekitar 43.

Ada dua kondisi:

1. Promise unggahan yang ditolak tetap disimpan sebagai `this.pending`. Penanganan `.catch()` hanya menampilkan toast pada rantai lain. Klik link berikutnya selalu menunggu promise yang masih ditolak dan tertahan, walaupun `busy` sudah nol. Memilih file baru kebetulan membuat rantai baru; itu bukan pemulihan navigasi yang jelas.
2. `this.$refs.leave.click()` hanya memulai request. `finally` langsung mengembalikan `leaving` ke false, sehingga klik berikutnya dapat memulai navigasi kedua sebelum respons pertama selesai.

**Hasil harness source asli:** unggahan normal → satu navigasi; unggahan gagal → nol navigasi dan toast berulang; dua klik navigasi sebelum penyelesaian request → dua pemanggilan tombol leave.

**Seharusnya:** antrean menampung promise yang kegagalannya sudah ditangani; pesan tetap meminta admin memilih ulang file gagal. Tahan flag navigasi sampai action `leaveEditor` selesai atau gagal.

**Dasar temuan:** direproduksi pada fungsi asli dengan stub DOM/Livewire; belum pengujian browser. Koreksi dan harness baru menangani kedua kondisi. Celah ini ikut terbawa dari kode rekomendasi audit saya sebelumnya. Kode koreksi: C03.

### R04 — P2: indikator simpan belum menangani kegagalan jaringan secara utuh

**Lokasi:** `resources/views/livewire/prototype/progress-form.blade.php`, indikator `wire:dirty` sekitar baris 558–570 dan error summary sekitar baris 158; `ProgressForm.php::persistDraft()`.

`wire:dirty` menunjukkan perbedaan state client dan snapshot server. Ketika request tidak sampai, perubahan tetap dirty. Source menampilkan “Menyimpan draft demo…” untuk keadaan ini, sedangkan status error PHP hanya dapat diperbarui kalau PHP benar-benar menerima request. Tidak ada penanganan kegagalan request lokal pada editor. Tidak ada guard `beforeunload` untuk ketikan yang masih menunggu debounce atau acknowledgement.

Error summary juga selalu menyatakan “Isian lainnya tetap tersimpan sebagai draft”, termasuk ketika penyimpanan draft gagal.

**Seharusnya:** bedakan request berjalan dan gagal; tampilkan retry tanpa menghapus isian; jangan menyatakan draft aman sebelum ada acknowledgement; tampilkan peringatan browser ketika meninggalkan input yang belum terkonfirmasi. Respons untuk input lama tidak boleh membersihkan penanda input yang lebih baru.

**Dasar temuan:** markup editor dan implementasi `wire:dirty` pada Livewire yang disertakan ZIP. Usulan state client diuji dengan harness fungsi; kondisi offline/reload/session expired belum diuji di browser.

C03–C04 menambahkan guard UI dan status error. Ini belum menggantikan kontrol revision, konflik dua tab, idempotensi, pemulihan sesi, atau ketahanan crash yang memang dijadwalkan pada Fase 5.

## 4. Catatan peralihan rencana dan bukti visual

### UI yang akan dipakai aplikasi masih berjalan di adapter tahap awal

Route aktif masih `/prototype` dan memakai `EnsurePrototypeEnabled`; state masih `FakeStore`/`FakeDraftStore`. Dokumen yang dipilih masih berupa nama/ukuran, bukan penyimpanan byte file operasional. Foto memakai data URL dalam state. Browser title, deskripsi halaman, dan beberapa teks masih memakai istilah prototype/demo.

Ini **pekerjaan integrasi fase berikutnya**, bukan alasan mengulang desain. Pada Fase 2, pertahankan view yang ada dan pindahkan wiring ke fondasi aplikasi. Upload nyata masuk Fase 4; draft yang tahan konflik dan kegagalan masuk Fase 5. Mengganti nama folder atau menghapus kata “prototype” tidak dengan sendirinya menyelesaikan integrasi.

### Header bulan masih hardcoded

`resources/views/livewire/prototype/shell.blade.php`, sekitar baris 77, masih menulis `Oktober 2026`. Angka aktivitas bulanan sudah dihitung berdasarkan bulan berjalan, sehingga label header akan berbeda konteks ketika bulan berganti. Gunakan C05.

### PIC Progress tidak tampil, tetapi sisa state masih ada

`ProgressForm.php` baris 24 kembali mempunyai properti `public ?string $picProgress = '';`. Properti ini tidak dipakai form aktif atau payload simpan terbaru. Beberapa test lama masih menyetelnya; store/fixture juga masih menyimpan field legacy.

Ini **bukan temuan bahwa input PIC Progress muncul lagi**. Bersihkan state dan ekspektasi test yang sudah usang ketika merapikan kontrak Fase 2. Histori sumber yang perlu diarsipkan tetap diperlakukan sebagai data legacy, bukan dihidupkan sebagai field UI.

### Dataset berbeda dan klaim 100% belum dapat diverifikasi

Fixture terbaru masih 3 project, 8 progress, 2 draft, dengan 5 Open dan 3 Closed. Reference approved memiliki 6 Open dan 2 Closed serta isi/nama/tanggal berbeda. Bandingkan screenshot memakai data yang sama; jangan mengubah count secara hardcoded demi terlihat sama.

`docs/progress/UI_REBUILD_SESSION.md` masih mencatat “100% Paritas Tercapai”, 80 test lulus, dan nama rekaman/screenshot. Berkas screenshot yang dirujuk tidak disertakan dalam ZIP ini. Catatan tersebut dapat berasal dari pemeriksaan sebelumnya; saya tidak dapat mengonfirmasi bahwa bukti itu menggambarkan source terbaru.

Perbarui bukti setelah koreksi, dengan commit/versi, viewport, data, dan skenario yang benar-benar diperiksa. Jangan memakai dokumen lama sebagai bukti bahwa R01–R04 sudah diuji.

## 5. Kode koreksi

Terapkan pada branch kerja. C03 dan C04 saling terkait. Tidak perlu merombak CSS, menyusun ulang halaman, mengubah PRD, menghapus database, atau mengganti framework.

### C01 — Ganti method `ProgressForm::persistDraft()`

```php
private function persistDraft(): bool
{
    if (! $this->writable()) {
        return false;
    }

    try {
        $fingerprint = $this->payloadFingerprint();

        if ($fingerprint === $this->draftFingerprint) {
            $this->resetErrorBag('save');
            $this->autosaveStatus = $this->draftUuid ? 'saved' : 'unsaved';
            return true;
        }

        if ($this->simulateSaveError) {
            $this->autosaveStatus = 'error';
            $this->addError('save', 'Simulasi kegagalan simpan draft. Silakan klik Coba Lagi.');
            return false;
        }

        $this->resetErrorBag('save');
        $draft = FakeDraftStore::save(
            $this->cleanProgressPayload($this->getDraftPayload())
        );
        $this->draftUuid = $draft['uuid'];
        $this->lastSavedTime = UiFormat::time($draft['updated_at'] ?? null);
        $this->draftFingerprint = $fingerprint;
        $this->autosaveStatus = 'saved';
        $this->dispatch('draft-changed');
        return true;
    } catch (\Throwable $e) {
        report($e);
        $this->autosaveStatus = 'error';
        return false;
    }
}
```

### C02 — Koreksi mapping tanggal di `FakeStore::saveProject()`

Sesudah deklarasi `$existing`, tambahkan closure ini:

```php
$dateValue = static function (string $key, ?string $legacyKey = null) use ($data, $existing): ?string {
    if (array_key_exists($key, $data)) {
        return $data[$key] === '' ? null : $data[$key];
    }

    return $existing[$key]
        ?? ($legacyKey !== null ? ($existing[$legacyKey] ?? null) : null);
};
```

Di array `$record`, **ganti** seluruh entri tanggal berikut, jangan menambahkan key duplikat. Pertahankan field non-tanggal yang sudah ada:

```php
'date_receive' => $dateValue('date_receive'),

't0_target' => $dateValue('t0_target', 't0_target_date'),

't0_actual' => $dateValue('t0_actual', 't0_actual_date'),

't0_target_date' => $dateValue('t0_target', 't0_target_date'),

't0_actual_date' => $dateValue('t0_actual', 't0_actual_date'),

'plan_start' => $dateValue('plan_start'),
'plan_finish' => $dateValue('plan_finish'),
'actual_start' => $dateValue('actual_start'),
'actual_finish' => $dateValue('actual_finish'),

'event_1' => $dateValue('event_1', 'event_1_date'),
'event_2' => $dateValue('event_2', 'event_2_date'),
'event_3' => $dateValue('event_3', 'event_3_date'),
'event_1_date' => $dateValue('event_1', 'event_1_date'),
'event_2_date' => $dateValue('event_2', 'event_2_date'),
'event_3_date' => $dateValue('event_3', 'event_3_date'),
```

### C03 — Ganti opening tag root editor

File: `resources/views/livewire/prototype/progress-form.blade.php`.

Ganti opening tag `<div ...>` pertama, termasuk seluruh `x-data` dan `x-on` miliknya, dengan blok berikut. **Pertahankan isi form dan closing `</div>` terakhir.** Jangan menambahkan root kedua.

Kode memakai `$wire.$intercept` dan `$wire.$interceptMessage` yang tersedia pada Livewire v4.4.7 dalam ZIP. Callback acknowledgement menggunakan `onSync` setelah snapshot disinkronkan, sebelum efek redirect dijalankan.

```blade
<div
    x-data="{
    observer: null, linkHandler: null, stopLeaveInterceptor: null, pending: Promise.resolve(), busy: 0, leaving: false,
    requestFailed: false, requestErrorMessage: &#x27;&#x27;, stopRequestInterceptor: null,
    beforeUnloadHandler: null, changeSeq: 0, unconfirmedInput: false,
    init() {
        this.$nextTick(() =&gt; this.growAll());
        this.observer = new MutationObserver(() =&gt; this.growAll());
        this.observer.observe(this.$el, { childList: true, subtree: true, characterData: true });
        this.stopRequestInterceptor = this.$wire.$interceptMessage(({ onFailure, onError, onSuccess }) =&gt; {
            const submittedSeq = this.changeSeq;
            onFailure(() =&gt; {
                this.requestFailed = true;
                this.requestErrorMessage = &#x27;Koneksi terputus. Perubahan belum terkonfirmasi.&#x27;;
            });
            onError(({ response }) =&gt; {
                this.requestFailed = true;
                this.requestErrorMessage = response?.status === 419
                    ? &#x27;Sesi berakhir. Pertahankan isian sebelum memuat ulang.&#x27;
                    : &#x27;Permintaan gagal. Isian masih ada di form.&#x27;;
            });
            onSuccess(({ onSync }) =&gt; onSync(() =&gt; {
                this.requestFailed = false;
                this.requestErrorMessage = &#x27;&#x27;;
                if (submittedSeq === this.changeSeq &amp;&amp; this.$wire.autosaveStatus !== &#x27;error&#x27;) {
                    this.unconfirmedInput = false;
                }
            }));
        });
        this.beforeUnloadHandler = event =&gt; {
            if (this.unconfirmedInput || this.requestFailed || this.busy &gt; 0 || this.$wire.autosaveStatus === &#x27;error&#x27;) {
                event.preventDefault();
                event.returnValue = &#x27;&#x27;;
            }
        };
        window.addEventListener(&#x27;beforeunload&#x27;, this.beforeUnloadHandler);
        this.stopLeaveInterceptor = this.$wire.$intercept(&#x27;leaveEditor&#x27;, ({ onFinish }) =&gt; {
            onFinish(() =&gt; { this.leaving = false; });
        });
        this.linkHandler = async event =&gt; {
            const link = event.target.closest(&#x27;a[href]&#x27;);
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.hasAttribute(&#x27;download&#x27;) || (link.target &amp;&amp; link.target !== &#x27;_self&#x27;)) return;
            const target = new URL(link.href, location.href);
            if (target.origin !== location.origin || target.pathname.replace(/\/$/, &#x27;&#x27;) !== location.pathname.replace(/\/$/, &#x27;&#x27;) || link.getAttribute(&#x27;href&#x27;).startsWith(&#x27;#&#x27;)) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            if (this.leaving) return;
            this.leaving = true;
            try {
                await this.pending;
                await this.$wire.$set(&#x27;pendingUrl&#x27;, target.href, false);
                this.$refs.leave.click();
            } catch (_) {
                this.leaving = false;
                this.$dispatch(&#x27;ui-toast&#x27;, { message: &#x27;Isian belum tersimpan. Coba lagi sebelum berpindah halaman.&#x27; });
            }
        };
        document.addEventListener(&#x27;click&#x27;, this.linkHandler, true);
    },
    destroy() {
        this.observer?.disconnect();
        document.removeEventListener(&#x27;click&#x27;, this.linkHandler, true);
        this.stopLeaveInterceptor?.();
        this.stopRequestInterceptor?.();
        window.removeEventListener(&#x27;beforeunload&#x27;, this.beforeUnloadHandler);
    },
    markInput(el) {
        this.changeSeq++;
        this.unconfirmedInput = true;
        this.grow(el);
    },
    grow(el) {
        if (!el?.matches(&#x27;.entry-sheet textarea&#x27;)) return;
        el.style.height = &#x27;auto&#x27;;
        el.style.height = Math.min(320, Math.max(56, el.scrollHeight + 2)) + &#x27;px&#x27;;
    },
    growAll() { this.$el.querySelectorAll(&#x27;.entry-sheet textarea&#x27;).forEach(el =&gt; this.grow(el)); },
    focus(selector) {
        this.$nextTick(() =&gt; requestAnimationFrame(() =&gt; this.$el.querySelector(selector)?.focus()));
    },
    enqueue(job) {
        this.busy++;
        const task = this.pending.catch(() =&gt; {}).then(job);
        this.pending = task
            .catch(() =&gt; this.$dispatch(&#x27;ui-toast&#x27;, {
                message: &#x27;Lampiran belum tersimpan. Coba pilih file kembali.&#x27;
            }))
            .finally(() =&gt; { this.busy--; });
        return this.pending;
    },
    pickPhotos(event, problemId) {
        const files = Array.from(event.target.files || []);
        event.target.value = &#x27;&#x27;;
        this.enqueue(async () =&gt; {
            for (const file of files) {
                if (![&#x27;image/jpeg&#x27;, &#x27;image/png&#x27;, &#x27;image/webp&#x27;].includes(file.type)) {
                    this.$dispatch(&#x27;ui-toast&#x27;, { message: &#x27;Gunakan foto JPG, PNG, atau WebP.&#x27; }); continue;
                }
                if (file.size &gt; 2 * 1024 * 1024) {
                    this.$dispatch(&#x27;ui-toast&#x27;, { message: &#x27;Gunakan foto di bawah 2 MB.&#x27; }); continue;
                }
                const src = await new Promise((resolve, reject) =&gt; {
                    const reader = new FileReader(); reader.onload = () =&gt; resolve(reader.result);
                    reader.onerror = reject; reader.readAsDataURL(file);
                });
                await this.$wire.$call(&#x27;attachProblemPhoto&#x27;, problemId, { name: file.name, src });
            }
        });
    },
    pickFiles(event, owner) {
        const files = Array.from(event.target.files || [], f =&gt; ({ name: f.name, size: f.size }));
        event.target.value = &#x27;&#x27;;
        if (files.length) this.enqueue(() =&gt; this.$wire.$call(&#x27;attachFiles&#x27;, owner, files));
    }
}"
    x-on:input.capture="markInput($event.target)"
    x-on:editor-focus.window="focus($event.detail.selector)"
    x-on:focus-first-error.window="focus('.input-error, [aria-invalid=true]')"
    x-on:keydown.window="if (($event.ctrlKey || $event.metaKey) &amp;&amp; $event.key === 'Enter' &amp;&amp; !document.querySelector('dialog[open]')) { $event.preventDefault(); if (!busy &amp;&amp; !$wire.isClosed) $refs.save.click(); }"
>
```

Handler `beforeunload` hanya memberi peringatan browser; ini bukan penyimpanan sinkron dan tidak menjamin pemulihan setelah browser crash. Alur sesi berakhir tetap memerlukan desain pemulihan pada integrasi autentikasi/draft.

### C04 — Perbarui indikator simpan dan klaim error summary

Pada file editor yang sama, ganti **blok `<div class="save-state">...</div>` di footer savebar** dengan kode ini. Tombol `Ke feedback`, tombol Simpan, serta tombol hidden `x-ref="leave"` tetap dipakai C03.

```blade
<div class="save-state">
    <div class="save-indicator error"
         role="status" aria-live="polite"
         x-show="requestFailed" style="display:none">
        <x-ui.icon name="info" />
        <span x-text="requestErrorMessage"></span>
        <button type="button" class="btn text sm"
                wire:click="saveDraft"
                wire:loading.attr="disabled" wire:target="saveDraft">
            Coba lagi
        </button>
    </div>

    <div x-show="!requestFailed">
        <div class="save-indicator" wire:dirty role="status" aria-live="polite">
            <x-ui.icon name="check" /><span>Menyimpan draft…</span>
        </div>
        <div wire:dirty.remove>
            <div id="save-indicator"
                 class="save-indicator {{ $autosaveStatus === 'error' ? 'error' : '' }}"
                 role="status" aria-live="polite"
                 @if($autosaveStatus === 'unsaved') hidden @endif>
                @if($autosaveStatus === 'saved')
                    <x-ui.icon name="check" />
                    <span>Draft tersimpan · {{ $lastSavedTime }}</span>
                @elseif($autosaveStatus === 'error')
                    <x-ui.icon name="info" /><span>Draft belum tersimpan</span>
                    <button type="button" class="btn text sm"
                            wire:click="saveDraft"
                            wire:loading.attr="disabled" wire:target="saveDraft">
                        Coba lagi
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
```

Di error summary editor, ganti paragraf yang selalu mengklaim draft tersimpan:

```blade
<p>
    {{ implode(' ', $errors->all()) }}
    @if($autosaveStatus === 'saved')
        Isian lainnya tetap tersimpan sebagai draft.
    @else
        Isian masih ada di form. Penyimpanan draft belum terkonfirmasi.
    @endif
</p>
```

Kata “demo” di indikator dihilangkan mengikuti perubahan rencana UI. Hal itu tidak mengubah penyimpanan menjadi PostgreSQL; integrasi backend tetap harus dikerjakan.

### C05 — Ganti bulan hardcoded pada topbar

File: `resources/views/livewire/prototype/shell.blade.php`.

```blade
<span class="topbar-date">
    {{ now('Asia/Jakarta')->locale('id')->isoFormat('MMMM YYYY') }}
</span>
```

### C06 — Tambahkan regresi yang menangkap R01 dan R02

File baru: `tests/Feature/UiRecheckRegressionTest.php`.

Test ini memakai adapter dan fixture uji yang sekarang ada. Jangan menjalankannya terhadap data operasional. Test belum dijalankan dalam review ini karena PHP tidak tersedia.

```php
<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\TestCase;

class UiRecheckRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        FakeStore::reset();
        foreach (FakeDraftStore::all() as $draft) {
            FakeDraftStore::delete($draft['uuid']);
        }
    }

    public function test_leaving_empty_editor_does_not_create_draft(): void
    {
        Livewire::test(ProgressForm::class)
            ->call('cancel')
            ->assertRedirect(route('prototype.index', ['tab' => 'dashboard']));

        $this->assertCount(0, FakeDraftStore::all());
    }

    public function test_leaving_unchanged_saved_progress_does_not_create_draft(): void
    {
        $before = FakeStore::findProgress('prog-001');

        Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
            ->call('cancel')
            ->assertRedirect(route('prototype.index', ['tab' => 'dashboard']));

        $this->assertCount(0, FakeDraftStore::all());
        $this->assertSame($before, FakeStore::findProgress('prog-001'));
    }

    public function test_actual_change_survives_failed_autosave_and_retry(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('simulateSaveError', true)
            ->set('notes', 'Catatan yang belum berhasil disimpan')
            ->assertSet('autosaveStatus', 'error');

        $this->assertCount(0, FakeDraftStore::all());

        $component->set('simulateSaveError', false)
            ->call('saveDraft')
            ->assertSet('autosaveStatus', 'saved');

        $draft = FakeDraftStore::find($component->get('draftUuid'));
        $this->assertSame('Catatan yang belum berhasil disimpan', $draft['notes']);
        $this->assertCount(1, FakeDraftStore::all());
    }

    public function test_project_dates_can_be_explicitly_cleared_including_aliases(): void
    {
        $keys = [
            'date_receive', 't0_target', 't0_actual',
            'plan_start', 'plan_finish', 'actual_start', 'actual_finish',
            'event_1', 'event_2', 'event_3',
        ];
        $project = FakeStore::findProject('proj-001');
        $dated = FakeStore::saveProject(array_replace(
            $project, array_fill_keys($keys, '2026-10-15')
        ));
        $cleared = FakeStore::saveProject(array_replace(
            $dated, array_fill_keys($keys, null)
        ));

        foreach ($keys as $key) {
            $this->assertNull($cleared[$key], $key);
        }
        foreach (['t0_target_date', 't0_actual_date', 'event_1_date', 'event_2_date', 'event_3_date'] as $key) {
            $this->assertNull($cleared[$key], $key);
        }
    }
}
```

## 6. Pemeriksaan yang perlu dijalankan setelah koreksi

### Pemeriksaan kode

Pada environment PHP yang sesuai composer.lock dan dependency Node yang dipasang untuk OS setempat:

```bash
php -l app/Livewire/Prototype/ProgressForm.php
php -l app/Prototype/FakeStore.php
php -l tests/Feature/UiRecheckRegressionTest.php
php artisan view:cache
php artisan test --filter=UiRecheckRegressionTest
php artisan test --filter=PrototypeUiParityRegressionTest
npm run build
```

`node_modules` lintas OS tidak dapat diandalkan untuk build. Untuk checkout uji yang bersih, pasang dependency dari lockfile memakai `npm ci`; jangan menghapus lockfile untuk mengatasi error native binding. Jalankan test terkait lainnya jika ada perubahan kontrak atau kegagalan yang menunjukkan regresi.

### Pemeriksaan browser yang menentukan selesai/tidaknya review

- [ ] Form baru → langsung Kembali/sidebar: jumlah draft tetap.
- [ ] Edit progress tanpa draft → tidak mengubah apa pun → Kembali: tidak muncul draft perubahan.
- [ ] Ketik satu perubahan → tunggu acknowledgement → reload: perubahan kembali.
- [ ] Ketik lalu segera reload sebelum debounce: browser memberi peringatan bila perubahan belum terkonfirmasi.
- [ ] Putus koneksi saat mengetik: indikator gagal terlihat, input tetap, retry setelah koneksi pulih berhasil.
- [ ] Gagalkan satu lampiran: pesan muncul; navigasi setelah error tidak tersangkut pada promise lama. File gagal perlu dipilih ulang.
- [ ] Klik dua link navigasi cepat ketika request lambat: hanya satu transisi aktif; sesudah gagal, dapat mencoba lagi.
- [ ] Gagal menyimpan draft lalu tekan Simpan dengan field wajib kurang: error summary tidak mengklaim draft sudah aman.
- [ ] Kosongkan seluruh jenis tanggal Project lalu buka ulang: nilai tetap kosong, termasuk alias T0/Event.
- [ ] Foto tetap membuka preview, tidak menghapus; problem/CA/next action/feedback tidak tertukar setelah tambah baris.
- [ ] Close/Reopen tetap manual dan seluruh isi tetap ada.
- [ ] Bandingkan dashboard, Project/modal, editor panjang, detail, dan draft pada 1366×768 serta 911×722 dengan dataset yang sama. Ulangi 390×844 untuk menjaga responsive layout yang sudah dibangun.

Browser QA harus mencatat versi, dataset, viewport, dan hasil sebenarnya. Jangan menandai selesai berdasarkan harness fungsi atau source yang tampak benar saja.

## 7. Urutan kerja yang disarankan

1. Terapkan C01 dan C02 untuk memperbaiki draft tanpa perubahan serta koreksi tanggal.
2. Terapkan C03 dan C04 bersama, lalu uji navigasi/unggahan gagal/offline di browser.
3. Terapkan C05 dan jalankan C06 beserta pemeriksaan kode yang relevan.
4. Lakukan perbandingan visual dengan data sama; perbarui handoff menggunakan bukti source terbaru.
5. Lanjutkan Fase 2 dengan mempertahankan UI ini dan daftar integrasi backend yang jelas. Kontrol konflik/idempotensi tetap dikerjakan sesuai fase, bukan dianggap selesai oleh koreksi UI ini.
