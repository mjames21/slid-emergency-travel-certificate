<?php

namespace App\Livewire\Admin\Airports;

use App\Models\Airport;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.admin.airports.index', [
            'airports' => Airport::latest()->get(),
        ]);
    }
}
