import app from 'flarum/forum/app';
import TwoFactorEnableModal from './components/TwoFactorEnableModal';

export default function checkForTwoFactor() {
  return new Promise(() => {
    setTimeout(() => {
      if (app.session.user?.mustEnable2FA()) {
        app.modal.show(TwoFactorEnableModal, { user: app.session.user, forced: true });
      }
    }, 300);
  });
}
