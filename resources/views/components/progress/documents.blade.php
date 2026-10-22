@props([
    'ownerKind' => 'progress',
    'ownerClientId' => null,
    'feedbackIndex' => null,
    'documents' => [],
    'isClosed' => false,
])

@php
    $wireUpload = $ownerKind === 'progress'
        ? 'pendingProgressDocuments'
        : "pendingFeedbackDocuments.{$ownerClientId}";

    $errorKey = $ownerKind === 'progress'
        ? 'documents.progress'
        : "documents.{$ownerClientId}";

    $captionPrefix = $ownerKind === 'progress'
        ? 'documents'
        : "feedbacks.{$feedbackIndex}.documents";
@endphp

<div class="documents-container" style="margin-top: 12px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
        <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">
            Lampiran Dokumen Baru
        </span>
        <span style="font-size: 11px; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; white-space: nowrap;">
            {{ count($documents) }} Dokumen
        </span>
    </div>

    @if(!empty($documents) && count($documents) > 0)
        <div class="file-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px;" data-testid="documents-list-{{ $ownerClientId ?: 'progress' }}">
            @foreach($documents as $dIdx => $doc)
                <div
                    class="file-row"
                    wire:key="doc-{{ $ownerClientId ?: 'prog' }}-{{ $doc['id'] ?? $dIdx }}"
                    style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;"
                    data-testid="doc-item-{{ $ownerClientId ?: 'progress' }}-{{ $dIdx }}"
                >
                    <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                        <x-ui.icon name="file" />
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-size: 12px; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $doc['original_name'] }}
                            </div>
                            <div style="font-size: 11px; color: #64748b;">
                                {{ max(1, round(($doc['size_bytes'] ?? 0) / 1024)) }} KB
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                        <input
                            type="text"
                            class="input"
                            style="font-size: 11px; padding: 4px 8px; width: 160px;"
                            wire:model.live.debounce.300ms="{{ $captionPrefix }}.{{ $dIdx }}.caption"
                            placeholder="Keterangan dokumen…"
                            aria-label="Keterangan dokumen {{ $dIdx + 1 }}"
                            @if($isClosed) disabled @endif
                            data-testid="doc-caption-{{ $ownerClientId ?: 'progress' }}-{{ $dIdx }}"
                        />

                        @if(!empty($doc['is_staging']))
                            <span style="font-size: 10px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 3px; border: 1px solid #fde68a;">
                                Draft
                            </span>
                            @if(!empty($doc['preview_url']))
                                <a
                                    href="{{ $doc['preview_url'] }}"
                                    class="btn ghost"
                                    style="font-size: 11px; padding: 4px 8px;"
                                    target="_blank"
                                    rel="noopener"
                                    data-testid="preview-doc-{{ $ownerClientId ?: 'progress' }}-{{ $dIdx }}"
                                >
                                    Preview
                                </a>
                            @endif
                            @unless($isClosed)
                                <button
                                    type="button"
                                    style="font-size: 11px; color: #ef4444; border: 0; background: none; cursor: pointer; padding: 2px 4px;"
                                    @if($ownerKind === 'progress')
                                        wire:click="removeProgressDocument('{{ $doc['id'] }}')"
                                    @else
                                        wire:click="removeFeedbackDocument('{{ $ownerClientId }}', '{{ $doc['id'] }}')"
                                    @endif
                                    aria-label="Hapus dokumen {{ $dIdx + 1 }}"
                                    data-testid="remove-doc-{{ $ownerClientId ?: 'progress' }}-{{ $dIdx }}"
                                >
                                    Hapus
                                </button>
                            @endunless
                        @else
                            @if(!empty($doc['preview_url']))
                                <a
                                    href="{{ $doc['preview_url'] }}"
                                    class="btn ghost"
                                    style="font-size: 11px; padding: 4px 8px;"
                                    target="_blank"
                                    rel="noopener"
                                    data-testid="preview-doc-{{ $ownerClientId ?: 'progress' }}-{{ $dIdx }}"
                                >
                                    Preview
                                </a>
                            @endif
                            @if(!empty($doc['download_url']))
                                <a
                                    href="{{ $doc['download_url'] }}"
                                    class="btn"
                                    style="font-size: 11px; padding: 4px 8px;"
                                    target="_blank"
                                    rel="noopener"
                                    data-testid="download-doc-{{ $ownerClientId ?: 'progress' }}-{{ $dIdx }}"
                                >
                                    Unduh
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @unless($isClosed)
        <div class="attachment-line" style="margin-top: 6px;">
            <label class="photo-add" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; cursor: pointer; border: 1px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; width: max-content; white-space: nowrap;" data-testid="upload-doc-label-{{ $ownerClientId ?: 'progress' }}">
                <x-ui.icon name="upload" />
                <span style="font-size: 12px; font-weight: 500; color: #475569; white-space: nowrap;">Tambah dokumen</span>
                <input
                    type="file"
                    multiple
                    accept=".pdf,.xls,.xlsx,.ppt,.pptx,.doc,.docx,.txt,.csv,.zip"
                    wire:model="{{ $wireUpload }}"
                    aria-label="Upload dokumen {{ $ownerKind === 'progress' ? 'kegiatan' : 'feedback' }}"
                    style="display: none;"
                    data-testid="upload-doc-input-{{ $ownerClientId ?: 'progress' }}"
                />
            </label>
        </div>
    @endunless

    <div wire:loading wire:target="{{ $wireUpload }}" style="font-size: 11px; color: #2563eb; margin-top: 4px;">
        Mengunggah dokumen ke staging privat…
    </div>

    @error($errorKey)
        <div class="field-error" style="margin-top: 4px;">{{ $message }}</div>
    @enderror
</div>
