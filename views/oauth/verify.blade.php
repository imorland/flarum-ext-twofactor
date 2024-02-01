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

  <form class="form" method="POST" action="{{ $url->to('forum')->route('twoFactor.oauth.verify') }}" id="twoFactorForm">
    <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
    
    <p class="form-group">
        <input type="text" class="form-control" name="twoFactorToken" id="twoFactorTokenInput" placeholder="{{ $translator->trans('ianm-twofactor.forum.log_in.two_factor_placeholder') }}" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required>
    </p>

    <p class="form-group">
      <button type="submit" class="button">{{ $translator->trans('ianm-twofactor.views.two_factor_token.submit_button') }}</button>
    </p>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('twoFactorForm');
      const tokenInput = document.getElementById('twoFactorTokenInput');

      tokenInput.addEventListener('input', function() {
        if (tokenInput.value.length === 6) {
          form.submit();
        }
      });
    });
  </script>
@endsection
