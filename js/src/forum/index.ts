import app from 'flarum/forum/app';
import extendUserSecurityPage from './extendUserSecurityPage';
import extendLogInModal from './extendLogInModal';
import extendForumApplication from './extendForumApplication';

export { default as extend } from './extend';

app.initializers.add('ianm/twofactor', () => {
  extendUserSecurityPage();
  extendLogInModal();
  extendForumApplication();
});
