<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->invitation->team->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team-invitation',
            with: [
                'teamName' => $this->invitation->team->name,
                'role' => str_replace('-', ' ', $this->invitation->role),
                'acceptUrl' => route('invitation.show', $this->invitation->token),
                'expiresAt' => $this->invitation->expires_at->toFormattedDateString(),
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
