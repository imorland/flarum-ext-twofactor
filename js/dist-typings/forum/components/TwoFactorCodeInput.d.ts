import Component from 'flarum/common/Component';
import type Mithril from 'mithril';
export interface TwoFactorCodeInputAttrs {
    placeholder?: string;
    initial?: string;
    /**
     * Called with the entered code when either 6 digits are entered or when the user presses enter.
     */
    onComplete: (code: string) => void;
}
export default class TwoFactorCodeInput extends Component<TwoFactorCodeInputAttrs> {
    value: string;
    oninit(vnode: Mithril.Vnode<TwoFactorCodeInputAttrs>): void;
    oninput(e: Event): void;
    onkeydown(e: KeyboardEvent): void;
    view(): Mithril.Children;
}
