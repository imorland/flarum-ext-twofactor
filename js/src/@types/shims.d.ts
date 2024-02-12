import User from 'flarum/common/models/User';

declare module 'flarum/common/models/User' {
  export default interface User {
    mustEnable2FA: () => boolean;
    twoFactorEnabled: () => boolean;
    canDisable2FA: () => boolean;
    backupCodesRemaining: () => number;
  }
}
