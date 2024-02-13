import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import User from 'flarum/common/models/User';
import { ILoginModalAttrs } from 'flarum/forum/components/LogInModal';
import type Mithril from 'mithril';
import username from 'flarum/common/helpers/username';

export interface TwoFactorDisableConfirmModalAttrs extends ILoginModalAttrs {
  user: User;
  onDisabled: () => void;
}

export default class TwoFactorDisableConfirmModal extends Modal<TwoFactorDisableConfirmModalAttrs> {
  oninit(vnode: Mithril.Vnode<TwoFactorDisableConfirmModalAttrs, this>) {
    super.oninit(vnode);

    this.loading = false;
  }

  className() {
    return 'TwoFactorDisableConfirmModal Modal--small';
  }

  title() {
    return app.translator.trans('ianm-twofactor.forum.security.confirm_disable_2fa_title');
  }

  content() {
    const isSelf = app.session.user?.id() === this.attrs.user.id();
    const thisUser = this.attrs.user;
    return (
      <div className="Modal-body">
        <p>
          {app.translator.trans(
            isSelf ? 'ianm-twofactor.forum.security.confirm_disable_2fa_text' : 'ianm-twofactor.forum.security.confirm_disable_2fa_text_other_user',
            { username: username(thisUser) }
          )}
        </p>
        <div className="Form-group">
          <Button className="Button Button--danger" onclick={this.disable.bind(this)} loading={this.loading}>
            {app.translator.trans('ianm-twofactor.forum.security.disable_2fa_button')}
          </Button>
          <Button className="Button Button--cancel" onclick={this.hide.bind(this)}>
            {app.translator.trans('ianm-twofactor.forum.security.cancel_button')}
          </Button>
        </div>
      </div>
    );
  }

  disable() {
    this.loading = true;
    const userId = this.attrs.user.id();

    app
      .request({
        method: 'DELETE',
        url: app.forum.attribute('apiUrl') + `/users/${userId}/twofactor/disable`,
      })
      .then(() => {
        this.loading = false;
        this.attrs.onDisabled();
        this.hide();
      })
      .catch((error) => {
        // Handle any errors.
      });
  }
}
