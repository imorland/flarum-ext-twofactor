import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import SettingsPage from './components/SettingsPage';

export default [
  ...commonExtend,

  new Extend.Admin() //
    .page(SettingsPage)
    .permission(
      () => ({
        icon: 'fas fa-shield-alt',
        label: app.translator.trans('ianm-twofactor.admin.permissions.see_two_factor_status_label'),
        permission: 'ianm-twofactor.seeTwoFactorStatus',
      }),
      'moderate',
      65
    )
    .permission(
      () => ({
        icon: 'fas fa-shield-alt',
        label: app.translator.trans('ianm-twofactor.admin.permissions.manage_others_label'),
        permission: 'ianm-twofactor.manageOthers',
      }),
      'moderate',
      60
    ),
];
