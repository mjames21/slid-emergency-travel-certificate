<?php

namespace App\Livewire\Staff\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\Billing\RecordNraReceiptPaymentService;
use App\Services\Evisa\InitiateOnlineEvisaPaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Invoice $invoice;
    public string $payer_phone = '';
    public string $manual_receipt_no = '';
    public string $manual_receipt_note = '';
    public $manual_receipt_image = null;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load([
            'visaApplication.passenger',
            'visaApplication.airport',
            'visaApplication.desk',
            'visaApplication.permit',
            'payments.receipt',
        ]);

        $this->payer_phone = (string) ($this->invoice->visaApplication->passenger?->phone ?: '');
    }

    public function openPdf()
    {
        return redirect()->route('documents.invoices.show', $this->invoice);
    }

    public function updatedManualReceiptImage(): void
    {
        if ($this->manual_receipt_no !== '' || ! $this->manual_receipt_image) {
            return;
        }

        $guess = $this->guessReceiptNoFromFilename((string) $this->manual_receipt_image->getClientOriginalName());

        if ($guess !== null) {
            $this->manual_receipt_no = $guess;
        }
    }

    public function recordNraReceiptPayment(RecordNraReceiptPaymentService $service): void
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->hasAnyStaffTitle([
                'system_administrator',
                'airport_manager',
                'shift_supervisor',
                'visa_processing_officer',
                'payment_officer',
            ]),
            403
        );

        if ($this->invoice->status === InvoiceStatus::Paid) {
            session()->flash('success', 'Payment is already confirmed for this invoice.');
            return;
        }

        $validated = $this->validate([
            'manual_receipt_no' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9 .\/-]{2,79}$/',
            ],
            'manual_receipt_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:12288'],
            'manual_receipt_note' => ['nullable', 'string', 'max:500'],
        ], [
            'manual_receipt_no.regex' => 'Use the official receipt number exactly as printed. Letters, numbers, spaces, hyphens, slashes, and periods are allowed.',
            'manual_receipt_image.mimes' => 'Upload or capture a clear receipt image as JPG, PNG, WEBP, HEIC, or HEIF.',
        ]);

        $file = $validated['manual_receipt_image'];
        $path = $file->store('receipts/nra', 'local');
        $hash = hash_file('sha256', $file->getRealPath());

        $receipt = $service->handle($user, $this->invoice, [
            'receipt_no' => $validated['manual_receipt_no'],
            'evidence_path' => $path,
            'evidence_original_name' => $file->getClientOriginalName(),
            'evidence_mime_type' => $file->getMimeType(),
            'evidence_size' => $file->getSize(),
            'evidence_hash' => $hash,
            'note' => $validated['manual_receipt_note'] ?? null,
        ]);

        $this->reset(['manual_receipt_no', 'manual_receipt_note', 'manual_receipt_image']);
        $this->refreshInvoice();

        session()->flash('success', "Payment confirmed with NRA receipt {$receipt->receipt_no}.");
    }

    public function startWangovCheckout(InitiateOnlineEvisaPaymentService $service): void
    {
        if ($this->invoice->status === InvoiceStatus::Paid) {
            session()->flash('success', 'Payment is already confirmed for this invoice.');
            return;
        }

        $this->validate([
            'payer_phone' => ['required', 'string', 'max:50'],
        ]);

        $result = $service->handle($this->invoice->visaApplication, $this->payer_phone);

        $this->invoice = $this->invoice->fresh([
            'visaApplication.passenger',
            'visaApplication.airport',
            'visaApplication.desk',
            'visaApplication.permit',
            'payments.receipt',
        ]);

        $message = match ($result['status'] ?? null) {
            'already_paid' => 'Payment is already confirmed for this invoice.',
            'sandbox_registered' => 'Payment request staged locally. WanGov credentials are not enabled in this environment.',
            default => 'WanGov checkout is ready. The traveler can pay now using GovPay.',
        };

        session()->flash('success', $message);
        $this->dispatch('wangov-checkout-ready', reference: $this->invoice->payment_reference);
    }

    public function render(): View
    {
        return view('livewire.staff.invoices.show', [
            'invoice' => $this->invoice,
        ]);
    }

    private function refreshInvoice(): void
    {
        $this->invoice = $this->invoice->fresh([
            'visaApplication.passenger',
            'visaApplication.airport',
            'visaApplication.desk',
            'visaApplication.permit',
            'payments.receipt',
        ]);
    }

    private function guessReceiptNoFromFilename(string $filename): ?string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);

        if (preg_match('/([A-Z]{2,}[- ]?\d{4,}|[A-Z0-9]{6,})/i', $base, $matches) !== 1) {
            return null;
        }

        return strtoupper(trim((string) $matches[1]));
    }
}
