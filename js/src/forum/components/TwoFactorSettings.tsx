import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import TwoFactorGrid from './TwoFactorGrid';
import type Mithril from 'mithril';
import User from 'flarum/common/models/User';

export interface TwoFactorSettingsAttrs extends ComponentAttrs {
  user: User;
}

export default class TwoFactorSettings extends Component<TwoFactorSettingsAttrs> {
  oninit(vnode: Mithril.Vnode<ComponentAttrs, this>) {
    super.oninit(vnode);

    // this.twoFactorEnabled = this.attrs.user.twoFactorEnabled();
    // this.canDisableTwoFactor = this.attrs.user.canDisable2FA();
    // this.loading = false;
  }

  view() {
    return (
      <div>
        <TwoFactorGrid user={this.attrs.user} />
      </div>
    );
  }
}
