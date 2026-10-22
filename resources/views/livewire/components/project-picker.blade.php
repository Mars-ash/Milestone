<section class="context" aria-label="Identitas project">
    <div class="jo-selector">
        <label class="field-label" for="f-project">JO No<span class="required">*</span></label>
        @if($readOnly)
            <div class="input disabled" style="background:#f1f5f9; cursor:not-allowed;">
                {{ $project?->jo_no ?: 'Belum dipilih' }}
            </div>
        @else
            <select
                id="f-project"
                class="input @error('selectedId') input-error @enderror"
                wire:model.live="selectedId"
                aria-required="true"
            >
                <option value="">Pilih JO No</option>
                @foreach($projects as $x)
                    <option value="{{ $x->id }}">{{ $x->jo_no }}</option>
                @endforeach
            </select>
        @endif
        @error('selectedId')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <div class="auto-label">
            <x-ui.icon name="lock" />Identitas Project Master
        </div>
        <div class="identity">
            <div class="identity-item">
                <label>Part</label>
                <strong>{{ $project?->part_name ?: '—' }}</strong>
                @if($project?->part_no)
                    <small>{{ $project->part_no }}</small>
                @endif
            </div>
            <div class="identity-item">
                <label>Customer</label>
                <strong>{{ $project?->customer ?: '—' }}</strong>
            </div>
            <div class="identity-item">
                <label>Model</label>
                <strong>{{ $project?->model ?: '—' }}</strong>
            </div>
            <div class="identity-item">
                <label>No Doc</label>
                <strong>{{ $project?->no_doc ?: '—' }}</strong>
            </div>
        </div>
    </div>
</section>
