import app from 'flarum/admin/app';
import extendUserListPage from './extendUserListPage';
import extendEditGroupModal from './extendEditGroupModal';

export { default as extend } from './extend';

app.initializers.add('ianm/twofactor', () => {
  extendUserListPage();
  extendEditGroupModal();
});
