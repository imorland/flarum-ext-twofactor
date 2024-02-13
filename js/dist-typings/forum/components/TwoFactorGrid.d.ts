import Component, { ComponentAttrs } from 'flarum/common/Component';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
import User from 'flarum/common/models/User';
export interface TwoFactorGridAttrs extends ComponentAttrs {
    user: User;
}
export default class TwoFactorGrid extends Component<TwoFactorGridAttrs> {
    user: User;
    twoFactorEnabled: boolean;
    canDisableTwoFactor: boolean;
    backupCodesRemaining: number;
    oninit(vnode: Mithril.Vnode<ComponentAttrs, this>): void;
    view(): JSX.Element;
    twoFactorItems(): ItemList<unknown>;
    getDisableAction(): JSX.Element;
    enableTwoFactor(): void;
    onTwoFactorEnabled(): void;
    disableTwoFactor(): void;
    onTwoFactorDisabled(): void;
    generateBackupCodes(): void;
}
