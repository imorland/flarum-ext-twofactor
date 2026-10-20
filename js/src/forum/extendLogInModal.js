import Form from 'flarum/common/components/Form';
import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import Stream from 'flarum/common/utils/Stream';

export default function extendLogInModal() {
  extend('flarum/forum/components/LogInModal', 'oninit', function (vnode) {
    // Initialize the 2FA token stream
    this.twoFactorToken = Stream('');
    this.twoFactorRequired = false;
  });

  extend('flarum/forum/components/LogInModal', 'fields', function (items) {
    // Add the 2FA input field to the form
    if (this.twoFactorRequired) {
      items.add(
        'twoFactor',
        <div className="Form-group TwoFactorInput">
          <legend>{app.translator.trans('ianm-twofactor.forum.log_in.two_factor_required_message')}</legend>
          <input
            className="FormControl"
            name="twoFactorToken"
            type="text"
            placeholder={app.translator.trans('ianm-twofactor.forum.log_in.two_factor_placeholder')}
            value={this.twoFactorToken()}
            disabled={this.loading}
            inputmode="numeric"
            pattern="[0-9]*"
            autocomplete="one-time-code"
            oninput={(e) => {
              this.twoFactorToken(e.currentTarget.value);

              // Submit automatically once a full token is entered, unless a captcha
              // still has to be completed (its token is sent with the login params).
              if (e.target.value.length === 6 && !captchaPending(this.loginParams())) {
                this.onsubmit(new Event('submit'));
              }
            }}
          />
        </div>,
        19
      );

      items.remove('identification');
      items.remove('password');
      items.remove('remember');
    }
  });

  extend('flarum/forum/components/LogInModal', 'loginParams', function (data) {
    // Add the twoFactorToken to the login params
    data.twoFactorToken = this.twoFactorToken();

    return data;
  });

  override('flarum/forum/components/LogInModal', 'body', function (original) {
    if (this.twoFactorRequired) {
      return <Form className="Form--centered">{this.fields().toArray()}</Form>;
    }

    return original();
  });

  override('flarum/forum/components/LogInModal', 'footer', function (original) {
    if (this.twoFactorRequired) {
      return <div />;
    }

    return original();
  });

  override('flarum/forum/components/LogInModal', 'onerror', function (original, error) {
    const detail = error.status === 422 ? error.response?.errors?.[0]?.detail : null;

    if (detail === 'two_factor_required') {
      // Not an error to show: ask for the token instead. Render the field now so
      // the original handler can focus it.
      this.twoFactorRequired = true;
      error.alert = null;
      m.redraw.sync();
    } else if (detail === 'two_factor_incorrect') {
      this.twoFactorToken('');

      if (error.alert) {
        error.alert.content = app.translator.trans('ianm-twofactor.forum.log_in.two_factor_incorrect_message');
      }
    }

    // Always hand on to the original handler, so core's messages are shown and
    // other extensions' hooks still run (a captcha resets its widget there).
    original(error);
  });
}

/**
 * Whether the login params include a captcha response (e.g. `turnstileToken`)
 * that has not been completed yet.
 */
function captchaPending(params) {
  return Object.keys(params).some((key) => /captcha|turnstile/i.test(key) && !params[key]);
}
