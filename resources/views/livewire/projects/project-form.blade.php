<div>
    <x-ui.reference-dialog id="project-dialog" :title="$projectId ? 'Edit project' : 'Tambah project'" cancel="cancel" wide>
        <form wire:submit.prevent="save" id="master-form">
            <div id="master-error" class="field-error" role="alert">{{ $errors->first() }}</div>
            <!-- Kelompok 1: Identitas -->
            <h3 class="group-title">Identitas</h3>
            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="master-jo">JO No *</label>
                    <input
                        id="master-jo"
                        class="input @error('jo_no') input-error @enderror"
                        wire:model="jo_no"
                        type="text"
                        data-testid="input-jo-no"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-part">Part No *</label>
                    <input
                        id="master-part"
                        class="input @error('part_no') input-error @enderror"
                        wire:model="part_no"
                        type="text"
                        data-testid="input-part-no"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-name">Part Name *</label>
                    <input
                        id="master-name"
                        class="input @error('part_name') input-error @enderror"
                        wire:model="part_name"
                        type="text"
                        data-testid="input-part-name"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-customer">Customer</label>
                    <input
                        id="master-customer"
                        class="input"
                        wire:model="customer"
                        type="text"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-model">Model</label>
                    <input
                        id="master-model"
                        class="input"
                        wire:model="model"
                        type="text"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-doc">No Doc</label>
                    <input
                        id="master-doc"
                        class="input"
                        wire:model="no_doc"
                        type="text"
                    >
                </div>
            </div>

            <!-- Kelompok 2: Material & proses -->
            <h3 class="group-title">Material &amp; proses</h3>
            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="master-material">Material</label>
                    <input
                        id="master-material"
                        class="input"
                        wire:model="material"
                        type="text"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-thickness">Material Thickness</label>
                    <input
                        id="master-thickness"
                        class="input"
                        wire:model="material_thickness_raw"
                        type="text"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-width">Material Width</label>
                    <input
                        id="master-width"
                        class="input"
                        wire:model="material_width_raw"
                        type="text"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-pitch">Material Pitch</label>
                    <input
                        id="master-pitch"
                        class="input"
                        wire:model="material_pitch_raw"
                        type="text"
                        data-testid="input-pitch"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-process">Process</label>
                    <select
                        id="master-process"
                        class="input"
                        wire:model="process"
                        data-testid="input-process"
                    >
                        <option value="">Pilih process</option>
                        @if($process && !in_array($process, \App\Models\Project::PROCESS_OPTIONS))
                            <option value="{{ $process }}">{{ $process }}</option>
                        @endif
                        @foreach(\App\Models\Project::PROCESS_OPTIONS as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Kelompok 3: Jadwal -->
            <h3 class="group-title">Jadwal</h3>
            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="master-receive">Date Receive</label>
                    <input
                        id="master-receive"
                        class="input"
                        wire:model="date_receive"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-t0Target">T0 Target</label>
                    <input
                        id="master-t0Target"
                        class="input"
                        wire:model="t0_target"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-t0Actual">T0 Actual</label>
                    <input
                        id="master-t0Actual"
                        class="input"
                        wire:model="t0_actual"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-planStart">Plan Start</label>
                    <input
                        id="master-planStart"
                        class="input"
                        wire:model="plan_start"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-planFinish">Plan Finish</label>
                    <input
                        id="master-planFinish"
                        class="input"
                        wire:model="plan_finish"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-actualStart">Actual Start</label>
                    <input
                        id="master-actualStart"
                        class="input"
                        wire:model="actual_start"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-actualFinish">Actual Finish</label>
                    <input
                        id="master-actualFinish"
                        class="input"
                        wire:model="actual_finish"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-event1">Event 1</label>
                    <input
                        id="master-event1"
                        class="input"
                        wire:model="event_1"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-event2">Event 2</label>
                    <input
                        id="master-event2"
                        class="input"
                        wire:model="event_2"
                        type="date"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="master-event3">Event 3</label>
                    <input
                        id="master-event3"
                        class="input"
                        wire:model="event_3"
                        type="date"
                    >
                </div>
            </div>

            <!-- Remark Project -->
            <div class="field" style="margin-top:22px">
                <label class="field-label" for="master-remark">Remark Project</label>
                <textarea
                    id="master-remark"
                    wire:model="remark"
                ></textarea>
            </div>
        </form>
        <x-slot:footer>
            <button type="button" class="btn" wire:click="cancel">Batal</button>
            <button type="button" class="btn primary" wire:click="save">Simpan project</button>
        </x-slot:footer>
    </x-ui.reference-dialog>
</div>
