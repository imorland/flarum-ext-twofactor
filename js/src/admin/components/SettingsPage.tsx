import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import UploadImageButton from 'flarum/admin/components/UploadImageButton';
import ExtractedGroupBar from './ExtractedGroupBar';

export default class SettingsPage extends ExtensionPage {
  content() {
    console.log(app.forum);

    return (
      <div className="container">
        <div className="TwoFactorSettingsPage">
          <div className="Form">
            <div className="Form-group">
              <h3>{app.translator.trans('ianm-twofactor.admin.settings.groups.title')}</h3>
              <p className="helpText">{app.translator.trans('ianm-twofactor.admin.settings.groups.help')}</p>
              <ExtractedGroupBar />
            </div>
            {this.buildSettingComponent({
              setting: 'ianm-twofactor.admin.settings.forum_logo_qr',
              type: 'boolean',
              label: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr'),
              help: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr_help'),
            })}
            <div className="Form-group">
              <label>{app.translator.trans('ianm-twofactor.admin.settings.logo_qr')}</label>
              <div className="helpText">{app.translator.trans('ianm-twofactor.admin.settings.logo_qr_help')}</div>
              <UploadImageButton name="ianm_twofactor_logo" />
            </div>
            {this.buildSettingComponent({
              setting: 'ianm-twofactor.admin.settings.forum_logo_qr_width',
              type: 'number',
              label: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr_width'),
              help: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr_width_help'),
              max: 200,
            })}
            {this.submitButton()}
          </div>
        </div>
      </div>
    );
  }
}
