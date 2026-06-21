<?php

namespace App\Livewire\Staff\Receipts;

use App\Models\Receipt;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public Receipt $receipt;

    public function mount(Receipt $receipt): void
    {
        $this->receipt = $receipt->load([
            'payment.invoice.visaApplication.passenger',
            'payment.invoice.visaApplication.airport',
            'issuer',
        ]);
    }

    public function openPdf()
    {
        return redirect()->route('documents.receipts.show', $this->receipt);
    }

    public function render(): View
    {
        return view('livewire.staff.receipts.show', [
            'receipt' => $this->receipt,
        ]);
    }
}
