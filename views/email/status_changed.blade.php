{!! $translator->trans('ianm-twofactor.email.body.status_changed', [
    '{recipient_display_name}' => $user->display_name,
    '{forum_url}' => $url->to('forum')->base(),
    '{type}' => $translator->trans('ianm-twofactor.email.status_type.' . $blueprint->type())
]) !!}
