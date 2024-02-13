import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import UserSecurityPage from 'flarum/forum/components/UserSecurityPage';
import TwoFactorSettings from './components/TwoFactorSettings';
import FieldSet from 'flarum/common/components/FieldSet';
import LinkButton from 'flarum/common/components/LinkButton';

export default function extendUserSecurityPage() {
  extend(UserSecurityPage.prototype, 'settingsItems', function (items) {
    items.add(
      'twoFactor',
      <FieldSet label={app.translator.trans('ianm-twofactor.forum.security.two_factor_heading')}>
        <p className="helpText">{app.translator.trans('ianm-twofactor.forum.security.two_factor_help')}</p>
        <p className="helpText">
          {app.translator.trans('ianm-twofactor.forum.security.two_factor_apps', {
            google: (
              <LinkButton
                external={true}
                href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                target="_blank"
                rel="noopener noreferrer"
              >
                Google Authenticator
              </LinkButton>
            ),
            microsoft: (
              <LinkButton external={true} href="https://www.microsoft.com/en-us/account/authenticator" target="_blank" rel="noopener noreferrer">
                Microsoft Authenticator
              </LinkButton>
            ),
            authy: (
              <LinkButton external={true} href="https://authy.com/download/" target="_blank" rel="noopener noreferrer">
                Authy
              </LinkButton>
            ),
          })}
        </p>
        <TwoFactorSettings user={this.user} />
      </FieldSet>,
      100
    );
  });
}
