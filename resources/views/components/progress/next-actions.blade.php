@props([
    'nextActions' => [],
    'isClosed' => false,
])

<div class="next-actions-list" data-testid="next-actions-list">
    @foreach($nextActions as $i => $act)
        @php
            $actClientId = $act['client_id'] ?? ('na-row-' . $i);
            $isActStored = !empty($act['id']);
            $canRemoveAct = !$isClosed && !$isActStored && count($nextActions) > 1;
        @endphp
        <div
            class="entry-action-row"
            wire:key="na-act-{{ $actClientId }}"
            data-testid="na-row-{{ $i }}"
            data-client-id="{{ $actClientId }}"
        >
            <div class="repeat-index" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="field action-description">
                <label class="field-label" for="f-na-{{ $i }}-desc">Next Action</label>
                <textarea
                    id="f-na-{{ $i }}-desc"
                    rows="2"
                    wire:model.live.debounce.300ms="nextActions.{{ $i }}.description"
                    placeholder="Rencana kegiatan berikutnya…"
                    aria-label="Uraian next action {{ $i + 1 }}"
                    @if($isClosed) disabled @endif
                    data-testid="na-desc-{{ $i }}"
                ></textarea>
                @error("nextActions.{$i}.description")
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="field">
                <label class="field-label" for="f-na-{{ $i }}-pic">PIC</label>
                <input
                    id="f-na-{{ $i }}-pic"
                    class="input"
                    wire:model.live.debounce.300ms="nextActions.{{ $i }}.pic"
                    placeholder="Nama PIC"
                    aria-label="PIC next action {{ $i + 1 }}"
                    @if($isClosed) disabled @endif
                    data-testid="na-pic-{{ $i }}"
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
                    @if($isClosed) disabled @endif
                    data-testid="na-date-{{ $i }}"
                />
            </div>
            @if($canRemoveAct)
                <button
                    type="button"
                    class="icon-btn row-remove"
                    wire:click="promptRemove('next_action', '{{ $actClientId }}')"
                    aria-label="Hapus next action {{ $i + 1 }}"
                    data-testid="remove-na-{{ $i }}"
                >
                    <x-ui.icon name="trash" />
                </button>
            @else
                <span></span>
            @endif
        </div>
    @endforeach
</div>

@unless($isClosed)
    <div class="section-foot" style="margin-top: 12px;">
        <button
            type="button"
            class="btn"
            wire:click="addNextAction"
            data-testid="add-na-btn"
        >
            <x-ui.icon name="plus" />Tambah next action
        </button>
    </div>
@endunless
