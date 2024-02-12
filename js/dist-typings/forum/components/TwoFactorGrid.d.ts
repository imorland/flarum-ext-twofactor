export default class TwoFactorGrid extends Component<any, undefined> {
    constructor();
    oninit(vnode: any): void;
    user: any;
    twoFactorEnabled: any;
    canDisableTwoFactor: any;
    backupCodesRemaining: any;
    view(): JSX.Element;
    twoFactorItems(): ItemList<any>;
    getDisableAction(): JSX.Element;
    enableTwoFactor(): void;
    onTwoFactorEnabled(): void;
    disableTwoFactor(): void;
    onTwoFactorDisabled(): void;
    generateBackupCodes(): void;
}
import Component from "flarum/common/Component";
import ItemList from "flarum/common/utils/ItemList";
