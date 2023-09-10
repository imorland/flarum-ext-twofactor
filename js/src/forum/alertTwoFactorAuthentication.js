import Alert from 'flarum/common/components/Alert';
import Button from 'flarum/common/components/Button';
import icon from 'flarum/common/helpers/icon';
import Component from 'flarum/common/Component';
import TwoFactorEnableModal from './components/TwoFactorEnableModal';

/**
 * Shows an alert if the user has not enabled 2FA.
 *
 * @param {import('../ForumApplication').default} app
 */
export default function alertTwoFactorAuthentication(app) {
  const user = app.session.user;

  if (!user || !user.mustEnable2FA()) return;

  class Enable2FAButton extends Component {
    view() {
      return (
        <Button className="Button Button--link" onclick={this.onclick.bind(this)} icon="fas fa-shield-alt">
          {app.translator.trans('ianm-twofactor.forum.security.enable_2fa_button')}
        </Button>
      );
    }

    onclick() {
      app.modal.show(TwoFactorEnableModal, { onEnabled: this.onTwoFactorEnabled.bind(this), user });
    }

    onTwoFactorEnabled() {
      user.mustEnable2FA(false);
      m.redraw();

      // Unmount the ContainedAlert
      m.mount(alertContainer, null);
    }
  }

  class ContainedAlert extends Alert {
    view(vnode) {
      const vdom = super.view(vnode);
      return { ...vdom, children: [<div className="container">{vdom.children}</div>] };
    }
  }

  const alertContainer = $('<div className="App-notices"/>').insertBefore('#content')[0];

  m.mount(alertContainer, {
    view: () => (
      <ContainedAlert dismissible={false} controls={[<Enable2FAButton />]} className="Alert--2faEnable">
        {app.translator.trans('ianm-twofactor.forum.user_2fa.alert_message')}
      </ContainedAlert>
    ),
  });
}
