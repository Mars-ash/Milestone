@props([
    'problems' => [],
    'isClosed' => false,
])

<div class="problem-stack" data-testid="problems-list">
    @foreach($problems as $i => $prob)
        @php
            $clientId = $prob['client_id'] ?? ('prob-row-' . $i);
            $isStored = !empty($prob['id']);
            $canRemoveRow = !$isClosed && !$isStored && count($problems) > 1;
        @endphp
        <article
            class="problem-card"
            wire:key="problem-{{ $clientId }}"
            data-testid="problem-card-{{ $i }}"
            data-client-id="{{ $clientId }}"
        >
            <div class="problem-card-head">
                <div class="problem-title">
                    <span class="problem-badge">Problem {{ $i + 1 }}</span>
                </div>
                @if($canRemoveRow)
                    <button
                        type="button"
                        class="icon-btn"
                        wire:click="promptRemove('problem', '{{ $clientId }}')"
                        aria-label="Hapus Problem {{ $i + 1 }}"
                        data-testid="remove-problem-{{ $i }}"
                    >
                        <x-ui.icon name="trash" />
                    </button>
                @endif
            </div>

            <div class="field full">
                <label class="field-label" for="problem-{{ $i }}">Uraian Problem {{ $i + 1 }}</label>
                <textarea
                    id="problem-{{ $i }}"
                    rows="2"
                    wire:model.live.debounce.300ms="problems.{{ $i }}.description"
                    placeholder="Jelaskan temuan atau kendala pada kegiatan ini…"
                    @if($isClosed) disabled @endif
                    data-testid="problem-description-{{ $i }}"
                ></textarea>
                @error("problems.{$i}.description")
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Photo Gallery per Problem (P04-T06) -->
            <x-progress.gallery
                :problemIndex="$i"
                :problemClientId="$clientId"
                :photos="$prob['photos'] ?? []"
                :isClosed="$isClosed"
            />

            <!-- Corrective Actions Repeater (P04-T02) -->
            <div style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <h4 style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 10px;">Tindakan Perbaikan (Corrective Action)</h4>
                <x-progress.corrective-actions
                    :problemIndex="$i"
                    :problemClientId="$clientId"
                    :correctiveActions="$prob['corrective_actions'] ?? []"
                    :isClosed="$isClosed"
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
            wire:click="addProblem"
            data-testid="add-problem-btn"
        >
            <x-ui.icon name="plus" />
            Tambah problem
        </button>
    </div>
@endunless