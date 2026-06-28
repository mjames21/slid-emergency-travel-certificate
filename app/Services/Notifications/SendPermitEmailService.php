<?php

namespace App\Services\Notifications;

use App\Mail\PermitIssuedMail;
use App\Models\NotificationLog;
use App\Models\Permit;
use App\Support\Audit;
use Illuminate\Support\Facades\Mail;

class SendPermitEmailService
{
    public function handle(Permit $permit): NotificationLog
    {
        $recipient = $permit->visaApplication->passenger->email;

        $log = NotificationLog::query()->create([
            'permit_id' => $permit->id,
            'visa_application_id' => $permit->visa_application_id,
            'channel' => 'email',
            'recipient' => $recipient,
            'subject' => 'Sierra Leone Immigration Department Emergency Travel Certificate',
            'status' => 'pending',
            'payload' => [
                'permit_no' => $permit->permit_no,
                'verification_code' => $permit->verification_code,
            ],
        ]);

        if (! $recipient) {
            $log->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => 'Passenger email not available.',
            ]);

            return $log;
        }

        Mail::to($recipient)->send(new PermitIssuedMail($permit));

        $log->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Audit::log(
            action: 'notification.email_sent',
            description: 'Emergency Travel Certificate email sent.',
            auditable: $log,
            metadata: [
                'permit_no' => $permit->permit_no,
                'recipient' => $recipient,
            ]
        );

        return $log;
    }
}
