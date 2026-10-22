@props([
    'name' => 'file',
    'class' => '',
])

@php
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'project' => '<path d="M3 7V5a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M3 8h18"/>',
        'progress' => '<rect x="4" y="4" width="16" height="17" rx="2"/><path d="M9 4V2h6v2M8 10h8M8 14h8M8 18h5"/>',
        'draft' => '<path d="M13 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-8M16 3l5 5M11 18l-5 1 1-5L18 3a2 2 0 0 1 3 3Z"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'right' => '<path d="m9 5 7 7-7 7"/>',
        'left' => '<path d="m15 5-7 7 7 7"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2Z"/><path d="M7 3v6h10V3M7 21v-8h10v8"/>',
        'filter' => '<path d="M4 7h16M7 12h10M10 17h4"/>',
        'sort' => '<path d="M8 4v16m-4-4 4 4 4-4M15 5h5M15 10h4M15 15h2"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v.1"/>',
        'lock' => '<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'camera' => '<path d="M3 7h4l2-3h6l2 3h4v13H3Z"/><circle cx="12" cy="13" r="4"/>',
        'upload' => '<path d="M12 16V3m-5 5 5-5 5 5M4 16v5h16v-5"/>',
        'file' => '<path d="M14 2H5v20h14V7Z"/><path d="M14 2v6h5M8 13h8M8 17h6"/>',
        'edit' => '<path d="m15 5 4 4M4 20l1-5L17 3a2 2 0 0 1 4 4L9 19Z"/>',
        'link' => '<path d="m10 13 4-4M8 16l-2 2a4 4 0 0 1-6-6l5-5a4 4 0 0 1 6 0M13 17a4 4 0 0 0 6 0l4-4a4 4 0 0 0-6-6l-2 2"/>',
        'branch' => '<path d="M6 3v12a4 4 0 0 0 4 4h9M6 8h13m-3-3 3 3-3 3m0 5 3 3-3 3"/>',
        'reopen' => '<path d="M3 11a9 9 0 1 1 3 8M3 3v8h8"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'trash' => '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 11h18"/>',
    ];

    $path = $paths[$name] ?? $paths['file'];
@endphp

<svg {{ $attributes->merge(['class' => 'icon ' . $class]) }} viewBox="0 0 24 24" aria-hidden="true">{!! $path !!}</svg>
