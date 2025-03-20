<?php

namespace Limonlabs\Bigcommerce\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyStoresEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Weekly Stores Email - ' . config('app.name');
        $subjectPrefix = config('mail.from.subject_prefix');
        
        if (!empty($subjectPrefix)) {
            $subject = $subjectPrefix . ' ' . $subject;
        }
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $plans = config('plans');
        $storesInTrial = get_stores_in_trial_period();
        $storesInExpiredTrial = get_stores_in_expired_trial(7);
        $storesUninstalledApps = get_stores_uninstalled_app(7);
        $storesInSubscription = get_subscribed_stores();
        
        return new Content(
            markdown: 'limonlabs/bigcommerce::mail.admin.weekly-stores-email',
            with: [
                'plans' => $plans,
                'storesInTrial' => $storesInTrial,
                'storesInExpiredTrial' => $storesInExpiredTrial,
                'storesUninstalledApps' => $storesUninstalledApps,
                'storesInSubscription' => $storesInSubscription,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
