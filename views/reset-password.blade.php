@extends('flarum.forum::layouts.basic')
@inject('url', 'Flarum\Http\UrlGenerator')

@section('title', $translator->trans('core.views.reset_password.title'))

@section('content')
  @if ($errors->any())
    <div class="errors">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form class="form" method="POST" action="{{ $url->to('forum')->route('savePassword') }}">
    <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
    <input type="hidden" name="passwordToken" value="{{ $passwordToken }}">

    <p class="form-group">
      <input type="password" class="form-control" name="password" autocomplete="new-password" placeholder="{{ $translator->trans('core.views.reset_password.new_password_label') }}">
    </p>

    <p class="form-group">
      <input type="password" class="form-control" name="password_confirmation" autocomplete="new-password" placeholder="{{ $translator->trans('core.views.reset_password.confirm_password_label') }}">
    </p>

    @if ($hasTwoFactorEnabled)
    <p class="form-group">
        <input type="text" class="form-control" name="twoFactorToken" placeholder="{{ $translator->trans('ianm-twofactor.views.reset_password.two_factor_token_label') }}" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
    </p>
    @endif

    <p class="form-group">
      <button type="submit" class="button">{{ $translator->trans('core.views.reset_password.submit_button') }}</button>
    </p>
  </form>
@endsection
