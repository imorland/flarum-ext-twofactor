import app from 'flarum/forum/app';
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';
import username from 'flarum/common/helpers/username';
import Stream from 'flarum/common/utils/Stream';

export interface TwoFactorDisableConfirmModalAttrs extends IInternalModalAttrs {
  user: User;
  onDisabled: () => void;
}

export default class TwoFactorDisableConfirmModal extends Modal<TwoFactorDisableConfirmModalAttrs> {
  oninit(vnode: Mithril.Vnode<TwoFactorDisableConfirmModalAttrs, this>) {
    super.oninit(vnode);

    this.loading = false;
    this.token = Stream('');
  }

  token!: Stream<string>;

  isSelf(): boolean {
    return app.session.user?.id() === this.attrs.user.id();
  }

  className() {
    return 'TwoFactorDisableConfirmModal Modal--small';
  }

  title() {
    return app.translator.trans('ianm-twofactor.forum.security.confirm_disable_2fa_title');
  }

  content() {
    const isSelf = this.isSelf();
    const thisUser = this.attrs.user;
    return (
      <div className="Modal-body">
        <p>
          {app.translator.trans(
            isSelf ? 'ianm-twofactor.forum.security.confirm_disable_2fa_text' : 'ianm-twofactor.forum.security.confirm_disable_2fa_text_other_user',
            { username: username(thisUser) }
          )}
        </p>
        <form
          onsubmit={(e: SubmitEvent) => {
            e.preventDefault();
            this.disable();
          }}
        >
          {isSelf && (
            <div className="Form-group">
              <input
                className="FormControl"
                name="token"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder={app.translator.trans('ianm-twofactor.forum.security.enter_current_token')}
                bidi={this.token}
                disabled={this.loading}
              />
            </div>
          )}
          <div className="Form-group">
            <Button type="submit" className="Button Button--danger" loading={this.loading} disabled={this.loading}>
              {app.translator.trans('ianm-twofactor.forum.security.disable_2fa_button')}
            </Button>
            <Button type="button" className="Button Button--cancel" onclick={this.hide.bind(this)}>
              {app.translator.trans('ianm-twofactor.forum.security.cancel_button')}
            </Button>
          </div>
        </form>
      </div>
    );
  }

  disable() {
    if (this.loading) return;

    this.loading = true;
    const userId = this.attrs.user.id();

    app
      .request({
        method: 'DELETE',
        url: app.forum.attribute('apiUrl') + `/users/${userId}/twofactor/disable`,
        body: this.isSelf() ? { token: this.token() } : undefined,
      })
      .then(() => {
        this.loading = false;
        this.attrs.onDisabled();
        this.hide();
      })
      .catch(() => {
        this.loading = false;
        this.token('');
        m.redraw();
      });
  }
}
