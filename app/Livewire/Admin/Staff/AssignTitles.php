<?php

namespace App\Livewire\Admin\Staff;

use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AssignTitles extends Component
{
    public function render(): View
    {
        return view('livewire.admin.staff.assign-titles', [
            'users' => User::with('staffTitles')->latest()->get(),
            'staffTitles' => StaffTitle::where('active', true)->orderBy('name')->get(),
        ]);
    }
}
