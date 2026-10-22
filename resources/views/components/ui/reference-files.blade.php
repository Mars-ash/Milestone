@props(['files' => []])
@if(count($files))
    <div class="file-list">
        @foreach($files as $file)
            <div class="file-row">
                <x-ui.icon name="file" />
                <div>
                    <strong>{{ $file['name'] ?? $file['filename'] ?? 'dokumen' }}</strong>
                    <small>{{ max(1, round(($file['size'] ?? 0) / 1024)) }} KB</small>
                </div>
                <span class="file-kind">Simulasi lampiran</span>
            </div>
        @endforeach
    </div>
@endif
