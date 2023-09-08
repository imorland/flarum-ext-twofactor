import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';

export default class TwoFactorDisableConfirmModal extends Modal {
  oninit(vnode) {
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
    return (
      <div className="Modal-body">
        <p>{app.translator.trans('ianm-twofactor.forum.security.confirm_disable_2fa_text')}</p>
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
