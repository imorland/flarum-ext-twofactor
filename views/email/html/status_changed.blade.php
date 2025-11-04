<x-mail::html.notification>
    <x-slot:body>
        {!! $formatter->convert($translator->trans('ianm-twofactor.email.body.status_changed', [
    '{recipient_display_name}' => $user->display_name,
    '{forum_url}' => $url->to('forum')->base(),
    '{type}' => $translator->trans('ianm-twofactor.email.status_type.'.$blueprint->type())
])) !!}
    </x-slot:body>

    <x-slot:preview><!-- Optional content --></x-slot:preview>
</x-mail::html.notification>
