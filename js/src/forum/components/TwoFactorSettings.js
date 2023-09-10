import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
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
      <div>
        <TwoFactorGrid user={this.attrs.user} />
      </div>
    );
  }
}
