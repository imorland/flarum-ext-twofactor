import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import ItemList from 'flarum/common/utils/ItemList';
import listItems from 'flarum/common/helpers/listItems';
import TwoFactorGridItem from './TwoFactorGridItem';
import Tooltip from 'flarum/common/components/Tooltip';
import TwoFactorEnableModal from './TwoFactorEnableModal';
import TwoFactorDisableConfirmModal from './TwoFactorDisableConfirmModal ';
import TwoFactorChangeDeviceModal from './TwoFactorChangeDeviceModal';
import type Mithril from 'mithril';
import User from 'flarum/common/models/User';

export interface TwoFactorGridAttrs extends ComponentAttrs {
  user: User;
}

export default class TwoFactorGrid extends Component<TwoFactorGridAttrs> {
  user!: User;
  twoFactorEnabled!: boolean;
  canDisableTwoFactor!: boolean;
  backupCodesRemaining!: number;

  oninit(vnode: Mithril.Vnode<ComponentAttrs, this>) {
    super.oninit(vnode);

    this.user = this.attrs.user;
    this.twoFactorEnabled = this.user.twoFactorEnabled();
    this.canDisableTwoFactor = this.user.canDisable2FA();
    this.backupCodesRemaining = this.user.backupCodesRemaining() || 0;
  }

  view() {
    return (
      <div className="TwoFactorGrid">
        <ul>{listItems(this.twoFactorItems().toArray())}</ul>
      </div>
    );
  }

  twoFactorItems() {
    const items = new ItemList();

    const disableAction = this.getDisableAction();
    const enableAction = (
      <Button className="Button Button--primary" onclick={this.enableTwoFactor.bind(this)}>
        {app.translator.trans('ianm-twofactor.forum.security.enable_2fa_button')}
      </Button>
    );

    items.add(
      'status',
      <TwoFactorGridItem
        icon="fas fa-shield-alt"
        title={app.translator.trans('ianm-twofactor.forum.security.two_factor_title')}
        value={
          this.twoFactorEnabled
            ? app.translator.trans('ianm-twofactor.forum.security.two_factor_enabled')
            : app.translator.trans('ianm-twofactor.forum.security.two_factor_disabled')
        }
        action={this.twoFactorEnabled ? disableAction : enableAction}
        helpText={!this.canDisableTwoFactor && app.translator.trans('ianm-twofactor.forum.security.cannot_disable')}
      />
    );

    // Only continue to add other items if Two Factor Authentication is enabled
    if (!this.twoFactorEnabled) return items;

    // Add change device option
    items.add(
      'changeDevice',
      <TwoFactorGridItem
        icon="fas fa-mobile-alt"
        title={app.translator.trans('ianm-twofactor.forum.security.change_device_title')}
        value={app.translator.trans('ianm-twofactor.forum.security.change_device_description')}
        action={
          <Button className="Button" onclick={this.changeDevice.bind(this)}>
            {app.translator.trans('ianm-twofactor.forum.security.change_device_button')}
          </Button>
        }
      />
    );

    items.add(
      'backupCodes',
      <TwoFactorGridItem
        icon="fas fa-key"
        title={app.translator.trans('ianm-twofactor.forum.security.backup_codes_remaining')}
        value={this.backupCodesRemaining}
        // action={
        //   this.backupCodesRemaining < 2 ? (
        //     <Button className="Button Button--primary" onclick={this.generateBackupCodes.bind(this)}>
        //       {app.translator.trans('ianm-twofactor.forum.security.generate_backup_codes_button')}
        //     </Button>
        //   ) : null
        // }
      />
    );

    // Add other items as needed

    return items;
  }

  getDisableAction() {
    const disableButton = (
      <Button className="Button Button--danger" onclick={this.disableTwoFactor.bind(this)} disabled={!this.canDisableTwoFactor}>
        {app.translator.trans('ianm-twofactor.forum.security.disable_2fa_button')}
      </Button>
    );

    return !this.canDisableTwoFactor ? (
      <Tooltip text={app.translator.trans('ianm-twofactor.forum.security.cannot_disable_tooltip')}>{disableButton}</Tooltip>
    ) : (
      disableButton
    );
  }

  enableTwoFactor() {
    app.modal.show(TwoFactorEnableModal, { onEnabled: this.onTwoFactorEnabled.bind(this), user: this.user });
  }

  onTwoFactorEnabled() {
    this.twoFactorEnabled = true;
    m.redraw();
  }

  disableTwoFactor() {
    app.modal.show(TwoFactorDisableConfirmModal, { onDisabled: this.onTwoFactorDisabled.bind(this), user: this.user });
  }

  onTwoFactorDisabled() {
    this.twoFactorEnabled = false;
    m.redraw();
  }

  changeDevice() {
    app.modal.show(TwoFactorChangeDeviceModal, { 
      user: this.user,
      onDeviceChanged: this.onDeviceChanged.bind(this)
    });
  }

  onDeviceChanged() {
    // Refresh backup codes count if needed
    this.backupCodesRemaining = this.user.backupCodesRemaining() || 0;
    m.redraw();
  }

  generateBackupCodes() {
    // Logic to generate more backup codes
    // Update this.backupCodesRemaining accordingly
    m.redraw();
  }
}
