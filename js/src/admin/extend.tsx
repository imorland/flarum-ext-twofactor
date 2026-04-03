import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import GroupBar from 'flarum/admin/components/GroupBar';
import Group from 'flarum/common/models/Group';
import UploadImageButton from 'flarum/common/components/UploadImageButton';
import extractText from 'flarum/common/utils/extractText';

const groupsTitle = 'ianm-twofactor.admin.settings.groups.title';
const groupsHelp = 'ianm-twofactor.admin.settings.groups.help';
const logoQr = 'ianm-twofactor.admin.settings.logo_qr';
const logoQrHelp = 'ianm-twofactor.admin.settings.logo_qr_help';

export default [
  ...commonExtend,

  new Extend.Admin()
    .customSetting(
      () => (
        <div className="Form-group">
          <h3>{app.translator.trans(groupsTitle)}</h3>
          <p className="helpText">{app.translator.trans(groupsHelp)}</p>
          <GroupBar groups={app.store.all<Group>('groups').filter((group) => ![Group.GUEST_ID, Group.MEMBER_ID].includes(group.id()!))} />
        </div>
      ),
      100
    )
    .setting(
      () => ({
        setting: 'ianm-twofactor.admin.settings.forum_logo_qr',
        type: 'boolean',
        label: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr'),
        help: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr_help'),
      }),
      90
    )
    .customSetting(
      () => (
        <div className="Form-group">
          <label>{app.translator.trans(logoQr)}</label>
          <div className="helpText">{app.translator.trans(logoQrHelp)}</div>
          <UploadImageButton
            name="ianm_twofactor_logo"
            routePath="ianm_twofactor_logo"
            value={app.data.settings['ianm_twofactor_logo_path']}
            url={app.forum.attribute('ianm_twofactor_logoUrl')}
          />
        </div>
      ),
      80
    )
    .setting(
      () => ({
        setting: 'ianm-twofactor.admin.settings.forum_logo_qr_width',
        type: 'number',
        label: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr_width'),
        help: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr_width_help'),
        max: 200,
      }),
      70
    )
    .customSetting(
      () => (
        <>
          <h3>{app.translator.trans('ianm-twofactor.admin.settings.tokens.heading')}</h3>
          <p className="helpText">{app.translator.trans('ianm-twofactor.admin.settings.tokens.help')}</p>
        </>
      ),
      60
    )
    .setting(
      () => ({
        setting: 'ianm-twofactor.kill_inactive_tokens',
        type: 'boolean',
        label: app.translator.trans('ianm-twofactor.admin.settings.tokens.kill_inactive_tokens'),
        help: app.translator.trans('ianm-twofactor.admin.settings.tokens.kill_inactive_tokens_help'),
      }),
      50
    )
    .setting(
      () => ({
        setting: 'ianm-twofactor.kill_inactive_tokens_age_days',
        type: 'number',
        min: 1,
        label: app.translator.trans('ianm-twofactor.admin.settings.tokens.kill_inactive_tokens_age_days'),
        help: app.translator.trans('ianm-twofactor.admin.settings.tokens.kill_inactive_tokens_age_days_help'),
      }),
      40
    )
    .setting(
      () => ({
        setting: 'ianm-twofactor.also_kill_developer_tokens',
        type: 'boolean',
        label: app.translator.trans('ianm-twofactor.admin.settings.tokens.also_kill_developer_tokens'),
        help: app.translator.trans('ianm-twofactor.admin.settings.tokens.also_kill_developer_tokens_help'),
      }),
      30
    )
    .generalIndexItems('settings', () => [
      {
        id: 'ianm_twofactor_required_groups',
        label: extractText(app.translator.trans(groupsTitle)),
        help: extractText(app.translator.trans(groupsHelp)),
      },
      {
        id: 'ianm_twofactor_logo',
        label: extractText(app.translator.trans(logoQr)),
        help: extractText(app.translator.trans(logoQrHelp)),
      },
    ])
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
