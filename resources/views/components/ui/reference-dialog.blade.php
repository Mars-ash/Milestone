@props(['id', 'title', 'cancel', 'wide' => false])

<dialog
    id="{{ $id }}"
    class="{{ $wide ? 'dialog-wide' : '' }}"
    aria-labelledby="{{ $id }}-title"
    wire:ignore.self
    x-data="{
        init() { this.$nextTick(() => { if (!this.$el.open) this.$el.showModal(); }); },
        destroy() { if (this.$el.open) this.$el.close(); }
    }"
    x-on:cancel.prevent="$wire.{{ $cancel }}()"
>
    <div class="dialog-head">
        <h2 id="{{ $id }}-title">{{ $title }}</h2>
        <button type="button" class="icon-btn" wire:click="{{ $cancel }}" aria-label="Tutup dialog">
            <x-ui.icon name="close" />
        </button>
    </div>
    <div class="dialog-body">{{ $slot }}</div>
    @isset($footer)
        <div class="dialog-foot">{{ $footer }}</div>
    @endisset
</dialog>
