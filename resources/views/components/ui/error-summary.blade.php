@props(['errors'])

@if($errors->any())
    <div
        id="error-summary"
        class="error-summary"
        role="alert"
        tabindex="-1"
        style="margin-bottom: 20px; padding: 16px; border: 1px solid #f87171; background-color: #fef2f2; border-radius: 8px; color: #991b1b;"
        x-data="{
            focusTarget(selector) {
                const el = document.querySelector(selector);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (typeof el.focus === 'function') {
                        el.focus({ preventScroll: true });
                    }
                }
            }
        }"
        data-testid="error-summary"
    >
        <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; margin-bottom: 8px;">
            <x-ui.icon name="info" class="w-5 h-5 text-red-600" />
            <span>Terdapat kesalahan pengisian data ({{ $errors->count() }} kesalahan):</span>
        </div>
        <ul style="list-style-type: disc; padding-left: 24px; margin: 0; font-size: 13px; line-height: 1.6;">
            @php
                $uniqueErrors = [];
                foreach ($errors->getMessages() as $key => $messages) {
                    // Skip alias keys like projectId or activityDate to avoid duplicate lines in summary
                    if (in_array($key, ['projectId', 'activityDate']) && isset($errors->getMessages()[$key === 'projectId' ? 'project_id' : 'activity_date'])) {
                        continue;
                    }
                    foreach ($messages as $msg) {
                        $uniqueErrors[$key] = $msg;
                    }
                }
            @endphp
            @foreach($uniqueErrors as $key => $msg)
                @php
                    $target = match($key) {
                        'projectId', 'project_id' => '#f-project',
                        'activityDate', 'activity_date' => '#f-date',
                        'activity' => '#f-activity',
                        'picCustomer', 'pic_customer' => '#f-customerPic',
                        'description' => '#f-description',
                        'processTrial', 'process_trial' => '#f-processTrial',
                        'machine' => '#f-machine',
                        'dieHighRaw', 'die_high_raw' => '#f-dh',
                        'materialActualTrial', 'material_actual_trial' => '#f-material',
                        'qtyMaterialRaw', 'qty_material_raw' => '#f-qtyMaterial',
                        'qtyProcessRaw', 'qty_process_raw' => '#f-qtyProcess',
                        'qtySampleRaw', 'qty_sample_raw' => '#f-qtySample',
                        'eventNote', 'event_note' => '#f-eventNote',
                        'remark' => '#f-remark',
                        default => '#heading-activity'
                    };
                @endphp
                <li>
                    <a
                        href="{{ $target }}"
                        @click.prevent="focusTarget('{{ $target }}')"
                        style="color: #b91c1c; text-decoration: underline; font-weight: 500;"
                    >
                        {{ $msg }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
