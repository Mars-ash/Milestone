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
            wire:model.live.debounce.300ms="description"
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
            <label class="field-label" for="f-dh">DH · Die High</label>
            <input id="f-dh" type="text" class="input" wire:model.live.debounce.300ms="dieHighRaw" />
        </div>
        <div class="field">
            <label class="field-label" for="f-material">Material Actual Trial</label>
            <input id="f-material" type="text" class="input" wire:model.live.debounce.300ms="materialActualTrial" />
        </div>
        <div class="field">
            <label class="field-label" for="f-qtyProcess">Qty Process</label>
            <input id="f-qtyProcess" type="text" class="input" wire:model.live.debounce.300ms="qtyProcessRaw" />
        </div>
        <div class="field">
            <label class="field-label" for="f-qtySample">Qty Sample</label>
            <input id="f-qtySample" type="text" class="input" wire:model.live.debounce.300ms="qtySampleRaw" />
        </div>
        <div class="field">
            <label class="field-label" for="f-qtyMaterial">Qty Material</label>
            <input id="f-qtyMaterial" type="text" class="input" wire:model.live.debounce.300ms="qtyMaterialRaw" />
        </div>
    </div>
</div>
