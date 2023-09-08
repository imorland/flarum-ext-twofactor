import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import TwoFactorEnableModal from './TwoFactorEnableModal';
import TwoFactorDisableConfirmModal from './TwoFactorDisableConfirmModal ';
import TwoFactorGrid from './TwoFactorGrid';

export default class TwoFactorSettings extends Component {
  oninit(vnode) {
    super.oninit(vnode);

    this.twoFactorEnabled = this.attrs.user.twoFactorEnabled();
    this.canDisableTwoFactor = this.attrs.user.canDisable2FA();
    this.loading = false;
  }

  view() {
    return (
      // <div className="TwoFactorSettings">
      //   <div className="Form-group">
      //     {this.twoFactorEnabled ? (
      //       <div>
      //         <p>{app.translator.trans('ianm-twofactor.forum.security.two_factor_enabled')}</p>
      //         <Button className="Button Button--danger" onclick={this.disableTwoFactor.bind(this)} disabled={!this.canDisableTwoFactor}>
      //           {app.translator.trans('ianm-twofactor.forum.security.disable_2fa_button')}
      //         </Button>
      //         {!this.canDisableTwoFactor && <p className="helpText">{app.translator.trans('ianm-twofactor.forum.security.cannot_disable')}</p>}
      //       </div>
      //     ) : (
      //       <div>
      //         <p>{app.translator.trans('ianm-twofactor.forum.security.two_factor_disabled')}</p>
      //         <Button className="Button Button--primary Button--2fa" onclick={this.enableTwoFactor.bind(this)} icon="fas fa-shield-alt">
      //           {app.translator.trans('ianm-twofactor.forum.security.enable_2fa_button')}
      //         </Button>
      //       </div>
      //     )}
      //   </div>
      // </div>,
      <div>
        <TwoFactorGrid user={this.attrs.user} />
      </div>
    );
  }
}
