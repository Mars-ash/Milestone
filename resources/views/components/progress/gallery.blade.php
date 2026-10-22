@props([
    'problemClientId',
    'problemIndex',
    'photos' => [],
    'isClosed' => false,
])

<div class="photo-gallery-container" style="margin-top: 12px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
        <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
            Lampiran Foto Temuan
        </span>
        <span style="font-size: 11px; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">
            {{ count($photos) }} Foto
        </span>
    </div>

    <div class="photo-strip" data-testid="photo-strip-{{ $problemIndex }}">
        @foreach($photos as $phIdx => $photo)
            <div
                class="photo-item"
                wire:key="photo-{{ $problemClientId }}-{{ $photo['id'] ?? $phIdx }}"
                style="display: inline-flex; flex-direction: column; align-items: flex-start; gap: 4px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px; background: #f8fafc;"
                data-testid="photo-item-{{ $problemIndex }}-{{ $phIdx }}"
            >
                <div style="position: relative;">
                    <button
                        type="button"
                        class="icon-btn photo-thumb thumb"
                        wire:click="openLightbox('{{ $photo['id'] }}')"
                        aria-label="Lihat foto {{ $phIdx + 1 }}"
                        title="Klik untuk memperbesar"
                        style="width: 80px; height: 60px; overflow: hidden; padding: 0; border: 1px solid #cbd5e1; border-radius: 4px;"
                    >
                        <img
                            src="{{ $photo['thumbnail_url'] ?? $photo['preview_url'] ?? '' }}"
                            alt="{{ $photo['caption'] ?: ($photo['original_name'] ?? 'Foto problem') }}"
                            style="width: 100%; height: 100%; object-fit: cover;"
                        />
                    </button>
                    @if(!empty($photo['is_staging']))
                        <span style="position: absolute; top: 2px; left: 2px; font-size: 9px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 1px 4px; border-radius: 3px; border: 1px solid #fde68a;">
                            Draft
                        </span>
                    @endif
                </div>

                <input
                    type="text"
                    class="input"
                    style="font-size: 11px; padding: 3px 6px; width: 120px;"
                    wire:model.live.debounce.300ms="problems.{{ $problemIndex }}.photos.{{ $phIdx }}.caption"
                    placeholder="Keterangan foto…"
                    aria-label="Caption foto {{ $phIdx + 1 }}"
                    @if($isClosed) disabled @endif
                    data-testid="photo-caption-{{ $problemIndex }}-{{ $phIdx }}"
                />

                @unless($isClosed)
                    <button
                        type="button"
                        style="font-size: 11px; color: #ef4444; border: 0; background: none; cursor: pointer; padding: 2px 0; align-self: flex-end;"
                        wire:click="removePhoto('{{ $problemClientId }}', '{{ $photo['id'] }}')"
                        aria-label="Hapus foto {{ $phIdx + 1 }}"
                        data-testid="remove-photo-{{ $problemIndex }}-{{ $phIdx }}"
                    >
                        Hapus
                    </button>
                @endunless
            </div>
        @endforeach

        @unless($isClosed)
            <label class="photo-add" data-testid="upload-photo-label-{{ $problemIndex }}" style="height: 100px; display: inline-flex; flex-direction: column; justify-content: center; align-items: center; min-width: 100px;">
                <x-ui.icon name="camera" />
                <span style="margin-top: 4px;">Tambah foto</span>
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    wire:model="pendingPhotos.{{ $problemClientId }}"
                    aria-label="Upload foto problem {{ $problemIndex + 1 }}"
                    data-testid="upload-photo-input-{{ $problemIndex }}"
                />
            </label>
        @endunless
    </div>

    <div wire:loading wire:target="pendingPhotos.{{ $problemClientId }}" style="font-size: 11px; color: #2563eb; margin-top: 4px;">
        Mengunggah berkas ke staging privat…
    </div>

    @error("photos.{$problemClientId}")
        <div class="field-error" style="margin-top: 4px;">{{ $message }}</div>
    @enderror
</div>
