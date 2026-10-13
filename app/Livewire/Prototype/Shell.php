<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeStore;
use Livewire\Component;

class Shell extends Component
{
    public string $activeTab = 'dashboard';
    public array $stats = [];
    public ?string $flashMessage = null;

    public function mount(?string $tab = null): void
    {
        $selectedTab = $tab ?? request()->query('tab');
        if ($selectedTab && in_array($selectedTab, ['dashboard', 'projects', 'progresses', 'drafts', 'components'])) {
            $this->activeTab = $selectedTab;
        } else {
            $this->activeTab = 'dashboard';
        }
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $this->stats = FakeStore::getStats();
    }

    public function resetData(): void
    {
        FakeStore::reset();
        $this->refreshStats();
        $this->flashMessage = 'Data simulasi prototype berhasil direset ke kondisi awal.';
        $this->dispatch('fixture-reset');
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $this->refreshStats();
        $projects = FakeStore::getProjects();
        $progresses = FakeStore::getProgresses();
        $drafts = FakeStore::getDrafts();

        return view('livewire.prototype.shell', [
            'projects' => $projects,
            'progresses' => $progresses,
            'drafts' => $drafts,
        ])->layout('components.layouts.app', [
            'title' => 'Prototype — Progress New Model',
        ]);
    }
}
