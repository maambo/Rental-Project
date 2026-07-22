<?php

namespace App\Mail;

use App\Models\PropertyApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedTenant extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PropertyApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Application Submitted – ' . $this->application->property->title);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.applications.submitted-tenant');
    }

    public function attachments(): array { return []; }
}
