@props([
    'feedbacks' => [],
    'isClosed' => false,
])

<div class="feedback-list" data-testid="feedback-list">
    @foreach($feedbacks as $i => $f)
        @php
            $fbClientId = $f['client_id'] ?? ('fb-row-' . $i);
            $isFbStored = !empty($f['id']);
            $canRemoveFb = !$isClosed && !$isFbStored && count($feedbacks) > 1;
        @endphp
        <article
            class="feedback-entry"
            wire:key="fb-{{ $fbClientId }}"
            data-testid="fb-row-{{ $i }}"
            data-client-id="{{ $fbClientId }}"
        >
            <div class="feedback-grid">
                <div class="repeat-index" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="field">
                    <label class="field-label" for="f-fb-{{ $i }}-content">Isi feedback</label>
                    <textarea
                        id="f-fb-{{ $i }}-content"
                        rows="2"
                        wire:model.live.debounce.300ms="feedbacks.{{ $i }}.content"
                        placeholder="Tanggapan customer"
                        aria-label="Isi feedback {{ $i + 1 }}"
                        @if($isClosed) disabled @endif
                        data-testid="fb-content-{{ $i }}"
                    ></textarea>
                    @error("feedbacks.{$i}.content")
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label class="field-label" for="f-fb-{{ $i }}-pic">PIC Feedback Customer</label>
                    <input
                        id="f-fb-{{ $i }}-pic"
                        class="input"
                        wire:model.live.debounce.300ms="feedbacks.{{ $i }}.pic"
                        placeholder="Nama PIC"
                        aria-label="PIC feedback customer {{ $i + 1 }}"
                        @if($isClosed) disabled @endif
                        data-testid="fb-pic-{{ $i }}"
                    />
                </div>
                <div class="field">
                    <label class="field-label" for="f-fb-{{ $i }}-date">Tanggal feedback</label>
                    <input
                        id="f-fb-{{ $i }}-date"
                        type="date"
                        class="input"
                        wire:model.live.debounce.300ms="feedbacks.{{ $i }}.feedback_date"
                        aria-label="Tanggal feedback {{ $i + 1 }}"
                        @if($isClosed) disabled @endif
                        data-testid="fb-date-{{ $i }}"
                    />
                </div>
                @if($canRemoveFb)
                    <button
                        type="button"
                        class="icon-btn row-remove"
                        wire:click="promptRemove('feedback', '{{ $fbClientId }}')"
                        aria-label="Hapus feedback baru {{ $i + 1 }}"
                        data-testid="remove-fb-{{ $i }}"
                    >
                        <x-ui.icon name="trash" />
                    </button>
                @else
                    <span></span>
                @endif
            </div>

            <div class="feedback-attachments">
                <!-- Dokumen Baru Feedback (P04-T07) -->
                <x-progress.documents
                    ownerKind="feedback"
                    :ownerClientId="$fbClientId"
                    :feedbackIndex="$i"
                    :documents="$f['documents'] ?? []"
                    :isClosed="$isClosed"
                />

                <!-- Dokumen Lama Feedback (P04-T07) -->
                <x-progress.legacy-documents
                    :legacyDocuments="$f['legacy_documents'] ?? []"
                    title="Arsip Dokumen Lama Feedback"
                />
            </div>
        </article>
    @endforeach
</div>

@unless($isClosed)
    <div class="section-foot" style="margin-top: 12px;">
        <button
            type="button"
            class="btn"
            wire:click="addFeedback"
            data-testid="add-feedback-btn"
        >
            <x-ui.icon name="plus" />Tambah feedback
        </button>
    </div>
@endunless
