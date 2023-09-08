/// <reference types="flarum/@types/translator-icu-rich" />
export default class TwoFactorDisableConfirmModal extends Modal<import("flarum/common/components/Modal").IInternalModalAttrs, undefined> {
    constructor();
    oninit(vnode: any): void;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    disable(): void;
}
import Modal from "flarum/common/components/Modal";
