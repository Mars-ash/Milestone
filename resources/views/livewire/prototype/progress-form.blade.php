<div
    x-data="{
    observer: null, linkHandler: null, stopLeaveInterceptor: null, pending: Promise.resolve(), busy: 0, leaving: false,
    requestFailed: false, requestErrorMessage: '', stopRequestInterceptor: null,
    beforeUnloadHandler: null, changeSeq: 0, unconfirmedInput: false,
    init() {
        this.$nextTick(() => this.growAll());
        this.observer = new MutationObserver(() => this.growAll());
        this.observer.observe(this.$el, { childList: true, subtree: true, characterData: true });
        this.stopRequestInterceptor = this.$wire.$interceptMessage(({ onFailure, onError, onSuccess }) => {
            const submittedSeq = this.changeSeq;
            onFailure(() => {
                this.requestFailed = true;
                this.requestErrorMessage = 'Koneksi terputus. Perubahan belum terkonfirmasi.';
            });
            onError(({ response }) => {
                this.requestFailed = true;
                this.requestErrorMessage = response?.status === 419
                    ? 'Sesi berakhir. Pertahankan isian sebelum memuat ulang.'
                    : 'Permintaan gagal. Isian masih ada di form.';
            });
            onSuccess(({ onSync }) => onSync(() => {
                this.requestFailed = false;
                this.requestErrorMessage = '';
                if (submittedSeq === this.changeSeq && this.$wire.autosaveStatus !== 'error') {
                    this.unconfirmedInput = false;
                }
            }));
        });
        this.beforeUnloadHandler = event => {
            if (this.unconfirmedInput || this.requestFailed || this.busy > 0 || this.$wire.autosaveStatus === 'error') {
                event.preventDefault();
                event.returnValue = '';
            }
        };
        window.addEventListener('beforeunload', this.beforeUnloadHandler);
        this.stopLeaveInterceptor = this.$wire.$intercept('leaveEditor', ({ onFinish }) => {
            onFinish(() => { this.leaving = false; });
        });
        this.linkHandler = async event => {
            const link = event.target.closest('a[href]');
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
            const target = new URL(link.href, location.href);
            if (target.origin !== location.origin || target.pathname.replace(/\/$/, '') !== location.pathname.replace(/\/$/, '') || link.getAttribute('href').startsWith('#')) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            if (this.leaving) return;
            this.leaving = true;
            try {
                await this.pending;
                await this.$wire.$set('pendingUrl', target.href, false);
                this.$refs.leave.click();
            } catch (_) {
                this.leaving = false;
                this.$dispatch('ui-toast', { message: 'Isian belum tersimpan. Coba lagi sebelum berpindah halaman.' });
            }
        };
        document.addEventListener('click', this.linkHandler, true);
    },
    destroy() {
        this.observer?.disconnect();
        document.removeEventListener('click', this.linkHandler, true);
        this.stopLeaveInterceptor?.();
        this.stopRequestInterceptor?.();
        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
    },
    markInput(el) {
        this.changeSeq++;
        this.unconfirmedInput = true;
        this.grow(el);
    },
    grow(el) {
        if (!el?.matches('.entry-sheet textarea')) return;
        el.style.height = 'auto';
        el.style.height = Math.min(320, Math.max(56, el.scrollHeight + 2)) + 'px';
    },
    growAll() { this.$el.querySelectorAll('.entry-sheet textarea').forEach(el => this.grow(el)); },
    focus(selector) {
        this.$nextTick(() => requestAnimationFrame(() => this.$el.querySelector(selector)?.focus()));
    },
    enqueue(job) {
        this.busy++;
        const task = this.pending.catch(() => {}).then(job);
        this.pending = task
            .catch(() => this.$dispatch('ui-toast', {
                message: 'Lampiran belum tersimpan. Coba pilih file kembali.'
            }))
            .finally(() => { this.busy--; });
        return this.pending;
    },
    pickPhotos(event, problemId) {
        const files = Array.from(event.target.files || []);
        event.target.value = '';
        this.enqueue(async () => {
            for (const file of files) {
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    this.$dispatch('ui-toast', { message: 'Gunakan foto JPG, PNG, atau WebP.' }); continue;
                }
                if (file.size > 2 * 1024 * 1024) {
                    this.$dispatch('ui-toast', { message: 'Gunakan foto di bawah 2 MB.' }); continue;
                }
                const src = await new Promise((resolve, reject) => {
                    const reader = new FileReader(); reader.onload = () => resolve(reader.result);
                    reader.onerror = reject; reader.readAsDataURL(file);
                });
                await this.$wire.$call('attachProblemPhoto', problemId, { name: file.name, src });
            }
        });
    },
    pickFiles(event, owner) {
        const files = Array.from(event.target.files || [], f => ({ name: f.name, size: f.size }));
        event.target.value = '';
        if (files.length) this.enqueue(() => this.$wire.$call('attachFiles', owner, files));
    }
}"
    x-on:input.capture="markInput($event.target)"
    x-on:editor-focus.window="focus($event.detail.selector)"
    x-on:focus-first-error.window="focus('.input-error, [aria-invalid=true]')"
    x-on:keydown.window="if (($event.ctrlKey || $event.metaKey) && $event.key === 'Enter' && !document.querySelector('dialog[open]')) { $event.preventDefault(); if (!busy && !$wire.isClosed) $refs.save.click(); }"
