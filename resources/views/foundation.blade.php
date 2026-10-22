@php
    $activeTab = request()->query('tab', 'progresses');
    if ($activeTab === 'dashboard') {
        $activeTab = 'progresses';
    }

    $activeNav = match($activeTab) {
        'projects' => 'project',
        'drafts' => 'draft',
        default => 'progress',
    };

    $label = match($activeTab) {
        'projects' => 'Project',
        'drafts' => 'Draft',
        'form' => 'Input Progress',
        default => 'Progress',
    };

    $admin = auth()->user();
    $draftCount = $admin ? app(\App\Services\ProgressDraftService::class)->listActiveDrafts($admin)->count() : 0;
@endphp

<x-layouts.app :title="$label . ' — Progress New Model'">
    <button class="scrim" @click="mobileMenu = false" aria-label="Tutup navigasi"></button>
    <aside class="sidebar" aria-label="Navigasi utama">
        <a class="brand" href="{{ route('app', ['tab' => 'progresses']) }}" @click="mobileMenu = false">
            <span>
                <span class="brand-name">New Model</span>
                <span class="brand-sub" style="display:block">PROGRESS TRACKER</span>
            </span>
        </a>
        <div class="nav-label">WORKSPACE</div>
        <nav class="nav">
            <a href="{{ route('app', ['tab' => 'progresses']) }}"
                class="nav-item {{ $activeNav === 'progress' ? 'active' : '' }}"
                @if($activeNav === 'progress') aria-current="page" @endif
                @click="mobileMenu = false">
                <x-ui.icon name="progress" /><span>Progress</span>
            </a>
            <a href="{{ route('app', ['tab' => 'projects']) }}"
                class="nav-item {{ $activeNav === 'project' ? 'active' : '' }}"
                @if($activeNav === 'project') aria-current="page" @endif
                @click="mobileMenu = false">
                <x-ui.icon name="project" /><span>Project</span>
            </a>
            <a href="{{ route('app', ['tab' => 'drafts']) }}"
                class="nav-item {{ $activeNav === 'draft' ? 'active' : '' }}"
                @if($activeNav === 'draft') aria-current="page" @endif
                @click="mobileMenu = false">
                <x-ui.icon name="draft" /><span>Draft</span>
                @if($draftCount > 0)
                    <span class="nav-count">{{ $draftCount }}</span>
                @endif
            </a>
        </nav>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <div class="breadcrumb">
                <button
                    type="button"
                    class="icon-btn mobile-menu"
                    aria-label="Buka navigasi"
                    :aria-expanded="mobileMenu"
                    @click="mobileMenu = !mobileMenu"
                >
                    <x-ui.icon name="menu" />
                </button>
                <span>Workspace</span>
                <span class="slash">/</span>
                <strong>{{ $label }}</strong>
            </div>
            <div class="topbar-right">
                <span class="topbar-date">{{ now('Asia/Jakarta')->locale('id')->isoFormat('MMMM YYYY') }}</span>
            </div>
        </header>

        <main id="main" class="main {{ $activeTab === 'form' ? 'editor-main' : '' }}" tabindex="-1">
            @if($activeTab === 'progresses')
                @if(request()->query('detail') || request()->query('id'))
                    <livewire:progresses.progress-show :progressId="(int) (request()->query('detail') ?? request()->query('id'))" />
                @else
                    <livewire:dashboard.dashboard-overview />
                    <livewire:progresses.progress-index :isDashboard="true" />
                @endif

            @elseif($activeTab === 'projects')
                @if(request()->query('detail'))
                    <livewire:projects.project-show :projectId="(int) request()->query('detail')" />
                @else
                    <livewire:projects.project-index />
                @endif

            @elseif($activeTab === 'drafts')
                <livewire:drafts.draft-index />

            @elseif($activeTab === 'form')
                <livewire:progresses.progress-editor
                    :projectId="request()->query('project_id') ? (int) request()->query('project_id') : null"
                    :progressId="request()->query('id') ? (int) request()->query('id') : null"
                    :draft="request()->query('draft')"
                />
            @endif
        </main>
    </div>
</x-layouts.app>
