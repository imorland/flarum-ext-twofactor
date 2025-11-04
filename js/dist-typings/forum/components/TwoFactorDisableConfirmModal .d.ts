import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';
export interface TwoFactorDisableConfirmModalAttrs extends IInternalModalAttrs {
    user: User;
    onDisabled: () => void;
}
export default class TwoFactorDisableConfirmModal extends Modal<TwoFactorDisableConfirmModalAttrs> {
    oninit(vnode: Mithril.Vnode<TwoFactorDisableConfirmModalAttrs, this>): void;
    className(): string;
    title(): string | any[];
    content(): JSX.Element;
    disable(): void;
}
