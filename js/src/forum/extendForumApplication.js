import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import ForumApplication from 'flarum/forum/ForumApplication';
import alertTwoFactorAuthentication from './alertTwoFactorAuthentication';

export default function extendForumApplication() {
  extend(ForumApplication.prototype, 'mount', function () {
    alertTwoFactorAuthentication(app);
  });
}
