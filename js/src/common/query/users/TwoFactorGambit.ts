import app from 'flarum/common/app';
import { BooleanGambit } from 'flarum/common/query/IGambit';

export default class TwoFactorGambit extends BooleanGambit {
  key(): string {
    return app.translator.trans('ianm-twofactor.lib.gambits.users.twofactor.key', {}, true);
  }

  canonicalKey(): string {
    return '2fa';
  }

  filterKey(): string {
    return 'twofactor';
  }

  enabled(): boolean {
    return !!app.session.user && !!app.forum.attribute<boolean>('canSeeTwoFactorStatus');
  }
}
