<?php

namespace App\Livewire\Sites;

use App\Models\Site;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.sites.index', [
            'sites' => Site::query()->latest()->get(),
        ])->layout('layouts.app');
    }
}
