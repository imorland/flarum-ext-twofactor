import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Stream from 'flarum/common/utils/Stream';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';

export default class TwoFactorEnableModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.user = this.attrs.user;
    // Statuses: 'loading', 'displayQR', 'displayBackupCodes', 'final'
    this.status = 'loading';
    this.qrCodeUrl = null;
    this.backupCodes = [];
    this.token = Stream('');
    this.code = null;
    this.activeTab = 'qrcode';
    this.loading = false;
  }

  className() {
    return 'TwoFactorEnableModal Modal--small';
  }

  title() {
    return app.translator.trans('ianm-twofactor.forum.security.two_factor_heading');
  }

  oncreate(vnode) {
    super.oncreate(vnode);

    const userId = this.user.id();
    app
      .request({
        method: 'GET',
        url: app.forum.attribute('apiUrl') + `/users/${userId}/twofactor/qrcode`,
      })
      .then((response) => {
        this.qrCodeUrl = response.svg;
        this.code = response.code;
        this.status = 'displayQR';
        m.redraw();
      });
  }

  onupdate() {
    const tokenInput = document.querySelector('.TwoFactorEnableModal [name=token]');
    if (tokenInput && document.activeElement !== tokenInput) {
      tokenInput.focus();
    }
  }

  content() {
    return (
      <div className="Modal-body">
        {this.status === 'loading' && (
          <div className="loading">
            <LoadingIndicator />
            <p>{app.translator.trans('ianm-twofactor.forum.security.loading_qr')}</p>
          </div>
        )}

        {this.status === 'displayQR' && (
          <div>
            <div className="tabs">
              <Button
                className={this.activeTab === 'qrcode' ? 'active' : ''}
                onclick={() => {
                  this.activeTab = 'qrcode';
                  m.redraw();
                }}
              >
                {app.translator.trans('ianm-twofactor.forum.security.qr_tab')}
              </Button>
              <Button
                className={this.activeTab === 'manual' ? 'active' : ''}
                onclick={() => {
                  this.activeTab = 'manual';
                  m.redraw();
                }}
              >
                {app.translator.trans('ianm-twofactor.forum.security.manual_tab')}
              </Button>
            </div>

            {this.activeTab === 'qrcode' && (
              <div className="qrSection">
                <img className="qrImage" src={this.qrCodeUrl} alt={app.translator.trans('ianm-twofactor.forum.security.qr_code_alt')} />
                <p className="helpText">{app.translator.trans('ianm-twofactor.forum.security.scan_qr_instruction')}</p>
              </div>
            )}

            {this.activeTab === 'manual' && (
              <div className="manualEntrySection">
                <code className="manualEntryCode">{this.code}</code>
                <p className="helpText">{app.translator.trans('ianm-twofactor.forum.security.manual_entry_instruction')}</p>
              </div>
            )}

            <div className="Form">
              <form onsubmit={this.onSubmit.bind(this)}>
                <div className="Form-group">
                  <input
                    className="FormControl"
                    name="token"
                    bidi={this.token}
                    placeholder={app.translator.trans('ianm-twofactor.forum.security.enter_token')}
                  />
                </div>
                <div className="Form-group">
                  <Button type="submit" className="Button Button--primary" onclick={this.verifyToken.bind(this)} loading={this.loading}>
                    {app.translator.trans('ianm-twofactor.forum.security.verify_button')}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {this.status === 'displayBackupCodes' && (
          <div>
            <p>{app.translator.trans('ianm-twofactor.forum.security.backup_codes')}</p>
            <ul>
              {this.backupCodes.map((code) => (
                <li>
                  <code>{code}</code>
                </li>
              ))}
            </ul>
            <p>{app.translator.trans('ianm-twofactor.forum.security.backup_codes_instruction')}</p>
            <Button
              className="Button Button--primary"
              onclick={() => {
                this.status = 'final';
                m.redraw();
              }}
            >
              {app.translator.trans('ianm-twofactor.forum.security.saved_backup_codes_button')}
            </Button>
          </div>
        )}

        {this.status === 'final' && (
          <div>
            <p>{app.translator.trans('ianm-twofactor.forum.security.two_factor_enabled_confirmation')}</p>
            <Button className="Button Button--primary" onclick={this.finish.bind(this)}>
              {app.translator.trans('ianm-twofactor.forum.security.ok_button')}
            </Button>
          </div>
        )}
      </div>
    );
  }

  verifyToken() {
    this.loading = true;

    app
      .request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/users/twofactor/verify',
        body: {
          token: this.token(),
        },
      })
      .then((response) => {
        this.backupCodes = response.backupCodes;
        this.status = 'displayBackupCodes';
        this.user.twoFactorEnabled(true);
        m.redraw();
      })
      .catch((error) => {
        //alert('Verification failed. Please try again.');
        //error.alert.content = 'Verification failed. Please try again.';
      })
      .finally(() => {
        this.loading = false;
      });
  }

  finish() {
    this.attrs.onEnabled();
    this.hide();
  }

  onSubmit(e) {
    e.preventDefault();
    this.verifyToken();
  }
}
