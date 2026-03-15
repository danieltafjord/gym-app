<x-mail::message>
# You've Been Invited

You have been invited to join **{{ $teamName }}** as a **{{ $role }}**.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

This invitation will expire on {{ $expiresAt }}.

If you did not expect this invitation, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
