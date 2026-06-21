<?php

namespace App\Livewire\Admin\Desks;

use App\Models\Desk;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.admin.desks.index', [
            'desks' => Desk::with('airport')->latest()->get(),
        ]);
    }
}
