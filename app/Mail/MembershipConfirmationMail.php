<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Gym;
use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    private ?EmailTemplate $template;

    public function __construct(
        public Membership $membership,
        public Gym $gym,
    ) {
        $this->template = EmailTemplate::resolve(
            'purchase_confirmation',
            $membership->team,
            $gym,
            $membership->plan,
        );
    }

    public function envelope(): Envelope
    {
        $subject = $this->template
            ? $this->replacePlaceholders($this->template->subject)
            : "Welcome to {$this->membership->plan->name}!";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $body = $this->template
            ? $this->replacePlaceholders($this->template->body)
            : $this->defaultBody();

        return new Content(
            markdown: 'emails.membership-confirmation',
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
            ['{customer_name}', '{plan_name}', '{gym_name}', '{access_code}', '{starts_at}', '{ends_at}'],
            [
                $this->membership->customer_name,
                $this->membership->plan->name,
                $this->gym->name,
                $this->membership->access_code,
                $this->membership->starts_at?->toDateString() ?? 'N/A',
                $this->membership->ends_at?->toDateString() ?? 'N/A',
            ],
            $text,
        );
    }

    private function defaultBody(): string
    {
        $startsAt = $this->membership->starts_at?->toDateTimeString() ?? 'Activates on first check-in';
        $endsAt = $this->membership->ends_at?->toDateTimeString() ?? 'N/A';

        return "Hi {$this->membership->customer_name},\n\n"
            ."Thank you for purchasing {$this->membership->plan->name} at {$this->gym->name}.\n\n"
            ."Your access code is: **{$this->membership->access_code}**\n\n"
            ."Start date: {$startsAt}\n"
            ."End date: {$endsAt}";
    }
}
