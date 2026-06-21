<?php

namespace App\Livewire\Admin\Devices;

use App\Models\DeviceRegistration;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.admin.devices.index', [
            'devices' => DeviceRegistration::with(['airport', 'desk', 'registrar'])->latest()->get(),
        ]);
    }
}
