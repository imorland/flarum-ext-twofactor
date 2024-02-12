import app from 'flarum/forum/app';
import extendUserSecurityPage from './extendUserSecurityPage';
import extendLogInModal from './extendLogInModal';
import checkForTwoFactor from './checkForTwoFactor';

export { default as extend } from './extend';

app.initializers.add('ianm/twofactor', () => {
  extendUserSecurityPage();
  extendLogInModal();
  checkForTwoFactor();
});
