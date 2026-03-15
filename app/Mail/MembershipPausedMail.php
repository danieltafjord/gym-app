<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipPausedMail extends Mailable
{
    use Queueable, SerializesModels;

    private ?EmailTemplate $template;

    public function __construct(public Membership $membership)
    {
        $this->template = EmailTemplate::resolve(
            'membership_paused',
            $membership->team,
            null,
            $membership->plan,
        );
    }

    public function envelope(): Envelope
    {
        $subject = $this->template
            ? $this->replacePlaceholders($this->template->subject)
            : "Your {$this->membership->plan->name} membership has been paused";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $body = $this->template
            ? $this->replacePlaceholders($this->template->body)
            : $this->defaultBody();

        return new Content(
            markdown: 'emails.membership-lifecycle',
            with: ['body' => $body],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function replacePlaceholders(string $text): string
    {
        return str_replace(
            ['{customer_name}', '{plan_name}'],
            [
                $this->membership->customer_name,
                $this->membership->plan->name,
            ],
            $text,
        );
    }

    private function defaultBody(): string
    {
        return "Hi {$this->membership->customer_name},\n\n"
            ."Your {$this->membership->plan->name} membership has been paused.\n\n"
            .'You can resume your membership at any time by contacting us.';
    }
}
