import Component, { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
export interface TwoFactorGridItemAttrs extends ComponentAttrs {
    icon: string;
    title: string;
    value: string;
    action?: Mithril.Children;
    helpText?: string;
}
export default class TwoFactorGridItem extends Component<TwoFactorGridItemAttrs> {
    view(): JSX.Element;
}
