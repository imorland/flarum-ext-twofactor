import app from 'flarum/admin/app';
import extendUserListPage from './extendUserListPage';
import extendEditGroupModal from './extendEditGroupModal';
import SettingsPage from './components/SettingsPage';

export { default as extend } from './extend';

app.initializers.add('ianm/twofactor', () => {
  app.extensionData
    .for('ianm-twofactor')
    .registerPage(SettingsPage)
    .registerPermission(
      {
        icon: 'fas fa-shield-alt',
        label: app.translator.trans('ianm-twofactor.admin.permissions.see_two_factor_status_label'),
        permission: 'ianm-twofactor.seeTwoFactorStatus',
      },
      'moderate',
      65
    )
    .registerPermission(
      {
        icon: 'fas fa-shield-alt',
        label: app.translator.trans('ianm-twofactor.admin.permissions.manage_others_label'),
        permission: 'ianm-twofactor.manageOthers',
      },
      'moderate',
      60
    );

  extendUserListPage();
  extendEditGroupModal();
});
