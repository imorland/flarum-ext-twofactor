import app from 'flarum/forum/app';
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Stream from 'flarum/common/utils/Stream';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';

export interface TwoFactorChangeDeviceModalAttrs extends IInternalModalAttrs {
  user: User;
  onDeviceChanged?: () => void;
}

export default class TwoFactorChangeDeviceModal extends Modal<TwoFactorChangeDeviceModalAttrs> {
  user!: User;
  // Statuses: 'verifyCurrentDevice', 'loadingQR', 'displayQR', 'displayBackupCodes', 'final'
  status: string = 'verifyCurrentDevice';
  qrCodeUrl: string | null = null;
  backupCodes: Array<string> = [];
  currentToken: Stream<string>;
  newToken: Stream<string>;
  code: string | null = null;
  activeTab: string = 'qrcode';
  loading: boolean = false;

  oninit(vnode: Mithril.Vnode<TwoFactorChangeDeviceModalAttrs>) {
    super.oninit(vnode);

    this.user = this.attrs.user;
    this.currentToken = Stream('');
    this.newToken = Stream('');
  }

  className() {
    return 'TwoFactorChangeDeviceModal Modal--small';
  }

  title() {
    return app.translator.trans('ianm-twofactor.forum.security.change_device_heading');
  }

  onupdate() {
    if (this.status === 'verifyCurrentDevice') {
      const tokenInput = document.querySelector('.TwoFactorChangeDeviceModal [name=currentToken]') as HTMLInputElement;
      if (tokenInput && document.activeElement !== tokenInput) {
        tokenInput.focus();
      }
    } else if (this.status === 'displayQR') {
      const tokenInput = document.querySelector('.TwoFactorChangeDeviceModal [name=newToken]') as HTMLInputElement;
      if (tokenInput && document.activeElement !== tokenInput) {
        tokenInput.focus();
      }
    }
  }

  content() {
    return (
      <div className="Modal-body">
        {this.status === 'verifyCurrentDevice' && this.renderVerifyCurrentDevice()}
        {this.status === 'loadingQR' && this.renderLoadingQR()}
        {this.status === 'displayQR' && this.renderDisplayQR()}
        {this.status === 'displayBackupCodes' && this.renderBackupCodes()}
        {this.status === 'final' && this.renderFinal()}
      </div>
    );
  }

  renderVerifyCurrentDevice() {
    return (
      <div>
        <p>{app.translator.trans('ianm-twofactor.forum.security.verify_current_device_message')}</p>
        <div className="Form">
          <form onsubmit={this.onVerifyCurrentDeviceSubmit.bind(this)}>
            <div className="Form-group">
              <input
                type="text"
                className="FormControl"
                name="currentToken"
                bidi={this.currentToken}
                placeholder={app.translator.trans('ianm-twofactor.forum.security.enter_current_token')}
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="one-time-code"
              />
            </div>
            <div className="Form-group">
              <Button 
                type="submit" 
                className="Button Button--primary" 
                loading={this.loading}
              >
                {app.translator.trans('ianm-twofactor.forum.security.verify_button')}
              </Button>
            </div>
          </form>
        </div>
      </div>
    );
  }

  renderLoadingQR() {
    return (
      <div className="loading">
        <LoadingIndicator />
        <p>{app.translator.trans('ianm-twofactor.forum.security.loading_qr')}</p>
      </div>
    );
  }

  renderDisplayQR() {
    return (
      <div>
        <p>{app.translator.trans('ianm-twofactor.forum.security.scan_new_device_qr')}</p>
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
          </div>
        )}

        {this.activeTab === 'manual' && (
          <div className="manualEntrySection">
            <code className="manualEntryCode">{this.code}</code>
            <p className="helpText">{app.translator.trans('ianm-twofactor.forum.security.manual_entry_instruction')}</p>
          </div>
        )}

        <p>{app.translator.trans('ianm-twofactor.forum.security.verify_new_device_message')}</p>
        <div className="Form">
          <form onsubmit={this.onVerifyNewDeviceSubmit.bind(this)}>
            <div className="Form-group">
              <input
                type="text"
                className="FormControl"
                name="newToken"
                bidi={this.newToken}
                placeholder={app.translator.trans('ianm-twofactor.forum.security.enter_new_token')}
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="one-time-code"
              />
            </div>
            <div className="Form-group">
              <Button 
                type="submit" 
                className="Button Button--primary" 
                loading={this.loading}
              >
                {app.translator.trans('ianm-twofactor.forum.security.verify_button')}
              </Button>
            </div>
          </form>
        </div>
      </div>
    );
  }

  renderBackupCodes() {
    return (
      <div>
        <p>{app.translator.trans('ianm-twofactor.forum.security.new_backup_codes')}</p>
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
    );
  }

  renderFinal() {
    return (
      <div>
        <p>{app.translator.trans('ianm-twofactor.forum.security.device_changed_confirmation')}</p>
        <Button className="Button Button--primary" onclick={this.finish.bind(this)}>
          {app.translator.trans('ianm-twofactor.forum.security.continue_button')}
        </Button>
      </div>
    );
  }

  onVerifyCurrentDeviceSubmit(e: Event) {
    e.preventDefault();
    this.verifyCurrentDevice();
  }

  verifyCurrentDevice() {
    this.loading = true;

    app
      .request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/users/twofactor/verify-current',
        body: {
          token: this.currentToken(),
        },
      })
      .then(() => {
        this.status = 'loadingQR';
        m.redraw();
        this.loadQrCode();
      })
      .catch((error) => {
        this.loading = false;
        m.redraw();
      });
  }

  loadQrCode() {
    const userId = this.user.id();
    app
      .request({
        method: 'GET',
        url: app.forum.attribute('apiUrl') + `/users/${userId}/twofactor/change-device/qrcode`,
      })
      .then((response: any) => {
        this.qrCodeUrl = response.svg;
        this.code = response.code;
        this.status = 'displayQR';
        this.loading = false;
        m.redraw();
      })
      .catch(() => {
        this.loading = false;
        m.redraw();
      });
  }

  onVerifyNewDeviceSubmit(e: Event) {
    e.preventDefault();
    this.verifyNewDevice();
  }

  verifyNewDevice() {
    this.loading = true;

    app
      .request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/users/twofactor/change-device/verify',
        body: {
          token: this.newToken(),
        },
      })
      .then((response: any) => {
        const { backupCodes } = response as { backupCodes: string[] };
        this.backupCodes = backupCodes;
        this.status = 'displayBackupCodes';
        this.loading = false;
        m.redraw();
      })
      .catch(() => {
        this.loading = false;
        m.redraw();
      });
  }

  finish() {
    this.attrs.onDeviceChanged?.();
    this.hide();
  }
}
