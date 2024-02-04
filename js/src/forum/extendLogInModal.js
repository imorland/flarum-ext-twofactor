import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import LogInModal from 'flarum/forum/components/LogInModal';
import Stream from 'flarum/common/utils/Stream';

export default function extendLogInModal() {
  extend(LogInModal.prototype, 'oninit', function (vnode) {
    // Initialize the 2FA token stream
    this.twoFactorToken = Stream('');
    this.twoFactorRequired = false;
  });

  extend(LogInModal.prototype, 'fields', function (items) {
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

              if (e.target.value.length === 6) {
                this.onsubmit(new Event('submit')); // Trigger the onsubmit method
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

  extend(LogInModal.prototype, 'loginParams', function (data) {
    // Add the twoFactorToken to the login params
    data.twoFactorToken = this.twoFactorToken();

    return data;
  });

  override(LogInModal.prototype, 'body', function (original) {
    if (this.twoFactorRequired) {
      return <div className="Form Form--centered">{this.fields().toArray()}</div>;
    }

    return original();
  });

  override(LogInModal.prototype, 'footer', function (original) {
    if (this.twoFactorRequired) {
      return <div />;
    }

    return original();
  });

  override(LogInModal.prototype, 'onerror', function (original, error) {
    if (error.status === 422) {
      const errors = error.response && error.response.errors;
      const firstErrorDetail = (errors && errors[0] && errors[0].detail) || '';

      if (firstErrorDetail.includes('two_factor_required')) {
        // If the error indicates that 2FA is required, show the 2FA input field
        this.twoFactorRequired = true;
      } else {
        // Handle other types of 422 errors here
        error.alert.content = app.translator.trans('core.forum.log_in.invalid_login_message');
        this.alertAttrs = error.alert;
      }
      m.redraw();
      this.onready();
    } else {
      original(error);
    }
  });
}
