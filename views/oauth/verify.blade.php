@extends('flarum.forum::layouts.basic')
@inject('url', 'Flarum\Http\UrlGenerator')

@section('title', $translator->trans('ianm-twofactor.views.two_factor_token.title'))

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

  <h3>{{ $translator->trans('ianm-twofactor.forum.log_in.two_factor_required_message') }}</h3>

  <form class="form" method="POST" action="{{ $url->to('forum')->route('twoFactor.oauth.verify') }}">
    <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
    
    <p class="form-group">
        <input type="text" class="form-control" name="twoFactorToken" placeholder="{{ $translator->trans('ianm-twofactor.forum.log_in.two_factor_placeholder') }}" required>
    </p>

    <p class="form-group">
      <button type="submit" class="button">{{ $translator->trans('ianm-twofactor.views.two_factor_token.submit_button') }}</button>
    </p>
  </form>
@endsection
