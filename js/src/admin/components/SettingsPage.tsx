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
            {this.submitButton()}
          </div>
        </div>
      </div>
    );
  }
}
