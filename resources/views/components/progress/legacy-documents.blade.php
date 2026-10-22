@props([
    'legacyDocuments' => [],
    'title' => 'Arsip Dokumen Lama (Metadata)',
])

@if(!empty($legacyDocuments) && count($legacyDocuments) > 0)
    <div class="legacy-documents-container" style="margin-top: 14px; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 12px; background: #f8fafc;" data-testid="legacy-documents-panel">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <x-ui.icon name="info" />
                <span style="font-size: 12px; font-weight: 600; color: #475569;">
                    {{ $title }}
                </span>
            </div>
            <span style="font-size: 11px; background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-weight: 500;">
                {{ count($legacyDocuments) }} Berkas
            </span>
        </div>

        <div class="legacy-doc-list" style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($legacyDocuments as $idx => $doc)
                <div
                    class="legacy-doc-item"
                    style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; padding: 8px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px;"
                    data-testid="legacy-doc-item-{{ $idx }}"
                >
                    <div style="display: flex; align-items: flex-start; gap: 8px; min-width: 0;">
                        <x-ui.icon name="file" />
                        <div style="min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #1e293b; word-break: break-all;">
                                {{ $doc['name'] ?: 'Tanpa nama berkas' }}
                            </div>
                            @if(!empty($doc['description']))
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    {{ $doc['description'] }}
                                </div>
                            @endif
                            @if(!empty($doc['source_field']))
                                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                    Sumber Access: {{ strtoupper(str_replace('_', ' ', $doc['source_field'])) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div style="flex-shrink: 0; text-align: right;">
                        <span
                            style="font-size: 10px; font-weight: 600; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; border: 1px solid #fde68a; display: inline-block;"
                            data-testid="legacy-badge-{{ $idx }}"
                        >
                            File lama tidak dimigrasikan
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
