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
          <input
            className="FormControl"
            name="twoFactorToken"
            type="text"
            placeholder={app.translator.trans('ianm-twofactor.forum.log_in.two_factor_placeholder')}
            bidi={this.twoFactorToken}
            disabled={this.loading}
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

  override(LogInModal.prototype, 'onerror', function (original, error) {
    if (error.status === 401) {
      const errors = error.response && error.response.errors;
      const firstErrorDetail = (errors && errors[0] && errors[0].detail) || '';

      if (firstErrorDetail.includes('two_factor_required')) {
        // If the error indicates that 2FA is required, show the 2FA input field
        this.twoFactorRequired = true;
        error.alert.content = app.translator.trans('ianm-twofactor.forum.log_in.two_factor_required_message');
        this.alertAttrs = error.alert;
      } else {
        // Handle other types of 401 errors here
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
