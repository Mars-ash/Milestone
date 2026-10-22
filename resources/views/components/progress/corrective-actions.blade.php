@props([
    'problemIndex' => 0,
    'problemClientId' => '',
    'correctiveActions' => [],
    'isClosed' => false,
])

<div class="corrective-list" data-testid="ca-list-{{ $problemIndex }}">
    @foreach($correctiveActions as $k => $a)
        @php
            $actClientId = $a['client_id'] ?? ('ca-row-' . $k);
            $isActStored = !empty($a['id']);
            $canRemoveAct = !$isClosed && !$isActStored && count($correctiveActions) > 1;
        @endphp
        <div
            class="entry-action-row"
            wire:key="prob-{{ $problemClientId }}-act-{{ $actClientId }}"
            data-testid="ca-row-{{ $problemIndex }}-{{ $k }}"
            data-client-id="{{ $actClientId }}"
        >
            <div class="repeat-index" aria-hidden="true">{{ str_pad($k + 1, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="field action-description">
                <label class="field-label" for="f-ca-{{ $problemIndex }}-{{ $k }}-desc">Corrective Action</label>
                <textarea
                    id="f-ca-{{ $problemIndex }}-{{ $k }}-desc"
                    rows="2"
                    wire:model.live.debounce.300ms="problems.{{ $problemIndex }}.corrective_actions.{{ $k }}.description"
                    placeholder="Tindakan perbaikan"
                    aria-label="Uraian corrective action {{ $k + 1 }} problem {{ $problemIndex + 1 }}"
                    @if($isClosed) disabled @endif
                    data-testid="ca-desc-{{ $problemIndex }}-{{ $k }}"
                ></textarea>
                @error("problems.{$problemIndex}.corrective_actions.{$k}.description")
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="field">
                <label class="field-label" for="f-ca-{{ $problemIndex }}-{{ $k }}-pic">PIC</label>
                <input
                    id="f-ca-{{ $problemIndex }}-{{ $k }}-pic"
                    class="input"
                    wire:model.live.debounce.300ms="problems.{{ $problemIndex }}.corrective_actions.{{ $k }}.pic"
                    placeholder="Nama PIC"
                    aria-label="PIC corrective action {{ $k + 1 }} problem {{ $problemIndex + 1 }}"
                    @if($isClosed) disabled @endif
                    data-testid="ca-pic-{{ $problemIndex }}-{{ $k }}"
                />
            </div>
            <div class="field">
                <label class="field-label" for="f-ca-{{ $problemIndex }}-{{ $k }}-date">Actual date</label>
                <input
                    id="f-ca-{{ $problemIndex }}-{{ $k }}-date"
                    type="date"
                    class="input"
                    wire:model.live.debounce.300ms="problems.{{ $problemIndex }}.corrective_actions.{{ $k }}.actual_date"
                    aria-label="Actual date corrective action {{ $k + 1 }} problem {{ $problemIndex + 1 }}"
                    @if($isClosed) disabled @endif
                    data-testid="ca-date-{{ $problemIndex }}-{{ $k }}"
                />
            </div>
            @if($canRemoveAct)
                <button
                    type="button"
                    class="icon-btn row-remove"
                    wire:click="promptRemove('corrective_action', '{{ $actClientId }}', '{{ $problemClientId }}')"
                    aria-label="Hapus corrective action {{ $k + 1 }} problem {{ $problemIndex + 1 }}"
                    data-testid="remove-ca-{{ $problemIndex }}-{{ $k }}"
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
    <button
        type="button"
        class="add-link ca-add"
        wire:click="addCorrectiveAction('{{ $problemClientId }}')"
        data-testid="add-ca-btn-{{ $problemIndex }}"
        style="margin-top: 8px;"
    >
        <x-ui.icon name="plus" />Tambah corrective action
    </button>
@endunless