>
    <!-- Tombol kembali -->
    <button type="button" class="back" wire:click="cancel" x-bind:disabled="busy > 0 || leaving">
        <x-ui.icon name="left" />Kembali ke daftar
    </button>

    <!-- Header Form Editor -->
    <div class="page-head editor-head">
        <div class="heading-inline">
            <h1>{{ $progressId ? 'Edit progress' : 'Tambah progress' }}</h1>
            <span class="status {{ $isClosed ? 'closed' : ($progressId ? '' : 'draft') }}">{{ $isClosed ? 'Closed' : ($progressId ? 'Open' : 'Draft') }}</span>
        </div>
        <div class="actions">
            @if($progressId)
                <a class="btn ghost sm" href="?tab=progresses&id={{ $progressId }}">
                    Lihat ringkasan <x-ui.icon name="right" />
                </a>
            @endif
            <span class="required-note">* Wajib diisi</span>
        </div>
    </div>



    <!-- Panel JO No & Autofill Identitas Project Master -->
    <section class="context" aria-label="Identitas project">
        <div class="jo-selector">
            <label class="field-label" for="jo-selector">JO No<span class="required">*</span></label>
            <select
                id="jo-selector"
                wire:model.live="projectId"
                aria-required="true"
                @if($errors->has('projectId')) aria-invalid="true" aria-describedby="error-project" class="input-error" @endif
            >
                <option value="">Pilih JO No</option>
                @foreach($projects as $x)
                    <option value="{{ $x['id'] }}">{{ $x['jo_no'] ?? $x['jo'] }}</option>
                @endforeach
            </select>
            @error('projectId')
                <div class="field-error" id="error-project">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <div class="auto-label">
                <x-ui.icon name="lock" />Identitas Project Master
            </div>
            @php
                $pName = $selectedProject['part_name'] ?? $selectedProject['name'] ?? null;
                $pPart = $selectedProject['part_no'] ?? $selectedProject['part'] ?? null;
                $pCust = $selectedProject['customer'] ?? null;
                $pModel = $selectedProject['model'] ?? null;
                $pDoc = $selectedProject['no_doc'] ?? $selectedProject['doc'] ?? null;
            @endphp
            <div class="identity">
                <div class="identity-item">
                    <label>Part</label>
                    <strong>{{ $pName ?: '—' }}</strong>
                    @if($pPart)
                        <small>{{ $pPart }}</small>
                    @endif
                </div>
                <div class="identity-item">
                    <label>Customer</label>
                    <strong>{{ $pCust ?: '—' }}</strong>
                </div>
                <div class="identity-item">
                    <label>Model</label>
                    <strong>{{ $pModel ?: '—' }}</strong>
                </div>
                <div class="identity-item">
                    <label>No Doc</label>
                    <strong>{{ $pDoc ?: '—' }}</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- Error Summary Alert -->
    @if($errors->any())
        <div class="error-summary" role="alert">
            <p>
    {{ implode(' ', $errors->all()) }}
    @if($autosaveStatus === 'saved')
        Isian lainnya tetap tersimpan sebagai draft.
    @else
        Isian masih ada di form. Penyimpanan draft belum terkonfirmasi.
    @endif
