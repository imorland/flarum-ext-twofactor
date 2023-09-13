/// <reference types="flarum/@types/translator-icu-rich" />
export default class TwoFactorEnableModal extends Modal<import("flarum/common/components/Modal").IInternalModalAttrs, undefined> {
    constructor();
    oninit(vnode: any): void;
    user: any;
    status: string | undefined;
    qrCodeUrl: any;
    backupCodes: any;
    token: any;
    code: any;
    activeTab: string | undefined;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    oncreate(vnode: any): void;
    onupdate(): void;
    content(): JSX.Element;
    verifyToken(): void;
    finish(): void;
    onSubmit(e: any): void;
}
import Modal from "flarum/common/components/Modal";
