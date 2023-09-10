import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import ExtractedGroupBar from './ExtractedGroupBar';

export default class SettingsPage extends ExtensionPage {
  content() {
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
              help: app.translator.trans('ianm-twofactor.admin.settings.forum_logo_qr_help')
            })}
            {this.submitButton()}
          </div>
        </div>
      </div>
    );
  }
}