</p>
        </div>
    @endif

    <!-- Single Work Sheet Panel (5 Section Tanpa Tab) -->
    <div class="panel entry-sheet">
        <!-- SECTION 01: Aktivitas -->
        <section class="entry-section" id="entry-activity" aria-labelledby="heading-activity">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">01</span>
                <h2 id="heading-activity" tabindex="-1">Aktivitas</h2>
            </div>
            <div class="form-grid activity-fields">
                <div class="field">
                    <label class="field-label" for="f-date">Tanggal aktivitas<span class="required">*</span></label>
                    <input
                        id="f-date"
                        type="date"
                        wire:model.live.debounce.300ms="activityDate"
                        class="input @error('activityDate') input-error @enderror"
                        aria-required="true"
                        @if($errors->has('activityDate')) aria-invalid="true" aria-describedby="error-activityDate" @endif
                    />
                    @error('activityDate')
                        <div class="field-error" id="error-activityDate">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="f-activity">Activity<span class="required">*</span></label>
                    <select
                        id="f-activity"
                        wire:model.live="activity"
                        class="@error('activity') input-error @enderror"
                        aria-required="true"
                        @if($errors->has('activity')) aria-invalid="true" aria-describedby="error-activity" @endif
                    >
                        <option value="">Pilih Activity</option>
                        @foreach(['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'] as $act)
                            <option value="{{ $act }}">{{ $act }}</option>
                        @endforeach
                    </select>
                    @error('activity')
                        <div class="field-error" id="error-activity">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="f-customerPic">PIC Customer</label>
                    <input
                        id="f-customerPic"
                        type="text"
                        wire:model.live.debounce.300ms="picCustomer"
                        class="input"
                    />
                </div>

                <div class="field full">
                    <label class="field-label" for="f-eventNote">Event</label>
                    <input id="f-eventNote" type="text" class="input" wire:model.live.debounce.300ms="eventNote" />
                </div>

                <div class="field full">
                    <label class="field-label" for="f-description">Activity Desc</label>
                    <textarea
                        id="f-description"
                        rows="2"
                        wire:model.live.debounce.300ms="notes"
                    ></textarea>
                </div>
            </div>

            <!-- Subkelompok Rincian Trial & Material -->
            <div class="trial-fields">
                <h3>Rincian trial & material</h3>
                <div class="form-grid trial-grid">
                    <div class="field">
                        <label class="field-label" for="f-processTrial">Process Trial</label>
                        <input id="f-processTrial" type="text" class="input" wire:model.live.debounce.300ms="processTrial" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="f-machine">Mesin</label>
                        <input id="f-machine" type="text" class="input" wire:model.live.debounce.300ms="machine" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="f-dh">Die High</label>
                        <input id="f-dh" type="text" class="input" wire:model.live.debounce.300ms="dieHighRaw" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="f-material">Material Actual Trial</label>
                        <input id="f-material" type="text" class="input" wire:model.live.debounce.300ms="materialActualTrial" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="f-qtyMaterial">Qty Material</label>
                        <input id="f-qtyMaterial" type="text" class="input" wire:model.live.debounce.300ms="qtyMaterialRaw" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="f-qtyProcess">Qty Process</label>
                        <input id="f-qtyProcess" type="text" class="input" wire:model.live.debounce.300ms="qtyProcessRaw" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="f-qtySample">Qty Sample</label>
                        <input id="f-qtySample" type="text" class="input" wire:model.live.debounce.300ms="qtySampleRaw" />
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 02: Catatan & Lampiran -->
        <section class="entry-section" id="entry-files" aria-labelledby="heading-files">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">02</span>
                <h2 id="heading-files" tabindex="-1">Catatan & lampiran</h2>
            </div>
            <div class="field full">
                <label class="field-label" for="f-remark">Remark Progress</label>
                <textarea
                    id="f-remark"
                    rows="2"
                    wire:model.live.debounce.300ms="remark"
                    placeholder="Catatan tambahan"
                ></textarea>
            </div>
            <div class="attachment-line">
                <label class="photo-add">
                    <x-ui.icon name="upload" />Tambah dokumen
                    <input type="file" multiple x-on:change="pickFiles($event, 'progress')" aria-label="Tambah dokumen kegiatan" />
                </label>
            </div>
            <x-ui.reference-files :files="$attachments" />
        </section>

        <!-- SECTION 03: Problem & Corrective Action -->
        <section class="entry-section" id="entry-problems" aria-labelledby="heading-problems">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">03</span>
                <h2 id="heading-problems" tabindex="-1">Problem & Corrective Action</h2>
            </div>
            <div class="problem-list">
                @foreach($problems as $i => $p)
                    <article class="problem" wire:key="problem-{{ $p['id'] ?? $i }}">
                        <div class="problem-head">
                            <h3>Problem {{ $i + 1 }}</h3>
                            @if($this->canRemove('problem', $p['id']) && count($problems) > 1)
                                <button
                                    type="button"
                                    class="icon-btn"
                                    wire:click="promptRemove('problem', '{{ $p['id'] }}')"
                                    aria-label="Hapus problem baru {{ $i + 1 }}"
                                >
                                    <x-ui.icon name="close" />
                                </button>
                            @endif
                        </div>
                        <div class="problem-body">
                            <div class="problem-input">
                                <label class="sr-only" for="problem-{{ $i }}">Uraian problem {{ $i + 1 }}</label>
                                <textarea
                                    class="problem-text"
                                    rows="2"
                                    id="problem-{{ $i }}"
                                    wire:model.live.debounce.300ms="problems.{{ $i }}.description"
                                    placeholder="Jelaskan temuan atau ketidaksesuaian"
                                ></textarea>
                                <div class="photo-strip">
                                    @foreach($p['photos'] ?? [] as $n => $ph)
                                        <button
                                            type="button"
                                            class="icon-btn photo-thumb"
                                            wire:click="openProblemPhoto('{{ $p['id'] }}', '{{ $ph['id'] }}')"
                                            aria-label="Perbesar {{ $ph['filename'] ?? 'foto' }}"
                                            title="{{ $ph['caption'] ?? $ph['filename'] ?? '' }}"
                                        >
                                            <img
                                                class="thumb"
                                                src="{{ $ph['path'] ?? 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%23e2e8f0%22/><text x=%2250%22 y=%2250%22 font-size=%2212%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 fill=%22%2364748b%22>Foto</text></svg>' }}"
                                                alt="{{ $ph['filename'] ?? 'Foto' }}"
                                            >
                                            <span class="sr-only">[SIMULASI] {{ $ph['caption'] ?? '' }}</span>
                                        </button>
                                    @endforeach
                                    <label class="photo-add">
                                        <x-ui.icon name="camera" />Foto problem
                                        <input type="file" accept="image/jpeg,image/png,image/webp" multiple
                                            x-on:change="pickPhotos($event, @js($p['id']))"
                                            aria-label="Tambah foto problem {{ $i + 1 }}">
                                    </label>
                                </div>
                            </div>

                            <div class="corrective-list">
                                @foreach($p['corrective_actions'] ?? [] as $k => $a)
                                    <div class="entry-action-row" wire:key="prob-{{ $p['id'] }}-act-{{ $a['id'] ?? $k }}">
                                        <div class="repeat-index" aria-hidden="true">{{ str_pad($k + 1, 2, '0', STR_PAD_LEFT) }}</div>
                                        <div class="field action-description">
                                            <label class="field-label" for="f-ca-{{ $i }}-{{ $k }}-desc">Corrective Action</label>
                                            <textarea
                                                id="f-ca-{{ $i }}-{{ $k }}-desc"
                                                rows="2"
                                                wire:model.live.debounce.300ms="problems.{{ $i }}.corrective_actions.{{ $k }}.description"
                                                placeholder="Tindakan perbaikan"
                                                aria-label="Uraian corrective action {{ $k + 1 }} problem {{ $i + 1 }}"
                                            ></textarea>
                                        </div>
                                        <div class="field">
                                            <label class="field-label" for="f-ca-{{ $i }}-{{ $k }}-pic">PIC</label>
                                            <input
                                                id="f-ca-{{ $i }}-{{ $k }}-pic"
                                                class="input"
                                                wire:model.live.debounce.300ms="problems.{{ $i }}.corrective_actions.{{ $k }}.pic"
                                                placeholder="Nama PIC"
                                                aria-label="PIC corrective action {{ $k + 1 }} problem {{ $i + 1 }}"
                                            />
                                        </div>
                                        <div class="field">
                                            <label class="field-label" for="f-ca-{{ $i }}-{{ $k }}-date">Actual date</label>
                                            <input
                                                id="f-ca-{{ $i }}-{{ $k }}-date"
                                                type="date"
                                                class="input"
                                                wire:model.live.debounce.300ms="problems.{{ $i }}.corrective_actions.{{ $k }}.actual_date"
                                                aria-label="Actual date corrective action {{ $k + 1 }} problem {{ $i + 1 }}"
                                            />
                                        </div>
                                        @if($this->canRemove('corrective_action', $a['id'], $p['id']) && count($p['corrective_actions'] ?? []) > 1)
                                            <button
                                                type="button"
                                                class="icon-btn row-remove"
                                                wire:click="promptRemove('corrective_action', '{{ $a['id'] }}', '{{ $p['id'] }}')"
                                                aria-label="Hapus corrective action {{ $k + 1 }} problem {{ $i + 1 }} baru"
                                            >
                                                <x-ui.icon name="close" />
                                            </button>
                                        @else
                                            <span></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <button
                                type="button"
                                class="add-link ca-add"
                                wire:click="addCorrectiveAction('{{ $p['id'] }}')"
                            >
                                <x-ui.icon name="plus" />Tambah corrective action
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
            <button
                type="button"
                class="add-link section-add"
                wire:click="addProblem"
            >
                <x-ui.icon name="plus" />Tambah problem
            </button>
        </section>

        <!-- SECTION 04: Next Action -->
        <section class="entry-section" id="entry-nextActions" aria-labelledby="heading-nextActions">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">04</span>
                <h2 id="heading-nextActions" tabindex="-1">Next Action</h2>
            </div>
            <div class="entry-repeat-list">
                @foreach($nextActions as $i => $a)
                    <div class="entry-action-row" wire:key="next-{{ $a['id'] ?? $i }}">
                        <div class="repeat-index" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="field action-description">
                            <label class="field-label" for="f-na-{{ $i }}-desc">Uraian next action</label>
                            <textarea
                                id="f-na-{{ $i }}-desc"
                                rows="2"
                                wire:model.live.debounce.300ms="nextActions.{{ $i }}.description"
                                placeholder="Langkah berikutnya"
                                aria-label="Uraian next action {{ $i + 1 }}"
                            ></textarea>
                        </div>
                        <div class="field">
                            <label class="field-label" for="f-na-{{ $i }}-pic">PIC</label>
                            <input
                                id="f-na-{{ $i }}-pic"
                                class="input"
                                wire:model.live.debounce.300ms="nextActions.{{ $i }}.pic"
                                placeholder="Nama PIC"
                                aria-label="PIC next action {{ $i + 1 }}"
                            />
                        </div>
                        <div class="field">
                            <label class="field-label" for="f-na-{{ $i }}-date">Actual date</label>
                            <input
                                id="f-na-{{ $i }}-date"
                                type="date"
                                class="input"
                                wire:model.live.debounce.300ms="nextActions.{{ $i }}.actual_date"
                                aria-label="Actual date next action {{ $i + 1 }}"
                            />
                        </div>
                        @if($this->canRemove('next_action', $a['id']) && count($nextActions) > 1)
                            <button
                                type="button"
                                class="icon-btn row-remove"
                                wire:click="promptRemove('next_action', '{{ $a['id'] }}')"
                                aria-label="Hapus next action {{ $i + 1 }} baru"
                            >
                                <x-ui.icon name="close" />
                            </button>
                        @else
                            <span></span>
                        @endif
                    </div>
                @endforeach
            </div>
            <button
                type="button"
                class="add-link section-add"
                wire:click="addNextAction"
            >
                <x-ui.icon name="plus" />Tambah next action
            </button>
        </section>

        <!-- SECTION 05: Feedback Customer -->
        <section class="entry-section" id="entry-feedback" aria-labelledby="heading-feedback">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">05</span>
                <h2 id="heading-feedback" tabindex="-1">Feedback Customer</h2>
            </div>
            <div class="feedback-list">
                @foreach($feedbacks as $i => $f)
                    <article class="feedback-entry" wire:key="fb-{{ $f['id'] ?? $i }}">
                        <div class="feedback-grid">
                            <div class="repeat-index" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="field">
                                <label class="field-label" for="f-fb-{{ $i }}-content">Isi feedback</label>
                                <textarea
                                    id="f-fb-{{ $i }}-content"
                                    rows="2"
                                    wire:model.live.debounce.300ms="feedbacks.{{ $i }}.content"
                                    placeholder="Tanggapan customer"
                                ></textarea>
                            </div>
                            <div class="field">
                                <label class="field-label" for="f-fb-{{ $i }}-pic">PIC Feedback Customer</label>
                                <input
                                    id="f-fb-{{ $i }}-pic"
                                    class="input"
                                    wire:model.live.debounce.300ms="feedbacks.{{ $i }}.pic"
                                    placeholder="Nama PIC"
                                />
                            </div>
                            <div class="field">
                                <label class="field-label" for="f-fb-{{ $i }}-date">Tanggal feedback</label>
                                <input
                                    id="f-fb-{{ $i }}-date"
                                    type="date"
                                    class="input"
                                    wire:model.live.debounce.300ms="feedbacks.{{ $i }}.date"
                                />
                            </div>
                            @if($this->canRemove('feedback', $f['id']) && count($feedbacks) > 1)
                                <button
                                    type="button"
                                    class="icon-btn row-remove"
                                    wire:click="promptRemove('feedback', '{{ $f['id'] }}')"
                                    aria-label="Hapus feedback baru {{ $i + 1 }}"
                                >
                                    <x-ui.icon name="close" />
                                </button>
                            @else
                                <span></span>
                            @endif
                        </div>
                        <div class="feedback-attachments">
                            <label class="photo-add compact-upload">
                                <x-ui.icon name="file" />Dokumen feedback
                                <input type="file" multiple x-on:change="pickFiles($event, @js($f['id']))"
                                    aria-label="Tambah dokumen feedback {{ $i + 1 }}">
                            </label>
                            <x-ui.reference-files :files="$f['files'] ?? []" />
                        </div>
                    </article>
                @endforeach
            </div>
            <button
                type="button"
                class="add-link section-add"
                wire:click="addFeedback"
            >
                <x-ui.icon name="plus" />Tambah feedback
            </button>
        </section>
    </div>

    <!-- Fixed Savebar di Bagian Bawah -->
    <footer class="savebar">
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
        <div class="actions">
            <button
                type="button"
                class="btn feedback-jump"
                onclick="const t = document.getElementById('entry-feedback'); t?.scrollIntoView({block:'start',behavior:'instant'}); t?.querySelector('h2')?.focus({preventScroll:true});"
            >
                Ke feedback ↓
            </button>
            <button
                type="button"
                class="btn primary"
                wire:click="save"
                x-ref="save"
                x-bind:disabled="busy > 0 || leaving || $wire.isClosed"
                wire:loading.attr="disabled"
                wire:target="save"
                title="Simpan progress (Ctrl+Enter)"
                @if($isClosed) disabled @endif
            >
                <x-ui.icon name="check" />Simpan progress
            </button>
        </div>
    </footer>

    <button type="button" hidden x-ref="leave" wire:click="leaveEditor"></button>

    @if($pendingRemoval)
        <x-ui.reference-dialog id="remove-row-dialog" title="Hapus baris baru?" cancel="cancelRemove">
            <p>Baris ini belum diterapkan ke progress. Isian pada baris ini akan dihapus dari draft.</p>
            <x-slot:footer>
                <button type="button" class="btn" wire:click="cancelRemove">Batal</button>
                <button type="button" class="btn danger" wire:click="confirmRemove">Hapus baris</button>
            </x-slot:footer>
        </x-ui.reference-dialog>
    @endif
    @if($activePhoto)
        <x-ui.reference-dialog id="editor-photo-dialog" title="Foto problem" cancel="closePhoto">
            <img class="full-photo" src="{{ $activePhoto['src'] }}" alt="{{ $activePhoto['name'] }}">
            <p>{{ $activePhoto['name'] }}</p>
        </x-ui.reference-dialog>
    @endif
</div>
