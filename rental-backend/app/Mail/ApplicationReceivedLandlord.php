<?php

namespace App\Mail;

use App\Models\PropertyApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationReceivedLandlord extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PropertyApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Application for ' . $this->application->property->title);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.applications.received-landlord');
    }

    public function attachments(): array { return []; }
}
