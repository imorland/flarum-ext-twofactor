/// <reference types="flarum/@types/translator-icu-rich" />
import Modal from 'flarum/common/components/Modal';
import User from 'flarum/common/models/User';
import { ILoginModalAttrs } from 'flarum/forum/components/LogInModal';
import type Mithril from 'mithril';
export interface TwoFactorDisableConfirmModalAttrs extends ILoginModalAttrs {
    user: User;
    onDisabled: () => void;
}
export default class TwoFactorDisableConfirmModal extends Modal<TwoFactorDisableConfirmModalAttrs> {
    oninit(vnode: Mithril.Vnode<TwoFactorDisableConfirmModalAttrs, this>): void;
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    disable(): void;
}
