import Extend from 'flarum/common/extenders';
import Group from 'flarum/common/models/Group';
import User from 'flarum/common/models/User';
import TwoFactor from './models/TwoFactor';

export default [
  new Extend.Store() //
    .add('two-factor', TwoFactor),

  new Extend.Model(User) //
    .attribute<boolean>('twoFactorEnabled')
    .attribute<boolean>('canDisable2FA')
    .attribute<boolean>('mustEnable2FA')
    .attribute<number>('backupCodesRemaining'),

  new Extend.Model(Group) //
    .attribute<boolean>('requires2FA'),
];
