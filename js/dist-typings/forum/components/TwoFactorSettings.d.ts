import Component, { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
import User from 'flarum/common/models/User';
export interface TwoFactorSettingsAttrs extends ComponentAttrs {
    user: User;
}
export default class TwoFactorSettings extends Component<TwoFactorSettingsAttrs> {
    oninit(vnode: Mithril.Vnode<ComponentAttrs, this>): void;
    view(): JSX.Element;
}
