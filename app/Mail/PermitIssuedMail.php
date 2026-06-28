<?php

namespace App\Mail;

use App\Models\Permit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PermitIssuedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Permit $permit) {}

    public function build(): self
    {
        return $this->subject('Sierra Leone Immigration Department Emergency Travel Certificate')
            ->view('emails.permit-issued');
    }
}
